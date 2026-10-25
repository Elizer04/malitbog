<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Malitbog - Terms of Use</title>
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

    <!-- Page Header Banner -->
    <header class="page-header-banner" data-aos="fade" data-aos-duration="1000">
        <h1>Terms of Use</h1>
        <p>E-Barangay Management & Information System - Barangay Malitbog</p>
    </header>

    <!-- Main Content Section -->
    <section class="content-section">
        <div class="content-container" data-aos="fade-up" data-aos-duration="800">
            <h3>1. Acceptance of Terms</h3>
            <p>By accessing or using the Barangay Management & Information System (BMIS) of Barangay Malitbog, you agree to comply with and be bound by these Terms of Use. If you do not agree to these terms, please refrain from registering or using the platform's resident and administrative services.</p>

            <h3>2. User Accounts and Verification</h3>
            <p>Access to online services requires account registration. You agree to provide accurate, current, and complete information during registration:</p>
            <ul>
                <li>Residents must provide verifiable community and contact information.</li>
                <li>Accounts are subject to review, verification, and approval by authorized barangay administrators.</li>
                <li>You are entirely responsible for maintaining the confidentiality of your account credentials and password.</li>
            </ul>

            <h3>3. Permitted Use and Restrictions</h3>
            <p>The platform is built to make community records easier to manage, public services faster to deliver, and information more accessible. Users agree <strong>not</strong> to:</p>
            <ul>
                <li>Submit false, fraudulent, or misleading information when requesting digital certificates, filing dispute blotters, or updating resident profiles.</li>
                <li>Attempt to bypass system security, role permissions, or data segregation boundaries between barangays.</li>
                <li>Use the platform for any unlawful activities, harassment, or unauthorized data extraction.</li>
            </ul>

            <h3>4. Data Privacy and Security</h3>
            <p>Resident records, medical health logs, and judicial dispute resolutions are protected according to assigned barangay roles and system permissions. By using the system, you consent to the handling of your data in accordance with our <a href="privacy.php" style="color: var(--secondary-blue); text-decoration: underline;">Data Privacy Policy</a>.</p>

            <h3>5. Limitation of Liability</h3>
            <p>Barangay Malitbog and the BMIS platform administrators strive to ensure continuous and secure service delivery. However, the system is provided on an "as-is" basis. The barangay shall not be held liable for temporary system downtimes, network connection errors, or delays caused by external technical factors beyond administrative control.</p>

            <h3>6. Modifications to Terms</h3>
            <p>We reserve the right to modify or update these Terms of Use at any time to reflect platform upgrades or changes in local governance policies. Continued use of the platform after updates indicates your acceptance of the revised terms.</p>

            <h3>7. Contact Information</h3>
            <p>If you have any questions, concerns, or technical issues regarding these Terms of Use, you may reach out through the official Barangay Malitbog communication channels or visit the barangay hall.</p>
            
            <!-- Added Checkbox Section with a Policy Button -->
            <div class="privacy-consent-box" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600; color: #2d3748;">
                    <input type="checkbox" id="agree-policy" name="agree_policy" required style="width: 18px; height: 18px; cursor: pointer;">
                    I have read, understood, and agree to the Terms of Use of Barangay Malitbog.
                </label>
                
                <!-- Policy Button on the Right (Initially Disabled) -->
                <button type="button" id="view-policy-btn" disabled style="background-color: #cbd5e1; color: #64748b; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px; transition: background-color 0.2s, color 0.2s;">
                    <i class=""></i> I Understand
                </button>
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
                    <li><a href="contact.php">Contact</a></li>
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

    <a href="#" class="back-to-top">
        <i class="fa-solid fa-arrow-up"></i>
    </a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 100
        });

        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navLinksContainer = document.getElementById('nav-links-container');

        mobileMenuBtn.addEventListener('click', () => {
            navLinksContainer.classList.toggle('active');
        });

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinksContainer.classList.remove('active');
            });
        });

        // Toggle "I Understand the Policy" button based on Checkbox state
        const agreeCheckbox = document.getElementById('agree-policy');
        const viewPolicyBtn = document.getElementById('view-policy-btn');

        agreeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                viewPolicyBtn.removeAttribute('disabled');
                viewPolicyBtn.style.backgroundColor = '#30a16c';
                viewPolicyBtn.style.color = 'white';
                viewPolicyBtn.style.cursor = 'pointer';
            } else {
                viewPolicyBtn.setAttribute('disabled', 'true');
                viewPolicyBtn.style.backgroundColor = '#cbd5e1';
                viewPolicyBtn.style.color = '#64748b';
                viewPolicyBtn.style.cursor = 'not-allowed';
            }
        });

        // Optional: Add click action for the button once enabled
        viewPolicyBtn.addEventListener('click', function() {
            if (!this.hasAttribute('disabled')) {
                alert('Thank you for agreeing to the Terms of Use!');
            }
        });
    </script>
</body>
</html>