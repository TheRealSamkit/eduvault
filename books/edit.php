<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

requireLogin();

$error = '';
$success = '';
$book = null;

if (isset($_GET['id'])) {
    $book_id = mysqli_real_escape_string($mysqli, $_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT * FROM book_listings WHERE id = $book_id AND user_id = $user_id";
    $result = mysqli_query($mysqli, $query);
    $book = mysqli_fetch_assoc($result);
    
    if (!$book) {
        header("Location: ../dashboard/my_books.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($mysqli, $_POST['title']);
    $subject = mysqli_real_escape_string($mysqli, $_POST['subject']);
    $board = mysqli_real_escape_string($mysqli, $_POST['board']);
    $location = mysqli_real_escape_string($mysqli, $_POST['location']);
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);
    
    $image_path = $book['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if (!empty($image_path) && file_exists($image_path)) {
                unlink($image_path);
            }
            $image_path = '../images/books/' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $error = "Failed to upload image";
            }
        } else {
            $error = "Invalid image format. Allowed: JPG, JPEG, PNG";
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE book_listings SET 
                  title = '$title',
                  subject = '$subject',
                  board = '$board',
                  location = '$location',
                  status = '$status',
                  image_path = '$image_path'
                  WHERE id = $book_id AND user_id = $user_id";
        
        if (mysqli_query($mysqli, $query)) {
            $success = "Book updated successfully!";
            $book = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM book_listings WHERE id = $book_id"));
        } else {
            $error = "Failed to update book: " . mysqli_error($mysqli);
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Book</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Book Title</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($book['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" 
                               value="<?php echo htmlspecialchars($book['subject']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Board</label>
                        <select name="board" class="form-select" required>
                            <option value="">Select Board</option>
                            <option value="CBSE" <?php echo $book['board'] == 'CBSE' ? 'selected' : ''; ?>>CBSE</option>
                            <option value="ICSE" <?php echo $book['board'] == 'ICSE' ? 'selected' : ''; ?>>ICSE</option>
                            <option value="State Board" <?php echo $book['board'] == 'State Board' ? 'selected' : ''; ?>>State Board</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Available" <?php echo $book['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                            <option value="Donated" <?php echo $book['status'] == 'Donated' ? 'selected' : ''; ?>>Donated</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" 
                               value="<?php echo htmlspecialchars($book['location']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Book Image</label>
                        <?php if (!empty($book['image_path'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo $book['image_path']; ?>" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Leave empty to keep current image</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="../dashboard/my_books.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Books
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>