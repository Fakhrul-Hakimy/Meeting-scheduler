<?php
require_once 'database_connection.php';

// Delete Meeting Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $meetingId = intval($_POST['meeting_id']);
    
    // Delete associated time slots
    $deleteTimeSlotsQuery = "DELETE FROM time_slots WHERE meeting_id = ?";
    $stmt = $conn->prepare($deleteTimeSlotsQuery);
    $stmt->bind_param("i", $meetingId);
    $stmt->execute();
    $stmt->close();
    
    // Delete meeting
    $deleteMeetingQuery = "DELETE FROM meetings WHERE id = ?";
    $stmt = $conn->prepare($deleteMeetingQuery);
    $stmt->bind_param("i", $meetingId);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?message=Meeting deleted successfully.");
        exit();
    } else {
        echo "Error deleting meeting: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Meeting</title>
</head>
<body>
    <h2>Delete Meeting</h2>
    <?php if (isset($_GET['id'])): ?>
        <form method="POST" action="">
            <p>Are you sure you want to delete meeting ID: <?php echo intval($_GET['id']); ?>?</p>
            <input type="hidden" name="meeting_id" value="<?php echo intval($_GET['id']); ?>">
            <input type="submit" name="confirm" value="Yes, Delete">
            <a href="admin_dashboard.php">Cancel</a>
        </form>
    <?php else: ?>
        <form method="GET" action="">
            <label for="id">Meeting ID:</label>
            <input type="text" id="id" name="id" required>
            <input type="submit" value="Delete Meeting">
        </form>
    <?php endif; ?>
</body>
</html>