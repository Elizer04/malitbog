<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['resident_id'])) {
    die("Error: User not logged in.");
}

$conn = new mysqli("localhost", "root", "", "bmis");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve string resident_id (e.g., "2026-002")
    $resident_id = $_SESSION['resident_id']; 
    $service     = $_POST['service'];
    $appt_date   = $_POST['appt_date'];
    $notes       = $_POST['notes'];

    // Updated bind_param: Changed "isss" to "ssss" since resident_id is VARCHAR(20)
    $stmt = $conn->prepare("INSERT INTO appointments (resident_id, service, appt_date, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $resident_id, $service, $appt_date, $notes);

    if ($stmt->execute()) {
        header("Location: resident_dashboard.php?status=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>