<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

// Handle search and filters
$where_conditions = ["b.status = 'Available'"]; // Only show available books
$search = isset($_GET['search']) ? mysqli_real_escape_string($mysqli, $_GET['search']) : '';
$board = isset($_GET['board']) ? mysqli_real_escape_string($mysqli, $_GET['board']) : '';
$subject = isset($_GET['subject']) ? mysqli_real_escape_string($mysqli, $_GET['subject']) : '';

if (!empty($search)) {
    $where_conditions[] = "(title LIKE '%$search%' OR subject LIKE '%$search%' OR location LIKE '%$search%')";
}
if (!empty($board)) {
    $where_conditions[] = "board = '$board'";
}
if (!empty($subject)) {
    $where_conditions[] = "subject = '$subject'";
}

$where_clause = implode(' AND ', $where_conditions);

// Get books with user information
$query = "SELECT b.*, u.name as owner_name, u.location as owner_location 
          FROM book_listings b 
          JOIN users u ON b.user_id = u.id 
          WHERE $where_clause 
          ORDER BY b.created_at DESC";
$result = mysqli_query($mysqli, $query);

// Get unique subjects and boards for filters
$subjects = mysqli_query($mysqli, "SELECT DISTINCT subject FROM book_listings WHERE subject != ''");
$boards = mysqli_query($mysqli, "SELECT DISTINCT board FROM book_listings WHERE board != ''");


// After existing filter conditions
$distance = isset($_GET['distance']) ? (int)$_GET['distance'] : 10; // Default 10km radius

// Modify the query to calculate distance and filter by it
if (isLoggedIn()) {
    // Get current user's location
    $user_query = "SELECT location, latitude, longitude FROM users WHERE id = " . $_SESSION['user_id'];
    $user_result = mysqli_query($mysqli, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);

    if ($user_data['latitude'] && $user_data['longitude']) {
        $query = "SELECT b.*, u.name as owner_name, u.location as owner_location,
                  u.latitude as owner_lat, u.longitude as owner_long,
                  (6371 * acos(cos(radians({$user_data['latitude']})) 
                  * cos(radians(u.latitude)) 
                  * cos(radians(u.longitude) - radians({$user_data['longitude']})) 
                  + sin(radians({$user_data['latitude']})) 
                  * sin(radians(u.latitude)))) AS distance
                  FROM book_listings b 
                  JOIN users u ON b.user_id = u.id 
                  WHERE $where_clause
                  HAVING distance <= $distance 
                  ORDER BY distance, b.created_at DESC";
    }
}

// Update the search form to include distance filter
?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <!-- Existing search fields -->
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="board" class="form-select">
                            <option value="">All Boards</option>
                            <?php while ($b = mysqli_fetch_assoc($boards)): ?>
                                <option value="<?php echo htmlspecialchars($b['board']); ?>" 
                                        <?php echo $board == $b['board'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($b['board']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                    <!-- Add distance filter -->
                    <?php if (isLoggedIn() && $user_data['latitude'] && $user_data['longitude']): ?>
                    <div class="col-md-2">
                        <select name="distance" class="form-select">
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
                        <?php if (!empty($book['image_path'])): ?>
                            <img src="<?php echo $book['image_path']; ?>" class="card-img-top" alt="Book Cover"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
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
                                    
                        <div class="card-footer bg-white">
                            <a href="view.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-info-circle me-1"></i>View Details
                            </a>
                            <small class="float-end text-muted">
                                Posted by: <?php echo htmlspecialchars($book['owner_name']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-books fa-3x text-muted mb-3"></i>
                <p class="lead">No books found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>