<?php
session_start();
include '../db_connection.php'; // Database connection

// Fetch admin's ID from the session
$admin_id = $_SESSION['user_id'];

// Check if user is logged in and if the role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Fetch admin's full name from the database
$query = "SELECT full_name FROM users WHERE user_id = '$admin_id'";
$result = $conn->query($query);
$admin = $result->fetch_assoc();
$admin_name = $admin['full_name']; // Admin's full name

// Handle filter query (approved or denied)
$filter_status = isset($_GET['filter']) ? $_GET['filter'] : '';

// Fetch leave requests with user details, filtered by search query and status if provided
$query = "SELECT lr.leave_id, u.full_name, lr.leave_date, lr.start_date, lr.end_date, lr.leave_type, lr.status
          FROM leave_requests lr
          INNER JOIN users u ON lr.user_id = u.user_id";

if (!empty($search_query) && !empty($filter_status)) {
    $query .= " WHERE (u.full_name LIKE ? OR u.email LIKE ?) AND lr.status = ?";
} elseif (!empty($search_query)) {
    $query .= " WHERE u.full_name LIKE ? OR u.email LIKE ?";
} elseif (!empty($filter_status)) {
    $query .= " WHERE lr.status = ?";
}

$query .= " ORDER BY lr.leave_date DESC";
$stmt = $conn->prepare($query);

if (!empty($search_query) && !empty($filter_status)) {
    $like_search = "%" . $search_query . "%";
    $stmt->bind_param("sss", $like_search, $like_search, $filter_status);
} elseif (!empty($search_query)) {
    $like_search = "%" . $search_query . "%";
    $stmt->bind_param("ss", $like_search, $like_search);
} elseif (!empty($filter_status)) {
    $stmt->bind_param("s", $filter_status);
}

$stmt->execute();
$leave_requests_result = $stmt->get_result();

// Handle search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Handle filter query (approved, denied, or all)
$filter_status = isset($_GET['filter']) ? $_GET['filter'] : '';

// Modify the query logic to handle the "All" filter
$query = "SELECT lr.leave_id, u.full_name, lr.leave_date, lr.start_date, lr.end_date, lr.leave_type, lr.status
          FROM leave_requests lr
          INNER JOIN users u ON lr.user_id = u.user_id";

$conditions = [];
$params = [];

if (!empty($search_query)) {
    $conditions[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $like_search = "%" . $search_query . "%";
    $params[] = $like_search;
    $params[] = $like_search;
}

if (!empty($filter_status)) {
    // Only apply filter if it's not empty
    if ($filter_status != '') {
        $conditions[] = "lr.status = ?";
        $params[] = $filter_status;
    }
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY lr.leave_date DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}

$stmt->execute();
$leave_requests_result = $stmt->get_result();

// Pagination logic
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records
$count_query = "SELECT COUNT(*) AS total
                FROM leave_requests lr
                INNER JOIN users u ON lr.user_id = u.user_id";
$count_conditions = [];
$count_params = [];

if (!empty($search_query)) {
    $count_conditions[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $like_search = "%" . $search_query . "%";
    $count_params[] = $like_search;
    $count_params[] = $like_search;
}

if (!empty($filter_status)) {
    $count_conditions[] = "lr.status = ?";
    $count_params[] = $filter_status;
}

if (!empty($count_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", $count_conditions);
}

$stmt = $conn->prepare($count_query);

if (!empty($count_params)) {
    $stmt->bind_param(str_repeat("s", count($count_params)), ...$count_params);
}

$stmt->execute();
$count_result = $stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch records for the current page
$query = "SELECT lr.leave_id, u.full_name, lr.leave_date, lr.start_date, lr.end_date, lr.leave_type, lr.status
          FROM leave_requests lr
          INNER JOIN users u ON lr.user_id = u.user_id";

$conditions = [];
$params = [];

if (!empty($search_query)) {
    $conditions[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $like_search = "%" . $search_query . "%";
    $params[] = $like_search;
    $params[] = $like_search;
}

if (!empty($filter_status)) {
    $conditions[] = "lr.status = ?";
    $params[] = $filter_status;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY lr.leave_date DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params) - 2) . "ii", ...$params);
}

$stmt->execute();
$leave_requests_result = $stmt->get_result();
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

        <div class="header">
            <div class="welcome-message">
                <span class="icon">
                    <i class="bi bi-person-circle"></i>
                </span>
                <!-- Display the admin's full name -->
                Welcome, <?php echo htmlspecialchars($admin_name); ?>
            </div>
        </div>

        <div class="page-title">
            <h1>LEAVE REQUESTS</h1>
        </div>

        <div class="dashboard-content">
            <div class="table-top">
                <div class="top1">
                    <div>
                        <h2>Requests Management Table </h2>
                    </div>
                </div>
                <div class="top2">
                    <div class="search-container">
                    <form method="get" class="search-form d-flex">
                        <input type="text" name="search" class="form-control" placeholder="Search user or email" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" style="background-color: #0025B7;" class="btn btn-primary ms-2">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </form>
                    </div>
                    <div class="table-btn">
                    <form method="get" class="filter-form">
                    <button type="submit" name="filter" value="" class="btn btn-secondary">All</button> <!-- All button added -->
                        <button type="submit" name="filter" value="approved" class="btn btn-success">Approved</button>
                        <button type="submit" name="filter" value="denied" class="btn btn-danger">Denied</button>
                    </form>
                    </div>
                </div>     
            </div>
            <div class="requests-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Leave Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if there are any leave requests
                        if ($leave_requests_result->num_rows > 0) {
                            // Loop through each leave request
                            while ($row = $leave_requests_result->fetch_assoc()) {
                                // Format the dates to "Mon Day, Year"
                                $start_date = date("M j, Y", strtotime($row['start_date']));
                                $end_date = date("M j, Y", strtotime($row['end_date']));
                                
                                // Get the status and leave_id
                                $status = htmlspecialchars($row['status']);
                                $leave_id = htmlspecialchars($row['leave_id']);
                                
                                // Set the status class based on the current status
                                $status_class = '';
                                if ($status == 'approved') {
                                    $status_class = 'approved-status';
                                } elseif ($status == 'denied') {
                                    $status_class = 'denied-status';
                                } elseif ($status == 'pending') {
                                    $status_class = 'pending-status';
                                }

                                // Create buttons for Approve, Deny, and Delete
                                $is_approved_or_denied = in_array($status, ['approved', 'denied']);
                                $approve_button = !$is_approved_or_denied ? "<form action='update_leave_status.php' method='POST' style='display:inline;'>
                                                                                <input type='hidden' name='leave_id' value='$leave_id'>
                                                                                <input type='hidden' name='action' value='approved'>
                                                                                <button type='submit' class='approve-action'>
                                                                                    <i class='bi bi-check-circle'></i> Approve
                                                                                </button>
                                                                            </form>" : '';
                                $deny_button = !$is_approved_or_denied ? "<form action='update_leave_status.php' method='POST' style='display:inline;'>
                                                                                <input type='hidden' name='leave_id' value='$leave_id'>
                                                                                <input type='hidden' name='action' value='denied'>
                                                                                <button type='submit' class='deny-action'>
                                                                                    <i class='bi bi-x-circle'></i> Deny
                                                                                </button>
                                                                            </form>" : '';
                                $delete_button = $is_approved_or_denied ? "<form action='delete_leave_request.php' method='POST' style='display:inline;'>
                                                                                <input type='hidden' name='leave_id' value='$leave_id'>
                                                                                <button type='submit' class='delete-action'>
                                                                                    <i class='bi bi-trash'></i> Delete
                                                                                </button>
                                                                            </form>" : '';

                                echo "<tr>
                                        <td>" . htmlspecialchars($row['full_name']) . "</td>
                                        <td>" . $start_date . "</td>
                                        <td>" . $end_date . "</td>
                                        <td>" . htmlspecialchars($row['leave_type']) . "</td>
                                        <td><span class='$status_class'>" . htmlspecialchars($row['status']) . "</span></td>
                                        <td class='action-buttons'>
                                            $approve_button
                                            $deny_button
                                            $delete_button
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No leave requests found.</td></tr>";
                        }
                        ?>
                    </tbody>

                </table>
            </div>
            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <!-- Previous Button -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo htmlspecialchars($search_query); ?>&filter=<?php echo htmlspecialchars($filter_status); ?>" tabindex="-1">Previous</a>
                    </li>
                    
                    <!-- Page Number Links -->
                    <?php
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = $i == $page ? "active" : "";
                        echo "<li class='page-item $active'>
                                <a class='page-link' href='?page=$i&search=$search_query&filter=$filter_status'>$i</a>
                            </li>";
                    }
                    ?>

                    <!-- Next Button -->
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo htmlspecialchars($search_query); ?>&filter=<?php echo htmlspecialchars($filter_status); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="requests.js" defer></script>
</body>
</html>
