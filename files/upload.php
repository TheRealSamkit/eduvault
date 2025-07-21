<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

$sidebar = true;
requireLogin();

$max_file_size = 10 * 1024 * 1024;
$allowed_mimes = getAllMimes($mysqli);
$allowed_ext = array_keys($allowed_mimes);
$allowed_mime_types = $allowed_mimes;

$error = '';

$subjects = mysqli_query($mysqli, "SELECT id, name FROM subjects ORDER BY name ASC");
$courses = mysqli_query($mysqli, "SELECT id, name FROM courses ORDER BY name ASC");
$years = mysqli_query($mysqli, "SELECT id, year FROM years ORDER BY year DESC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($mysqli, $_POST['title']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);
    $subject_id = (int) $_POST['subject_id'];
    $course_id = (int) $_POST['course_id'];
    $year_id = (int) $_POST['year_id'];
    $tags = isset($_POST['tags']) ? mysqli_real_escape_string($mysqli, $_POST['tags']) : '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error = "Invalid file format. Allowed: " . strtoupper(implode(', ', $allowed_ext));
        } elseif ($_FILES['file']['size'] > $max_file_size) {
            $error = "File size exceeds 10MB limit";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected_mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
            finfo_close($finfo);

            if ($detected_mime !== $allowed_mime_types[$ext]) {
                $error = "File MIME type does not match the file extension.";
            } else {
                $file_size = $_FILES['file']['size']; // in bytes
                $file_path = '../uploads/files/' . uniqid() . '.' . $ext;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    $content_hash = generateContentHash($file_path);
                    $slug = generateSlug($title, $mysqli);

                    $query = "INSERT INTO digital_files (user_id, slug, title, description, subject_id, course_id, year_id, file_path, file_type, file_size, tags, content_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($mysqli, $query);
                    mysqli_stmt_bind_param($stmt, "isssiiisssss", $user_id, $slug, $title, $description, $subject_id, $course_id, $year_id, $file_path, $ext, $file_size, $tags, $content_hash);

                    if (mysqli_stmt_execute($stmt)) {
                        // Award tokens for successful upload (e.g., 5 tokens per upload)
                        $tokens_to_award = 5;
                        mysqli_query($mysqli, "UPDATE users SET tokens = tokens + $tokens_to_award WHERE id = $user_id");

                        // Send token notification if user wants token notifications
                        if (getUserPreference($user_id, 'notify_tokens', '1', $mysqli) == '1') {
                            $title = "Tokens Earned";
                            $message = "You earned {$tokens_to_award} tokens for uploading!";
                            createNotification($user_id, 'token', $title, $message, null, null, $mysqli);
                        }

                        flash('success', 'File uploaded successfully! You earned ' . $tokens_to_award . ' tokens.');
                        redirect("" . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error = "Upload failed: " . mysqli_error($mysqli);
                        if (file_exists($file_path))
                            unlink($file_path);
                    }
                } else {
                    $error = "Failed to move uploaded file.";
                }
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }

    if (!empty($error)) {
        flash('error', $error);
        redirect("" . $_SERVER['PHP_SELF']);
        exit();
    }
}
require_once '../includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css"
    integrity="sha512-WvVX1YO12zmsvTpUQV8s7ZU98DnkaAokcciMZJfnNWyNzm7//QRV61t4aEr0WdIa4pe854QHLTV302vH92FSMw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"
    integrity="sha512-oQq8uth41D+gIH/NJvSJvVB85MFk1eWpMK6glnkg6I7EdMqC1XVkW7RxLheXwmFdG03qScCM7gKS/Cx3FYt7Tg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div class="d-flex align-items-start">
    <?php include '../includes/sidebar.php'; ?>
    <div class="flex-grow-1 main-content">
        <div class="container-md row justify-content-center gx-1 mb-3">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                        <i class="fas fa-upload fa-lg me-2"></i>
                        <h4 class="mb-0">Upload Study Material</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate
                            id="uploadForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">File <span class="text-danger">*</span></label>
                                    <div class="dropzone dz-clickable rounded-3 border border-2 border-primary bg-e-secondary p-4 mb-3 form-control"
                                        id="fileDropzone">
                                        <div class="dz-message text-center text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                            <span>Drag & drop your file here, or click to select</span><br>
                                            <small>Max size: 10MB. Allowed formats:
                                                <?php echo strtoupper(implode(", ", $allowed_ext)) ?></small>
                                        </div>
                                    </div>
                                    <input type="file" name="file" id="fileInput" class="d-none" required
                                        accept="<?php echo implode(',', array_map(fn($e) => '.' . $e, $allowed_ext)); ?>">
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="title" class="form-control" id="floatingTitle"
                                            placeholder="Title" required>
                                        <label for="floatingTitle">Title</label>
                                        <div class="invalid-feedback">Please enter a title.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <select name="subject_id" class="form-select" id="floatingSubject" required>
                                            <option value="">Select Subject</option>
                                            <?php mysqli_data_seek($subjects, 0);
                                            while ($s = mysqli_fetch_assoc($subjects)): ?>
                                                <option value="<?php echo $s['id']; ?>">
                                                    <?php echo htmlspecialchars($s['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <label for="floatingSubject">Subject</label>
                                        <div class="invalid-feedback">Please select a subject.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <select name="course_id" class="form-select" id="floatingCourse" required>
                                            <option value="">Select Course</option>
                                            <?php mysqli_data_seek($courses, 0);
                                            while ($c = mysqli_fetch_assoc($courses)): ?>
                                                <option value="<?php echo $c['id']; ?>">
                                                    <?php echo htmlspecialchars($c['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <label for="floatingCourse">Course</label>
                                        <div class="invalid-feedback">Please select a course.</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating mb-3">
                                        <select name="year_id" class="form-select" id="floatingYear" required>
                                            <option value="">Select Year</option>
                                            <?php mysqli_data_seek($years, 0);
                                            while ($y = mysqli_fetch_assoc($years)): ?>
                                                <option value="<?php echo $y['id']; ?>">
                                                    <?php echo htmlspecialchars($y['year']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <label for="floatingYear">Year</label>
                                        <div class="invalid-feedback">Please select a year.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea name="description" class="form-control" id="floatingDescription"
                                            placeholder="Description" style="height: 100px" required></textarea>
                                        <label for="floatingDescription">Description</label>
                                        <div class="invalid-feedback">Please enter a description.</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" name="tags" class="form-control" id="floatingTags"
                                            placeholder="Comma-separated tags">
                                        <label for="floatingTags">Tags (comma-separated, e.g. notes, exam, 2025)</label>
                                        <div class="form-text">Optional. Helps others find your file.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                </button>
                                <a href="../dashboard/my_uploads.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to My Uploads
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Dropzone config
    Dropzone.autoDiscover = false;
    const allowedExt = <?php echo json_encode($allowed_ext); ?>;
    const maxFileSizeMB = 10;
    const dz = new Dropzone("body", {
        url: "#", // Prevent auto-upload
        autoProcessQueue: false,
        maxFiles: 1,
        maxFilesize: maxFileSizeMB,
        acceptedFiles: allowedExt.map(e => "." + e).join(","),
        clickable: "#fileDropzone", // Make only the designated area clickable
        previewsContainer: false, // We're handling feedback manually
        init: function () {
            const dzMessage = document.querySelector("#fileDropzone .dz-message");
            const originalMessageHTML = dzMessage.innerHTML;
            const dzElem = document.getElementById('fileDropzone');

            this.on("addedfile", function (file) {
                // Set file to hidden input for form submit
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                }
                // Update UI to show filename and a remove link
                dzMessage.innerHTML = `<i class="fas fa-file-check fa-2x mb-2 text-success"></i><br><span>${file.name}</span><br><small class="text-muted">Click here or drop another file to replace.</small>`;
                dzMessage.querySelector('span').style.cssText = "word-break: break-all; font-weight: bold;";

                const removeLink = document.createElement('a');
                removeLink.href = "#";
                removeLink.innerHTML = "Remove file";
                removeLink.className = "text-danger mt-2 d-block";
                removeLink.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation(); // prevent file dialog from opening
                    this.removeFile(file);
                };
                dzMessage.appendChild(removeLink);
                dzElem.classList.remove('border-danger');
            });

            this.on("removedfile", function () {
                const fileInput = document.getElementById('fileInput');
                if (fileInput) fileInput.value = '';
                dzMessage.innerHTML = originalMessageHTML;
                dzElem.classList.remove('border-danger');
            });

            this.on("maxfilesexceeded", function (file) {
                this.removeAllFiles();
                this.addFile(file);
            });

            this.on("dragenter", function () {
                document.body.classList.add('dz-drag-hover');
            });

            this.on("dragleave", function () {
                document.body.classList.remove('dz-drag-hover');
            });

            this.on("drop", function () {
                document.body.classList.remove('dz-drag-hover');
            });
        }
    });

    // Bootstrap validation
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        var fileInput = document.getElementById('fileInput');
        var dzElem = document.getElementById('fileDropzone');

        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity() || fileInput.files.length === 0) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (fileInput.files.length === 0) {
                        dzElem.classList.add('border-danger');
                    }
                } else {
                    dzElem.classList.remove('border-danger');
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
<?php require_once '../includes/footer.php'; ?>