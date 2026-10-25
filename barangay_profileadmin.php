<?php
include 'db_connect.php'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$success_msg = '';
$error_msg = '';

// Ensure upload directory exists
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// 1. Handle Form Submission for Profile Update (Standard POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $stmt = $pdo->prepare("UPDATE barangay_profile SET 
            barangay_name = ?, 
            established_year = ?, 
            barangay_captain = ?, 
            total_population = ?, 
            land_area = ?, 
            overview_text = ?, 
            economy_geography_text = ? 
            WHERE id = 1");
            
        $stmt->execute([
            $_POST['barangay_name'],
            $_POST['established_year'],
            $_POST['barangay_captain'],
            $_POST['total_population'],
            $_POST['land_area'],
            $_POST['overview_text'],
            $_POST['economy_geography_text']
        ]);
        
        $success_msg = "Barangay profile successfully updated!";
    } catch (\PDOException $e) {
        $error_msg = "Error updating profile: " . $e->getMessage();
    }
}

// 2. Handle AJAX Form Submissions for Officials (Update, Add, or Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    try {
        if ($action === 'delete_official') {
            $official_id = $_POST['official_id'] ?? 0;
            
            // Delete the image file from server if it exists
            $imgStmt = $pdo->prepare("SELECT photo FROM barangay_officials WHERE id = ?");
            $imgStmt->execute([$official_id]);
            $officialData = $imgStmt->fetch(PDO::FETCH_ASSOC);
            if ($officialData && !empty($officialData['photo']) && file_exists($officialData['photo'])) {
                unlink($officialData['photo']);
            }

            $delStmt = $pdo->prepare("DELETE FROM barangay_officials WHERE id = ?");
            $delStmt->execute([$official_id]);
            echo json_encode(['success' => true, 'message' => 'Official successfully deleted from the database!']);
            exit;
        } 
        elseif ($action === 'update_officials') {
            // If existing officials data array is sent, loop through and update them
            if (isset($_POST['official_id']) && is_array($_POST['official_id'])) {
                for ($i = 0; $i < count($_POST['official_id']); $i++) {
                    $id = $_POST['official_id'][$i];
                    $name = $_POST['official_name'][$i];
                    $position = $_POST['official_position'][$i];

                    // Check if a new photo was uploaded for this official
                    $photo_sql = "";
                    $params = [$name, $position];

                    if (isset($_FILES['official_photo']['name'][$i]) && $_FILES['official_photo']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['official_photo']['tmp_name'][$i];
                        $fileName = $_FILES['official_photo']['name'][$i];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                        if (in_array($fileExtension, $allowedExtensions)) {
                            $newFileName = 'official_' . $id . '_' . time() . '.' . $fileExtension;
                            $dest_path = $upload_dir . $newFileName;

                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                // Delete old photo if exists
                                $oldImgStmt = $pdo->prepare("SELECT photo FROM barangay_officials WHERE id = ?");
                                $oldImgStmt->execute([$id]);
                                $oldData = $oldImgStmt->fetch(PDO::FETCH_ASSOC);
                                if ($oldData && !empty($oldData['photo']) && file_exists($oldData['photo'])) {
                                    unlink($oldData['photo']);
                                }

                                $photo_sql = ", photo = ?";
                                $params[] = $dest_path;
                            }
                        }
                    }

                    $params[] = $id;
                    $stmt = $pdo->prepare("UPDATE barangay_officials SET name = ?, position = ?" . $photo_sql . " WHERE id = ?");
                    $stmt->execute($params);
                }
            }

            // Handle adding a brand new official if filled out
            if (!empty($_POST['new_official_name']) && !empty($_POST['new_official_position'])) {
                $new_name = $_POST['new_official_name'];
                $new_position = $_POST['new_official_position'];
                $new_photo_path = null;

                if (isset($_FILES['new_official_photo']) && $_FILES['new_official_photo']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['new_official_photo']['tmp_name'];
                    $fileName = $_FILES['new_official_photo']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = 'official_new_' . time() . '.' . $fileExtension;
                        $dest_path = $upload_dir . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $new_photo_path = $dest_path;
                        }
                    }
                }

                $insertStmt = $pdo->prepare("INSERT INTO barangay_officials (name, position, photo) VALUES (?, ?, ?)");
                $insertStmt->execute([$new_name, $new_position, $new_photo_path]);
            }

            echo json_encode(['success' => true, 'message' => 'Barangay officials successfully updated!']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Fetch current profile data
$stmt = $pdo->query("SELECT * FROM barangay_profile WHERE id = 1");
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch current barangay officials list
$officialsStmt = $pdo->query("SELECT * FROM barangay_officials ORDER BY id ASC");
$officials = $officialsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Profile - Barangay <?php echo htmlspecialchars($profile['barangay_name']); ?>[cite: 1]</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #000066;
            --secondary-blue: #1a1a8c;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --primary-dark: #2c3e50;
            --text-gray: #555;
            --teal-green: #157347;   
            --deep-red: #dc3545;
            --card-blue: #154c79;
            --pill-light-blue: #2c72a8;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0 auto;
            padding: 0 20px 40px 20px;
            background-color: var(--bg-light);
            color: #333;
            min-height: 100vh;
        }

        .top-bar {
            background: var(--secondary-blue);
            color: white;
            padding: 8px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            margin: 0 -20px; 
        }

        .social-icons { display: flex; gap: 20px; color: white; }
        .social-icons i { cursor: pointer; font-size: 14px; transition: color 0.3s ease; }
        .social-icons i:hover { color: var(--accent-gold); }

        .main-nav {
            background: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0 -20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-section { display: flex; align-items: center; gap: 15px; }

        .back-link {
            color: var(--secondary-blue);
            font-size: 1.1rem;
            text-decoration: none;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--bg-light);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .back-link:hover { background: var(--secondary-blue); color: white; }

        .logo-section img { 
            height: 55px; width: 55px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--primary-blue);
        }

        .logo-text h2 { margin: 0; color: var(--primary-blue); font-size: 1.2rem; letter-spacing: 1px; }
        .logo-text small { color: var(--text-gray); font-size: 0.75rem; }

        .hero-banner {
            background: linear-gradient(rgba(26, 26, 140, 0.85), rgba(26, 26, 140, 0.85)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 50px 10%;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 0 -20px;
        }

        .welcome-msg h1 { font-size: 2.2rem; margin: 0 0 10px 0; font-weight: 800; }
        .welcome-msg h1 span { color: var(--accent-gold); }
        .welcome-msg p { font-size: 0.85rem; letter-spacing: 2px; margin: 0; opacity: 0.85; text-transform: uppercase; }

        .urgent-notices {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        .urgent-notices strong { display: block; margin-bottom: 5px; color: var(--accent-gold); letter-spacing: 1px; }
        .urgent-notices ul { margin: 0; padding-left: 15px; color: #f1f1f1; }

        .container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 40px auto 0 auto;
            align-items: start;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }

        .sidebar-info { text-align: center; }
        .sidebar-info img {
            width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
            border: 3px solid var(--secondary-blue); margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(26, 26, 140, 0.15);
        }

        .sidebar-title { font-size: 1.4rem; color: #1a1a1a; margin: 0 0 5px 0; font-weight: 800; }
        .sidebar-subtitle { color: var(--text-gray); font-size: 0.85rem; margin: 0 0 25px 0; }

        .info-group {
            text-align: left; background: var(--bg-light); padding: 12px 16px;
            border-radius: 12px; margin-bottom: 12px; border: 1px solid rgba(0,0,0,0.03);
        }
        .info-group label {
            display: block; font-size: 0.75rem; text-transform: uppercase;
            letter-spacing: 1px; color: var(--secondary-blue); font-weight: 700; margin-bottom: 3px;
        }
        .info-group p { margin: 0; font-size: 0.95rem; font-weight: 600; color: #222; }

        .section-title {
            font-size: 1.3rem; color: var(--secondary-blue); margin: 0 0 20px 0;
            font-weight: 700; border-bottom: 2px solid var(--bg-light); padding-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center;
        }

        .vm-heading { font-size: 0.95rem; color: var(--secondary-blue); margin: 0 0 8px 0; letter-spacing: 1px; font-weight: 700; }
        .vm-text { font-size: 0.9rem; color: var(--text-gray); line-height: 1.6; margin: 0; }

        /* ========================================================= */
        /* Organizational Chart Style (Matching Reference Image)    */
        /* ========================================================= */
        .org-chart-container {
            width: 100%;
            padding: 30px 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            overflow-x: auto;
            position: relative; /* REQUIRED for absolute SVG positioning */
        }

        .org-level {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            width: 100%;
            margin-bottom: 40px;
            position: relative;
        }

        /* Official Card Structure */
        .org-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            width: 220px;
            text-align: center;
            z-index: 2;
        }

        /* Circular Avatar Photo Container */
        .avatar-box {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid var(--primary-blue);
            overflow: hidden;
            background: #eaf2f8;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
            z-index: 3;
            margin-bottom: -15px; /* Overlaps onto the pill box below */
        }

        .avatar-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-box i {
            font-size: 42px;
            color: var(--primary-blue);
        }

        /* Official Pill Tag (Name & Position) */
        .info-pill {
            width: 100%;
            background: var(--primary-blue);
            border-radius: 20px;
            padding: 22px 10px 10px 10px;
            box-sizing: border-box;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .info-pill .official-name {
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .info-pill .official-title {
            background: var(--pill-light-blue);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-top: 4px;
            width: 90%;
            box-sizing: border-box;
            text-transform: capitalize;
        }

        /* Direct SVG Connecting Arrows Layer */
        .svg-connector {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }

        .svg-connector line {
            stroke: var(--primary-blue);
            stroke-width: 2;
        }

        /* Admin Controls & Forms */
        .admin-edit-btn {
            background: var(--secondary-blue); color: white; border: none;
            padding: 6px 14px; border-radius: 6px; font-size: 0.8rem;
            cursor: pointer; font-weight: 600; transition: background 0.3s;
        }
        .admin-edit-btn:hover { background: var(--primary-blue); }

        .edit-form-container {
            display: none; background: #fafafa; padding: 20px;
            border-radius: 12px; border: 1px dashed var(--secondary-blue); margin-top: 15px;
        }

        .form-control-group { margin-bottom: 15px; }
        .form-control-group label { display: block; font-size: 0.8rem; font-weight: 700; color: var(--secondary-blue); margin-bottom: 5px; }
        .form-control-group input, .form-control-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box;
        }
        .form-control-group textarea { resize: vertical; min-height: 100px; }

        .btn-save {
            background: var(--teal-green); color: white; border: none; padding: 10px 20px;
            border-radius: 8px; font-weight: 700; cursor: pointer; transition: opacity 0.3s;
        }
        .btn-save:hover { opacity: 0.9; }

        .btn-delete {
            background: var(--deep-red); color: white; border: none; padding: 8px 12px;
            border-radius: 6px; font-size: 0.8rem; cursor: pointer; text-decoration: none;
        }
        .btn-delete:hover { opacity: 0.85; }

        .alert-success {
            background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px;
            margin-bottom: 20px; border: 1px solid #c3e6cb; font-size: 0.9rem;
        }
        .alert-danger {
            background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 8px;
            margin-bottom: 20px; border: 1px solid #f5c6cb; font-size: 0.9rem;
        }

        /* Application Result & Confirmation Modal Custom Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content-custom {
            background-color: #fff; padding: 20px; border-radius: 12px;
            width: 100%; max-width: 420px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); position: relative; box-sizing: border-box;
        }
        .modal-close-btn {
            position: absolute; right: 18px; top: 15px; font-size: 1.4rem; font-weight: bold; color: #aaa; cursor: pointer;
        }
        .modal-close-btn:hover { color: #000; }
        .modal-header-custom { border-bottom: 2px solid #eee; padding-bottom: 8px; margin-bottom: 15px; padding-right: 30px; }
        .modal-header-custom h3 { margin: 0; color: var(--primary-blue); font-size: 1.1rem; }
        .modal-body-content { padding: 5px 0; font-size: 0.9rem; color: #333; line-height: 1.5; word-wrap: break-word; }
        .btn-modal-action { background: var(--primary-blue); color: white; padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-action:hover { background: #1a1a8c; }
        .btn-modal-cancel { background: #6c757d; color: white; padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-cancel:hover { background: #5a6268; }
        .btn-modal-danger { background: var(--deep-red); color: white; padding: 8px 18px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-danger:hover { opacity: 0.85; }
        .modal-footer { margin-top: 20px; text-align: right; padding-top: 15px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px; }

        /* Professional Footer Styles */
        .site-footer {
            background-color: #120b13; color: #d1d5db; padding: 60px 40px 30px 40px;
            margin: 80px -20px -40px -20px; font-size: 0.85rem;
        }
        .footer-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.5fr; gap: 40px; padding-bottom: 40px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .footer-col h3, .footer-col h4 { color: white; font-weight: 700; margin-top: 0; }
        .footer-col h3 { font-size: 1.1rem; display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        .footer-col h3 img { height: 30px; width: 30px; border-radius: 50%; object-fit: cover; }
        .footer-col h4 { font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; color: #9ca3af; }
        .footer-col p { color: #9ca3af; line-height: 1.6; margin-bottom: 20px; font-size: 0.8rem; }
        .footer-col ul { list-style: none; padding: 0; margin: 0; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col ul li a { color: #9ca3af; text-decoration: none; transition: color 0.3s ease; font-size: 0.85rem; }
        .footer-col ul li a:hover { color: white; }
        .footer-social-icons { display: flex; gap: 15px; }
        .footer-social-icons a { background: rgba(255, 255, 255, 0.08); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.3s ease; }
        .footer-social-icons a:hover { background: var(--secondary-blue); }
        .footer-bottom { max-width: 1200px; margin: 30px auto 0 auto; display: flex; justify-content: space-between; align-items: center; color: #9ca3af; font-size: 0.8rem; flex-wrap: wrap; gap: 15px; }
        .footer-bottom-links { display: flex; gap: 25px; }
        .footer-bottom-links a { color: #9ca3af; text-decoration: none; transition: color 0.3s ease; }
        .footer-bottom-links a:hover { color: white; }
        
        .back-to-top {
            position: fixed; bottom: 30px; right: 30px; background: var(--secondary-blue);
            color: white; width: 45px; height: 45px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 999;
        }

        @media(max-width: 900px) { 
            .container { grid-template-columns: 1fr; } 
            .footer-container { grid-template-columns: 1fr 1fr; } 
            .org-level { flex-wrap: wrap; gap: 30px; }
            .svg-connector { display: none; }
        }
    </style>
</head>
<body>

    <!-- Top Social Bar -->
    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-twitter"></i>
        </div>
        <div></div>
    </div>

    <!-- Main Navigation Header -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="admin_dashboard.php" class="back-link" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
            <div class="logo-text">
                <h2>BARANGAY <?php echo htmlspecialchars($profile['barangay_name']); ?></h2>
                <small><?php echo htmlspecialchars($profile['municipality'] ?? 'ORIENTAL MINDORO'); ?></small>
            </div>
        </div>
    </nav>

    <!-- Hero Banner Profile -->
    <header class="hero-banner profile-hero">
        <div class="welcome-msg">
            <h1>BARANGAY <span>PROFILE</span></h1>
            <p>PROVINCE OF ORIENTAL MINDORO - BARANGAY <?php echo htmlspecialchars($profile['barangay_name']); ?></p>
        </div>
        <div class="urgent-notices">
            <strong>OFFICE HOURS</strong>
            <ul>
                <li>Monday - Friday: 8:00 AM - 5:00 PM</li>
                <li>Emergency Services: 24/7 Available</li>
            </ul>
        </div>
    </header>

    <!-- Content Layout Grid -->
    <div class="container">
        
        <!-- Sidebar Info Section -->
        <div class="card sidebar-info">
            <img src="image/barangaylogo3.jpg" alt="Barangay Seal">
            <h2 class="sidebar-title"><?php echo htmlspecialchars($profile['barangay_name']); ?></h2>
            <p class="sidebar-subtitle">Established <?php echo htmlspecialchars($profile['established_year']); ?></p>
            
            <div class="info-group">
                <label>Total Population</label>
                <p><?php echo htmlspecialchars($profile['total_population']); ?></p>
            </div>
            <div class="info-group">
                <label>Land Area</label>
                <p><?php echo htmlspecialchars($profile['land_area']); ?></p>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content-wrapper">
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert-success"><i class="fa fa-check-circle"></i> <?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- Profile & Background Card -->
            <div class="card">
                <h3 class="section-title">
                    <span>Barangay Profile & Background</span>
                    <button class="admin-edit-btn" onclick="toggleEditForm()">
                        <i class="fa fa-edit"></i> Edit Profile
                    </button>
                </h3>

                <div id="profile-display-view" class="vision-mission-grid">
                    <div>
                        <h4 class="vm-heading">OVERVIEW</h4>
                        <p class="vm-text"><?php echo nl2br(htmlspecialchars($profile['overview_text'])); ?></p>
                    </div>
                    <div style="margin-top: 20px;">
                        <h4 class="vm-heading">LOCAL ECONOMY & GEOGRAPHY</h4>
                        <p class="vm-text"><?php echo nl2br(htmlspecialchars($profile['economy_geography_text'])); ?></p>
                    </div>
                </div>

                <div id="profile-edit-form" class="edit-form-container">
                    <form action="" method="POST">
                        <h4 style="margin-top:0; color:var(--secondary-blue);">Update Barangay Details</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-control-group">
                                <label>Barangay Name</label>
                                <input type="text" name="barangay_name" value="<?php echo htmlspecialchars($profile['barangay_name']); ?>" required>
                            </div>
                            <div class="form-control-group">
                                <label>Established Year</label>
                                <input type="text" name="established_year" value="<?php echo htmlspecialchars($profile['established_year']); ?>" required>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div class="form-control-group">
                                <label>Barangay Captain</label>
                                <input type="text" name="barangay_captain" value="<?php echo htmlspecialchars($profile['barangay_captain']); ?>" required>
                            </div>
                            <div class="form-control-group">
                                <label>Total Population</label>
                                <input type="text" name="total_population" value="<?php echo htmlspecialchars($profile['total_population']); ?>" required>
                            </div>
                            <div class="form-control-group">
                                <label>Land Area</label>
                                <input type="text" name="land_area" value="<?php echo htmlspecialchars($profile['land_area']); ?>" required>
                            </div>
                        </div>

                        <div class="form-control-group">
                            <label>Overview Text</label>
                            <textarea name="overview_text" required><?php echo htmlspecialchars($profile['overview_text']); ?></textarea>
                        </div>

                        <div class="form-control-group">
                            <label>Local Economy & Geography</label>
                            <textarea name="economy_geography_text" required><?php echo htmlspecialchars($profile['economy_geography_text']); ?></textarea>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="update_profile" class="btn-save"><i class="fa fa-save"></i> Save Changes</button>
                            <button type="button" class="admin-edit-btn" style="background:#6c757d;" onclick="toggleEditForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

           <!-- Barangay Officials Organizational Chart Card -->
<div class="card">
    <h3 class="section-title">
        <span>Barangay Officials</span>
        <button class="admin-edit-btn" onclick="toggleOfficialsForm()">
            <i class="fa fa-edit"></i> Manage Officials
        </button>
    </h3>
    
    <!-- Display View for Officials (Organizational Chart Style) -->
    <div id="officials-display-view">
        <?php if (count($officials) > 0): ?>
            <?php 
            $captains = [];
            $otherOfficials = [];

            // 1. Separate the Barangay Captain from the rest of the officials
            foreach ($officials as $official) {
                $posLower = strtolower($official['position']);
                if (strpos($posLower, 'captain') !== false || strpos($posLower, 'punong barangay') !== false) {
                    $captains[] = $official;
                } else {
                    $otherOfficials[] = $official;
                }
            }

            // Fallback if no specific captain title is found
            if (empty($captains) && !empty($officials)) {
                $captains[] = array_shift($officials);
                $otherOfficials = $officials;
            }

            // 2. Split the remaining officials into two equal/balanced rows (Row 1 and Row 2)
            $totalOthers = count($otherOfficials);
            $halfCount = ceil($totalOthers / 2);
            
            $row1Officials = array_slice($otherOfficials, 0, $halfCount);
            $row2Officials = array_slice($otherOfficials, $halfCount);
            ?>

            <div class="org-chart-container" id="orgChartContainer">
                <!-- Connecting Arrows Layer -->
                <svg class="svg-connector" id="svgConnector">
                    <defs>
                        <marker id="arrowhead" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto">
                            <path d="M 0 0 L 10 5 L 0 10 z" fill="#000066" />
                        </marker>
                    </defs>
                </svg>

                <!-- Level 1: Top Executive (Barangay Captain) -->
                <div class="org-level" id="level1">
                    <?php foreach ($captains as $index => $captain): ?>
                        <div class="org-node" id="captain-<?php echo $index; ?>">
                            <div class="avatar-box">
                                <?php if (!empty($captain['photo']) && file_exists($captain['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($captain['photo']); ?>" alt="<?php echo htmlspecialchars($captain['name']); ?>">
                                <?php else: ?>
                                    <i class="fa fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="info-pill">
                                <div class="official-name"><?php echo htmlspecialchars($captain['name']); ?></div>
                                <div class="official-title"><?php echo htmlspecialchars($captain['position']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Level 2: First Following Row of Officials (e.g., Kagawads, Secretary, Treasurer) -->
                <?php if (!empty($row1Officials)): ?>
                    <div class="org-level" id="level2">
                        <?php foreach ($row1Officials as $index => $manager): ?>
                            <div class="org-node level2-node" id="manager-<?php echo $index; ?>">
                                <div class="avatar-box">
                                    <?php if (!empty($manager['photo']) && file_exists($manager['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($manager['photo']); ?>" alt="<?php echo htmlspecialchars($manager['name']); ?>">
                                    <?php else: ?>
                                        <i class="fa fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="info-pill">
                                    <div class="official-name"><?php echo htmlspecialchars($manager['name']); ?></div>
                                    <div class="official-title"><?php echo htmlspecialchars($manager['position']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Level 3: Second Following Row of Officials (Remaining Staff / Support) -->
                <?php if (!empty($row2Officials)): ?>
                    <div class="org-level" id="level3">
                        <?php foreach ($row2Officials as $index => $st): ?>
                            <div class="org-node level3-node" id="staff-<?php echo $index; ?>">
                                <div class="avatar-box">
                                    <?php if (!empty($st['photo']) && file_exists($st['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($st['photo']); ?>" alt="<?php echo htmlspecialchars($st['name']); ?>">
                                    <?php else: ?>
                                        <i class="fa fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="info-pill">
                                    <div class="official-name"><?php echo htmlspecialchars($st['name']); ?></div>
                                    <div class="official-title"><?php echo htmlspecialchars($st['position']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Script to dynamically render and refresh arrow connectors -->
            <script>
                function drawChartConnectors() {
                    const container = document.getElementById('orgChartContainer');
                    const svg = document.getElementById('svgConnector');
                    const displayView = document.getElementById('officials-display-view');
                    
                    // Do not draw if container is hidden or doesn't exist
                    if (!container || !svg || (displayView && displayView.style.display === 'none')) return;

                    // Set explicit SVG dimensions matching container scroll size to handle scrolling properly
                    svg.style.width = container.scrollWidth + 'px';
                    svg.style.height = container.scrollHeight + 'px';
                    svg.setAttribute('width', container.scrollWidth);
                    svg.setAttribute('height', container.scrollHeight);

                    // Clear previous lines except definitions
                    const defs = svg.querySelector('defs');
                    svg.innerHTML = '';
                    if (defs) {
                        svg.appendChild(defs);
                    } else {
                        const newDefs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                        newDefs.innerHTML = '<marker id="arrowhead" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 0 L 10 5 L 0 10 z" fill="#000066" /></marker>';
                        svg.appendChild(newDefs);
                    }

                    const containerRect = container.getBoundingClientRect();
                    const captains = document.querySelectorAll('#level1 .org-node');
                    const managers = document.querySelectorAll('#level2 .org-node');
                    const staff = document.querySelectorAll('#level3 .org-node');

                    // Draw connections from Level 1 (Captain) to Level 2 (Managers/Kagawads)
                    captains.forEach(captain => {
                        const capRect = captain.getBoundingClientRect();
                        const startX = capRect.left - containerRect.left + container.scrollLeft + capRect.width / 2;
                        const startY = capRect.bottom - containerRect.top + container.scrollTop;

                        managers.forEach(manager => {
                            const mgrRect = manager.getBoundingClientRect();
                            const endX = mgrRect.left - containerRect.left + container.scrollLeft + mgrRect.width / 2;
                            const endY = mgrRect.top - containerRect.top + container.scrollTop;

                            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                            line.setAttribute('x1', startX);
                            line.setAttribute('y1', startY);
                            line.setAttribute('x2', endX);
                            line.setAttribute('y2', endY);
                            line.setAttribute('marker-end', 'url(#arrowhead)');
                            svg.appendChild(line);
                        });
                    });

                    // Draw connections from Level 2 to Level 3 (Support / Remaining Staff)
                    if (managers.length > 0 && staff.length > 0) {
                        managers.forEach((manager, i) => {
                            const mgrRect = manager.getBoundingClientRect();
                            const startX = mgrRect.left - containerRect.left + container.scrollLeft + mgrRect.width / 2;
                            const startY = mgrRect.bottom - containerRect.top + container.scrollTop;

                            if (staff[i]) {
                                const stRect = staff[i].getBoundingClientRect();
                                const endX = stRect.left - containerRect.left + container.scrollLeft + stRect.width / 2;
                                const endY = stRect.top - containerRect.top + container.scrollTop;

                                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                                line.setAttribute('x1', startX);
                                line.setAttribute('y1', startY);
                                line.setAttribute('x2', endX);
                                line.setAttribute('y2', endY);
                                line.setAttribute('marker-end', 'url(#arrowhead)');
                                svg.appendChild(line);
                            }
                        });
                    }
                }

                // Bind triggers for load, resize, and layout changes
                window.addEventListener('load', () => setTimeout(drawChartConnectors, 100));
                window.addEventListener('resize', () => {
                    clearTimeout(window.resizeTimer);
                    window.resizeTimer = setTimeout(drawChartConnectors, 100);
                });
            </script>
        <?php else: ?>
            <p style="color:var(--text-gray); font-style: italic;">No officials recorded yet.</p>
        <?php endif; ?>
    </div>

                <!-- Admin Edit/Add/Delete Form for Officials (Hidden by Default) -->
                <div id="officials-edit-form" class="edit-form-container">
                    <form id="officialsEditForm" enctype="multipart/form-data" onsubmit="saveOfficialsDetails(event)">
                        <input type="hidden" name="action" value="update_officials">
                        <h4 style="margin-top:0; color:var(--secondary-blue);">Edit or Update Existing Officials</h4>
                        
                        <?php foreach ($officials as $official): ?>
                            <div style="background: white; padding: 12px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #ddd;">
                                <input type="hidden" name="official_id[]" value="<?php echo $official['id']; ?>">
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 10px; align-items: center;">
                                    <div class="form-control-group" style="margin-bottom:0;">
                                        <label>Name</label>
                                        <input type="text" name="official_name[]" value="<?php echo htmlspecialchars($official['name']); ?>" required>
                                    </div>
                                    <div class="form-control-group" style="margin-bottom:0;">
                                        <label>Position</label>
                                        <input type="text" name="official_position[]" value="<?php echo htmlspecialchars($official['position']); ?>" required>
                                    </div>
                                    <div class="form-control-group" style="margin-bottom:0;">
                                        <label>Photo (Leave blank to keep)</label>
                                        <input type="file" name="official_photo[]" accept="image/*" style="font-size:0.75rem;">
                                    </div>
                                    <div>
                                        <button type="button" class="btn-delete" title="Delete Official" onclick="confirmDeleteOfficial(<?php echo $official['id']; ?>)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <hr style="margin: 20px 0; border:0; border-top: 1px solid #ddd;">
                        
                        <h4 style="color:var(--secondary-blue);">Add New Official</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                            <div class="form-control-group">
                                <label>New Official Name</label>
                                <input type="text" name="new_official_name" placeholder="e.g. Juan Dela Cruz">
                            </div>
                            <div class="form-control-group">
                                <label>New Official Position</label>
                                <input type="text" name="new_official_position" placeholder="e.g. Kagawad - Health">
                            </div>
                            <div class="form-control-group">
                                <label>New Official Photo</label>
                                <input type="file" name="new_official_photo" accept="image/*" style="font-size:0.75rem;">
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <button type="submit" id="submitOfficialsBtn" class="btn-save"><i class="fa fa-save"></i> Save Officials</button>
                            <button type="button" class="admin-edit-btn" style="background:#6c757d;" onclick="toggleOfficialsForm()">Cancel</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div id="confirmDeleteModal" class="modal">
        <div class="modal-content-custom">
            <span class="modal-close-btn" onclick="closeConfirmModal()">&times;</span>
            <div class="modal-header-custom">
                <h3>Confirm Deletion</h3>
            </div>
            <div class="modal-body-content">
                <p style="margin: 0;">Are you sure you want to permanently delete this official?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn-modal-danger" id="confirmDeleteBtn" onclick="executeDeleteOfficial()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Application Result Modal -->
    <div id="resultModal" class="modal">
        <div class="modal-content-custom">
            <span class="modal-close-btn" onclick="closeResultModal()">&times;</span>
            <div class="modal-header-custom">
                <h3 id="resultModalTitle">Application Notice</h3>
            </div>
            <div class="modal-body-content">
                <p id="resultModalMessageBody" style="margin: 0;">Processing your request...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-action" onclick="closeResultModal()">OK</button>
            </div>
        </div>
    </div>

    <!-- Professional Footer Section -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-col">
                <h3><img src="image/bmis.jpg" alt="BMIS Logo"> BMIS</h3>
                <p>BARANGAY MANAGEMENT & INFORMATION SYSTEM</p>
                <p>A secure digital platform supporting resident records, barangay services, communication, and local governance.</p>
                <div class="footer-social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-facebook-messenger"></i></a>
                    <a href="#"><i class="fa-solid fa-globe"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>PLATFORM</h4>
                <ul>
                    <li><a href="admin_dashboard.php">Admin Portal</a></li>
                    <li><a href="resident_portal.php">Resident Services</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>INFORMATION</h4>
                <ul>
                    <li><a href="barangay_profile.php">About BMIS</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>SERVICE NOTICE</h4>
                <p>Access is limited to verified users and authorized barangay personnel.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; 2026 BMIS. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="#">Data Privacy</a>
                <a href="#">Terms of Use</a>
            </div>
        </div>
    </footer>

    <a href="#" class="back-to-top">
        <i class="fa-solid fa-arrow-up"></i>
    </a>

    <!-- JavaScript & AJAX Handlers -->
    <script>
        const resultModal = document.getElementById('resultModal');
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const resultModalTitle = document.getElementById('resultModalTitle');
        const resultModalMessageBody = document.getElementById('resultModalMessageBody');
        
        let officialIdToDelete = null;

        function toggleEditForm() {
            const displayView = document.getElementById('profile-display-view');
            const editForm = document.getElementById('profile-edit-form');
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
                displayView.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                displayView.style.display = 'grid';
            }
        }

        function toggleOfficialsForm() {
            const displayView = document.getElementById('officials-display-view');
            const editForm = document.getElementById('officials-edit-form');
            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
                displayView.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                displayView.style.display = 'block';
                // Wait for DOM to display the container blocks properly before drawing lines
                setTimeout(drawChartConnectors, 100);
            }
        }

        function closeResultModal() { 
            resultModal.style.display = "none"; 
            location.reload();
        }

        function closeConfirmModal() {
            confirmDeleteModal.style.display = "none";
            officialIdToDelete = null;
        }

        function confirmDeleteOfficial(officialId) {
            officialIdToDelete = officialId;
            confirmDeleteModal.style.display = 'flex';
        }

        function executeDeleteOfficial() {
            if (!officialIdToDelete) return;

            confirmDeleteModal.style.display = 'none';

            const formData = new FormData();
            formData.append('action', 'delete_official');
            formData.append('official_id', officialIdToDelete);

            fetch('', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultModalTitle.textContent = "Success: Official Deleted";
                    resultModalMessageBody.textContent = data.message;
                } else {
                    resultModalTitle.textContent = "Notice: Error Occurred";
                    resultModalMessageBody.textContent = "Error: " + data.message;
                }
                resultModal.style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                resultModalTitle.textContent = "System Error";
                resultModalMessageBody.textContent = "An unexpected error occurred.";
                resultModal.style.display = 'flex';
            });
        }

        function saveOfficialsDetails(event) {
            event.preventDefault();
            const form = document.getElementById('officialsEditForm');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitOfficialsBtn');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

            fetch('', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                toggleOfficialsForm();
                if (data.success) {
                    resultModalTitle.textContent = "Success: Officials Updated";
                    resultModalMessageBody.textContent = data.message;
                } else {
                    resultModalTitle.textContent = "Notice: Error Occurred";
                    resultModalMessageBody.textContent = "Error: " + data.message;
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-save"></i> Save Officials';
                resultModal.style.display = 'flex';
            })
            .catch(error => {
                toggleOfficialsForm();
                console.error('Error:', error);
                resultModalTitle.textContent = "System Error";
                resultModalMessageBody.textContent = "An unexpected error occurred.";
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa fa-save"></i> Save Officials';
                resultModal.style.display = 'flex';
            });
        }

        window.onclick = function(event) {
            if (event.target === resultModal) closeResultModal();
            if (event.target === confirmDeleteModal) closeConfirmModal();
        }
    </script>
</body>
</html>