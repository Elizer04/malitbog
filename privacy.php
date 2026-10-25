<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Privacy Policy - Barangay Malitbog BMIS</title>
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

    <header class="page-header-banner" data-aos="fade" data-aos-duration="800">
        <h1>Data Privacy Policy</h1>
        <p>Commitment to protecting your personal information under the Data Privacy Act of 2012</p>
    </header>

    <!-- Privacy Policy Main Content -->
    <section class="privacy-content-section">
        <div class="privacy-container" data-aos="fade-up" data-aos-duration="1000">
            
            <div class="highlight-box">
                <p><i class="fa-solid fa-shield-halved"></i> Barangay Malitbog values your privacy. All information collected through the Barangay Management & Information System (BMIS) is handled in strict compliance with Republic Act No. 10173 (Data Privacy Act of 2012).</p>
            </div>

            <h2>1. Introduction</h2>
            <p>Welcome to the Barangay Malitbog E-Barangay platform. This Data Privacy Policy outlines how we collect, use, secure, and protect your personal data when you interact with our resident registry, request digital certificates, submit health logs, or file judicial and dispute records.</p>

            <h2>2. Information We Collect</h2>
            <p>To deliver responsive local governance and process community requests, we collect and process the following information:</p>
            <ul>
                <li><strong>Personal Identification Details:</strong> Full name, date of birth, contact numbers, residential address, and civil status.</li>
                <li><strong>Account Credentials:</strong> Username, password, and verification data utilized when accessing the resident portal.</li>
                <li><strong>Service Requests:</strong> Data required for generating digital certificates, clearances, or processing beneficiary statuses.</li>
                <li><strong>Blotter and Judicial Records:</strong> Records related to community dispute resolutions and amicable settlement hearings managed securely by authorized personnel.</li>
                <li><strong>Health Logbook Entries:</strong> Medical consultations, immunization details, and health center metrics kept strictly confidential for public health tracking.</li>
            </ul>

            <h2>3. Purpose of Data Processing</h2>
            <p>Your data is collected exclusively for legitimate governance and community service purposes, including:</p>
            <ul>
                <li>Verifying resident identity and managing organized community registries.</li>
                <li>Reviewing, approving, printing, and issuing digital barangay certificates.</li>
                <li>Facilitating official communications, announcements, and emergency updates.</li>
                <li>Maintaining records for dispute resolution and health monitoring systems within Barangay Malitbog.</li>
            </ul>

            <h2>4. Data Security and Role-Based Access</h2>
            <p>We implement strict administrative, physical, and technical security measures to safeguard your records:</p>
            <ul>
                <li><strong>Secure by Role:</strong> System access is strictly restricted based on verified user permissions (Resident, Barangay Admin, or Super Admin).</li>
                <li><strong>Barangay Isolation:</strong> Resident information belongs exclusively to the assigned barangay, ensuring strict data boundaries.</li>
                <li><strong>Data Protection:</strong> Encrypted protocols and secure server structures prevent unauthorized access, alteration, or disclosure.</li>
            </ul>

            <h2>5. Your Rights as a Data Subject</h2>
            <p>Under the Data Privacy Act of 2012, you hold specific rights regarding your personal data:</p>
            <ul>
                <li><strong>Right to be Informed:</strong> Know whether your personal data is being processed.</li>
                <li><strong>Right to Access:</strong> Request reasonable access to view or update your personal profile records.</li>
                <li><strong>Right to Correction:</strong> Dispute inaccuracies or errors in your registry entries through proper barangay verification.</li>
                <li><strong>Right to Erasure or Blocking:</strong> Suspend or remove personal data subject to legal and administrative barangay record-keeping requirements.</li>
            </ul>

            <h2>6. Contact Us</h2>
            <p>If you have questions, concerns, or requests regarding this Data Privacy Policy or how your information is handled, you may reach out to the Barangay Malitbog administration through the official portal channels or visit the Barangay Hall directly.</p>

            <!-- Added Checkbox Section with a Policy Button -->
            <div class="privacy-consent-box" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600; color: #2d3748;">
                    <input type="checkbox" id="agree-policy" name="agree_policy" required style="width: 18px; height: 18px; cursor: pointer;">
                    I have read, understood, and agree to the Data Privacy Policy of Barangay Malitbog.
                </label>
                    
                <div class="form-group">
                    <!-- Main Button with text inside -->
                    <button type="button" id="view-policy-btn" disabled style="background-color: #cbd5e1; color: #64748b; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: not-allowed; display: inline-flex; align-items: center; gap: 6px; transition: background-color 0.2s, color 0.2s;">
                        I Understand
                    </button>
                    
                    <!-- Message container to show text directly in the form -->
                    <div id="privacy-message" style="margin-top: 10px; font-weight: 600; color: #1a1a8c;"></div>
                </div>
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

    <!-- AOS Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 100 });

        // Mobile Menu Toggle
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
            // Only allow changing if it hasn't been submitted/agreed yet
            if (!viewPolicyBtn.classList.contains('agreed-success')) {
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
            }
        });

        viewPolicyBtn.addEventListener('click', function() {
            if (!this.hasAttribute('disabled') && !this.classList.contains('agreed-success')) {
                // Find the message container element
                const messageContainer = document.getElementById('privacy-message');
                
                // Display the message directly inside the form
                messageContainer.textContent = 'Thank you for agreeing to the Data Privacy Policy!';
                messageContainer.style.color = '#1a1a8c'; 

                // Change button content to a check icon and lock it
                this.classList.add('agreed-success');
                this.innerHTML = '<i class="fa-solid fa-check"></i>';
                this.setAttribute('disabled', 'true');
                this.style.cursor = 'default';
                
                // Optionally disable the checkbox so they can't uncheck it after confirming
                agreeCheckbox.disabled = true;
            }
        });
    </script>
</body>
</html>