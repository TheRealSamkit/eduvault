<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/email_manager.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    redirect('index.php');
}

$email_success = '';
$email_error = '';
$email_manager = new EmailManager();

// Handle adding new items and notify users
if (isset($_POST['add_board'])) {
    $board_name = trim($_POST['board_name']);
    if (!empty($board_name)) {
        $add_board_stmt = mysqli_prepare($mysqli, "INSERT INTO boards (name) VALUES (?)");
        mysqli_stmt_bind_param($add_board_stmt, 's', $board_name);
        mysqli_stmt_execute($add_board_stmt);
        mysqli_stmt_close($add_board_stmt);
        // Notify users
        if (isset($_POST['notify_users'])) {
            $users = mysqli_query($mysqli, "SELECT name, email FROM users WHERE status = 'active'");
            $sent = 0;
            while ($user = mysqli_fetch_assoc($users)) {
                $ok = $email_manager->sendCustom($user['email'], 'New Board Added', '<p>Hi ' . htmlspecialchars($user['name']) . ',</p><p>A new board <b>' . htmlspecialchars($board_name) . '</b> has been added to EduVault.</p>');
                if ($ok)
                    $sent++;
            }
            $email_success = "Notification sent to $sent users.";
        }
    }
}
if (isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']);
    if (!empty($subject_name)) {
        $add_subject_stmt = mysqli_prepare($mysqli, "INSERT INTO subjects (name) VALUES (?)");
        mysqli_stmt_bind_param($add_subject_stmt, 's', $subject_name);
        mysqli_stmt_execute($add_subject_stmt);
        mysqli_stmt_close($add_subject_stmt);
        // Notify users
        if (isset($_POST['notify_users'])) {
            $users = mysqli_query($mysqli, "SELECT name, email FROM users WHERE status = 'active'");
            $sent = 0;
            while ($user = mysqli_fetch_assoc($users)) {
                $ok = $email_manager->sendCustom($user['email'], 'New Subject Added', '<p>Hi ' . htmlspecialchars($user['name']) . ',</p><p>A new subject <b>' . htmlspecialchars($subject_name) . '</b> has been added to EduVault.</p>');
                if ($ok)
                    $sent++;
            }
            $email_success = "Notification sent to $sent users.";
        }
    }
}
if (isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name']);
    if (!empty($course_name)) {
        $add_course_stmt = mysqli_prepare($mysqli, "INSERT INTO courses (name) VALUES (?)");
        mysqli_stmt_bind_param($add_course_stmt, 's', $course_name);
        mysqli_stmt_execute($add_course_stmt);
        mysqli_stmt_close($add_course_stmt);
        // Notify users
        if (isset($_POST['notify_users'])) {
            $users = mysqli_query($mysqli, "SELECT name, email FROM users WHERE status = 'active'");
            $sent = 0;
            while ($user = mysqli_fetch_assoc($users)) {
                $ok = $email_manager->sendCustom($user['email'], 'New Course Added', '<p>Hi ' . htmlspecialchars($user['name']) . ',</p><p>A new course <b>' . htmlspecialchars($course_name) . '</b> has been added to EduVault.</p>');
                if ($ok)
                    $sent++;
            }
            $email_success = "Notification sent to $sent users.";
        }
    }
}
if (isset($_POST['add_year'])) {
    $year = trim($_POST['year']);
    if (!empty($year)) {
        $add_year_stmt = mysqli_prepare($mysqli, "INSERT INTO years (year) VALUES (?)");
        mysqli_stmt_bind_param($add_year_stmt, 's', $year);
        mysqli_stmt_execute($add_year_stmt);
        mysqli_stmt_close($add_year_stmt);
        // Notify users
        if (isset($_POST['notify_users'])) {
            $users = mysqli_query($mysqli, "SELECT name, email FROM users WHERE status = 'active'");
            $sent = 0;
            while ($user = mysqli_fetch_assoc($users)) {
                $ok = $email_manager->sendCustom($user['email'], 'New Year Added', '<p>Hi ' . htmlspecialchars($user['name']) . ',</p><p>A new year <b>' . htmlspecialchars($year) . '</b> has been added to EduVault.</p>');
                if ($ok)
                    $sent++;
            }
            $email_success = "Notification sent to $sent users.";
        }
    }
}

// Handle item removal
if (isset($_POST['action'], $_POST['item_id'], $_POST['type'])) {
    $item_id = (int) $_POST['item_id'];
    $type = $_POST['type'];

    switch ($type) {
        case 'board':
            $table = 'boards';
            break;
        case 'subject':
            $table = 'subjects';
            break;
        case 'course':
            $table = 'courses';
            break;
        case 'year':
            $table = 'years';
            break;
        default:
            break;
    }

    if (isset($table)) {
        $del_stmt = mysqli_prepare($mysqli, "DELETE FROM $table WHERE id = ?");
        mysqli_stmt_bind_param($del_stmt, 'i', $item_id);
        mysqli_stmt_execute($del_stmt);
        mysqli_stmt_close($del_stmt);
    }
}

// Fetch all data
$boards = getAllBoards($mysqli);
$subjects = getAllSubjects($mysqli);
$courses = mysqli_query($mysqli, "SELECT * FROM courses ORDER BY name ASC");
$years = mysqli_query($mysqli, "SELECT * FROM years ORDER BY year DESC");

$title = "Educational Metadata Management - Admin Panel";
require_once '../includes/admin_header.php';
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>

            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>Educational Metadata Management</h2>

                <div class="row">
                    <!-- Boards Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>Boards</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addBoardModal">
                                    <i class="fas fa-plus me-2"></i>Add Board
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="boardsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($board = mysqli_fetch_assoc($boards)): ?>
                                                <tr>
                                                    <td><?= $board['id']; ?></td>
                                                    <td><?= htmlspecialchars($board['board']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger"
                                                            onclick="removeItem(<?= $board['id']; ?>, 'board')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-book-open me-2"></i>Subjects</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addSubjectModal">
                                    <i class="fas fa-plus me-2"></i>Add Subject
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="subjectsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($subject = mysqli_fetch_assoc($subjects)): ?>
                                                <tr>
                                                    <td><?= $subject['id']; ?></td>
                                                    <td><?= htmlspecialchars($subject['subject']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger"
                                                            onclick="removeItem(<?= $subject['id']; ?>, 'subject')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Courses</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addCourseModal">
                                    <i class="fas fa-plus me-2"></i>Add Course
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="coursesTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                                                <tr>
                                                    <td><?= $course['id']; ?></td>
                                                    <td><?= htmlspecialchars($course['name']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger"
                                                            onclick="removeItem(<?= $course['id']; ?>, 'course')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Years Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Years</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#addYearModal">
                                    <i class="fas fa-plus me-2"></i>Add Year
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table" id="yearsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Year</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($year = mysqli_fetch_assoc($years)): ?>
                                                <tr>
                                                    <td><?= $year['id']; ?></td>
                                                    <td><?= htmlspecialchars($year['year']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger"
                                                            onclick="removeItem(<?= $year['id']; ?>, 'year')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once '../modals/addBoardModal.php';
    require_once '../modals/addSubjectModal.php';
    require_once '../modals/addCourseModal.php';
    require_once '../modals/addYearModal.php';
    include '../includes/admin_footer.php'; ?>

    <script>
        $(document).ready(function () {
            $('#boardsTable, #subjectsTable, #coursesTable, #yearsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                pageLength: 10,
                order: [[1, 'asc']]
            });
        });

        function removeItem(itemId, type) {
            if (confirm('Are you sure you want to remove this item? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="item_id" value="${itemId}">
                    <input type="hidden" name="type" value="${type}">
                    <input type="hidden" name="action" value="remove">
                `;
                document.body.append(form);
                form.submit();
            }
        }
    </script>
</body>

</html>
<?php if ($email_success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($email_success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($email_error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($email_error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>