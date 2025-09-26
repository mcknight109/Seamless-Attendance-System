    <?php
    session_start();
    include '../db_connection.php';

    // Set timezone
    date_default_timezone_set('Asia/Manila');

    // Check if user is logged in and admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: login.php");
        exit();
    }

    // Fetch users and their assigned schedules (users.schedule_id references schedules.schedule_id)
    $users_sql = "
        SELECT u.user_id, u.fname, u.lname, u.email, u.schedule_id,
            s.shift_name, s.start_time, s.end_time
        FROM users u
        LEFT JOIN schedules s ON u.schedule_id = s.schedule_id
        ORDER BY u.lname, u.fname
    ";
    $users_result = $conn->query($users_sql);
    $users = $users_result ? $users_result->fetch_all(MYSQLI_ASSOC) : [];

    // Fetch created schedules
    $schedules_sql = "SELECT * FROM schedules ORDER BY created_at DESC";
    $schedules_result = $conn->query($schedules_sql);
    $schedules = $schedules_result ? $schedules_result->fetch_all(MYSQLI_ASSOC) : [];

    // Read success indicator to show success modal (controller redirects back with ?success=xxx)
    $success = isset($_GET['success']) ? $_GET['success'] : '';
    $confirm = isset($_GET['confirm']) ? $_GET['confirm'] : '';
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Schedule Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../admin/scss/admin.scss">
    <link rel="stylesheet" href="../admin/scss/table.scss">
    <link rel="stylesheet" href="../admin/scss/btn.scss">
    <link rel="stylesheet" href="../admin/scss/dashboard.scss">
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
        <a class="nav-link" href="attendance.php"><span class="icon"><i class="bi bi-list-check"></i></span><span class="description">Attendance</span></a>
        <a class="nav-link" href="leave_requests.php"><span class="icon"><i class="bi bi-chat-left-text"></i></span><span class="description">Leave Requests</span></a>
        <a class="nav-link active" href="schedule.php"><span class="icon"><i class="bi bi-clipboard"></i></span><span class="description">Schedule</span></a>
        <hr>
        <a class="nav-link" href="../logout.php"><span class="icon"><i class="bi bi-box-arrow-right"></i></span><span class="description">Logout</span></a>
        </nav>
    </div>

    <main class="main-content">
        <div class="content-container">
        <div class="header">
            <div class="page-title">
            <h1>SCHEDULE MANAGEMENT</h1>
            <p id="current-date">Wed, January 20, 2026</p>
            <p id="current-time">Time: 01:20 PM</p>
            </div>
            <div class="welcome-message">
            <span class="icon"><i class="bi bi-person-circle"></i></span>
            Welcome, <? echo $fname, '', $lname?>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="table-top">
                <div class="top2">
                    <div class="search-container">
                        <form method="get" class="search-form d-flex">
                            <input type="text" name="search" class="form-control" placeholder="Search user or email" value="">
                            <button type="submit" class="btn btn-primary ms-2"><i class="bi bi-search"></i> Search</button>
                        </form>
                    </div>
                    
                    <div class="table-btn">
                        <button id="showUsersBtn" class="btn btn-outline-primary">Users</button>
                        <button id="showSchedulesBtn" class="btn btn-primary">Schedules</button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addScheduleModal"><i class="bi bi-plus-lg"></i> Add Schedule</button>
                    </div>
                </div>
            </div>

            <div class="manage-container">
            <!-- Users table (default visible) -->
            <div id="usersTableWrapper">
                <table class="table">
                <thead>
                    <tr>
                    <th>Name</th>
                    <th>Schedule</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($u['fname'].' '.$u['lname']); ?></td>
                        <td>
                            <?php echo $u['shift_name'] ? htmlspecialchars($u['shift_name']) : '<span class="text-muted">No schedule</span>'; ?>
                        </td>
                        <td><?php echo $u['start_time'] ? date("h:i A", strtotime($u['start_time'])) : '-'; ?></td>
                        <td><?php echo $u['end_time'] ? date("h:i A", strtotime($u['end_time'])) : '-'; ?></td>
                        <td class="btn-actions">
                            <!-- Edit assigned schedule (opens modal) -->
                            <button class="btn btn-sm btn-outline-secondary me-1 edit-user-schedule-btn btn1-action"
                                    data-user-id="<?php echo $u['user_id']; ?>"
                                    data-user-name="<?php echo htmlspecialchars($u['fname'].' '.$u['lname']); ?>"
                                    data-schedule-id="<?php echo $u['schedule_id']; ?>"
                                    type="button">
                            <i class="bi bi-pencil-square"></i>
                            </button>

                            <!-- Clear user's schedule -->
                            <button class="btn btn-sm btn-outline-danger clear-user-schedule-btn btn2-action"
                                    data-user-id="<?php echo $u['user_id']; ?>"
                                    data-user-name="<?php echo htmlspecialchars($u['fname'].' '.$u['lname']); ?>"
                                    type="button">
                            <i class="bi bi-x-square"></i>
                            </button>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="5" class="text-center">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>

            <!-- Schedules table (hidden by default) -->
            <div id="schedulesTableWrapper" style="display:none;">
                <table class="table table-striped">
                <thead>
                    <tr>
                    <th>Schedule</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($schedules)): ?>
                    <?php foreach ($schedules as $s): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($s['shift_name']); ?></td>
                        <td><?php echo date("h:i A", strtotime($s['start_time'])); ?></td>
                        <td><?php echo date("h:i A", strtotime($s['end_time'])); ?></td>
                        <td class="btn-actions">
                            <button class="btn btn-sm btn-outline-secondary edit-schedule-btn btn1-action"
                                    data-schedule-id="<?php echo $s['schedule_id']; ?>"
                                    data-shift-name="<?php echo htmlspecialchars($s['shift_name']); ?>"
                                    data-start-time="<?php echo $s['start_time']; ?>"
                                    data-end-time="<?php echo $s['end_time']; ?>"
                                    type="button">
                            <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-schedule-btn btn2-action"
                                    data-schedule-id="<?php echo $s['schedule_id']; ?>"
                                    data-shift-name="<?php echo htmlspecialchars($s['shift_name']); ?>"
                                    type="button">
                            <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="4" class="text-center">No schedules found.</td></tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
            </div> <!-- manage-container -->
        </div> <!-- dashboard-content -->
        </div> <!-- content-container -->
    </main>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="Controller/ScheduleController.php?action=add_schedule" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Shift Name</label>
                        <input name="shift_name" required class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input name="start_time" type="time" required class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <input name="end_time" type="time" required class="form-control" />
                    </div>
                    </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="Controller/ScheduleController.php?action=edit_schedule" class="modal-content">
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Shift Name</label>
                        <input name="shift_name" id="edit_shift_name" required class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input name="start_time" id="edit_start_time" type="time" required class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <input name="end_time" id="edit_end_time" type="time" required class="form-control" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign / Edit User Schedule Modal -->
    <div class="modal fade" id="assignUserScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="Controller/ScheduleController.php?action=assign_schedule" class="modal-content">
                <input type="hidden" name="user_id" id="assign_user_id">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="assign_user_name" class="small text-muted"></p>
                    <div class="mb-3">
                        <label class="form-label">Select Schedule</label>
                        <select name="schedule_id" id="assign_schedule_select" class="form-select" required>
                        <option value="">-- choose schedule --</option>
                        <?php foreach ($schedules as $s): ?>
                            <option value="<?php echo $s['schedule_id']; ?>"><?php echo htmlspecialchars($s['shift_name']).' ('.date("h:i A", strtotime($s['start_time'])).' - '.date("h:i A", strtotime($s['end_time'])).')'; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Assign</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete / Clear Confirmation Modal (re-uses existing structure) -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:36px;color:red;"></i>
                    <h5 class="mt-3" id="deleteConfirmTitle">Are you sure?</h5>
                    <p class="small text-muted" id="deleteConfirmText">This action cannot be undone.</p>
                    <div class="mt-3">
                        <form method="post" id="deleteConfirmForm" style="display:inline;">
                        <!-- action and hidden inputs will be set by JS -->
                        <input type="hidden" name="target_id" id="delete_target_id" value="">
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal (you said you already made one; re-using setSuccessModal id) -->
    <div class="modal fade" id="setSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <i class="bi bi-check-circle-fill" style="font-size:36px;color:green;"></i>
                    <h5 class="mt-3" id="successMessage">Success</h5>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="successOkBtn">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple toggles between Users and Schedules tables
        const usersWrapper = document.getElementById('usersTableWrapper');
        const schedulesWrapper = document.getElementById('schedulesTableWrapper');
        document.getElementById('showSchedulesBtn').addEventListener('click', () => {
        usersWrapper.style.display = 'none';
        schedulesWrapper.style.display = '';
    });
        document.getElementById('showUsersBtn').addEventListener('click', () => {
        schedulesWrapper.style.display = 'none';
        usersWrapper.style.display = '';
    });

        // Edit schedule button — populate edit modal
        document.querySelectorAll('.edit-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_schedule_id').value = btn.dataset.scheduleId;
            document.getElementById('edit_shift_name').value = btn.dataset.shiftName;
            document.getElementById('edit_start_time').value = btn.dataset.startTime;
            document.getElementById('edit_end_time').value = btn.dataset.endTime;
            new bootstrap.Modal(document.getElementById('editScheduleModal')).show();
        });
    });

        // Assign/edit user schedule buttons — populate assign modal
        document.querySelectorAll('.edit-user-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('assign_user_id').value = btn.dataset.userId;
            document.getElementById('assign_user_name').textContent = btn.dataset.userName;
            // preselect current schedule if available
            const currentSchedule = btn.dataset.scheduleId;
            const select = document.getElementById('assign_schedule_select');
            if (currentSchedule) select.value = currentSchedule;
            else select.value = '';
            new bootstrap.Modal(document.getElementById('assignUserScheduleModal')).show();
        });
    });

        // Clear user's schedule: open confirm modal and set form to clear_user_schedule
        document.querySelectorAll('.clear-user-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const userId = btn.dataset.userId;
            const userName = btn.dataset.userName;
            // configure confirm modal
            document.getElementById('deleteConfirmTitle').textContent = 'Clear user schedule?';
            document.getElementById('deleteConfirmText').textContent = 'This will remove the schedule assignment from ' + userName + '.';
            const form = document.getElementById('deleteConfirmForm');
            form.action = 'Controller/ScheduleController.php?action=clear_user_schedule';
            document.getElementById('delete_target_id').name = 'user_id';
            document.getElementById('delete_target_id').value = userId;
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        });
    });

        // Delete schedule (from schedules table)
        document.querySelectorAll('.delete-schedule-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const scheduleId = btn.dataset.scheduleId;
            const shiftName = btn.dataset.shiftName;
            document.getElementById('deleteConfirmTitle').textContent = 'Delete schedule?';
            document.getElementById('deleteConfirmText').textContent = 'Delete schedule "' + shiftName + '" permanently?';
            const form = document.getElementById('deleteConfirmForm');
            form.action = 'Controller/ScheduleController.php?action=delete_schedule';
            document.getElementById('delete_target_id').name = 'schedule_id';
            document.getElementById('delete_target_id').value = scheduleId;
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        });
    });

        // After actions, controller may redirect with ?success=... ; show success modal with message
        (function showSuccessIfAny(){
        const urlParams = new URLSearchParams(window.location.search);
        const s = urlParams.get('success');
        if (!s) return;
        let msg = 'Success';
        if (s === 'schedule_added') msg = 'Schedule added successfully';
        else if (s === 'schedule_updated') msg = 'Schedule updated';
        else if (s === 'schedule_deleted') msg = 'Schedule deleted';
        else if (s === 'user_assigned') msg = 'User schedule assigned';
        else if (s === 'user_cleared') msg = 'User schedule cleared';
        document.getElementById('successMessage').textContent = msg;
        new bootstrap.Modal(document.getElementById('setSuccessModal')).show();

        // remove query param from URL so refreshing won't re-open modal
        const url = new URL(window.location);
        url.searchParams.delete('success');
        window.history.replaceState({}, document.title, url.toString());
        })();
    </script>
</body>
</html>
