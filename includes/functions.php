<?php

function getFileIcon($file_type)
{
    switch ($file_type) {
        case "pdf":
            return "pdf text-danger";
        case "docx":
            return "word";
        case "pptx":
            return "powerpoint";
        case "txt":
            return "alt text-warning";
        case "jpg":
        case "jpeg":
        case "png":
            return "image text-info";
        default:
            return "invoice";
    }
}

function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
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

function getAllYears($mysqli)
{
    $result = mysqli_query($mysqli, "SELECT id, year FROM years ORDER BY year DESC");
    return $result;
}

function getAllCourses($mysqli)
{
    $result = mysqli_query($mysqli, "SELECT id, name as course FROM courses WHERE name != '' ORDER BY name ASC");
    return $result;
}

function getCount($mysqli, $table, $alias, $id)
{
    $query = "SELECT COUNT(*) as $alias FROM $table WHERE user_id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($result)[$alias];
    mysqli_stmt_close($stmt);
    return $count;
}

function getFileWithStats($mysqli, $file_id): array|bool|null
{
    $query = "SELECT f.*, u.name as uploader_name, u.id as uploader_id, u.avatar_path as uploader_avatar,
        s.name as subject_name, c.name as course_name, y.year as year_name,
        COALESCE(f.download_count, 0) as download_count,
        (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id AND content_type = 'file') as report_count,
        COALESCE(f.average_rating, 0) as avg_rating
        FROM digital_files f 
        JOIN users u ON f.user_id = u.id 
        LEFT JOIN subjects s ON f.subject_id = s.id
        LEFT JOIN courses c ON f.course_id = c.id
        LEFT JOIN years y ON f.year_id = y.id
        WHERE f.id = ? AND f.status = 'active' AND f.visibility = 'public'";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $file_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $file;
}

// Enhanced search function with full-text search and better performance
function searchFiles($mysqli, $search_query = '', $filters = [], $sort_by = 'relevance', $limit = 15, $offset = 0)
{
    $where_conditions = ["f.status = 'active'", "f.visibility = 'public'", "f.verified = 1"];
    $params = [];
    $param_types = "";
    $select_fields = "f.*, u.name as uploader_name, u.id as uploader_id, u.avatar_path as uploader_avatar,
        s.name as subject_name, c.name as course_name, y.year as year_name,
        COALESCE(f.download_count, 0) as download_count,
        COALESCE(f.average_rating, 0) as avg_rating";

    // Add relevance score for full-text search
    if (!empty($search_query)) {
        $select_fields .= ", MATCH(f.title, f.description, f.tags, f.keywords) AGAINST (? IN BOOLEAN MODE) as relevance_score";
        $where_conditions[] = "MATCH(f.title, f.description, f.tags, f.keywords) AGAINST (? IN BOOLEAN MODE)";
        $params[] = $search_query;
        $params[] = $search_query;
        $param_types .= "ss";
    } else {
        $select_fields .= ", 0 as relevance_score";
    }

    // Apply filters
    if (!empty($filters['subject_id'])) {
        $where_conditions[] = "f.subject_id = ?";
        $params[] = $filters['subject_id'];
        $param_types .= "i";
    }

    if (!empty($filters['course_id'])) {
        $where_conditions[] = "f.course_id = ?";
        $params[] = $filters['course_id'];
        $param_types .= "i";
    }

    if (!empty($filters['year_id'])) {
        $where_conditions[] = "f.year_id = ?";
        $params[] = $filters['year_id'];
        $param_types .= "i";
    }

    if (!empty($filters['file_type'])) {
        $where_conditions[] = "f.file_type = ?";
        $params[] = $filters['file_type'];
        $param_types .= "s";
    }

    if (!empty($filters['tags'])) {
        $where_conditions[] = "f.tags LIKE ?";
        $params[] = "%{$filters['tags']}%";
        $param_types .= "s";
    }

    // Build ORDER BY clause
    $order_by = "";
    switch ($sort_by) {
        case 'popularity':
            $order_by = "ORDER BY f.download_count DESC, f.average_rating DESC, f.upload_date DESC";
            break;
        case 'recent':
            $order_by = "ORDER BY f.upload_date DESC";
            break;
        case 'rating':
            $order_by = "ORDER BY f.average_rating DESC, f.download_count DESC";
            break;
        case 'size':
            $order_by = "ORDER BY f.file_size DESC";
            break;
        default: // relevance
            if (!empty($search_query)) {
                $order_by = "ORDER BY relevance_score DESC, f.download_count DESC";
            } else {
                $order_by = "ORDER BY f.download_count DESC, f.upload_date DESC";
            }
    }

    $where_clause = implode(' AND ', $where_conditions);

    $query = "SELECT $select_fields
        FROM digital_files f
        LEFT JOIN users u ON f.user_id = u.id
        LEFT JOIN subjects s ON f.subject_id = s.id
        LEFT JOIN courses c ON f.course_id = c.id
        LEFT JOIN years y ON f.year_id = y.id
        WHERE $where_clause $order_by
        LIMIT ?, ?";

    $stmt = mysqli_prepare($mysqli, $query);
    if (!empty($params)) {
        $param_types .= "ii";
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

// Get search count for pagination
function getSearchCount($mysqli, $search_query = '', $filters = [])
{
    $where_conditions = ["f.status = 'active'", "f.visibility = 'public'", "f.verified = 1"];
    $params = [];
    $param_types = "";

    if (!empty($search_query)) {
        $where_conditions[] = "MATCH(f.title, f.description, f.tags, f.keywords) AGAINST (? IN BOOLEAN MODE)";
        $params[] = $search_query;
        $param_types .= "s";
    }

    // Apply same filters as search function
    if (!empty($filters['subject_id'])) {
        $where_conditions[] = "f.subject_id = ?";
        $params[] = $filters['subject_id'];
        $param_types .= "i";
    }

    if (!empty($filters['course_id'])) {
        $where_conditions[] = "f.course_id = ?";
        $params[] = $filters['course_id'];
        $param_types .= "i";
    }

    if (!empty($filters['year_id'])) {
        $where_conditions[] = "f.year_id = ?";
        $params[] = $filters['year_id'];
        $param_types .= "i";
    }

    if (!empty($filters['file_type'])) {
        $where_conditions[] = "f.file_type = ?";
        $params[] = $filters['file_type'];
        $param_types .= "s";
    }

    if (!empty($filters['tags'])) {
        $where_conditions[] = "f.tags LIKE ?";
        $params[] = "%{$filters['tags']}%";
        $param_types .= "s";
    }

    $where_clause = implode(' AND ', $where_conditions);

    $query = "SELECT COUNT(*) as total FROM digital_files f WHERE $where_clause";
    $stmt = mysqli_prepare($mysqli, $query);

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    return $count;
}

// Get search suggestions
function getSearchSuggestions($mysqli, $query = '', $limit = 10)
{
    if (empty($query)) {
        $sql = "SELECT suggestion FROM search_suggestions ORDER BY popularity_score DESC LIMIT ?";
        $stmt = mysqli_prepare($mysqli, $sql);
        mysqli_stmt_bind_param($stmt, "i", $limit);
    } else {
        $sql = "SELECT suggestion FROM search_suggestions 
                WHERE suggestion LIKE ? 
                ORDER BY popularity_score DESC LIMIT ?";
        $stmt = mysqli_prepare($mysqli, $sql);
        $search_term = "%$query%";
        mysqli_stmt_bind_param($stmt, "si", $search_term, $limit);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $suggestions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row['suggestion'];
    }

    mysqli_stmt_close($stmt);
    return $suggestions;
}

// Log search analytics
function logSearchAnalytics($mysqli, $user_id, $search_query, $filters, $results_count, $ip_address)
{
    $query = "INSERT INTO search_analytics (user_id, search_query, search_filters, results_count, ip_address) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $query);
    $filters_json = json_encode($filters);
    mysqli_stmt_bind_param($stmt, "issis", $user_id, $search_query, $filters_json, $results_count, $ip_address);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Get popular files (using the optimized view)
function getPopularFiles($mysqli, $limit = 10)
{
    $query = "SELECT * FROM v_popular_files LIMIT ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}

// Get recent files (using the optimized view)
function getRecentFiles($mysqli, $limit = 10)
{
    $query = "SELECT * FROM v_recent_files LIMIT ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}

// Update search suggestion popularity
function updateSearchSuggestionPopularity($mysqli, $suggestion)
{
    $query = "INSERT INTO search_suggestions (suggestion, popularity_score) 
              VALUES (?, 1) 
              ON DUPLICATE KEY UPDATE popularity_score = popularity_score + 1";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, "s", $suggestion);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Generate content hash for duplicate detection
function generateContentHash($file_path)
{
    if (file_exists($file_path)) {
        return hash_file('sha256', $file_path);
    }
    return null;
}

// Extract keywords from text (basic implementation)
function extractKeywords($text, $max_keywords = 10)
{
    // Remove HTML tags and special characters
    $text = strip_tags($text);
    $text = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $text);

    // Convert to lowercase and split into words
    $words = str_word_count(strtolower($text), 1);

    // Remove common stop words
    $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those'];
    $words = array_diff($words, $stopwords);

    // Count word frequency
    $word_count = array_count_values($words);

    // Sort by frequency and get top keywords
    arsort($word_count);
    $keywords = array_slice(array_keys($word_count), 0, $max_keywords);

    return implode(', ', $keywords);
}

// Legacy functions (kept for backward compatibility)
function getFilesWithStats($mysqli, $where = "1=1", $params = [], $param_types = "", $offset = 0, $limit = 12, $order = "ORDER BY f.upload_date DESC"): bool|mysqli_result
{
    // Enhanced to use new fields
    $query = "SELECT f.*, u.name as uploader_name, u.id as uploader_id, u.avatar_path as uploader_avatar,
        (SELECT COUNT(*) FROM reported_content WHERE content_id = f.id AND status = 'resolved') as report_count,
        COALESCE(f.download_count, 0) as download_count,
        COALESCE(f.average_rating, 0) as avg_rating,
        s.name as subject,
        c.name as course,
        y.year
        FROM digital_files f
        JOIN users u ON f.user_id = u.id
        LEFT JOIN subjects s ON f.subject_id = s.id
        LEFT JOIN courses c ON f.course_id = c.id
        LEFT JOIN years y ON f.year_id = y.id
        WHERE $where AND f.status = 'active' AND f.visibility = 'public' $order LIMIT ?, ?";

    $stmt = mysqli_prepare($mysqli, $query);
    if (!empty($params)) {
        $param_types .= "ii";
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
        'type' => $type,
        'message' => $message
    ];
}

function redirect(string $url): void
{
    header("Location: $url");
    exit();
}

?>