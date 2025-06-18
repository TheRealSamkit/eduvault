<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();

$error = '';
$success = '';
$max_image_size = 2 * 1024 * 1024;
$book = null;

$subjects = getAllSubjects($mysqli);
$boards = getAllBoards($mysqli);

$allowed_mimes = getImageMimes($mysqli);
$allowed_ext = array_keys($allowed_mimes);
$allowed_mime_types = $allowed_mimes;

// Validate & fetch book for editing
if (isset($_GET['id'])) {
    $book_id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = mysqli_prepare($mysqli, "SELECT * FROM book_listings WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $book_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $book = mysqli_fetch_assoc($result);

    if (!$book) {
        header("Location: ../dashboard/my_books.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($mysqli, trim($_POST['title']));
    $subject = (int) $_POST['subject'];
    $board = (int) $_POST['board'];
    $location = mysqli_real_escape_string($mysqli, trim($_POST['location']));
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);

    $image_path = $book['image_path'];

    // Image handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_ext = array_keys($allowed_mime_types);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext)) {
            if ($_FILES['image']['size'] > $max_image_size) {
                $error = "Image size exceeds 2MB limit.";
            } else {
                // Validate MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected_mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if ($detected_mime !== $allowed_mime_types[$ext]) {
                    $error = "Invalid image MIME type.";
                } else {
                    // Remove old image if exists
                    $old_image_local = '../uploads/images/' . basename($book['image_path']);
                    if (!empty($book['image_path']) && file_exists($old_image_local)) {
                        unlink($old_image_local);
                    }

                    $new_image_name = uniqid() . '.' . $ext;
                    $upload_path = '../uploads/images/' . $new_image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_path = BASE_URL . 'uploads/images/' . $new_image_name;
                    } else {
                        $error = "Failed to upload image.";
                    }
                }
            }
        } else {
            $error = "Invalid image format. Allowed: " . strtoupper(implode(', ', $allowed_ext));
        }
    }

    // If no errors, proceed with DB update
    if (empty($error)) {
        $query = "UPDATE book_listings 
                  SET title = ?, subject_id = ?, board_id = ?, location = ?, status = ?, image_path = ? 
                  WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, "siisssii", $title, $subject, $board, $location, $status, $image_path, $book_id, $_SESSION['user_id']);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Book updated successfully!";
            header("Location: edit.php?id=$book_id");
            exit();
        } else {
            $error = "Failed to update book: " . mysqli_error($mysqli);
        }
    }

    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header("Location: edit.php?id=$book_id");
        exit();
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Book</h4>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Book Title</label>
                        <input type="text" name="title" class="form-control bg-dark-body"
                            value="<?php echo htmlspecialchars($book['title']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject" class="form-select bg-dark-body" required>
                            <option value="">Select Subject</option>
                            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                    <option value="<?php echo htmlspecialchars($s['id']); ?>" 
                                        <?php echo $book['subject_id'] == $s['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['subject']); ?>
                                    </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Board</label>
                        <select name="board" class="form-select bg-dark-body" required>
                            <option value="">Select Board</option>
                            <?php while ($b = mysqli_fetch_assoc($boards)): ?>
                                    <option value="<?php echo htmlspecialchars($b['id']); ?>" 
                                        <?php echo $book['board_id'] == $b['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($b['board']); ?>
                                    </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select bg-dark-body" required>
                            <option value="Available" <?php echo $book['status'] == 'Available' ? 'selected' : ''; ?>>
                                Available</option>
                            <option value="Donated" <?php echo $book['status'] == 'Donated' ? 'selected' : ''; ?>>Donated
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control bg-dark-body"
                            value="<?php echo htmlspecialchars($book['location']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Book Image</label>
                        <?php if (!empty($book['image_path'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo $book['image_path']; ?>" class="img-thumbnail"
                                        style="max-width: 200px;">
                                </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control bg-dark-body" accept=".jpg,.jpeg,.png">
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