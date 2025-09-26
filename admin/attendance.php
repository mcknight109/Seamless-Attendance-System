<?php
session_start();
include '../db_connection.php';

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Check if user is logged in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// --- Search and Filter ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

// --- Pagination ---
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Base Query ---
$sql = "
    SELECT a.*, u.fname, u.lname, u.email, s.shift_name, s.start_time, s.end_time
    FROM attendance a
    JOIN users u ON a.user_id = u.user_id
    LEFT JOIN schedules s ON a.schedule_id = s.schedule_id
    WHERE 1
";

$count_sql = "
    SELECT COUNT(*) as total
    FROM attendance a
    JOIN users u ON a.user_id = u.user_id
    LEFT JOIN schedules s ON a.schedule_id = s.schedule_id
    WHERE 1
";

// --- Add Filters ---
$conditions = [];
$params = [];
$param_types = "";

// Search filter
if (!empty($search)) {
    $conditions[] = "(u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "sss";
}

// Date filter
if (!empty($selected_date)) {
    $conditions[] = "a.date = ?";
    $params[] = $selected_date;
    $param_types .= "s";
}

// Add conditions to queries
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
    $count_sql .= " AND " . implode(" AND ", $conditions);
}

// Order + Pagination
$sql .= " ORDER BY a.date DESC, a.time_in ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= "ii";

// --- Prepare and Execute Main Query ---
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$attendances = $result->fetch_all(MYSQLI_ASSOC);

// --- Count for Pagination ---
$count_stmt = $conn->prepare($count_sql);
if (!empty($conditions)) {
    // bind only search/date params (exclude limit & offset)
    $count_stmt->bind_param(substr($param_types, 0, -2), ...array_slice($params, 0, -2));
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
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
                <span class="icon"><i class="bi bi-cpu"></i></span>
                <span class="description">S.A.S</span>
            </a>
            <span class="category">Admin</span>
            <a class="nav-link" href="admin_dashboard.php"><span class="icon"><i class="bi bi-bounding-box"></i></span><span class="description">Dashboard</span></a>
            <hr>
            <span class="category">Management</span>
            <a class="nav-link" href="users_management.php"><span class="icon"><i class="bi bi-people"></i></span><span class="description">Users</span></a>
            <a class="nav-link active" href="attendance.php"><span class="icon"><i class="bi bi-list-check"></i></span><span class="description">Attendance</span></a>
            <a class="nav-link" href="leave_requests.php"><span class="icon"><i class="bi bi-chat-left-text"></i></span><span class="description">Leave Requests</span></a>
            <a class="nav-link" href="schedule.php"><span class="icon"><i class="bi bi-clipboard"></i></span><span class="description">Schedule</span></a>
            <hr>
            <a class="nav-link" href="../logout.php"><span class="icon"><i class="bi bi-box-arrow-right"></i></span><span class="description">Logout</span></a>
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
                    <span class="icon"><i class="bi bi-person-circle"></i></span>
                    Welcome, Admin
                </div>
            </div>
            <div class="dashboard-content">
                <div class="table-top">
                    <div class="top2 d-flex justify-content-between">
                        <!-- Search -->
                        <div class="search-container">
                            <form method="get" class="search-form d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Search user or email" value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary ms-2"><i class="bi bi-search"></i> Search</button>
                            </form>
                        </div>
                        <!-- Date Filter -->
                        <div class="table-btn d-flex">
                            <form method="get" class="d-flex align-items-center me-2">
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <label for="filter_date" style="font-size: 12px;">Select Date:</label>
                                <input type="date" name="filter_date" id="filter_date" class="form-control mx-2" value="<?php echo htmlspecialchars($selected_date); ?>">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                            <button id="download-pdf" class="btn btn-primary">Print Attendace</button>
                        </div>
                    </div>     
                </div>

                <div class="manage-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Schedule</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($attendances) > 0): ?>
                                <?php foreach ($attendances as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['fname'].' '.$row['lname']); ?></td>
                                        <td>
                                            <?php 
                                                echo $row['shift_name'] 
                                                    ? htmlspecialchars($row['shift_name'])." (".date("h:i A", strtotime($row['start_time']))." - ".date("h:i A", strtotime($row['end_time'])).")" 
                                                    : 'No schedule'; 
                                            ?>
                                        </td>
                                        <td><?php echo date("F j, Y", strtotime($row['date'])); ?></td>
                                        <td><?php echo $row['time_in'] ? date("h:i A", strtotime($row['time_in'])) : '-'; ?></td>
                                        <td><?php echo $row['time_out'] ? date("h:i A", strtotime($row['time_out'])) : '-'; ?></td>
                                        <td>
                                            <?php 
                                                $status = strtolower($row['status']);
                                                $color = "secondary";
                                                if ($status == "present") $color = "success";
                                                elseif ($status == "late") $color = "warning text-dark";
                                                elseif ($status == "absent") $color = "danger";
                                                elseif ($status == "on leave") $color = "info text-dark";
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No attendance records found.</td></tr>
                            <?php endif; ?>
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