<?php

function getFileIcon($file_type)
{
    switch ($file_type) {
        case "pdf":
            return "pdf";
        case "docx":
            return "word";
        case "pptx":
            return "powerpoint";
        case "txt":
            return "alt";
        case "jpg":
        case "jpeg":
        case "png":
            return "image";
        default:
            return "invoice";
    }
}
function formatFileSizeMB($bytes)
{
    if ($bytes > 0) {
        return number_format($bytes / 1024 * 1024, 1) . ' MB';
    } else {
        return '0 MB';
    }
}

function getAllMimes($mysqli)
{
    $mimes = [];
    $result = mysqli_query($mysqli, "SELECT extension, mime_types FROM mimes");
    while ($row = mysqli_fetch_assoc($result)) {
        $mimes[$row['extension']] = $row['mime_types'];
    }
    return $mimes;
}

function getImageMimes($mysqli)
{
    $mimes = [];
    $result = mysqli_query($mysqli, "SELECT extension, mime_types FROM mimes WHERE extension IN ('jpg','jpeg','png')");
    while ($row = mysqli_fetch_assoc($result)) {
        $mimes[$row['extension']] = $row['mime_types'];
    }
    return $mimes;
}

function getAllSubjects($mysqli)
{
    $result = mysqli_query($mysqli, "SELECT id, name as subject FROM subjects WHERE name != '' ORDER BY name ASC");
    return $result;
}

function getAllBoards($mysqli)
{
    $result = mysqli_query($mysqli, "SELECT id, name as board FROM boards WHERE name != '' ORDER BY id");
    return $result;
}

function getFileWithStats($mysqli, $file_id)
{
    $query = "SELECT f.*, u.name as uploader_name, u.id as uploader_id, 
        (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count,
        (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id AND content_type = 'file') as report_count,
        (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating
        FROM digital_files f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $file_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $file;
}

function getFilesWithStats($mysqli, $where = "1=1", $params = [], $param_types = "", $offset = 0, $limit = 12, $order = "ORDER BY f.upload_date DESC")
{
    $query = "SELECT f.*, u.name as uploader_name, u.id as uploader_id,
        (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id AND status = 'resolved') as report_count,
        (SELECT COUNT(*) FROM downloads WHERE file_id = f.id) as download_count,
        (SELECT AVG(rating) FROM file_feedback WHERE file_id = f.id) as avg_rating,
        s.name as subject,
        c.name as course,
        y.year
        FROM digital_files f
        JOIN users u ON f.user_id = u.id
        LEFT JOIN subjects s ON f.subject_id = s.id
        LEFT JOIN courses c ON f.course_id = c.id
        LEFT JOIN years y ON f.year_id = y.id
        WHERE $where $order LIMIT ?, ?";

    $stmt = mysqli_prepare($mysqli, $query);
    if (!empty($params)) {
        $param_types .= "ii"; // Add types for LIMIT parameters
        $params[] = $offset;
        $params[] = $limit;
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    } else {
        mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}
function toastBgClass($type)
{
    switch ($type) {
        case 'success':
            return 'success';
        case 'error':
            return 'danger';
        case 'warning':
            return 'warning';
        case 'info':
            return 'info';
        default:
            return 'secondary';
    }
}

function flash(string $type, string $message): void
{
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }

    $_SESSION['toasts'][] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}
?>