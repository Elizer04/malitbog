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

// Fetch user details from the database including email and contact info
$stmt = $conn->prepare("SELECT full_name, contact_no, email FROM residents WHERE resident_id = ?");
$stmt->bind_param("s", $resident_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $full_name = $row['full_name'];
    $contact_no = $row['contact_no'];
    $email = $row['email'];
} else {
    $full_name = "";
    $contact_no = "";
    $email = "";
}
$stmt->close();

if (empty($resident_email) && !empty($email)) {
    $resident_email = $email;
}

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
    
    if ($stmt_msg =$conn->prepare($msg_query)) {$stmt_msg->bind_param("sssssssss", $resident_id,$resident_email, $resident_id,$resident_email, $resident_id,$resident_email, $resident_id,$resident_email, $resident_id);$stmt_msg->execute();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Certification - Barangay Malitbog[cite: 1]</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/resident-dashboard.css">
   <style>
    .hero-banner {
        background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), 
                    url('image/backgroundphoto1.jpg') no-repeat center center/cover;
        padding: 40px 10% 60px;
        color: white;
        text-align: left;
        box-sizing: border-box;
    }

    /* Additional styling elements to perfectly match Certification Overview nav styles */
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
<body>
    
    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i> <i class="fab fa-youtube"></i> <i class="fab fa-twitter"></i>
        </div>
        <div class="profile-section">
            <a href="barangay_profile.php" style="text-decoration: none; color: inherit;"></a>
        </div>
    </div>

    <!-- Updated Navigation Bar -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="certification_overview.php" class="rd-back-link" title="Back to Certification Overview">
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

    <!-- Global Overlay & Drawers -->
    <div class="rd-global-overlay" id="globalOverlay" onclick="closeAllModals()"></div>

    <!-- Menu Slide Drawer -->
    <div class="menu-slide-drawer" id="menuSlideDrawer">
        <div class="menu-drawer-header">
            <h3><i class="fas fa-bars"></i> Menu Options</h3>
            <button type="button" class="close-btn" onclick="toggleNavBarMenu()">&times;</button>
        </div>
        <div class="menu-drawer-body">
            <a href="certification_overview.php"><i class="fas fa-file-alt"></i> Certification Overview</a>
            <a href="resident_dashboard.php"><i class="fas fa-home"></i> Dashboard Home</a>
            <a href="my_profile.php"><i class="fas fa-user-circle"></i> Profile</a>
            <hr style="border: 0; border-top: 1px solid #ddd; margin: 5px 0;">
            <a href="javascript:void(0);" id="logoutBtnDrawer"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Message Drawer Modal[cite: 1] -->
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
            <span class="modal-close-btn" onclick="closeModal('resultModal')">&times;</span>
            <div class="modal-header-custom">
                <h3 id="modalTitle">Application Notice</h3>
            </div>
            
            <div class="modal-body-content">
                <p id="modalMessageBody" style="margin: 0;">Processing your request...</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-modal-action" onclick="closeModal('resultModal')">OK</button>
            </div>
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

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>DOCUMENT <span>REQUEST</span></h1>
            <p>BARANGAY MALITBOG OFFICIAL SERVICES</p>
        </div>
    </header>

    <div class="container">
        <div class="form-header">
            <h2>Certification Application</h2>
        </div>

        <form id="certificationForm" action="process_request.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="resident_id" value="<?php echo htmlspecialchars($resident_id); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

            <div class="form-section-title"><i class="fa fa-id-card"></i> Personal Info</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" readonly required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>" readonly required>
                </div>
            </div>

            <div class="form-section-title"><i class="fa fa-file-alt"></i> Request Details</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Document Type</label>
                    <select name="cert_type" id="cert_type" onchange="updateFee()" required>
                        <option value="">Select Document Type</option>
                        <option value="indigency">Barangay Indigency</option>
                        <option value="clearance">Barangay Clearance</option>
                        <option value="residency">Certificate of Residency</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Processing Fee</label>
                    <input type="text" name="fee" id="fee_display" class="fee-highlight" value="₱0.00" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Purpose Category</label>
                    <select name="purpose_cat" required>
                        <option value="Employment">Employment</option>
                        <option value="Scholarship">Scholarship</option>
                        <option value="ID">Government ID</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Detailed Purpose</label>
                <textarea name="purpose_detail" rows="2" placeholder="Reason for request..." required></textarea>
            </div>

            <div class="form-section-title"><i class="fa fa-upload"></i> Supporting Document</div>
            <div class="form-group">
                <div class="file-upload-box">
                    <input type="file" name="requirement_file" accept="image/*,.pdf">
                    <p style="font-size: 0.75rem; color: #777; margin-top: 5px;">Upload Valid ID (JPG, PNG, PDF)</p>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">Submit Application</button>
        </form>
    </div>

    <script>
        function updateFee() {
            const certType = document.getElementById('cert_type').value;
            const feeInput = document.getElementById('fee_display');
            const feeMap = {
                'indigency': '₱0.00 (Free)',
                'clearance': '₱50.00',
                'residency': '₱50.00'
            };
            feeInput.value = feeMap[certType] || '₱0.00';
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

        window.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
                globalOverlay.style.display = 'none';
            }
        });

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

        window.addEventListener('click', function(e) {
            document.querySelectorAll('.msg-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            const resultModal = document.getElementById('resultModal');
            if (e.target === resultModal) {
                resultModal.style.display = 'none';
            }
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
            document.getElementById('resultModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Handle Certification Form Submission via AJAX & Result Modal Display[cite: 1]
        const certificationForm = document.getElementById('certificationForm');
        const submitBtn = document.getElementById('submitBtn');
        const resultModal = document.getElementById('resultModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessageBody = document.getElementById('modalMessageBody');

        certificationForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent standard page reload[cite: 1]

            const formData = new FormData(certificationForm);
            submitBtn.disabled = true;
            submitBtn.textContent = "Submitting Application...";

            const startTime = Date.now();

            fetch('process_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const elapsedTime = Date.now() - startTime;
                const minDisplayTime = 500; 
                const delay = elapsedTime < minDisplayTime ? minDisplayTime - elapsedTime : 0;

                setTimeout(() => {
                    let serverMsg = data.trim();

                    if (serverMsg.toLowerCase().includes('success') || serverMsg === '1') {
                        modalTitle.textContent = "Success: Application Submitted";
                        modalMessageBody.textContent = "Your certification application has been successfully submitted! You can track its status on your dashboard.";
                        certificationForm.reset(); 
                        document.getElementById('fee_display').value = "₱0.00"; // Reset dynamic fee field
                    } else {
                        modalTitle.textContent = "Notice: Submission Issue";
                        
                        if (serverMsg.toLowerCase().includes('duplicate') || serverMsg.toLowerCase().includes('already') || serverMsg.toLowerCase().includes('exists')) {
                            modalMessageBody.textContent = "You already have a pending or existing request for this document type. Please check your dashboard for updates.";
                        } else {
                            modalMessageBody.textContent = serverMsg || 'Application submission failed. Please check your inputs and try again.';
                        }
                    }

                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Application';
                    resultModal.style.display = 'flex'; // Show modal popup
                }, delay);
            })
            .catch(error => {
                const elapsedTime = Date.now() - startTime;
                const minDisplayTime = 500;
                const delay = elapsedTime < minDesignTime ? minDisplayTime - elapsedTime : 0; // fallback safety

                setTimeout(() => {
                    modalTitle.textContent = "System Error";
                    modalMessageBody.textContent = 'A network error occurred. Please check your connection and try again.';
                    console.error('Error:', error);

                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Application';
                    resultModal.style.display = 'flex'; // Show modal popup
                }, delay);
            });
        });
    </script>
</body>
</html>
<?php 
if (isset($conn)) {$conn->close(); }
?>