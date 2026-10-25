<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIMS - Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; color: white; width: 250px; position: fixed; }
        .main-content { margin-left: 250px; padding: 40px; }
        .nav-link { color: #bdc3c7; transition: 0.3s; padding: 15px 25px; }
        .nav-link:hover, .nav-link.active { background-color: #34495e; color: white; border-left: 4px solid #3498db; }
        .form-card { background: white; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .btn-request { background-color: #3498db; border: none; padding: 12px 30px; font-weight: 600; border-radius: 8px; transition: 0.3s; color: white; }
        .btn-request:hover { background-color: #2980b9; transform: translateY(-2px); color: white; }
        .section-title { border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 20px; color: #2c3e50; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3 class="p-4 text-center">BIMS Resident</h3>
    <nav class="nav flex-column">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-th-large me-2"></i> Dashboard</a>
        <a class="nav-link" href="#"><i class="fas fa-user me-2"></i> My Profile</a>
        <a class="nav-link active" href="#"><i class="fas fa-calendar-check me-2"></i> Health Services</a>
        <a class="nav-link" href="#"><i class="fas fa-bullhorn me-2"></i> Announcements</a>
        <div class="mt-5">
            <a class="nav-link" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </nav>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Schedule an Appointment</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Book Appointment</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card form-card mb-4 p-4">
                    <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-notes-medical me-2"></i> Center Hours</h5>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item">Mon - Fri: 8:00 AM - 5:00 PM</li>
                        <li class="list-group-item">Sat: 8:00 AM - 12:00 PM</li>
                        <li class="list-group-item text-danger fw-bold">Closed on Sundays</li>
                    </ul>
                    <div class="mt-4 p-3 bg-light rounded border">
                        <p class="mb-0 small text-muted"><i class="fas fa-info-circle me-1"></i> Please arrive 15 minutes before your scheduled slot.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card form-card p-4">
                    <form action="process_booking.php" method="POST">
                        
                        <h5 class="section-title">Visit Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Select Service</label>
                                <select name="service" id="service" class="form-select" required>
                                    <option value="" selected disabled>Select service...</option>
                                    <option value="General Checkup">General Checkup</option>
                                    <option value="Consultation">Consultation</option>
                                    <option value="Dental">Dental Service</option>
                                    <option value="Prenatal">Prenatal Care</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preferred Date</label>
                                <input type="date" name="appointment_date" id="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preferred Time Slot</label>
                                <select name="time_slot" id="time" class="form-select" required>
                                    <option value="08:00 AM">08:00 AM - 10:00 AM</option>
                                    <option value="10:00 AM">10:00 AM - 12:00 PM</option>
                                    <option value="01:00 PM">01:00 PM - 03:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <h5 class="section-title">Additional Information</h5>
                        <div class="mb-4">
                            <label for="reason" class="form-label fw-semibold">Reason for Appointment</label>
                            <textarea name="reason" id="reason" rows="3" class="form-control" placeholder="Briefly describe your health concern..."></textarea>
                        </div>

                        <div class="alert alert-info small">
                            You will receive a notification once your appointment is confirmed by the center staff.
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light me-2" onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-request">Confirm Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>