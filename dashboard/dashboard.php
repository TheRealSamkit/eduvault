<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/header.php';

requireLogin();

$user_id = $_SESSION['user_id'];

// Get user's books count
$books_query = "SELECT COUNT(*) as book_count FROM book_listings WHERE user_id = $user_id";
$books_result = mysqli_query($mysqli, $books_query);
$books_count = mysqli_fetch_assoc($books_result)['book_count'];

// Get user's files count
$files_query = "SELECT COUNT(*) as file_count FROM digital_files WHERE user_id = $user_id";
$files_result = mysqli_query($mysqli, $files_query);
$files_count = mysqli_fetch_assoc($files_result)['file_count'];

// Get user's total downloads
$downloads_query = "SELECT COUNT(*) as download_count FROM downloads WHERE file_id IN 
                   (SELECT id FROM digital_files WHERE user_id = $user_id)";
$downloads_result = mysqli_query($mysqli, $downloads_query);
$downloads_count = mysqli_fetch_assoc($downloads_result)['download_count'];

// Get user info
$user_query = "SELECT name, email, location FROM users WHERE id = $user_id";
$user_result = mysqli_query($mysqli, $user_query);
$user = mysqli_fetch_assoc($user_result);
?>

<div class="container-md">

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user me-2"></i>Profile</h5>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($user['location']); ?></p>
                    <button onclick="updateLocation()" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-map-marker-alt me-2"></i>Update Location
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-book me-2"></i>My Books</h5>
                            <h2 class="mb-0"><?php echo $books_count; ?></h2>
                            <a href="my_books.php" class="text-white">Manage Books →</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-file-alt me-2"></i>My Files</h5>
                            <h2 class="mb-0"><?php echo $files_count; ?></h2>
                            <a href="my_uploads.php" class="text-white">Manage Files →</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-download me-2"></i>Downloads</h5>
                            <h2 class="mb-0"><?php echo $downloads_count; ?></h2>
                            <span class="small">Total downloads of your files</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-plus-circle me-2"></i>Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="../books/add.php" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>Add New Book
                        </a>
                        <a href="../files/upload.php" class="btn btn-outline-success">
                            <i class="fas fa-upload me-2"></i>Upload Study Material
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Recent Activity</h5>
                    <div class="list-group list-group-flush">
                        <?php
                        $activity_query = "SELECT 'book' as type, title, created_at FROM book_listings 
                                     WHERE user_id = $user_id
                                     UNION
                                     SELECT 'file' as type, title, upload_date FROM digital_files 
                                     WHERE user_id = $user_id
                                     ORDER BY created_at DESC LIMIT 5";
                        $activity_result = mysqli_query($mysqli, $activity_query);

                        while ($activity = mysqli_fetch_assoc($activity_result)) {
                            $icon = $activity['type'] == 'book' ? 'book' : 'file-alt';
                            echo '<div class="list-group-item">';
                            echo '<i class="fas fa-' . $icon . ' me-2"></i>';
                            echo htmlspecialchars($activity['title']);
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                // Send to server using AJAX
                fetch('update_location.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `latitude=${latitude}&longitude=${longitude}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Location updated successfully!');
                            location.reload();
                        }
                    });
            });
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>