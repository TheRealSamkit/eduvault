<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';

// Handle search and filters
$where_conditions = ["b.status = ?"];
$params = ["Available"];
$param_types = "s";

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

// Get unique subjects and boards for filters (no user input, safe)
$subjects = mysqli_query($mysqli, "SELECT DISTINCT name as subject,id FROM subjects WHERE name != ''");
$boards = mysqli_query($mysqli, "SELECT DISTINCT name as board,id FROM boards  WHERE name != '' order by id");

$distance = isset($_GET['distance']) ? (int)$_GET['distance'] : 10; // Default 10km radius

// Get books with user information
if (isLoggedIn()) {
    // Get current user's location
    $user_query = "SELECT location, latitude, longitude FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($mysqli, $user_query);
    mysqli_stmt_bind_param($user_stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user_data = mysqli_fetch_assoc($user_result);
    mysqli_stmt_close($user_stmt);

    if ($user_data['latitude'] && $user_data['longitude']) {
        $query = "SELECT b.*, u.name as owner_name, u.location as owner_location, s.name as subject, bo.name as board, u.id as owner_id,
                  u.latitude as owner_lat, u.longitude as owner_long,
                  (6371 * acos(cos(radians(?)) 
                  * cos(radians(u.latitude)) 
                  * cos(radians(u.longitude) - radians(?)) 
                  + sin(radians(?)) 
                  * sin(radians(u.latitude)))) AS distance
                  FROM book_listings b 
                  JOIN users u ON b.user_id = u.id 
                    JOIN boards bo ON b.board_id = bo.id
                  JOIN subjects s ON b.subject_id = s.id
                  WHERE $where_clause
                  HAVING distance <= ? 
                  ORDER BY distance, b.created_at DESC";
        $stmt = mysqli_prepare($mysqli, $query);
        $full_param_types = "ddd" . $param_types . "i";
        $full_params = array_merge([
            $user_data['latitude'],
            $user_data['longitude'],
            $user_data['latitude'],
        ], $params, [$distance]);
        mysqli_stmt_bind_param($stmt, $full_param_types, ...$full_params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        // fallback to normal query if no lat/long
        $query = "SELECT b.*, u.name as owner_name, u.location as owner_location, s.name as subject, bo.name as board,u.id as owner_id, 
                 b.image_path, b.created_at, b.user_id
        FROM book_listings b
        JOIN users u ON b.user_id = u.id 
        JOIN subjects s ON b.subject_id = s.id
        JOIN boards bo ON b.board_id = bo.id
        WHERE $where_clause
        ORDER BY b.created_at DESC";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }
} else {
    $query = "SELECT b.*, u.name as owner_name, u.location as owner_location, s.name as subject, bo.name as board,u.id as owner_id, 
                 b.image_path, b.created_at, b.user_id
        FROM book_listings b
        JOIN users u ON b.user_id = u.id 
        JOIN subjects s ON b.subject_id = s.id
        JOIN boards bo ON b.board_id = bo.id
        WHERE $where_clause
        ORDER BY b.created_at DESC";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
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
                        <input type="text" name="search" class="form-control bg-dark-body" 
                               placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="board" class="form-select bg-dark-body">
                            <option value="">All Boards</option>
                            <?php while ($b = mysqli_fetch_assoc($boards)): ?>
                                <option value="<?php echo htmlspecialchars($b['id']); ?>" 
                                        <?php echo $board == $b['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($b['board']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="subject" class="form-select bg-dark-body">
                            <option value="">All Subjects</option>
                            <?php while ($s = mysqli_fetch_assoc($subjects)): ?>
                                <option value="<?php echo htmlspecialchars($s['id']); ?>"
                                        <?php echo $subject == $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['subject']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if (isLoggedIn() && $user_data['latitude'] && $user_data['longitude']): ?>
                    <div class="col-md-2">
                        <select name="distance" class="form-select bg-dark-body">
                            <option value="5" <?php echo $distance == 5 ? 'selected' : ''; ?>>Within 5 km</option>
                            <option value="10" <?php echo $distance == 10 ? 'selected' : ''; ?>>Within 10 km</option>
                            <option value="20" <?php echo $distance == 20 ? 'selected' : ''; ?>>Within 20 km</option>
                            <option value="50" <?php echo $distance == 50 ? 'selected' : ''; ?>>Within 50 km</option>
                            <option value="100" <?php echo $distance == 100 ? 'selected' : ''; ?>>Within 100 km</option>
                        </select>
                    </div>
                    <?php endif; ?>
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
                                    <?php if (isset($book['distance'])): ?>
                                        <br>    
                                            <i class="fas fa-route me-1"></i><?php echo round($book['distance'], 1); ?> km away
                                    <?php endif; ?>
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
</div>

<?php require_once '../includes/footer.php'; ?>