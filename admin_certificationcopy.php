<?php
// admin_certificationcopy.php
session_start();
include 'db_connect.php'; // Database connection file[cite: 1]

// Security Check: Ensure only authorized administrative/official access
if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'official' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['name'] ?? 'Administrator / Official';

// HANDLE DIRECT PRINT REQUEST ONLY (View/Print only)
if (isset($_GET['action']) && $_GET['action'] === 'print' && isset($_GET['id']) && isset($_GET['type'])) {
    $print_id = mysqli_real_escape_string($conn, $_GET['id']);
    $print_type = mysqli_real_escape_string($conn, $_GET['type']);
    
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

        <div class="watermark-note">Note: Not Valid Without Barangay Seal and Official Dry Seal.</div>
    </div>
</body>
</html>
<?php
    exit;
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
    <title>Certification Records (View Only) - Barangay Malitbog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #000066;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
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

        .rd-nav-item {
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

        .rd-nav-item.custom-home-style span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            text-transform: capitalize;
            letter-spacing: 0.5px;
        }

        .rd-nav-item i { font-size: 1.1rem; }
        .rd-nav-item:hover { color: var(--primary-blue); }
        .rd-nav-item.active { color: var(--accent-gold); }

        .hero-banner {
            background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 60px 10%;
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

        .btn-print { background: var(--print-green); color: white; }
        .btn-print:hover { background: #1b5e20; }
        .btn-file { background: #e3f2fd; color: #1565c0; margin-right: 5px; }
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
            <span><i class="fa fa-user-shield"></i> Official Portal (View Only)</span>
        </div>
    </div>

    <nav class="main-nav">
        <div class="logo-section">
            <a href="official_dashboard.php" class="back-link" title="Go Back">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>OFFICIAL VIEW PANEL</small>
            </div>
        </div>
        <div class="nav-links">
            <a href="official_dashboard.php" class="rd-nav-item custom-home-style" title="Official Home">
                <i class="fas fa-home"></i>
                <span>home</span>
            </a>
        </div>
    </nav>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>CERTIFICATION <span>RECORDS</span></h1>
            <p>View and print official resident certification records</p>
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
            <span>Certification Records List</span>
            <small style="font-weight: normal; font-size: 0.8rem; color: #666;">View-Only Mode</small>
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
                        <?php if ($statusClass === 'approved'): ?>
                            <a href="admin_certificationcopy.php?action=print&id=<?php echo urlencode($row['resident_id']); ?>&type=<?php echo urlencode($docCategory); ?>" 
                               target="_blank" class="action-btn btn-print">
                               <i class="fa fa-print"></i> Print Cert
                            </a>
                        <?php else: ?>
                            <span style="color: #777; font-size: 0.8rem; font-style: italic;">View Only</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr class="no-data-row">
                    <td colspan="9" style="text-align: center; color: #777;">No certification records found.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
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

        document.addEventListener('DOMContentLoaded', () => {
            const activeTab = document.querySelector('.tab-btn.active');
            if (activeTab) {
                filterTab('residency', activeTab);
            }
        });
    </script>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>