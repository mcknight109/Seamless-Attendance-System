<?php
session_start();
include '../db_connection.php';
date_default_timezone_set('Asia/Manila');

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch_current_user') {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT user_id, fname, lname, email, gender, profile FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode($result);
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
    <link rel="stylesheet" href="../users/scss/user.scss">
    <link rel="stylesheet" href="../users/scss/table.scss"> 
    <link rel="stylesheet" href="../users/scss/modal.scss">
    <link rel="stylesheet" href="../users/scss/btn.scss"> 
    <title>Admin Dashboard</title>
    <style>
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
            <span class="category">Profile</span>
            <a class="nav-link" href="user_dashboard.php">
                <span class="icon">
                    <i class="bi bi-bounding-box"></i>
                </span>
                <span class="description">Profile</span>
            </a>
            <hr>
            <span class="category">Attendance</span>
            <a class="nav-link" href="time_in.php">
                <span class="icon">
                    <i class="bi bi-people"></i>
                </span>
                <span class="description">Attendance</span>
            </a>
            <a class="nav-link" href="request_leave.php">
                <span class="icon">
                    <i class="bi bi-chat-left-text"></i>
                </span>
                <span class="description">Request Leave</span>
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
                    <h1>PROFILE DASHBOARD</h1>
                    <p id="current-date">Wed, January 20, 2026</p>
                    <p id="current-time">Time: 01:20 PM</p>
                </div>
                <div class="welcome-message">
                    <span class="icon">
                        <i class="bi bi-person-circle"></i>
                    </span>
                    Welcome, Neil Alferez
                </div>
            </div>

            <div class="dashboard-content">
                <div class="profile-container">
                    <div class="profile-image">
                        <div class="image">
                            <img id="dashboardProfileImage" src="../images/profilee.png" alt="Profile Image">
                        </div>
                        <!-- Edit Account Btn -->
                        <div class="edit-btn">
                            <button class="edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                        </div>
                    </div>
                    <div class="vertical-hr"></div>
                        <div class="info-table">
                            <table>
                                <h2>INFORMATION</h2>
                                <tr>
                                    <th>Name</th>
                                    <td>: <?php echo htmlspecialchars($user['fname'] . " " . $user['lname']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>: <?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Gender</th>
                                    <td>: <?php echo htmlspecialchars($user['gender']); ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>: <?php echo htmlspecialchars($user['status']); ?></td>
                                </tr>
                                <tr>
                                    <th>Account Created</th>
                                    <td>: <?php echo date("M d, Y", strtotime($user['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="editProfileForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_profile">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Your Account</h5>
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
                                        <input type="text" class="form-control" id="fname" name="fname" 
                                            value="<?php echo htmlspecialchars($user['fname']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="lname" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lname" name="lname" 
                                            value="<?php echo htmlspecialchars($user['lname']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <!-- <label class="form-label">Gender</label> -->
                                        <div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" value="Male"
                                                    <?php echo ($user['gender'] === 'Male') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="genderMale">Male</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" value="Female"
                                                    <?php echo ($user['gender'] === 'Female') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="genderFemale">Female</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="gender" value="Other"
                                                    <?php echo ($user['gender'] === 'Other') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="genderOther">Other</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="text" class="form-control" id="password" name="password" 
                                            value="" placeholder="Enter new password">
                                    </div>                      
                                </div>
                            </div> <!-- row -->
                        </div> <!-- container-fluid -->
                    </div>
                    <!-- Footer with buttons aligned bottom-right -->
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="requestSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-check-circle-fill" style="font-size:36px;color:green;"></i>
                    <h5 class="mt-3">Account updated successfully</h5>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successOkBtn">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/date_time.js"></script>

    <script>
        document.getElementById("editProfileForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("Controller/EditController.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    // Close edit modal
                    let modal = bootstrap.Modal.getInstance(document.getElementById("editProfileModal"));
                    modal.hide();

                    // Show success modal
                    new bootstrap.Modal(document.getElementById("requestSuccessModal")).show();

                } else {
                    alert("Update failed: " + resp.message);
                }
            })
            .catch(err => console.error("Error:", err));
        });
    </script>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    // Fetch logged-in user profile
    fetch("user_dashboard.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=fetch_current_user"
    })
    .then(res => res.json())
    .then(data => {
        // Dashboard profile
        const dashboardImg = document.getElementById("dashboardProfileImage");
        if (data.profile) {
            dashboardImg.src = "../uploads/profile/" + data.profile;
        } else {
            dashboardImg.src = "../images/profilee.png"; // fallback
        }

        // Modal profile preview
        const preview = document.getElementById("profilePreview");
        preview.innerHTML = data.profile
            ? `<img src="../uploads/profile/${data.profile}" alt="Profile">`
            : `<span class="text-muted">No image</span>`;
    });

    // Live preview when uploading new file in modal
    document.getElementById("profile").addEventListener("change", function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById("profilePreview");

        preview.innerHTML = ""; 
        if (file) {
            const img = document.createElement("img");
            img.src = URL.createObjectURL(file);
            img.onload = () => URL.revokeObjectURL(img.src);
            preview.appendChild(img);
        } else {
            preview.innerHTML = `<span class="text-muted">No image</span>`;
        }
    });
});
</script>


</body>
</html>
