<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    include 'database_connection.php';
    include 'functions.php';

    // Handle login
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Validate credentials directly
        $query = "SELECT password FROM admins WHERE username = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['logged_in'] = true;
        } else {
            $login_error = "Invalid username or password.";
        }
    }

    // Handle logout
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: index.php");
        exit;
    }

    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'];
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        $query = "UPDATE admins SET password = ? WHERE username = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }
        $stmt->bind_param("ss", $hashed_new_password, $_SESSION['username']);
        $stmt->execute();
        $stmt->close();

        $password_change_success = "Password changed successfully.";
    }

    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Admin Login
                    </div>
                    <div class="card-body">
                        <?php if (isset($login_error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary">Login</button>
                        </form>
                        <div class="mt-3">
                            <a href="../index.html" class="btn btn-secondary">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
        exit;
    }

    // Handle database selection
    if (isset($_POST['database'])) {
        $_SESSION['selected_db'] = $_POST['database'];
    }
    $servername = "localhost";
$username = "root";
$password = "";

    $selected_db = isset($_SESSION['selected_db']) ? $_SESSION['selected_db'] : 'k1_meeting_scheduler';
    $conn = new mysqli($servername, $username, $password, $selected_db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch All Meetings Logic
    if ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST') {
        // Modified SQL query to exclude 'time' as it does not exist in the 'meetings' table
        $stmt = $conn->prepare("SELECT meetings.id, meetings.date, meetings.purpose, users.first_name, users.surname FROM meetings JOIN users ON meetings.user_id = users.id GROUP BY meetings.id");
        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }
        $stmt->execute();
        $allMeetings = $stmt->get_result();
        $stmt->close();
        
        // Fetch Current Meeting Logic
        $stmtCurrent = $conn->prepare("SELECT meetings.id, meetings.date, MIN(time_slots.start_time) as start_time, MAX(time_slots.end_time) as end_time, meetings.purpose, users.first_name, users.surname FROM meetings JOIN users ON meetings.user_id = users.id JOIN time_slots ON meetings.id = time_slots.meeting_id WHERE meetings.date = CURDATE() AND time_slots.start_time <= CURTIME() AND time_slots.end_time >= CURTIME() GROUP BY meetings.id ORDER BY time_slots.start_time DESC LIMIT 1");
        if (!$stmtCurrent) {
            die("Preparation failed: " . $conn->error);
        }
        $stmtCurrent->execute();
        $currentMeeting = $stmtCurrent->get_result()->fetch_assoc();
        $stmtCurrent->close();

        // Modified Fetch Incoming Meetings Logic to retrieve only the next upcoming meeting
        $stmtIncoming = $conn->prepare("SELECT meetings.id, meetings.date, MIN(time_slots.start_time) as start_time, meetings.purpose, users.first_name, users.surname FROM meetings JOIN users ON meetings.user_id = users.id JOIN time_slots ON meetings.id = time_slots.meeting_id WHERE (meetings.date > CURDATE() OR (meetings.date = CURDATE() AND time_slots.start_time > CURTIME())) GROUP BY meetings.id ORDER BY meetings.date ASC, time_slots.start_time ASC LIMIT 1");
        if (!$stmtIncoming) {
            die("Preparation failed: " . $conn->error);
        }
        $stmtIncoming->execute();
        $incomingMeeting = $stmtIncoming->get_result()->fetch_assoc();
        $stmtIncoming->close();
    }

    // Handle Search Logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        $searchTerm = $_POST['search'];
        
        // Prepare and execute the search query
        $stmtSearch = $conn->prepare("SELECT meetings.id, meetings.date, meetings.purpose, users.first_name, users.surname FROM meetings JOIN users ON meetings.user_id = users.id WHERE meetings.date = ? GROUP BY meetings.id");
        if (!$stmtSearch) {
            die("Preparation failed: " . $conn->error);
        }
        $stmtSearch->bind_param("s", $searchTerm);
        $stmtSearch->execute();
        $searchResults = $stmtSearch->get_result();
        $stmtSearch->close();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <!-- Logo -->
        <div class="text-center mb-4">
            <img src="logo.png" alt="Logo" class="img-fluid" style="max-width: 350px;">
        </div>
        <!-- Back Button -->
        <div class="mb-4">
            <a href="../index.html" class="btn btn-secondary">Back to Home</a>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
        </div>
        <!-- Database Selection -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="database" class="form-label">Select Database:</label>
                <select name="database" id="database" class="form-select" onchange="this.form.submit()">
                    <option value="k1_meeting_scheduler" <?php if ($selected_db == 'k1_meeting_scheduler') echo 'selected'; ?>>K1 Meeting Scheduler</option>
                    <option value="k2_meeting_scheduler" <?php if ($selected_db == 'k2_meeting_scheduler') echo 'selected'; ?>>K2 Meeting Scheduler</option>
                </select>
            </div>
        </form>

        <!-- Logout Button -->
        <form method="POST" action="">
            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
        </form>

        <!-- Current Meeting -->
        <div class="card mb-4">
            <div class="card-header">
                Current Meeting
            </div>
            <div class="card-body">
                <?php
                if (isset($currentMeeting)) {
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead class="table-dark"><tr><th>ID</th><th>Date</th><th>Time</th><th>Purpose</th><th>User</th><th>Actions</th></tr></thead>';
                    echo '<tbody>';
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($currentMeeting['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($currentMeeting['date']) . '</td>';
                    echo '<td>' . htmlspecialchars($currentMeeting['start_time'] . ' - ' . $currentMeeting['end_time']) . '</td>';
                    echo '<td>' . htmlspecialchars($currentMeeting['purpose']) . '</td>';
                    echo '<td>' . htmlspecialchars($currentMeeting['first_name'] . " " . $currentMeeting['surname']) . '</td>';
                    echo '<td>';
                    echo '<button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="' . $currentMeeting['id'] . '">Delete</button>';
                    echo '</td>';
                    echo '</tr>';
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div class="alert alert-info">No current meetings available.</div>';
                }
                ?>
            </div>
        </div>

        <!-- Incoming Meetings -->
        <div class="card mb-4">
            <div class="card-header">
                Incoming Meeting
            </div>
            <div class="card-body">
                <?php
                if (isset($incomingMeeting)) {
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead class="table-dark"><tr><th>ID</th><th>Date</th><th>Time</th><th>Purpose</th><th>User</th><th>Actions</th></thead>';
                    echo '<tbody>';
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($incomingMeeting['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($incomingMeeting['date']) . '</td>';
                    echo '<td>' . htmlspecialchars($incomingMeeting['start_time']) . '</td>';
                    echo '<td>' . htmlspecialchars($incomingMeeting['purpose']) . '</td>';
                    echo '<td>' . htmlspecialchars($incomingMeeting['first_name'] . " " . $incomingMeeting['surname']) . '</td>';
                    echo '<td>';
                    echo '<button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="' . $incomingMeeting['id'] . '">Delete</button>';
                    echo '</td>';
                    echo '</tr>';
                    echo '</tbody>';
                    echo '</table>';

                    // Add Modern Countdown Timer within a Bootstrap Card
                    echo '<div class="card mt-4">';
                    echo '    <div class="card-header">';
                    echo '        <h5 class="mb-0">Time Until Meeting</h5>';
                    echo '    </div>';
                    echo '    <div class="card-body">';
                    echo '        <div id="countdown" class="countdown-container">';
                    echo '            <div class="countdown-item">';
                    echo '                <span id="days">0</span>';
                    echo '                <span class="countdown-label">Days</span>';
                    echo '            </div>';
                    echo '            <div class="countdown-item">';
                    echo '                <span id="hours">0</span>';
                    echo '                <span class="countdown-label">Hours</span>';
                    echo '            </div>';
                    echo '            <div class="countdown-item">';
                    echo '                <span id="minutes">0</span>';
                    echo '                <span class="countdown-label">Minutes</span>';
                    echo '            </div>';
                    echo '            <div class="countdown-item">';
                    echo '                <span id="seconds">0</span>';
                    echo '                <span class="countdown-label">Seconds</span>';
                    echo '            </div>';
                    echo '        </div>';
                    echo '    </div>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-info">No upcoming meetings available.</div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Search Meetings -->
        <div class="card mb-4">
            <div class="card-header">
                Search Meetings
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="input-group">
                        <input type="date" class="form-control" name="search" placeholder="Search by Date" required>
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- List of All Meetings -->
        <div class="card">
            <div class="card-header">
                List of All Meetings
            </div>
            <div class="card-body">
                <?php
                if (isset($allMeetings) && $allMeetings->num_rows > 0) {
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead class="table-dark"><tr><th>ID</th><th>Date</th><th>Time</th><th>Purpose</th><th>User</th><th>Actions</th></thead>';
                    echo '<tbody>';
                    while($meeting = $allMeetings->fetch_assoc()) {
                        // Combine multiple time slots
                        $timeSlots = getCombinedTimeSlots($meeting['id']);
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($meeting['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($meeting['date']) . '</td>';
                        echo '<td>' . htmlspecialchars(implode(', ', $timeSlots)) . '</td>';
                        echo '<td>' . htmlspecialchars($meeting['purpose']) . '</td>';
                        echo '<td>' . htmlspecialchars($meeting['first_name'] . " " . $meeting['surname']) . '</td>';
                        echo '<td>';
                        echo '<button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="' . $meeting['id'] . '">Delete</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div class="alert alert-info">No meetings available.</div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Search Results -->
        <?php if (isset($searchResults)): ?>
        <div class="card mt-4">
            <div class="card-header">Search Results:</div>
            <div class="card-body">
                <?php
                if ($searchResults->num_rows > 0) {
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>ID</th><th>Date</th><th>Time</th><th>Purpose</th><th>User</th><th>Actions</th></thead>';
                    echo '<tbody>';
                    while($row = $searchResults->fetch_assoc()) {
                        // Combine multiple time slots
                        $timeSlots = getCombinedTimeSlots($row['id']);
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['date']) . '</td>';
                        echo '<td>' . htmlspecialchars(implode(', ', $timeSlots)) . '</td>';
                        echo '<td>' . htmlspecialchars($row['purpose']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['first_name'] . " " . $row['surname']) . '</td>';
                        echo '<td>';
                        echo '<button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="' . $row['id'] . '">Delete</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                } else {
                    echo '<div class="alert alert-info">No meetings found.</div>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Delete Meeting Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="delete_meeting.php">
                  <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <input type="hidden" name="meeting_id" id="delete-meeting-id">
                      <p>Are you sure you want to delete this meeting?</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="">
                  <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <div class="mb-3">
                          <label for="new_password" class="form-label">New Password</label>
                          <input type="password" class="form-control" id="new_password" name="new_password" required>
                      </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                  </div>
              </form>
            </div>
          </div>
        </div>
    </div>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate Delete Modal with meeting ID
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
          var button = event.relatedTarget;
          var meetingId = button.getAttribute('data-id');
          
          document.getElementById('delete-meeting-id').value = meetingId;
        });

        // Add JavaScript for Modern Countdown Timer
        <?php if (isset($incomingMeeting)): ?>
        // Set the date and time of the upcoming meeting
        var meetingDateTime = "<?php echo htmlspecialchars($incomingMeeting['date'] . ' ' . $incomingMeeting['start_time']); ?>";
    
        // Convert to timestamp
        var meetingTime = new Date(meetingDateTime).getTime();
    
        // Update the count down every 1 second
        var countdownFunction = setInterval(function() {
    
            // Get current time
            var now = new Date().getTime();
    
            // Find the distance between now and the meeting time
            var distance = meetingTime - now;
    
            // Time calculations
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
            // Display the result in respective elements
            document.getElementById("days").innerHTML = days;
            document.getElementById("hours").innerHTML = hours;
            document.getElementById("minutes").innerHTML = minutes;
            document.getElementById("seconds").innerHTML = seconds;
    
            // If the count down is over, write some text 
            if (distance < 0) {
                clearInterval(countdownFunction);
                document.getElementById("countdown").innerHTML = "Meeting has started.";
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
</body>
</html>