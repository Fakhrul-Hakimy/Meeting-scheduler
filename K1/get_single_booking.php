
<?php
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "k1_meeting_scheduler";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $bookingId = $_GET['id'];

    $sql = "SELECT ts.id, u.first_name, u.surname, u.email, m.date, ts.start_time, ts.end_time, m.purpose
            FROM time_slots ts
            JOIN meetings m ON ts.meeting_id = m.id
            JOIN users u ON m.user_id = u.id
            WHERE ts.id = ? AND ts.status = 'booked'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode($booking);
    } else {
        echo json_encode(['error' => 'Booking not found.']);
    }

    $stmt->close();
}

$conn->close();
?>
    } else {
        echo json_encode(['error' => 'Booking not found.']);
    }

    $stmt->close();
}

$conn->close();
?>

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode($booking);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
}

$conn->close();
?>
            WHERE ts.id = ? AND ts.status = 'booked'";
    $stmt = $conn->prepare($sql);
            JOIN users u ON m.user_id = u.id

    } else {
        echo json_encode(['error' => 'Booking not found.']);
    }
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        echo json_encode($booking);
            WHERE ts.id = ? AND ts.status = 'booked'";
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt = $conn->prepare($sql);

    $sql = "SELECT ts.id, u.first_name, u.surname, u.email, m.date, ts.start_time, ts.end_time, m.purpose
            FROM time_slots ts
            JOIN meetings m ON ts.meeting_id = m.id
            JOIN users u ON m.user_id = u.id

    $sql = "SELECT ts.id, u.first_name, u.surname, u.email, m.date, ts.start_time, ts.end_time, m.purpose
            FROM time_slots ts
            JOIN meetings m ON ts.meeting_id = m.id

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $bookingId = $_GET['id'];
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $bookingId = $_GET['id'];
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
$username = "root";
$password = "";
$dbname = "meeting_scheduler";

// Create connection
$username = "root";
$password = "";
$dbname = "meeting_scheduler";

// Create connection
<?php
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
<?php
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";