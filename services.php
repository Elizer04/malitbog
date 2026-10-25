<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BIMS - Our Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .icon-box {
            width: 60px;
            height: 60px;
            background: #2c3e50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .header-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
	
	 /* Navigation Bar */
	 nav {
          
            color: white;
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
          
		position: relative;
            z-index: 1000;
		flex-wrap: wrap;
        }
        nav .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        nav .logo img {
            height: 70px; 
            width: 70px;
		object-fit: cover;
            border-radius: 50%; /* Makes it a circle */
            border: 3px solid #ffcc00; /* Added a gold border to pop */
            box-shadow: 0 8px 15px rgba(0,0,0,0.3); /* Shadow for depth */
            background-color: white;
        }
	

        nav .logo strong {
            font-size: 1.2rem;
            letter-spacing: 1px;
		color: #ffcc00;
        }

       
	
    </style>
</head>
<body>

      <section class="header-section text-center">
        <div class="container">
		 <nav>
        <div class="logo">
            <img src="image/barangaylogo3.jpg" alt="Barangay Logo">
            
        </div>

            <h1 class="display-4 fw-bold">Barangay Services</h1>
            <p class="lead">Efficient, transparent, and accessible digital services for every resident.</p>
        </div>
    </section>

    <div class="container mb-5">
        <div class="row g-4">
            
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 p-4 service-card shadow-sm">
                    <div class="icon-box">
                        <i class="fa-solid fa-file-signature fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Barangay Clearance</h5>
                    <p class="text-muted small">Request and process clearances for employment, business, or residency.</p>
                   
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 p-4 service-card shadow-sm">
                    <div class="icon-box bg-danger">
                        <i class="fa-solid fa-gavel fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Blotters & Cases</h5>
                    <p class="text-muted small">Report incidents or view status of filed barangay mediation cases.</p>
                   
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 p-4 service-card shadow-sm">
                    <div class="icon-box bg-success">
                        <i class="fa-solid fa-folder-open fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Official Records</h5>
                    <p class="text-muted small">Access your personal registration, ID details, and certificates.</p>
                   
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 p-4 service-card shadow-sm">
                    <div class="icon-box bg-info text-white">
                        <i class="fa-solid fa-heart-pulse fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">Health Services</h5>
                    <p class="text-muted small">Track immunization, nutrition records, and medical assistance.</p>
                   
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>