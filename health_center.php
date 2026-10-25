<?php
include 'db_connect.php'; 
session_start();

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


$resident_id = $_SESSION['resident_id'];
$resident_email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Resident';

// Handle Appointment Form Submission via AJAX/POST (Allows multiple appointments per resident)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appointment'])) {
    $form_resident_id = trim($_POST['resident_id']);
    $service = trim($_POST['service']);
    $appt_date = trim($_POST['appt_date']);
    $notes = trim($_POST['notes']);

    if (!empty($form_resident_id) && !empty($service) && !empty($appt_date)) {
        $current_year = date('Y');
        $prefix = "APPT" . $current_year . "-";
        
        $conn->begin_transaction();
        try {
            $count_query = "SELECT COUNT(*) as total FROM appointments WHERE YEAR(created_at) = ?";
            $stmt_count = $conn->prepare($count_query);
            $stmt_count->bind_param("s", $current_year);
            $stmt_count->execute();
            $count_result = $stmt_count->get_result()->fetch_assoc();
            $next_seq = intval($count_result['total']) + 1;
            $stmt_count->close();

            $appointment_code = $prefix . str_pad($next_seq, 4, '0', STR_PAD_LEFT);

            $stmt_appt = $conn->prepare("INSERT INTO appointments (appointment_code, resident_id, service, appt_date, notes, status, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
            if ($stmt_appt) {
                $stmt_appt->bind_param("sisss", $appointment_code, $form_resident_id, $service, $appt_date, $notes);
                if ($stmt_appt->execute()) {
                    $conn->commit();
                    $stmt_appt->close();
                    
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        echo json_encode(['status' => 'success', 'appt_id' => $appointment_code]);
                        exit();
                    } else {
                        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&appt=" . $appointment_code);
                        exit();
                    }
                } else {
                    throw new Exception('Failed to save appointment into database.');
                }
            } else {
                throw new Exception('Failed to prepare statement.');
            }
        } catch (Exception $e) {
            $conn->rollback();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                exit();
            }
        }
    } else {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
            exit();
        }
    }
}

// 3. Fetch all messages linked to this resident
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

// 4. Fetch Health Schedule Updates from Database safely
$center_schedules = [];
$sched_query = "SELECT day_of_week, service_title, description FROM health_schedule_updates ORDER BY id DESC LIMIT 5";
if ($result_sched = $conn->query($sched_query)) {
    while ($row_sched = $result_sched->fetch_assoc()) {
        $center_schedules[] = $row_sched;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Center - Barangay Malitbog[cite: 1]</title>
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
            --border-color: #e0e0e0;
            --success-green: #28a745;
        }

        .modal {
            display: none; position: fixed; z-index: 1500; left: 0; top: 0; width: 100%; height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; overflow-y: auto; padding: 15px;
            box-sizing: border-box;
        }
        .modal-content {
            background-color: #fff; padding: 20px; border-radius: 12px; width: 95%; max-width: 360px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); position: relative; box-sizing: border-box;
        }
        .close-btn { background: none; border: none; font-size: 1.5rem; font-weight: bold; color: #aaa; cursor: pointer; }
        .close-btn:hover { color: #000; }
        .modal-header { border-bottom: 2px solid #eee; padding-bottom: 8px; margin-bottom: 15px; }
        .modal-header h3 { margin: 0; color: var(--primary-blue); font-size: 1.15rem; }
        .modal-body-content { padding: 5px 0; font-size: 0.9rem; color: #333; line-height: 1.4; }
        .modal-footer {
            margin-top: 15px; text-align: right; padding-top: 12px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px;
        }
        .btn-modal-action { background: var(--primary-blue); color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-action:hover { background: #1a1a8c; }

        .top-bar { background: #1a1a8c; color: white; padding: 5px 10%; display: flex; justify-content: space-between; font-size: 0.75rem; }
        .main-nav { background: white; padding: 8px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: relative; z-index: 1100; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-section img { height: 50px; width: 50px; border-radius: 50%; object-fit: cover; }
        .logo-text h2 { margin: 0; color: var(--primary-blue); font-size: 1.1rem; letter-spacing: 1px; }
        .logo-text small { font-size: 0.7rem; }
        
        .back-link-icon { text-decoration: none; color: var(--primary-blue); font-size: 1.1rem; margin-right: 5px; display: flex; align-items: center; transition: opacity 0.2s; }
        .back-link-icon:hover { opacity: 0.7; }

        .nav-links { display: flex; align-items: center; gap: 40px; }
        .rd-nav-item, .rd-nav-link-dropdown a, .rd-nav-link-dropdown span {
            display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #333; gap: 4px; font-size: 0.75rem; font-weight: 600; transition: color 0.2s;
        }
        .rd-nav-item.custom-home-style span, .rd-menu-symbol-trigger.custom-profile-style span {
            font-family: 'Poppins', sans-serif; font-size: 0.8rem; text-transform: capitalize; letter-spacing: 0.5px;
        }
        .rd-nav-item i, .rd-message-symbol-trigger i, .rd-menu-symbol-trigger i { font-size: 1.1rem; }
        .rd-nav-item:hover, .rd-message-symbol-trigger:hover, .rd-menu-symbol-trigger:hover { color: var(--primary-blue); }

        .rd-nav-link-dropdown { display: inline-flex; align-items: center; position: relative; }
        .rd-message-symbol-trigger { cursor: pointer; position: relative; }
        .rd-msg-badge { background-color: #ff3333; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.6rem; font-weight: bold; }
        .rd-msg-badge-top { position: absolute; top: -4px; right: -8px; }

        @keyframes pulseGlow {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 51, 51, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(255, 51, 51, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 51, 51, 0); }
        }
        .has-unread-pulse { animation: pulseGlow 1.8s infinite; border-radius: 50%; }

        .menu-slide-drawer { display: none; position: fixed; z-index: 1300; right: 0; top: 0; width: 300px; max-width: 28vw; height: 100%; background-color: white; box-shadow: -5px 0 20px rgba(0,0,0,0.15); flex-direction: column; }
        .menu-drawer-header { background-color: var(--primary-blue); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .menu-drawer-header h3 { margin: 0; font-size: 1.1rem; }
        .menu-drawer-body { padding: 15px; overflow-y: auto; flex-grow: 1; display: flex; flex-direction: column; gap: 10px; }
        .menu-drawer-body a { display: flex; align-items: center; gap: 15px; padding: 15px; text-decoration: none; color: #333; font-size: 0.95rem; font-weight: 500; background: #f9f9f9; border-left: 4px solid var(--accent-gold); border-radius: 4px; }
        .menu-drawer-body a i { font-size: 1.1rem; color: var(--primary-blue); width: 20px; text-align: center; }

        .message-modal { display: none; position: fixed; z-index: 1300; right: 0; top: 0; width: 340px; max-width: 30vw; height: 100%; background-color: white; box-shadow: -5px 0 20px rgba(0,0,0,0.15); flex-direction: column; }
        .message-header { background-color: var(--primary-blue); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .message-header h3 { margin: 0; font-size: 1.1rem; }
        .message-body { padding: 15px; overflow-y: auto; flex-grow: 1; }

        .admin-msg-card { background: #f9f9f9; border-left: 4px solid var(--accent-gold); padding: 12px 15px; margin-bottom: 12px; border-radius: 4px; cursor: pointer; position: relative; }
        .admin-msg-card.unread { background: #eef2ff; border-left-color: var(--primary-blue); }
        .admin-msg-card.read { background: #f9f9f9; border-left-color: #ccc; opacity: 0.85; }

        .msg-meta { font-size: 0.75rem; color: #777; display: flex; justify-content: space-between; margin-bottom: 6px; padding-right: 25px; }
        .msg-sender { font-weight: bold; color: var(--primary-blue); }
        .msg-content { font-size: 0.9rem; color: #333; margin: 0; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .msg-options-container { position: absolute; top: 10px; right: 12px; }
        .msg-options-btn { background: none; border: none; color: #777; cursor: pointer; font-size: 1rem; padding: 2px 6px; border-radius: 4px; display: none; }
        .admin-msg-card.read .msg-options-btn { display: block; }
        .msg-dropdown-menu { display: none; position: absolute; right: 0; top: 25px; background: white; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 6px; z-index: 20; min-width: 100px; }
        .msg-dropdown-menu.show { display: block; }
        .msg-dropdown-menu button { background: none; border: none; width: 100%; padding: 8px 12px; text-align: left; font-size: 0.85rem; color: #ff3333; cursor: pointer; border-radius: 6px; }

        .read-modal { display: none; position: fixed; z-index: 1400; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 500px; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; }
        .read-modal-header { background: var(--primary-blue); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .read-modal-body { padding: 20px; max-height: 400px; overflow-y: auto; }
        .read-modal-body p { margin: 0; line-height: 1.6; color: #333; white-space: pre-line; }

        .rd-global-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 1050; }
        .menu-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--primary-blue); cursor: pointer; z-index: 1350; }

        .rd-hero-banner {
            background: linear-gradient(135deg, var(--primary-blue), #1a1a8c);
            padding: 40px 8%; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
        }
        .rd-welcome-msg h1 { margin: 0 0 5px 0; font-size: 1.8rem; font-family: 'Poppins', sans-serif; color: #ffffff !important; }
        .rd-welcome-msg h1 span { color: var(--accent-gold) !important; }
        .rd-welcome-msg p { margin: 0; font-size: 0.9rem; color: #ffffff !important; opacity: 0.95; }

        .rd-container { max-width: 1200px; margin: -30px auto 40px; padding: 0 5%; position: relative; z-index: 10; }
        .health-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .rd-section-title { text-transform: uppercase; font-weight: bold; margin-bottom: 20px; color: var(--primary-blue); border-left: 4px solid var(--accent-gold); padding-left: 12px; font-size: 1.2rem; }
        .card { background: white; border: 1px solid var(--border-color); padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 11px 14px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        .form-group input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .btn-track { background: var(--primary-blue); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; text-transform: uppercase; }
        
        .schedule-item { display: flex; flex-direction: column; gap: 4px; padding: 12px 0; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .schedule-date-header { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .schedule-notice { font-size: 0.82rem; color: #555; font-style: italic; }
        .status-badge { background: var(--success-green); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }

        @media screen and (max-width: 768px) {
            .menu-toggle { display: block; }
            .nav-links { display: none; } 
            .menu-slide-drawer, .message-modal { width: 50% !important; max-width: 50vw !important; min-width: unset !important; }
            .health-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="rd-dashboard-page">

    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-twitter"></i>
        </div>
        <div>
            <div class="profile-section">
                <a href="barangay_profile.php" style="text-decoration: none; color: inherit;"></a>
            </div>
        </div>
    </div>

    <nav class="main-nav">
        <div class="logo-section">
            <a href="resident_dashboard.php" id="backDashboardBtn" class="back-link-icon" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>PROVINCE OF ORIENTAL MINDORO</small>
            </div>
        </div>

        <button class="menu-toggle" id="mobile-menu-btn" aria-label="Toggle Navigation" onclick="toggleNavBarMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
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
                <a href="my_profile.php" class="rd-menu-symbol-trigger custom-profile-style" title="My Profile">
                    <i class="fas fa-user-circle"></i>
                    <span>profile</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="rd-global-overlay" id="globalOverlay" onclick="closeAllModals()"></div>

    <div class="menu-slide-drawer" id="menuSlideDrawer">
        <div class="menu-drawer-header">
            <h3><i class="fas fa-bars"></i> Menu Options</h3>
            <button type="button" class="close-btn" onclick="toggleNavBarMenu()">&times;</button>
        </div>
        <div class="menu-drawer-body">
            <a href="resident_dashboard.php"><i class="fas fa-home"></i> Home</a>
            <a href="javascript:void(0);" onclick="toggleNavBarMenu(); toggleMessages();">
                <i class="fas fa-envelope"></i> Inbox 
                <?php if ($unread_count > 0): ?>
                    <span class="rd-msg-badge" id="msgBadgeMobile" style="margin-left: auto;"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="my_profile.php"><i class="fas fa-user-circle"></i> Profile</a>

            <hr style="border: 0; border-top: 1px solid #ddd; margin: 5px 0;">
            <div style="font-size: 0.75rem; font-weight: bold; color: #777; padding: 5px 15px; text-transform: uppercase;">Resident Services</div>
            <a href="certification.php"><i class="fas fa-file-contract"></i> Certification</a>
            <a href="judicial_system.php"><i class="fas fa-gavel"></i> Judicial System</a>
            <a href="health_center.php"><i class="fas fa-hospital-user"></i> Health Center</a>
        </div>
    </div>

    <div class="message-modal" id="messageDrawer">
        <div class="message-header">
            <h3><i class="fas fa-comments"></i> Admin Messages</h3>
            <button type="button" class="close-btn" onclick="toggleMessages()">&times;</button>
        </div>
        <div class="message-body">
            <?php if (!empty($admin_messages)): ?>
                <?php foreach ($admin_messages as$msg): ?>
                    <?php 
                        $msg_text = isset($msg['remarks']) ?$msg['remarks'] : '';
                        $msg_time = isset($msg['created_at']) ? $msg['created_at'] : 'Recently';$msg_source = isset($msg['src']) ?$msg['src'] : 'general';
                        $msg_hash = isset($msg['msg_hash']) ? $msg['msg_hash'] : md5($msg_source . $msg_text .$msg_time);
                        $is_read =$msg['is_read'];
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

    <div id="resultModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('resultModal')" style="position: absolute; right: 15px; top: 12px;">&times;</span>
            <div class="modal-header"><h3 id="modalTitle">Appointment Notice</h3></div>
            <div class="modal-body-content"><p id="modalMessageBody" style="margin: 0;">Processing...</p></div>
            <div class="modal-footer"><button type="button" class="btn-modal-action" onclick="closeModal('resultModal')">OK</button></div>
        </div>
    </div>

    <header class="rd-hero-banner">
        <div class="rd-welcome-msg">
            <h1>HEALTH <span>CENTER</span></h1>
            <p>PROVINCE OF ORIENTAL MINDORO - BARANGAY MALITBOG[cite: 1]</p>
        </div>
    </header>

    <div class="rd-container">
        <div class="health-grid">
            <div class="main-content">
                <h2 class="rd-section-title">Book an Appointment</h2>
                <div class="card">
                    <form id="appointmentForm" method="POST" action="">
                        <div class="form-group">
                            <label>Resident ID</label>
                            <input type="text" name="resident_id" value="<?php echo htmlspecialchars($resident_id); ?>" readonly required>
                        </div>
                        <div class="form-group">
                            <label>Nature of Visit</label>
                            <select name="service" required>
                                <option value="">Select Service...</option>
                                <option value="General Check-up">General Check-up</option>
                                <option value="Vaccination">Vaccination</option>
                                <option value="Prenatal Care">Prenatal Care</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Preferred Date</label>
                            <input type="date" name="appt_date" required>
                        </div>
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <input type="text" name="notes" placeholder="Symptoms or concerns">
                        </div>
                        <button type="submit" name="submit_appointment" id="submitBtn" class="btn-track">REQUEST APPOINTMENT</button>
                    </form>
                </div>
            </div>

            <div class="sidebar">
                <h2 class="rd-section-title">Center Schedule</h2>
                <div class="card">
                    <?php if (!empty($center_schedules)): ?>
                        <?php foreach ($center_schedules as$sched): ?>
                            <div class="schedule-item">
                                <div class="schedule-date-header">
                                    <span><strong><?php echo htmlspecialchars($sched['day_of_week']); ?></strong></span>
                                    <span class="status-badge"><?php echo htmlspecialchars($sched['service_title']); ?></span>
                                </div>
                                <?php if (!empty($sched['description'])): ?>
                                    <span class="schedule-notice"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($sched['description']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #888; padding: 10px 0;">
                            <p style="margin: 0; font-size: 0.9rem;">No upcoming schedule updates.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                const badgeMobile = document.getElementById('msgBadgeMobile');
                
                [badgeTop, badgeMobile].forEach(badge => {
                    if (badge) {
                        let currentCount = parseInt(badge.innerText, 10);
                        if (currentCount > 1) {
                            badge.innerText = currentCount - 1;
                        } else {
                            badge.remove();
                            const inboxTrigger = document.querySelector('.rd-message-symbol-trigger');
                            if (inboxTrigger) {
                                inboxTrigger.classList.remove('has-unread-pulse');
                            }
                        }
                    }
                });

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

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'resultModal') location.reload();
        }

        function closeAllModals() {
            document.getElementById('menuSlideDrawer').style.display = 'none';
            document.getElementById('messageDrawer').style.display = 'none';
            document.getElementById('readMessageModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        const appointmentForm = document.getElementById('appointmentForm');
        const submitBtn = document.getElementById('submitBtn');
        const resultModal = document.getElementById('resultModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessageBody = document.getElementById('modalMessageBody');

        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(appointmentForm);
            formData.append('submit_appointment', '1');

            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    modalTitle.textContent = "Success: Appointment Requested";
                    modalMessageBody.textContent = "Your health appointment has been successfully requested (Reference ID: #" + data.appt_id + "). You may book additional appointments if needed.";
                    appointmentForm.reset();
                } else {
                    modalTitle.textContent = "Notice: Submission Issue";
                    modalMessageBody.textContent = data.message || 'Appointment request failed.';
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'REQUEST APPOINTMENT';
                resultModal.style.display = 'flex';
            })
            .catch(error => {
                modalTitle.textContent = "System Error";
                modalMessageBody.textContent = 'A network error occurred.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'REQUEST APPOINTMENT';
                resultModal.style.display = 'flex';
            });
        });
    </script>
</body>
</html>
<?php if (isset($conn)) {$conn->close(); } ?>