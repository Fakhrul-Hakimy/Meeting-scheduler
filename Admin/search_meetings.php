<?php
// ...existing code...

// Search Meetings Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $searchTerm = $_POST['search'];
    
    // Modified SQL query to exclude 'time' as it does not exist in the 'meetings' table
    $stmt = $conn->prepare("SELECT meetings.id, meetings.date, meetings.purpose, users.first_name, users.surname FROM meetings JOIN users ON meetings.user_id = users.id WHERE meetings.id LIKE ?");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }
    $likeTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>Search Results:</h3>";
    if ($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_assoc()) {
            // Combine multiple time slots
            $timeSlots = getCombinedTimeSlots($row['id']);
            echo "<li>";
            echo "ID: " . htmlspecialchars($row['id']) . "<br>";
            echo "Date: " . htmlspecialchars($row['date']) . "<br>";
            echo "Time: " . htmlspecialchars(implode(', ', $timeSlots)) . "<br>";
            echo "Purpose: " . htmlspecialchars($row['purpose']) . "<br>";
            echo "User: " . htmlspecialchars($row['first_name'] . " " . $row['surname']) . "<br>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "No meetings found.";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Meetings</title>
</head>
<body>
    <h2>Search Meetings</h2>
    <form method="POST" action="">
        <label for="search">Search by Title:</label>
        <input type="text" id="search" name="search" required>
        <input type="submit" value="Search">
    </form>
    <!-- Display search results -->
</body>
</html>