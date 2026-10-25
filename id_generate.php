function generateResidentID($conn) {
    $currentYear = date('Y'); // Get current year (e.g., 2026)
    
    // Query to get the last ID generated for the current year
    $query = "SELECT resident_id FROM residents WHERE resident_id LIKE '$currentYear-%' ORDER BY resident_id DESC LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Extract sequence number from "2026-002" -> 2
        $lastNum = (int) explode('-', $row['resident_id'])[1];
        $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    } else {
        // First record of the current year
        $newNum = '001';
    }

    return $currentYear . '-' . $newNum; // Returns "2026-004"
}