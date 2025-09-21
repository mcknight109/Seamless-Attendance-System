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
    <title>Leave Requests</title>
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
                    <h1>SCHEDULE MANAGEMENT</h1>
                    <p id="current-date">Wed, January 20, 2026</p>
                    <p id="current-time">Time: 01:20 PM</p>
                </div>
                <div class="welcome-message">
                    <span class="icon">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    <!-- Display the admin's full name -->
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
                            <form method="get">
                                <button type="submit" name="filter" value="approved" class="btn btn-primary">
                                    Requests
                                </button>
                                 <button type="submit" name="filter" value="approved" class="btn btn-primary">
                                    Add Schedule
                                </button>
                            </form>
                        </div>
                    </div>     
                </div>

                <div class="manage-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Shift Type</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Admin Neil</td>
                                <td>Admin Neil</td>
                                <td>Admin Neil</td>
                                <td>Admin Neil</td>
                                <td>Admin Neil</td>
                                <td class="btn-actions">
                                    <form method="POST">
                                        <button class='btn btn-sm btn-outline-secondary edit-action' type="submit" name="" value="">
                                            <i class='bi bi-pencil-square'></i>
                                        </button>
                                        <button class='btn btn-sm btn-outline-danger delete-action' type="submit" name="" value="">
                                            <i class='bi bi-trash'></i>
                                        </button>
                                    </form>
                                    <span class="badge bg-secondary"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
    <div class="sched-form" style="display: none;">
        <form action="add_schedule.php" method="POST">
            <div class="mb-3">
                <label for="shift_type" class="form-label">Type of Shift</label>
                <select id="shift_type" name="shift_type" class="form-select" required>
                    <option value="morning">Morning Shift</option>
                    <option value="afternoon">Afternoon Shift</option>
                    <option value="night">Night Shift</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="start_time" class="form-label">Shift Start Time</label>
                <input type="time" id="start_time" name="start_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="end_time" class="form-label">Shift End Time</label>
                <input type="time" id="end_time" name="end_time" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Schedule</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>
</body>
</html>
