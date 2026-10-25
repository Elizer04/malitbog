<?php
// 1. Start session and enforce access control (Allow Admin and Official)
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Determine if the current user is an admin or an official
$user_role = $_SESSION['role'] ?? '';
$is_admin = ($user_role === 'admin' || isset($_SESSION['is_admin'])); // Adjust based on your session variables
// Alternatively, if your official login sets $_SESSION['role'] === 'official':
$is_official = ($user_role === 'official');

// 2. Database connection setup
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "bmis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Compute next sequential Resident ID based on database count (Synced with Registration logic)
$count_result = $conn->query("SELECT COUNT(*) AS total FROM residents");
$count_row = $count_result->fetch_assoc();
$next_num = $count_row['total'] + 1;
$current_year = date('Y');
$auto_resident_id = $current_year . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);

// Handle Add New Resident Action (Admin Only Guard)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resident'])) {
    if ($is_official) {
        echo "<script>alert('Access Denied: Officials can only view resident records.'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }

    $resident_id = !empty($_POST['resident_id']) ? trim($_POST['resident_id']) : $auto_resident_id;
    
    $first_name        = trim($_POST['first_name']);
    $middle_name       = trim($_POST['middle_name']);
    $last_name         = trim($_POST['last_name']);
    $full_name         = trim("$first_name $middle_name $last_name");
    $sex               = $_POST['sex'];
    $date_of_birth     = $_POST['date_of_birth'];
    $civil_status      = $_POST['civil_status'];
    $religion          = trim($_POST['religion']);
    $citizenship       = trim($_POST['citizenship']);
    $blood_type        = $_POST['blood_type'];
    $occupation        = trim($_POST['occupation']);
    $is_head_of_family = isset($_POST['is_head_of_family']) ? 1 : 0;
    $number_of_dependents = !empty($_POST['number_of_dependents']) ? intval($_POST['number_of_dependents']) : 0;
    $spouse_name       = trim($_POST['spouse_name']);
    $precinct_number   = trim($_POST['precinct_number']);
    $voter_status      = $_POST['voter_status'];
    $membership        = trim($_POST['membership']);
    $address           = trim($_POST['address']);
    $email             = trim($_POST['email']);
    $contact_no        = trim($_POST['contact_no']);
    $password_val      = password_hash('defaultpassword', PASSWORD_DEFAULT); 
    $status            = 'Verified';

    $profile_image = 'default.png';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['profile_image']['name']);
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $profile_image = $file_name;
        }
    }

    $stmt = $conn->prepare("INSERT INTO residents (resident_id, full_name, last_name, first_name, middle_name, sex, occupation, membership, precinct_number, religion, citizenship, voter_status, blood_type, is_head_of_family, number_of_dependents, spouse_name, date_of_birth, civil_status, address, email, password, contact_no, status, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "sssssssssssssissssssssss", 
        $resident_id, $full_name, $last_name, $first_name, $middle_name, 
        $sex, $occupation, $membership, $precinct_number, $religion, 
        $citizenship, $voter_status, $blood_type, $is_head_of_family, 
        $number_of_dependents, $spouse_name, $date_of_birth, $civil_status, 
        $address, $email, $password_val, $contact_no, $status, $profile_image
    );

    if ($stmt->execute()) {
        echo "<script>alert('New resident successfully added and saved to database!'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    } else {
        echo "<script>alert('Error adding resident: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Handle Update/Edit Resident Action (Admin Only Guard)[cite: 1]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_resident'])) {
    if ($is_official) {
        echo "Access Denied: Officials can only view resident records.";
        exit();
    }

    $resident_id       = trim($_POST['resident_id']);
    $first_name        = trim($_POST['first_name']);
    $middle_name       = trim($_POST['middle_name']);
    $last_name         = trim($_POST['last_name']);
    $full_name         = trim("$first_name $middle_name $last_name");
    $sex               = $_POST['sex'];
    $date_of_birth     = $_POST['date_of_birth'];
    $civil_status      = $_POST['civil_status'];
    $religion          = trim($_POST['religion']);
    $citizenship       = trim($_POST['citizenship']);
    $blood_type        = $_POST['blood_type'];
    $occupation        = trim($_POST['occupation']);
    $is_head_of_family = isset($_POST['is_head_of_family']) ? 1 : 0;
    $number_of_dependents = !empty($_POST['number_of_dependents']) ? intval($_POST['number_of_dependents']) : 0;
    $spouse_name       = trim($_POST['spouse_name']);
    $precinct_number   = trim($_POST['precinct_number']);
    $voter_status      = $_POST['voter_status'];
    $membership        = trim($_POST['membership']);
    $address           = trim($_POST['address']);
    $email             = trim($_POST['email']);
    $contact_no        = trim($_POST['contact_no']);
    $status            = $_POST['status'];

    $stmt = $conn->prepare("UPDATE residents SET full_name = ?, last_name = ?, first_name = ?, middle_name = ?, sex = ?, occupation = ?, membership = ?, precinct_number = ?, religion = ?, citizenship = ?, voter_status = ?, blood_type = ?, is_head_of_family = ?, number_of_dependents = ?, spouse_name = ?, date_of_birth = ?, civil_status = ?, address = ?, email = ?, contact_no = ?, status = ? WHERE resident_id = ?");
    
    $stmt->bind_param(
        "sssssssssssssissssssss", 
        $full_name, $last_name, $first_name, $middle_name, 
        $sex, $occupation, $membership, $precinct_number, $religion, 
        $citizenship, $voter_status, $blood_type, $is_head_of_family, 
        $number_of_dependents, $spouse_name, $date_of_birth, $civil_status, 
        $address, $email, $contact_no, $status, $resident_id
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating resident: " . $conn->error;
    }
    $stmt->close();
    exit();
}

// Handle Verification Action (Admin Only Guard)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_id'])) {
    if ($is_official) {
        echo "<script>alert('Access Denied: Officials can only view resident records.'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }

    $verify_id = $_POST['verify_id'];
    $stmt = $conn->prepare("UPDATE residents SET status = 'Verified' WHERE resident_id = ?");
    $stmt->bind_param("s", $verify_id);
    
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Error updating status: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Handle Missing Requirements Action (Admin Only Guard)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['missing_req_id'])) {
    if ($is_official) {
        echo "Access Denied: Officials can only view resident records.";
        exit();
    }

    $missing_req_id  = $_POST['missing_req_id'];
    $resident_name   = isset($_POST['resident_name']) ? trim($_POST['resident_name']) : 'Resident';
    $resident_email  = isset($_POST['resident_email']) ? trim($_POST['resident_email']) : 'N/A';
    $current_date    = date("F j, Y");
    
    $formatted_message = "OFFICIAL NOTICE: MISSING REQUIREMENTS\n"
                       . "Barangay Malitbog Administrative Office\n"
                       . "Date: " . $current_date . "\n\n"
                       . "Dear " . $resident_name . " (" . $resident_email . "),\n\n"
                       . "We are writing to inform you that your resident verification application is currently on hold due to missing or incomplete documentation.\n\n"
                       . "Please update your submission at your earliest convenience to complete the verification process.\n\n"
                       . "Sincerely,\n"
                       . "Barangay Malitbog Administration";
    
    $stmt = $conn->prepare("UPDATE residents SET status = 'Incomplete', remarks = ? WHERE resident_id = ?");
    $stmt->bind_param("ss", $formatted_message, $missing_req_id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error notifying resident: " . $conn->error;
    }
    $stmt->close();
    exit();
}

// Handle Delete Resident Action (Admin Only Guard)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if ($is_official) {
        echo "<script>alert('Access Denied: Officials can only view resident records.'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    }

    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM residents WHERE resident_id = ?");
    $stmt->bind_param("s", $delete_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Resident record successfully deleted from the database!'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting resident: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Fetch database records matching your column definitions
$pending_query = "SELECT * FROM residents WHERE LOWER(status) IN ('pending', 'incomplete', 'pending registration') ORDER BY resident_id DESC";
$verified_query = "SELECT * FROM residents WHERE LOWER(status) = 'verified' ORDER BY resident_id DESC";

$pending_result = $conn->query($pending_query);
$verified_result = $conn->query($verified_query);

$pending_count = $pending_result ? $pending_result->num_rows : 0;
$verified_count = $verified_result ? $verified_result->num_rows : 0;
$total_count = $pending_count + $verified_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Residents - Barangay Malitbog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #000066;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --admin-red: #cc0000;
            --admin-orange: #d97706;
            --admin-green: #28a745;
        }

        body {
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
        }

        .top-bar {
            background: #1a1a8c;
            color: white;
            padding: 5px 10%;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .top-bar div:first-child {
            display: flex;
            gap: 15px;
        }

        .social-icons {
            display: flex;
            gap: 20px;
        }

        .main-nav {
            background: white;
            padding: 8px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-link {
            color: var(--primary-blue);
            font-size: 1.2rem;
            text-decoration: none;
            margin-right: 5px;
            transition: color 0.3s ease;
        }

        .back-link:hover { color: var(--accent-gold); }

        .logo-section img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .logo-text h2 {
            margin: 0;
            color: var(--primary-blue);
            font-size: 1.1rem;
            letter-spacing: 1px;
        }
        .logo-text small { font-size: 0.7rem; }

        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 30px; 
        }

        .rd-nav-item, 
        .rd-nav-link-dropdown a, 
        .rd-nav-link-dropdown span {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #333;
            gap: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .rd-nav-item.custom-home-style span,
        .rd-menu-symbol-trigger.custom-profile-style span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            text-transform: capitalize;
            letter-spacing: 0.5px;
        }

        .rd-nav-item i, 
        .rd-menu-symbol-trigger i { font-size: 1.1rem; }

        .rd-nav-item:hover,
        .rd-menu-symbol-trigger:hover { color: var(--primary-blue); }

        .rd-nav-link-dropdown {
            display: inline-flex;
            align-items: center;
            position: relative;
        }

        .rd-nav-item.active { color: var(--accent-gold); }

        .hero-banner {
            background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 80px 10%;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-msg h1 { margin: 0; font-size: 2.2rem; }
        .welcome-msg p { font-size: 1.2rem; margin-top: 10px; opacity: 0.9; }
        .welcome-msg span { color: var(--accent-gold); }

        .container {
            max-width: 1200px;
            margin: -30px auto 40px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .section-title {
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 25px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .tab-btn {
            background: #f1f3f5;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            color: #555;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover { background: #e2e6ea; }
        .tab-btn.active { background: var(--primary-blue); color: white; }

        .tab-count {
            background: rgba(0, 0, 0, 0.15);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .tab-btn.active .tab-count { background: var(--accent-gold); color: #000; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 350px;
            font-family: inherit;
        }

        .btn-add {
            background: var(--primary-blue);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .btn-add:hover { background: #000044; }

        .resident-table {
            width: 100%;
            border-collapse: collapse;
        }

        .resident-table th {
            background: #f8f9fa;
            color: var(--primary-blue);
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #eee;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .resident-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

        .status-pill {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-incomplete { background: #feefc3; color: #b45309; }
        .status-active { background: #e6ffed; color: #28a745; }

        .action-btns i {
            margin-right: 15px;
            cursor: pointer;
            color: var(--primary-blue);
            transition: 0.2s;
        }

        .action-btns i:hover { transform: scale(1.2); }
        .action-btns i.fa-trash { color: var(--admin-red); }
        .action-btns i.fa-edit { color: var(--admin-orange); }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close-btn:hover { color: #000; }

        .modal-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary-blue);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f4f4f4;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .modal-profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .profile-img-preview {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
        }

        .modal-footer {
            margin-top: 20px;
            text-align: right;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-verify {
            background-color: var(--admin-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-verify:hover { background-color: #218838; }

        .btn-notify {
            background-color: var(--admin-orange);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-notify:hover { background-color: #b45309; }

        /* Application Result Modal Styles */
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

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: bold;
            color: #444;
        }

        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .letter-preview {
            background-color: #fcfcfc;
            border: 1px solid #dcdcdc;
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #333;
            line-height: 1.5;
            white-space: pre-line;
        }

        .letter-header {
            font-weight: bold;
            text-transform: uppercase;
            color: var(--primary-blue);
            margin-bottom: 4px;
        }

        .letter-sub {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 12px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 8px;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-twitter"></i>
        </div>
        <div class="profile-section">
            <span>
                <i class="fa fa-user-shield"></i> 
                <?php echo $is_official ? 'Barangay Official Portal (View Only)' : 'Administrator Access'; ?>
            </span>
        </div>
    </div>

    <nav class="main-nav">
        <div class="logo-section">
            <a href="<?php echo $is_official ? 'official_dashboard.php' : 'admin_dashboard.php'; ?>" class="back-link" title="Go Back">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small><?php echo $is_official ? 'OFFICIAL PORTAL' : 'ADMINISTRATIVE PANEL'; ?></small>
            </div>
        </div>
        <div class="nav-links">
            <a href="<?php echo $is_official ? 'official_dashboard.php' : 'admin_dashboard.php'; ?>" class="rd-nav-item custom-home-style" title="Home">
                <i class="fas fa-home"></i>
                <span>home</span>
            </a>
            <div class="rd-nav-link-dropdown">
                
            </div>
        </div>
    </nav>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>RESIDENT <span>DIRECTORY</span></h1>
            <p><?php echo $is_official ? 'Viewing Resident Records - Barangay Malitbog, Laguna' : 'Managing the People of Barangay Malitbog, Laguna'; ?></p>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: bold; font-size: 1.2rem; color: var(--accent-gold);">
                TOTAL: <?php echo $total_count; ?>
            </div>
            <p>System Status: <span style="color: #00ff00;">Online</span></p>
        </div>
    </header>

    <div class="container">
        <div class="section-title">
            <span>Resident Directory (<?php echo $is_official ? 'View Only' : 'Management'; ?>)</span>
            <small style="font-weight: normal; font-size: 0.8rem; color: #666;">Database Records</small>
        </div>

        <div class="action-bar">
            <input type="text" id="searchInput" class="search-box" onkeyup="filterResidents()" placeholder="Search by name or ID">
            
            <!-- Hide 'Add New Resident' button if logged in as official -->
            <?php if (!$is_official): ?>
                <button class="btn-add" onclick="openAddModal()"><i class="fa fa-plus"></i> Add New Resident</button>
            <?php endif; ?>
        </div>

        <div class="category-tabs">
            <button class="tab-btn active" onclick="switchTab('pending')">
                <i class="fa fa-clock"></i> Pending <span class="tab-count"><?php echo $pending_count; ?></span>
            </button>
            <button class="tab-btn" onclick="switchTab('verified')">
                <i class="fa fa-check-circle"></i> Verified <span class="tab-count"><?php echo $verified_count; ?></span>
            </button>
        </div>

        <!-- Hidden Verification Form -->
        <form id="verify-form" method="POST" style="display: none;">
            <input type="hidden" name="verify_id" id="verify_id_input">
        </form>

        <!-- Hidden Delete Form -->
        <form id="delete-form" method="POST" style="display: none;">
            <input type="hidden" name="delete_id" id="delete_id_input">
        </form>

        <!-- Pending Residents Tab Content -->
        <div id="pending-tab" class="tab-content active">
            <table class="resident-table" id="pendingTable">
                <thead>
                    <tr>
                        <th>Resident ID</th>
                        <th>Full Name</th>
                        <th>Birthdate</th>
                        <th>Civil Status</th>
                        <th>Address</th>
                        <th>Contact Info</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                        <?php while($row = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['resident_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($row['civil_status']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td>
                                    <div><small><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></small></div>
                                    <div><small><i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['contact_no']); ?></small></div>
                                </td>
                                <td>
                                    <span class="status-pill <?php echo (strtolower($row['status']) === 'incomplete') ? 'status-incomplete' : 'status-pending'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <i class="fa fa-eye" title="View Details" onclick='viewResident(<?php echo json_encode($row); ?>)'></i>
                                    <?php if (!$is_official): ?>
                                        <i class="fa fa-edit" title="Edit Resident" onclick='openEditModal(<?php echo json_encode($row); ?>)'></i>
                                        <i class="fa fa-trash" title="Delete" onclick="confirmDelete('<?php echo htmlspecialchars($row['resident_id']); ?>')"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="no-data-row">
                            <td colspan="8" style="text-align: center;">No pending resident records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Verified Residents Tab Content -->
        <div id="verified-tab" class="tab-content">
            <table class="resident-table" id="verifiedTable">
                <thead>
                    <tr>
                        <th>Resident ID</th>
                        <th>Full Name</th>
                        <th>Birthdate</th>
                        <th>Civil Status</th>
                        <th>Address</th>
                        <th>Contact Info</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($verified_result && $verified_result->num_rows > 0): ?>
                        <?php while($row = $verified_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['resident_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($row['civil_status']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td>
                                    <div><small><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($row['email']); ?></small></div>
                                    <div><small><i class="fa fa-phone"></i> <?php echo htmlspecialchars($row['contact_no']); ?></small></div>
                                </td>
                                <td>
                                    <span class="status-pill status-active">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <i class="fa fa-eye" title="View Details" onclick='viewResident(<?php echo json_encode($row); ?>)'></i>
                                    <?php if (!$is_official): ?>
                                        <i class="fa fa-edit" title="Edit Resident" onclick='openEditModal(<?php echo json_encode($row); ?>)'></i>
                                        <i class="fa fa-trash" title="Delete" onclick="confirmDelete('<?php echo htmlspecialchars($row['resident_id']); ?>')"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="no-data-row">
                            <td colspan="8" style="text-align: center;">No verified resident records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD NEW RESIDENT MODAL (Admin Only) -->
    <?php if (!$is_official): ?>
    <div id="addResidentModal" class="modal">
        <div class="modal-content" style="max-width: 750px;">
            <span class="close-btn" onclick="closeModal('addResidentModal')">&times;</span>
            <div class="modal-header">
                <h3>Add New Resident Record</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_resident" value="1">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Resident ID (Auto-Generated)</label>
                        <input type="text" name="resident_id" value="<?php echo $auto_resident_id; ?>" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Profile Image</label>
                        <input type="file" name="profile_image" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Sex *</label>
                        <select name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Birthdate *</label>
                        <input type="date" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label>Civil Status *</label>
                        <select name="civil_status" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" name="religion">
                    </div>
                    <div class="form-group">
                        <label>Citizenship</label>
                        <input type="text" name="citizenship" value="Filipino">
                    </div>
                    <div class="form-group">
                        <label>Blood Type</label>
                        <input type="text" name="blood_type" placeholder="e.g., O+, A+">
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation">
                    </div>
                    <div class="form-group">
                        <label>Is Head of Family?</label>
                        <select name="is_head_of_family">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Number of Dependents</label>
                        <input type="number" name="number_of_dependents" value="0">
                    </div>
                    <div class="form-group">
                        <label>Spouse Name</label>
                        <input type="text" name="spouse_name">
                    </div>
                    <div class="form-group">
                        <label>Precinct Number</label>
                        <input type="text" name="precinct_number">
                    </div>
                    <div class="form-group">
                        <label>Voter Status</label>
                        <select name="voter_status">
                            <option value="Registered">Registered</option>
                            <option value="Not Registered">Not Registered</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Membership / Sector</label>
                        <input type="text" name="membership" placeholder="e.g., Senior Citizen, PWD">
                    </div>
                    <div class="form-group full-width">
                        <label>Complete Address *</label>
                        <input type="text" name="address" required placeholder="House No., Street, Barangay Malitbog">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Contact Number *</label>
                        <input type="text" name="contact_no" required placeholder="09XXXXXXXXX">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-add" style="background: #666;" onclick="closeModal('addResidentModal')">Cancel</button>
                    <button type="submit" class="btn-verify"><i class="fa fa-save"></i> Add Resident</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT RESIDENT MODAL (Admin Only) -->
    <div id="editResidentModal" class="modal">
        <div class="modal-content" style="max-width: 750px;">
            <span class="close-btn" onclick="closeModal('editResidentModal')">&times;</span>
            <div class="modal-header">
                <h3>Edit Resident Record</h3>
            </div>
            <form id="edit-form" method="POST">
                <input type="hidden" name="update_resident" value="1">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Resident ID</label>
                        <input type="text" id="edit_resident_id" name="resident_id" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select id="edit_status" name="status" required>
                            <option value="Verified">Verified</option>
                            <option value="Pending">Pending</option>
                            <option value="Incomplete">Incomplete</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" id="edit_middle_name" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Sex *</label>
                        <select id="edit_sex" name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Birthdate *</label>
                        <input type="date" id="edit_date_of_birth" name="date_of_birth" required>
                    </div>
                    <div class="form-group">
                        <label>Civil Status *</label>
                        <select id="edit_civil_status" name="civil_status" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" id="edit_religion" name="religion">
                    </div>
                    <div class="form-group">
                        <label>Citizenship</label>
                        <input type="text" id="edit_citizenship" name="citizenship">
                    </div>
                    <div class="form-group">
                        <label>Blood Type</label>
                        <input type="text" id="edit_blood_type" name="blood_type">
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" id="edit_occupation" name="occupation">
                    </div>
                    <div class="form-group">
                        <label>Is Head of Family?</label>
                        <select id="edit_is_head_of_family" name="is_head_of_family">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Number of Dependents</label>
                        <input type="number" id="edit_number_of_dependents" name="number_of_dependents" value="0">
                    </div>
                    <div class="form-group">
                        <label>Spouse Name</label>
                        <input type="text" id="edit_spouse_name" name="spouse_name">
                    </div>
                    <div class="form-group">
                        <label>Precinct Number</label>
                        <input type="text" id="edit_precinct_number" name="precinct_number">
                    </div>
                    <div class="form-group">
                        <label>Voter Status</label>
                        <select id="edit_voter_status" name="voter_status">
                            <option value="Registered">Registered</option>
                            <option value="Not Registered">Not Registered</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Membership / Sector</label>
                        <input type="text" id="edit_membership" name="membership">
                    </div>
                    <div class="form-group full-width">
                        <label>Complete Address *</label>
                        <input type="text" id="edit_address" name="address" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Contact Number *</label>
                        <input type="text" id="edit_contact_no" name="contact_no" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-add" style="background: #666;" onclick="closeModal('editResidentModal')">Cancel</button>
                    <button type="submit" class="btn-verify" id="submitEditBtn"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resident Details Modal (Accessible to Both, Action buttons hidden for officials) -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('detailsModal')">&times;</span>
            <div class="modal-header">
                <h3>Resident Complete Details</h3>
            </div>
            
            <div class="modal-profile-header">
                <img id="modal-img-profile" src="" alt="Profile Image" class="profile-img-preview">
                <div>
                    <h3 id="modal-name" style="margin: 0; color: var(--primary-blue);"></h3>
                    <small id="modal-id-badge" style="color: #666; font-weight: bold;"></small>
                </div>
            </div>

            <div class="detail-row"><span class="detail-label">Resident ID:</span><span id="modal-id"></span></div>
            <div class="detail-row"><span class="detail-label">First Name:</span><span id="modal-first-name"></span></div>
            <div class="detail-row"><span class="detail-label">Middle Name:</span><span id="modal-middle-name"></span></div>
            <div class="detail-row"><span class="detail-label">Last Name:</span><span id="modal-last-name"></span></div>
            <div class="detail-row"><span class="detail-label">Sex:</span><span id="modal-sex"></span></div>
            <div class="detail-row"><span class="detail-label">Birthdate:</span><span id="modal-dob"></span></div>
            <div class="detail-row"><span class="detail-label">Civil Status:</span><span id="modal-civil"></span></div>
            <div class="detail-row"><span class="detail-label">Religion:</span><span id="modal-religion"></span></div>
            <div class="detail-row"><span class="detail-label">Citizenship:</span><span id="modal-citizenship"></span></div>
            <div class="detail-row"><span class="detail-label">Blood Type:</span><span id="modal-blood-type"></span></div>
            <div class="detail-row"><span class="detail-label">Occupation:</span><span id="modal-occupation"></span></div>
            <div class="detail-row"><span class="detail-label">Is Head of Family:</span><span id="modal-head-family"></span></div>
            <div class="detail-row"><span class="detail-label">Number of Dependents:</span><span id="modal-dependents"></span></div>
            <div class="detail-row"><span class="detail-label">Spouse Name:</span><span id="modal-spouse"></span></div>
            <div class="detail-row"><span class="detail-label">Precinct Number:</span><span id="modal-precinct"></span></div>
            <div class="detail-row"><span class="detail-label">Voter Status:</span><span id="modal-voter-status"></span></div>
            <div class="detail-row"><span class="detail-label">Membership:</span><span id="modal-membership"></span></div>
            <div class="detail-row"><span class="detail-label">Address:</span><span id="modal-address"></span></div>
            <div class="detail-row"><span class="detail-label">Email:</span><span id="modal-email"></span></div>
            <div class="detail-row"><span class="detail-label">Phone:</span><span id="modal-phone"></span></div>
            <div class="detail-row"><span class="detail-label">Status:</span><span id="modal-status"></span></div>
            <div class="detail-row"><span class="detail-label">Remarks / Notice:</span><span id="modal-remarks" style="white-space: pre-line;"></span></div>
            <div class="detail-row"><span class="detail-label">Created At:</span><span id="modal-created-at"></span></div>

            <div class="modal-footer" id="modal-footer-actions"></div>
        </div>
    </div>

    <!-- Send Missing Requirements Formal Letter Modal -->
    <div id="notifyModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('notifyModal')">&times;</span>
            <div class="modal-header">
                <h3>Formal Notice - Missing Requirements</h3>
            </div>
            <form id="missing-form">
                <input type="hidden" name="missing_req_id" id="missing_id_input">
                <input type="hidden" name="resident_name" id="missing_name_input">
                <input type="hidden" name="resident_email" id="missing_email_input">
                
                <div class="letter-preview">
                    <div class="letter-header">Barangay Malitbog Administrative Office</div>
                    <div class="letter-sub">Official Notice of Incomplete Application Document</div>
                    Dear <strong id="display_notify_name">[Resident Name]</strong> (<span id="display_notify_email" style="color:#666;">[Resident Email]</span>),

                    We are writing to inform you that your resident verification application is currently on hold due to missing or incomplete documentation.

                    Please update your submission at your earliest convenience to complete the verification process.

                    Sincerely,
                    Barangay Malitbog Administration
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-add" style="background: #666;" onclick="closeModal('notifyModal')">Cancel</button>
                    <button type="submit" class="btn-notify" id="submitNotifyBtn"><i class="fa fa-paper-plane"></i> Issue Formal Notice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Application Result Modal -->
    <div id="resultModal" class="modal">
        <div class="modal-content-custom">
            <span class="modal-close-btn" onclick="closeModal('resultModal'); window.location.reload();">&times;</span>
            <div class="modal-header-custom">
                <h3 id="modalTitle">Application Notice</h3>
            </div>
            
            <div class="modal-body-content">
                <p id="modalMessageBody" style="margin: 0;">Processing your request...</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-modal-action" onclick="closeModal('resultModal'); window.location.reload();">OK</button>
            </div>
        </div>
    </div>

    <script>
        const isOfficial = <?php echo json_encode($is_official); ?>;

        function switchTab(tabName) {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));

            if (tabName === 'pending') {
                tabs[0].classList.add('active');
                document.getElementById('pending-tab').classList.add('active');
            } else {
                tabs[1].classList.add('active');
                document.getElementById('verified-tab').classList.add('active');
            }
            filterResidents();
        }

        function filterResidents() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const tables = [document.getElementById('pendingTable'), document.getElementById('verifiedTable')];

            tables.forEach(table => {
                const tr = table.getElementsByTagName('tr');
                for (let i = 1; i < tr.length; i++) {
                    if (tr[i].classList.contains('no-data-row')) continue;

                    const tdId = tr[i].getElementsByTagName('td')[0];
                    const tdName = tr[i].getElementsByTagName('td')[1];
                    const tdAddress = tr[i].getElementsByTagName('td')[4];

                    if (tdId || tdName || tdAddress) {
                        const idValue = tdId ? (tdId.textContent || tdId.innerText) : '';
                        const nameValue = tdName ? (tdName.textContent || tdName.innerText) : '';
                        const addressValue = tdAddress ? (tdAddress.textContent || tdAddress.innerText) : '';

                        if (
                            idValue.toUpperCase().indexOf(filter) > -1 ||
                            nameValue.toUpperCase().indexOf(filter) > -1 ||
                            addressValue.toUpperCase().indexOf(filter) > -1
                        ) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            });
        }

        function openAddModal() {
            if (isOfficial) return;
            document.getElementById('addResidentModal').style.display = 'flex';
        }

        function openEditModal(resident) {
            if (isOfficial) return;
            document.getElementById('edit_resident_id').value = resident.resident_id || '';
            document.getElementById('edit_status').value = resident.status || 'Verified';
            document.getElementById('edit_first_name').value = resident.first_name || '';
            document.getElementById('edit_middle_name').value = resident.middle_name || '';
            document.getElementById('edit_last_name').value = resident.last_name || '';
            document.getElementById('edit_sex').value = resident.sex || '';
            document.getElementById('edit_date_of_birth').value = resident.date_of_birth || '';
            document.getElementById('edit_civil_status').value = resident.civil_status || 'Single';
            document.getElementById('edit_religion').value = resident.religion || '';
            document.getElementById('edit_citizenship').value = resident.citizenship || '';
            document.getElementById('edit_blood_type').value = resident.blood_type || '';
            document.getElementById('edit_occupation').value = resident.occupation || '';
            document.getElementById('edit_is_head_of_family').value = resident.is_head_of_family || '0';
            document.getElementById('edit_number_of_dependents').value = resident.number_of_dependents || '0';
            document.getElementById('edit_spouse_name').value = resident.spouse_name || '';
            document.getElementById('edit_precinct_number').value = resident.precinct_number || '';
            document.getElementById('edit_voter_status').value = resident.voter_status || 'Registered';
            document.getElementById('edit_membership').value = resident.membership || '';
            document.getElementById('edit_address').value = resident.address || '';
            document.getElementById('edit_email').value = resident.email || '';
            document.getElementById('edit_contact_no').value = resident.contact_no || '';

            document.getElementById('editResidentModal').style.display = 'flex';
        }

        function viewResident(resident) {
            document.getElementById('modal-id').textContent = resident.resident_id || 'N/A';
            document.getElementById('modal-id-badge').textContent = 'ID: ' + (resident.resident_id || 'N/A');
            document.getElementById('modal-name').textContent = resident.full_name || 'N/A';
            
            document.getElementById('modal-first-name').textContent = resident.first_name || 'N/A';
            document.getElementById('modal-middle-name').textContent = resident.middle_name || 'N/A';
            document.getElementById('modal-last-name').textContent = resident.last_name || 'N/A';
            document.getElementById('modal-sex').textContent = resident.sex || 'N/A';
            document.getElementById('modal-dob').textContent = resident.date_of_birth || 'N/A';
            document.getElementById('modal-civil').textContent = resident.civil_status || 'N/A';
            document.getElementById('modal-religion').textContent = resident.religion || 'N/A';
            document.getElementById('modal-citizenship').textContent = resident.citizenship || 'N/A';
            document.getElementById('modal-blood-type').textContent = resident.blood_type || 'N/A';
            document.getElementById('modal-occupation').textContent = resident.occupation || 'N/A';
            
            document.getElementById('modal-head-family').textContent = (resident.is_head_of_family == 1) ? 'Yes' : 'No';
            document.getElementById('modal-dependents').textContent = resident.number_of_dependents !== null ? resident.number_of_dependents : '0';
            document.getElementById('modal-spouse').textContent = resident.spouse_name || 'N/A';
            
            document.getElementById('modal-precinct').textContent = resident.precinct_number || 'N/A';
            document.getElementById('modal-voter-status').textContent = resident.voter_status || 'N/A';
            document.getElementById('modal-membership').textContent = resident.membership || 'N/A';
            
            document.getElementById('modal-address').textContent = resident.address || 'N/A';
            document.getElementById('modal-email').textContent = resident.email || 'N/A';
            document.getElementById('modal-phone').textContent = resident.contact_no || 'N/A';
            document.getElementById('modal-status').textContent = resident.status || 'N/A';
            
            let remarksText = resident.remarks || 'None';
            document.getElementById('modal-remarks').textContent = remarksText;
            
            document.getElementById('modal-created-at').textContent = resident.created_at || 'N/A';

            const uploadPath = 'uploads/'; 
            const profileImgElem = document.getElementById('modal-img-profile');
            if (resident.profile_image) {
                profileImgElem.src = uploadPath + resident.profile_image;
            } else {
                profileImgElem.src = uploadPath + 'default.png';
            }

            const modalFooter = document.getElementById('modal-footer-actions');
            // Show action buttons inside the view modal ONLY if NOT official and status is not verified
            if (!isOfficial && resident.status && resident.status.toLowerCase() !== 'verified') {
                const safeName = (resident.full_name || '').replace(/'/g, "\\'");
                const safeEmail = (resident.email || '').replace(/'/g, "\\'");
                
                modalFooter.innerHTML = `
                    <button class="btn-notify" onclick="openNotifyModal('${resident.resident_id}', '${safeName}', '${safeEmail}')">
                        <i class="fa fa-exclamation-triangle"></i> Notify Missing Requirements
                    </button>
                    <button class="btn-verify" onclick="confirmVerify('${resident.resident_id}')">
                        <i class="fa fa-check-circle"></i> Verify Resident Record
                    </button>
                `;
            } else {
                modalFooter.innerHTML = ``;
            }

            document.getElementById('detailsModal').style.display = 'flex';
        }

        function openNotifyModal(residentId, residentName, residentEmail) {
            if (isOfficial) return;
            closeModal('detailsModal');
            document.getElementById('missing_id_input').value = residentId;
            document.getElementById('missing_name_input').value = residentName;
            document.getElementById('missing_email_input').value = residentEmail;
            
            document.getElementById('display_notify_name').textContent = residentName;
            document.getElementById('display_notify_email').textContent = residentEmail;
            
            document.getElementById('notifyModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            const el = document.getElementById(modalId);
            if (el) el.style.display = 'none';
        }

        function confirmVerify(residentId) {
            if (isOfficial) return;
            if (confirm("Are you sure you want to verify this resident?")) {
                document.getElementById('verify_id_input').value = residentId;
                document.getElementById('verify-form').submit();
            }
        }

        function confirmDelete(residentId) {
            if (isOfficial) return;
            if (confirm("Are you sure you want to permanently delete this resident record from the database?")) {
                document.getElementById('delete_id_input').value = residentId;
                document.getElementById('delete-form').submit();
            }
        }

        // Handle Edit Form Submission via AJAX (Admin Only)
        const editForm = document.getElementById('edit-form');
        if (editForm) {
            const submitEditBtn = document.getElementById('submitEditBtn');
            const resultModal = document.getElementById('resultModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessageBody = document.getElementById('modalMessageBody');

            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (isOfficial) return;

                const formData = new FormData(editForm);
                submitEditBtn.disabled = true;
                submitEditBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    let serverMsg = data.trim();
                    closeModal('editResidentModal');

                    if (serverMsg === 'success') {
                        modalTitle.textContent = "Success: Record Updated";
                        modalMessageBody.textContent = "Resident record successfully updated!";
                    } else {
                        modalTitle.textContent = "Notice: Error Occurred";
                        modalMessageBody.textContent = serverMsg || 'Error updating resident. Please try again.';
                    }

                    submitEditBtn.disabled = false;
                    submitEditBtn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
                    resultModal.style.display = 'flex';
                })
                .catch(error => {
                    closeModal('editResidentModal');
                    modalTitle.textContent = "System Error";
                    modalMessageBody.textContent = 'A network error occurred. Please check your connection and try again.';
                    console.error('Error:', error);

                    submitEditBtn.disabled = false;
                    submitEditBtn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
                    resultModal.style.display = 'flex';
                });
            });
        }

        // Handle Missing Requirements Form Submission via AJAX
        const missingForm = document.getElementById('missing-form');
        if (missingForm) {
            const submitNotifyBtn = document.getElementById('submitNotifyBtn');
            const resultModal = document.getElementById('resultModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessageBody = document.getElementById('modalMessageBody');

            missingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (isOfficial) return;

                const formData = new FormData(missingForm);
                submitNotifyBtn.disabled = true;
                submitNotifyBtn.textContent = "Sending Notice...";

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    let serverMsg = data.trim();
                    closeModal('notifyModal');

                    if (serverMsg === 'success') {
                        modalTitle.textContent = "Success: Notice Sent";
                        modalMessageBody.textContent = "Letter has been successfully sent and saved to resident remarks.";
                    } else {
                        modalTitle.textContent = "Notice: Error Occurred";
                        modalMessageBody.textContent = serverMsg || 'Error notifying resident. Please try again.';
                    }

                    submitNotifyBtn.disabled = false;
                    submitNotifyBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Issue Formal Notice';
                    resultModal.style.display = 'flex';
                })
                .catch(error => {
                    closeModal('notifyModal');
                    modalTitle.textContent = "System Error";
                    modalMessageBody.textContent = 'A network error occurred. Please check your connection and try again.';
                    console.error('Error:', error);

                    submitNotifyBtn.disabled = false;
                    submitNotifyBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Issue Formal Notice';
                    resultModal.style.display = 'flex';
                });
            });
        }

        window.onclick = function(event) {
            const addModal = document.getElementById('addResidentModal');
            const editModal = document.getElementById('editResidentModal');
            const detailsModal = document.getElementById('detailsModal');
            const notifyModal = document.getElementById('notifyModal');
            const resultModal = document.getElementById('resultModal');
            if (addModal && event.target === addModal) addModal.style.display = 'none';
            if (editModal && event.target === editModal) editModal.style.display = 'none';
            if (detailsModal && event.target === detailsModal) detailsModal.style.display = 'none';
            if (notifyModal && event.target === notifyModal) notifyModal.style.display = 'none';
            if (resultModal && event.target === resultModal) {
                resultModal.style.display = 'none';
                window.location.reload();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>