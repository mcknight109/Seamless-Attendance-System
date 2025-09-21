<?php
session_start();
include '../db_connection.php'; // Database connection

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fname   = $_SESSION['fname'] ?? '';
$lname   = $_SESSION['lname'] ?? '';

// Pagination setup
$limit = 5; // rows per page
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search = trim($_GET['search'] ?? '');
$params = [];
$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM leave_requests WHERE user_id = ?";
$params[] = $user_id;

if ($search !== '') {
    $sql .= " AND (leave_type LIKE ? OR reason LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
$types = str_repeat("s", count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$requests = [];
while ($row = $res->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();

// Total rows for pagination
$totalRes = $conn->query("SELECT FOUND_ROWS() as total")->fetch_assoc();
$totalRows = $totalRes['total'];
$totalPages = ceil($totalRows / $limit);

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
    <title>Request Leave</title>
</head>
<body>
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link logo-link" href="user_dashboard.php">
                <span class="icon"><i class="bi bi-cpu"></i></span>
                <span class="description">S.A.S</span>
            </a>
            <span class="category">Profile</span>
            <a class="nav-link" href="user_dashboard.php">
                <span class="icon"><i class="bi bi-bounding-box"></i></span>
                <span class="description">Profile</span>
            </a>
            <hr>
            <span class="category">Attendance</span>
            <a class="nav-link" href="time_in.php">
                <span class="icon"><i class="bi bi-people"></i></span>
                <span class="description">Attendance</span>
            </a>
            <a class="nav-link active" href="request_leave.php">
                <span class="icon"><i class="bi bi-chat-left-text"></i></span>
                <span class="description">Request leave</span>
            </a>
            <hr>
            <a class="nav-link" href="../logout.php">
                <span class="icon"><i class="bi bi-box-arrow-right"></i></span>
                <span class="description">Logout</span>
            </a>
        </nav>
    </div>

    <main class="main-content">
        <div class="content-container">
            <div class="header d-flex justify-content-between">
                <div class="page-title">
                    <h1>LEAVE REQUESTS</h1>
                    <p id="current-date"></p>
                    <p id="current-time"></p>
                </div>
                <div class="welcome-message">
                    <span class="icon"><i class="bi bi-person-circle"></i></span>
                    Welcome, <?php echo htmlspecialchars($fname . ' ' . $lname); ?>
                </div>
            </div>

            <div class="dashboard-container">
                <div class="table-top">
                    <div class="top2">
                        <div class="search-container">
                            <form method="get" class="search-form d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Search by type/reason" value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary ms-2" style="background-color:#0025B7;">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </form>
                        </div>
                        <div class="table-btn">
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#requestLeaveModal">
                                Request Leave
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="manage-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Reason</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) > 0): ?>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($req['leave_type']); ?></td>
                                    <td><?php echo htmlspecialchars($req['reason']); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($req['start_date'])); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($req['end_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $req['status']=='Approved'?'success':($req['status']=='Pending'?'warning':'danger'); ?>">
                                            <?php echo htmlspecialchars($req['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No leave requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container d-flex justify-content-center">
                <nav>
                    <ul class="pagination">
                        <li class="page-item <?php if ($page<=1) echo 'disabled'; ?>">
                            <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo max(1, $page-1); ?>">←</a>
                        </li>
                        <?php for ($i=1; $i<=$totalPages; $i++): ?>
                            <li class="page-item <?php if ($page==$i) echo 'active'; ?>">
                                <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($page>=$totalPages) echo 'disabled'; ?>">
                            <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo min($totalPages, $page+1); ?>">→</a>
                        </li>
                    </ul>
                </nav>
            </div>     
        </div>
    </main>

    <!-- Request Leave Modal -->
    <div class="modal fade" id="requestLeaveModal" tabindex="-1">
      <div class="modal-dialog">
        <form method="post" action="Controller/RequestsController.php" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Request Leave</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Leave Type</label>
              <select name="leave_type" class="form-control" required>
                <option value="">-- Select --</option>
                <option value="Sick">Sick</option>
                <option value="Vacation">Vacation</option>
                <option value="Emergency">Emergency</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Reason</label>
              <textarea name="reason" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="requestSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-check-circle-fill" style="font-size:36px;color:green;"></i>
                    <h5 class="mt-3">Request Leave submitted</h5>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successOkBtn">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>
</body>
</html>
