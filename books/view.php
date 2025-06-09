<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$book_id = mysqli_real_escape_string($mysqli, $_GET['id']);
$query = "SELECT b.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone, u.location as owner_location, s.name as subject, bo.name as board 
          FROM book_listings b
          JOIN users u ON b.user_id = u.id 
          JOIN subjects s ON b.subject_id = s.id
          JOIN boards bo ON b.board_id = bo.id
          WHERE b.id = $book_id";

$result = mysqli_query($mysqli, $query);
$book = mysqli_fetch_assoc($result);

if (!$book) {
    header("Location: list.php");
    exit();
}
?>

<div class="row justify-content-center container-fluid my-5">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (!empty($book['image_path'])): ?>
                            <img src="<?php echo $book['image_path']; ?>" class="img-fluid rounded" alt="Book Cover">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="height: 300px;">
                                <i class="fas fa-book fa-4x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-8">
                        <h2 class="mb-3"><?php echo htmlspecialchars($book['title']); ?></h2>

                        <div class="mb-4">
                            <span class="badge bg-primary me-2">
                                <i
                                    class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($book['subject']); ?>
                            </span>
                            <span class="badge bg-secondary me-2">
                                <i class="fas fa-university me-1"></i><?php echo htmlspecialchars($book['board']); ?>
                            </span>
                            <span
                                class="badge <?php echo $book['status'] == 'Available' ? 'bg-success' : 'bg-secondary'; ?>">
                                <i class="fas fa-check-circle me-1"></i><?php echo $book['status']; ?>
                            </span>
                        </div>

                        <h5 class="mb-3">Owner Details</h5>
                        <p class="mb-2">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($book['owner_name']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($book['owner_location']); ?>
                        </p>

                        <?php if (isLoggedIn()): ?>
                            <div class="alert alert-info">
                                <h6 class="mb-2">Contact Information</h6>
                                <p class="mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    <?php echo htmlspecialchars($book['owner_email']); ?>
                                </p>
                                <?php if (!empty($book['owner_phone'])): ?>
                                    <p class="mb-0">
                                        <i class="fas fa-phone me-2"></i>
                                        <?php echo htmlspecialchars($book['owner_phone']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-lock me-2"></i>
                                Please <a href="../login.php">login</a> to view contact details
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white">
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Books
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>