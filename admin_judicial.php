<?php
session_start();

include 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'update_status') {
        $id = intval($_POST['id']);
        $new_status = $_POST['status'] ?? '';

        $stmt = $conn->prepare("UPDATE my_cases SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);

        if ($stmt->execute()) { echo json_encode(['status' => 'success', 'message' => 'Status updated successfully.']); } else { echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']); } $stmt->close();
        exit;
    }

    if ($_POST['action'] === 'delete_case') {
        $id = intval($_POST['id']);

        $stmt =$conn->prepare("DELETE FROM my_cases WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) { echo json_encode(['status' => 'success', 'message' => 'Blotter report deleted successfully.']); } else { echo json_encode(['status' => 'error', 'message' => 'Failed to delete report.']); }$stmt->close();
        exit;
    }
}

// -------------------------------------------------------------
// 2. FETCH CASES WITH FILTERS
// -------------------------------------------------------------
$search =$_GET['search'] ?? '';
$status_filter =$_GET['status'] ?? '';

$sql = "SELECT * FROM my_cases WHERE 1=1";
$params = [];$types = "";

if (!empty($search)) {$sql .= " AND (case_id LIKE ? OR reporter_name LIKE ? OR respondent_name LIKE ? OR type_of_incident LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] =$searchTerm;
    $params[] =$searchTerm;
    $params[] =$searchTerm;
    $params[] =$searchTerm;
    $types .= "ssss";
}

if (!empty($status_filter)) {$sql .= " AND status = ?";
    $params[] =$status_filter;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {$stmt->bind_param($types, ...$params);
}
$stmt->execute();$result = $stmt->get_result();$cases = $result->fetch_all(MYSQLI_ASSOC);$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Blotter Report Management - Barangay Malitbog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #000066;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --border-color: #e2e8f0;
            --text-dark: #1e293b;
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
            color: var(--text-dark);
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

        /* Container Layout */
        .container {
            max-width: 1300px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #0f172a;
        }

        /* Filter Section */
        .filter-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-card input[type="text"], .filter-card select {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            background: white;
        }

        .filter-card input[type="text"] { flex: 1; min-width: 200px; }

        .btn-filter {
            background: var(--primary-blue);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-filter:hover { background: #000044; }

        /* Table Styling */
        .table-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }

        th {
            background: #f8fafc;
            color: #475569;
            padding: 14px 16px;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        tr:hover { background: #f8fafc; }

        /* Action Buttons */
        .btn-action-icon {
            border: none;
            background: none;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 5px;
            margin-right: 5px;
            color: #64748b;
            transition: color 0.2s;
        }
        .btn-action-icon:hover { color: var(--primary-blue); }
        .btn-delete:hover { color: #dc2626; }

        /* Modal Styles */
        .modal {
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
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 25px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .close-modal, .close-btn {
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            background: none;
            border: none;
        }
        .close-modal:hover, .close-btn:hover { color: #333; }

        .modal-body-content { padding: 5px 0; font-size: 0.9rem; color: #333; line-height: 1.4; }
        .modal-footer {
            margin-top: 15px; text-align: right; padding-top: 12px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px;
        }
        .btn-modal-action { background: var(--primary-blue); color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-action:hover { background: #1a1a8c; }
        .btn-modal-cancel { background: #ccc; color: #333; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .btn-modal-cancel:hover { background: #b3b3b3; }

        .detail-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-blue);
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-group {
            margin-bottom: 12px;
        }

        .detail-group label {
            font-weight: 600;
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            display: block;
        }

        .detail-group p {
            margin: 4px 0 0 0;
            font-size: 0.9rem;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
        
        ul.modal-list {
            margin: 4px 0 0 0;
            padding-left: 20px;
            font-size: 0.9rem;
            background: #f8fafc;
            padding: 8px 20px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .evidence-item-container {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 10px;
        }
        .evidence-img-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .evidence-img-preview:hover {
            transform: scale(1.03);
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

    <!-- Main Navigation Bar with Design Consistency -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="admin_dashboard.php" id="backBtn" class="back-link" title="Go Back">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>ADMINISTRATOR PANEL - BLOTTER MANAGEMENT</small>
            </div>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="rd-nav-item custom-home-style" title="Admin Home">
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

    <div class="container">
        <div class="page-header">
            <h2>Blotter Reports Management</h2>
        </div>

        <!-- Filter / Search Form -->
        <form method="GET" class="filter-card">
            <input type="text" name="search" placeholder="Search Case ID, Reporter, Respondent..." value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="Draft" <?= $status_filter === 'Draft' ? 'selected' : '' ?>>Draft</option>
                <option value="Mediation Stage" <?= $status_filter === 'Mediation Stage' ? 'selected' : '' ?>>Mediation Stage</option>
                <option value="Resolved" <?= $status_filter === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="Dismissed" <?= $status_filter === 'Dismissed' ? 'selected' : '' ?>>Dismissed</option>
            </select>
            <button type="submit" class="btn-filter"><i class="fa fa-search"></i> Filter</button>
        </form>

        <!-- Blotter Records Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Case ID</th>
                        <th>Complainant / Reporter</th>
                        <th>Respondent</th>
                        <th>Incident Type</th>
                        <th>Incident Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($cases) > 0): ?>
                        <?php foreach ($cases as$case): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($case['case_id']) ?></strong></td>
                                <td><?= htmlspecialchars($case['reporter_name']) ?></td>
                                <td><?= htmlspecialchars($case['respondent_name'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($case['type_of_incident'] ?: 'Unspecified') ?></td>
                                <td><?= htmlspecialchars($case['incident_date'] ?: 'N/A') ?></td>
                                <td>
                                    <select onchange="updateCaseStatus(<?= $case['id'] ?>, this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; border: 1px solid #cbd5e1;">
                                        <option value="Mediation Stage" <?= $case['status'] === 'Mediation Stage' ? 'selected' : '' ?>>Mediation Stage</option>
                                        <option value="Resolved" <?= $case['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                        <option value="Dismissed" <?= $case['status'] === 'Dismissed' ? 'selected' : '' ?>>Dismissed</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn-action-icon" title="View Full Details" onclick='openViewModal(<?= json_encode($case) ?>)'>
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn-action-icon btn-delete" title="Delete Case" onclick="deleteCase(<?= $case['id'] ?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">No blotter reports found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalCaseId" style="margin: 0; color: var(--primary-blue);">Case Details</h3>
                <span class="close-modal" onclick="closeViewModal()">&times;</span>
            </div>
            <div id="modalBody">
                <!-- Case details loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Logout / Notice Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content" style="max-width: 360px;">
            <span class="close-btn" onclick="document.getElementById('logoutModal').style.display='none'" style="position: absolute; right: 15px; top: 12px;">&times;</span>
            <div class="modal-header" style="border-bottom: 2px solid #eee; padding-bottom: 8px; margin-bottom: 15px;">
                <h3 style="margin:0; color:var(--primary-blue); font-size: 1.15rem;">System Notice</h3>
            </div>
            <div class="modal-body-content">
                <p style="margin: 0;">Are you sure you want to leave or log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('logoutModal').style.display='none'">Cancel</button>
                <a href="index.php" style="text-decoration: none;">
                    <button type="button" class="btn-modal-action">Yes</button>
                </a>
            </div>
        </div>
    </div>

    <script>
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
            document.getElementById('viewModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        // Logout Button Modal Logic
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

        // Back Button Logic
        const backBtn = document.getElementById('backBtn');
        if (backBtn) {
            backBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'admin_dashboard.php';
            });
        }

        // Update Case Status via AJAX & Save Directly to Database
        function updateCaseStatus(id, newStatus) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', newStatus);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Case status updated successfully.');
                } else {
                    alert('Failed to update status.');
                }
            })
            .catch(() => alert('Network error occurred.'));
        }

        // Delete Case via AJAX
        function deleteCase(id) {
            if (!confirm('Are you sure you want to delete this blotter report?')) return;

            const formData = new FormData();
            formData.append('action', 'delete_case');
            formData.append('id', id);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(() => alert('Network error occurred.'));
        }

        // Close View Details Modal
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
            globalOverlay.style.display = 'none';
        }

        // Open View Details Modal
        function openViewModal(data) {
            document.getElementById('modalCaseId').innerText = 'Case Details - ' + (data.case_id || 'N/A');

            let witnessesHtml = '';
            try {
                const witnesses = JSON.parse(data.witnesses || '[]');
                if (witnesses.length > 0) {
                    witnessesHtml = '<ul class="modal-list">' + witnesses.map(w => `<li><strong>${w.name || 'Unnamed'}</strong>${w.details ? ' - ' + w.details : ''}</li>`).join('') + '</ul>';
                } else {
                    witnessesHtml = '<p>None recorded</p>';
                }
            } catch(e) { witnessesHtml = '<p>None recorded</p>'; }

            let suspectsHtml = '';
            try {
                const suspects = JSON.parse(data.suspects || '[]');
                if (suspects.length > 0) {
                    suspectsHtml = '<ul class="modal-list">' + suspects.map(s => `<li><strong>${s.name || 'Unnamed'}</strong> (${s.gender || 'N/A'}, ${s.age || 'N/A'} yrs) - ${s.address || 'No address'}</li>`).join('') + '</ul>';
                } else {
                    suspectsHtml = '<p>None recorded</p>';
                }
            } catch(e) { suspectsHtml = '<p>None recorded</p>'; }

            let evidenceHtml = '';
            try {
                const evidence = JSON.parse(data.evidence || '[]');
                if (evidence.length > 0) {
                    evidenceHtml = '<div class="modal-list" style="padding: 10px;">';
                    evidence.forEach(ev => {
                        let desc = '';
                        let filePath = '';

                        if (typeof ev === 'string') {
                            if (ev.match(/\.(jpg|jpeg|png|gif|webp)$/i) || ev.includes('uploads/')) {
                                filePath = ev;
                            } else {
                                desc = ev;
                            }
                        } else if (typeof ev === 'object' && ev !== null) {
                            desc = ev.description || ev.name || '';
                            filePath = ev.file || ev.path || ev.image || ev.photo || '';
                        }

                        evidenceHtml += `<div class="evidence-item-container">`;
                        if (desc) {
                            evidenceHtml += `<span><strong>Description:</strong> ${desc}</span>`;
                        }
                        if (filePath) {
                            evidenceHtml += `<a href="${filePath}" target="_blank" title="Click to view full size">
                                                <img src="${filePath}" alt="Evidence Photo" class="evidence-img-preview">
                                             </a>`;
                        }
                        if (!desc && !filePath) {
                            evidenceHtml += `<span>${JSON.stringify(ev)}</span>`;
                        }
                        evidenceHtml += `</div>`;
                    });
                    evidenceHtml += '</div>';
                } else {
                    evidenceHtml = '<p>None recorded</p>';
                }
            } catch(e) { evidenceHtml = '<p>None recorded</p>'; }

            const modalBodyHtml = `
                <div class="detail-section-title">General Information</div>
                <div class="grid-3">
                    <div class="detail-group">
                        <label>Case ID</label>
                        <p>${data.case_id || 'N/A'}</p>
                    </div>
                    <div class="detail-group">
                        <label>Status</label>
                        <p>${data.status || 'N/A'}</p>
                    </div>
                    <div class="detail-group">
                        <label>Date Filed</label>
                        <p>${data.created_at || 'N/A'}</p>
                    </div>
                </div>

                <div class="detail-section-title">Incident Information</div>
                <div class="grid-2">
                    <div class="detail-group">
                        <label>Type of Incident</label>
                        <p>${data.type_of_incident || 'Unspecified'}</p>
                    </div>
                    <div class="detail-group">
                        <label>Incident Date & Time</label>
                        <p>${data.incident_date || 'N/A'}</p>
                    </div>
                </div>
                <div class="detail-group">
                    <label>Incident Location</label>
                    <p>${data.incident_location || 'N/A'}</p>
                </div>

                <div class="detail-section-title">Complainant / Reporter Details</div>
                <div class="grid-2">
                    <div class="detail-group">
                        <label>Reporter Name</label>
                        <p>${data.reporter_name || 'N/A'}</p>
                    </div>
                    <div class="detail-group">
                        <label>Contact Number</label>
                        <p>${data.reporter_contact || 'N/A'}</p>
                    </div>
                </div>
                <div class="detail-group">
                    <label>Reporter Address</label>
                    <p>${data.reporter_address || 'N/A'}</p>
                </div>

                <div class="detail-section-title">Respondent Details</div>
                <div class="grid-2">
                    <div class="detail-group">
                        <label>Respondent Name</label>
                        <p>${data.respondent_name || 'N/A'}</p>
                    </div>
                    <div class="detail-group">
                        <label>Respondent Contact</label>
                        <p>${data.respondent_contact || 'N/A'}</p>
                    </div>
                </div>
                <div class="detail-group">
                    <label>Respondent Address</label>
                    <p>${data.respondent_address || 'N/A'}</p>
                </div>

                <div class="detail-section-title">Parties Involved</div>
                <div class="detail-group">
                    <label>Suspects / Persons Involved</label>
                    ${suspectsHtml}
                </div>
                <div class="detail-group" style="margin-top: 10px;">
                    <label>Witnesses</label>
                    ${witnessesHtml}
                </div>

                <div class="detail-section-title">Narrative & Evidence</div>
                <div class="detail-group">
                    <label>Narrative / Incident Details</label>
                    <p style="white-space: pre-wrap; line-height: 1.5;">${data.narrative || data.incident_details || 'No narrative provided.'}</p>
                </div>
                <div class="detail-group" style="margin-top: 10px;">
                    <label>Evidence Items</label>
                    ${evidenceHtml}
                </div>
            `;

            document.getElementById('modalBody').innerHTML = modalBodyHtml;
            document.getElementById('viewModal').style.display = 'flex';
            globalOverlay.style.display = 'block';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('viewModal');
            const logoutModal = document.getElementById('logoutModal');
            if (event.target === modal) {
                closeViewModal();
            }
            if (event.target === logoutModal) {
                logoutModal.style.display = 'none';
                globalOverlay.style.display = 'none';
            }
        }
    </script>
</body>
</html>