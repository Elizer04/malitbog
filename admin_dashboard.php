<?php
// admin_dashboard.php

// Database Connection Configuration
$host = 'localhost';
$dbname = 'bmis'; // Updated to match your database name from other files
$username = 'root';             
$password = '';                 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Count All Residents (combining counts or querying total records)
    $stmtResidents = $pdo->query("SELECT COUNT(*) FROM residents");
    $totalResidents = $stmtResidents->fetchColumn();

    // 2. Count Processed This Week across certification tables or main certifications
    $stmtProcessed = $pdo->query("SELECT COUNT(*) FROM (
        SELECT created_at FROM cert_of_residency WHERE WEEK(created_at, 1) = WEEK(NOW(), 1) AND YEAR(created_at) = YEAR(NOW())
        UNION ALL
        SELECT created_at FROM cert_of_indigency WHERE WEEK(created_at, 1) = WEEK(NOW(), 1) AND YEAR(created_at) = YEAR(NOW())
        UNION ALL
        SELECT created_at FROM barangay_clearance WHERE WEEK(created_at, 1) = WEEK(NOW(), 1) AND YEAR(created_at) = YEAR(NOW())
    ) AS processed_week");
    $processedThisWeek = $stmtProcessed->fetchColumn();

    // 3. Count Active Cases (Blotter cases where status is not 'Settled')
    $stmtActiveCases = $pdo->query("SELECT COUNT(*) FROM cases WHERE TRIM(status) != 'Settled'");
    $activeCases = $stmtActiveCases->fetchColumn();

    // 4. Count Appointments Today (Health center appointments scheduled for the current date)
    $stmtAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appt_date) = CURDATE()");
    $appointmentsToday = $stmtAppointments->fetchColumn();

    // Optional: Get pending certification/document requests for badge counts
    $stmtPendingRes = $pdo->query("SELECT COUNT(*) FROM cert_of_residency WHERE status = 'Pending'");
    $pendingInd = $pdo->query("SELECT COUNT(*) FROM cert_of_indigency WHERE status = 'Pending'");
    $pendingClear = $pdo->query("SELECT COUNT(*) FROM barangay_clearance WHERE status = 'Pending'");
    $pendingCount = $stmtPendingRes->fetchColumn() + $pendingInd->fetchColumn() + $pendingClear->fetchColumn();

} catch (PDOException $e) {
    // Fallback values if database connection fails
    $totalResidents = 0;
    $processedThisWeek = 0;
    $activeCases = 0;
    $appointmentsToday = 0;
    $pendingCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Malitbog</title>
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

        /* Top Header & Navigation */
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
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1100;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Back Arrow Styling for Logo Edge */
        .back-link {
            color: var(--primary-blue);
            font-size: 1.2rem;
            text-decoration: none;
            margin-right: 5px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--accent-gold);
        }

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
        .logo-text small { font-size: 0.7rem; color: #555; display: block; }

        /* Unified Certification Symbol Design Integration */
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
        .rd-menu-symbol-trigger i,
        .rd-nav-menu-bar-trigger i {
            font-size: 1.1rem;
        }

        .rd-nav-item:hover,
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

        .rd-nav-menu-bar-trigger {
            cursor: pointer;
            position: relative;
        }

        .rd-nav-item.active {
            color: var(--accent-gold);
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
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .admin-info-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            font-size: 2.2rem;
            color: var(--accent-gold);
        }

        .admin-details h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        .admin-details p {
            margin: 3px 0 0 0;
            font-size: 0.8rem;
            color: #d1d5db;
        }

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

        /* Hero Banner for Admin */
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

        /* Admin Stats Cards */
        .container {
            max-width: 1200px;
            margin: -30px auto 40px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
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

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .service-card {
            border: 1px solid #eee;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            transition: 0.3s;
            position: relative;
        }

        .service-card:hover { 
            border-color: var(--accent-gold); 
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .service-card i {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }

        .btn-track {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            margin-top: 15px;
            text-transform: uppercase;
            transition: background 0.3s;
        }

        .btn-track:hover {
            background: #000044;
        }

        .badge {
            background: var(--admin-red);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            position: absolute;
            top: 15px;
            right: 15px;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .top-bar { display: none; }
            .main-nav { padding: 8px 12px; }
            .nav-links { gap: 15px; }
            .rd-nav-item span,
            .rd-menu-symbol-trigger span,
            .rd-nav-menu-bar-trigger span {
                display: none;
            }
            .menu-slide-drawer {
                max-width: 50% !important;
                width: 50% !important;
            }
            .hero-banner { padding: 40px 5%; flex-direction: column; text-align: center; gap: 20px; }
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
            <span><i class="fa fa-user-shield"></i> Administrator Access</span>
        </div>
    </div>

    <nav class="main-nav">
        <div class="logo-section">
            <a href="#" id="backBtn" class="back-link" title="Go Back">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>ADMINISTRATIVE PANEL</small>
            </div>
        </div>
        <div class="nav-links">
            <!-- Home Symbol Design Applied -->
            <a href="admin_dashboard.php" class="rd-nav-item custom-home-style active" title="Admin Home">
                <i class="fas fa-home"></i>
                <span>home</span>
            </a>

            <!-- Slide-in Menu Bar Trigger -->
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
                    <h4>Barangay Administrator</h4>
                    <p>admin@malitbog.gov.ph</p>
                </div>
            </div>
            <button type="button" class="close-btn" onclick="toggleNavBarMenu()">&times;</button>
        </div>
        <div class="menu-drawer-body">
            <a href="barangay_profileadmin.php"><i class="fas fa-university"></i> Barangay Info</a>
            <a href="change_password_admin.php"><i class="fas fa-key"></i> Change Password</a>
            <!-- Logout Button with id="logoutBtn" to trigger the modal dialog box -->
            <a href="javascript:void(0);" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>WELCOME <span>ADMIN</span></h1>
            <p>Managing Barangay Malitbog</p>
        </div>
        <div style="text-align: right;">
            <div id="current-date" style="font-weight: bold; font-size: 1.2rem; color: var(--accent-gold);"></div>
            <p>System Status: <span style="color: #00ff00;">Online</span></p>
        </div>
    </header>

    <div class="container">
        <div class="section-title">
            <span>Management Overview</span>
            <small style="font-weight: normal; font-size: 0.8rem; color: #666;">Real-time Data</small>
        </div>
        
        <div class="services-grid">
            <!-- 1. All Residents Overview -->
            <div class="service-card">
                <i class="fa fa-users"></i>
                <div style="font-weight: bold; color: #666;">TOTAL RESIDENTS</div>
                <div class="stat-number"><?php echo number_format($totalResidents); ?></div>
                <small>All residents</small>
                <a href="manage_resident.php" style="text-decoration: none;">
                    <button class="btn-track">Manage List</button>
                </a>
            </div>

            <!-- 2. Processed This Week Overview -->
            <div class="service-card">
                <?php if ($pendingCount > 0): ?>
                    <span class="badge"><?php echo $pendingCount; ?> PENDING</span>
                <?php endif; ?>
                <i class="fa fa-file-signature"></i>
                <div style="font-weight: bold; color: #666;">CERTIFICATIONS</div>
                <div class="stat-number"><?php echo number_format($processedThisWeek); ?></div>
                <small>Processed this week</small>
                <a href="admincertification_overview.php" style="text-decoration: none;">
                    <button class="btn-track">Review Requests</button>
                </a>
            </div>

            <!-- 3. Active Case Overview -->
            <div class="service-card">
                <i class="fa fa-gavel"></i>
                <div style="font-weight: bold; color: #666;">BLOTTER CASES</div>
                <div class="stat-number"><?php echo number_format($activeCases); ?></div>
                <small>Active Cases</small>
                <a href="admin_judicial.php" style="text-decoration: none;">
                    <button class="btn-track">Case Records</button>
                </a>
            </div>

            <!-- 4. Appointments Today Overview -->
            <div class="service-card">
                <i class="fa fa-notes-medical"></i>
                <div style="font-weight: bold; color: #666;">HEALTH CENTER</div>
                <div class="stat-number"><?php echo number_format($appointmentsToday); ?></div>
                <small>Appointments Today</small>
                <a href="admin_health.php" style="text-decoration: none;">
                    <button class="btn-track">View Schedule</button>
                </a>
            </div>
        </div>
    </div>

    <!-- System Notice Modal for Back Arrow / Logout -->
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

    <script>
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerHTML = new Date().toLocaleDateString(undefined, options);

        // Slide-in Menu Drawer Logic matching Profile Page
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
            document.getElementById('logoutModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        // Back Button System Notice Modal Logic
        const backBtn = document.getElementById('backBtn');
        const logoutModal = document.getElementById('logoutModal');

        if (backBtn) {
            backBtn.addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.style.display = 'flex';
                globalOverlay.style.display = 'block';
            });
        }

        // Logout Button Modal Logic (Matched with profile page)
        const logoutBtn = document.getElementById('logoutBtn');

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