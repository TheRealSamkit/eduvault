<?php
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload for FPDF

$sidebar = true;
requireLogin();

$max_file_size = 10 * 1024 * 1024;
$allowed_mimes = getAllMimes($mysqli);
$allowed_ext = array_keys($allowed_mimes);
$allowed_mime_types = $allowed_mimes;

$allowed_image_ext = ['jpg', 'jpeg', 'png'];

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

    // Multi-image upload logic
    if (isset($_FILES['file'])) {
        $files = $_FILES['file'];
        $file_count = is_array($files['name']) ? count($files['name']) : 1;
        $is_multi_image = $file_count > 1;
        $image_files = [];
        $valid_images = true;
        if ($is_multi_image) {
            if ($file_count > 15) {
                $error = "You can upload a maximum of 15 images.";
            } else {
                for ($i = 0; $i < $file_count; $i++) {
                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed_image_ext)) {
                        $valid_images = false;
                        $error = "Only JPEG and PNG images are allowed for multi-image upload.";
                        break;
                    }
                    $image_files[] = [
                        'tmp_name' => $files['tmp_name'][$i],
                        'ext' => $ext
                    ];
                }
            }
        }
        if ($is_multi_image && $valid_images && empty($error)) {
            // Merge images into PDF
            $pdf = new \FPDF();
            foreach ($image_files as $img) {
                $img_info = getimagesize($img['tmp_name']);
                if ($img_info === false)
                    continue;
                $w = $img_info[0];
                $h = $img_info[1];
                // Convert px to mm (A4 max size)
                $w_mm = $w * 0.264583;
                $h_mm = $h * 0.264583;
                // Copy to temp file with correct extension
                $tmpPath = sys_get_temp_dir() . '/' . uniqid('img_', true) . '.' . $img['ext'];
                copy($img['tmp_name'], $tmpPath);
                $pdf->AddPage('P', [$w_mm, $h_mm]);
                $pdf->Image($tmpPath, 0, 0, $w_mm, $h_mm);
                @unlink($tmpPath);
            }
            $file_path = '../uploads/files/' . uniqid() . '.pdf';
            $pdf->Output('F', $file_path);
            $ext = 'pdf';
            $file_size = filesize($file_path);
            $content_hash = generateContentHash($file_path);
            $slug = generateSlug($title, $mysqli);
            $query = "INSERT INTO digital_files (user_id, slug, title, description, subject_id, course_id, year_id, file_path, file_type, file_size, tags, content_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, "isssiiisssss", $user_id, $slug, $title, $description, $subject_id, $course_id, $year_id, $file_path, $ext, $file_size, $tags, $content_hash);
            if (mysqli_stmt_execute($stmt)) {
                // Consume 5 tokens for this operation
                mysqli_query($mysqli, "UPDATE users SET tokens = GREATEST(tokens - 5, 0) WHERE id = $user_id");
                if (getUserPreference($user_id, 'notify_tokens', '1', $mysqli) == '1') {
                    $title = "Tokens Used";
                    $message = "You used 5 tokens to merge images into a PDF.";
                    createNotification($user_id, 'token', $title, $message, null, null, $mysqli);
                }
                flash('success', 'Images merged and uploaded as PDF! 5 tokens consumed.');
                redirect("" . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Upload failed: " . mysqli_error($mysqli);
                if (file_exists($file_path))
                    unlink($file_path);
            }
        } else if (!$is_multi_image) {
            // Single file upload (existing logic, but only allow image or allowed ext)
            $ext = strtolower(pathinfo($files['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) {
                $error = "Invalid file format. Allowed: " . strtoupper(implode(', ', $allowed_ext));
            } elseif ($files['size'] > $max_file_size) {
                $error = "File size exceeds 10MB limit";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected_mime = finfo_file($finfo, $files['tmp_name']);
                finfo_close($finfo);
                if ($detected_mime !== $allowed_mime_types[$ext]) {
                    $error = "File MIME type does not match the file extension.";
                } else {
                    $file_size = $files['size'];
                    $file_path = '../uploads/files/' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($files['tmp_name'], $file_path)) {
                        $content_hash = generateContentHash($file_path);
                        $slug = generateSlug($title, $mysqli);
                        $query = "INSERT INTO digital_files (user_id, slug, title, description, subject_id, course_id, year_id, file_path, file_type, file_size, tags, content_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($mysqli, $query);
                        mysqli_stmt_bind_param($stmt, "isssiiisssss", $user_id, $slug, $title, $description, $subject_id, $course_id, $year_id, $file_path, $ext, $file_size, $tags, $content_hash);
                        if (mysqli_stmt_execute($stmt)) {
                            // Consume 5 tokens for this operation
                            mysqli_query($mysqli, "UPDATE users SET tokens = GREATEST(tokens - 5, 0) WHERE id = $user_id");
                            if (getUserPreference($user_id, 'notify_tokens', '1', $mysqli) == '1') {
                                $title = "Tokens Used";
                                $message = "You used 5 tokens to upload a file.";
                                createNotification($user_id, 'token', $title, $message, null, null, $mysqli);
                            }
                            flash('success', 'File uploaded successfully! 5 tokens consumed.');
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
        <div class="container-md">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-upload fa-lg text-primary"></i>
                <h2 class="mb-0">Upload Study Material</h2>
            </div>
            <div class="card shadow mb-4">
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
                                <input type="file" name="file[]" id="fileInput" class="d-none" required
                                    accept="<?php echo implode(',', array_map(fn($e) => '.' . $e, $allowed_ext)); ?>"
                                    multiple>
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
<script>
    // Dropzone config
    Dropzone.autoDiscover = false;
    const allowedExt = <?php echo json_encode($allowed_ext); ?>;
    const imageExt = ['jpg', 'jpeg', 'png'];
    const maxFileSizeMB = 10;
    let dz = new Dropzone("body", {
        url: "#", // Prevent auto-upload
        autoProcessQueue: false,
        maxFiles: 15,
        maxFilesize: maxFileSizeMB,
        acceptedFiles: allowedExt.map(e => "." + e).join(","),
        clickable: "#fileDropzone", // Make only the designated area clickable
        previewsContainer: false, // We're handling feedback manually
        init: function () {
            const dzMessage = document.querySelector("#fileDropzone .dz-message");
            const originalMessageHTML = dzMessage.innerHTML;
            const dzElem = document.getElementById('fileDropzone');
            this.on("addedfile", function (file) {
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    const dt = new DataTransfer();
                    for (let i = 0; i < this.files.length; i++) {
                        dt.items.add(this.files[i]);
                    }
                    fileInput.files = dt.files;
                }
                // If all files are images, allow up to 15, else only 1
                const allImages = Array.from(this.files).every(f => imageExt.includes(f.name.split('.').pop().toLowerCase()));
                if (allImages) {
                    this.options.maxFiles = 15;
                } else {
                    this.options.maxFiles = 1;
                    if (this.files.length > 1) {
                        // Remove extra files
                        while (this.files.length > 1) {
                            this.removeFile(this.files[this.files.length - 1]);
                        }
                    }
                }
                dzMessage.innerHTML = `<i class=\"fas fa-file-check fa-2x mb-2 text-success\"></i><br><span>${this.files.length} file(s) selected</span><br><small class=\"text-muted\">${allImages ? 'You can upload up to 15 images.' : 'Only one non-image file allowed.'}</small>`;
                dzMessage.querySelector('span').style.cssText = "word-break: break-all; font-weight: bold;";
                const removeLink = document.createElement('a');
                removeLink.href = "#";
                removeLink.innerHTML = "Remove all files";
                removeLink.className = "text-danger mt-2 d-block";
                removeLink.onclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.removeAllFiles();
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