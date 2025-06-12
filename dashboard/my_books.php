<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Handle book deletion
if (isset($_POST['delete_book'])) {
    $book_id = mysqli_real_escape_string($mysqli, $_POST['book_id']);
    $delete_query = "DELETE FROM book_listings WHERE id = $book_id AND user_id = $user_id";
    mysqli_query($mysqli, $delete_query);
}

// Fetch user's books
$books_query = "SELECT b.*,s.name as subject, bo.name as board
                FROM book_listings b 
                JOIN subjects s ON b.subject_id = s.id
                Join boards bo ON b.board_id = bo.id
                WHERE b.user_id = $user_id
                ORDER BY b.created_at DESC";
$books_result = mysqli_query($mysqli, $books_query);
?>
<div class="container-md mb-5 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book me-2"></i>My Books</h2>
        <a href="../books/add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Book
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (mysqli_num_rows($books_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Board</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($book['board']); ?></td>
                                    <td>
                                        <span
                                            class="badge <?php echo $book['status'] == 'Available' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $book['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($book['location']); ?></td>
                                    <td>
                                        <a href="../books/edit.php?id=<?php echo $book['id']; ?>"
                                            class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this book?');">
                                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                            <button type="submit" name="delete_book" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <p class="lead">You haven't listed any books yet.</p>
                    <a href="../books/add.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Your First Book
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>