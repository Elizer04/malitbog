<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Malitbog - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<style>
    .hero-banner {
            scroll-margin-top: 80px;
            background: linear-gradient(rgba(26, 26, 140, 0.85), rgba(26, 26, 140, 0.85)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 60px 10%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 30px;
            width: 100%;
            box-sizing: border-box;
        }
</style>
<body>
    
    <div class="top-bar">
        <div class="social-icons">
            <a href="https://www.facebook.com/share/1MqLQuYLdG/"><i class="fab fa-facebook"></i></a>
            <a href="https://youtube.com/@officialpage-barangaymalitbog?si=eo-uBsODjPVwKpOn"><i class="fab fa-youtube"></i></a>
            <i class="fab fa-instagram"></i>
            <i class="fa-brands fa-twitter"></i>
        </div>
        <div></div>
    </div>
    <nav class="main-nav">
        <div class="logo-section">
            <img src="image/ebarangay.jpg" alt="Barangay Logo">
            <div class="log-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>MUNICIPALITY OF BONGABONG</small>
            </div>
        </div>

        <button class="menu-toggle" id="mobile-menu-btn" aria-label="Toggle Navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="nav-links" id="nav-links-container">
            <a href="#home" class="nav-item active">Home</a>
            <a href="#governance-section" class="nav-item">Barangay Services</a>
            <a href="#how-it-works" class="nav-item">How It Works</a>
            <a href="#about-platform" class="nav-item">About</a>
            <!-- <a href="admin_login.php" class="nav-item">Admin</a> -->
            <a href="loginform.php" class="btn-signin"><i class="fa-solid fa-user"></i> Sign In</a>
        </div>
    </nav>

    <!-- Hero Section Banner -->
    <header id="home" class="hero-banner">
        <div class="hero-image-container" data-aos="fade-right" data-aos-duration="1000">
            <img src="image/backgroundphoto1.jpg" alt="Barangay Building">
        </div>
        <div class="hero-text-side" data-aos="fade-left" data-aos-duration="1000">
            <h1>WELCOME TO</h1>
            <h2>E-BARANGAY</h2>
            <p class="hero-subtitle">A WEB BASED INFORMATTION MANAGEMENT SYSTEM FOR BARANGAY MALITBOG</p>

            <div class="seals-row">
                <img src="image/barangaylogo3.jpg" class="seal" alt="Barangay Seal 1">
                <img src="image/ebarangay.jpg" class="seal" alt="Barangay Seal 2">
            </div>
        </div>
    </header>

    <!-- E-Governance Systems Grid Section -->
    <section id="governance-section" class="system-nav">
        <div data-aos="fade-up" data-aos-duration="800">
            <div class="core-features-badge">
                <i class="fa-solid fa-wand-magic-sparkles"></i> CORE FEATURES
            </div>
            <h2 class="services-main-title">One system for everyday <span>barangay services</span></h2>
            <p class="services-description">
                Built to make community records easier to manage, public services faster to deliver, and information more accessible to authorized users.
            </p>
        </div>

        <div class="system-grid">
            <!-- Resident Registry Card -->
            <div class="system-card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-icon-box">
                    <i class="fa-solid fa-address-card"></i>
                </div>
                <h4>Resident Registry</h4>
                <p>Maintain organized resident profiles, demographic details, beneficiary status, and household records.</p>
                <div class="card-number">01</div>
            </div>

            <!-- Digital Certificates Card -->
            <div class="system-card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-icon-box">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <h4>Digital Certificates</h4>
                <p>Create, review, approve, preview, print, and release barangay documents using reusable templates.</p>
                <div class="card-number">02</div>
            </div>

            <!-- Judicial System / Dispute Resolution Card -->
            <div class="system-card" data-aos="fade-up" data-aos-delay="300">
                <div class="card-icon-box">
                    <i class="fa-solid fa-gavel"></i>
                </div>
                <h4>Judicial System</h4>
                <p>Manage community dispute resolutions, blotter records, and amicable settlement hearings efficiently.</p>
                <div class="card-number">03</div>
            </div>

            <!-- Health Logbook Card -->
            <div class="system-card" data-aos="fade-up" data-aos-delay="400">
                <div class="card-icon-box">
                    <i class="fa-solid fa-square-plus"></i>
                </div>
                <h4>Health Logbook</h4>
                <p>Track medical consultations, vaccination logs, and health center patient records securely.</p>
                <div class="card-number">04</div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works-section">
        <div data-aos="fade-up" data-aos-duration="800">
            <div class="process-badge">
                <i class="fa-solid fa-diagram-project"></i> HOW ebarangay WORKS
            </div>
            <h2 class="process-main-title">A clear path from registration<br>to <span>digital service</span></h2>
            <p class="process-description">
                The platform combines account verification, barangay assignment, protected access, and service processing in one coordinated workflow.
            </p>
        </div>

        <div class="process-steps-container">
            <!-- Step 1: Register -->
            <div class="process-step" data-aos="zoom-in" data-aos-delay="100">
                <div class="step-icon-wrapper burgundy">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h4>Register</h4>
                <p>A resident creates an account using their community and contact information.</p>
            </div>

            <!-- Step 2: Verify -->
            <div class="process-step" data-aos="zoom-in" data-aos-delay="200">
                <div class="step-icon-wrapper teal">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4>Verify</h4>
                <p>The assigned barangay reviews and approves the resident account.</p>
            </div>

            <!-- Step 3: Access Services -->
            <div class="process-step" data-aos="zoom-in" data-aos-delay="300">
                <div class="step-icon-wrapper burgundy">
                    <i class="fa-solid fa-grip"></i>
                </div>
                <h4>Access Services</h4>
                <p>Approved users access announcements, applications, messages, and profile tools.</p>
            </div>

            <!-- Step 4: Receive Updates -->
            <div class="process-step" data-aos="zoom-in" data-aos-delay="400">
                <div class="step-icon-wrapper teal">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4>Receive Updates</h4>
                <p>Residents receive status updates while administrators manage requests and records.</p>
            </div>
        </div>
    </section>

    <!-- About the Platform Section (Centered on Screen) -->
    <section id="about-platform" class="about-platform-section">
        <div class="about-platform-wrapper" data-aos="fade-up" data-aos-duration="1000">
            <div class="about-platform-left" data-aos="fade-right" data-aos-delay="200">
                <div class="hero-image-container" style="border-radius: 30px; border-color: var(--secondary-blue);">
                    <img src="image/ebarangay.jpg" alt="ebarangay Platform Seal">
                </div>
            </div>
            <div class="about-platform-right" data-aos="fade-left" data-aos-delay="200">
                <div class="platform-badge">
                    <i class="fa-solid fa-circle-info"></i> ABOUT THE PLATFORM
                </div>
                <h2>Technology that supports responsive governance</h2>
                <p>
                    The Barangay Management & Information System is designed to improve how barangays organize information, provide resident services, and communicate with their communities.
                </p>
                <p>
                    It supports local workflows while protecting the separation of data between barangays and limiting access according to assigned user roles.
                </p>

                <div class="platform-features-grid">
                    <div class="platform-feature-card" data-aos="fade-up" data-aos-delay="100">
                        <h4>Community-centered</h4>
                        <p>Features are organized around actual barangay and resident service needs.</p>
                    </div>
                    <div class="platform-feature-card" data-aos="fade-up" data-aos-delay="200">
                        <h4>Secure by role</h4>
                        <p>Access is limited according to resident, barangay admin, or super admin permissions.</p>
                    </div>
                    <div class="platform-feature-card" data-aos="fade-up" data-aos-delay="300">
                        <h4>Barangay isolated</h4>
                        <p>Resident information belongs only to the correct assigned barangay.</p>
                    </div>
                    <div class="platform-feature-card" data-aos="fade-up" data-aos-delay="400">
                        <h4>Built to expand</h4>
                        <p>The platform supports additional local services as barangay needs evolve.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Professional Footer Section -->
    <footer class="site-footer" data-aos="fade" data-aos-duration="1000">
        <div class="footer-container">
            <!-- Column 1: Brand / Description -->
            <div class="footer-col">
                <h3><img src="image/ebarangay.jpg" alt="ebarangay Logo"> ebarangay</h3>
                <p>BARANGAY MANAGEMENT & INFORMATION SYSTEM</p>
                <p>A secure digital platform supporting resident records, barangay services, communication, and local governance.</p>
                <p>Bridging barangays and residents through secure technology, reliable data, and responsive public service.</p>
            </div>


            <!-- Column 2: Platform Links -->
            <div class="footer-col">
                <h4>PLATFORM</h4><br>
                <ul>
                    <li><a href="#governance-section">Features</a></li>
                    <li><a href="admin_login.php">Admin Portal</a></li>
                    <li><a href="resident_portal.php">Resident Services</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                </ul>
            </div>

            <!-- Column 3: Information Links -->
            <div class="footer-col">
                <h4>INFORMATION</h4><br>
                <ul>
                    <li><a href="#about-platform">About ebarangay</a></li>
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

        <div class="footer-bottom">
            <div>&copy; 2026 ebarangay. All rights reserved.</div>
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

        // Scroll Spy to dynamically toggle the 'active' class on links based on view position
        const sections = document.querySelectorAll('header[id], section[id]');
        const navItems = document.querySelectorAll('.nav-links .nav-item');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href') === `#${current}`) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>