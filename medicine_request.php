<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIMS - Request Medicine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; color: white; width: 250px; position: fixed; }
        .main-content { margin-left: 250px; padding: 40px; }
        .nav-link { color: #bdc3c7; transition: 0.3s; padding: 15px 25px; }
        .nav-link:hover, .nav-link.active { background-color: #34495e; color: white; border-left: 4px solid #3498db; }
        .form-card { background: white; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        /* Matched accent color to medicine theme while keeping button style */
        .btn-request { background-color: #e74c3c; border: none; padding: 12px 30px; font-weight: 600; border-radius: 8px; transition: 0.3s; color: white; }
        .btn-request:hover { background-color: #c0392b; transform: translateY(-2px); color: white; }
        .section-title { border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 20px; color: #2c3e50; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3 class="p-4 text-center">BIMS Resident</h3>
    <nav class="nav flex-column">
        <a class="nav-link" href="dashboard.php"><i class="fas fa-th-large me-2"></i> Dashboard</a>
        <a class="nav-link" href="#"><i class="fas fa-user me-2"></i> My Profile</a>
        <a class="nav-link active" href="health_portal.php"><i class="fas fa-heartbeat me-2"></i> Health Portal</a>
        <a class="nav-link" href="#"><i class="fas fa-bullhorn me-2"></i> Announcements</a>
        <div class="mt-5">
            <a class="nav-link" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </nav>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Medicine Request</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="health_portal.php" class="text-decoration-none">Health Portal</a></li>
                    <li class="breadcrumb-item active">Request Medicine</li>
                </ol>
            </nav>
        </div>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $medicine = htmlspecialchars($_POST['medicine_name']);
            echo "<div class='alert alert-success border-0 shadow-sm mb-4'>
                    <i class='fas fa-check-circle me-2'></i> Success! Your request for <strong>$medicine</strong> has been submitted.
                  </div>";
        }
        ?>

        <div class="row">
            <div class="col-lg-4">
                <div class="card form-card mb-4 p-4 text-center">
                    <div class="mb-3 text-danger">
                        <i class="fas fa-pills fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Availability Check</h5>
                    <p class="small text-muted mb-4">Submit your request to check the current stock at the Barangay Health Unit.</p>
                    <ul class="list-group list-group-flush text-start small">
                        <li class="list-group-item"><i class="fas fa-check me-2 text-success"></i> Free of charge</li>
                        <li class="list-group-item"><i class="fas fa-check me-2 text-success"></i> Resident only</li>
                        <li class="list-group-item"><i class="fas fa-paperclip me-2 text-primary"></i> Prescription required for antibiotics</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card form-card p-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <h5 class="section-title">Medication Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Medicine Name</label>
                                <input type="text" name="medicine_name" class="form-control" placeholder="e.g. Paracetamol, Amoxicillin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quantity</label>
                                <input type="number" name="quantity" class="form-control" min="1" placeholder="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit Type</label>
                                <select name="type" class="form-select">
                                    <option value="tablets">Tablets</option>
                                    <option value="capsules">Capsules</option>
                                    <option value="bottles">Bottles (Syrup)</option>
                                </select>
                            </div>
                        </div>

                        <h5 class="section-title">Additional Info</h5>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Reason for Request</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Briefly describe why you need this medication..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Prescription (Optional)</label>
                            <input type="file" class="form-control" name="prescription" accept="image/*,.pdf">
                            <div class="form-text">Accepted: JPG, PNG, or PDF.</div>
                        </div>

                        <div class="alert alert-info small border-0 bg-light">
                            <i class="fas fa-info-circle me-1"></i> Note: Approval depends on stock availability and medical necessity.
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light me-2" onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-request">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>