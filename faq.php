<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Malitbog - Frequently Asked Questions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
    <div class="top-bar">
        <div class="social-icons">
            <a href="https://www.facebook.com/share/1MqLQuYLdG/"><i class="fab fa-facebook"></i></a>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-instagram"></i>
            <i class="fas fa-twitter"></i>
        </div>
        <div></div>
    </div>
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
            <a href="index.php#about-platform" class="nav-item">About</a>
            <a href="resident_portal.php" class="btn-signin"><i class="fa-solid fa-user"></i> Sign In</a>
        </div>
    </nav>

    <!-- Header Banner -->
    <header class="hero-banner">
        <div data-aos="fade-up" data-aos-duration="1000">
            <h1>FREQUENTLY ASKED QUESTIONS</h1>
            <p class="hero-subtitle">Everything you need to know about the E-Barangay Management Information System for Barangay Malitbog</p>
        </div>
    </header>

    <!-- FAQ Content Section -->
    <section class="faq-section">
        <div class="faq-container">
            
            <div class="faq-item" data-aos="fade-up" data-aos-delay="100">
                <h3><i class="fa-solid fa-circle-question"></i> What is the E-Barangay Management Information System (BMIS)?</h3>
                <p>It is a secure digital platform designed for Barangay Malitbog (Municipality of Bongabong) to organize community records, deliver public services faster, and make information more accessible to authorized users.</p>
            </div>

            <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                <h3><i class="fa-solid fa-circle-question"></i> What core features are included in the system?</h3>
                <p>The platform features four core modules: Resident Registry (for profiles and households), Digital Certificates (for document processing and printing), Judicial System (for dispute resolutions and blotters), and Health Logbook (for medical consultations and vaccination logs).</p>
            </div>

            <div class="faq-item" data-aos="fade-up" data-aos-delay="300">
                <h3><i class="fa-solid fa-circle-question"></i> How do I register and access resident services?</h3>
                <p>First, a resident creates an account using their community and contact information. Once the assigned barangay reviews and approves your account, you can sign in to access announcements, applications, messages, and profile tools.</p>
            </div>

            <div class="faq-item" data-aos="fade-up" data-aos-delay="400">
                <h3><i class="fa-solid fa-circle-question"></i> How is my personal data and account security protected?</h3>
                <p>Access is strictly limited according to assigned user roles (resident, barangay admin, or super admin). Additionally, resident information belongs only to the correct assigned barangay to ensure isolation and data privacy.</p>
            </div>

            <div class="faq-item" data-aos="fade-up" data-aos-delay="500">
                <h3><i class="fa-solid fa-circle-question"></i> Who can use the Admin Portal?</h3>
                <p>The Admin Portal is restricted to authorized barangay personnel and administrators to manage service requests, resident accounts, and local records.</p>
            </div>

        </div>
    </section>

    <!-- Professional Footer Section -->
    <footer class="site-footer" data-aos="fade" data-aos-duration="1000">
        <div class="footer-container">
            <div class="footer-col">
                <h3><img src="image/bmis.jpg" alt="BMIS Logo"> BMIS</h3>
                <p>BARANGAY MANAGEMENT & INFORMATION SYSTEM</p>
                <p>A secure digital platform supporting resident records, barangay services, communication, and local governance.</p>
                <p>Bridging barangays and residents through secure technology, reliable data, and responsive public service.</p>
            </div>
            <div class="footer-col">
                <h4>PLATFORM</h4><br>
                <ul>
                    <li><a href="index.php#governance-section">Features</a></li>
                    <li><a href="admin_login.php">Admin Portal</a></li>
                    <li><a href="resident_portal.php">Resident Services</a></li>
                    <li><a href="index.php#how-it-works">How It Works</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>INFORMATION</h4><br>
                <ul>
                    <li><a href="index.php#about-platform">About BMIS</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="resident_portal.php">Sign In</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>SERVICE NOTICE</h4><br>
                <p>Access is limited to verified users and authorized barangay personnel.</p>
                <p>Records are protected according to assigned barangay and system role.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; 2026 BMIS. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="privacy.php">Data Privacy</a>
                <a href="terms.php">Terms of Use</a>
                <a href="support.php">Support</a>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 100 });
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navLinksContainer = document.getElementById('nav-links-container');
        mobileMenuBtn.addEventListener('click', () => {
            navLinksContainer.classList.toggle('active');
        });
    </script>
</body>
</html>