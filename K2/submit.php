<?php
session_start(); // Start the session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "k2_meeting_scheduler";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $date = $_POST['date'];
    $timeSlots = explode(',', $_POST['timeSlots']); // Expecting time slots separated by commas
    $purpose = $_POST['purpose'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert user if not exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userId = $row['id'];
        } else {
            $sql = "INSERT INTO users (first_name, surname, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $firstName, $surname, $email);
            $stmt->execute();
            $userId = $stmt->insert_id;
        }

        // Insert meeting
        $sql_insert_meeting = "INSERT INTO meetings (user_id, date, purpose) VALUES (?, ?, ?)";
        $stmt_meeting = $conn->prepare($sql_insert_meeting);
        $stmt_meeting->bind_param("iss", $userId, $date, $purpose);
        $stmt_meeting->execute();
        $meetingId = $stmt_meeting->insert_id;
        $stmt_meeting->close();

        // Insert multiple time slots
        $sql_insert_slot = "INSERT INTO time_slots (meeting_id, start_time, end_time, status) VALUES (?, ?, ?, 'booked')";
        $stmt_slot = $conn->prepare($sql_insert_slot);
        foreach ($timeSlots as $slot) {
            list($startTime, $endTime) = explode(' - ', trim($slot));
            $stmt_slot->bind_param("iss", $meetingId, $startTime, $endTime);
            $stmt_slot->execute();
        }
        $stmt_slot->close();

        // Commit transaction
        $conn->commit();

        // Store booking details in session
        $_SESSION['booking'] = [
            'first_name' => $firstName,
            'surname' => $surname,
            'email' => $email,
            'purpose' => $purpose,
            'date' => $date,
            'timeSlots' => $timeSlots
        ];

        // Close statements
        $stmt->close();

        // Close connection
        $conn->close();

        // Redirect to a success page
        header("Location: success.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        // Close connection
        $conn->close();
        // Optionally, redirect to an error page with the error message
        echo "Error: " . $e->getMessage();
        exit();
    }
}
?>
