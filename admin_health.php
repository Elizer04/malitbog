<?php
include 'db_connect.php'; 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure table for health schedule updates exists
$conn->query("CREATE TABLE IF NOT EXISTS health_schedule_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    service_title VARCHAR(255) NOT NULL,
    description TEXT,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle New Appointment Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    $resident_id     = $_POST['resident_id'];
    $service         = $_POST['service'];
    $appt_date       = $_POST['appt_date'];
    $notes           = $_POST['notes'];
    $status          = $_POST['status'];
    
    $appointment_code = 'APP-' . strtoupper(substr(uniqid(), -6));

    $insert_stmt = $conn->prepare("INSERT INTO appointments (appointment_code, resident_id, service, appt_date, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $insert_stmt->bind_param("ssssss", $appointment_code, $resident_id, $service, $appt_date, $notes, $status);
    $insert_stmt->execute();
    $insert_stmt->close();

    header("Location: " . $_SERVER['PHP_SELF'] . "?status=created");
    exit();
}

// Handle Update Form Submission & Admin Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_appointment'])) {
    $appointment_code = $_POST['appointment_code'];
    $appt_date        = $_POST['appt_date'];
    $status           = $_POST['status'];
    $remarks          = $_POST['remarks'];

    $update_stmt = $conn->prepare("UPDATE appointments SET appt_date = ?, status = ?, remarks = ? WHERE appointment_code = ?");
    $update_stmt->bind_param("ssss", $appt_date, $status, $remarks, $appointment_code);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
    exit();
}

// Handle Health Schedule Update Posting (Monday to Friday)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_schedule_update'])) {
    $day_of_week   = $_POST['day_of_week'];
    $service_title = $_POST['service_title'];
    $description   = $_POST['description'];

    $sched_stmt = $conn->prepare("INSERT INTO health_schedule_updates (day_of_week, service_title, description) VALUES (?, ?, ?)");
    $sched_stmt->bind_param("sss", $day_of_week, $service_title, $description);
    $sched_stmt->execute();
    $sched_stmt->close();

    header("Location: " . $_SERVER['PHP_SELF'] . "?status=sched_posted");
    exit();
}

// Fetch all appointments ordered by creation date
$sql = "SELECT appointment_code, service, appt_date, notes, status, created_at, remarks FROM appointments ORDER BY created_at DESC";
$result = $conn->query($sql);

$totalCount = $result ? $result->num_rows : 0;

$pendingQuery = "SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'";
$pendingResult = $conn->query($pendingQuery);
$pendingCount = $pendingResult ? $pendingResult->fetch_assoc()['count'] : 0;

$vaccinationQuery = "SELECT COUNT(*) as count FROM appointments WHERE (service LIKE '%vaccine%' OR service LIKE '%vaccination%') AND status = 'Completed'";
$vaccinationResult = $conn->query($vaccinationQuery);
$vaccinationCount = $vaccinationResult ? $vaccinationResult->fetch_assoc()['count'] : 0;

// Fetch existing health schedule updates
$scheduleQuery = "SELECT * FROM health_schedule_updates ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), posted_at DESC";
$scheduleResult = $conn->query($scheduleQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Center Management - Barangay Malitbog</title>
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

        .rd-nav-item.active { color: var(--accent-gold); }

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
        }

        .success-banner {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .section-title {
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 35px;
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

        .btn-action {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background 0.3s;
        }

        .btn-action:hover { background: #000044; }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 450px;
            max-width: 90%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        .modal-content h3 {
            margin-top: 0;
            color: var(--primary-blue);
            text-transform: uppercase;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-secondary {
            background: #ccc;
            color: #333;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
        }
        .btn-secondary:hover { background: #b3b3b3; }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            background: none;
            border: none;
        }
        .close-btn:hover { color: #000; }

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
            <a href="admin_dashboard.php" class="back-link" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>ADMINISTRATIVE PANEL</small>
            </div>
        </div>
        <div class="nav-links">
            <!-- Admin Home Navigation Link -->
            <a href="admin_dashboard.php" class="rd-nav-item custom-home-style" title="Admin Home">
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
            <a href="javascript:void(0);" id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>HEALTH CENTER <span>MANAGEMENT</span></h1>
            <p>Monitor appointments, weekly schedules, and medical supply inventory</p>
        </div>
        <div style="text-align: right;">
            <div id="current-date" style="font-weight: bold; font-size: 1.2rem; color: var(--accent-gold);"></div>
            <p>System Status: <span style="color: #00ff00;">Online</span></p>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_GET['status']) &&$_GET['status'] === 'success'): ?>
            <div class="success-banner">
                <span><i class="fas fa-check-circle"></i> Appointment successfully updated and notification message sent!</span>
                <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 1rem; cursor: pointer; color: #155724;">&times;</button>
            </div>
        <?php elseif (isset($_GET['status']) &&$_GET['status'] === 'created'): ?>
            <div class="success-banner">
                <span><i class="fas fa-check-circle"></i> New appointment successfully added to the database!</span>
                <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 1rem; cursor: pointer; color: #155724;">&times;</button>
            </div>
        <?php elseif (isset($_GET['status']) &&$_GET['status'] === 'sched_posted'): ?>
            <div class="success-banner">
                <span><i class="fas fa-check-circle"></i> Weekday service update successfully published!</span>
                <button type="button" onclick="this.parentElement.style.display='none';" style="background: none; border: none; font-size: 1rem; cursor: pointer; color: #155724;">&times;</button>
            </div>
        <?php endif; ?>

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
            <span>Monday - Friday Health Services Updates</span>
            <button class="btn-action" style="background: var(--primary-blue); color: white;" onclick="openSchedModal()">+ Post Service Update</button>
        </div>

        <table class="data-table" style="margin-bottom: 40px;">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Service Title</th>
                    <th>Description / Details</th>
                    <th>Posted At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($scheduleResult &&$scheduleResult->num_rows > 0) {
                    while ($schedRow =$scheduleResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong><i class='fas fa-calendar-day'></i> " . htmlspecialchars($schedRow['day_of_week']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($schedRow['service_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($schedRow['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($schedRow['posted_at']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No weekday service updates posted yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="section-title">
            <span>Appointment Requests</span>
            <button class="btn-action" style="background: var(--accent-gold); color: #333;" onclick="openAddModal()">+ New Record</button>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Appointment Code</th>
                    <th>Service</th>
                    <th>Appt Date</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result &&$result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {$statusClass = strtolower($row['status']) === 'pending' ? 'pending' : 'confirmed';$existingRemarks = isset($row['remarks']) ?$row['remarks'] : '';
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['appointment_code']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars(ucfirst($row['service'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['appt_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                        echo "<td><span class='status-pill " . $statusClass . "'>" . htmlspecialchars($row['status']) . "</span></td>";
                        echo "<td><button class='btn-action' onclick=\"openUpdateModal('" . htmlspecialchars($row['appointment_code'], ENT_QUOTES) . "', '" . htmlspecialchars($row['appt_date'], ENT_QUOTES) . "', '" . htmlspecialchars($row['status'], ENT_QUOTES) . "', '" . htmlspecialchars($row['service'], ENT_QUOTES) . "', '" . htmlspecialchars($existingRemarks, ENT_QUOTES) . "')\">Update</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No appointments found.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Post Health Center Weekday Service Update Modal -->
    <div id="schedModal" class="modal-overlay">
        <div class="modal-content">
            <button type="button" class="close-btn" onclick="closeSchedModal()">&times;</button>
            <h3>Post Weekday Service Update</h3>
            <form method="POST" action="">
                <input type="hidden" name="post_schedule_update" value="1">
                <div class="form-group">
                    <label for="sched_day">Day of Week (Monday - Friday)</label>
                    <select id="sched_day" name="day_of_week" required>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sched_title">Service Title</label>
                    <select id="sched_title" name="service_title" required>
                        <option value="" disabled selected>Select a health service</option>
                        <option value="General Checkup">General Checkup</option>
                        <option value="Pediatric Immunization & Consult">Pediatric Immunization & Consult</option>
                        <option value="Prenatal & Postnatal Care">Prenatal & Postnatal Care</option>
                        <option value="Dental Checkup">Dental Checkup</option>
                        <option value="Vaccination Drive">Vaccination Drive</option>
                        <option value="Family Planning Consult">Family Planning Consult</option>
                        <option value="TB DOTS Checkup">TB DOTS Checkup</option>
                        <option value="Blood Pressure & Glucose Monitoring">Blood Pressure & Glucose Monitoring</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sched_desc">Description & Instructions</label>
                    <textarea id="sched_desc" name="description" rows="4" required placeholder="Enter service availability hours, requirements, or health provider details..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeSchedModal()">Cancel</button>
                    <button type="submit" class="btn-action"><i class="fas fa-bullhorn"></i> Publish Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add New Appointment Modal Form -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-content">
            <button type="button" class="close-btn" onclick="closeAddModal()">&times;</button>
            <h3>Add New Appointment Record</h3>
            <form method="POST" action="">
                <input type="hidden" name="add_appointment" value="1">
                <div class="form-group">
                    <label for="add_resident_id">Resident ID</label>
                    <input type="text" id="add_resident_id" name="resident_id" required placeholder="Enter Resident ID">
                </div>
                <div class="form-group">
                    <label for="add_service">Service</label>
                    <input type="text" id="add_service" name="service" required placeholder="e.g., General Checkup, Vaccination">
                </div>
                <div class="form-group">
                    <label for="add_appt_date">Appointment Date</label>
                    <input type="date" id="add_appt_date" name="appt_date" required>
                </div>
                <div class="form-group">
                    <label for="add_status">Status</label>
                    <select id="add_status" name="status" required>
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="add_notes">Notes</label>
                    <textarea id="add_notes" name="notes" rows="3" placeholder="Optional notes or details..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn-action"><i class="fas fa-save"></i> Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Appointment Modal Form -->
    <div id="updateModal" class="modal-overlay">
        <div class="modal-content">
            <button type="button" class="close-btn" onclick="closeUpdateModal()">&times;</button>
            <h3>Set Appointment & Schedule</h3>
            <form method="POST" action="">
                <input type="hidden" name="update_appointment" value="1">
                <div class="form-group">
                    <label for="modal_appointment_code">Appointment Code</label>
                    <input type="text" id="modal_appointment_code" name="appointment_code" readonly style="background: #e9ecef;">
                </div>
                <div class="form-group">
                    <label for="modal_appt_date">Appointment Date</label>
                    <input type="date" id="modal_appt_date" name="appt_date" required onchange="autoGenerateMessage()">
                </div>
                <div class="form-group">
                    <label for="modal_status">Status</label>
                    <select id="modal_status" name="status" required onchange="autoGenerateMessage()">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modal_remarks">Send Message / Remarks to Resident</label>
                    <textarea id="modal_remarks" name="remarks" rows="3" placeholder="Type instructions or message for the resident..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeUpdateModal()">Cancel</button>
                    <button type="submit" class="btn-action"><i class="fas fa-paper-plane"></i> Update & Send</button>
                </div>
            </form>
        </div>
    </div>

    <!-- System Notice Modal for Logout / Back -->
    <div id="logoutModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 360px;">
            <button type="button" class="close-btn" onclick="document.getElementById('logoutModal').style.display='none'">&times;</button>
            <div style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                <h3 style="margin: 0;">System Notice</h3>
            </div>
            <div style="margin-bottom: 20px; font-size: 0.9rem; color: #333;">
                <p style="margin: 0;">Are you sure you want to leave or log out?</p>
            </div>
            <div class="modal-actions" style="margin-top: 0;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('logoutModal').style.display='none'">Cancel</button>
                <a href="index.php" style="text-decoration: none;">
                    <button type="button" class="btn-action" style="background: var(--primary-blue);">Yes</button>
                </a>
            </div>
        </div>
    </div>

    <script>
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerHTML = new Date().toLocaleDateString(undefined, options);

        let currentService = ''; 

        // Slide-in Menu Drawer Logic
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

        // Logout Button Modal Logic
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('menuSlideDrawer').style.display = 'none';
                document.getElementById('logoutModal').style.display = 'flex';
                globalOverlay.style.display = 'block';
            });
        }

        function openSchedModal() { document.getElementById('schedModal').style.display = 'flex'; }
        function closeSchedModal() { document.getElementById('schedModal').style.display = 'none'; }
        function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

        function openUpdateModal(appointmentCode, apptDate, status, service, remarks) {
            document.getElementById('modal_appointment_code').value = appointmentCode;
            document.getElementById('modal_appt_date').value = apptDate;
            currentService = service || 'medical service';
            
            const statusSelect = document.getElementById('modal_status');
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].value.toLowerCase() === status.toLowerCase()) {
                    statusSelect.selectedIndex = i;
                    break;
                }
            }

            if (remarks && remarks.trim() !== "") {
                document.getElementById('modal_remarks').value = remarks;
            } else {
                autoGenerateMessage();
            }

            document.getElementById('updateModal').style.display = 'flex';
        }

        function closeUpdateModal() { document.getElementById('updateModal').style.display = 'none'; }

        function autoGenerateMessage() {
            const status = document.getElementById('modal_status').value;
            const apptDate = document.getElementById('modal_appt_date').value;
            const remarksField = document.getElementById('modal_remarks');
            
            let formattedDate = apptDate;
            if (apptDate) {
                const dateObj = new Date(apptDate);
                if (!isNaN(dateObj)) {
                    formattedDate = dateObj.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
                }
            }

            let message = '';
            const serviceName = currentService.charAt(0).toUpperCase() + currentService.slice(1);

            switch (status.toLowerCase()) {
                case 'confirmed':
                    message = `Hello! Your appointment for ${serviceName} has been CONFIRMED for ${formattedDate}. Please arrive 10 minutes prior to your schedule at the Barangay Malitbog Health Center.`;
                    break;
                case 'completed':
                    message = `Thank you for visiting the Barangay Malitbog Health Center. Your appointment for ${serviceName} is marked COMPLETED.`;
                    break;
                case 'pending':
                default:
                    message = `Your appointment request for ${serviceName} on ${formattedDate} is currently PENDING review.`;
                    break;
            }
            remarksField.value = message;
        }

        window.onclick = function(event) {
            const schedModal = document.getElementById('schedModal');
            const addModal = document.getElementById('addModal');
            const updateModal = document.getElementById('updateModal');
            const logoutModal = document.getElementById('logoutModal');
            
            if (event.target === schedModal) schedModal.style.display = 'none';
            if (event.target === addModal) addModal.style.display = 'none';
            if (event.target === updateModal) updateModal.style.display = 'none';
            if (event.target === logoutModal) {
                logoutModal.style.display = 'none';
                globalOverlay.style.display = 'none';
            }
        }
    </script>
</body>
</html>