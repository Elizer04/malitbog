<?php
session_start();
include 'db_connect.php';

// Ensure the admin is logged in
if (!isset($_SESSION['email'])) {
    echo "Unauthorized access.";
    exit();
}

if (isset($_POST['update'])) {
    $email =$_SESSION['email']; // Session email matching the admin table
    $old_password =$_POST['old_password'];
    $new_password =$_POST['new_password'];

    // 1. Fetch the current password from the `admin` table in `bmis`
    $stmt =$conn->prepare("SELECT password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);$stmt->execute();
    $result =$stmt->get_result();

    if ($row =$result->fetch_assoc()) {
        $stored_password =$row['password'];

        // Compares plain-text passwords based on your current DB table structure
        if ($old_password ===$stored_password) {
            // 2. Update to the new password in BOTH the `admin` table AND the `official_information` table
            $update_admin =$conn->prepare("UPDATE admin SET password = ? WHERE email = ?");
            $update_admin->bind_param("ss", $new_password,$email);
            
            $update_official =$conn->prepare("UPDATE official_information SET password = ? WHERE email = ?");
            $update_official->bind_param("ss", $new_password,$email);

            $admin_updated =$update_admin->execute();
            $official_updated =$update_official->execute();

            if ($admin_updated &&$official_updated) {
                echo "success";
            } else {
                echo "Error updating password in the database.";
            }

            $update_admin->close();$update_official->close();
        } else {
            echo "Current password is incorrect.";
        }
    } else {
        echo "Admin account not found.";
    }
    
    $stmt->close();$conn->close();
}
?>