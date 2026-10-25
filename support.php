<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Malitbog - Support & Help Desk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<style>
    /* Additional styles specific to Support page layout */
    .support-container {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }
    
    .support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .support-card {
        background: #ffffff;
        padding: 40px 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #eaeaea;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .support-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .support-card .card-icon-box {
        font-size: 2.5rem;
        color: var(--primary-blue, #1a1a8c);
        margin-bottom: 20px;
    }

    .support-card h4 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
    }

    .support-card p {
        font-size: 0.95rem;
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .support-link-btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: var(--primary-blue, #1a1a8c);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: background 0.2s;
    }

    .support-link-btn:hover {
        background-color: #121262;
    }
</style>
<body>
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="social-icons">
            <a href="https://www.facebook.com/share/1MqLQuYLdG/"><i class="fab fa-facebook"></i></a>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-instagram"></i>
            <i class="fas fa-twitter"></i>
        </div>
        <div></div>
    </div>

    <!-- Main Navigation -->
    <nav class="main-nav">
        <div class="logo-section">
            <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>MUNICIPALITY OF BONGABONG</small>
            </div>
        </div>

        <button class="menu-toggle" id="mobile-menu-btn" aria-label="Toggle Navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="nav-links" id="nav-links-container">
            <a href="index.php" class="nav-item">Home</a>
            <a href="index.php#governance-section" class="nav-item">Barangay Services</a>
            <a href="index.php#how-it-works" class="nav-item">How It Works</a>
            <a href="faq.php" class="nav-item">FAQ</a>
            <a href="contact.php" class="nav-item">Contact</a>
            <a href="resident_portal.php" class="btn-signin"><i class="fa-solid fa-user"></i> Sign In</a>
        </div>
    </nav>

    <!-- Hero Section Banner -->
    <header class="hero-banner">
        <div class="hero-image-container" data-aos="fade-right" data-aos-duration="1000">
            <img src="image/backgroundphoto1.jpg" alt="Barangay Support">
        </div>
        <div class="hero-text-side" data-aos="fade-left" data-aos-duration="1000">
            <h1>HELP & SUPPORT</h1>
            <h2>E-BARANGAY DESK</h2>
            <p class="hero-subtitle">We are here to assist you with system navigation, account verification, and barangay services.</p>

            <div class="seals-row">
                <img src="image/barangaylogo3.jpg" class="seal" alt="Barangay Seal 1">
                <img src="image/bmis.jpg" class="seal" alt="Barangay Seal 2">
            </div>
        </div>
    </header>

    <!-- Support Options Section -->
    <section class="support-container">
        <div style="text-align: center;" data-aos="fade-up" data-aos-duration="800">
            <div class="core-features-badge" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-life-ring"></i> ASSISTANCE CHANNELS
            </div>
            <h2 class="services-main-title">How can we help you <span>today?</span></h2>
            <p class="services-description" style="max-width: 700px; margin: 0 auto;">
                Choose from the options below to find answers, review common guidelines, or get in touch directly with the Barangay Malitbog support staff.
            </p>
        </div>

        <div class="support-grid">
            <!-- FAQ Card -->
            <div class="support-card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-icon-box">
                    <i class="fa-solid fa-circle-question"></i>
                </div>
                <h4>Frequently Asked Questions</h4>
                <p>Browse through answers regarding account registration, digital certificate requests, and resident verification processes.</p>
                <a href="faq.php" class="support-link-btn">View FAQs</a>
            </div>

            <!-- Contact Helpdesk Card -->
            <div class="support-card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-icon-box">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h4>Contact Barangay Hall</h4>
                <p>Send a direct message or visit the official Barangay Malitbog hall for hands-on assistance and document inquiries.</p>
                <a href="contact.php" class="support-link-btn">Contact Us</a>
            </div>

            <!-- Privacy Policy Card -->
            <div class="support-card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-icon-box">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <h4>Privacy Policy</h4>
                <p>Review how your personal information and resident data are securely collected, handled, and protected under privacy guidelines.</p>
                <a href="privacy.php" class="support-link-btn">Privacy Policy</a>
            </div>

            <!-- Terms of Use Card -->
            <div class="support-card" data-aos="fade-up" data-aos-delay="400">
                <div class="card-icon-box">
                    <i class="fa-solid fa-file-contract"></i>
                </div>
                <h4>Terms of Use</h4>
                <p>Understand the platform user policies, user responsibilities, and guidelines for accessing digital barangay portal services.</p>
                <a href="terms.php" class="support-link-btn">Terms of Use</a>
            </div>
        </div>
    </section>

    <!-- Professional Footer Section -->
    <footer class="site-footer" data-aos="fade" data-aos-duration="1000">
        <div class="footer-container">
            <!-- Column 1: Brand / Description -->
            <div class="footer-col">
                <h3><img src="image/bmis.jpg" alt="BMIS Logo"> BMIS</h3>
                <p>BARANGAY MANAGEMENT & INFORMATION SYSTEM</p>
                <p>A secure digital platform supporting resident records, barangay services, communication, and local governance.</p>
                <p>Bridging barangays and residents through secure technology, reliable data, and responsive public service.</p>
            </div>

            <!-- Column 2: Platform Links -->
            <div class="footer-col">
                <h4>PLATFORM</h4><br>
                <ul>
                    <li><a href="index.php#governance-section">Features</a></li>
                    <li><a href="admin_login.php">Admin Portal</a></li>
                    <li><a href="resident_portal.php">Resident Services</a></li>
                    <li><a href="index.php#how-it-works">How It Works</a></li>
                </ul>
            </div>

            <!-- Column 3: Information Links -->
            <div class="footer-col">
                <h4>INFORMATION</h4><br>
                <ul>
                    <li><a href="index.php#about-platform">About BMIS</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="resident_portal.php">Sign In</a></li>
                </ul>
            </div>

            <!-- Column 4: Service Notice -->
            <div class="footer-col">
                <h4>SERVICE NOTICE</h4><br>
                <p>Access is limited to verified users and authorized barangay personnel.</p>
                <p>Records are protected according to assigned barangay and system role.</p>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <div>&copy; 2026 BMIS. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="privacy.php">Data Privacy</a>
                <a href="terms.php">Terms of Use</a>
                <a href="support.php">Support</a>
            </div>
        </div>
    </footer>

    <!-- Floating Back to Top Anchor -->
    <a href="#" class="back-to-top">
        <i class="fa-solid fa-arrow-up"></i>
    </a>

    <!-- AOS (Animate on Scroll) Library JS Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS animations
        AOS.init({
            once: true,
            offset: 100
        });

        // Mobile Menu Toggle Script
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navLinksContainer = document.getElementById('nav-links-container');

        mobileMenuBtn.addEventListener('click', () => {
            navLinksContainer.classList.toggle('active');
        });

        // Close mobile menu automatically when a link is clicked
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinksContainer.classList.remove('active');
            });
        });
    </script>
</body>
</html>