<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireLogin();

if (!isset($_GET['file'])) {
    flash('error', 'No file specified.');
    die('<div class="alert alert-danger m-4">No file specified.</div>');
}
$file_path = $_GET['file'];
$file_path = str_replace('..', '/eduvault', $file_path); // Sanitize
$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    flash('error', 'Only PDF files can be previewed here.');
    die('<div class="alert alert-danger m-4">Only PDF files can be previewed here.</div>');
}
// Optionally, check if file exists on server (if using local files)
// $real_path = realpath(__DIR__ . '/../' . ltrim($file_path, '/'));
// if (!$real_path || !file_exists($real_path)) {
//     die('<div class="alert alert-danger m-4">File not found.</div>');
// }
require_once '../includes/header.php';
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!empty($slug)) {
    // Fetch file id for token check
    $query = "SELECT id FROM digital_files WHERE slug = ? LIMIT 1";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($file && isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        if (!checkAndConsumeToken($user_id, $file['id'], $mysqli)) {
            flash('error', 'You do not have enough tokens to preview this file. Upload files to earn more tokens!');
            redirect('../dashboard/dashboard.php');
            exit();
        }
    }
}
?>
<div class="container py-4">
    <h3 class="mb-4"><i class="fas fa-file-pdf text-danger me-2"></i>PDF Preview</h3>
    <div id="pdfViewer"
        style="min-height:70vh; background:#f8f9fa; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05);"></div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.pdfjsLib) {
            document.getElementById('pdfViewer').innerHTML = '<div class="text-danger text-center">PDF.js library not loaded.</div>';
            return;
        }
        pdfjsLib.GlobalWorkerOptions.workerSrc = '/eduvault/pdfjs/build/pdf.worker.js';
        var url = '<?php echo addslashes($file_path); ?>';
        var container = document.getElementById('pdfViewer');
        pdfjsLib.getDocument(url).promise.then(function (pdf) {
            container.innerHTML = '';
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                pdf.getPage(pageNum).then(function (page) {
                    var viewport = page.getViewport({ scale: 1.2 });
                    var canvas = document.createElement('canvas');
                    canvas.className = 'mb-3 shadow-sm';
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    container.appendChild(canvas);
                    var renderContext = {
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport
                    };
                    page.render(renderContext);
                });
            }
        }).catch(function () {
            container.innerHTML = '<div class="text-danger text-center">Failed to load PDF preview.</div>';
        });
    });
</script>
<?php require_once '../includes/footer.php'; ?>