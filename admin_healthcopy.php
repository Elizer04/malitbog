<?php
// official_health.php
session_start();

// Security Check: Ensure user is logged in as an official
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'official') {
    header("Location: login.php");
    exit();
}

$official_name = $_SESSION['name'];
$official_email = $_SESSION['email'];

// Database connection setup[cite: 1]
$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "bmis";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch official profile information[cite: 2]
$query = "SELECT * FROM official_information WHERE email = '$official_email' LIMIT 1";
$result_official = mysqli_query($conn, $query);
$official_data = mysqli_fetch_assoc($result_official);

// Fetch all appointments ordered by creation date (View-only for officials)[cite: 1]
$sql = "SELECT appointment_code, service, appt_date, notes, status, created_at, remarks FROM appointments ORDER BY created_at DESC";
$result = $conn->query($sql);

// Calculate stats dynamically[cite: 1]
$totalCount = $result ? $result->num_rows : 0;

// Calculate Pending Consultations dynamically[cite: 1]
$pendingQuery = "SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'";
$pendingResult = $conn->query($pendingQuery);
$pendingCount = $pendingResult ? $pendingResult->fetch_assoc()['count'] : 0;

// Calculate Vaccinations Done dynamically[cite: 1]
$vaccinationQuery = "SELECT COUNT(*) as count FROM appointments WHERE (service LIKE '%vaccine%' OR service LIKE '%vaccination%') AND status = 'Completed'";
$vaccinationResult = $conn->query($vaccinationQuery);
$vaccinationCount = $vaccinationResult ? $vaccinationResult->fetch_assoc()['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Center Schedule - Barangay Malitbog Official Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #000066;
            --secondary-blue: #1a1a8c;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --admin-red: #cc0000;
            --text-gray: #555;
            --primary-dark: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
        }

        /* Top Header & Navigation */
        .top-bar {
            background: var(--secondary-blue);
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
            position: sticky;
            top: 0;
            z-index: 1100;
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

        .back-link:hover {
            color: var(--accent-gold);
        }

        .logo-section img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-gold);
        }

        .logo-text h2 {
            margin: 0;
            color: var(--primary-blue);
            font-size: 1.1rem;
            letter-spacing: 1px;
        }
        .logo-text small { font-size: 0.7rem; color: #555; display: block; }

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
        .rd-nav-menu-bar-trigger:hover {
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

        /* Slide-in Menu Drawer Styles[cite: 2] */
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

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        .close-btn:hover { color: #fff; }

        /* Hero Banner for Official Portal */
        .hero-banner {
            background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 60px 10%;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-msg h1 { margin: 0; font-size: 2.0rem; }
        .welcome-msg p { font-size: 1.1rem; margin-top: 10px; opacity: 0.9; }
        .welcome-msg span { color: var(--accent-gold); }

        /* Container & Content Boxes */
        .container {
            max-width: 1200px;
            margin: -30px auto 40px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
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

        .health-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid var(--primary-blue);
            transition: 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .stat-box h3 { margin: 0; color: #666; font-size: 0.8rem; text-transform: uppercase; }
        .stat-box p { margin: 10px 0 0; font-size: 1.5rem; font-weight: bold; color: var(--primary-blue); }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: var(--primary-blue);
            border-bottom: 2px solid #eee;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .status-pill {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
            text-transform: capitalize;
        }

        .pending { background: #fff3cd; color: #856404; }
        .confirmed { background: #e3f2fd; color: #1565c0; }
        .completed { background: #e8f5e9; color: #2e7d32; }

        .badge-viewonly {
            background: #e2e8f0;
            color: #475569;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
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
            <span><i class="fa fa-id-badge"></i> Barangay Official Portal (View-Only Access)</span>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="official_dashboard.php" class="back-link" title="Back to Official Dashboard">
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
            <h1>HEALTH CENTER <span>SCHEDULE</span></h1>
            <p>View-only health appointments and medical schedule log</p>
        </div>
        <div style="text-align: right;">
            <div id="current-date" style="font-weight: bold; font-size: 1.2rem; color: var(--accent-gold);"></div>
            <p>System Status: <span style="color: #00ff00;">Online</span></p>
        </div>
    </header>

    <div class="container">
        <!-- Health Statistics Summary (View-Only)[cite: 1] -->
        <div class="health-stats">
            <div class="stat-box">
                <h3>Total Appointments</h3>
                <p><?php echo htmlspecialchars($totalCount); ?></p>
            </div>
            <div class="stat-box" style="border-left-color: var(--accent-gold);">
                <h3>Pending Consultations</h3>
                <p><?php echo htmlspecialchars($pendingCount); ?></p>
            </div>
            <div class="stat-box" style="border-left-color: #28a745;">
                <h3>Vaccinations Done</h3>
                <p><?php echo htmlspecialchars($vaccinationCount); ?></p>
            </div>
        </div>

        <div class="section-title">
            <span>All Appointment Records (View Only)</span>
            <span class="badge-viewonly"><i class="fas fa-eye"></i> View Mode</span>
        </div>

        <!-- Appointment Table (No modification buttons)[cite: 1] -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Appointment Code</th>
                    <th>Service</th>
                    <th>Appt Date</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = strtolower($row['status']) === 'pending' ? 'pending' : 'confirmed';
                        if (strtolower($row['status']) === 'completed') {
                            $statusClass = 'completed';
                        }
                        $existingRemarks = isset($row['remarks']) ? $row['remarks'] : 'None';
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['appointment_code']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars(ucfirst($row['service'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['appt_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['notes'] ? $row['notes'] : '-') . "</td>";
                        echo "<td><span class='status-pill " . $statusClass . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                        echo "<td>" . htmlspecialchars($existingRemarks) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>No appointments found.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript for Live Date and Slide Drawer Functionality[cite: 2] -->
    <script>
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerHTML = new Date().toLocaleDateString(undefined, options);

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
    </script>
</body>
</html>