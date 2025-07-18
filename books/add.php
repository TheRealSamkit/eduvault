<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

$sidebar = true;
requireLogin();

$error = '';
$success = '';

$subjects = getAllSubjects($mysqli);
$boards = getAllBoards($mysqli);

$max_image_size = 2 * 1024 * 1024;
$allowed_mimes = getImageMimes($mysqli);
$allowed_ext = array_keys($allowed_mimes);
$allowed_mime_types = $allowed_mimes;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($mysqli, trim($_POST['title']));
    $subject = (int) $_POST['subject'];
    $board = (int) $_POST['board'];
    $location = mysqli_real_escape_string($mysqli, trim($_POST['location']));

    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        if ($_FILES['image']['size'] > $max_image_size) {
            $error = "Image size exceeds 2MB limit.";
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed_ext)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected_mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if ($detected_mime !== $allowed_mime_types[$ext]) {
                    $error = "Invalid image type.";
                } else {
                    $image_name = uniqid() . '.' . $ext;
                    $upload_dir = '../uploads/images/';
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                        $image_path = '/eduvault' . '/uploads/images/' . $image_name;
                    } else {
                        $error = "Failed to upload image.";
                    }
                }
            } else {
                $error = "Invalid image format. Allowed: " . strtoupper(implode(', ', $allowed_ext));
            }
        }
    }

    if (empty($error)) {
        $query = "INSERT INTO book_listings (user_id, title, subject_id, board_id, location, image_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, 'isisss', $user_id, $title, $subject, $board, $location, $image_path);
        if (mysqli_stmt_execute($stmt)) {
            flash('success', 'Book added successfully!');
            redirect("../dashboard/my_books.php");
            exit();
        } else {
            $error = "Failed to add book. Please try again later.";
        }
    } else {
        flash('error', $error);
        redirect("add.php");
        exit();
    }
}

require_once '../includes/header.php';
?>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-fluid row justify-content-center gx-1">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-book me-2"></i>Add New Book</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Book Title</label>
                                <input type="text" name="title" class="form-control bg-dark-body" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <select name="subject" class="form-select input-dark" required>
                                    <option value="">Select Subject</option>
                                    <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                        <option value="<?php echo htmlspecialchars($s['id']); ?>">
                                            <?php echo htmlspecialchars($s['subject']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Board</label>
                                <select name="board" class="form-select input-dark" required>
                                    <option value="">Select Board</option>
                                    <?php while ($b = mysqli_fetch_assoc($boards)): ?>
                                        <option value="<?php echo htmlspecialchars($b['id']); ?>">
                                            <?php echo htmlspecialchars($b['board']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control bg-dark-body" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Book Image</label>
                                <input type="file" name="image" class="form-control bg-dark-body" required
                                    accept=".jpg,.jpeg,.png">
                                <div class="form-text">Max size: 2MB</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Book
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
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>