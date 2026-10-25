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

$host = '127.0.0.1';
$dbname = 'bmis'; 
$username = 'root'; 
$password = 'eli'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}



$resident_id = isset($_SESSION['resident_id']) ? $_SESSION['resident_id'] : 1;
$user_full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'irene moros';

// Fetch active cases for this resident
try {
    $stmtActive = $pdo->prepare("
        SELECT * FROM cases 
        WHERE complainant_id = ? AND status NOT IN ('Resolved', 'Dismissed', 'Settled') 
        ORDER BY created_at DESC
    ");
    $stmtActive->execute([$resident_id]);
    $active_cases_list =$stmtActive->fetchAll();
} catch (Exception $e) {$active_cases_list = [];
}

// Fetch all messages linked strictly to this resident's ID only[cite: 1]
$admin_messages = [];$unread_count = 0;

if (isset($conn) && $resident_id) {$msg_query = "
        SELECT 'residency' AS src, claim_message AS remarks, status, created_at FROM cert_of_residency WHERE resident_id = ? AND claim_message IS NOT NULL AND claim_message != ''
        UNION ALL
        SELECT 'indigency' AS src, claim_message AS remarks, status, created_at FROM cert_of_indigency WHERE resident_id = ? AND claim_message IS NOT NULL AND claim_message != ''
        UNION ALL
        SELECT 'clearance' AS src, claim_message AS remarks, status, created_at FROM barangay_clearance WHERE resident_id = ? AND claim_message IS NOT NULL AND claim_message != ''
        UNION ALL
        SELECT 'resident' AS src, remarks, 'General' AS status, created_at FROM residents WHERE resident_id = ? AND remarks IS NOT NULL AND remarks != ''
        UNION ALL
        SELECT 'appointment' AS src, remarks, status, created_at FROM appointments WHERE resident_id = ? AND remarks IS NOT NULL AND remarks != ''
        ORDER BY created_at DESC
    ";
    
    if ($stmt_msg =$conn->prepare($msg_query)) {$stmt_msg->bind_param("sssss", $resident_id,$resident_id, $resident_id,$resident_id, $resident_id);$stmt_msg->execute();
        $res_msg =$stmt_msg->get_result();
        while ($row_msg = $res_msg->fetch_assoc()) {$msg_text = isset($row_msg['remarks']) ?$row_msg['remarks'] : '';
            $msg_time = isset($row_msg['created_at']) ? $row_msg['created_at'] : 'Recently';$msg_source = isset($row_msg['src']) ?$row_msg['src'] : 'general';
            $msg_hash = md5($msg_source . $msg_text .$msg_time);
            
            if (!in_array($msg_hash,$_SESSION['deleted_messages'])) {
                $row_msg['msg_hash'] =$msg_hash;
                $row_msg['is_read'] = in_array($msg_hash,$_SESSION['read_messages']);
                
                if (!$row_msg['is_read']) {$unread_count++;
                }
                
                $admin_messages[] =$row_msg;
            }
        }
        $stmt_msg->close();
    }
}

$current_date = date('M d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Cases Tracker - Barangay Malitbog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/resident-dashboard.css">
   
    <style>
        .rd-dashboard-page .rd-hero-banner {
            scroll-margin-top: 80px;
            background: linear-gradient(rgba(26, 26, 140, 0.85), rgba(26, 26, 140, 0.85)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 40px 10%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
            box-sizing: border-box;
        }

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
        .logo-text h2 { margin: 0; color: #000066; font-size: 1.1rem; letter-spacing: 1px; }
        .logo-text small { font-size: 0.7rem; color: #555; display: block; }

        .rd-back-link {
            font-size: 1.2rem;
            color: #000066;
            margin-right: 5px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .rd-back-link:hover { color: #ffcc00; }

        .nav-links { display: flex; align-items: center; gap: 25px; }

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
        .rd-menu-symbol-trigger.active,
        .rd-nav-item.active {
            color: #ffcc00;
        }

        .rd-nav-link-dropdown { display: inline-flex; align-items: center; position: relative; }
        .rd-message-symbol-trigger, .rd-nav-menu-bar-trigger { cursor: pointer; position: relative; }

        .rd-msg-badge { background-color: #ff3333; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.6rem; font-weight: bold; }
        .rd-msg-badge-top { position: absolute; top: -4px; right: -8px; }

        .cases-table-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 25px;
            margin-top: 20px;
            margin-bottom: 40px;
            overflow-x: auto;
        }

        .rd-custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.95rem;
        }

        .rd-custom-table th, .rd-custom-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #edf2f7;
        }

        .rd-custom-table th {
            background-color: #f8fafc;
            color: #1a1a8c;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .rd-custom-table tr:hover {
            background-color: #f8fafc;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pending { background-color: #fef3c7; color: #d97706; }
        .status-inprogress { background-color: #e0f2fe; color: #0284c7; }
        .status-default { background-color: #f1f5f9; color: #475569; }

        @media (max-width: 768px) {
            .main-nav { padding: 8px 12px; }
            .logo-section img { height: 40px; width: 40px; }
            .logo-text h2 { font-size: 0.95rem; }
            .logo-text small { font-size: 0.6rem; }
            .nav-links { gap: 15px; }
            .rd-nav-item span, .rd-menu-symbol-trigger span, .rd-nav-menu-bar-trigger span, .rd-message-symbol-trigger span { display: none; }
        }
    </style>
</head>
<body class="rd-dashboard-page">

    <!-- Top Bar -->
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

    <!-- Main Navigation Bar -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="dashboard_overview.php" class="rd-back-link" title="Back to Dashboard Overview">
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
                <a href="my_profile.php" class="rd-menu-symbol-trigger custom-profile-style" title="My Profile">
                    <i class="fas fa-user-circle"></i>
                    <span>profile</span>
                </a>
            </div>

            <div class="rd-nav-link-dropdown">
                <a href="javascript:void(0);" class="rd-nav-menu-bar-trigger" onclick="toggleNavBarMenu()" title="Menu Options">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Global Overlay -->
    <div class="rd-global-overlay" id="globalOverlay" onclick="closeAllModals()"></div>

    <!-- Slide Drawer Menu -->
    <div class="menu-slide-drawer" id="menuSlideDrawer">
        <div class="menu-drawer-header">
            <h3><i class="fas fa-bars"></i> Menu Options</h3>
            <button type="button" class="close-btn" onclick="toggleNavBarMenu()">&times;</button>
        </div>
        <div class="menu-drawer-body">
            <a href="certification.php"><i class="fas fa-file-contract"></i> Apply Certification</a>
            <a href="dashboard_overview.php"><i class="fas fa-chart-pie"></i> Dashboard Overview</a>
            <a href="resident_dashboard.php"><i class="fas fa-home"></i> Dashboard Home</a>
            <a href="my_profile.php"><i class="fas fa-user-circle"></i> Profile</a>
            <hr style="border: 0; border-top: 1px solid #ddd; margin: 5px 0;">
            <a href="javascript:void(0);" id="logoutBtnDrawer"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Admin Messages Drawer -->
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

    <!-- Read Message Modal -->
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

    <!-- Hero Banner -->
    <header class="rd-hero-banner">
        <div class="rd-welcome-msg">
            <h1>ACTIVE CASES <span>TRACKER</span></h1>
            <p>Viewing ongoing cases for <strong><?php echo htmlspecialchars($user_full_name); ?></strong> | <?php echo$current_date; ?></p>
        </div>
        <div class="rd-urgent-notices">
            <strong>SYSTEM STATUS</strong>
            <ul>
                <li><strong>Monitoring Ongoing Blotter & Judicial Cases</strong></li>
                <li>Barangay Malitbog Incident Monitor</li>
            </ul>
        </div>
    </header>

    <!-- Main Body Content -->
    <div class="rd-container">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-top: 30px;">
            <h2 class="rd-section-title" style="margin-bottom: 0;"><i class="fas fa-briefcase"></i> Your Active Cases</h2>
        </div>

        <div class="cases-table-container">
            <?php if (!empty($active_cases_list)): ?>
                <table class="rd-custom-table">
                    <thead>
                        <tr>
                            <th>Case ID / No.</th>
                            <th>Incident Details</th>
                            <th>Respondent</th>
                            <th>Date Filed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_cases_list as$case): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($case['case_id'] ?? $case['id'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo htmlspecialchars($case['incident_details'] ?? $case['details'] ?? $case['description'] ?? 'Ongoing Investigation'); ?></td>
                                <td><?php echo htmlspecialchars($case['respondent_name'] ?? 'Unspecified'); ?></td>
                                <td><?php echo htmlspecialchars($case['created_at'] ?? 'Recent'); ?></td>
                                <td>
                                    <?php 
                                        $status = $case['status'] ?? 'Pending';$badge_class = 'status-default';
                                        if (strtolower($status) === 'pending')$badge_class = 'status-pending';
                                        elseif (in_array(strtolower($status), ['in progress', 'ongoing', 'scheduled']))$badge_class = 'status-inprogress';
                                    ?>
                                    <span class="status-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">No active cases found under your account.</p>
                    <p style="font-size: 0.9rem; color: #94a3b8; margin-top: 5px;">All your filed blotters are either resolved, dismissed, or settled.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn" onclick="document.getElementById('logoutModal').style.display='none'" style="position: absolute; right: 15px; top: 12px;">&times;</span>
            <div class="modal-header">
                <h3>System Notice</h3>
            </div>
            <div class="modal-body-content">
                <p style="margin: 0;">Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" id="cancelLogoutBtn" onclick="document.getElementById('logoutModal').style.display='none'">Cancel</button>
                <a href="index.php" style="text-decoration: none;">
                    <button type="button" class="btn-modal-action">Yes</button>
                </a>
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

        const logoutBtnDrawer = document.getElementById('logoutBtnDrawer');
        const logoutModal = document.getElementById('logoutModal');

        if (logoutBtnDrawer) {
            logoutBtnDrawer.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('menuSlideDrawer').style.display = 'none';
                logoutModal.style.display = 'flex';
                globalOverlay.style.display = 'block';
            });
        }

        window.onclick = function(event) {
            if (event.target === logoutModal) {
                logoutModal.style.display = 'none';
                globalOverlay.style.display = 'none';
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

        function closeAllModals() {
            document.getElementById('menuSlideDrawer').style.display = 'none';
            document.getElementById('messageDrawer').style.display = 'none';
            document.getElementById('readMessageModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }
    </script>
</body>
</html>
<?php 
if (isset($conn)) {$conn->close(); }
?>