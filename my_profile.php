<?php
include 'db_connect.php'; 
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['resident_id'])) {
    header("Location: resident_portal.php");
    exit();
}

if (!isset($_SESSION['deleted_messages'])) { $_SESSION['deleted_messages'] = []; }
if (!isset($_SESSION['read_messages'])) { $_SESSION['read_messages'] = []; }

// Handle AJAX request to delete a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_msg_hash'])) {
    $msg_hash = $_POST['delete_msg_hash'];
    if (!in_array($msg_hash, $_SESSION['deleted_messages'])) {
        $_SESSION['deleted_messages'][] = $msg_hash;
    }
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle AJAX request to mark a message as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_hash'])) {
    $msg_hash = $_POST['mark_read_hash'];
    if (!in_array($msg_hash, $_SESSION['read_messages'])) {
        $_SESSION['read_messages'][] = $msg_hash;
    }
    echo json_encode(['status' => 'success']);
    exit();
}

$resident_id  = $_SESSION['resident_id'];
$resident_email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Resident';
$status_error = '';
$status_success = '';

// 3. Handle Form Submissions (Photo and Profile Details)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_msg_hash']) && !isset($_POST['mark_read_hash'])) {
    
    // A. Handle Profile Details Update
    if (isset($_POST['update_profile'])) {
        $last_name        = trim($_POST['last_name']);
        $first_name       = trim($_POST['first_name']);
        $middle_name      = trim($_POST['middle_name']);
        $date_of_birth    = trim($_POST['birthdate']);
        $civil_status     = trim($_POST['civil_status']);
        $address          = trim($_POST['address']);
        $email            = trim($_POST['email']);
        $phone            = trim($_POST['phone']);
        
        $sex              = trim($_POST['sex']);
        $occupation       = trim($_POST['occupation']);
        $membership       = trim($_POST['membership']);
        $precinct_number  = trim($_POST['precinct_number']);
        $religion         = trim($_POST['religion']);
        $citizenship      = trim($_POST['citizenship']);
        $voter_status     = trim($_POST['voter_status']);
        $blood_type       = trim($_POST['blood_type']);
        $is_head_of_family = trim($_POST['is_head_of_family']);
        $number_of_dependents = intval($_POST['number_of_dependents']); 
        $spouse_name      = trim($_POST['spouse_name']); 

        $full_name = trim($first_name . ' ' . (!empty($middle_name) ? $middle_name . ' ' : '') . $last_name);

        $stmt_update = $conn->prepare("UPDATE residents SET full_name = ?, last_name = ?, first_name = ?, middle_name = ?, date_of_birth = ?, civil_status = ?, address = ?, email = ?, contact_no = ?, sex = ?, occupation = ?, membership = ?, precinct_number = ?, religion = ?, citizenship = ?, voter_status = ?, blood_type = ?, is_head_of_family = ?, number_of_dependents = ?, spouse_name = ? WHERE resident_id = ?");
        $stmt_update->bind_param("sssssssssssssssssssss", $full_name, $last_name, $first_name, $middle_name, $date_of_birth, $civil_status, $address, $email, $phone, $sex, $occupation, $membership, $precinct_number, $religion, $citizenship, $voter_status, $blood_type, $is_head_of_family, $number_of_dependents, $spouse_name, $resident_id);

        if ($stmt_update->execute()) {
            $status_success = "Profile updated successfully!";
        } else {
            $status_error = "Failed to update profile details: " . $stmt_update->error;
        }
        $stmt_update->close();
    }

    // B. Handle Profile Photo Upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 2 * 1024 * 1024; // 2MB Limit

            if (!in_array($file['type'], $allowed_types)) {
                $status_error = "Invalid image type. Only JPG, PNG, and WEBP allowed.";
            } elseif ($file['size'] > $max_size) {
                $status_error = "Image size exceeds 2MB limit.";
            } else {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'profile_' . $resident_id . '_' . time() . '.' . $extension;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $stmt_img = $conn->prepare("UPDATE residents SET profile_image = ? WHERE resident_id = ?");
                    $stmt_img->bind_param("ss", $new_filename, $resident_id);
                    $stmt_img->execute();
                    $stmt_img->close();
                    if (empty($status_error)) {
                        $status_success = "Profile picture updated successfully!";
                    }
                } else {
                    $status_error = "Failed to move uploaded file.";
                }
            }
        } else {
            $status_error = "Error uploading image file.";
        }
    }
}

// 4. Fetch logged-in resident's profile data
$stmt = $conn->prepare("SELECT * FROM residents WHERE resident_id = ?");
$stmt->bind_param("s", $resident_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User profile not found.";
    exit();
}
$stmt->close();

// 5. Fetch Admin Messages for Inbox Drawer
$admin_messages = [];
$unread_count = 0;

if ($resident_id || $resident_email) {
    $msg_query = "
        SELECT 'residency' AS src, claim_message AS remarks, status, created_at FROM cert_of_residency WHERE (resident_id = ? OR email = ?) AND claim_message IS NOT NULL AND claim_message != ''
        UNION ALL
        SELECT 'indigency' AS src, claim_message AS remarks, status, created_at FROM cert_of_indigency WHERE (resident_id = ? OR email = ?) AND claim_message IS NOT NULL AND claim_message != ''
        UNION ALL
        SELECT 'clearance' AS src, claim_message AS remarks, status, created_at FROM barangay_clearance WHERE (resident_id = ? OR email = ?) AND claim_message IS NOT NULL AND claim_message != ''
        UNION ALL
        SELECT 'resident' AS src, remarks, 'General' AS status, created_at FROM residents WHERE (resident_id = ? OR email = ?) AND remarks IS NOT NULL AND remarks != ''
        UNION ALL
        SELECT 'appointment' AS src, remarks, status, created_at FROM appointments WHERE resident_id = ? AND remarks IS NOT NULL AND remarks != ''
        ORDER BY created_at DESC
    ";
    
    if ($stmt_msg = $conn->prepare($msg_query)) {
        $stmt_msg->bind_param("sssssssss", $resident_id, $resident_email, $resident_id, $resident_email, $resident_id, $resident_email, $resident_id, $resident_email, $resident_id);
        $stmt_msg->execute();
        $res_msg = $stmt_msg->get_result();
        while ($row_msg = $res_msg->fetch_assoc()) {
            $msg_text = isset($row_msg['remarks']) ? $row_msg['remarks'] : '';
            $msg_time = isset($row_msg['created_at']) ? $row_msg['created_at'] : 'Recently';
            $msg_source = isset($row_msg['src']) ? $row_msg['src'] : 'general';
            $msg_hash = md5($msg_source . $msg_text . $msg_time);
            
            if (!in_array($msg_hash, $_SESSION['deleted_messages'])) {
                $row_msg['msg_hash'] = $msg_hash;
                $row_msg['is_read'] = in_array($msg_hash, $_SESSION['read_messages']);
                
                if (!$row_msg['is_read']) {
                    $unread_count++;
                }
                
                $admin_messages[] = $row_msg;
            }
        }
        $stmt_msg->close();
    }
}

$conn->close();

$age = 'N/A';
if (!empty($user['date_of_birth'])) {
    $birthDate = new DateTime($user['date_of_birth']);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
}

$current_status = strtolower($user['status'] ?? 'pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Barangay Malitbog[cite: 1]</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="resident-dashboard.css">
    <style>
        :root {
            --primary-blue: #000066;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
        }

        html, body {
            min-height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            -webkit-text-size-adjust: 100%;
        }

        .top-bar {
            background: #1a1a8c;
            color: white;
            padding: 5px 10%;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }
        .top-bar div:first-child { display: flex; gap: 15px; }
        .social-icons { display: flex; gap: 20px; }
        .profile-section i { margin-right: 5px; }

        .main-nav {
            background: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1100;
        }

        .logo-section { display: flex; align-items: center; gap: 15px; }
        .logo-section img { height: 50px; width: 50px; border-radius: 50%; }
        .logo-text h2 { margin: 0; color: var(--primary-blue); font-size: 1.1rem; letter-spacing: 1px; }
        .logo-text small { font-size: 0.7rem; color: #555; display: block; }

        .rd-back-link {
            font-size: 1.2rem;
            color: var(--primary-blue);
            margin-right: 5px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .rd-back-link:hover { color: var(--accent-gold); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 25px;
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
        .rd-menu-symbol-trigger.custom-profile-style span,
        .rd-nav-menu-bar-trigger span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.75rem;
            text-transform: capitalize;
            letter-spacing: 0.5px;
        }

        .rd-nav-item i, 
        .rd-message-symbol-trigger i, 
        .rd-menu-symbol-trigger i,
        .rd-nav-menu-bar-trigger i {
            font-size: 1.1rem;
        }

        .rd-nav-item:hover,
        .rd-message-symbol-trigger:hover,
        .rd-menu-symbol-trigger:hover,
        .rd-nav-menu-bar-trigger:hover,
        .rd-menu-symbol-trigger.active {
            color: var(--accent-gold);
        }

        .rd-nav-link-dropdown {
            display: inline-flex;
            align-items: center;
            position: relative;
        }

        .rd-message-symbol-trigger,
        .rd-nav-menu-bar-trigger {
            cursor: pointer;
            position: relative;
        }

        .rd-msg-badge { background-color: #ff3333; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.6rem; font-weight: bold; }
        .rd-msg-badge-top { position: absolute; top: -4px; right: -8px; }

        /* Notification Pulse & Glow Effect */
        @keyframes pulseGlow {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 51, 51, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(255, 51, 51, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 51, 51, 0); }
        }
        .has-unread-pulse {
            animation: pulseGlow 1.8s infinite;
            border-radius: 50%;
        }

        /* Slide-in Menu Drawer Styles */
        .menu-slide-drawer {
            display: none;
            position: fixed;
            z-index: 1300;
            right: 0;
            top: 0;
            width: 100%;
            max-width: 380px;
            height: 100%;
            background-color: white;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            flex-direction: column;
            animation: slideInMenu 0.3s ease-out;
        }

        @keyframes slideInMenu {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        .menu-drawer-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-drawer-header h3 { margin: 0; font-size: 1.1rem; }

        .menu-drawer-body {
            padding: 15px;
            overflow-y: auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu-drawer-body a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            text-decoration: none;
            color: #333;
            font-size: 0.95rem;
            font-weight: 500;
            background: #f9f9f9;
            border-left: 4px solid var(--accent-gold);
            border-radius: 4px;
            transition: transform 0.15s ease, background 0.2s ease, color 0.2s ease;
        }

        .menu-drawer-body a:hover {
            transform: translateY(-2px);
            background: #f0f0f0;
            color: var(--primary-blue);
        }

        .menu-drawer-body a i {
            font-size: 1.1rem;
            color: var(--primary-blue);
            width: 20px;
            text-align: center;
        }

        /* Modal Styles for System Notice */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 2000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            justify-content: center; 
            align-items: center; 
            overflow-y: auto; 
            padding: 15px;
            box-sizing: border-box;
        }
        .modal-content {
            background-color: #fff; 
            padding: 20px; 
            border-radius: 12px; 
            width: 95%; 
            max-width: 360px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); 
            position: relative; 
            box-sizing: border-box;
        }
        .close-btn { 
            background: none; 
            border: none; 
            font-size: 1.5rem; 
            font-weight: bold; 
            color: #aaa; 
            cursor: pointer; 
        }
        .close-btn:hover { color: #333; }
        .modal-header { border-bottom: 2px solid #eee; padding-bottom: 8px; margin-bottom: 15px; }
        .modal-header h3 { margin: 0; color: var(--primary-blue); font-size: 1.15rem; }
        .modal-body-content { padding: 5px 0; font-size: 0.9rem; color: #333; line-height: 1.4; }
        .modal-footer {
            margin-top: 15px; text-align: right; padding-top: 12px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px;
        }
        .btn-modal-action { background: var(--primary-blue); color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-action:hover { background: #1a1a8c; }
        .btn-modal-cancel { background: #ccc; color: #333; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-cancel:hover { background: #b3b3b3; }

        /* Message Drawer Modal Styling */
        .message-modal {
            display: none;
            position: fixed;
            z-index: 1300;
            right: 0;
            top: 0;
            width: 100%;
            max-width: 380px;
            height: 100%;
            background-color: white;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            flex-direction: column;
            animation: slideInMsg 0.3s ease-out;
        }

        @keyframes slideInMsg {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }

        .message-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-header h3 { margin: 0; font-size: 1.1rem; }

        .message-body {
            padding: 15px;
            overflow-y: auto;
            flex-grow: 1;
        }

        .admin-msg-card {
            background: #f9f9f9;
            border-left: 4px solid var(--accent-gold);
            padding: 12px 15px;
            margin-bottom: 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.2s ease;
            position: relative;
        }

        .admin-msg-card:hover { transform: translateY(-2px); background: #f0f0f0; }
        .admin-msg-card.unread { background: #eef2ff; border-left-color: var(--primary-blue); }
        .admin-msg-card.read { background: #f9f9f9; border-left-color: #ccc; opacity: 0.85; }

        .msg-meta {
            font-size: 0.75rem;
            color: #777;
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            padding-right: 25px;
        }

        .msg-sender { font-weight: bold; color: var(--primary-blue); }

        .msg-content {
            font-size: 0.9rem;
            color: #333;
            margin: 0;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Three-Dot Menu Styles */
        .msg-options-container {
            position: absolute;
            top: 10px;
            right: 12px;
        }

        .msg-options-btn {
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            font-size: 1rem;
            padding: 2px 6px;
            border-radius: 4px;
            display: none;
        }

        .admin-msg-card.read .msg-options-btn {
            display: block;
        }

        .msg-options-btn:hover {
            background: rgba(0,0,0,0.08);
            color: #000;
        }

        .msg-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 25px;
            background: white;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 6px;
            z-index: 20;
            min-width: 100px;
        }

        .msg-dropdown-menu.show {
            display: block;
        }

        .msg-dropdown-menu button {
            background: none;
            border: none;
            width: 100%;
            padding: 8px 12px;
            text-align: left;
            font-size: 0.85rem;
            color: #ff3333;
            cursor: pointer;
            border-radius: 6px;
        }

        .msg-dropdown-menu button:hover {
            background: #fff0f0;
        }

        /* Reading Popup Modal */
        .read-modal {
            display: none;
            position: fixed;
            z-index: 1400;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .read-modal-header {
            background: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .read-modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .read-modal-body p { margin: 0; line-height: 1.6; color: #333; white-space: pre-line; }

        .rd-global-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1050;
        }

        /* Standard Profile Styles */
        .hero-banner {
            background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 40px 5%; 
            color: white;
            text-align: center;
        }

        .welcome-msg h1 { margin: 0; font-size: 1.8rem; }
        .welcome-msg span { color: var(--accent-gold); }
        .welcome-msg p { font-size: 0.95rem; margin-top: 8px; }

        .container {
            max-width: 900px; /* Adjusted max-width slightly for a cleaner centered vertical view */
            margin: -20px auto 40px;
            padding: 0 15px;
            position: relative;
            z-index: 10;
        }

        /* Unified Container Housing both Sections */
        .profile-unified-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }

        /* CHANGED: Switched to a vertical stacked layout and centered elements */
        .profile-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        /* Make individual sections take full width cleanly inside the vertical flow */
        .profile-wrapper > div {
            width: 100%;
            max-width: 800px;
        }

        .scrollable-info-container {
            max-height: 335px;
            overflow-y: auto;
            padding-right: 5px;
            margin-bottom: 20px;
        }

        .scrollable-info-container::-webkit-scrollbar { width: 6px; }
        .scrollable-info-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .scrollable-info-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        .scrollable-info-container::-webkit-scrollbar-thumb:hover { background: var(--primary-blue); }

        .profile-img-container {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 15px;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--white);
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #ccc;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .btn-edit-photo {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background 0.2s;
        }

        .btn-edit-photo:hover { background: #000099; }

        .section-title {
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            border-left: 5px solid var(--accent-gold);
            padding-left: 12px;
            font-size: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .info-group { display: flex; flex-direction: column; }

        .info-label {
            font-size: 0.75rem;
            color: #777;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-input:disabled {
            background-color: #f9f9f9;
            border-color: #e5e5e5;
            color: #333;
            cursor: default;
        }

        .form-input:focus { outline: none; border-color: var(--primary-blue); }

        .btn-track {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            font-size: 0.9rem;
            width: 100%;
        }
        @media(min-width: 769px) {
            .btn-track { width: auto; }
        }
        .btn-track:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        .alert-error {
            color: #d9534f;
            background-color: #f9dedc;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .status-badge {
            display: inline-block;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .status-verified { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; }
        .status-pending { color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; }

        @media (max-width: 768px) {
            .top-bar { display: none; }
            .main-nav { padding: 8px 12px; }
            .logo-section img { height: 40px; width: 40px; }
            .logo-text h2 { font-size: 0.95rem; }
            .logo-text small { font-size: 0.6rem; }
            
            .nav-links { gap: 15px; }
            .rd-nav-item span,
            .rd-menu-symbol-trigger span,
            .rd-nav-menu-bar-trigger span,
            .rd-message-symbol-trigger span {
                display: none;
            }

            .menu-slide-drawer, 
            .message-modal {
                max-width: 50% !important;
                width: 50% !important;
            }

            .hero-banner { padding: 30px 15px; }
            .welcome-msg h1 { font-size: 1.4rem; }
            .welcome-msg p { font-size: 0.8rem; }

            .container { margin-top: -15px; padding: 0 10px; }
            .profile-unified-container { padding: 15px; border-radius: 12px; }
            .profile-wrapper { display: flex; flex-direction: column; align-items: center; gap: 20px; }
            .info-grid { grid-template-columns: 1fr; gap: 10px; }
            .info-group[style*="grid-column"] { grid-column: span 1 !important; }
            
            .scrollable-info-container { max-height: none; overflow-y: visible; padding-right: 0; }
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
            <a href="barangay_profile.php" style="text-decoration: none; color: inherit;">
                <span style="margin-right: 20px;"></span>
            </a>
        </div>
    </div>

    <!-- Navigation Bar matching Resident Dashboard -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="resident_dashboard.php" class="rd-back-link" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>PROVINCE OF ORIENTAL MINDORO</small>
            </div>
        </div>
        <div class="nav-links">
            <a href="resident_dashboard.php" class="rd-nav-item custom-home-style" title="Home">
                <i class="fas fa-home"></i>
                <span>home</span>
            </a>

            <div class="rd-nav-link-dropdown">
                <a href="javascript:void(0);" class="rd-message-symbol-trigger <?php echo ($unread_count > 0) ? 'has-unread-pulse' : ''; ?>" onclick="toggleMessages()" title="Inbox / Messages">
                    <i class="fas fa-envelope"></i>
                    <span>Inbox</span>
                    <?php if ($unread_count > 0): ?>
                        <span class="rd-msg-badge rd-msg-badge-top" id="msgBadgeTop"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="rd-nav-link-dropdown">
                <a href="my_profile.php" class="rd-menu-symbol-trigger custom-profile-style active" title="My Profile">
                    <i class="fas fa-user-circle"></i>
                    <span>profile</span>
                </a>
            </div>

            <!-- Slide-in Menu Bar Trigger -->
            <div class="rd-nav-link-dropdown">
                <a href="javascript:void(0);" class="rd-nav-menu-bar-trigger" onclick="toggleNavBarMenu()" title="Menu Options">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Global Overlay & Slide-in Drawers -->
    <div class="rd-global-overlay" id="globalOverlay" onclick="closeAllModals()"></div>

    <!-- Slide-in Menu Drawer -->
<div class="menu-slide-drawer" id="menuSlideDrawer">
    <div class="menu-drawer-header">
       <h3><i class="fas fa-user-circle"></i> <span id="fullName"><?php echo htmlspecialchars($full_name ?? 'full_name'); ?></span></h3>
        <button type="button" class="close-btn" onclick="toggleNavBarMenu()">&times;</button>
    </div>
    <div class="menu-drawer-body">
        <a href="barangay_profile.php"><i class="fas fa-university"></i> Barangay Info</a>
        <a href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
        <a href="javascript:void(0);" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

    <!-- Message Inbox Drawer -->
    <div class="message-modal" id="messageDrawer">
        <div class="message-header">
            <h3><i class="fas fa-comments"></i> Admin Messages</h3>
            <button type="button" class="close-btn" onclick="toggleMessages()">&times;</button>
        </div>
        <div class="message-body">
            <?php if (!empty($admin_messages)): ?>
                <?php foreach ($admin_messages as $msg): ?>
                    <?php 
                        $msg_text = isset($msg['remarks']) ? $msg['remarks'] : '';
                        $msg_time = isset($msg['created_at']) ? $msg['created_at'] : 'Recently';
                        $msg_source = isset($msg['src']) ? $msg['src'] : 'general';
                        $msg_hash = isset($msg['msg_hash']) ? $msg['msg_hash'] : md5($msg_source . $msg_text . $msg_time);
                        $is_read = $msg['is_read'];
                    ?>
                    <div class="admin-msg-card <?php echo $is_read ? 'read' : 'unread'; ?>" onclick='readMessage(this, <?php echo json_encode($msg_text); ?>, <?php echo json_encode($msg_time); ?>, "<?php echo $msg_hash; ?>")'>
                        <div class="msg-meta">
                            <span class="msg-sender"><i class="fas fa-user-shield"></i> Barangay Admin</span>
                            <span><?php echo htmlspecialchars($msg_time); ?></span>
                        </div>
                        <p class="msg-content"><?php echo htmlspecialchars($msg_text); ?></p>
                        
                        <div class="msg-options-container" onclick="event.stopPropagation();">
                            <button type="button" class="msg-options-btn" onclick="toggleMsgOptions(event, this)" title="Options">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="msg-dropdown-menu">
                                <button type="button" onclick="deleteMessage(event, '<?php echo $msg_hash; ?>', this.closest('.admin-msg-card'))">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: #888; margin-top: 20px;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; color: #ccc;"></i>
                    <p>No messages from Admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Read Message Details Modal -->
    <div class="read-modal" id="readMessageModal">
        <div class="read-modal-header">
            <h3 style="margin: 0; font-size: 1rem;"><i class="fas fa-envelope-open-text"></i> Admin Message Details</h3>
            <button type="button" class="close-btn" onclick="closeReadModal()">&times;</button>
        </div>
        <div class="read-modal-body">
            <div class="msg-meta" style="margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                <span class="msg-sender"><i class="fas fa-user-shield"></i> Barangay Admin</span>
                <span id="readMsgTime">Recently</span>
            </div>
            <p id="readMsgContent"></p>
        </div>
    </div>

    <!-- System Notice Modal for Logout -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn" onclick="document.getElementById('logoutModal').style.display='none'" style="position: absolute; right: 15px; top: 12px;">&times;</span>
            <div class="modal-header">
                <h3>System Notice</h3>
            </div>
            <div class="modal-body-content">
                <p style="margin: 0;">Are you sure you want to leave or log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" id="cancelLogoutBtn" onclick="document.getElementById('logoutModal').style.display='none'">Cancel</button>
                <a href="index.php" style="text-decoration: none;">
                    <button type="button" class="btn-modal-action">Yes</button>
                </a>
            </div>
        </div>
    </div>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>RESIDENT <span>PROFILE</span></h1>
            <p>PROVINCE OF ORIENTAL MINDORO - BARANGAY MALITBOG</p>
        </div>
    </header>

    <div class="container">
        <form id="profileForm" action="" method="POST" enctype="multipart/form-data">
            
            <?php if (!empty($status_error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($status_error); ?></div>
            <?php endif; ?>

            <?php if (!empty($status_success)): ?>
                <div class="alert-success"><?php echo htmlspecialchars($status_success); ?></div>
            <?php endif; ?>

            <!-- Unified Single Container -->
            <div class="profile-unified-container">
                <div class="profile-wrapper">
                    <!-- Top/Center Section: Profile Photo & Status Card Section -->
                    <div style="text-align: center;">
                        <div class="profile-img-container">
                            <div class="profile-img">
                                <?php 
                                $image_path = 'uploads/' . ($user['profile_image'] ?? '');
                                if (!empty($user['profile_image']) && file_exists($image_path)): 
                                ?>
                                    <img id="imagePreview" src="<?php echo htmlspecialchars($image_path); ?>" alt="Profile Picture" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="fa fa-user" id="defaultIcon"></i>
                                    <img id="imagePreview" src="#" alt="Profile Picture" style="width:100%; height:100%; object-fit:cover; display:none;">
                                <?php endif; ?>
                            </div>
                            
                            <label for="profile_photo" class="btn-edit-photo" title="Upload Photo">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="profile_photo" id="profile_photo" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="previewFile()">
                        </div>

                        <h2 style="color: var(--primary-blue); margin-bottom: 5px; font-size: 1.2rem;">
                            <?php 
                                $full_display_name = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                echo htmlspecialchars(!empty($full_display_name) ? $full_display_name : 'N/A'); 
                            ?>
                        </h2>
                        <p style="color: #666; font-size: 0.85rem;"><i class="fa fa-id-card"></i> Resident ID: <?php echo htmlspecialchars($user['resident_id']); ?></p>
                        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px auto; width: 60%;">
                        
                        <div style="text-align: center;">
                            <small style="color: #999;">Account Status</small>
                            <div style="margin-top: 5px;">
                                <?php if ($current_status === 'verified resident' || $current_status === 'verified'): ?>
                                    <div class="status-badge status-verified">
                                        <i class="fa fa-check-circle"></i> Verified Resident
                                    </div>
                                <?php else: ?>
                                    <div class="status-badge status-pending">
                                        <i class="fa fa-clock"></i> Pending Verification
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Section: Personal Information Form Section -->
                    <div>
                        <h3 class="section-title">Personal Information</h3>
                        
                        <div class="scrollable-info-container">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label class="info-label" for="last_name">Last Name</label>
                                    <input type="text" class="form-input profile-field" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required disabled>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="first_name">First Name</label>
                                    <input type="text" class="form-input profile-field" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required disabled>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="middle_name">Middle Name</label>
                                    <input type="text" class="form-input profile-field" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>" disabled>
                                </div>
                                 <div class="info-group">
                                    <label class="info-label" for="email">Email</label>
                                    <input type="email" class="form-input profile-field" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required disabled><br>
                                </div>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="address">Address</label>
                                    <input type="text" class="form-input profile-field" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="birthdate">Date of Birth</label>
                                    <input type="date" class="form-input profile-field" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>" required disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="age">Age</label>
                                    <input type="text" class="form-input" id="age" value="<?php echo htmlspecialchars($age); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="sex">Sex</label>
                                    <select class="form-input profile-field" id="sex" name="sex" disabled><br>
                                        <option value="Male" <?php echo ($user['sex'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($user['sex'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="civil_status">Civil Status</label>
                                    <select class="form-input profile-field" id="civil_status" name="civil_status" disabled><br>
                                        <option value="Single" <?php echo ($user['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo ($user['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Widowed" <?php echo ($user['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                        <option value="Separated" <?php echo ($user['civil_status'] ?? '') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                                    </select><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="occupation">Occupation</label>
                                    <input type="text" class="form-input profile-field" id="occupation" name="occupation" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="membership">Membership (Organization / Sector)</label>
                                    <input type="text" class="form-input profile-field" id="membership" name="membership" value="<?php echo htmlspecialchars($user['membership'] ?? ''); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="precinct_number">Precinct Number</label>
                                    <input type="text" class="form-input profile-field" id="precinct_number" name="precinct_number" value="<?php echo htmlspecialchars($user['precinct_number'] ?? ''); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="religion">Religion</label>
                                    <input type="text" class="form-input profile-field" id="religion" name="religion" value="<?php echo htmlspecialchars($user['religion'] ?? ''); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="citizenship">Citizenship</label>
                                    <input type="text" class="form-input profile-field" id="citizenship" name="citizenship" value="<?php echo htmlspecialchars($user['citizenship'] ?? ''); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="voter_status">Voter Status</label>
                                    <select class="form-input profile-field" id="voter_status" name="voter_status" disabled>
                                        <option value="Registered" <?php echo ($user['voter_status'] ?? '') === 'Registered' ? 'selected' : ''; ?>>Registered</option>
                                        <option value="Unregistered" <?php echo ($user['voter_status'] ?? '') === 'Unregistered' ? 'selected' : ''; ?>>Unregistered</option>
                                    </select><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="blood_type">Blood Type</label>
                                    <select class="form-input profile-field" id="blood_type" name="blood_type" disabled><br>
                                        <option value="">Select Blood Type</option>
                                        <option value="A+" <?php echo ($user['blood_type'] ?? '') === 'A+' ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo ($user['blood_type'] ?? '') === 'A-' ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo ($user['blood_type'] ?? '') === 'B+' ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo ($user['blood_type'] ?? '') === 'B-' ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo ($user['blood_type'] ?? '') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo ($user['blood_type'] ?? '') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                        <option value="O+" <?php echo ($user['blood_type'] ?? '') === 'O+' ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo ($user['blood_type'] ?? '') === 'O-' ? 'selected' : ''; ?>>O-</option>
                                    </select><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="is_head_of_family">Is Head of Family?</label>
                                    <select class="form-input profile-field" id="is_head_of_family" name="is_head_of_family" disabled><br>
                                        <option value="Yes" <?php echo ($user['is_head_of_family'] ?? '') === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                        <option value="No" <?php echo ($user['is_head_of_family'] ?? '') === 'No' ? 'selected' : ''; ?>>No</option>
                                    </select><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="number_of_dependents">Number of Dependents</label>
                                    <input type="number" min="0" class="form-input profile-field" id="number_of_dependents" name="number_of_dependents" value="<?php echo htmlspecialchars($user['number_of_dependents'] ?? '0'); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="spouse_name">Spouse Name</label>
                                    <input type="text" class="form-input profile-field" id="spouse_name" name="spouse_name" value="<?php echo htmlspecialchars($user['spouse_name'] ?? ''); ?>" disabled><br>
                                </div>
                                <div class="info-group">
                                    <label class="info-label" for="address">Address</label>
                                    <input type="text" class="form-input profile-field" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required disabled><br>
                                </div>
                                <!-- <div class="info-group">
                                    <label class="info-label" for="email">Email</label>
                                    <input type="email" class="form-input profile-field" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required disabled><br>
                                </div> -->
                                <div class="info-group" style="grid-column: span 2;">
                                    <label class="info-label" for="phone">Phone Number</label>
                                    <input type="text" class="form-input profile-field" id="phone" name="phone" value="<?php echo htmlspecialchars($user['contact_no'] ?? ''); ?>" required disabled><br>
                                </div>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <button type="button" id="toggleEditBtn" class="btn-track" onclick="toggleEditMode()">
                                <i class="fa fa-edit"></i> Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleEditMode() {
            const fields = document.querySelectorAll('.profile-field');
            const btn = document.getElementById('toggleEditBtn');
            const form = document.getElementById('profileForm');

            if (fields[0].disabled) {
                fields.forEach(field => field.disabled = false);
                btn.type = 'button';
                btn.innerHTML = '<i class="fa fa-save"></i> Save Changes';
                btn.onclick = function() {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'update_profile';
                    hiddenInput.value = '1';
                    form.appendChild(hiddenInput);
                    form.submit();
                };
            }
        }

        function previewFile() {
            const preview = document.getElementById('imagePreview');
            const defaultIcon = document.getElementById('defaultIcon');
            const file = document.getElementById('profile_photo').files[0];
            const reader = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
                preview.style.display = 'block';
                if (defaultIcon) {
                    defaultIcon.style.display = 'none';
                }
            }

            if (file) {
                reader.readAsDataURL(file);
                document.getElementById('profileForm').submit();
            }
        }

        const globalOverlay = document.getElementById('globalOverlay');

        function toggleNavBarMenu() {
            const menuDrawer = document.getElementById('menuSlideDrawer');
            document.getElementById('messageDrawer').style.display = 'none';
            
            if (menuDrawer.style.display === 'flex') {
                menuDrawer.style.display = 'none';
                globalOverlay.style.display = 'none';
            } else {
                globalOverlay.style.display = 'block';
                menuDrawer.style.display = 'flex';
            }
        }

        function toggleMessages() {
            const drawer = document.getElementById('messageDrawer');
            document.getElementById('menuSlideDrawer').style.display = 'none';
            
            if (drawer.style.display === 'flex') {
                drawer.style.display = 'none';
                const readModalOpen = document.getElementById('readMessageModal').style.display === 'block';
                if (!readModalOpen) {
                    globalOverlay.style.display = 'none';
                }
            } else {
                globalOverlay.style.display = 'block';
                drawer.style.display = 'flex';
            }
        }

        function readMessage(cardElement, content, time, hash) {
            document.getElementById('readMsgContent').innerText = content;
            document.getElementById('readMsgTime').innerText = time;

            document.getElementById('readMessageModal').style.display = 'block';
            globalOverlay.style.display = 'block';

            if (cardElement.classList.contains('unread')) {
                cardElement.classList.remove('unread');
                cardElement.classList.add('read');

                const badgeTop = document.getElementById('msgBadgeTop');
                
                if (badgeTop) {
                    let currentCount = parseInt(badgeTop.innerText, 10);
                    if (currentCount > 1) {
                        badgeTop.innerText = currentCount - 1;
                    } else {
                        badgeTop.remove();
                        const inboxTrigger = document.querySelector('.rd-message-symbol-trigger');
                        if (inboxTrigger) {
                            inboxTrigger.classList.remove('has-unread-pulse');
                        }
                    }
                }

                const formData = new URLSearchParams();
                formData.append('mark_read_hash', hash);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).catch(err => console.error('Error marking message as read:', err));
            }
        }

        function toggleMsgOptions(event, btn) {
            event.stopPropagation();
            document.querySelectorAll('.msg-dropdown-menu').forEach(menu => {
                if (menu !== btn.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            const menu = btn.nextElementSibling;
            menu.classList.toggle('show');
        }

        window.addEventListener('click', function() {
            document.querySelectorAll('.msg-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });

        function deleteMessage(event, hash, cardElement) {
            event.stopPropagation();

            const formData = new URLSearchParams();
            formData.append('delete_msg_hash', hash);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    cardElement.style.transition = 'all 0.3s ease';
                    cardElement.style.opacity = '0';
                    cardElement.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        cardElement.remove();
                        const messageBody = document.querySelector('.message-body');
                        if (!messageBody.querySelector('.admin-msg-card')) {
                            messageBody.innerHTML = `
                                <div style="text-align: center; color: #888; margin-top: 20px;">
                                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; color: #ccc;"></i>
                                    <p>No messages from Admin.</p>
                                </div>
                            `;
                        }
                    }, 300);
                }
            })
            .catch(err => console.error('Error deleting message:', err));
        }

        function closeReadModal() {
            document.getElementById('readMessageModal').style.display = 'none';
            const drawer = document.getElementById('messageDrawer');
            if (drawer.style.display !== 'flex') {
                globalOverlay.style.display = 'none';
            }
        }

        function closeAllModals() {
            document.getElementById('menuSlideDrawer').style.display = 'none';
            document.getElementById('messageDrawer').style.display = 'none';
            document.getElementById('readMessageModal').style.display = 'none';
            document.getElementById('logoutModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        // Logout Modal Trigger Logic
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');

        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('menuSlideDrawer').style.display = 'none';
                logoutModal.style.display = 'flex';
                globalOverlay.style.display = 'block';
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target === logoutModal) {
                logoutModal.style.display = 'none';
                globalOverlay.style.display = 'none';
            }
        });
    </script>
</body>
</html>