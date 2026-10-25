<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Malitbog - Contact Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
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

    <!-- Main Navigation Bar -->
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
            <a href="contact.php" class="nav-item active">Contact</a>
            <a href="resident_portal.php" class="btn-signin"><i class="fa-solid fa-user"></i> Sign In</a>
        </div>
    </nav>

    <!-- Page Header Banner -->
    <header class="page-header-banner" data-aos="fade" data-aos-duration="800">
        <h1>Get in Touch</h1>
        <p>Have questions, concerns, or need assistance? Reach out to the Barangay Malitbog team.</p>
    </header>

    <!-- Contact Content Section -->
    <section class="contact-section">
        <div class="contact-container">
            
            <!-- Left Side: Contact Information -->
            <div class="contact-info-side" data-aos="fade-right" data-aos-duration="1000">
                <h3>Contact Information</h3>
                <p>We are here to assist residents with inquiries regarding digital certificates, resident registry, clearance requests, and general barangay services.</p>
                
                <div class="info-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <h4>Office Address</h4>
                        <p>Barangay Hall, Barangay Malitbog<br>Municipality of Bongabong, Oriental Mindoro 5211</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fa-solid fa-envelope"></i>
                    <div>
                        <h4>Email Support</h4>
                        <p>support@barangaymalitbog.ph<br>info@barangaymalitbog.ph</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fa-solid fa-phone"></i>
                    <div>
                        <h4>Contact Numbers</h4>
                        <p>Hotline: +63 (900) 000-0000<br>Landline: (043) 000-0000</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fa-solid fa-clock"></i>
                    <div>
                        <h4>Office Hours</h4>
                        <p>Monday to Friday: 8:00 AM – 5:00 PM<br>Closed during weekends and holidays.</p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Contact Form -->
            <div class="contact-form-side" data-aos="fade-left" data-aos-duration="1000">
                <h3>Send Us a Message</h3>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter your contact number">
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                    </div>

                    <button type="submit" class="btn-submit"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
                </form>
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