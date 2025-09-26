<?php
session_start();
include '../db_connection.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check if user is logged in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Handle search & filter
$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? '');
$leave_requests = [];

try {
    $sql = "SELECT lr.leave_id, lr.user_id, lr.leave_type, lr.reason, lr.start_date, lr.end_date, lr.status, lr.created_at, u.fname, u.lname
            FROM leave_requests lr
            INNER JOIN users u ON lr.user_id = u.user_id
            WHERE 1=1";

    $params = [];
    $types  = '';

    if ($search !== '') {
        $sql .= " AND (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ?)";
        $like = "%" . $search . "%";
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types   .= "sss";
    }

    if ($filter !== '') {
        $sql .= " AND lr.status = ?";
        $params[] = $filter;
        $types   .= "s";
    }

    $sql .= " ORDER BY lr.created_at DESC";
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $leave_requests[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // log or handle error
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

    <!-- Sidebar -->
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
            <a class="nav-link active" href="leave_requests.php">
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-container">
            <div class="header">
                <div class="page-title">
                    <h1>USERS LEAVE REQUESTS</h1>
                    <p id="current-date"></p>
                    <p id="current-time"></p>
                </div>
                <div class="welcome-message">
                    <i class="bi bi-person-circle"></i> Welcome, Admin
                </div>
            </div>

            <div class="dashboard-content">
                <div class="table-top">
                    <div class="top2">
                        <div class="search-container">
                            <form method="get" class="search-form d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Search user or email" value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary ms-2"><i class="bi bi-search">
                                    Search
                                </i></button>
                            </form>
                        </div>
                        <div class="table-btn">
                            <form method="get" class="filter-form ms-2">
                                <button type="submit" name="filter" value="" class="btn btn-secondary">All</button>
                                <button type="submit" name="filter" value="approved" class="btn btn-success">Approved</button>
                                <button type="submit" name="filter" value="rejected" class="btn btn-danger">Rejected</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="manage-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($leave_requests) > 0): ?>
                            <?php foreach ($leave_requests as $leave): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($leave['fname'] . ' ' . $leave['lname']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php echo $leave['status']=='approved'?'bg-success':($leave['status']=='rejected'?'bg-danger':'bg-warning'); ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                    <td class="btn-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn1-action" 
                                            data-bs-toggle="modal" data-bs-target="#viewModal"
                                            data-leave='<?php echo json_encode($leave); ?>'>
                                            <i class="bi bi-file-earmark-text-fill"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn2-action" 
                                            data-leave-id="<?php echo $leave['leave_id']; ?>" 
                                            data-bs-toggle="modal" data-bs-target="#rejectConfirmModal">
                                            <i class="bi bi-file-earmark-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">No leave requests found.</td></tr>
                        <?php endif; ?>
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

    <!-- View Leave Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="bi bi-journal-text"></i> Leave Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- dynamic content filled by JS -->
                </div>
                <div class="modal-footer">
                    <form method="post" action="Controller/RequestsController.php" class="me-auto">
                        <input type="hidden" name="leave_id" id="approveLeaveId">
                        <button type="submit" name="action" value="approve" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Approve
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Reject
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="approveSuccessModal" tabindex="-1" aria-labelledby="approveSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <?php if (isset($_GET['success']) && $_GET['success'] === 'reject'): ?>
                        <i class="bi bi-x-circle-fill" style="font-size:36px;color:red;"></i>
                        <h5 class="mt-3">Leave request rejected</h5>
                    <?php else: ?>
                        <i class="bi bi-check-circle-fill" style="font-size:36px;color:green;"></i>
                        <h5 class="mt-3">Leave request approved</h5>
                    <?php endif; ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successOkBtn">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirm Modal -->
    <div class="modal fade" id="rejectConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:36px;"></i>
                    <h5>Reject this leave request?</h5>
                    <form method="post" action="Controller/RequestsController.php" class="mt-3">
                        <input type="hidden" name="leave_id" id="rejectLeaveId">
                        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var successModal = new bootstrap.Modal(document.getElementById('approveSuccessModal'));
                successModal.show();
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>
    <script>
        // Populate view modal
        const viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const leave = JSON.parse(button.getAttribute('data-leave'));
            document.getElementById('approveLeaveId').value = leave.leave_id;

            document.getElementById('viewModalBody').innerHTML = `
                <p><i class="bi bi-person"></i> <strong>Name:</strong> ${leave.fname} ${leave.lname}</p>
                <p><i class="bi bi-card-list"></i> <strong>Leave Type:</strong> ${leave.leave_type}</p>
                <p><i class="bi bi-calendar-event"></i> <strong>Start Date:</strong> ${leave.start_date}</p>
                <p><i class="bi bi-calendar-check"></i> <strong>End Date:</strong> ${leave.end_date}</p>
                <p><i class="bi bi-info-circle"></i> <strong>Status:</strong> ${leave.status}</p>
                <p><i class="bi bi-chat-left-text"></i> <strong>Reason:</strong> ${leave.reason}</p>
            `;
        });



        // Pass leave_id to reject modal
        const rejectModal = document.getElementById('rejectConfirmModal');
        rejectModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('rejectLeaveId').value = button.getAttribute('data-leave-id');
        });
    </script>
</body>
</html>
