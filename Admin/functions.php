<?php
// ...existing code...

// Function to combine multiple time slots
function getCombinedTimeSlots($meeting_id) {
    global $conn;
    
    $query = "SELECT start_time, end_time FROM time_slots WHERE meeting_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $meeting_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $combinedTimeSlots = [];
    while ($row = $result->fetch_assoc()) {
        $combinedTimeSlots[] = $row['start_time'] . ' - ' . $row['end_time'];
    }
    
    $stmt->close();
    
    return $combinedTimeSlots;
}

// Function to validate user credentials
function validateUser($username, $password) {
    include 'database_connection.php';
    
    $query = "SELECT password FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();
    
    // Verify the provided password against the hashed password from the database
    return password_verify($password, $hashed_password);
}
?>