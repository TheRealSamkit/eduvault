<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle book actions
if (isset($_POST['action']) && isset($_POST['book_id'])) {
    $book_id = (int) $_POST['book_id'];
    $action = $_POST['action'];

    if ($action === 'remove') {
        mysqli_query($mysqli, "DELETE FROM book_listings WHERE id = $book_id");
    } elseif ($action === 'verify') {
        mysqli_query($mysqli, "UPDATE book_listings SET verified = 1 WHERE id = $book_id");
    }

    // Log the action
    $admin_id = $_SESSION['admin_id'];
    $ip = $_SERVER['REMOTE_ADDR'];
    mysqli_query($mysqli, "INSERT INTO activity_logs (admin_id, action, ip_address) 
                          VALUES ($admin_id, 'Book $action ID: $book_id', '$ip')");
}

// Get books list with user information
$books = mysqli_query($mysqli, "SELECT b.*, u.name as owner_name, u.email as owner_email 
                               FROM book_listings b 
                               JOIN users u ON b.user_id = u.id 
                               ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Book Management - Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    </head>

    <body>
        <div class="container-fluid">
            <div class="row">
                <?php include '../includes/sidebar.php'; ?>

                <div class="col-md-10 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-book me-2"></i>Book Management</h2>
                        <div>
                            <button class="btn btn-success me-2" onclick="exportBooks('csv')">
                                <i class="fas fa-file-csv me-2"></i>Export CSV
                            </button>
                            <button class="btn btn-primary" onclick="exportBooks('pdf')">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
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
                                                <td><?php echo $book['id']; ?></td>
                                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                                <td><?php echo htmlspecialchars($book['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($book['board']); ?></td>
                                                <td>
                                                    <span data-bs-toggle="tooltip"
                                                        title="<?php echo htmlspecialchars($book['owner_email']); ?>">
                                                        <?php echo htmlspecialchars($book['owner_name']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($book['location']); ?></td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php echo $book['status'] === 'Available' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $book['status']; ?>
                                                    </span>
                                                </td>

                                                <td><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="viewBook(<?php echo $book['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="removeBook(<?php echo $book['id']; ?>)">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Book View Modal -->
        <div class="modal fade" id="bookModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Book Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="bookModalBody">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#booksTable').DataTable({
                    order: [[8, 'desc']],
                    pageLength: 10
                });

                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });

            function viewBook(bookId) {
                $.get('ajax/get_book.php', { id: bookId }, function (data) {
                    $('#bookModalBody').html(data);
                    $('#bookModal').modal('show');
                });
            }

            function verifyBook(bookId) {
                if (confirm('Are you sure you want to verify this book?')) {
                    submitBookAction(bookId, 'verify');
                }
            }

            function removeBook(bookId) {
                if (confirm('Are you sure you want to remove this book? This action cannot be undone.')) {
                    submitBookAction(bookId, 'remove');
                }
            }

            function submitBookAction(bookId, action) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                <input type="hidden" name="book_id" value="${bookId}">
                <input type="hidden" name="action" value="${action}">
            `;
                document.body.append(form);
                form.submit();
            }

            function exportBooks(format) {
                window.location.href = `export_books.php?format=${format}`;
            }
        </script>
    </body>

</html>