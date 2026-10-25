<?php
$host = 'localhost';
$db   = 'bmis';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch current profile data from the database
$stmt = $pdo->query("SELECT * FROM barangay_profile WHERE id = 1");
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch current barangay officials list from the database
$officialsStmt = $pdo->query("SELECT * FROM barangay_officials ORDER BY id ASC");
$officials = $officialsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Profile - Barangay <?php echo htmlspecialchars($profile['barangay_name']); ?></title>
    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Embedded CSS Matching the Home Page Design -->
    <style>
        :root {
            --primary-blue: #000066;
            --secondary-blue: #1a1a8c;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --primary-dark: #2c3e50;
            --accent-blue: #294a71;
            --text-gray: #555;
            --deep-burgundy: #591624; 
            --teal-green: #157347;   
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0 auto;
            padding: 0 20px 40px 20px;
            background-color: var(--bg-light);
            color: #333;
            min-height: 100vh;
        }

        /* Top Bar & Nav */
        .top-bar {
            background: var(--secondary-blue);
            color: white;
            padding: 8px 10%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            box-sizing: border-box;
            z-index: 1000;
            margin: 0 -20px; 
        }

        .social-icons {
            display: flex;
            gap: 20px;
            color: white;
        }

        .social-icons i {
            cursor: pointer;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .social-icons i:hover {
            color: var(--accent-gold);
        }

        .main-nav {
            background: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0 -20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-link {
            color: var(--secondary-blue);
            font-size: 1.1rem;
            text-decoration: none;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--bg-light);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .back-link:hover {
            background: var(--secondary-blue);
            color: white;
        }

        .logo-section img { 
            height: 55px; 
            width: 55px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 2px solid var(--primary-blue);
        }

        .logo-text h2 {
            margin: 0;
            color: var(--primary-blue);
            font-size: 1.2rem;
            letter-spacing: 1px;
        }

        .logo-text small {
            color: var(--text-gray);
            font-size: 0.75rem;
        }

        /* Hero Banner Matching Index Style */
        .hero-banner {
            background: linear-gradient(rgba(26, 26, 140, 0.85), rgba(26, 26, 140, 0.85)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 50px 10%;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 0 -20px;
        }

        .welcome-msg h1 {
            font-size: 2.2rem;
            margin: 0 0 10px 0;
            font-weight: 800;
        }

        .welcome-msg h1 span {
            color: var(--accent-gold);
        }

        .welcome-msg p {
            font-size: 0.85rem;
            letter-spacing: 2px;
            margin: 0;
            opacity: 0.85;
            text-transform: uppercase;
        }

        .urgent-notices {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        .urgent-notices strong {
            display: block;
            margin-bottom: 5px;
            color: var(--accent-gold);
            letter-spacing: 1px;
        }

        .urgent-notices ul {
            margin: 0;
            padding-left: 15px;
            color: #f1f1f1;
        }

        /* Main Content Grid Layout */
        .container {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 40px auto 0 auto;
            align-items: start;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }

        /* Sidebar Profile Styling */
        .sidebar-info {
            text-align: center;
        }

        .sidebar-info img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-blue);
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(26, 26, 140, 0.15);
        }

        .sidebar-title {
            font-size: 1.4rem;
            color: #1a1a1a;
            margin: 0 0 5px 0;
            font-weight: 800;
        }

        .sidebar-subtitle {
            color: var(--text-gray);
            font-size: 0.85rem;
            margin: 0 0 25px 0;
        }

        .info-group {
            text-align: left;
            background: var(--bg-light);
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 12px;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .info-group label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--secondary-blue);
            font-weight: 700;
            margin-bottom: 3px;
        }

        .info-group p {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #222;
        }

        /* Main Content Section Titles */
        .section-title {
            font-size: 1.3rem;
            color: var(--secondary-blue);
            margin: 0 0 20px 0;
            font-weight: 700;
            border-bottom: 2px solid var(--bg-light);
            padding-bottom: 10px;
        }

        .vm-heading {
            font-size: 0.95rem;
            color: var(--secondary-blue);
            margin: 0 0 8px 0;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .vm-text {
            font-size: 0.9rem;
            color: var(--text-gray);
            line-height: 1.6;
            margin: 0;
        }

        /* Officials Grid/List Styling */
        .official-card {
            display: flex;
            align-items: center;
            gap: 15px;
            background: var(--bg-light);
            padding: 15px 20px;
            border-radius: 14px;
            margin-bottom: 15px;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .official-card i {
            font-size: 1.4rem;
            width: 45px;
            height: 45px;
            background: rgba(26, 26, 140, 0.08);
            color: var(--secondary-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .official-name {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .official-card small {
            color: var(--text-gray);
            font-size: 0.8rem;
        }

        /* Professional Footer Styles */
        .site-footer {
            background-color: #120b13; 
            color: #d1d5db;
            padding: 60px 40px 30px 40px;
            margin: 80px -20px -40px -20px;
            font-size: 0.85rem;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
            gap: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-col h3, .footer-col h4 {
            color: white;
            font-weight: 700;
            margin-top: 0;
        }

        .footer-col h3 {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .footer-col h3 img {
            height: 30px;
            width: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .footer-col h4 {
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
            color: #9ca3af;
        }

        .footer-col p {
            color: #9ca3af;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.8rem;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-col ul li a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.85rem;
        }

        .footer-col ul li a:hover {
            color: white;
        }

        .footer-social-icons {
            display: flex;
            gap: 15px;
        }

        .footer-social-icons a {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .footer-social-icons a:hover {
            background: var(--secondary-blue);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 30px auto 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #9ca3af;
            font-size: 0.8rem;
            flex-wrap: wrap;
            gap: 15px;
        }

        .footer-bottom-links {
            display: flex;
            gap: 25px;
        }

        .footer-bottom-links a {
            color: #9ca3af;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: white;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--secondary-blue);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 999;
        }

        @media(max-width: 900px) {
            .container { grid-template-columns: 1fr; }
            .footer-container { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

    <!-- Top Social Bar -->
    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-twitter"></i>
        </div>
        <div></div>
    </div>

    <!-- Main Navigation Header -->
    <nav class="main-nav">
        <div class="logo-section">
            <a href="official_dashboard.php" class="back-link" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
            <div class="logo-text">
                <h2>BARANGAY <?php echo htmlspecialchars($profile['barangay_name']); ?></h2>
                <small><?php echo htmlspecialchars($profile['municipality'] ?? 'ORIENTAL MINDORO'); ?></small>
            </div>
        </div>
    </nav>

    <!-- Hero Banner Profile -->
    <header class="hero-banner profile-hero">
        <div class="welcome-msg">
            <h1>BARANGAY <span>PROFILE</span></h1>
            <p>PROVINCE OF ORIENTAL MINDORO - BARANGAY <?php echo htmlspecialchars($profile['barangay_name']); ?></p>
        </div>
        <div class="urgent-notices">
            <strong>OFFICE HOURS</strong>
            <ul>
                <li>Monday - Friday: 8:00 AM - 5:00 PM</li>
                <li>Emergency Services: 24/7 Available</li>
            </ul>
        </div>
    </header>

    <!-- Content Layout Grid -->
    <div class="container">
        <!-- Sidebar Info -->
        <div class="card sidebar-info">
            <img src="image/barangaylogo3.jpg" alt="Barangay Seal">
            <h2 class="sidebar-title"><?php echo htmlspecialchars($profile['barangay_name']); ?></h2>
            <p class="sidebar-subtitle">Established <?php echo htmlspecialchars($profile['established_year']); ?></p>
            
            <div class="info-group">
                <label>Barangay Captain</label>
                <p><?php echo htmlspecialchars($profile['barangay_captain']); ?></p>
            </div>
            <div class="info-group">
                <label>Total Population</label>
                <p><?php echo htmlspecialchars($profile['total_population']); ?></p>
            </div>
            <div class="info-group">
                <label>Land Area</label>
                <p><?php echo htmlspecialchars($profile['land_area']); ?></p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content-wrapper">
            <div class="card mb-20">
                <h3 class="section-title">Barangay Profile & Background</h3>
                <div class="vision-mission-grid">
                    <div>
                        <h4 class="vm-heading">OVERVIEW</h4>
                        <p class="vm-text"><?php echo nl2br(htmlspecialchars($profile['overview_text'])); ?></p>
                    </div>
                    <div style="margin-top: 20px;">
                        <h4 class="vm-heading">LOCAL ECONOMY & GEOGRAPHY</h4>
                        <p class="vm-text"><?php echo nl2br(htmlspecialchars($profile['economy_geography_text'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Dynamic Barangay Officials List -->
            <div class="card">
                <h3 class="section-title">Barangay Officials</h3>
                
                <?php if (count($officials) > 0): ?>
                    <?php foreach ($officials as $official): ?>
                        <div class="official-card">
                            <i class="fa fa-user-tie"></i>
                            <div>
                                <div class="official-name"><?php echo htmlspecialchars($official['name']); ?></div>
                                <small><?php echo htmlspecialchars($official['position']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--text-gray); font-style: italic;">No officials recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Professional Footer Section -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-col">
                <h3><img src="image/bmis.jpg" alt="BMIS Logo"> BMIS</h3>
                <p>BARANGAY MANAGEMENT & INFORMATION SYSTEM</p>
                <p>A secure digital platform supporting resident records, barangay services, communication, and local governance.</p>
                <div class="footer-social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-facebook-messenger"></i></a>
                    <a href="#"><i class="fa-solid fa-globe"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>PLATFORM</h4>
                <ul>
                    <li><a href="admin_login.php">Admin Portal</a></li>
                    <li><a href="resident_portal.php">Resident Services</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>INFORMATION</h4>
                <ul>
                    <li><a href="barangay_profile.php">About BMIS</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>SERVICE NOTICE</h4>
                <p>Access is limited to verified users and authorized barangay personnel.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; 2026 BMIS. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="#">Data Privacy</a>
                <a href="#">Terms of Use</a>
            </div>
        </div>
    </footer>

    <a href="#" class="back-to-top">
        <i class="fa-solid fa-arrow-up"></i>
    </a>

</body>
</html>