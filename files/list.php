<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Handle search and filters
$where_conditions = ["1=1"];
$params = [];
$param_types = "";

// Only show verified files
$where_conditions[] = "f.verified = 1";

// Pagination settings
$items_per_page = 12; // Number of items per page
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$course = isset($_GET['course']) ? trim($_GET['course']) : '';
$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$file_type = isset($_GET['fileType']) ? trim($_GET['fileType']) : '';

if (!empty($search)) {
    $where_conditions[] = "(f.title LIKE ? OR f.description LIKE ? OR f.subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "sss";
}
if (!empty($course)) {
    $where_conditions[] = "f.course = ?";
    $params[] = $course;
    $param_types .= "s";
}
if (!empty($subject)) {
    $where_conditions[] = "f.subject = ?";
    $params[] = $subject;
    $param_types .= "s";
}
if (!empty($year)) {
    $where_conditions[] = "f.year = ?";
    $params[] = $year;
    $param_types .= "s";
}
if (!empty($file_type)) {
    $where_conditions[] = "f.file_type = ?";
    $params[] = $file_type;
    $param_types .= "s";
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM digital_files f WHERE $where_clause";
$count_stmt = mysqli_prepare($mysqli, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_items = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = ceil($total_items / $items_per_page);

// Modify the main query to include pagination
$result = getFilesWithStats($mysqli, $where_clause, $params, $param_types, $offset, $items_per_page);

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
                        <div class="col-md-3"><input type="text" name="search" class="form-control bg-dark-body"
                                placeholder="Search files..." value="<?php echo htmlspecialchars($search); ?>"></div>
                        <div class="col-md-2">
                            <select name="course" class="form-select bg-dark-body">
                                <option value="">All Courses</option>
                                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                                    <option value="<?php echo htmlspecialchars($c['course']); ?>" <?php echo $course == $c['course'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['course']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="fileType" class="form-select bg-dark-body">
                                <option value="">All File Types</option>
                                <?php while ($f = mysqli_fetch_assoc($file_types)): ?>
                                    <option value="<?php echo htmlspecialchars($f['file_type']); ?>" <?php echo $file_type == $f['file_type'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($f['file_type']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="subject" class="form-select bg-dark-body">
                                <option value="">All Subjects</option>
                                <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                    <option value="<?php echo htmlspecialchars($s['subject']); ?>" <?php echo $subject == $s['subject'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['subject']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="year" class="form-select bg-dark-body">
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


                        <div class="card-footer  d-flex justify-content-between align-items-center">
                            <?php if (isLoggedIn()): ?>
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm"><i
                                        class="fas fa-download me-1"></i>Download</a>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal"
                                    data-content-type="file" data-report-id="<?php echo $file['id']; ?>"
                                    data-report-title="<?php echo htmlspecialchars($file['title']); ?>">
                                    <i class="fas fa-flag me-1"></i>Report
                                </button>
                            <?php else: ?>
                                <a href="../login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                    class="btn btn-warning btn-sm w-100"><i class="fas fa-lock me-1"></i>Login to
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
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($total_pages > 1): ?>
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($course) ? '&course=' . urlencode($course) : ''; ?><?php echo !empty($subject) ? '&subject=' . urlencode($subject) : ''; ?><?php echo !empty($year) ? '&year=' . urlencode($year) : ''; ?><?php echo !empty($file_type) ? '&fileType=' . urlencode($file_type) : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($course) ? '&course=' . urlencode($course) : ''; ?><?php echo !empty($subject) ? '&subject=' . urlencode($subject) : ''; ?><?php echo !empty($year) ? '&year=' . urlencode($year) : ''; ?><?php echo !empty($file_type) ? '&fileType=' . urlencode($file_type) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($course) ? '&course=' . urlencode($course) : ''; ?><?php echo !empty($subject) ? '&subject=' . urlencode($subject) : ''; ?><?php echo !empty($year) ? '&year=' . urlencode($year) : ''; ?><?php echo !empty($file_type) ? '&fileType=' . urlencode($file_type) : ''; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>