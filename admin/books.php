<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle book actions
if (isset($_POST['action'], $_POST['book_id'])) {
    $book_id = (int) $_POST['book_id'];
    $action = $_POST['action'];

    if ($action === 'remove') {
        $del_stmt = mysqli_prepare($mysqli, "DELETE FROM book_listings WHERE id = ?");
        mysqli_stmt_bind_param($del_stmt, 'i', $book_id);
        mysqli_stmt_execute($del_stmt);
        mysqli_stmt_close($del_stmt);
    } elseif ($action === 'verify') {
        $verify_stmt = mysqli_prepare($mysqli, "UPDATE book_listings SET verified = 1 WHERE id = ?");
        mysqli_stmt_bind_param($verify_stmt, 'i', $book_id);
        mysqli_stmt_execute($verify_stmt);
        mysqli_stmt_close($verify_stmt);
    }

    // Log the action
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt = mysqli_prepare($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
    $log_action = "Book $action ID: $book_id";
    mysqli_stmt_bind_param($log_stmt, 'iss', $admin_id, $log_action, $ip);
    mysqli_stmt_execute($log_stmt);
    mysqli_stmt_close($log_stmt);
}

// Handle adding boards or subjects
if (isset($_POST['add_board'])) {
    $board_name = $_POST['board_name'];
    $add_board_stmt = mysqli_prepare($mysqli, "INSERT INTO boards (name) VALUES (?)");
    mysqli_stmt_bind_param($add_board_stmt, 's', $board_name);
    mysqli_stmt_execute($add_board_stmt);
    mysqli_stmt_close($add_board_stmt);
}
if (isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'];
    $add_subject_stmt = mysqli_prepare($mysqli, "INSERT INTO subjects (name) VALUES (?)");
    mysqli_stmt_bind_param($add_subject_stmt, 's', $subject_name);
    mysqli_stmt_execute($add_subject_stmt);
    mysqli_stmt_close($add_subject_stmt);
}

$books = mysqli_query($mysqli, "SELECT b.*, u.name as owner_name, u.email as owner_email, bo.name as board, s.name as subject
                                FROM book_listings b
                                JOIN users u ON b.user_id = u.id
                                JOIN boards bo ON b.board_id = bo.id
                                JOIN subjects s ON b.subject_id = s.id
                                ORDER BY b.created_at DESC");

$boards = getAllBoards($mysqli);
$subjects = getAllSubjects($mysqli);

$title = "Book Management - Admin Panel";
require_once '../includes/admin_header.php';
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/admin_sidebar.php'; ?>

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-book me-2"></i>Book Management</h2>
                    <div>
                        <button class="btn btn-success me-2" onclick="exportBooks('csv')"><i
                                class="fas fa-file-csv me-2"></i>Export CSV</button>
                        <button class="btn btn-primary" onclick="exportBooks('pdf')"><i
                                class="fas fa-file-pdf me-2"></i>Export PDF</button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body table-responsive">
                        <table class="table" id="booksTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Board</th>
                                    <th>Owner</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Posted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($book = mysqli_fetch_assoc($books)): ?>
                                    <tr>
                                        <td><?= $book['id']; ?></td>
                                        <td><?= htmlspecialchars($book['title']); ?></td>
                                        <td><?= htmlspecialchars($book['subject']); ?></td>
                                        <td><?= htmlspecialchars($book['board']); ?></td>
                                        <td>
                                            <span data-bs-toggle="tooltip"
                                                title="<?= htmlspecialchars($book['owner_email']); ?>">
                                                <?= htmlspecialchars($book['owner_name']); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($book['location']); ?></td>
                                        <td><span
                                                class="badge bg-<?= $book['status'] === 'Available' ? 'success' : 'secondary'; ?>"><?= $book['status']; ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($book['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="viewBook(<?= $book['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="removeBook(<?= $book['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4><i class="fas fa-chalkboard me-2"></i>Boards</h4>
                        <div class="card">
                            <div class="card-body table-responsive">
                                <table class="table" id="boardsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($board = mysqli_fetch_assoc($boards)): ?>
                                            <tr>
                                                <td><?= $board['id']; ?></td>
                                                <td><?= htmlspecialchars($board['board']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <button class="btn btn-outline-primary w-100 mt-2" data-bs-toggle="modal"
                                    data-bs-target="#addBoardModal">
                                    <i class="fas fa-plus me-2"></i>Add New Board
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4><i class="fas fa-book-open me-2"></i>Subjects</h4>
                        <div class="card">
                            <div class="card-body table-responsive">
                                <table class="table" id="subjectsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($subject = mysqli_fetch_assoc($subjects)): ?>
                                            <tr>
                                                <td><?= $subject['id']; ?></td>
                                                <td><?= htmlspecialchars($subject['subject']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <button class="btn btn-outline-primary w-100 mt-2" data-bs-toggle="modal"
                                    data-bs-target="#addSubjectModal">
                                    <i class="fas fa-plus me-2"></i>Add New Subject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php
    require_once '../modals/viewBookModal.php';
    require_once '../modals/addBookModal.php';
    require_once '../modals/addSubjectModal.php';
    include '../includes/admin_footer.php'; ?>

    <script>
        $(document).ready(function () {
            $('#booksTable').DataTable({ order: [[8, 'desc']], pageLength: 10 });
            $('#boardsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                pageLength: 10,
                order: [[1, 'asc']]
            });

            $('#subjectsTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                pageLength: 10,
                order: [[1, 'asc']]
            });
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

            $('#bookModal').on('shown.bs.modal', function () {
                $('#bookModalBody').focus();
            });
        });

        function viewBook(bookId) {
            $.get('ajax/get_book.php', { id: bookId }, function (data) {
                $('#bookModalBody').html(data);
                $('#bookModal').modal('show');
            });
        }

        function removeBook(bookId) {
            if (confirm('Are you sure you want to remove this book? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="book_id" value="${bookId}"><input type="hidden" name="action" value="remove">`;
                document.body.append(form);
                form.submit();
            }
        }

        function exportBooks(format) {
            window.location.href = `export.php?format=${format}&type=books`;
        }
    </script>
</body>

</html>