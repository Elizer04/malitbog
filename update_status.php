<?php
// update_status.php
session_start();
include 'db_connect.php'; // Database connection file

// Check if request parameters are provided
if ((isset($_POST['id']) || isset($_GET['id'])) && (isset($_POST['action']) || isset($_GET['action']))) {
    
    // Retrieve ID and action (supports both POST form submissions and GET query links)
    $id = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id']);
    $action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

    // Map action to database status value
    $statusMap = [
        'process' => 'Approved', // or 'Processing' depending on workflow
        'reject'  => 'Rejected'
    ];

    if (array_key_exists($action, $statusMap)) {
        $new_status = $statusMap[$action];

        // Prepare UPDATE statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE certification_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Request ID #{$id} status updated to {$new_status}.";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to update record: " . $stmt->error;
            $_SESSION['msg_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid action specified.";
        $_SESSION['msg_type'] = "warning";
    }
} else {
    $_SESSION['message'] = "Missing required parameters.";
    $_SESSION['msg_type'] = "warning";
}

// Close database connection
$conn->close();

// Redirect back to admin_certification page
header("Location: admin_certification.php");
exit();
?>