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
    echo json_encode([]);
    exit();
}

// Build the query with optional filters
$query = "SELECT ts.id, u.first_name, u.surname, u.email, m.date, ts.start_time, ts.end_time, m.purpose
          FROM time_slots ts
          JOIN meetings m ON ts.meeting_id = m.id
          JOIN users u ON m.user_id = u.id
          WHERE ts.status = 'booked'";

$conditions = [];
$params = [];
$types = "";

// Check for search parameters
if(isset($_GET['name']) && !empty($_GET['name'])){
    $conditions[] = "(u.first_name LIKE ? OR u.surname LIKE ?)";
    $params[] = '%' . $_GET['name'] . '%';
    $params[] = '%' . $_GET['name'] . '%';
    $types .= "ss";
}

if(isset($_GET['email']) && !empty($_GET['email'])){
    // Corrected the condition to filter by email
    $conditions[] = "u.email LIKE ?";
    $params[] = '%' . $_GET['email'] . '%';
    $types .= "s";
}

if(isset($_GET['date']) && !empty($_GET['date'])){
    $conditions[] = "m.date = ?";
    $params[] = $_GET['date'];
    $types .= "s";
}

if(count($conditions) > 0){
    $query .= " AND " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($query);

if($stmt){
    if(count($params) > 0){
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];

    while($row = $result->fetch_assoc()){
        $bookings[] = $row;
    }

    echo json_encode($bookings);
} else {
    echo json_encode([]);
}

$conn->close();
?>