<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

$where_conditions = ["b.status = ?"];
$params = ["Available"];
$param_types = "s";

// Pagination settings
$items_per_page = 12; // Number of items per page
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$board = isset($_GET['board']) ? trim($_GET['board']) : '';
$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';

if (!empty($search)) {
    $where_conditions[] = "(b.title LIKE ? OR s.name LIKE ? OR b.location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= "sss";
}
if (!empty($board)) {
    $where_conditions[] = "b.board_id = ?";
    $params[] = $board;
    $param_types .= "i";
}
if (!empty($subject)) {
    $where_conditions[] = "b.subject_id = ?";
    $params[] = $subject;
    $param_types .= "i";
}

$where_clause = implode(' AND ', $where_conditions);

$subjects = getAllSubjects($mysqli);
$boards = getAllBoards($mysqli);

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM book_listings b WHERE $where_clause";
$count_stmt = mysqli_prepare($mysqli, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $param_types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_items = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = ceil($total_items / $items_per_page);

// Get books with user information
$query = "SELECT b.*, u.name as owner_name, u.location as owner_location, s.name as subject, bo.name as board,u.id as owner_id, 
         b.image_path, b.created_at, b.user_id
FROM book_listings b
JOIN users u ON b.user_id = u.id 
JOIN subjects s ON b.subject_id = s.id
JOIN boards bo ON b.board_id = bo.id
WHERE $where_clause
ORDER BY b.created_at DESC
LIMIT ?, ?";

$stmt = mysqli_prepare($mysqli, $query);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($mysqli));
}

$param_types .= "ii"; // Add types for LIMIT parameters
$params[] = $offset;
$params[] = $items_per_page;

if (!mysqli_stmt_bind_param($stmt, $param_types, ...$params)) {
    die("Parameter binding failed: " . mysqli_stmt_error($stmt));
}

if (!mysqli_stmt_execute($stmt)) {
    die("Query execution failed: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die("Getting result failed: " . mysqli_stmt_error($stmt));
}

require_once '../includes/header.php';
require_once '../modals/reportmodal.php';
?>

<div class="row mb-4 container-fluid gx-2">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3" >
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control input-dark" 
                               placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="board" class="form-select input-dark">
                            <option value="">All Boards</option>
                            <?php while ($b = mysqli_fetch_assoc($boards)): ?>
                                <option value="<?php echo htmlspecialchars($b['id']); ?>" 
                                        <?php echo $board == $b['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($b['board']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="subject" class="form-select input-dark">
                            <option value="">All Subjects</option>
                            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                <option value="<?php echo htmlspecialchars($s['id']); ?>"
                                        <?php echo $subject == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['subject']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container-md">  
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($book = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($book['image_path'])):?>
                            <img src="<?php echo $book['image_path']; ?>" class="card-img-top" alt="Book Cover"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <i class="fas fa-book fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-graduation-cap me-1"></i><?php echo htmlspecialchars($book['subject']); ?>
                                    <br>
                                    <i class="fas fa-university me-1"></i><?php echo htmlspecialchars($book['board']); ?>
                                    <br>
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($book['owner_location']); ?>
                                </small>
                            </p>
                        </div>
                                    
                        <div class="card-footer gy-2">
                            <?php if (isLoggedIn()): ?>
                                <a href="view.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle me-1"></i>View Details
                                </a>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal"
                                    data-content-type="book" data-report-id="<?php echo $book['id']; ?>"
                                    data-report-title="<?php echo htmlspecialchars($book['title']); ?>">
                                    <i class="fas fa-flag me-1"></i>Report
                                </button>
                            <?php else: ?>
                                <a href="view.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-info-circle me-1"></i>View Details
                                </a>
                            <?php endif; ?>
                            <small class="float-end text-muted">
                            <?php if (isLoggedIn()): ?>
                                Posted by: <a href="../pages/view.php?id=<?php echo htmlspecialchars($book['owner_id']) ?>">
                                <?php echo htmlspecialchars($book['owner_name']); ?></a>
                            <?php else: ?>
                                Posted by: <?php echo htmlspecialchars($book['owner_name']); ?>
                            <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <p class="lead">No books found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($total_pages > 1): ?>
                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($board) ? '&board=' . urlencode($board) : ''; ?><?php echo !empty($subject) ? '&subject=' . urlencode($subject) : ''; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($board) ? '&board=' . urlencode($board) : ''; ?><?php echo !empty($subject) ? '&subject=' . urlencode($subject) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($board) ? '&board=' . urlencode($board) : ''; ?><?php echo !empty($subject) ? '&subject=' . urlencode($subject) : ''; ?>">
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