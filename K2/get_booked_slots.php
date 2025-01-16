<?php
// Suppress error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Database connection parameters
$host = 'localhost';
$db   = 'k2_meeting_scheduler';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Set up PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode([]);
    exit;
}

// Get the date from POST request and validate it
$date = isset($_POST['date']) ? $_POST['date'] : '';

if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    // Assuming there's a bookings table with date, start_time, end_time, status
    $sql = "SELECT ts.start_time, ts.end_time 
            FROM time_slots ts 
            JOIN meetings m ON ts.meeting_id = m.id 
            WHERE m.date = ? AND ts.status = 'booked'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    $bookings = $stmt->fetchAll();

    $bookedSlots = [];
    foreach ($bookings as $booking) {
        // Format to "HH:MM:SS - HH:MM:SS"
        $bookedSlots[] = "{$booking['start_time']} - {$booking['end_time']}";
    }

    echo json_encode($bookedSlots);
} else {
    echo json_encode([]);
}
?>
