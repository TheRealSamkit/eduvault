<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Handle search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$subject_id = isset($_GET['subject']) ? (int) $_GET['subject'] : 0;
$course_id = isset($_GET['course']) ? (int) $_GET['course'] : 0;
$year_id = isset($_GET['year']) ? (int) $_GET['year'] : 0;
$file_type = isset($_GET['fileType']) ? trim($_GET['fileType']) : '';
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'recent';
$tags = isset($_GET['tags']) ? trim($_GET['tags']) : '';

// Pagination settings
$items_per_page = 12;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Build filters array
$filters = [];
if (!empty($subject_id))
    $filters['subject_id'] = $subject_id;
if (!empty($course_id))
    $filters['course_id'] = $course_id;
if (!empty($year_id))
    $filters['year_id'] = $year_id;
if (!empty($file_type))
    $filters['file_type'] = $file_type;
if (!empty($tags))
    $filters['tags'] = $tags;

// Use the enhanced search function
$result = searchFiles($mysqli, $search, $filters, $sort_by, $items_per_page, $offset);
$total_items = getSearchCount($mysqli, $search, $filters);
$total_pages = ceil($total_items / $items_per_page);

// Get filter options using functions
$file_types = mysqli_query($mysqli, "SELECT DISTINCT file_type FROM digital_files WHERE file_type != '' AND status = 'active'");
$subjects = getAllSubjects($mysqli);
$courses = getAllCourses($mysqli);
$years = getAllYears($mysqli);

// Log search analytics if user is logged in
if (isLoggedIn() && (!empty($search) || !empty($filters))) {
    logSearchAnalytics($mysqli, $_SESSION['user_id'], $search, $filters, $total_items, $_SERVER['REMOTE_ADDR']);
}

// Update search suggestion popularity
if (!empty($search)) {
    updateSearchSuggestionPopularity($mysqli, $search);
}

$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

require_once '../includes/header.php';
require_once '../modals/reportmodal.php';
?>
<div class="container-md py-3 mx-lg-5">
    <div class="row">
        <nav class="col-lg-3 d-none d-lg-block">
            <div class="bg-body-tertiary border rounded-2 p-3 position-sticky sidebar-filters" style="top:90px;">
                <form method="GET" action="" id="sidebar-filters-form">
                    <h6 class="mb-3 text-primary"><i class="fas fa-filter me-2"></i>Filters</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Course</label>
                        <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Courses</option>
                            <?php mysqli_data_seek($courses, 0);
                            while ($c = mysqli_fetch_assoc($courses)): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $course_id == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['course']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Subject</label>
                        <select name="subject" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Subjects</option>
                            <?php mysqli_data_seek($subjects, 0);
                            while ($s = mysqli_fetch_assoc($subjects)): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo $subject_id == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['subject']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Year</label>
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <?php mysqli_data_seek($years, 0);
                            while ($y = mysqli_fetch_assoc($years)): ?>
                                <option value="<?php echo $y['id']; ?>" <?php echo $year_id == $y['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($y['year']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">File Type</label>
                        <select name="fileType" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <?php mysqli_data_seek($file_types, 0);
                            while ($f = mysqli_fetch_assoc($file_types)): ?>
                                <option value="<?php echo htmlspecialchars($f['file_type']); ?>" <?php echo $file_type == $f['file_type'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(strtoupper($f['file_type'])); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Sort By</label>
                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="recent" <?php echo $sort_by == 'recent' ? 'selected' : ''; ?>>Most Recent
                            </option>
                            <option value="popularity" <?php echo $sort_by == 'popularity' ? 'selected' : ''; ?>>Most
                                Popular</option>
                            <option value="rating" <?php echo $sort_by == 'rating' ? 'selected' : ''; ?>>Highest Rated
                            </option>
                            <option value="size" <?php echo $sort_by == 'size' ? 'selected' : ''; ?>>Largest Files
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Tags</label>
                        <input type="text" name="tags" class="form-control form-control-sm" placeholder="Enter tags..."
                            value="<?php echo htmlspecialchars($tags); ?>">
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100 mb-2"><i
                            class="fas fa-filter me-1"></i>Apply Filters</button>
                    <a href="list.php" class="btn btn-outline-secondary btn-sm w-100"><i
                            class="fas fa-times me-1"></i>Clear</a>
                </form>
            </div>
        </nav>
        <!-- Offcanvas Filters (Mobile) -->
        <div class="d-lg-none mb-3">
            <button class="btn btn-outline-primary w-100" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobileFilters" aria-controls="mobileFilters">
                <i class="fas fa-filter me-1"></i>Filters
            </button>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileFilters"
                aria-labelledby="mobileFiltersLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="mobileFiltersLabel"><i class="fas fa-filter me-2"></i>Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <form method="GET" action="" id="mobile-filters-form">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Course</label>
                            <select name="course" class="form-select form-select-sm">
                                <option value="">All Courses</option>
                                <?php mysqli_data_seek($courses, 0);
                                while ($c = mysqli_fetch_assoc($courses)): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $course_id == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['course']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Subject</label>
                            <select name="subject" class="form-select form-select-sm">
                                <option value="">All Subjects</option>
                                <?php mysqli_data_seek($subjects, 0);
                                while ($s = mysqli_fetch_assoc($subjects)): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo $subject_id == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['subject']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Year</label>
                            <select name="year" class="form-select form-select-sm">
                                <option value="">All Years</option>
                                <?php mysqli_data_seek($years, 0);
                                while ($y = mysqli_fetch_assoc($years)): ?>
                                    <option value="<?php echo $y['id']; ?>" <?php echo $year_id == $y['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($y['year']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">File Type</label>
                            <select name="fileType" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <?php mysqli_data_seek($file_types, 0);
                                while ($f = mysqli_fetch_assoc($file_types)): ?>
                                    <option value="<?php echo htmlspecialchars($f['file_type']); ?>" <?php echo $file_type == $f['file_type'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(strtoupper($f['file_type'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Sort By</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="recent" <?php echo $sort_by == 'recent' ? 'selected' : ''; ?>>Most Recent
                                </option>
                                <option value="popularity" <?php echo $sort_by == 'popularity' ? 'selected' : ''; ?>>Most
                                    Popular</option>
                                <option value="rating" <?php echo $sort_by == 'rating' ? 'selected' : ''; ?>>Highest Rated
                                </option>
                                <option value="size" <?php echo $sort_by == 'size' ? 'selected' : ''; ?>>Largest Files
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Tags</label>
                            <input type="text" name="tags" class="form-control form-control-sm"
                                placeholder="Enter tags..." value="<?php echo htmlspecialchars($tags); ?>">
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 mb-2"><i
                                class="fas fa-filter me-1"></i>Apply Filters</button>
                        <a href="list.php" class="btn btn-outline-secondary btn-sm w-100"><i
                                class="fas fa-times me-1"></i>Clear</a>
                    </form>
                </div>
            </div>
        </div>
        <main class="col-lg-9">
            <form method="GET" action="" class="mb-3">
                <div class="input-group input-group-lg">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search files, descriptions, tags..."
                        value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i>Search</button>
                </div>
            </form>
            <div class="bg-subtle rounded-3 p-3 mb-4 border">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h6 class="mb-1">Search Results</h6>
                        <small class="text-muted">
                            Showing
                            <?php echo min($offset + 1, $total_items); ?>-<?php echo min($offset + $items_per_page, $total_items); ?>
                            of <?php echo number_format($total_items); ?> results
                        </small>
                    </div>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <?php if (!empty($search)): ?>
                            <span class="badge bg-primary">Search: "<?php echo htmlspecialchars($search); ?>"</span>
                        <?php endif; ?>
                        <?php if (!empty($filters)): ?>
                            <span class="badge bg-secondary"><?php echo count($filters); ?> filter(s) applied</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($file = mysqli_fetch_assoc($result)): ?>
                        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex align-items-stretch">
                            <div class="card file-card h-100 w-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 text-truncate">
                                            <a href="view.php?id=<?php echo $file['id']; ?>"
                                                class="text-decoration-none color fs-4"
                                                aria-label="View details for <?php echo htmlspecialchars($file['title']); ?>">
                                                <i class="fas fa-file-<?php echo getFileIcon(strtolower($file['file_type'])); ?> file-icon text-primary"
                                                    aria-hidden="true"></i>
                                                <?php echo htmlspecialchars($file['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown"
                                                aria-label="File actions for <?php echo htmlspecialchars($file['title']); ?>">
                                                <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="view.php?id=<?php echo $file['id']; ?>"
                                                        aria-label="View details for <?php echo htmlspecialchars($file['title']); ?>">
                                                        <i class="fas fa-eye me-2" aria-hidden="true"></i>View Details
                                                    </a></li>
                                                <?php if (isLoggedIn()): ?>
                                                    <li><a class="dropdown-item" href="download.php?id=<?php echo $file['id']; ?>"
                                                            aria-label="Download <?php echo htmlspecialchars($file['title']); ?>">
                                                            <i class="fas fa-download me-2" aria-hidden="true"></i>Download
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                                            data-bs-target="#exampleModal" data-content-type="file"
                                                            data-report-id="<?php echo $file['id']; ?>"
                                                            data-report-title="<?php echo htmlspecialchars($file['title']); ?>"
                                                            aria-label="Report <?php echo htmlspecialchars($file['title']); ?>">
                                                            <i class="fas fa-flag me-2" aria-hidden="true"></i>Report
                                                        </a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted small mb-2 text-truncate" style="max-width: 100%;">
                                        <?php echo htmlspecialchars(substr($file['description'], 0, 120)); ?>
                                        <?php if (strlen($file['description']) > 120): ?>...<?php endif; ?>
                                    </p>
                                    <div class="stats-row d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge bg-info stats-badge">
                                                <i class="fas fa-download me-1"
                                                    aria-hidden="true"></i><?php echo number_format($file['download_count']); ?>
                                            </span>
                                            <span class="badge bg-warning stats-badge">
                                                <i class="fas fa-star me-1"
                                                    aria-hidden="true"></i><?php echo number_format($file['avg_rating'] ?: 0, 1); ?>
                                            </span>
                                            <span class="badge bg-light text-dark stats-badge">
                                                <i class="fas fa-hdd me-1"
                                                    aria-hidden="true"></i><?php echo formatFileSize($file['file_size']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <?php if (!empty($file['subject_name'])): ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-book me-1"
                                                    aria-hidden="true"></i><?php echo htmlspecialchars($file['subject_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($file['course_name'])): ?>
                                            <span class="badge bg-secondary text-wrap">
                                                <i class="fas fa-graduation-cap me-1"
                                                    aria-hidden="true"></i><?php echo htmlspecialchars($file['course_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($file['year_name'])): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-calendar me-1"
                                                    aria-hidden="true"></i><?php echo $file['year_name']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($file['tags'])): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tags me-1" aria-hidden="true"></i>
                                                <?php
                                                $tags = explode(',', $file['tags']);
                                                foreach (array_slice($tags, 0, 3) as $tag):
                                                    $tag = trim($tag);
                                                    if (!empty($tag)):
                                                        ?>
                                                        <span
                                                            class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($tag); ?></span>
                                                        <?php
                                                    endif;
                                                endforeach;
                                                if (count($tags) > 3): ?>
                                                    <span class="text-muted">+<?php echo count($tags) - 3; ?> more</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    <div class="border-top pt-2">
                                        <small class="text-muted">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    By <?php echo htmlspecialchars($file['uploader_name']); ?>
                                                </span>
                                                <span>
                                                    <?php echo date('M d, Y', strtotime($file['upload_date'])); ?>
                                                </span>
                                            </div>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 d-flex flex-column flex-md-row gap-2">
                                    <?php if (isLoggedIn()): ?>
                                        <?php if (strtolower($file['file_type']) === 'pdf'): ?>
                                            <a href="/eduvault/pdfjs/web/viewer.html?file=<?php echo urlencode($host . '/eduvault/files/pdf_proxy.php?id=' . $file['id']); ?>"
                                                target="_blank" class="btn btn-outline-secondary btn-sm flex-fill"
                                                title="Full Page PDF Preview">
                                                <i class="fas fa-eye me-1"></i>Full Preview
                                            </a>
                                        <?php elseif (in_array(strtolower($file['file_type']), ['txt', 'csv', 'md'])): ?>
                                            <a href="txt_preview.php?id=<?php echo $file['id']; ?>" target="_blank"
                                                class="btn btn-outline-secondary btn-sm flex-fill" title="Full Page Text Preview">
                                                <i class="fas fa-eye me-1"></i>Full Preview
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-preview-file"
                                                data-file-id="<?php echo $file['id']; ?>"
                                                data-file-type="<?php echo strtolower($file['file_type']); ?>"
                                                data-file-title="<?php echo htmlspecialchars($file['title']); ?>"
                                                data-file-path="<?php echo str_replace('..', '/eduvault', $file['file_path']); ?>">
                                                <i class="fas fa-eye me-1"></i>Preview
                                            </button>
                                        <?php endif; ?>
                                        <a href="download.php?id=<?php echo $file['id']; ?>"
                                            class="btn btn-success btn-sm flex-fill"
                                            aria-label="Download <?php echo htmlspecialchars($file['title']); ?>">
                                            <i class="fas fa-download me-1" aria-hidden="true"></i>Download
                                        </a>
                                        <a href="view.php?id=<?php echo $file['id']; ?>"
                                            class="btn btn-outline-primary btn-sm flex-fill"
                                            aria-label="View details for <?php echo htmlspecialchars($file['title']); ?>">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="../auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                            class="btn btn-warning btn-sm w-100"
                                            aria-label="Login to download <?php echo htmlspecialchars($file['title']); ?>">
                                            <i class="fas fa-lock me-1" aria-hidden="true"></i>Login to Download
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="no-results">
                            <img src="/eduvault/assets/svg/no_files.svg" alt="No files found" class="img-fluid">
                            <h5 class="mt-3">No files found</h5>
                            <p class="text-muted">Try adjusting your search criteria or browse all files.</p>
                            <a href="list.php" class="btn btn-primary">
                                <i class="fas fa-search me-1" aria-hidden="true"></i>Browse All Files
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&course=<?php echo $course_id; ?>&subject=<?php echo $subject_id; ?>&year=<?php echo $year_id; ?>&fileType=<?php echo urlencode($file_type); ?>&sort=<?php echo $sort_by; ?>&tags=<?php echo urlencode($tags); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=1&search=<?php echo urlencode($search); ?>&course=<?php echo $course_id; ?>&subject=<?php echo $subject_id; ?>&year=<?php echo $year_id; ?>&fileType=<?php echo urlencode($file_type); ?>&sort=<?php echo $sort_by; ?>&tags=<?php echo urlencode($tags); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&course=<?php echo $course_id; ?>&subject=<?php echo $subject_id; ?>&year=<?php echo $year_id; ?>&fileType=<?php echo urlencode($file_type); ?>&sort=<?php echo $sort_by; ?>&tags=<?php echo urlencode($tags); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&course=<?php echo $course_id; ?>&subject=<?php echo $subject_id; ?>&year=<?php echo $year_id; ?>&fileType=<?php echo urlencode($file_type); ?>&sort=<?php echo $sort_by; ?>&tags=<?php echo urlencode($tags); ?>">
                                        <?php echo $total_pages; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                    href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&course=<?php echo $course_id; ?>&subject=<?php echo $subject_id; ?>&year=<?php echo $year_id; ?>&fileType=<?php echo urlencode($file_type); ?>&sort=<?php echo $sort_by; ?>&tags=<?php echo urlencode($tags); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../modals/filePreviewModal.php'; ?>
<?php require_once '../includes/footer.php'; ?>