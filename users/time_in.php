<?php
session_start();
include '../db_connection.php'; // Database connection

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../users/scss/user.scss">
    <link rel="stylesheet" href="../users/scss/table.scss">    
    <link rel="stylesheet" href="../users/scss/btn.scss"> 
    <style>
body {
    background: #f4f6f9;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}

/* Attendance Title */
.attendance-title {
    font-size: 1.9rem;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    margin-bottom: 2rem;
    border-bottom: 3px solid #e2e8f0;
    padding-bottom: 0.8rem;
    letter-spacing: -0.5px;
}

/* Info Section */
.attendance-info {
    background: #ffffff;
    border-radius: 16px;
    padding: 1.8rem 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
}
.attendance-info p {
    font-size: 1rem;
    margin: 1rem 0;
    display: flex;
    justify-content: space-between; /* Aligns label left, value right */
    align-items: center;
    padding: 0.6rem 1rem;
    background: #f9fafb;
    border-radius: 10px;
    color: #444;
}
.attendance-info p i {
    font-size: 1.2rem;
    color: #2563eb;
}
.attendance-info span {
    font-weight: 600;
    color: #111827;
}

/* Status Tag */
.status-tag {
    padding: 0.45rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    background: #cbd5e0;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Clock */
.clock-time {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1d4ed8;
}

/* Action Buttons */
.attendance-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.5rem;
}
.action-btn {
    flex: 1;
    border: none;
    border-radius: 14px;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    transition: all 0.25s ease-in-out;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.action-btn i {
    font-size: 1.25rem;
}

/* Individual Button Colors */
.action-btn.clockin {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}
.action-btn.clockin:hover {
    background: linear-gradient(135deg, #16a34a, #15803d);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
}
.action-btn.absent {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
}
.action-btn.absent:hover {
    background: linear-gradient(135deg, #dc2626, #991b1b);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
}
.action-btn.earlyout {
    background: linear-gradient(135deg, #facc15, #ca8a04);
    color: #222;
}
.action-btn.earlyout:hover {
    background: linear-gradient(135deg, #eab308, #a16207);
    box-shadow: 0 4px 12px rgba(234, 179, 8, 0.35);
}
    </style>
</head>
<body>
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link logo-link" href="admin_dashboard.php">
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
            <a class="nav-link active" href="time_in.php">
                <span class="icon"><i class="bi bi-people"></i></span>
                <span class="description">Attendance</span>
            </a>
            <a class="nav-link" href="request_leave.php">
                <span class="icon"><i class="bi bi-chat-left-text"></i></span>
                <span class="description">Request Leave</span>
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
                <div class="manage-container">
                    <h2 class="attendance-title">Make an Attendance</h2>
                    <!-- Schedule Info -->
                    <div class="attendance-info">
                        <p><span>📅 Schedule:</span> 
                            <?php 
                                $stmt = $conn->prepare("
                                    SELECT s.shift_name, s.start_time, s.end_time 
                                    FROM users u 
                                    JOIN schedules s ON u.schedule_id = s.schedule_id 
                                    WHERE u.user_id = ?
                                ");
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result()->fetch_assoc();
                                
                                echo $result ? 
                                    htmlspecialchars($result['shift_name'])." (".date("h:i A", strtotime($result['start_time']))." - ".date("h:i A", strtotime($result['end_time'])).")" 
                                    : "No schedule assigned";
                            ?>
                        </p>

                        <p><span>🟢 Status:</span> 
                            <span id="attendanceStatus" class="status-tag">Not yet clocked in</span>
                        </p>

                        <p><span>⏰ Clock:</span> <span id="liveClock" class="clock-time"></span></p>
                    </div>

                    <!-- Options -->
                    <div class="attendance-actions">
                        <button class="action-btn clockin" data-action="clockin">Clock In</button>
                        <button class="action-btn absent" data-action="absent">Absent</button>
                        <button class="action-btn earlyout" data-action="earlyout">Early Out</button>
                    </div>
                </div>    
            </div>

        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal fade" id="SuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <i class="bi bi-check-circle-fill text-success"></i>
                <h5 class="mt-3 fw-bold" id="successMessage">Attendance Recorded!</h5>
                <p class="text-muted small">Your attendance has been successfully saved.</p>
                <button class="btn btn-primary mt-3" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="ConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <i class="bi bi-question-circle-fill text-warning"></i>
                <h5 class="mt-3 fw-bold">Confirm Action</h5>
                <p class="text-muted small" id="confirmText">Are you sure you want to perform this action?</p>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button class="btn btn-primary" id="confirmBtn">Yes, Proceed</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <script src="js/date_time.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Live Clock (Attendance only)
    function updateLiveClock() {
        const now = new Date();
        document.getElementById("liveClock").textContent = now.toLocaleTimeString();
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    // Button references
    const clockinBtn = document.querySelector(".action-btn.clockin");
    const absentBtn  = document.querySelector(".action-btn.absent");
    const earlyBtn   = document.querySelector(".action-btn.earlyout");

    // Modals
    let actionType = "";
    const confirmModal = new bootstrap.Modal(document.getElementById("ConfirmModal"));
    const successModal = new bootstrap.Modal(document.getElementById("SuccessModal"));

    // Update buttons based on state
    function updateButtons(state) {
        if (state === "none") {
            clockinBtn.style.display = "block";
            clockinBtn.textContent = "Clock In";
            clockinBtn.dataset.action = "clockin";
            absentBtn.style.display = "block";
            earlyBtn.style.display = "none";
        } else if (state === "ontime" || state === "late") {
            clockinBtn.style.display = "block";
            clockinBtn.textContent = "Clock Out";
            clockinBtn.dataset.action = "clockout";
            absentBtn.style.display = "none";
            earlyBtn.style.display = "block";
        } else if (state === "absent" || state === "earlyout" || state === "overtime") {
            clockinBtn.style.display = "none";
            absentBtn.style.display = "none";
            earlyBtn.style.display = "none";
        }
    }

    // Fetch today’s attendance status on load
    fetch("Controller/AttendanceController.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "action=status"
    })
    .then(res => res.json())
    .then(data => {
        updateButtons(data.status);
        document.getElementById("attendanceStatus").textContent =
            data.status === "none" ? "Not yet clocked in" : data.status;
        document.getElementById("attendanceStatus").className =
            "status-tag " + (
                data.status === "ontime" ? "bg-success" :
                data.status === "late" ? "bg-warning text-dark" :
                data.status === "absent" ? "bg-danger" :
                data.status === "earlyout" ? "bg-info text-dark" : "bg-secondary"
            );
    });

    // Button click → show confirm modal
    document.querySelectorAll("[data-action]").forEach(btn => {
        btn.addEventListener("click", function() {
            actionType = this.getAttribute("data-action");
            document.getElementById("confirmText").textContent =
                actionType === "clockin" ? "Do you want to clock in now?" :
                actionType === "clockout" ? "Do you want to clock out now?" :
                actionType === "absent" ? "Mark yourself absent today?" :
                "Confirm early out?";
            confirmModal.show();
        });
    });

    // Confirm modal → perform action
    document.getElementById("confirmBtn").addEventListener("click", function() {
        confirmModal.hide();

        fetch("Controller/AttendanceController.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "action=" + actionType
        })
        .then(res => res.json())
        .then(data => {
            // Update status badge
            document.getElementById("attendanceStatus").textContent = data.status;
            document.getElementById("attendanceStatus").className =
                "status-tag " + (
                    data.status === "ontime" ? "bg-success" :
                    data.status === "late" ? "bg-warning text-dark" :
                    data.status === "absent" ? "bg-danger" :
                    data.status === "earlyout" ? "bg-info text-dark" :
                    data.status === "overtime" ? "bg-dark text-white" :
                    "bg-secondary"
                );

            // Update buttons for new state
            updateButtons(data.status);

            // Show success modal
            document.getElementById("successMessage").textContent = data.message;
            successModal.show();
        });
    });
});
</script>


</body>
</html>
