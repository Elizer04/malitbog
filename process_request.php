<?php
include 'db_connect.php';

session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $resident_id    =$_POST['resident_id'] ?? '';
    $full_name      =$_POST['full_name'] ?? '';
    $contact_no     =$_POST['contact_no'] ?? '';
    $email          =$_POST['email'] ?? ''; // Capture the email field
    $cert_type      =$_POST['cert_type'] ?? '';
    $purpose_cat    =$_POST['purpose_cat'] ?? '';
    $purpose_detail =$_POST['purpose_detail'] ?? '';

    // Clean fee input (removes ₱, free text, spaces)
    $raw_fee =$_POST['fee'] ?? '0.00';
    $fee     = (float) preg_replace('/[^0-9.]/', '',$raw_fee);

    // Handle File Upload safely[cite: 2]
    $file_path = null;
    if (isset($_FILES['requirement_file']) && $_FILES['requirement_file']['error'] === UPLOAD_ERR_OK) {$file_tmp  = $_FILES['requirement_file']['tmp_name'];$file_name = time() . '_' . basename($_FILES['requirement_file']['name']);$upload_dir = 'uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $target_path = $upload_dir .$file_name;
        if (move_uploaded_file($file_tmp,$target_path)) {
            $file_path =$target_path;
        }
    }

    // Map the form's cert_type value to your exact database table names
    $target_table = "";
    switch ($cert_type) {
        case 'indigency':
            $target_table = 'cert_of_indigency';
            break;
        case 'clearance':
            $target_table = 'barangay_clearance';
            break;
        case 'residency':
            $target_table = 'cert_of_residency'; 
            break;
        default:
            die("Error: Invalid document type selected.");
    }

    // Dynamically prepare the SQL statement including 'email'
    $sql = "INSERT INTO `$target_table` (resident_id, full_name, contact_no, email, cert_type, fee, purpose_cat, purpose_detail, requirement_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Updated bind parameters: 9 variables (s = string, d = double) -> sssssdsss
        $stmt->bind_param("sssssdsss", $resident_id, $full_name,$contact_no, $email,$cert_type, $fee,$purpose_cat, $purpose_detail,$file_path);

        if ($stmt->execute()) {
            header("Location: resident_dashboard.php?status=success");
            exit();
        } else {
            echo "Error executing query: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}
?>