<?php
session_start();
include '../db_connection.php';

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Check if user is logged in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
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
    <link rel="stylesheet" href="../admin/scss/admin.scss">
    <link rel="stylesheet" href="../admin/scss/table.scss">   
    <link rel="stylesheet" href="../admin/scss/btn.scss"> 
    <link rel="stylesheet" href="../admin/scss/dashboard.scss"> 
    <title>Attendance</title>
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
            <span class="category">Admin</span>
            <a class="nav-link" href="admin_dashboard.php">
                <span class="icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="description">Dashboard</span>
            </a>
            <hr>
            <span class="category">Management</span>
            <a class="nav-link" href="users_management.php">
                <span class="icon">
                    <i class="bi bi-people"></i>
                </span>
                <span class="description">Users</span>
            </a>
            <a class="nav-link" href="attendance.php">
                <span class="icon">
                    <i class="bi bi-list-check"></i>
                </span>
                <span class="description">Attendance</span>
            </a>
            <a class="nav-link" href="leave_requests.php">
                <span class="icon">
                    <i class="bi bi-chat-left-text"></i>
                </span>
                <span class="description">Leave Requests</span>
            </a>
            <a class="nav-link" href="schedule.php">
                    <span class="icon">
                        <i class="bi bi-clipboard"></i>
                    </span>
                    <span class="description">Schedule</span>
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
        <div class="content-container">
            <div class="header">
                <div class="page-title">
                    <h1>ATTENDANCE RECORD</h1>
                    <p id="current-date">Wed, January 20, 2026</p>
                    <p id="current-time">Time: 01:20 PM</p>
                </div>
                <div class="welcome-message">
                    <span class="icon">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    Welcome, Admin Neil
                </div>
            </div>
            <div class="dashboard-content">
                <div class="table-top">
                    <!-- <div class="top1">
                    </div> -->
                    <div class="top2">
                        <div class="search-container">
                            <form method="get" class="search-form d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Search user or email" value="">
                                <button type="submit" class="btn btn-primary ms-2">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </form>
                        </div>
                        <div class="table-btn">
                            <form method="post" class="d-flex align-items-center">
                                <label for="filter_date" style="font-size: 12px;">Select Date:</label>
                                <input type="date" name="filter_date" id="filter_date" class="form-control me-2" value="<?php echo htmlspecialchars($selected_date); ?>">
                                <button type="submit" class="btn btn-primary">
                                    Filter
                                </button>
                            </form>
                            <button id="download-pdf" class="btn btn-primary">
                                Print Attendace
                            </button>
                        </div>
                    </div>     
                </div>

                <div class="manage-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Shift Type</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <!-- Inside the table where you loop through the results -->
                        <tbody>
                            <tr>
                                <td>Alex Cruz</td>
                                <td>Alex Cruz</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td><span>Absent</span></td>
                            </tr>
                            <tr>
                                <td colspan='6'>No attendance records found for the selected date.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination-container">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" href=""><-</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="">1</a>
                            </li>
                        
                            <li class="page-item">
                                <a class="page-link" href="">-></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>
</body>
</html>
