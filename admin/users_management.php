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

// Fetch admin's ID and email from the session
$admin_id = $_SESSION['user_id'];
$admin_email = $_SESSION['email'];

// Fetch admin's full name from the database if not stored in session
if (!isset($_SESSION['full_name'])) {
    $query = "SELECT full_name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($full_name);
    if ($stmt->fetch()) {
        $_SESSION['full_name'] = $full_name; // Store in session for future use
    } else {
        $_SESSION['full_name'] = 'Admin'; // Fallback
    }
    $stmt->close();
}

// Use the stored full name for display
$admin_name = htmlspecialchars($_SESSION['full_name']);

// Handle search input
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Fetch user data from the database with optional search query
$query = "SELECT user_id, full_name, gender, contact_no, role 
          FROM users 
          WHERE full_name LIKE ? OR email LIKE ? 
          ORDER BY user_id ASC";

$stmt = $conn->prepare($query);
$search_param = "%" . $search_query . "%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination
$count_query = "SELECT COUNT(*) AS total FROM users WHERE full_name LIKE ? OR email LIKE ?";
$stmt = $conn->prepare($count_query);
$search_param = "%" . $search_query . "%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$stmt->bind_result($total_records);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total_records / $limit);

// Fetch user data with pagination
$query = "SELECT user_id, full_name, gender, contact_no, role 
          FROM users 
          WHERE full_name LIKE ? OR email LIKE ? 
          ORDER BY user_id ASC
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
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
    <link rel="stylesheet" href="../admin/scss/modal.scss">
    <link rel="stylesheet" href="../admin/edit_account.css">
    <title>Users Management</title>
    <style>
        .action-btns {
            display: flex;
            gap: 10px;
        }

        .action-btns a {
            padding: 5px 10px;
            border: 1px solid #ccc;
            background-color: #f4f4f4;
            text-decoration: none;
            color: black;
        }

        .action-btns a:hover {
            background-color: #ddd;
        }
    </style>
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
                Welcome, <?php echo $admin_name; ?>
            </div>
        </div>

        <div class="page-title">
            <h1>EMPLOYEE MANAGEMENT</h1>
        </div>

        <div class="dashboard-content">
            <div class="table-top">
                <div class="top1">
                    <div>
                        <h2>User Management Table</h2>
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
                        <button class="add-btn" id="openModalButton">
                        <i class="bi bi-person-plus"></i>
                        Add account
                    </button>
                </div>
            </div>     
            </div>
        

            <div class="manage-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Contact No</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if there are any results
                        if ($result->num_rows > 0) {
                            // Loop through each user record
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row['full_name']) . "</td>
                                    <td>" . htmlspecialchars($row['gender']) . "</td>
                                    <td>" . htmlspecialchars($row['contact_no']) . "</td>
                                    <td>" . htmlspecialchars($row['role']) . "</td>
                                    <td class='action-btns'>
                                        <button class='edit-action'>
                                            <i class='bi bi-pencil-square'></i> Edit
                                        </button>
                                        <button class='delete-action' data-user-id='" . $row['user_id'] . "'>
                                            <i class='bi bi-trash'></i> Delete
                                        </button>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search_query); ?>" aria-label="Previous">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search_query); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search_query); ?>" aria-label="Next">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

            <!-- Add Account Modal -->
            <div id="addAccountModal" class="add-account-modal">
                <div class="modal-content">
                    <span class="close-btn" id="closeModalBtn">&times;</span>
                        <div class="form-text">
                            <h2>Add Account</h2>
                            <hr>
                        </div>
                    <div class="modal-body">
                        <div class="section1">
                            <div class="profile-img">
                                <img src="../images/profilee.png" alt="profile">
                            </div>
                        <form id="addAccountForm" method="post" action="../admin/add_account_handler.php">
                            <div class="edit-input">
                                <label for="shift_type">Shift Type:</label>
                                <select id="edit_shift_type" name="shift_type" required onchange="fetchShiftTimes(this.value, 'edit_shift_time')">
                                    <option value="">Select Shift Type</option>
                                    <option value="morning">Morning</option>
                                    <option value="afternoon">Afternoon</option>
                                    <option value="night">Night</option>
                                </select>
                            </div>
                            <div class="edit-input">
                                <label for="edit_shift_time">Available Shift Time:</label>
                                <select id="edit_shift_time" name="shift_time" required>
                                    <option value="">Select Shift Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="section2">
                                <div class="edit-input">
                                    <label for="full_name">Full Name:</label>
                                    <input type="text" id="full_name" name="full_name" required pattern="[A-Za-z\s]+" title="Full name should only contain letters and spaces.">
                                </div>
                                <div class="edit-input">
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email" required pattern="^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Please enter a valid email address.">
                                </div>
                                <div class="edit-input">
                                    <label for="password">Password:</label>
                                    <input type="password" id="password" name="password" required maxlength="8" title="Password must be exactly 8 characters.">
                                </div>
                                <div class="edit-input">
                                    <label for="gender">Gender:</label>
                                    <select id="gender" name="gender" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="edit-input">
                                    <label for="contact_no">Contact No:</label>
                                    <input type="text" id="contact_no" name="contact_no" required pattern="^\d{11}$" title="Contact No should be exactly 11 digits.">
                                </div>
                                <div class="edit-input">
                                    <label for="role">Role:</label>
                                    <select id="role" name="role" required>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="submit-group">
                                    <div class="submit-btn">
                                        <button type="submit">Add Account</button>
                                    </div>
                                    <div class="cancel-btn">
                                        <button type="button" id="cancelEditBtn">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div id="editAccountModal" class="edit-account-modal">
                <div class="modal-content">
                    <span class="close-btn" id="closeModalBtn">&times;</span>
                    <div class="form-text">
                        <h2>Edit Account</h2>
                        <hr>
                        
                    </div>
                    
                    <div class="modal-body">
                        <form id="editAccountForm" method="POST" action="update_user.php">
                            <input type="hidden" id="edit_user_id" name="user_id">
                            <div class="edit-input">
                                <label for="edit_full_name">Full Name:</label>
                                <input type="text" id="edit_full_name" name="full_name" required>
                            </div>
                            <div class="edit-input">
                                <label for="edit_email">Email:</label>
                                <input type="email" id="edit_email" name="email" required>
                            </div>
                            <div class="edit-input">
                                <label for="edit_password">Password:</label>
                                <input type="password" id="edit_password" name="password" placeholder="Leave empty if not changing">
                            </div>
                            <div class="edit-input">
                                <label for="edit_gender">Gender:</label>
                                <select id="edit_gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="edit-input">
                                <label for="edit_contact_no">Contact No:</label>
                                <input type="text" id="edit_contact_no" name="contact_no" required>
                            </div>
                            <div class="edit-input">
                                <label for="edit_role">Role:</label>
                                <select id="edit_role" name="role" required>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="submit-group">
                                <div class="submit-btn">
                                    <button type="submit">Save Changes</button>
                                </div>
                                <div class="cancel-btn">
                                    <button type="button" id="cancelEditBtn">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div id="successModal" class="success-modal">
                <div class="modal-content">
                    <div class="success-container">
                        <h2>Successfully Edited Account</h2>
                        <button id="closeSuccessModal" class="close-btn">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
            
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/add_account.js"></script>
    <script src="../login/input.js"></script>
    <script src="../admin/edit_account.js"></script>
    <script src="../admin/management.js"></script>

</body>
</html>
