<?php
    session_start();
    $error_msg = "";
    if(isset($_POST['login'])){
        $connection = mysqli_connect("localhost", "root", "", "bmis");
        
        $email = mysqli_real_escape_string($connection, $_POST['email']);
        $password = $_POST['password']; 

        $query = "SELECT * FROM official_information WHERE email = '$email' LIMIT 1";
        $redirect_page = "official_dashboard.php";

        $query_run = mysqli_query($connection, $query);
        
        if($row = mysqli_fetch_assoc($query_run)) {
            if($row['password'] == $password){
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = 'official';
                header("Location: " . $redirect_page);
                exit();
            } else {
                $error_msg = "Incorrect credentials. Please try again.";
            }
        } else {
            $error_msg = "Admin record not found.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login - Barangay Malitbog</title>
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
            max-width: 500px;
            width: 100%;
        }

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

        .login-header {
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f0f4ff;
            color: var(--primary-blue);
            font-size: 35px;
        }

        .login-header h2 {
            font-size: 1.3rem;
            margin: 0;
            letter-spacing: 1px;
            color: var(--primary-blue);
            font-weight: 800;
            margin-bottom: 4px;
        }

        .login-header p {
            font-size: 0.8rem;
            color: var(--teal-green);
            margin-top: 0;
            margin-bottom: 0;
            font-weight: 600;
        }

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

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .btn-login {
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

        .btn-login:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(26, 26, 140, 0.25);
        }

        .error-box {
            border: 1px solid #c40000;
            border-radius: 10px;
            padding: 12px;
            color: #c40000;
            font-size: 0.85rem;
            margin-bottom: 20px;
            background-color: #fcf4f4;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
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
            <a href="loginform.php" class="btn-home" aria-label="Back to Homepage" title="Back to Homepage">
                <i class="fas fa-arrow-left"></i>
            </a>
            <a href="index.php" class="logo-section">
                <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
                <div class="logo-text">
                    <h2>Barangay Malitbog</h2>
                    <small>Officials</small>
                </div>
            </a>
        </div>
    </nav>

    <div class="main-content">
        <div class="portal-wrapper">
            <div class="portal-card-box" data-aos="fade-up" data-aos-duration="800">
                <div class="login-header">
                    <div class="portal-logo">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2>OFFICIALS LOGIN</h2>
                    <p>Barangay Information Management System</p>
                </div>

                <?php if(!empty($error_msg)): ?>
                    <div class="error-box">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <span><?php echo $error_msg; ?></span>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter admin email address" autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="new-password" required>
                            <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-login">LOGIN</button>
                </form>

                <div class="footer-links">
                    <a href="index.php">← Back to Homepage</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="site-footer" data-aos="fade" data-aos-duration="1000">
        <div class="footer-bottom">
            <div>&copy; 2026 Barangay Malitbog E-Governance System. All Rights Reserved.</div>
            <div class="footer-bottom-links">
                <a href="privacy.php">Data Privacy</a>
                <a href="terms.php">Terms of Use</a>
                <a href="support.php">Support</a>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 50 });

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>