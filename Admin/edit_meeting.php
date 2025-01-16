<?php
include 'database_connection.php';

// Fetch current meeting details
$meeting_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT date, location, description FROM meetings WHERE id = ?");
$stmt->bind_param("i", $meeting_id);
$stmt->execute();
$meeting = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Edit Meeting Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and update meeting details in the database
    $meeting_id = intval($_POST['meeting_id']);
    $date = $_POST['date'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    
    // Server-side validation
    if (empty($date) || empty($location) || empty($description)) {
        echo "All fields are required.";
    } else {
        // Update meetings table
        $sql = "UPDATE meetings SET date=?, location=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $date, $location, $description, $meeting_id);
        
        if ($stmt->execute()) {
            // Remove existing time slots
            $deleteTimeSlotsQuery = "DELETE FROM time_slots WHERE meeting_id = ?";
            $stmtDelete = $conn->prepare($deleteTimeSlotsQuery);
            $stmtDelete->bind_param("i", $meeting_id);
            $stmtDelete->execute();
            $stmtDelete->close();
            
            // Insert new time slots
            if (isset($_POST['time_slots']) && is_array($_POST['time_slots'])) {
                $insertTimeSlotQuery = "INSERT INTO time_slots (meeting_id, start_time, end_time) VALUES (?, ?, ?)";
                $stmtInsert = $conn->prepare($insertTimeSlotQuery);
                foreach ($_POST['time_slots'] as $start_time) {
                    $start = date("H:i:s", strtotime($start_time));
                    $end = date("H:i:s", strtotime($start_time . ' +1 hour'));
                    $stmtInsert->bind_param("iss", $meeting_id, $start, $end);
                    $stmtInsert->execute();
                }
                $stmtInsert->close();
            }
            
            header("Location: admin_dashboard.php?message=Meeting updated successfully.");
            exit();
        } else {
            echo "Error updating meeting: " . $conn->error;
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Meeting</title>
    <!-- Include Bootstrap CSS and any other needed CSS/JS libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Meeting</h2>
        <form method="POST" action="">
            <input type="hidden" name="meeting_id" value="<?php echo intval($_GET['id']); ?>">
            <label for="date">Date:</label>
            <input type="date" id="edit-date" name="date" value="<?php echo htmlspecialchars($meeting['date']); ?>" required class="form-control mb-3">
            
            <!-- Time Slots Section -->
            <label>Time Slots:</label>
            <div id="time-slots-container" class="mb-3">
                <?php
                // Fetch existing time slots for the meeting
                $stmtTimeSlots = $conn->prepare("SELECT start_time FROM time_slots WHERE meeting_id = ?");
                $stmtTimeSlots->bind_param("i", $meeting_id);
                $stmtTimeSlots->execute();
                $resultTimeSlots = $stmtTimeSlots->get_result();
                while ($row = $resultTimeSlots->fetch_assoc()) {
                    $start_time = date("H:i", strtotime($row['start_time']));
                    echo '<div class="input-group mb-2 time-slot">
                            <input type="time" name="time_slots[]" value="' . htmlspecialchars($start_time) . '" class="form-control" required>
                            <button type="button" class="btn btn-danger remove-time-slot">Remove</button>
                          </div>';
                }
                $stmtTimeSlots->close();
                ?>
                <!-- Initially, can have one time slot if none exist -->
                <?php if ($resultTimeSlots->num_rows == 0): ?>
                    <div class="input-group mb-2 time-slot">
                        <input type="time" name="time_slots[]" class="form-control" required>
                        <button type="button" class="btn btn-danger remove-time-slot">Remove</button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" class="btn btn-secondary mb-3" id="add-time-slot">Add Time Slot</button><br><br>
            
            <label for="location">Location:</label>
            <input type="text" id="edit-location" name="location" value="<?php echo htmlspecialchars($meeting['location']); ?>" required class="form-control mb-3">
            <label for="description">Description:</label>
            <textarea id="edit-description" name="description" required class="form-control mb-3"><?php echo htmlspecialchars($meeting['description']); ?></textarea><br><br>
            <input type="submit" value="Update Meeting" class="btn btn-primary">
        </form>
    </div>
    
    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('add-time-slot').addEventListener('click', function() {
            var container = document.getElementById('time-slots-container');
            var div = document.createElement('div');
            div.className = 'input-group mb-2 time-slot';
            div.innerHTML = '<input type="time" name="time_slots[]" class="form-control" required>' +
                            '<button type="button" class="btn btn-danger remove-time-slot">Remove</button>';
            container.appendChild(div);
        });
        
        document.getElementById('time-slots-container').addEventListener('click', function(e) {
            if (e.target && e.target.matches('button.remove-time-slot')) {
                e.target.parentElement.remove();
            }
        });
    </script>
</body>
</html>