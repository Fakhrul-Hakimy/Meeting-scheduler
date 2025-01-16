<?php
session_start();
$booking = isset($_SESSION['booking']) ? $_SESSION['booking'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Scheduled</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if ($booking): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h2>Meeting Scheduled Successfully</h2>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Meeting Details</h5>
                    <p class="card-text"><strong>First Name:</strong> <?php echo htmlspecialchars($booking['first_name']); ?></p>
                    <p class="card-text"><strong>Surname:</strong> <?php echo htmlspecialchars($booking['surname']); ?></p>
                    <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                    <p class="card-text"><strong>Purpose:</strong> <?php echo htmlspecialchars($booking['purpose']); ?></p>
                    <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($booking['date']); ?></p>
                    <hr>
                    <h5 class="card-title">Time Slots:</h5>
                    <?php foreach ($booking['timeSlots'] as $slot): ?>
                        <div class="alert alert-info" role="alert">
                            <strong>Time Slot:</strong> <?php echo htmlspecialchars($slot); ?>
                        </div>
                    <?php endforeach; ?>
                    <a href="index.html" class="btn btn-primary">Schedule Another Meeting</a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">No Booking Details Found!</h4>
                <p>There was an issue retrieving your booking details. Please try scheduling a meeting again.</p>
                <hr>
                <a href="index.html" class="btn btn-danger">Go Back</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    // Clear booking details from session
    unset($_SESSION['booking']);
    ?>
</body>
</html>