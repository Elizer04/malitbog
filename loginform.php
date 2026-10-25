<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Portal - Barangay Malitbog</title>
    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="top-bar">
        <div class="social-icons">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-twitter"></i>
        </div>
        <div></div>
    </div>
    <nav class="main-nav">
        <div class="nav-left">
            <a href="index.php" class="btn-home" aria-label="Back to Home" title="Back to Home">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="logo-section">
                <img src="image/ebarangay.jpg" alt="Barangay Logo">
                <div class="logo-text">
                    <h2>Barangay Malitbog</h2>
                    <small>Resident Portal</small>
                </div>
            </div>
        </div>
        
        
        <div class="nav-links" id="nav-links-container">
            <!-- Additional right-side navigation links can be added here if needed -->
        </div>
    </nav>

    <!-- Main Content Wrapper with Unified Container Card -->
    <div class="main-content">
        <div class="portal-wrapper">
            <div class="portal-card-box" data-aos="fade-up" data-aos-duration="800" style="display: flex; justify-content: center; align-items: center; min-height: 450px;">


                <!-- Login Container Card Side -->
                <div class="login-container">
                    <div class="login-header">
                        <img src="image/barangaylogo3.jpg" alt="Barangay Logo" class="portal-logo">
                        <h2>Barangay Portal</h2>
                        <p>Please select your role to proceed to your secure dashboard.</p>
                    </div>

                    <div class="button-group">
                        <a href="resident_portal.php" class="btn-login" style="text-decoration: none; display: inline-flex; justify-content: center; align-items: center;">
                            <i class="fa-solid fa-user" style="margin-right: 10px;"></i> Resident Login
                        </a>
                        <a href="official_portal.php" class="btn-login" style="background-color: #28a745; color: white; text-decoration: none; display: inline-flex; justify-content: center; align-items: center;">
                            <i class="fa-solid fa-user-shield" style="margin-right: 10px;"></i> Barangay Official
                        </a>
                        <a href="admin_login.php" class="btn-login" style="background-color: #a72837; color: white; text-decoration: none; display: inline-flex; justify-content: center; align-items: center;">
                            <i class="fa-solid fa-lock" style="margin-right: 10px;"></i> Admin
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Footer Section -->
    <footer class="site-footer" data-aos="fade" data-aos-duration="1000">
        <div class="footer-bottom">
            <div>&copy; 2026 BMIS. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="privacy.php">Data Privacy</a>
                <a href="terms.php">Terms of Use</a>
                <a href="support.php">Support</a>
            </div>
        </div>
    </footer>

    <!-- AOS Script Integration & Mobile Navigation Toggle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50
        });

        // Mobile Menu Toggle Script Integration
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navLinksContainer = document.getElementById('nav-links-container');

        mobileMenuBtn.addEventListener('click', () => {
            navLinksContainer.classList.toggle('active');
        });

        // Close mobile menu automatically when any link inside is clicked
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinksContainer.classList.remove('active');
            });
        });
    </script>
</body>
</html>