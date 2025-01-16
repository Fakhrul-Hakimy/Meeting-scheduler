<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "k1_meeting_scheduler";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $timeSlot = $_POST['timeSlot'];
    list($startTime, $endTime) = explode(' - ', $timeSlot);

    // Format times to HH:MM:SS
    $startTimeFormatted = date("H:i:s", strtotime($startTime));
    $endTimeFormatted = date("H:i:s", strtotime($endTime));

    $sql = "SELECT COUNT(*) as count FROM time_slots ts
            JOIN meetings m ON ts.meeting_id = m.id
            WHERE m.date = ? AND ts.start_time = ? AND ts.end_time = ? AND ts.status = 'booked'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $date, $startTimeFormatted, $endTimeFormatted);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $response = ['available' => $row['count'] == 0];
    echo json_encode($response);

    // Close connection
    $stmt->close();
    $conn->close();
}
?>
