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
    <link rel="stylesheet" href="../admin/scss/dashboard.scss"> 
    <link rel="stylesheet" href="../admin/scss/btn.scss">    
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
                    <h1>ADMIN DASHBOARD</h1>
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
            <div class="box-content">
                <div class="box box1">
                    <span class="icon">
                        <i class="bi bi-people-fill"></i>
                    </span>
                    <p>Total Users: 1</p>
                </div>
                <div class="box box2">
                    <span class="icon">
                        <i class="bi bi-person-fill-check"></i>
                    </span>
                    <p>Time-In Today: 1</p>
                </div>
                <div class="box box3">
                    <span class="icon">
                        <i class="bi bi-person-fill-x"></i>
                    </span>
                    <p>Time-Out Today: 1</p>
                </div>
                <div class="box box4">
                    <span class="icon">
                        <i class="bi bi-person-fill-exclamation"></i>
                    </span>
                    <p>Absent Today: 1</p>
                </div>
            </div>
            <div class="user-content">
                <div class="pie-chart-container">
                    <h2>User Status Today</h2>
                    <hr>
                    <canvas id="userStatusPieChart"></canvas>
                </div>
                <div class="recent-container">
                    <h2>Recently Logged-in Users</h2>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Time-In Record -->
                            <tr>
                                <td>Neil Alferez</td>
                                <td>Neil Alferez</td>
                                <td>
                                    <span class="time-in-status">Time-In</span>
                                </td>
                            </tr>
                            <!-- Time-Out Record (If exists) -->

                            <tr>
                                <td>Neil Alferez</td>
                                <td>Neil Alferez</td>
                                <td>
                                    <span class="time-out-status">Time-Out</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">No users logged in today.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Ensure proper data passing from PHP to JS
        var late = 1;
        var onTime = 1;
        var overTime = 1;
        var absent = 1;
        var onLeave = 1;

        var ctx = document.getElementById('userStatusPieChart').getContext('2d');
        var userStatusPieChart = new Chart(ctx, {
            type: 'pie',  // Pie chart type
            data: {
                labels: ['Late', 'On Time', 'Over Time', 'Absent', 'On Leave'],  // Labels for the chart
                datasets: [{
                    data: [late, onTime, overTime, absent, onLeave],  // Dataset from PHP variables
                    backgroundColor: ['#ff6347', '#4caf50', '#ffa500', '#f44336', '#2196f3'],  // Colors for each section
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',  // Position the legend at the top
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;  // Display the count in the tooltip
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>
</body>
</html>
