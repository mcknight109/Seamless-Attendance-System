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

// Initialize variables for status messages
$create_error = '';
$create_success = false;

// Handle create user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    // Collect and validate inputs
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $gender = $_POST['gender'] ?? 'Other';
    $schedule_id = !empty($_POST['schedule_id']) ? intval($_POST['schedule_id']) : null;

    if (!$fname || !$lname || !$email || !$password) {
        $create_error = 'Please fill in all required fields.';
    } else {
        // Handle profile upload (optional)
        $profile_filename = null;
        if (isset($_FILES['profile']) && $_FILES['profile']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if ($_FILES['profile']['error'] === UPLOAD_ERR_OK && in_array($_FILES['profile']['type'], $allowed_types)) {
                $uploads_dir = __DIR__ . '/../uploads/profile/';
                if (!file_exists($uploads_dir)) {
                    mkdir($uploads_dir, 0755, true);
                }
                $ext = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
                $profile_filename = 'profile_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $target = $uploads_dir . $profile_filename;
                if (!move_uploaded_file($_FILES['profile']['tmp_name'], $target)) {
                    $create_error = 'Failed to move uploaded profile image.';
                }
            } else {
                $create_error = 'Invalid profile image (only JPG, PNG, GIF, WEBP are allowed).';
            }
        }

        if (empty($create_error)) {
            // Hash the password for safety
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert to DB using prepared statement
            $sql = "INSERT INTO users (fname, lname, email, password, role, gender, schedule_id, profile, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $profile_param = $profile_filename ?: null;
                $stmt->bind_param(
                    'sssssiss',
                    $fname,
                    $lname,
                    $email,
                    $password_hash,
                    $role,
                    $gender,
                    $schedule_id,
                    $profile_param
                );

                // Note: bind_param requires types matching values; schedule_id may be null -> adjust
                // We'll use a simpler approach with explicit types and handling:
                $stmt->close();

                // Re-prepare with conditional query to accept null schedule_id
                if ($schedule_id === null) {
                    $sql2 = "INSERT INTO users (fname, lname, email, password, role, gender, profile, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                    $stmt2 = $conn->prepare($sql2);
                    if ($stmt2) {
                        $stmt2->bind_param('sssssss', $fname, $lname, $email, $password_hash, $role, $gender, $profile_param);
                        $exec = $stmt2->execute();
                        if ($exec) {
                            $create_success = true;
                        } else {
                            $create_error = 'Database error: ' . $stmt2->error;
                        }
                        $stmt2->close();
                    } else {
                        $create_error = 'Database prepare failed: ' . $conn->error;
                    }
                } else {
                    $sql3 = "INSERT INTO users (fname, lname, email, password, role, gender, schedule_id, profile, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $stmt3 = $conn->prepare($sql3);
                    if ($stmt3) {
                        $stmt3->bind_param('sssssiss', $fname, $lname, $email, $password_hash, $role, $gender, $schedule_id, $profile_param);
                        $exec = $stmt3->execute();
                        if ($exec) {
                            $create_success = true;
                        } else {
                            $create_error = 'Database error: ' . $stmt3->error;
                        }
                        $stmt3->close();
                    } else {
                        $create_error = 'Database prepare failed: ' . $conn->error;
                    }
                }
            } else {
                $create_error = 'Database prepare failed: ' . $conn->error;
            }
        }
    }
}

// Handle update user form submission
$edit_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $user_id = intval($_POST['user_id']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    $sql = "UPDATE users SET role = ?" . (!empty($password) ? ", password = ?" : "") . " WHERE user_id = ?";
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $role, $password_hash, $user_id);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $role, $user_id);
    }

    if ($stmt->execute()) {
        $edit_success = true;
    }
    $stmt->close();
}

// Handle delete user form submission
$delete_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $delete_success = true;
    }
    $stmt->close();
}

// Fetch users for display (basic fetch, adjust as needed)
$users = [];
$search = trim($_GET['search'] ?? '');
try {
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $conn->prepare("SELECT user_id, fname, lname, role, gender, status, profile, created_at FROM users WHERE fname LIKE ? OR lname LIKE ? OR email LIKE ? ORDER BY created_at DESC");
        $stmt->bind_param('sss', $like, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
    } else {
        $res = $conn->query("SELECT user_id, fname, lname, role, gender, status, profile, created_at FROM users ORDER BY created_at DESC");
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
    }
} catch (Exception $e) {
    // handle or log if desired
}

// Handle fetch single user (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch_user') {
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("SELECT user_id, fname, lname, email, role, gender, profile FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode($result);
    exit;
}

// Handle update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $user_id = intval($_POST['user_id']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    $updatesql = "UPDATE users SET role = ?" . (!empty($password) ? ", password = ?" : "") . " WHERE user_id = ?";
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare($updatesql);
        $stmt->bind_param("ssi", $role, $password_hash, $user_id);
    } else {
        $stmt = $conn->prepare($updatesql);
        $stmt->bind_param("si", $role, $user_id);
    }

    $success = $stmt->execute();
    echo json_encode(["success" => $success]);
    exit;
}

// Handle delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $success = $stmt->execute();
    echo json_encode(["success" => $success]);
    exit;
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
    <link rel="stylesheet" href="../admin/scss/modal.scss">
    <link rel="stylesheet" href="../admin/scss/dashboard.scss">
    <title>Users Management</title>
    <style>
        .action-btns {
            display: flex;
            gap: 10px;
            justify-content: center;
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

        /* Add small adjustments for modal preview area */
        .profile-preview {
            width: 100%;
            height: 280px;
            border: 1px dashed #ccc;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
            border-radius:8px;
            background:#fafafa;
        }
        .profile-preview img {
            max-width:100%;
            max-height:100%;
            object-fit: fill;
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
            <a class="nav-link active" href="users_management.php">
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
                    <h1>USERS MANAGEMENT</h1>
                    <p id="current-date">Wed, January 20, 2026</p>
                    <p id="current-time">Time: 01:20 PM</p>
                </div>
                <div class="welcome-message">
                    <span class="icon">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    Welcome, <?php echo htmlspecialchars($_SESSION['fname'] ?? '') . ' ' . htmlspecialchars($_SESSION['lname'] ?? '') ; ?> 
                </div>
            </div>

            <div class="dashboard-content">
                <div class="table-top">
                    <div class="top2">
                        <div class="search-container">
                        <form method="get" class="search-form d-flex">
                            <input type="text" name="search" class="form-control" placeholder="Search user or email" value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" style="background-color: #0025B7;" class="btn btn-primary ms-2">
                                <i class="bi bi-search"></i> 
                                Search
                            </button>
                        </form>
                        </div>
                        <div class="table-btn">
                            <button class="btn btn-primary" id="btnOpenAddUser">
                                <i class="bi bi-person-plus"></i>
                                Add account
                            </button>
                        </div>
                    </div>     
                </div>

                <div class="manage-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['fname'] . ' ' . $u['lname']); ?></td>
                                        <td><?php echo htmlspecialchars($u['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                                        <td><?php echo htmlspecialchars($u['status']); ?></td>
                                        <td class='btn-actions'>
                                            <button class='btn btn-sm btn-outline-secondary btn1-action edit-action' data-id="<?php echo $u['user_id']; ?>">
                                                <i class='bi bi-pencil-square'></i>
                                            </button>
                                            <button class='btn btn-sm btn-outline-danger btn2-action delete-action' data-id="<?php echo $u['user_id']; ?>">
                                                <i class='bi bi-trash'></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan='6'>No users found.</td>
                                </tr>
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="createUserForm" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="create_user">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <!-- Left column: profile -->
                                <div class="col-md-5">
                                    <div class="mb-3">
                                        <label class="form-label">Profile Preview</label>
                                        <div class="profile-preview" id="profilePreview">
                                            <span class="text-muted">No image selected</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="profile" class="form-label">Upload Profile Picture</label>
                                        <input class="form-control" type="file" id="profile" name="profile" accept="image/*">
                                    </div>
                                    <small class="text-muted">Supported: JPG, PNG, GIF, WEBP</small>
                                </div>

                                <!-- Right column: stacked inputs -->
                                <div class="col-md-7">
                                    <div class="mb-3">
                                        <label for="fname" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="fname" name="fname" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="lname" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lname" name="lname" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="text" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label class="form-label">Role</label> -->
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="role" id="roleUser" value="user" checked>
                                                <label class="form-check-label" for="roleUser">User</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="role" id="roleAdmin" value="admin">
                                                <label class="form-check-label" for="roleAdmin">Admin</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label class="form-label">Gender</label> -->
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" checked>
                                                <label class="form-check-label" for="genderMale">Male</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female">
                                                <label class="form-check-label" for="genderFemale">Female</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other">
                                                <label class="form-check-label" for="genderOther">Other</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- row -->
                        </div> <!-- container-fluid -->
                    </div>
                    <!-- Footer with buttons aligned bottom-right -->
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="editUserForm" method="post">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">
                            <!-- Profile -->
                            <div class="col-md-5">
                                <label class="form-label">Profile Picture</label>
                                <div class="profile-preview" id="editProfilePreview">
                                    <span class="text-muted">No image</span>
                                </div>
                            </div>
                            
                            <!-- Info -->
                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="edit_fname" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="edit_lname" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password (leave blank to keep)</label>
                                    <input type="text" class="form-control" name="password" id="edit_password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="role" id="edit_roleUser" value="user">
                                            <label class="form-check-label" for="edit_roleUser">User</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="role" id="edit_roleAdmin" value="admin">
                                            <label class="form-check-label" for="edit_roleAdmin">Admin</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div> <!-- row -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="createSuccessModal" tabindex="-1" aria-labelledby="createSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-check-circle-fill" style="font-size:36px;color:green;"></i>
                    <h5 class="mt-3">User created successfully</h5>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successOkBtn">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editSuccessModal" tabindex="-1" aria-labelledby="editSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-check-circle-fill" style="font-size:36px;color:green;"></i>
                    <h5 class="mt-3">Account edited successfully</h5>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successOkBtn">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:36px;color:red;"></i>
                    <h5 class="mt-3">Are you sure?</h5>
                    <p class="small text-muted">This action cannot be undone.</p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Error Modal (optional) -->
    <div class="modal fade" id="createErrorModal" tabindex="-1" aria-labelledby="createErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-x-circle-fill" style="font-size:36px;color:#d9534f;"></i>
                    <h6 class="mt-3 text-danger">There was an issue</h6>
                    <p id="createErrorMessage" class="small text-muted px-3"></p>
                    <div class="mt-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editModal = new bootstrap.Modal(document.getElementById("editUserModal"));
            const deleteModal = new bootstrap.Modal(document.getElementById("deleteConfirmModal"));

            let deleteUserId = null;

            // Handle Edit Click
            document.querySelectorAll(".edit-action").forEach(btn => {
                btn.addEventListener("click", function () {
                const userId = this.dataset.id;

                fetch("users_management.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=fetch_user&user_id=" + userId
                })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("edit_user_id").value = data.user_id;
                    document.getElementById("edit_fname").value = data.fname;
                    document.getElementById("edit_lname").value = data.lname;
                    document.getElementById("edit_email").value = data.email;
                    document.getElementById("edit_password").value = "";

                    // Profile preview
                    const preview = document.getElementById("editProfilePreview");
                    preview.innerHTML = data.profile 
                    ? `<img src="../uploads/profile/${data.profile}" alt="Profile">`
                    : `<span class="text-muted">No image</span>`;

                    // Role
                    if (data.role === "admin") {
                    document.getElementById("edit_roleAdmin").checked = true;
                    } else {
                    document.getElementById("edit_roleUser").checked = true;
                    }

                    editModal.show();
                });
                });
            });

            // Handle Update Submit
            document.getElementById("editUserForm").addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch("users_management.php", {
                method: "POST",
                body: new URLSearchParams([...formData])
                })
                .then(res => res.json())
                .then(resp => {
                if (resp.success) {
                    editModal.hide();
                    new bootstrap.Modal(document.getElementById("editSuccessModal")).show();
                    setTimeout(() => location.reload(), 1500);
                }
                });
            });

            // Handle Delete Click
            document.querySelectorAll(".delete-action").forEach(btn => {
                btn.addEventListener("click", function () {
                deleteUserId = this.dataset.id;
                deleteModal.show();
                });
            });

            // Confirm Delete
            document.getElementById("confirmDeleteBtn").addEventListener("click", function () {
                fetch("users_management.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=delete_user&user_id=" + deleteUserId
                })
                .then(res => res.json())
                .then(resp => {
                if (resp.success) {
                    deleteModal.hide();
                    location.reload();
                }
                });
            });
        });
    </script>

    <script>
        // Open add user modal on button click
        const btnOpen = document.getElementById('btnOpenAddUser');
        const addUserModalEl = document.getElementById('addUserModal');
        const addUserModal = new bootstrap.Modal(addUserModalEl);

        btnOpen.addEventListener('click', () => {
            // reset form
            document.getElementById('createUserForm').reset();
            document.getElementById('profilePreview').innerHTML = '<span class="text-muted">No image selected</span>';
            addUserModal.show();
        });

        // Profile preview
        const profileInput = document.getElementById('profile');
        profileInput.addEventListener('change', function (e) {
            const preview = document.getElementById('profilePreview');
            preview.innerHTML = '';
            const file = this.files[0];
            if (!file) {
                preview.innerHTML = '<span class="text-muted">No image selected</span>';
                return;
            }
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.onload = function() {
                URL.revokeObjectURL(this.src);
            }
            preview.appendChild(img);
        });

        // If a server-side create happened, show success or error modal
        <?php if ($create_success): ?>
            const successModal = new bootstrap.Modal(document.getElementById('createSuccessModal'));
            // Show after the current event loop to ensure modal markup exists
            setTimeout(() => successModal.show(), 200);
            // Optionally close addUserModal if open
            if (addUserModal) addUserModal.hide();
        <?php elseif (!empty($create_error)): ?>
            const errorModal = new bootstrap.Modal(document.getElementById('createErrorModal'));
            document.getElementById('createErrorMessage').textContent = <?php echo json_encode($create_error); ?>;
            setTimeout(() => errorModal.show(), 200);
        <?php endif; ?>
    </script>
</body>
</html>
