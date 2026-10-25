<?php
include 'db_connect.php'; 

$count_result = $conn->query("SELECT COUNT(*) AS total FROM residents");
$count_row = $count_result->fetch_assoc();
$next_num = $count_row['total'] + 1;
$current_year = date('Y');
$auto_resident_id = $current_year . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Registration - Barangay Malitbog[cite: 1]</title>
    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Custom CSS for Two-Column Grid Form Layout -->
    <style>
        .portal-card-box {
            max-width: 800px !important; /* Expanded width to accommodate two columns comfortably */
            width: 100%;
            margin: 20px auto;
            padding: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px 20px;
            width: 100%;
        }
        /* Make specific elements span across both columns */
        .form-group.full-width, 
        .btn-register, 
        .footer-links {
            grid-column: span 2;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr; /* Fallback to 1 column on mobile devices */
            }
            .form-group.full-width, 
            .btn-register, 
            .footer-links {
                grid-column: span 1;
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
            <a href="resident_portal.php" class="btn-home" aria-label="Back to Portal" title="Back to Portal">
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
    </nav>

    <div class="main-content">
        <div class="portal-wrapper">
            <div class="portal-card-box" data-aos="fade-up" data-aos-duration="800" style="display: flex; justify-content: center; align-items: center; min-height: 450px;">
                <div class="registration-header" style="text-align: center; margin-bottom: 25px;">
                    <img src="image/accregister.jpg" alt="Barangay Logo" class="portal-logo" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%;">
                    <h2>ACCOUNT REGISTRATION</h2>
                    <p>Join your barangay digital portal</p><br><br>
              

                <form id="registrationForm" action="process_registration.php" method="POST" autocomplete="off">
                    <input type="hidden" id="resident_id" name="resident_id" value="<?php echo htmlspecialchars($auto_resident_id); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" placeholder="Dela Cruz" maxlength="50" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" placeholder="Juan" maxlength="50" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" placeholder="Santos" maxlength="50" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required>
                        </div>

                        <div class="form-group">
                            <label for="civil_status">Civil Status</label>
                            <select id="civil_status" name="civil_status" required>
                                <option value="" disabled selected>Select Civil Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="address">Address / Purok</label>
                            <select id="address" name="address" required>
                                <option value="" disabled selected>Select Address / Purok</option>
                                <option value="Centro">Centro</option>
                                <option value="KM4">KM4</option>
                                <option value="KM5">KM5</option>
                                <option value="Miaje">Miaje</option>
                                <option value="Mayangis">Mayangis</option>
                                <option value="Sampalocan">Sampalocan</option>
                                <option value="Sto. Niño">Sto. Niño</option>
                                <option value="Alacan">Alacan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" placeholder="+63 912 345 6789" autocomplete="off" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="juan.dc@example.com" autocomplete="off" required>
                        </div>

                       <div class="form-group full-width">
                            <label for="password">Password (6 to 8 characters)</label>
                            <div class="password-wrapper" style="position: relative;">
                                <input type="password" id="password" name="password" placeholder="Enter 6-8 characters" minlength="6" maxlength="20" autocomplete="new-password" required style="width: 100%;">
                                <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn-register" id="submitBtn">Register Now</button>

                        <div class="footer-links" style="text-align: center; margin-top: 10px;">
                            <span>Already have an account? <a href="resident_portal.php">Login here</a></span>
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Application Result Modal -->
    <div id="resultModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('resultModal')">&times;</span>
            <div class="modal-header">
                <h3 id="modalTitle">Registration Notice</h3>
            </div>
            <div class="modal-body-content">
                <p id="modalMessageBody" style="margin: 0;">Processing your status...</p>
            </div>
            <div class="modal-footer">
    <button type="button" class="btn-modal-action" id="modalActionBtn" onclick="closeModal('resultModal')">OK</button>
</div>
        </div>
    </div>

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

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

       // Handle Registration Form Submission via AJAX & Result Modal Display
        const registrationForm = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');
        const resultModal = document.getElementById('resultModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessageBody = document.getElementById('modalMessageBody');
        const modalActionBtn = document.getElementById('modalActionBtn'); // Added reference

        // Variable to track whether we should redirect on close
        let shouldRedirect = false;

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            // If redirect flag is true, navigate to resident portal when modal closes
            if (shouldRedirect) {
                window.location.href = 'resident_portal.php';
            }
        }

        registrationForm.addEventListener('submit', function(e) {
    e.preventDefault(); 

    const passwordVal = password.value;

    // Check if password length is between 6 and 8 characters
    if (passwordVal.length < 6 || passwordVal.length > 20) {
        modalTitle.textContent = "Validation Error";
        modalMessageBody.textContent = "Password must be strictly between 6 and 8 characters long.";
        modalActionBtn.textContent = "OK";
        shouldRedirect = false;
        resultModal.style.display = 'flex';
        return; // Stop form submission
    }

    const formData = new FormData(registrationForm);
    submitBtn.disabled = true;
    submitBtn.textContent = "Processing Registration...";

    const startTime = Date.now();

    fetch('process_registration.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const elapsedTime = Date.now() - startTime;
        const minDisplayTime = 500; 
        const delay = elapsedTime < minDisplayTime ? minDisplayTime - elapsedTime : 0;

        setTimeout(() => {
            let serverMsg = data.trim();

            shouldRedirect = false;

            if (serverMsg.toLowerCase().includes('success') || serverMsg === '1') {
                // Grab the generated resident ID from the hidden input field
                const generatedResidentId = document.getElementById('resident_id').value;

                modalTitle.textContent = "Success: Registration Complete";
                // Include the Resident ID in the message text
                modalMessageBody.innerHTML = `Registration successful! Your Resident ID is <strong>${generatedResidentId}</strong>. You can now log in to the portal.`;
                
                registrationForm.reset(); 
                
                shouldRedirect = true;
                modalActionBtn.textContent = "OK";
            } else if (serverMsg === 'duplicate' || serverMsg.toLowerCase().includes('duplicate') || serverMsg.toLowerCase().includes('already')) {
                modalTitle.textContent = "Notice: Existing Record Detected";
                modalMessageBody.textContent = "The resident details or email address provided are already registered in the Barangay Malitbog registry system.";
                modalActionBtn.textContent = "OK";
            } else {
                modalTitle.textContent = "Database Error";
                modalMessageBody.textContent = serverMsg; 
                modalActionBtn.textContent = "OK";
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Register Now';
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
            modalActionBtn.textContent = "OK";
            shouldRedirect = false;
            console.error('Error:', error);

            submitBtn.disabled = false;
            submitBtn.textContent = 'Register Now';
            resultModal.style.display = 'flex'; 
        }, delay);
        });
    });

        window.onclick = function(event) {
            if (event.target === resultModal) {
                closeModal('resultModal'); // Reuse closeModal function to handle redirection if flagged
            }
        }
    </script>
</body>
</html>