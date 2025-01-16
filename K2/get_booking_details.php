
<?php
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "k2_meeting_scheduler";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $timeSlot = $_POST['timeSlot'];
    list($startTime, $endTime) = explode(' - ', $timeSlot);

    // Format times to HH:MM:SS
    $startTimeFormatted = date("H:i:s", strtotime($startTime));
    $endTimeFormatted = date("H:i:s", strtotime($endTime));

    $sql = "SELECT u.first_name, u.surname, u.email, m.purpose 
            FROM time_slots ts
            JOIN meetings m ON ts.meeting_id = m.id
            JOIN users u ON m.user_id = u.id
            WHERE m.date = ? AND ts.start_time = ? AND ts.end_time = ? AND ts.status = 'booked'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $date, $startTimeFormatted, $endTimeFormatted);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode([
            'first_name' => $booking['first_name'],
            'surname' => $booking['surname'],
            'email' => $booking['email'],
            'purpose' => $booking['purpose']
        ]);
    } else {
        echo json_encode(['error' => 'No booking found for the provided time slot.']);
    }

    // Close connection
    $stmt->close();
    $conn->close();
}
?>