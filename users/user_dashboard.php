<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit();
}

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Check if there is a session message
if (isset($_SESSION['message'])) {
    // Display the message in an alert
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    // Unset the session message after displaying it
    unset($_SESSION['message']);
}

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Fetch the user data from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

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
    <link rel="stylesheet" href="../users/scss/modal.scss">
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
            <h1>USER DASHBOARD</h1>
        </div>
        <div class="dashboard-content">
            <div class="profile-container">
                <div class="profile-image">
                    <div class="image">
                        <img src="../images/profilee.png" alt="Profile Image">
                    </div>
                    <!-- Edit Account Btn -->
                    <div class="edit-btn">
                        <button class="add-btn" id="">
                            <i class="bi bi-pencil-square"></i>
                            Edit
                        </button>
                    </div>
                </div>
                <div class="vertical-hr"></div>
                <div class="info-table">
                    <table>
                        <h2>INFOMATION</h2>
                        <tr>
                            <th>Name</th>
                                <td>: <?php echo $user['full_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                                <td>: <?php echo $user['email']; ?></td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                                <td>: <?php echo $user['gender']; ?></td>
                        </tr>
                        <tr>
                            <th>Contact</th>
                                <td>: <?php echo $user['contact_no']; ?></td>
                        </tr>
                        <tr>
                            <th style="text-align: right;">Time In</th>
                            <td>: <?php echo date('h:i A', strtotime($user['start_time'])); ?></td>
                        </tr>
                        <tr>
                            <th>Time Out</th>
                            <td>: <?php echo date('h:i A', strtotime($user['end_time'])); ?></td>
                        </tr>
                    </table>
                        <div class="profile-btn">
                            <div class="shift-btn">
                                <a href="" style="text-transform: capitalize;"><strong><?php echo $user['shift_type']; ?> shift</strong></a>
                            </div>
                            <div class="in-btn">
                                <a href="time_in.php">Time In</a>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </main>
            <!-- Edit Account Modal -->
            <div id="editAccountModal" class="user-edit-modal">
                <div class="modal-content">
                    <span class="close-btn" id="closeModalBtn">&times;</span>
                    <div class="form-text">
                        <h2>Edit Account</h2>
                        <hr>
                    </div>
                    <form id="editAccountForm" method="POST" action="edit_user.php">
    <input type="hidden" id="user_id" name="user_id" value="<?php echo $user['user_id']; ?>">

    <div class="form-group">
        <div class="edit-input">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
        </div>
        <div class="edit-input">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
        </div>
    </div>

    <div class="form-group">
        <div class="edit-input">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter new password">
        </div>
        <div class="edit-input">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="Male" <?php echo $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo $user['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <div class="edit-input">
            <label for="contact_no">Contact No:</label>
            <input type="text" id="contact_no" name="contact_no" value="<?php echo $user['contact_no']; ?>" required>
        </div>
    </div>

    <div class="submit-group">
        <div class="submit-btn">
            <button type="submit">Save Changes</button>
        </div>
        <div class="submit-btn">
            <button type="button" id="cancelEditBtn" class="cancel-btn">Cancel</button>
        </div>
    </div>
</form>
                </div>
            <!-- Success Modal -->
            <div id="successModal" class="success-modal">
                <div class="modal-content">
                    <div class="success-container">
                        <h2>Successfully Edited Your Account</h2>
                        <button id="closeSuccessModal" class="close-btn">Close</button>
                    </div>
                </div>
            </div>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./edit_user.js"></script>
</body>
</html>
