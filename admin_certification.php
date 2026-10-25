<?php
// admin_certification.php
session_start();
include 'db_connect.php'; 

// HANDLE DIRECT PRINT REQUESTS (CERTIFICATE OR RECEIPT) WITHIN THE SAME FILE
if (isset($_GET['action']) && ($_GET['action'] === 'print' || $_GET['action'] === 'print_receipt') && isset($_GET['id']) && isset($_GET['type'])) {
    $print_id = mysqli_real_escape_string($conn, $_GET['id']);
    $print_type = mysqli_real_escape_string($conn, $_GET['type']);
    $action_mode = $_GET['action'];
    
    // Determine table based on certificate type
    $table_name = "barangay_clearance";
    if ($print_type === 'residency') {
        $table_name = "cert_of_residency";
    } elseif ($print_type === 'indigency') {
        $table_name = "cert_of_indigency";
    }

    $print_query = "SELECT * FROM $table_name WHERE resident_id = '$print_id' LIMIT 1";
    $print_result = mysqli_query($conn, $print_query);
    $doc = mysqli_fetch_assoc($print_result);

    if (!$doc) {
        die("<script>alert('Document record not found.'); window.close();</script>");
    }
    
    $doc_title_display = "BARANGAY " . strtoupper($print_type);
    if ($print_type === 'residency') $doc_title_display = "CERTIFICATE OF RESIDENCY";
    if ($print_type === 'indigency') $doc_title_display = "CERTIFICATE OF INDIGENCY";
    if ($print_type === 'clearance') $doc_title_display = "BARANGAY CLEARANCE";

    // ==================== SEPARATE VIEW 1: PRINT OFFICIAL CERTIFICATE ====================
    if ($action_mode === 'print') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print - <?php echo ucwords($print_type); ?> - Barangay Malitbog</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #000;
            background: #fff;
            line-height: 1.6;
        }
        .cert-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            padding: 20px;
        }
        .cert-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .cert-header img {
            height: 70px;
            width: 70px;
            margin: 0 15px;
            vertical-align: middle;
        }
        .header-text {
            display: inline-block;
            text-align: center;
            vertical-align: middle;
        }
        .header-text p {
            margin: 2px 0;
            font-size: 0.95rem;
        }
        .office-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 20px;
            letter-spacing: 0.5px;
        }
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.5rem;
            margin: 15px 0 30px 0;
            text-decoration: underline;
            letter-spacing: 1px;
        }
        .cert-date {
            text-align: right;
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 1.05rem;
        }
        .to-whom {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 1.05rem;
        }
        .cert-body {
            text-align: justify;
            text-indent: 40px;
            font-size: 1.1rem;
            margin-bottom: 40px;
        }
        .signatures-section {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .applicant-sig, .captain-sig {
            text-align: center;
            width: 250px;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sig-label {
            font-size: 0.9rem;
        }
        .watermark-note {
            margin-top: 60px;
            font-size: 0.85rem;
            font-style: italic;
        }
        .no-print {
            text-align: center;
            margin-bottom: 30px;
        }
        .print-btn {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 10px 25px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button class="print-btn" onclick="window.print()"><i class="fa fa-print"></i> Print Official Document</button>
        <button class="print-btn" style="background:#555; margin-left:10px;" onclick="window.close()">Close Window</button>
    </div>

    <div class="cert-container">
        <div class="cert-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="width: 20%; text-align: left;"><img src="image/barangaylogo3.jpg" alt="Logo" style="height:75px; width:75px; border-radius:50%;"></div>
                <div class="header-text" style="width: 60%;">
                    <p>Republic of the Philippines</p>
                    <p>Province of Laguna</p>
                    <p>Municipality of Calamba</p>
                    <p><strong>BARANGAY MALITBOG</strong></p>
                </div>
                <div style="width: 20%; text-align: right;"><img src="image/barangaylogo3.jpg" alt="Seal" style="height:75px; width:75px; border-radius:50%;"></div>
            </div>
            
            <div class="office-title">OFFICE OF THE PUNONG BARANGAY</div>
            <div class="doc-title"><?php echo $doc_title_display; ?></div>
        </div>

        <div class="cert-date"><?php echo date('F d, Y', strtotime($doc['created_at'])); ?></div>

        <div class="to-whom">TO WHOM IT MAY CONCERN:</div>

        <div class="cert-body">
            This is to certify that <strong><?php echo strtoupper(htmlspecialchars($doc['full_name'])); ?></strong>, resident of Barangay Malitbog, Calamba, Laguna, is personally known to me and has no derogatory or criminal record filed against them in this barangay as of this date.
            <br><br>
            This certification is issued upon the request of the above-mentioned name for <strong><?php echo htmlspecialchars($doc['purpose_cat']); ?> - <?php echo htmlspecialchars($doc['purpose_detail']); ?></strong> purposes.
        </div>

        <div class="signatures-section">
            <div class="applicant-sig">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-label">(Name and Signature of Applicant)</div>
            </div>
            <div class="captain-sig">
                <div class="sig-line">HON. BRGY. CAPTAIN</div>
                <div class="sig-label">Punong Barangay</div>
            </div>
        </div>

        <div class="watermark-note">
            Note: Not Valid Without Barangay Seal and Official Dry Seal.
        </div>
    </div>
</body>
</html>
<?php
    exit;
    }

    // ==================== SEPARATE VIEW 2: PRINT OFFICIAL RECEIPT ====================
    if ($action_mode === 'print_receipt') {
        $fee_amount = ($print_type === 'indigency') ? 0.00 : 50.00;
        $or_number = "OR-" . date('Ymd', strtotime($doc['created_at'])) . "-" . strtoupper(substr($print_type, 0, 3)) . "-" . htmlspecialchars($doc['resident_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Receipt - #<?php echo htmlspecialchars($doc['resident_id']); ?> - Barangay Malitbog</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 30px;
            color: #000;
            background: #fff;
        }
        .receipt-container {
            max-width: 450px;
            margin: 0 auto;
            border: 2px dashed #000;
            padding: 25px;
            background: #fff;
        }
        .receipt-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .receipt-totals {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin: 15px 0;
        }
        .no-print {
            text-align: center;
            margin-bottom: 25px;
        }
        .print-btn {
            background: #003366;
            color: white;
            border: none;
            padding: 8px 20px;
            font-size: 0.9rem;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-container { border: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button class="print-btn" onclick="window.print()"><i class="fa fa-receipt"></i> Print Official Receipt</button>
        <button class="print-btn" style="background:#555; margin-left:10px;" onclick="window.close()">Close Window</button>
    </div>

    <div class="receipt-container">
        <div class="receipt-header">
            REPUBLIC OF THE PHILIPPINES<br>
            PROVINCE OF LAGUNA<br>
            MUNICIPALITY OF CALAMBA<br>
            <strong>BARANGAY MALITBOG</strong><br>
            OFFICE OF THE BARANGAY TREASURER<br>
            <strong>OFFICIAL TRANSACTION RECEIPT</strong>
        </div>

        <div class="receipt-row">
            <span><strong>OR No:</strong></span>
            <span><?php echo $or_number; ?></span>
        </div>
        <div class="receipt-row">
            <span><strong>Date:</strong></span>
            <span><?php echo date('F d, Y', strtotime($doc['created_at'])); ?></span>
        </div>
        <div class="receipt-row" style="margin-top: 10px;">
            <span><strong>Received From:</strong></span>
            <span><strong><?php echo strtoupper(htmlspecialchars($doc['full_name'])); ?></strong></span>
        </div>
        <div class="receipt-row">
            <span><strong>Payment For:</strong></span>
            <span>Barangay <?php echo ucwords($print_type); ?> Fee</span>
        </div>

        <div class="receipt-totals">
            <div class="receipt-row">
                <span><strong>Certification Fee:</strong></span>
                <span>₱<?php echo number_format($fee_amount, 2); ?></span>
            </div>
            <div class="receipt-row" style="font-size: 1rem; margin-top: 8px;">
                <span><strong>TOTAL AMOUNT PAID:</strong></span>
                <span><strong>₱<?php echo number_format($fee_amount, 2); ?></strong></span>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: right; font-size: 0.85rem;">
            <br><br>
            <strong>AUTHORIZED COLLECTING OFFICER</strong><br>
            <em>Barangay Treasurer / Staff</em>
        </div>
    </div>
</body>
</html>
<?php
    exit;
    }
}

// Query total counts[cite: 1]
$residency_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM cert_of_residency");
$total_residency = mysqli_fetch_assoc($residency_count_result)['total'] ?? 0;

$indigency_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM cert_of_indigency");
$total_indigency = mysqli_fetch_assoc($indigency_count_result)['total'] ?? 0;

$clearance_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barangay_clearance");
$total_clearance = mysqli_fetch_assoc($clearance_count_result)['total'] ?? 0;
$total_cert_count = $total_residency + $total_indigency + $total_clearance;

// Combine records[cite: 1]
$query = "
    SELECT resident_id, full_name, contact_no, email, 'residency' AS cert_type, purpose_cat, purpose_detail, requirement_file, status, created_at FROM cert_of_residency
    UNION ALL
    SELECT resident_id, full_name, contact_no, email, 'indigency' AS cert_type, purpose_cat, purpose_detail, requirement_file, status, created_at FROM cert_of_indigency
    UNION ALL
    SELECT resident_id, full_name, contact_no, email, 'clearance' AS cert_type, purpose_cat, purpose_detail, requirement_file, status, created_at FROM barangay_clearance
    ORDER BY created_at DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification Requests - Barangay Malitbog</title>
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
            --admin-orange: #d97706;
            --admin-green: #28a745;
            --print-green: #2e7d32;
        }

        body {
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
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
            padding: 8px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .logo-text small { font-size: 0.7rem; }

        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 30px; 
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
        .rd-menu-symbol-trigger.custom-profile-style span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            text-transform: capitalize;
            letter-spacing: 0.5px;
        }

        .rd-nav-item i, 
        .rd-menu-symbol-trigger i { font-size: 1.1rem; }

        .rd-nav-item:hover,
        .rd-menu-symbol-trigger:hover { color: var(--primary-blue); }

        .rd-nav-link-dropdown {
            display: inline-flex;
            align-items: center;
            position: relative;
        }

        .rd-nav-item.active { color: var(--accent-gold); }

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
            overflow-x: auto;
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

        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .tab-btn {
            background: #f1f3f5;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            color: #555;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover { background: #e2e6ea; }
        .tab-btn.active { background: var(--primary-blue); color: white; }

        .tab-count {
            background: rgba(0, 0, 0, 0.15);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .tab-btn.active .tab-count { background: var(--accent-gold); color: #000; }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-box {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 350px;
            font-family: inherit;
        }

        .request-table {
            width: 100%;
            border-collapse: collapse;
        }

        .request-table th {
            background: #f8f9fa;
            color: var(--primary-blue);
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid #eee;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .request-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 0.95rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
            text-transform: capitalize;
        }

        .pending { background: #fff3e0; color: #ef6c00; }
        .approved { background: #e8f5e9; color: #2e7d32; }
        .rejected { background: #ffebee; color: #c62828; }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.75rem;
            text-transform: uppercase;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 3px;
        }

        .btn-approve { background: var(--primary-blue); color: white; }
        .btn-deny { background: #eee; color: #333; }
        .btn-reject-modal { background: var(--admin-red); color: white; }
        .btn-print { background: var(--print-green); color: white; }
        .btn-approve:hover { background: #000044; }
        .btn-reject-modal:hover { background: #990000; }
        .btn-print:hover { background: #1b5e20; }
        .btn-file { background: #e3f2fd; color: #1565c0; margin-right: 5px; }

        .request-row { display: table-row; }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            margin-top: 0;
            color: var(--primary-blue);
            text-transform: uppercase;
            font-size: 1.1rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.9rem;
            box-sizing: border-box;
            font-family: inherit;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-cancel { background: #ccc; color: #333; }

        .letter-preview {
            background-color: #fcfcfc;
            border: 1px solid #dcdcdc;
            border-left: 4px solid var(--primary-blue);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #333;
            line-height: 1.5;
        }

        .letter-header {
            font-weight: bold;
            text-transform: uppercase;
            color: var(--primary-blue);
            margin-bottom: 4px;
        }

        .letter-sub {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 12px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 8px;
        }

        .receipt-preview-box {
            background: #fff;
            border: 1px dashed #000066;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85rem;
            color: #111;
        }

        .receipt-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .receipt-row-flex {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .success-message-box {
            text-align: center;
            padding: 40px 20px;
        }
        .success-message-box i {
            font-size: 3.5rem;
            color: var(--print-green);
            margin-bottom: 15px;
        }
        .success-message-box h4 {
            color: var(--primary-blue);
            margin: 0 0 10px 0;
            font-size: 1.3rem;
            text-transform: uppercase;
        }
        .success-message-box p {
            color: #666;
            font-size: 0.95rem;
            margin: 0 0 20px 0;
            line-height: 1.5;
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
            <a href="admin_dashboard.php" class="back-link" title="Go Back">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>ADMINISTRATIVE PANEL</small>
            </div>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php" class="rd-nav-item custom-home-style" title="Admin Home">
                <i class="fas fa-home"></i>
                <span>home</span>
            </a>
            <div class="rd-nav-link-dropdown">
              
           
            </div>
        </div>
    </nav>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>DOCUMENT <span>CERTIFICATIONS</span></h1>
            <p>Review and process official resident certification requests</p>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: bold; font-size: 1.2rem; color: var(--accent-gold);">
                TOTAL: <?php echo $total_cert_count; ?>
            </div>
            <p>System Status: <span style="color: #00ff00;">Online</span></p>
        </div>
    </header>

    <div class="container">
        <div class="section-title">
            <span>Certification Management</span>
            <small style="font-weight: normal; font-size: 0.8rem; color: #666;">Document Filter Queue</small>
        </div>

        <div class="action-bar">
            <input type="text" id="searchInput" class="search-box" onkeyup="filterCertifications()" placeholder="Search by name, ID, or purpose...">
        </div>

        <div class="category-tabs">
            <button class="tab-btn active" onclick="filterTab('residency', this)">
                <i class="fa fa-file-alt"></i> Residency <span class="tab-count"><?php echo $total_residency; ?></span>
            </button>
            <button class="tab-btn" onclick="filterTab('indigency', this)">
                <i class="fa fa-file-invoice"></i> Indigency <span class="tab-count"><?php echo $total_indigency; ?></span>
            </button>
            <button class="tab-btn" onclick="filterTab('clearance', this)">
                <i class="fa fa-stamp"></i> Barangay Clearance <span class="tab-count"><?php echo $total_clearance; ?></span>
            </button>
        </div>

        <table class="request-table" id="certTable">
            <thead>
                <tr>
                    <th>Resident ID</th>
                    <th>Resident Name</th>
                    <th>Contact No</th>
                    <th>Document Type</th>
                    <th>Purpose</th>
                    <th>Requirement File</th>
                    <th>Status</th>
                    <th>Date Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = htmlspecialchars($row['status']);
                        $statusClass = strtolower($status);
                        $docCategory = strtolower($row['cert_type']); 
                        $residentEmail = htmlspecialchars($row['email'] ?? '');
                ?>
                <tr class="request-row" data-doc="<?php echo $docCategory; ?>">
                    <td>#<?php echo htmlspecialchars($row['resident_id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($row['cert_type'])); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['purpose_cat']); ?></strong><br>
                        <small style="color: #666;"><?php echo htmlspecialchars($row['purpose_detail']); ?></small>
                    </td>
                    <td>
                        <?php if (!empty($row['requirement_file'])): ?>
                            <a href="<?php echo htmlspecialchars($row['requirement_file']); ?>" target="_blank" class="action-btn btn-file">
                                <i class="fa fa-eye"></i> View
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">None</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <?php if ($statusClass === 'pending'): ?>
                            <button type="button" class="action-btn btn-approve" 
                                    onclick="openApprovalModal('<?php echo $row['resident_id']; ?>', '<?php echo $docCategory; ?>', '<?php echo addslashes($row['full_name']); ?>', '<?php echo htmlspecialchars($row['contact_no']); ?>', '<?php echo $residentEmail; ?>')">
                                Approve
                            </button>
                            <button type="button" class="action-btn btn-deny" 
                                    onclick="openRejectModal('<?php echo $row['resident_id']; ?>', '<?php echo $docCategory; ?>', '<?php echo addslashes($row['full_name']); ?>', '<?php echo $residentEmail; ?>')">
                                Reject
                            </button>
                        <?php elseif ($statusClass === 'approved'): ?>
                            <a href="admin_certification.php?action=print&id=<?php echo urlencode($row['resident_id']); ?>&type=<?php echo urlencode($docCategory); ?>" 
                               target="_blank" class="action-btn btn-print">
                               <i class="fa fa-print"></i> Print Cert
                            </a>
                            <a href="admin_certification.php?action=print_receipt&id=<?php echo urlencode($row['resident_id']); ?>&type=<?php echo urlencode($docCategory); ?>" 
                               target="_blank" class="action-btn btn-print" style="background:#003366;">
                               <i class="fa fa-receipt"></i> Print Receipt
                            </a>
                        <?php elseif ($statusClass === 'rejected'): ?>
                            <button type="button" class="action-btn" style="background:#cc0000; color:white;" 
                                    onclick="openDeleteModal('<?php echo $row['resident_id']; ?>', '<?php echo $docCategory; ?>', '<?php echo addslashes($row['full_name']); ?>')">
                               <i class="fa fa-trash"></i> Delete
                            </button>
                        <?php else: ?>
                            <a href="admin_certification.php?action=print_receipt&id=<?php echo urlencode($row['resident_id']); ?>&type=<?php echo urlencode($docCategory); ?>" 
                               target="_blank" class="action-btn btn-print" style="background:#003366;">
                               <i class="fa fa-receipt"></i> Print Receipt
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr class="no-data-row">
                    <td colspan="9" style="text-align: center; color: #777;">No certification requests found.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Approval Form Modal Container -->
    <div id="approvalModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Approve & Release Details</h3>
            
            <div id="successMessageBox" style="display: none;" class="success-message-box">
                <i class="fas fa-paper-plane"></i>
                <h4>Message Sent & Receipt Dispatched!</h4>
                <p>The certification has been approved successfully, and the claim notice & transaction receipt have been sent to the resident's email.</p>
                <button type="button" class="action-btn btn-approve" onclick="closeAndReload()" style="padding: 10px 30px; font-size: 0.9rem;">OK</button>
            </div>

            <form id="approvalForm" onsubmit="handleApprovalSubmit(event)">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" id="modalResidentId">
                <input type="hidden" name="type" id="modalDocType">

                <div class="form-group">
                    <label>Resident Name</label>
                    <input type="text" id="modalResidentName" readonly style="background: #e9ecef; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label for="resident_email">Resident Email Address (For Transaction Receipt)</label>
                    <input type="email" id="resident_email" name="resident_email" placeholder="e.g., resident@example.com" required>
                </div>

                <div class="form-group">
                    <label for="or_number">Official Receipt (OR) Number </label>
                    <input type="text" id="or_number" name="or_number" readonly required style="background: #e9ecef; font-weight: bold; color: var(--primary-blue);">
                </div>

                <div class="form-group">
                    <label for="amount_paid">Amount Paid (₱) </label>
                    <input type="number" step="0.01" id="amount_paid" name="amount_paid" required style="background: #f8f9fa; font-weight: bold;" oninput="updateReceiptPreview()">
                </div>

                <div class="form-group">
                    <label for="claim_message">Message to Resident (Formal Ready-to-Claim Notice)</label>
                    <div class="letter-preview">
                        <div class="letter-header">Barangay Malitbog Administrative Office</div>
                        <div class="letter-sub">Official Certificate Release & Claim Notice</div>
                        Dear <strong id="display_modal_resident_name">[Resident Name]</strong>,<br><br>
                        <textarea id="claim_message" name="claim_message" rows="7" style="resize: vertical; border: 1px dashed #ccc; background: #fff; padding: 8px; font-family: inherit; font-size: 0.85rem;" required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label>Detailed Official Receipt Preview</label>
                    <div class="receipt-preview-box">
                        <div class="receipt-header">
                            REPUBLIC OF THE PHILIPPINES<br>
                            PROVINCE / MUNICIPALITY<br>
                            BARANGAY MALITBOG<br>
                            <strong>OFFICE OF THE BARANGAY TREASURER</strong><br>
                            OFFICIAL TRANSACTION RECEIPT
                        </div>
                        <div class="receipt-row-flex">
                            <span><strong>OR No:</strong> <span id="preview_or">[OR Number]</span></span>
                            <span><strong>Date:</strong> <span id="preview_date">--</span></span>
                        </div>
                        <div style="margin-bottom: 5px;"><strong>Received From:</strong> <span id="preview_name">[Resident Name]</span></div>
                        <div style="margin-bottom: 5px;"><strong>Payment For:</strong> Barangay <span id="preview_doc_type">[Document Type] Fee</span></div>
                        <div class="receipt-row-flex" style="border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; padding: 5px 0; margin-top: 5px;">
                            <span><strong>Total Amount Paid:</strong></span>
                            <span><strong>₱<span id="preview_amount">0.00</span></strong></span>
                        </div>
                        <div style="margin-top: 8px; text-align: right; font-size: 0.75rem;">
                            <em>Authorized Barangay Collecting Officer</em>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 10px; background: #e8f4fd; padding: 10px; border-radius: 6px;">
                    <input type="checkbox" id="send_receipt_email" name="send_receipt_email" value="1" checked style="width: auto; cursor: pointer;">
                    <label for="send_receipt_email" style="margin-bottom: 0; cursor: pointer; color: var(--primary-blue); font-weight: 600;">
                        <i class="fa fa-receipt"></i> Send Transaction Receipt details to user email
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-btn btn-cancel" onclick="closeApprovalModal()">Cancel</button>
                    <button type="submit" class="action-btn btn-approve">Confirm Release</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Confirmation & Message Modal Container -->
    <div id="rejectModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Reject Request & Send Notice</h3>
            
            <div id="rejectSuccessBox" style="display: none;" class="success-message-box">
                <i class="fas fa-paper-plane" style="color: var(--admin-red);"></i>
                <h4>Rejection Notice Sent Successfully!</h4>
                <p>The certification request has been marked as rejected, and the notice has been sent to the resident's email.</p>
                <button type="button" class="action-btn btn-reject-modal" onclick="closeRejectAndReload()" style="padding: 10px 30px; font-size: 0.9rem;">OK</button>
            </div>

            <form id="rejectForm" onsubmit="handleRejectSubmit(event)">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" id="rejectResidentId">
                <input type="hidden" name="type" id="rejectDocType">

                <div class="form-group">
                    <label>Resident Name</label>
                    <input type="text" id="rejectResidentName" readonly style="background: #e9ecef; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label for="reject_resident_email">Resident Email Address</label>
                    <input type="email" id="reject_resident_email" name="resident_email" placeholder="e.g., resident@example.com" required>
                </div>

                <div class="form-group">
                    <label for="reject_message">Message / Reason for Rejection (Formal Notice)</label>
                    <div class="letter-preview" style="border-left-color: var(--admin-red);">
                        <div class="letter-header" style="color: var(--admin-red);">Barangay Malitbog Administrative Office</div>
                        <div class="letter-sub">Official Certification Request Status: Notice of Rejection</div>
                        Dear <strong id="display_reject_resident_name">[Resident Name]</strong>,<br><br>
                        <textarea id="reject_message" name="reject_message" rows="7" style="resize: vertical; border: 1px dashed #ccc; background: #fff; padding: 8px; font-family: inherit; font-size: 0.85rem;" required></textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="action-btn btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="action-btn btn-reject-modal">Confirm Rejection & Send Notice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Form Modal Container -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 450px;">
            <h3>Delete Record Confirmation</h3>
            <p style="color: #555; font-size: 0.95rem; margin-top: 15px;">
                Are you sure you want to permanently delete this record from the database for <strong id="deleteResidentName">[Resident Name]</strong>?
            </p>
            <form id="deleteForm" action="process_certification.php" method="GET">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteResidentId">
                <input type="hidden" name="type" id="deleteDocType">

                <div class="modal-actions" style="margin-top: 25px;">
                    <button type="button" class="action-btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="action-btn" style="background:#cc0000; color:white;">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentActiveDocType = 'residency';

        function filterTab(docType, element) {
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));
            element.classList.add('active');
            currentActiveDocType = docType;

            filterCertifications();
        }

        function filterCertifications() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('certTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                if (tr[i].classList.contains('no-data-row')) continue;

                const docAttr = tr[i].getAttribute('data-doc');
                const tdId = tr[i].getElementsByTagName('td')[0];
                const tdName = tr[i].getElementsByTagName('td')[1];
                const tdPurpose = tr[i].getElementsByTagName('td')[4];

                let matchesTab = (docAttr === currentActiveDocType);

                if (matchesTab) {
                    if (tdId || tdName || tdPurpose) {
                        const idVal = tdId ? (tdId.textContent || tdId.innerText) : '';
                        const nameVal = tdName ? (tdName.textContent || tdName.innerText) : '';
                        const purposeVal = tdPurpose ? (tdPurpose.textContent || tdPurpose.innerText) : '';

                        if (
                            idVal.toUpperCase().indexOf(filter) > -1 ||
                            nameVal.toUpperCase().indexOf(filter) > -1 ||
                            purposeVal.toUpperCase().indexOf(filter) > -1
                        ) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        function openApprovalModal(residentId, docType, residentName, contactNo, residentEmail) {
            document.getElementById('successMessageBox').style.display = 'none';
            document.getElementById('approvalForm').style.display = 'block';
            document.getElementById('approvalForm').reset();

            document.getElementById('modalResidentId').value = residentId;
            document.getElementById('modalDocType').value = docType;
            document.getElementById('modalResidentName').value = residentName;
            document.getElementById('display_modal_resident_name').innerText = residentName;

            document.getElementById('preview_name').innerText = residentName;
            document.getElementById('preview_doc_type').innerText = docType.toUpperCase();
            document.getElementById('preview_date').innerText = new Date().toLocaleDateString();

            if (residentEmail && residentEmail.trim() !== '') {
                document.getElementById('resident_email').value = residentEmail;
            } else {
                const cleanName = residentName.toLowerCase().replace(/[^a-z0-9]/g, '');
                document.getElementById('resident_email').value = `${cleanName}${residentId}@gmail.com`;
            }

            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const randomSeq = Math.floor(1000 + Math.random() * 9000);
            const docPrefix = docType.substring(0, 3).toUpperCase();
            
            const autoOrNumber = `OR-${year}${month}${day}-${docPrefix}-${residentId}-${randomSeq}`;
            document.getElementById('or_number').value = autoOrNumber;
            document.getElementById('preview_or').innerText = autoOrNumber;

            let autoAmount = '50.00';
            if (docType === 'indigency') {
                autoAmount = '0.00';
            } else if (docType === 'clearance' || docType === 'residency') {
                autoAmount = '50.00';
            }
            document.getElementById('amount_paid').value = autoAmount;
            document.getElementById('preview_amount').innerText = parseFloat(autoAmount).toFixed(2);

            const formalNoticeText = `Warm greetings from the Office of the Punong Barangay, Barangay Malitbog!\n\n` +
                `We are pleased to formally notify you that your application for an official Barangay ${docType.toUpperCase()} has been thoroughly reviewed, verified, and APPROVED by our administrative office.\n\n` +
                `Your requested document is now ready for physical release. You may claim your official certificate personally at the Barangay Malitbog Hall during regular operating hours (Monday to Friday, 8:00 AM to 5:00 PM).\n\n` +
                `Important Reminders & Requirements upon Claiming:\n` +
                `1. Present a valid government-issued photo ID or institutional ID for proper identity verification.\n` +
                `2. Present this official email notice/transaction copy along with your Official Receipt Reference Number (${autoOrNumber}).\n\n` +
                `Thank you for your cooperation and adherence to our local barangay protocols. Should you have any questions or require further assistance, please feel free to reach out to our administrative staff.`;

            document.getElementById('claim_message').value = formalNoticeText;
            document.getElementById('approvalModal').style.display = 'flex';
        }

        function openRejectModal(residentId, docType, residentName, residentEmail) {
            document.getElementById('rejectSuccessBox').style.display = 'none';
            document.getElementById('rejectForm').style.display = 'block';
            document.getElementById('rejectForm').reset();

            document.getElementById('rejectResidentId').value = residentId;
            document.getElementById('rejectDocType').value = docType;
            document.getElementById('rejectResidentName').value = residentName;
            document.getElementById('display_reject_resident_name').innerText = residentName;

            if (residentEmail && residentEmail.trim() !== '') {
                document.getElementById('reject_resident_email').value = residentEmail;
            } else {
                const cleanName = residentName.toLowerCase().replace(/[^a-z0-9]/g, '');
                document.getElementById('reject_resident_email').value = `${cleanName}${residentId}@gmail.com`;
            }

            const formalRejectText = `Warm greetings from the Office of the Punong Barangay, Barangay Malitbog!\n\n` +
                `We regret to inform you that your application for an official Barangay ${docType.toUpperCase()} could not be approved at this time due to the following reason / incomplete requirements:\n\n` +
                `[Please specify the reason or missing requirement here, e.g., Unclear supporting document, residency verification pending, etc.]\n\n` +
                `You are welcome to re-submit your request with the complete requirements or visit the Barangay Hall for further clarification during regular operating hours (Monday to Friday, 8:00 AM to 5:00 PM).\n\n` +
                `Thank you for your understanding.`;

            document.getElementById('reject_message').value = formalRejectText;
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        function closeRejectAndReload() {
            document.getElementById('rejectModal').style.display = 'none';
            location.reload();
        }

        function handleRejectSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('rejectForm');
            const formData = new FormData(form);

            fetch('process_certification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                form.style.display = 'none';
                document.getElementById('rejectSuccessBox').style.display = 'block';
            })
            .catch(error => {
                console.error('Error saving rejection details:', error);
                alert('An error occurred while processing the rejection. Please try again.');
            });
        }

        function openDeleteModal(residentId, docType, residentName) {
            document.getElementById('deleteResidentId').value = residentId;
            document.getElementById('deleteDocType').value = docType;
            document.getElementById('deleteResidentName').innerText = residentName;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        function updateReceiptPreview() {
            const amountVal = document.getElementById('amount_paid').value;
            document.getElementById('preview_amount').innerText = amountVal ? parseFloat(amountVal).toFixed(2) : '0.00';
        }

        function closeApprovalModal() {
            document.getElementById('approvalModal').style.display = 'none';
        }

        function closeAndReload() {
            document.getElementById('approvalModal').style.display = 'none';
            location.reload();
        }

        function handleApprovalSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('approvalForm');
            const formData = new FormData(form);

            fetch('process_certification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                form.style.display = 'none';
                document.getElementById('successMessageBox').style.display = 'block';
            })
            .catch(error => {
                console.error('Error saving approval details:', error);
                alert('An error occurred while saving the record. Please try again.');
            });
        }

        window.onclick = function(event) {
            const approvalModal = document.getElementById('approvalModal');
            const rejectModal = document.getElementById('rejectModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target === approvalModal) approvalModal.style.display = 'none';
            if (event.target === rejectModal) rejectModal.style.display = 'none';
            if (event.target === deleteModal) deleteModal.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const activeTab = document.querySelector('.tab-btn.active');
            if (activeTab) {
                filterTab('residency', activeTab);
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>