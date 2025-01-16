<?php

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "k2_meeting_scheduler";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $timeSlot = $_POST['timeSlot'];

    // Extract start_time and end_time
    list($startTime, $endTime) = explode(' - ', $timeSlot);

    // Prepare SQL to fetch meeting details
    $sql = "SELECT u.first_name, u.surname, u.email, m.purpose 
            FROM time_slots ts 
            JOIN meetings m ON ts.meeting_id = m.id 
            JOIN users u ON m.user_id = u.id 
            WHERE m.date = ? AND ts.start_time = ? AND ts.end_time = ? AND ts.status = 'booked' LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
        exit();
    }

    $stmt->bind_param("sss", $date, $startTime, $endTime);
    $stmt->execute();
    $stmt->bind_result($firstName, $surname, $email, $purpose);
    
    if ($stmt->fetch()) {
        $name = $firstName . ' ' . $surname;
        echo json_encode([
            'success' => true,
            'name' => $name,
            'email' => $email,
            'purpose' => $purpose
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No meeting details found for this slot.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>