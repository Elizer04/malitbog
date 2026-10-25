<?php
include 'db_connect.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $type = $_POST['type'] ?? '';

    $table = '';
    if ($type === 'residency') {
        $table = 'cert_of_residency';
    } elseif ($type === 'indigency') {
        $table = 'cert_of_indigency';
    } elseif ($type === 'clearance') {
        $table = 'barangay_clearance';
    }

    if (empty($table) || empty($id)) {
        http_response_code(400);
        echo "Invalid parameters.";
        exit;
    }

    if ($action === 'approve') {
        $resident_email  = $_POST['resident_email'] ?? '';
        $or_number       = $_POST['or_number'] ?? '';
        $amount_paid     = $_POST['amount_paid'] ?? 0;
        $claim_message   = $_POST['claim_message'] ?? '';
        
        // Updates the certification request table row directly with the claim message
        $stmt = $conn->prepare("UPDATE $table SET status = 'Approved', email = ?, or_number = ?, amount_paid = ?, claim_message = ? WHERE resident_id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ssdsi", $resident_email, $or_number, $amount_paid, $claim_message, $id);
            
            if ($stmt->execute()) {
                // ==================== REAL EMAIL FUNCTION FOR APPROVAL ====================
                if (!empty($resident_email)) {
                    $to = $resident_email;
                    $subject = "Barangay Malitbog - Official Certification Approved & Claim Notice";
                    $message = $claim_message;

                    $headers = "From: elizerhilliado@gmail.com\r\n" .
                               "Reply-To: elizerhilliado@gmail.com\r\n" .
                               "X-Mailer: PHP/" . phpversion();

                    // Send the email
                    @mail($to, $subject, $message, $headers);
                }
                // ===========================================================================

                http_response_code(200);
                echo "Success";
            } else {
                http_response_code(500);
                echo "Database update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo "Statement preparation failed: " . $conn->error;
        }
    } 
    elseif ($action === 'reject') {
        $resident_email = $_POST['resident_email'] ?? '';
        $reject_message = $_POST['reject_message'] ?? '';
        
        // Updates table row with Rejected status, email, and stores the rejection message into claim_message
        $stmt = $conn->prepare("UPDATE $table SET status = 'Rejected', email = ?, claim_message = ? WHERE resident_id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ssi", $resident_email, $reject_message, $id);
            
            if ($stmt->execute()) {
                // ==================== REAL EMAIL FUNCTION FOR REJECTION ====================
                if (!empty($resident_email)) {
                    $to = $resident_email;
                    $subject = "Barangay Malitbog - Certification Request Status: Notice of Rejection";
                    $message = $reject_message;

                    $headers = "From: elizerhilliado@gmail.com\r\n" .
                               "Reply-To: elizerhilliado@gmail.com\r\n" .
                               "X-Mailer: PHP/" . phpversion();

                    // Send the email
                    @mail($to, $subject, $message, $headers);
                }
                // ===========================================================================

                http_response_code(200);
                echo "Success";
            } else {
                http_response_code(500);
                echo "Database update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            http_response_code(500);
            echo "Statement preparation failed: " . $conn->error;
        }
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';
    $type = $_GET['type'] ?? '';

    $table = '';
    if ($type === 'residency') {
        $table = 'cert_of_residency';
    } elseif ($type === 'indigency') {
        $table = 'cert_of_indigency';
    } elseif ($type === 'clearance') {
        $table = 'barangay_clearance';
    }

    if (!empty($table) && !empty($id)) {
        if ($action === 'delete') {
            // Permanently delete the record from the database table
            $stmt = $conn->prepare("DELETE FROM $table WHERE resident_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'archive') {
            $stmt = $conn->prepare("UPDATE $table SET status = 'Archived' WHERE resident_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: admin_certification.php");
    exit();
}
?>