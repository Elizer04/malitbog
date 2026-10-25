<?php
// update_password.php
session_start();

// 1. Check if resident is logged in
if (!isset($_SESSION['resident_id'])) {
    header("Location: resident_portal.php");
    exit();
}

// 2. Include database connection
include 'db_connect.php';

// 3. Process form submission
if (isset($_POST['update'])) {
    $resident_id = $_SESSION['resident_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Fetch current password from database for this resident
    $sql = "SELECT password FROM residents WHERE resident_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $resident_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        // Note: If you use hashing (like password_hash), use password_verify($old_password, $stored_password) instead.
        // For plain text comparison (matching standard setup if not hashed yet):
        if ($old_password === $stored_password) {
            
            // Optional: If you want to hash the new password for security, use: 
            // $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            // and update with that variable instead. Let's update directly:

            $update_sql = "UPDATE residents SET password = ? WHERE resident_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_password, $resident_id);

            if ($update_stmt->execute()) {
                // Success message or redirect with success alert
                echo "<script>alert('Password updated successfully!'); window.location.href='resident_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error updating password. Please try again.'); window.location.href='my_profile.php';</script>";
            }
            
            $update_stmt->close();
        } else {
            echo "<script>alert('Incorrect current password.'); window.location.href='my_profile.php';</script>";
        }
    } else {
        echo "<script>alert('Resident record not found.'); window.location.href='resident_portal.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: my_profile.php");
    exit();
}
?>