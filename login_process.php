<?php
session_start();

// Database configuration matching your setup
$servername = "localhost";
$username   = "root";
$password   = "eli";
$dbname     = "bmis";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST['username']);
    $user_pass   = $_POST['password'];

    // Search for a matching resident by email OR resident_id
    $stmt = $conn->prepare("SELECT * FROM residents WHERE email = ? OR resident_id = ?");
    $stmt->bind_param("ss", $login_input, $login_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Check raw password (matching registration handling)
        if ($user_pass === $row['password']) {
            $_SESSION['resident_id'] = $row['resident_id'];
            $_SESSION['full_name']   = $row['full_name'];
            $_SESSION['email']       = $row['email'];

            // Redirect to dashboard on successful login
            header("Location: resident_dashboard.php");
            exit();
        } else {
            // Password mismatch
            header("Location: resident_portal.php?error=1");
            exit();
        }
    } else {
        // User not found
        header("Location: resident_portal.php?error=1");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>