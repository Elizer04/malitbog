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
            <a href="loginform.php" class="btn-home" aria-label="Back to Home" title="Back to Home">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="logo-section">
                <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
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
                <!-- Barangay Building Image Side with Banner -->
                <!-- <div class="portal-image-container">
                    <img src="image/login_form.jpg" alt="Barangay Building">
                    <div class="image-caption-banner">BARANGAY HALL</div>
                </div> -->

                <!-- Login Container Card Side -->
                <div class="login-container">
                    <div class="login-header">
                        <img src="image/barangaylogo3.jpg" alt="Barangay Logo" class="portal-logo">
                        <h2>RESIDENT PORTAL</h2>
                        <p>Access your barangay services and records</p>
                    </div>

                    <?php
                    if(isset($_GET['error'])) {
                        echo "<div class='error-message'><i class='fa-solid fa-triangle-exclamation'></i> Invalid Email, Resident ID, or Password</div>";
                    }
                    ?>

                    <form action="login_process.php" method="POST">
                        <div class="form-group">
                            <label for="username">Email or Resident ID</label>
                            <input type="text" id="username" name="username" placeholder="Enter your email or resident ID" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                        </div>

                        <button type="submit" class="btn-login">Sign In</button>
                    </form>

                    <div class="footer-links">
                        <a href="forgot_password.php">Forgot Password?</a><br><br>
                        <span>Don't have an account? <a href="register.php">Register here</a></span>
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