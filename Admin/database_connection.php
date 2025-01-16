<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

// ...existing code...

// Database Connection for Admin Operations
$servername = "localhost";
$username = "root";
$password = "";

// Default database
$dbname = "k1_meeting_scheduler";

if (isset($_SESSION['selected_db'])) {
    $dbname = $_SESSION['selected_db'];
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>