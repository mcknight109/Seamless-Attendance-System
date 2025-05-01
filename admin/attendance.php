<?php
session_start();
include '../db_connection.php'; // Database connection

// Check if user is logged in and if the role is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Set timezone to ensure correct time display
date_default_timezone_set('Asia/Manila');

// Fetch admin's name based on the user_id stored in the session
$admin_name = "Admin"; // Default value in case the query fails

$query_admin = "SELECT full_name FROM users WHERE user_id = ?";
$stmt_admin = $conn->prepare($query_admin);
$stmt_admin->bind_param("i", $admin_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();

if ($result_admin->num_rows > 0) {
    $row_admin = $result_admin->fetch_assoc();
    $admin_name = $row_admin['full_name'];
}

// Get today's date or the selected date
$selected_date = isset($_POST['filter_date']) ? $_POST['filter_date'] : date('Y-m-d');

// Fetch all users and their attendance for the selected date, including the shift type
$query = "
    SELECT 
        u.user_id,
        u.full_name,
        u.gender,
        s.shift_type,  -- Added shift_type here
        COALESCE(a.time_in, 'N/A') AS time_in,
        COALESCE(a.time_out, 'N/A') AS time_out
    FROM users u
    LEFT JOIN attendance a 
    ON u.user_id = a.user_id AND a.date = ?
    LEFT JOIN shifts s  -- Assuming there is a 'shifts' table
    ON u.shift_id = s.shift_id  -- Adjust this join condition based on your actual database structure
    ORDER BY u.full_name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();

// Get search query if available
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Update query to include search logic
if ($search_query) {
    // With search logic
    $query = "
        SELECT 
            u.user_id,
            u.full_name,
            u.gender,
            s.shift_type,
            COALESCE(a.time_in, 'N/A') AS time_in,
            COALESCE(a.time_out, 'N/A') AS time_out
        FROM users u
        LEFT JOIN attendance a 
        ON u.user_id = a.user_id AND a.date = ?
        LEFT JOIN shifts s
        ON u.shift_id = s.shift_id
        WHERE (u.full_name LIKE ? OR u.email LIKE ?)
        ORDER BY 
            CASE 
                WHEN a.time_in IS NOT NULL THEN 0
                ELSE 1
            END,
            u.full_name ASC";
    $search_param = '%' . $search_query . '%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $selected_date, $search_param, $search_param);
} else {
    // Without search logic
    $query = "
        SELECT 
            u.user_id,
            u.full_name,
            u.gender,
            s.shift_type,
            COALESCE(a.time_in, 'N/A') AS time_in,
            COALESCE(a.time_out, 'N/A') AS time_out
        FROM users u
        LEFT JOIN attendance a 
        ON u.user_id = a.user_id AND a.date = ?
        LEFT JOIN shifts s
        ON u.shift_id = s.shift_id
        ORDER BY 
            CASE 
                WHEN a.time_in IS NOT NULL THEN 0
                ELSE 1
            END,
            u.full_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selected_date);
}

$stmt->execute();
$result = $stmt->get_result();

// Pagination setup
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure the page is at least 1
$offset = ($page - 1) * $limit;

// Fetch total number of rows for pagination
$count_query = "SELECT COUNT(*) AS total FROM users u 
                LEFT JOIN attendance a ON u.user_id = a.user_id AND a.date = ?
                WHERE u.full_name LIKE ?";
$search_param = '%' . $search_query . '%';
$stmt_count = $conn->prepare($count_query);
$stmt_count->bind_param("ss", $selected_date, $search_param);
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$total_rows = $count_result['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch rows for the current page
$query = "SELECT u.user_id, u.full_name, u.gender, s.shift_type,
          COALESCE(a.time_in, 'N/A') AS time_in, COALESCE(a.time_out, 'N/A') AS time_out
          FROM users u
          LEFT JOIN attendance a ON u.user_id = a.user_id AND a.date = ?
          LEFT JOIN shifts s ON u.shift_id = s.shift_id
          WHERE u.full_name LIKE ?
          ORDER BY u.full_name ASC
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $selected_date, $search_param, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
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

        <div class="header">
            <div class="welcome-message">
                <span class="icon">
                <i class="bi bi-person-circle"></i>
                </span>
                Welcome, <?php echo htmlspecialchars($admin_name); ?>
            </div>
        </div>

        <div class="page-title">
            <h1>ATTENDANCE</h1>
        </div>
            
        <div class="dashboard-content">
            <div class="table-top">
                <div class="top1">
                    <div class="">
                        <h2><?php echo date('F j, Y', strtotime($selected_date)); ?></h2>
                    </div>
                    <div class="">
                    <button id="download-pdf" class="btn btn-primary" style="background-color: #0025B7;">Print Attendace</button>
                    </div>
                </div>
                <div class="top2">
                <div class="search-container">
                    <form method="get" class="search-form d-flex">
                        <input type="text" name="search" class="form-control" placeholder="Search user or email" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit"  style="background-color: #0025B7;" class="btn btn-primary ms-2">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </form>
                </div>
                    <div class="filter-btn">
                        <form method="post" class="d-flex align-items-center">
                            <label for="filter_date" class="me-2">Select Date:</label>
                            <input type="date" name="filter_date" id="filter_date" class="form-control me-2" value="<?php echo htmlspecialchars($selected_date); ?>">
                            <button type="submit" style="background-color: #0025B7;" class="btn btn-primary">Filter</button>
                        </form>
                    </div>
                </div>     
            </div>

            <div class="attendance-container">
                <table class="table table-striped">
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
                        <?php
                        // Check if there are any results
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Check if the user has Time-in or not to determine status
                                $status = 'Absent';  // Default status is Absent
                                if ($row['time_in'] != 'N/A') {
                                    $status = 'Present'; // User has Time-in, mark as Present
                                }

                                // Format time_in and time_out to 12-hour format with AM/PM
                                $time_in = ($row['time_in'] != 'N/A') ? date('h:i A', strtotime($row['time_in'])) : 'N/A';
                                $time_out = ($row['time_out'] != 'N/A') ? date('h:i A', strtotime($row['time_out'])) : 'N/A';

                                // Output the row with formatted times and status
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['full_name']) . "</td>
                                        <td style='text-transform: capitalize;'>" . htmlspecialchars($row['shift_type']) . "</td>
                                        <td>" . htmlspecialchars($time_in) . "</td>
                                        <td>" . htmlspecialchars($time_out) . "</td>
                                        <td>" . htmlspecialchars($status) . "</td>
                                        <td><span class='" . ($status == 'Present' ? 'present-status' : 'absent-status') . "'>" . htmlspecialchars($status) . "</span></td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No attendance records found for the selected date.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
        </div>

        </main>
        <script>
            document.getElementById('download-pdf').addEventListener('click', function () {
                // Show confirmation alert
                const userConfirmed = confirm("Do you want to download the Attendance PDF for today?");
                
                if (userConfirmed) {
                    // Get the table data
                    const table = document.querySelector('table');
                    const rows = table.querySelectorAll('tr');
                    
                    // Create a new jsPDF instance
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    
                    // Set title and date
                    doc.setFontSize(18);
                    doc.text("Attendance for " + '<?php echo date("F j, Y", strtotime($selected_date)); ?>', 10, 10);
                    
                    // Set the table header
                    doc.setFontSize(12);
                    let x = 10;
                    let y = 20;
                    doc.text("Full Name", x, y);
                    doc.text("Shift Type", x + 60, y);
                    doc.text("Time In", x + 120, y);
                    doc.text("Time Out", x + 160, y);
                    doc.text("Status", x + 200, y);
                    
                    // Set the row data
                    y += 10;
                    rows.forEach((row, index) => {
                        if (index === 0) return; // Skip the header row

                        const cols = row.querySelectorAll('td');
                        if (cols.length > 0) {
                            doc.text(cols[0].innerText, x, y); // Full Name
                            doc.text(cols[1].innerText, x + 60, y); // Shift Type
                            doc.text(cols[2].innerText, x + 120, y); // Time In
                            doc.text(cols[3].innerText, x + 160, y); // Time Out
                            doc.text(cols[4].innerText, x + 200, y); // Status
                            y += 10;
                        }
                    });

                    // Save the PDF
                    doc.save('attendance-<?php echo date("Y-m-d", strtotime($selected_date)); ?>.pdf');
                } else {
                    // If the user cancels the download
                    alert("PDF download canceled.");
                }
            });
        </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</body>
</html>
