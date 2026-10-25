<?php
session_start();

// Security Check: Prevent direct access if not logged in or not an official
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'official') {
    header("Location: login.php");
    exit();
}

$official_name = $_SESSION['name'];
$official_email = $_SESSION['email'];

$host = '127.0.0.1';
$dbname = 'bmis';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Fetch dynamic case counts
$stmtCount = $pdo->query("SELECT COUNT(*) AS total FROM cases WHERE TRIM(status) != 'Settled'");
$active_cases_count = $stmtCount->fetch()['total'];

$stmtCases = $pdo->query("
    SELECT c.*, h.hearing_date 
    FROM cases c 
    LEFT JOIN (
        SELECT case_id, MIN(hearing_date) AS hearing_date 
        FROM hearings 
        WHERE hearing_date >= CURDATE() 
        GROUP BY case_id
    ) h ON c.case_id = h.case_id 
    ORDER BY c.created_at DESC
");
$cases = $stmtCases->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Records - Barangay Official Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #000066;
            --secondary-blue: #1a1a8c;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --text-gray: #555;
            --admin-red: #cc0000;
        }

        html, body {
            min-height: 100%;
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
        }

        * { box-sizing: border-box; }

        .top-bar {
            background: var(--secondary-blue);
            color: white;
            padding: 5px 10%;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .top-bar div:first-child { display: flex; gap: 15px; }

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
        .back-link { color: var(--primary-blue); font-size: 1.2rem; text-decoration: none; margin-right: 5px; transition: color 0.3s ease; }
        .back-link:hover { color: var(--accent-gold); }
        .logo-section img { height: 50px; width: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-gold); }
        .logo-text h2 { margin: 0; color: var(--primary-blue); font-size: 1.1rem; letter-spacing: 1px; }
        .logo-text small { font-size: 0.7rem; color: #555; display: block; }

        .nav-links { display: flex; align-items: center; gap: 25px; }
        .rd-nav-item, .rd-nav-link-dropdown a, .rd-nav-link-dropdown span {
            display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #333; gap: 4px; font-size: 0.75rem; font-weight: 600; transition: color 0.2s;
        }
        .rd-nav-item.custom-home-style span, .rd-nav-menu-bar-trigger span {
            font-family: 'Poppins', sans-serif; font-size: 0.75rem; text-transform: capitalize; letter-spacing: 0.5px;
        }
        .rd-nav-item i, .rd-nav-menu-bar-trigger i { font-size: 1.1rem; }
        .rd-nav-item:hover, .rd-nav-menu-bar-trigger:hover { color: var(--accent-gold); }
        .rd-nav-link-dropdown { display: inline-flex; align-items: center; position: relative; }
        .rd-nav-menu-bar-trigger { cursor: pointer; }

        /* Slide-in Menu Drawer Styles */
        .menu-slide-drawer {
            display: none; position: fixed; z-index: 1300; right: 0; top: 0; width: 100%; max-width: 380px; height: 100%; background-color: white; box-shadow: -5px 0 20px rgba(0,0,0,0.15); flex-direction: column; animation: slideInMenu 0.3s ease-out;
        }
        @keyframes slideInMenu { from { transform: translateX(100%); } to { transform: translateX(0); } }
        .menu-drawer-header { background-color: var(--primary-blue); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; }
        .admin-info-container { display: flex; align-items: center; gap: 12px; }
        .admin-avatar { font-size: 2.2rem; color: var(--accent-gold); }
        .admin-details h4 { margin: 0; font-size: 1rem; font-weight: 600; color: white; }
        .admin-details p { margin: 3px 0 0 0; font-size: 0.8rem; color: #d1d5db; }
        .menu-drawer-body { padding: 15px; overflow-y: auto; flex-grow: 1; display: flex; flex-direction: column; gap: 10px; }
        .menu-drawer-body a {
            display: flex; align-items: center; gap: 15px; padding: 15px; text-decoration: none; color: #333; font-size: 0.95rem; font-weight: 500; background: #f9f9f9; border-left: 4px solid var(--accent-gold); border-radius: 4px; transition: 0.2s;
        }
        .menu-drawer-body a:hover { background: #f0f0f0; color: var(--primary-blue); }
        .menu-drawer-body a i { font-size: 1.1rem; color: var(--primary-blue); width: 20px; text-align: center; }
        .rd-global-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 1050; }
        .close-btn { background: none; border: none; font-size: 1.5rem; font-weight: bold; color: #aaa; cursor: pointer; }
        .close-btn:hover { color: #fff; }

        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 60px 10%; color: white; display: flex; justify-content: space-between; align-items: center;
        }
        .welcome-msg h1 { margin: 0; font-size: 2.0rem; }
        .welcome-msg p { font-size: 1.1rem; margin-top: 10px; opacity: 0.9; }
        .welcome-msg span { color: var(--accent-gold); }

        /* Container & Table */
        .container { max-width: 1200px; margin: -30px auto 40px; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; z-index: 10; }
        .section-title { text-transform: uppercase; font-weight: bold; margin-bottom: 25px; color: #333; display: flex; justify-content: space-between; align-items: center; }
        .action-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .case-table { width: 100%; border-collapse: collapse; }
        .case-table th { text-align: left; padding: 15px; background: #f8f9fa; color: var(--primary-blue); border-bottom: 2px solid #eee; text-transform: uppercase; font-size: 0.85rem; }
        .case-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .case-status { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; display: inline-block; background: #e2e3e5; color: #383d41; }
        .btn-view { background: var(--primary-blue); color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; transition: background 0.3s; }
        .btn-view:hover { background: #000044; }

        /* Modal (Read-Only View) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 4% auto; padding: 30px; border: 1px solid #888; width: 55%; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); position: relative; }
        .close-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 20px; }
        .close-modal:hover, .close-modal:focus { color: var(--admin-red); text-decoration: none; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .detail-group { margin-bottom: 10px; }
        .detail-group.full-width { grid-column: span 2; }
        .detail-group label { font-weight: bold; color: var(--primary-blue); display: block; margin-bottom: 5px; font-size: 0.8rem; text-transform: uppercase; }
        .detail-input { width: 100%; padding: 10px 12px; background: #e9ecef; border-radius: 6px; border: 1px solid #ddd; font-size: 0.95rem; color: #333; box-sizing: border-box; cursor: not-allowed; }
        .modal-actions { grid-column: span 2; display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 600; text-transform: uppercase; transition: background 0.3s; }
        .btn-cancel:hover { background: #5a6268; }

        @media (max-width: 768px) {
            .top-bar { display: none; }
            .menu-slide-drawer { max-width: 60% !important; width: 60% !important; }
            .hero-banner { padding: 40px 5%; flex-direction: column; text-align: center; gap: 20px; }
        }
    </style>
</head>
<body>

    <!-- Top Utility Bar -->
    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-twitter"></i>
        </div>
        <div class="profile-section">
            <span><i class="fa fa-id-badge"></i> Barangay Official Portal</span>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="official_dashboard.php" class="back-link" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>OFFICIAL PORTAL</small>
            </div>
        </div>
        <div class="nav-links">
            <a href="official_dashboard.php" class="rd-nav-item custom-home-style" title="Official Home">
                <i class="fas fa-home"></i>
                <span>home</span>
            </a>
            <div class="rd-nav-link-dropdown">
                <a href="javascript:void(0);" class="rd-nav-menu-bar-trigger" onclick="toggleNavBarMenu()" title="Menu Options">
                    <i class="fas fa-bars"></i>
                    <span>Menu</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Global Overlay & Slide-in Drawer -->
    <div class="rd-global-overlay" id="globalOverlay" onclick="closeAllModals()"></div>

    <div class="menu-slide-drawer" id="menuSlideDrawer">
        <div class="menu-drawer-header">
            <div class="admin-info-container">
                <i class="fas fa-user-circle admin-avatar"></i>
                <div class="admin-details">
                    <h4><?php echo htmlspecialchars($official_name); ?></h4>
                    <p><?php echo htmlspecialchars($official_email); ?></p>
                </div>
            </div>
            <button type="button" class="close-btn" onclick="toggleNavBarMenu()">&times;</button>
        </div>
        <div class="menu-drawer-body">
            <a href="official_dashboard.php"><i class="fas fa-house-chimney"></i> Dashboard</a>
            <a href="manage_residentcopy.php"><i class="fas fa-users"></i> Residents Record</a>
            <a href="admin_certificationcopy.php"><i class="fas fa-file-lines"></i> Certificates & Blotter</a>
            <a href="admin_judicialcopy.php"><i class="fas fa-gavel"></i> Case Records</a>
            <a href="admin_healthcopy.php"><i class="fas fa-notes-medical"></i> Health Center Schedule</a>
         
        </div>
    </div>

    <!-- Hero Banner -->
    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>CASE RECORDS & <span>JUDICIAL SYSTEM</span></h1>
            <p>View community dispute logs and case directories (Read-Only)</p>
        </div>
        <div style="text-align: right;">
            <div id="current-date" style="font-weight: bold; font-size: 1.2rem; color: var(--accent-gold);"></div>
            <p>Active Cases: <span style="color: #ff4444; font-weight: bold;"><?= $active_cases_count ?></span></p>
        </div>
    </header>

    <div class="container">
        <div class="section-title">
            <span>Case Management Overview</span>
            <small style="font-weight: normal; font-size: 0.8rem; color: #666;">Legal Department Records</small>
        </div>

        <div class="action-header">
            <h3 style="color: var(--primary-blue); margin: 0;">Case Directory</h3>
            <span style="font-size: 0.85rem; color: #666;"><i class="fa fa-info-circle"></i> Official Access: View-Only Mode</span>
        </div>

        <table class="case-table">
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Respondent</th>
                    <th>Type</th>
                    <th>Next Hearing</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cases)): ?>
                    <?php foreach ($cases as $case): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($case['case_id']) ?></strong></td>
                            <td><?= htmlspecialchars($case['respondent_name']) ?></td>
                            <td><?= htmlspecialchars($case['nature_of_dispute']) ?></td>
                            <td><?= $case['hearing_date'] ? date('M d, Y', strtotime($case['hearing_date'])) : 'Unscheduled' ?></td>
                            <td>
                                <?php
                                    $currentStatus = trim($case['status']);
                                ?>
                                <span class="case-status">
                                    <?= htmlspecialchars($currentStatus) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" 
                                    data-case-id="<?= htmlspecialchars($case['case_id']) ?>"
                                    data-respondent="<?= htmlspecialchars($case['respondent_name']) ?>"
                                    data-nature="<?= htmlspecialchars($case['nature_of_dispute']) ?>"
                                    data-details="<?= htmlspecialchars($case['details'] ?? '') ?>"
                                    data-hearing="<?= htmlspecialchars($case['hearing_date'] ?? '') ?>"
                                    data-status="<?= htmlspecialchars($currentStatus) ?>"
                                    data-created="<?= htmlspecialchars($case['created_at'] ?? 'N/A') ?>"
                                    onclick="openCaseModal(this)">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No cases found in the database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Read-Only Case Details Modal Popup -->
    <div id="caseModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2 style="color: var(--primary-blue); margin-top: 0; margin-bottom: 10px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                <i class="fa fa-folder-open"></i> Case Details (View-Only)
            </h2>
            <div class="detail-grid">
                <div class="detail-group">
                    <label>Case ID</label>
                    <input type="text" class="detail-input" id="modal-case-id" readonly>
                </div>
                <div class="detail-group full-width">
                    <label>Respondent Name</label>
                    <input type="text" class="detail-input" id="modal-respondent" readonly>
                </div>
                <div class="detail-group full-width">
                    <label>Nature of Dispute / Type</label>
                    <input type="text" class="detail-input" id="modal-nature" readonly>
                </div>
                <div class="detail-group full-width">
                    <label>Case Details / Incident Description</label>
                    <textarea class="detail-input" id="modal-details" rows="3" readonly></textarea>
                </div>
                <div class="detail-group">
                    <label>Next Hearing Date</label>
                    <input type="text" class="detail-input" id="modal-hearing" readonly>
                </div>
                <div class="detail-group">
                    <label>Case Status</label>
                    <input type="text" class="detail-input" id="modal-status" readonly>
                </div>
                <div class="detail-group full-width">
                    <label>Date Filed / Created At</label>
                    <input type="text" class="detail-input" id="modal-created" readonly>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancelModalBtn">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerHTML = new Date().toLocaleDateString(undefined, options);

        const modal = document.getElementById('caseModal');
        const closeModalBtn = document.querySelector('.close-modal');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const globalOverlay = document.getElementById('globalOverlay');

        function toggleNavBarMenu() {
            const menuDrawer = document.getElementById('menuSlideDrawer');
            if (menuDrawer.style.display === 'flex') {
                menuDrawer.style.display = 'none';
                globalOverlay.style.display = 'none';
            } else {
                globalOverlay.style.display = 'block';
                menuDrawer.style.display = 'flex';
            }
        }

        function closeAllModals() {
            document.getElementById('menuSlideDrawer').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        function openCaseModal(button) {
            document.getElementById('modal-case-id').value = button.getAttribute('data-case-id');
            document.getElementById('modal-respondent').value = button.getAttribute('data-respondent');
            document.getElementById('modal-nature').value = button.getAttribute('data-nature');
            document.getElementById('modal-details').value = button.getAttribute('data-details');
            
            const hearingDate = button.getAttribute('data-hearing');
            document.getElementById('modal-hearing').value = hearingDate ? hearingDate : 'Unscheduled';
            
            document.getElementById('modal-status').value = button.getAttribute('data-status');
            document.getElementById('modal-created').value = button.getAttribute('data-created');
            
            modal.style.display = "block";
        }

        function closeModal() { 
            modal.style.display = "none"; 
        }

        closeModalBtn.onclick = closeModal;
        cancelModalBtn.onclick = closeModal;
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>