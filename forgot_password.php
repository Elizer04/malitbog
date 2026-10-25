<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Barangay Malitbog</title>
    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS (Animate on Scroll) Library CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
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
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f0f4ff 0%, #e6eeff 50%, #d9e4ff 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Bar Styling */
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

        /* Main Navigation Header */
        .main-nav {
            background: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
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

        a.btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-blue);
            background: transparent;
            border: none;
            text-decoration: none;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        a.btn-home:hover {
            color: var(--accent-gold);
        }

        /* Main Content Layout */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e6eeff 50%, #d9e4ff 100%);
        }

        .portal-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 600px;
            width: 100%;
        }

        /* Unified Card Box Style */
        .portal-card-box {
            background: var(--white);
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(26, 26, 140, 0.12);
            overflow: hidden;
            width: 100%;
            border: 1px solid rgba(26, 26, 140, 0.12);
            padding: 3rem 2.5rem;
            box-sizing: border-box;
        }

        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .portal-logo {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
            margin-bottom: 0.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .registration-header h2 {
            font-size: 1.3rem;
            margin: 0;
            letter-spacing: 1px;
            color: var(--primary-blue);
            font-weight: 800;
            margin-bottom: 4px;
        }

        .registration-header p {
            font-size: 0.8rem;
            color: var(--teal-green);
            margin-top: 0;
            margin-bottom: 0;
            font-weight: 600;
        }

        /* Form Controls Styling */
        .form-group {
            text-align: left;
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--accent-blue);
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 0.85rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
            background-color: #f9fbfd;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(26, 26, 140, 0.1);
        }

        .btn-register {
            width: 100%;
            background: var(--primary-blue);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.8rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-register:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(26, 26, 140, 0.25);
        }

        .footer-links {
            text-align: center;
            margin-top: 1.2rem;
            font-size: 0.8rem;
            color: var(--text-gray);
            border-top: 1px solid rgba(0,0,0,0.06);
            padding-top: 1rem;
        }

        .footer-links a {
            color: var(--secondary-blue);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--accent-gold);
            text-decoration: underline;
        }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 2000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 16px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 1.4rem;
            color: var(--text-gray);
            cursor: pointer;
        }

        .close-btn:hover { color: #000; }

        .modal-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary-blue);
            font-size: 1.2rem;
        }

        .modal-body-content {
            font-size: 0.95rem;
            color: #333;
            line-height: 1.6;
        }

        .modal-footer {
            margin-top: 25px;
            text-align: right;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
        }

        .btn-modal-action {
            background: var(--secondary-blue);
            color: white;
            padding: 10px 22px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-modal-action:hover { background: var(--primary-blue); }

        /* Professional Footer Styles */
        .site-footer {
            background-color: var(--primary-blue);
            color: #d1d5db;
            padding: 30px 40px;
            font-size: 0.85rem;
            width: 100%;
            box-sizing: border-box;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
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

        /* Responsive Breakpoints */
        @media(max-width: 768px) {
            .portal-card-box {
                padding: 2rem 1.5rem;
            }
            .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
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
        <div class="nav-left">
            <a href="resident_portal.php" class="btn-home" aria-label="Back to Portal" title="Back to Portal">
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="index.php" class="logo-section">
                <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
                <div class="logo-text">
                    <h2>Barangay Malitbog</h2>
                    <small>Resident Portal</small>
                </div>
            </a>
        </div>
    </nav>

    <!-- Main Content Reset Container -->
    <div class="main-content">
        <div class="portal-wrapper">
            <div class="portal-card-box" data-aos="fade-up" data-aos-duration="800">
                <div class="registration-header">
                    <img src="image/resetpassword.jpg" alt="Reset Password Logo" class="portal-logo">
                    <h2>RESET PASSWORD</h2>
                    <p>Recover access to your digital account</p>
                </div>

                <form id="resetForm" action="process_reset.php" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label for="email">Registered Email Address</label>
                        <input type="email" id="email" name="email" placeholder="juan.dc@example.com" autocomplete="off" required>
                    </div>

                    <button type="submit" name="reset-request-submit" class="btn-register" id="submitBtn">Send Reset Link</button>
                </form>

                <div class="footer-links">
                    <span>Remember your password? <a href="resident_portal.php">Login here</a></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Result Modal -->
    <div id="resultModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('resultModal')">&times;</span>
            <div class="modal-header">
                <h3 id="modalTitle">Password Reset Notice</h3>
            </div>
            
            <div class="modal-body-content">
                <p id="modalMessageBody" style="margin: 0;">Processing your request...</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-modal-action" onclick="closeModal('resultModal')">OK</button>
            </div>
        </div>
    </div>

    <!-- Professional Footer Section -->
    <footer class="site-footer" data-aos="fade" data-aos-duration="1000">
        <div class="footer-bottom">
            <div>&copy; 2026 BMIS. All rights reserved.</div>
            <div class="footer-bottom-links">
                <a href="#">Data Privacy</a>
                <a href="#">Terms of Use</a>
                <a href="#">Support</a>
            </div>
        </div>
    </footer>

    <!-- AOS Script Integration -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50
        });

        // Modal utility functions
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Handle Form Submission with AJAX & Result Modal Display
        const resetForm = document.getElementById('resetForm');
        const submitBtn = document.getElementById('submitBtn');
        const resultModal = document.getElementById('resultModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessageBody = document.getElementById('modalMessageBody');

        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(resetForm);
            submitBtn.disabled = true;
            submitBtn.textContent = "Sending Reset Link...";

            const startTime = Date.now();

            fetch('process_reset.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const elapsedTime = Date.now() - startTime;
                const minDisplayTime = 500; 
                const delay = elapsedTime < minDisplayTime ? minDisplayTime - elapsedTime : 0;

                setTimeout(() => {
                    if (data.toLowerCase().includes('success') || data.trim() === '1') {
                        modalTitle.textContent = "Success: Reset Link Sent";
                        modalMessageBody.textContent = "If an account matches this email, a password reset link has been successfully dispatched. Please check your inbox.";
                        resetForm.reset(); 
                    } else if (data.toLowerCase().includes('not found') || data.toLowerCase().includes('no record')) {
                        modalTitle.textContent = "Notice: Email Not Found";
                        modalMessageBody.textContent = "The email address provided is not registered in the Barangay Malitbog database. Please check your spelling or sign up for a new account.";
                    } else {
                        modalTitle.textContent = "Notice: Request Status";
                        modalMessageBody.textContent = data || 'Password reset request processed. Please check your email inbox.';
                    }

                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Reset Link';
                    resultModal.style.display = 'flex';
                }, delay);
            })
            .catch(error => {
                const elapsedTime = Date.now() - startTime;
                const minDisplayTime = 500;
                const delay = elapsedTime < minDisplayTime ? minDisplayTime - elapsedTime : 0;

                setTimeout(() => {
                    modalTitle.textContent = "System Error";
                    modalMessageBody.textContent = 'A network error occurred. Please check your connection and try again.';
                    console.error('Error:', error);

                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Reset Link';
                    resultModal.style.display = 'flex';
                }, delay);
            });
        });

        // Close modal when clicking outside of content window
        window.onclick = function(event) {
            if (event.target === resultModal) {
                resultModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>