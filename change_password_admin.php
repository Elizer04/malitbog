<?php
// 1. Start session
session_start();

// Check if admin is logged in via email
if (!isset($_SESSION['email'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php'; 

$admin_email = $_SESSION['email'];
$full_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Barangay Malitbog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="resident-dashboard.css">
    <style>
        :root {
            --primary-blue: #000066;
            --accent-gold: #ffcc00;
            --text-white: #ffffff;
            --white: #ffffff;
            --bg-light: #f4f7f6;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Segoe UI', Arial, 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
        }

        .top-bar {
            background: #1a1a8c;
            color: white;
            padding: 5px 10%;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
        }
        .top-bar div:first-child { display: flex; gap: 15px; }
        .social-icons { display: flex; gap: 20px; }

        .main-nav {
            background: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo-section { display: flex; align-items: center; gap: 15px; }
        .logo-section img { height: 60px; width: 60px; border-radius: 50%; }
        .logo-text h2 { margin: 0; color: var(--primary-blue); font-size: 1.2rem; letter-spacing: 1px; }

        .rd-back-link {
            font-size: 1.2rem;
            color: var(--primary-blue);
            margin-right: 5px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .rd-back-link:hover { color: var(--accent-gold); }

        .hero-banner {
            background: linear-gradient(rgba(0, 0, 102, 0.7), rgba(0, 0, 102, 0.7)), 
                        url('image/backgroundphoto1.jpg') no-repeat center center/cover;
            padding: 50px 10%; 
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-msg h1 { margin: 0; font-size: 2.2rem; }
        .welcome-msg span { color: var(--accent-gold); }
        .welcome-msg p { font-size: 1.2rem; margin-top: 10px; }

        .container {
            max-width: 600px;
            margin: -30px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .section-title {
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 25px;
            color: #333;
            border-left: 5px solid var(--accent-gold);
            padding-left: 15px;
        }

        .info-group { display: flex; flex-direction: column; margin-bottom: 20px; }

        .info-label {
            font-size: 0.8rem;
            color: #777;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-input:focus { outline: none; border-color: var(--primary-blue); }

        .btn-track {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-track:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .btn-track:disabled { background: #555588; cursor: not-allowed; transform: none; box-shadow: none; }

        .rd-back-dashboard {
            text-align: center;
            margin-top: 15px;
        }
        .rd-back-dashboard a {
            font-size: 0.85rem;
            color: #666;
            text-decoration: none;
            font-weight: 600;
        }
        .rd-back-dashboard a:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
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
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .close-btn {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        .close-btn:hover { color: #000; }

        .modal-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .modal-header h3 { margin: 0; color: #000066; }

        .modal-body-content {
            padding: 10px 0;
            font-size: 0.95rem;
            color: #333;
            line-height: 1.5;
        }

        .modal-footer {
            margin-top: 20px;
            text-align: right;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-modal-action {
            background: #000066;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: 0.3s;
        }
        .btn-modal-action:hover { background: #000044; }

        @media (max-width: 768px) {
            html, body { overflow: auto; }
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
    </div>

    <nav class="main-nav">
        <div class="logo-section">
            <a href="admin_dashboard.php" class="rd-back-link" title="Back to Dashboard">
                <i class="fa fa-arrow-left"></i>
            </a>
            <img src="image/barangaylogo3.jpg" alt="Logo">
            <div class="logo-text">
                <h2>BARANGAY MALITBOG</h2>
                <small>PROVINCE OF ORIENTAL MINDORO</small>
            </div>
        </div>
    </nav>

    <header class="hero-banner">
        <div class="welcome-msg">
            <h1>SECURITY <span>SETTINGS</span></h1>
            <p>UPDATE YOUR PASSWORD - BARANGAY MALITBOG</p>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h3 class="section-title">Change Password</h3>
            
            <form id="updatePasswordForm" action="update_password_admin.php" method="post">
                <div class="info-group">
                    <label class="info-label" for="old_password">Current Password</label>
                    <input type="password" class="form-input" id="old_password" name="old_password" placeholder="Enter existing password" required>
                </div>
                
                <div class="info-group">
                    <label class="info-label" for="new_password">New Password</label>
                    <input type="password" class="form-input" id="new_password" name="new_password" placeholder="Enter new password" required>
                </div>

                <!-- Added Confirm Password Field -->
                <div class="info-group">
                    <label class="info-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-input" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>

                <button type="submit" name="update" class="btn-track" id="submitBtn">
                    <i class="fas fa-lock-open mr-2"></i> Update Password
                </button>
            </form>
            
            <div class="rd-back-dashboard">
                <a href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div id="resultModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('resultModal')">&times;</span>
            <div class="modal-header">
                <h3 id="modalTitle">Password Update Notice</h3>
            </div>
            <div class="modal-body-content">
                <p id="modalMessageBody" style="margin: 0;">Processing your request...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-action" onclick="closeModal('resultModal')">OK</button>
            </div>
        </div>
    </div>

    <script>
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        const updatePasswordForm = document.getElementById('updatePasswordForm');
        const submitBtn = document.getElementById('submitBtn');
        const resultModal = document.getElementById('resultModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessageBody = document.getElementById('modalMessageBody');

        updatePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault(); 

            // Optional front-end check to make sure new password and confirm password match
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                modalTitle.textContent = "Validation Error";
                modalMessageBody.textContent = "New passwords do not match. Please check and try again.";
                resultModal.style.display = 'flex';
                return;
            }

            const formData = new FormData(updatePasswordForm);
            formData.append('update', 'true');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            const startTime = Date.now();

            fetch('update_password_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                const elapsedTime = Date.now() - startTime;
                const minDisplayTime = 500; 
                const delay = elapsedTime < minDisplayTime ? minDisplayTime - elapsedTime : 0;

                setTimeout(() => {
                    const cleanResponse = data ? data.trim() : '';

                    if (cleanResponse.toLowerCase().includes('success') || cleanResponse === '1') {
                        modalTitle.textContent = "Success: Password Updated";
                        modalMessageBody.textContent = "Your password has been successfully updated across all admin profiles.";
                        updatePasswordForm.reset(); 
                    } else {
                        modalTitle.textContent = "Notice: Update Issue";
                        modalMessageBody.textContent = (cleanResponse && cleanResponse.length < 120 && !cleanResponse.startsWith('<')) 
                            ? cleanResponse 
                            : "Unable to update password. Please verify that your current password is correct and try again.";
                    }

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-lock-open mr-2"></i> Update Password';
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
                    submitBtn.innerHTML = '<i class="fas fa-lock-open mr-2"></i> Update Password';
                    resultModal.style.display = 'flex'; 
                }, delay);
            });
        });

        window.onclick = function(event) {
            if (event.target === resultModal) {
                resultModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php 
if (isset($conn)) {
    $conn->close(); 
}
?>