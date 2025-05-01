<?php
session_start();
include '../db_connection.php';

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, role, shift_type, start_time, end_time FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $email = $user['email'];
    $role = $user['role'];
    $shift_type = $user['shift_type'];
    $start_time = $user['start_time']; // Assume this is stored in "HH:MM:SS" format
    $end_time = $user['end_time'];     // Assume this is stored in "HH:MM:SS" format
} else {
    echo "User not found.";
    exit();
}

// Get today's date
$today_date = date("Y-m-d"); // Format: YYYY-MM-DD for database comparison

// Fetch today's attendance
$query = "SELECT time_in, time_out, status FROM attendance WHERE user_id = ? AND date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $today_date);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_assoc();

$time_in = $attendance['time_in'] ?? null;
$time_out = $attendance['time_out'] ?? null;
$status = $attendance['status'] ?? 'Absent'; // Default status if no record exists

$message = '';

// Fetch schedule request status for the user (assuming you have a table 'schedule_requests')
$query = "SELECT status FROM schedule_requests WHERE user_id = ? ORDER BY request_date DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$request_status = 'No Request'; // Default status

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $request_status = $row['status']; // Fetch the last request's status
}

// Existing code to fetch user and attendance data

// Function to calculate the status based on the time-in logic
function calculate_status($time_in, $start_time, $end_time) {
    if ($time_in === null) {
        return 'Absent'; // If no time-in, user is considered absent
    }

    // Convert string times to timestamps for comparison
    $current_time = strtotime($time_in);
    $shift_start = strtotime($start_time);
    $shift_end = strtotime($end_time);

    // 2 hours grace period after start_time
    $grace_period_end = strtotime("+2 hours", $shift_start);

    // Check if the time_in is within the grace period (on-time)
    if ($current_time <= $grace_period_end) {
        return 'On-time';
    } 
    // Check if time_in is within the shift time (Late but within shift hours)
    elseif ($current_time <= $shift_end) {
        return 'Late';
    } 
    // Check if the time_in is after the shift end time (Over-time)
    else {
        return 'Over-time';
    }
}

// Update the status after Time In is recorded
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['time_in']) && $time_in === null) {
        $current_time = date('H:i:s'); // Get the current time in "HH:MM:SS" format
        $status = calculate_status($current_time, $start_time, $end_time); // Calculate the status

        // Insert Time In record into the database
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, time_in, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $today_date, $current_time, $status);

        if ($stmt->execute()) {
            $message = "Time In recorded at $current_time. Status: $status.";
            $time_in = $current_time; // Update locally
        } else {
            $message = "Failed to record Time In.";
        }
    } elseif (isset($_POST['time_out']) && $time_out === null) {
        $current_time = date('H:i:s'); // Get the current time in "HH:MM:SS" format
        $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE user_id = ? AND date = ?");
        $stmt->bind_param("sis", $current_time, $user_id, $today_date);
        if ($stmt->execute()) {
            $message = "Time Out recorded at $current_time.";
            $time_out = $current_time; // Update locally
        } else {
            $message = "Failed to record Time Out.";
        }
    }
}

// Function to format time to 12-hour format with AM/PM
function format_time($time) {
    return ($time != null && $time != 'N/A') ? date('h:i A', strtotime($time)) : 'Not yet';
}

// Set allowed Time In and Time Out window
$allowed_time_in_start = '07:00 AM';
$allowed_time_in_end = '09:00 AM';
$allowed_time_out_start = '04:00 PM';
$allowed_time_out_end = '06:00 PM';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../users/scss/user.scss">
    <link rel="stylesheet" href="../users/scss/table.scss">    
    <link rel="stylesheet" href="../users/scss/btn.scss"> 
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link logo-link" href="admin_dashboard.php">
                <span class="icon">
                    <i class="bi bi-cpu"></i>
                </span>
                <span class="description">S.A.S</span>
            </a>
            <span class="category">Profile</span>
            <a class="nav-link" href="user_dashboard.php">
                <span class="icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="description">Profile</span>
            </a>
            <hr>
            <span class="category">Attendance</span>
            <a class="nav-link" href="time_in.php">
                <span class="icon">
                    <i class="bi bi-people"></i>
                </span>
                <span class="description">Time-in</span>
            </a>
            <!-- <a class="nav-link" href="attendance_log.php">
                <span class="icon">
                    <i class="bi bi-list-check"></i>
                </span>
                <span class="description">Attendance Log</span>
            </a> -->
            <a class="nav-link" href="request_leave.php">
                <span class="icon">
                    <i class="bi bi-chat-left-text"></i>
                </span>
                <span class="description">Request Leave</span>
            </a>
            <hr>
            <a class="nav-link" href="../logout.php">
                <span class="icon">
                    <i class="bi bi-box-arrow-right"></i>
                </span>
                <span class="description">Logout</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="header">
            <div class="welcome-message">
                <span class="icon">
                <i class="bi bi-person-circle"></i>
                </span>
                Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
            </div>
        </div>

        <div class="page-title">
            <h1>TIME-IN</h1>
        </div>

        <div class="dashboard-content">
            <div class="in-container">
                <div class="date-in">
                    <div class="label-display">
                        <h1>ATTENDANCE TURN-IN</h1>
                    </div>
                    <!-- Display the current date -->
                    <div class="date-display">
                        <p>Date: <?php echo date("F, d Y", strtotime($today_date)); ?></p>
                    </div>
                    <!-- Display the current time -->
                    <div class="time-display" id="current-time">
                    <p>Time: <span id="time"></span></p>
                    </div>
                    <div class="status-display">
                        <p>Status: <?php echo htmlspecialchars($status); ?></p>
                    </div>
                </div>
                <div class="status">
                    <div class="in-status">
                        <p><strong>Time In:</strong><br><?php echo format_time($time_in); ?> </p>
                    </div>
                    <div class="out-status">
                        <p><strong>Time Out:</strong><br><?php echo format_time($time_out); ?> </p>
                    </div>
                </div>

            <div class="button-submit">
                <form method="POST">
                    <?php if ($time_in === null): ?>
                        <!-- Display both Time In and Time Out buttons initially -->
                        <div class="in-button">
                            <button type="submit" name="time_in">
                                <i class="bi bi-box-arrow-in-right"></i> Time In
                            </button>
                        </div>
                    <?php elseif ($time_out === null): ?>
                        <!-- Only Time Out button is displayed after Time In is clicked -->
                        <div class="in-button">
                            <button type="submit" name="time_out">
                                <i class="bi bi-box-arrow-in-left"></i>
                                Time Out
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Message indicating both actions are completed -->
                        <p>You have already completed your Time In and Time Out for today.</p>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?> 
                </form>
            </div>
                <!-- Display allowed time range for Time In and Time Out -->
                <div class="time-info">
                    <!-- Button to open the modal for requesting schedule -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestScheduleModal">
                        Request Schedule
                    </button>
                    <!-- Display the current status of the request -->
                    <div class="request-status">
                        <p><strong>Status of Schedule Request:</strong> <?php echo htmlspecialchars($request_status); ?></p>
                    </div>
                </div>
            </div>    
        </div>
    </main>
    <!-- Modal for requesting schedule -->
    <div class="modal fade" id="requestScheduleModal" tabindex="-1" aria-labelledby="requestScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestScheduleModalLabel">Request Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleRequestForm">
                        <div class="mb-3">
                            <label for="shift_type" class="form-label">Shift Type</label>
                            <select class="form-select" id="shift_type" name="shift_type" required>
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('scheduleRequestForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        // Create FormData object to send the form data
        var formData = new FormData(this);

        // Use AJAX to submit the form data
        fetch('submit_schedule_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Parse the JSON response
        .then(data => {
            if (data.status === 'success') {
                alert(data.message); // Show success message
                $('#requestScheduleModal').modal('hide'); // Close the modal
            } else {
                alert(data.message); // Show error message
            }
        })
        .catch(error => {
            alert('An error occurred while submitting the request. Please try again.');
        });
    });
    </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../login/time.js"></script>
</body>
</html>