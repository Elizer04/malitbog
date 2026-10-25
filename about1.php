<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name']);
    $contact_no     = trim($_POST['contact_no']);
    $cert_type      = trim($_POST['cert_type']);
    $purpose_cat    = trim($_POST['purpose_cat']);
    $purpose_detail = trim($_POST['purpose_detail']);
    
    $file_dest = null;

    // Handle File Upload
    if (isset($_FILES['requirement_file']) && $_FILES['requirement_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['requirement_file']['tmp_name'];
        $file_name  = $_FILES['requirement_file']['name'];
        $ext        = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($ext, $allowed)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $file_dest    = $upload_dir . $new_filename;

            move_uploaded_file($file_tmp, $file_dest);
        }
    }

    // Insert into Database
    $sql = "INSERT INTO requests (full_name, contact_no, cert_type, purpose_cat, purpose_detail, requirement_file, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $full_name, $contact_no, $cert_type, $purpose_cat, $purpose_detail, $file_dest);

    if ($stmt->execute()) {
        header("Location: resident_dashboard.php?status=success");
        exit();
    } else {
        echo "Error saving application: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: resident_dashboard.php");
    exit();
}