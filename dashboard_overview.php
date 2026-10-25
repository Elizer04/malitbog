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
$resident_email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$user_full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'irene moros';
$full_name = $user_full_name;

// 3. Fetch metrics & report history dynamically from 'my_cases' table
try {
    // Total cases filed by this resident in my_cases
    $stmtTotalCases = $pdo->prepare("SELECT COUNT(*) FROM my_cases WHERE resident_id = ?");
    $stmtTotalCases->execute([$resident_id]);
    $total_blotter_reports = $stmtTotalCases->fetchColumn();

    // Active cases where resident_id matches and status is not resolved/dismissed/settled/closed
    $stmtActive = $pdo->prepare("SELECT COUNT(*) FROM my_cases WHERE resident_id = ? AND status NOT IN ('Resolved', 'Dismissed', 'Settled', 'Closed')");
    $stmtActive->execute([$resident_id]);
    $active_cases = $stmtActive->fetchColumn();

    // Resolved cases where resident_id matches and status is resolved/settled/closed
    $stmtResolved = $pdo->prepare("SELECT COUNT(*) FROM my_cases WHERE resident_id = ? AND status IN ('Resolved', 'Settled', 'Closed')");
    $stmtResolved->execute([$resident_id]);
    $resolved_cases = $stmtResolved->fetchColumn();

    // Cases reported today by this resident in my_cases
    $stmtToday = $pdo->prepare("SELECT COUNT(*) FROM my_cases WHERE resident_id = ? AND DATE(created_at) = CURDATE()");
    $stmtToday->execute([$resident_id]);
    $reported_incidents_today = $stmtToday->fetchColumn();

    // Fetch User Case History from my_cases table
    $stmtHistory = $pdo->prepare("SELECT id, case_id, type_of_incident, incident_date, status, created_at FROM my_cases WHERE resident_id = ? ORDER BY created_at DESC");
    $stmtHistory->execute([$resident_id]);
    $report_history = $stmtHistory->fetchAll();

} catch (Exception $e) {
    $total_blotter_reports = 0;
    $active_cases = 0;
    $resolved_cases = 0;
    $reported_incidents_today = 0;
    $report_history = [];
}

// Calculate dynamic status bar widths based on counts
$max_metric = max($total_blotter_reports, $active_cases, $resolved_cases, $reported_incidents_today, 1);
$blotter_fill_width = ($total_blotter_reports / $max_metric) * 100;
$active_fill_width  = ($active_cases / $max_metric) * 100;
$resolved_fill_width = ($resolved_cases / $max_metric) * 100;
$today_fill_width   = ($reported_incidents_today / $max_metric) * 100;

// 4. Fetch all messages linked strictly to this resident's ID only
$admin_messages = []; 
$unread_count = 0;

if (isset($conn) && $resident_id) {
    $msg_query = "
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
    
    if ($stmt_msg = $conn->prepare($msg_query)) {
        $stmt_msg->bind_param("sssss", $resident_id, $resident_id, $resident_id, $resident_id, $resident_id);
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
                
                if (!$row_msg['is_read']) { $unread_count++; }
                
                $admin_messages[] = $row_msg;
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
    <title>Dashboard Overview - Barangay Malitbog</title>
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

        .metric-value-display {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a1a8c;
            margin: 5px 0;
        }

        .report-table-container {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
            overflow-x: auto;
        }

        .report-history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            text-align: left;
        }

        .report-history-table th, .report-history-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eaeaea;
            font-size: 0.9rem;
        }

        .report-history-table th {
            background-color: #f8f9fa;
            color: #000066;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        .badge-resolved { background-color: #d4edda; color: #155724; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-active { background-color: #cce5ff; color: #004085; }
        .badge-default { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body class="rd-dashboard-page">

    <!-- Main Navigation Bar -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="resident_dashboard.php" class="rd-back-link" title="Back to Resident Dashboard">
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
        </div>
    </nav>

    <!-- Hero Banner -->
    <header class="rd-hero-banner">
        <div class="rd-welcome-msg">
            <h1>DASHBOARD <span>OVERVIEW</span></h1>
            <p>Welcome, <strong><?php echo htmlspecialchars($user_full_name); ?></strong> | <?php echo $current_date; ?></p>
        </div>
        <div class="rd-urgent-notices">
            <strong>SYSTEM STATUS</strong>
            <ul>
                <li><strong>Tracking Active Cases & My Cases Records</strong></li>
                <li>Barangay Malitbog Judicial & Incident Monitor</li>
            </ul>
        </div>
    </header>

    <!-- Metrics Cards Grid Section -->
    <div class="rd-container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; margin-top: 30px;">
            <h2 class="rd-section-title" style="margin-bottom: 0;">Case Management Metrics</h2>
            <a href="judicial_system.php" class="rd-btn-link" style="text-decoration: none;">
                <button type="button" class="rd-btn-track rd-btn-blue" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus-circle"></i> File New Case
                </button>
            </a>
        </div>

        <div class="rd-services-grid">
            <!-- Total Cases Card -->
            <div class="rd-service-card">
                <i class="fas fa-copy"></i>
                <div class="rd-card-title">TOTAL CASES</div>
                <div class="metric-value-display" id="metric-blotter"><?= $total_blotter_reports ?></div>
                <div class="rd-status-bar"><div class="rd-status-fill rd-fill-ready" id="bar-blotter" style="width: <?= $blotter_fill_width ?>%;"></div></div>
                <a href="blotter_report.php" class="rd-btn-link">
                    <button class="rd-btn-track rd-btn-blue">VIEW ALL</button>
                </a>
            </div>

            <!-- Active Cases Card -->
            <div class="rd-service-card">
                <i class="fas fa-briefcase"></i>
                <div class="rd-card-title">ACTIVE CASES</div>
                <div class="metric-value-display" id="metric-active"><?= $active_cases ?></div>
                <div class="rd-status-bar"><div class="rd-status-fill rd-fill-judicial" id="bar-active" style="width: <?= $active_fill_width ?>%;"></div></div>
                <a href="active_cases.php" class="rd-btn-link">
                    <button class="rd-btn-track">TRACK CASES</button>
                </a>
            </div>

            <!-- Resolved Cases Card -->
            <div class="rd-service-card">
                <i class="fas fa-check-circle"></i>
                <div class="rd-card-title">RESOLVED CASES</div>
                <div class="metric-value-display" id="metric-resolved"><?= $resolved_cases ?></div>
                <div class="rd-status-bar"><div class="rd-status-fill rd-fill-health" id="bar-resolved" style="width: <?= $resolved_fill_width ?>%;"></div></div>
                <a href="resolved_cases.php" class="rd-btn-link">
                    <button class="rd-btn-track">VIEW RESOLVED</button>
                </a>
            </div>

            <!-- Incidents Today Card -->
            <div class="rd-service-card">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="rd-card-title">CASES TODAY</div>
                <div class="metric-value-display" id="metric-today"><?= $reported_incidents_today ?></div>
                <div class="rd-status-bar"><div class="rd-status-fill rd-fill-ready" id="bar-today" style="width: <?= $today_fill_width ?>%;"></div></div>
                <a href="incidents_today.php" class="rd-btn-link">
                    <button class="rd-btn-track rd-btn-blue">CHECK TODAY</button>
                </a>
            </div>
        </div>

        <!-- My Cases History Table Section -->
        <div class="report-table-container">
            <h3 style="color: #000066; margin-top: 0; font-size: 1.1rem;"><i class="fas fa-history"></i> My Cases Table Records</h3>
            <?php if (!empty($report_history)): ?>
                <table class="report-history-table">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Type of Incident</th>
                            <th>Incident Date</th>
                            <th>Date Filed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_history as $row): ?>
                            <?php 
                                $status_val = strtolower(trim($row['status']));
                                $badge_class = 'badge-default';
                                if (in_array($status_val, ['resolved', 'settled', 'closed'])) {
                                    $badge_class = 'badge-resolved';
                                } elseif (in_array($status_val, ['pending', 'mediation stage'])) {
                                    $badge_class = 'badge-pending';
                                } else {
                                    $badge_class = 'badge-active';
                                }
                            ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars(!empty($row['case_id']) ? $row['case_id'] : $row['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars(!empty($row['type_of_incident']) ? $row['type_of_incident'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(!empty($row['incident_date']) ? date('M d, Y', strtotime($row['incident_date'])) : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($row['created_at']))); ?></td>
                                <td>
                                    <span class="badge-status <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars(!empty($row['status']) ? ucfirst($row['status']) : 'Pending'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; color: #888; padding: 30px 0;">
                    <i class="fas fa-folder-open" style="font-size: 2.5rem; margin-bottom: 10px; color: #ccc;"></i>
                    <p>No records found in the my cases table.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
<?php 
if (isset($conn)) { $conn->close(); }
?>