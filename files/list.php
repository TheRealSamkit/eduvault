<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Handle search and filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';
$course = isset($_GET['course']) ? mysqli_real_escape_string($mysqli, $_GET['course']) : '';
$subject = isset($_GET['subject']) ? mysqli_real_escape_string($mysqli, $_GET['subject']) : '';
$year = isset($_GET['year']) ? mysqli_real_escape_string($mysqli, $_GET['year']) : '';
$file_type = isset($_GET['fileType']) ? mysqli_real_escape_string($mysqli, $_GET['fileType']) : '';

$where_conditions = ["1=1"];

if (!empty($search))
    $where_conditions[] = "(title LIKE '%$search%' OR description LIKE '%$search%' OR subject LIKE '%$search%')";
if (!empty($course))
    $where_conditions[] = "course = '$course'";
if (!empty($subject))
    $where_conditions[] = "subject = '$subject'";
if (!empty($year))
    $where_conditions[] = "year = '$year'";
if (!empty($file_type))
    $where_conditions[] = "file_type = '$file_type'";

$where_clause = implode(' AND ', $where_conditions);

$query = "SELECT f.*, u.name as uploader_name, u.id as uploader_id,
    (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id AND status = 'resolved') as report_count,
    (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count,
    (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating
    FROM digital_files f
    JOIN users u ON f.user_id = u.id
    WHERE $where_clause
    ORDER BY f.upload_date DESC";
$result = mysqli_query($mysqli, $query);

$file_types = mysqli_query($mysqli, "SELECT DISTINCT file_type FROM digital_files WHERE file_type != ''");
$courses = mysqli_query($mysqli, "SELECT DISTINCT course FROM digital_files WHERE course != ''");
$subjects = mysqli_query($mysqli, "SELECT DISTINCT subject FROM digital_files WHERE subject != ''");
$years = mysqli_query($mysqli, "SELECT DISTINCT year FROM digital_files WHERE year != 0 ORDER BY year DESC");

require_once '../includes/header.php';
require_once '../modals/reportmodal.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3"><input type="text" name="search" class="form-control"
                                placeholder="Search files..." value="<?php echo htmlspecialchars($search); ?>"></div>
                        <div class="col-md-2">
                            <select name="course" class="form-select">
                                <option value="">All Courses</option>
                                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                    <option value="<?php echo htmlspecialchars($c['course']); ?>" <?php echo $course == $c['course'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="fileType" class="form-select">
                                <option value="">All File Types</option>
                                <?php while ($f = mysqli_fetch_assoc($file_types)): ?>
                                    <option value="<?php echo htmlspecialchars($f['file_type']); ?>" <?php echo $file_type == $f['file_type'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($f['file_type']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="subject" class="form-select">
                                <option value="">All Subjects</option>
                                <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                    <option value="<?php echo htmlspecialchars($s['subject']); ?>" <?php echo $subject == $s['subject'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['subject']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="year" class="form-select">
                                <option value="">All Years</option>
                                <?php while ($y = mysqli_fetch_assoc($years)): ?>
                                    <option value="<?php echo $y['year']; ?>" <?php echo $year == $y['year'] ? 'selected' : ''; ?>>
                                        <?php echo $y['year']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-1"><button type="submit" class="btn btn-primary w-100"><i
                                    class="fas fa-search"></i></button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-md">
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($file = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-3">
                                    <a href="view.php?id=<?php echo $file['id']; ?>" class="text-decoration-none">
                                        <i
                                            class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($file['title']); ?>
                                    </a>
                                </h5>
                                <div class="text-end">
                                    <span class="badge bg-info me-2"><i
                                            class="fas fa-download me-1"></i><?php echo $file['download_count']; ?></span>
                                    <span class="badge bg-danger me-2"><i
                                            class="fas fa-times-circle me-1"></i><?php echo $file['report_count']; ?></span>
                                    <span class="badge bg-warning"><i
                                            class="fas fa-star me-1"></i><?php echo number_format($file['avg_rating'] ?: 0, 1); ?></span>
                                </div>
                            </div>

                            <p class="card-text"><?php echo nl2br(htmlspecialchars($file['description'])); ?></p>

                            <div class="mb-2">
                                <span class="badge bg-primary me-2"><i
                                        class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($file['subject']); ?></span>
                                <span class="badge bg-secondary me-2"><i
                                        class="fas fa-book me-1"></i><?php echo htmlspecialchars($file['course']); ?></span>
                                <span class="badge bg-success"><i
                                        class="fas fa-calendar me-1"></i><?php echo $file['year']; ?></span>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-file me-1"></i>
                                    <?php echo formatFileSizeMB($file['file_size']); ?>
                                </small>
                            </div>

                            <small class="text-muted">
                                Uploaded by
                                <?php if (isLoggedIn()): ?>
                                    <a
                                        href="../pages/view.php?id=<?php echo $file['uploader_id']; ?>"><?php echo htmlspecialchars($file['uploader_name']); ?></a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($file['uploader_name']); ?>
                                <?php endif; ?>
                                on <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                            </small>
                        </div>


                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <?php if (isLoggedIn()): ?>
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm"><i
                                        class="fas fa-download me-1"></i>Download</a>
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal"
                                    data-content-type="file" data-report-id="<?php echo $file['id']; ?>"
                                    data-report-title="<?php echo htmlspecialchars($file['title']); ?>">
                                    <i class="fas fa-flag me-1"></i>Report
                                </button>
                            <?php else: ?>
                                <a href="../login.php" class="btn btn-warning btn-sm w-100"><i class="fas fa-lock me-1"></i>Login to
                                    Download</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <p class="lead">No files found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>