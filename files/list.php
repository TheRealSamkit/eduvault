<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

// Handle search and filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';
$course = isset($_GET['course']) ? mysqli_real_escape_string($mysqli, $_GET['course']) : '';
$subject = isset($_GET['subject']) ? mysqli_real_escape_string($mysqli, $_GET['subject']) : '';
$year = isset($_GET['year']) ? mysqli_real_escape_string($mysqli, $_GET['year']) : '';

$where_conditions = ["1=1"];

if (!empty($search)) {
    $where_conditions[] = "(title LIKE '%$search%' OR description LIKE '%$search%' OR subject LIKE '%$search%')";
}
if (!empty($course)) {
    $where_conditions[] = "course = '$course'";
}
if (!empty($subject)) {
    $where_conditions[] = "subject = '$subject'";
}
if (!empty($year)) {
    $where_conditions[] = "year = '$year'";
}

$where_clause = implode(' AND ', $where_conditions);

// Get files with user information and download count
// Update the query to include average rating
$query = "SELECT f.*, u.name as uploader_name, 
          (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count,
          (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating
          FROM digital_files f 
          JOIN users u ON f.user_id = u.id 
          WHERE $where_clause 
          ORDER BY f.upload_date DESC";
$result = mysqli_query($mysqli, $query);

// Get unique filters
$courses = mysqli_query($mysqli, "SELECT DISTINCT course FROM digital_files WHERE course != ''");
$subjects = mysqli_query($mysqli, "SELECT DISTINCT subject FROM digital_files WHERE subject != ''");
$years = mysqli_query($mysqli, "SELECT DISTINCT year FROM digital_files WHERE year != 0 ORDER BY year DESC");
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search files..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="course" class="form-select">
                            <option value="">All Courses</option>
                            <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                <option value="<?php echo htmlspecialchars($c['course']); ?>"
                                        <?php echo $course == $c['course'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['course']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="subject" class="form-select">
                            <option value="">All Subjects</option>
                            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                <option value="<?php echo htmlspecialchars($s['subject']); ?>"
                                        <?php echo $subject == $s['subject'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['subject']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="year" class="form-select">
                            <option value="">All Years</option>
                            <?php while ($y = mysqli_fetch_assoc($years)): ?>
                                <option value="<?php echo $y['year']; ?>"
                                        <?php echo $year == $y['year'] ? 'selected' : ''; ?>>
                                    <?php echo $y['year']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($file = mysqli_fetch_assoc($result)): 
            switch(strtolower($file["file_type"])){
                case "pdf":
                    $file_icon="pdf";
                    break;
                case "docx":
                    $file_icon="word";
                    break;
                case "pptx":
                        $file_icon="powerpoint";
                        break;
                default:
                    $file_icon="lines";
                    break;
            }
            ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-3">
                                <a href="view.php?id=<?php echo $file['id']; ?>" class="text-decoration-none">
                                    <i class="fas fa-file-<?php echo $file_icon ?> me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($file['title']); ?>
                                </a>
                            </h5>
                            <div>
                                <span class="badge bg-info me-2">
                                    <i class="fas fa-download me-1"></i><?php echo $file['download_count']; ?>
                                </span>
                                <span class="badge bg-warning">
                                    <i class="fas fa-star me-1"></i>
                                    <?php echo number_format($file['avg_rating'] ?: 0, 1); ?>
                                </span>
                            </div>
                        </div>
                        
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>
                        
                        <div class="mb-3">
                            <span class="badge bg-primary me-2">
                                <i class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($file['subject']); ?>
                            </span>
                            <span class="badge bg-secondary me-2">
                                <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($file['course']); ?>
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-calendar me-1"></i><?php echo $file['year']; ?>
                            </span>
                        </div>
                        
                        <small class="text-muted">
                            Uploaded by <?php echo htmlspecialchars($file['uploader_name']); ?>
                            on <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                        </small>
                    </div>
                    
                    <div class="card-footer bg-white">
                        <?php if (isLoggedIn()): ?>
                            <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        <?php else: ?>
                            <a href="../login.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-lock me-1"></i>Login to Download
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <p class="lead">No files found matching your criteria.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>