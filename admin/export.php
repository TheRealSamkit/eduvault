<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    redirect("index.php");
    exit();
}

// Get export format from URL
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';
$type = isset($_GET['type']) ? $_GET['type'] : 'files';
$allowedFormats = ['csv', 'pdf'];


if (!in_array($format, $allowedFormats)) {
    die('Invalid format.');
}

// Fetch data to export
if ($type === 'files') {
    $query = "
    SELECT f.*, u.name AS owner_name, u.email AS owner_email, COUNT(d.file_id) AS download_count
    FROM digital_files f
    JOIN users u ON f.user_id = u.id
    LEFT JOIN downloads d ON f.id = d.file_id
    GROUP BY f.id
    ORDER BY f.upload_date DESC
";
    $result = mysqli_query($mysqli, $query);

} else {
    $result = mysqli_query($mysqli, "SELECT b.*, u.name as owner_name, u.email as owner_email , bo.name as board,s.name as subject
                               FROM book_listings b 
                               JOIN users u ON b.user_id = u.id
                               JOIN boards bo on b.board_id=bo.id
                               JOIN subjects s on b.subject_id = s.id
                               ORDER BY b.created_at DESC");
}

// Export CSV
if ($format === 'csv' && $type === 'files') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="books_export.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Type', 'Subject', 'Owner', 'Email', 'Size (MB)', 'Downloads', 'Verified', 'Uploaded_at']);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            $row['title'],
            strtoupper($row['file_type']),
            $row['subject'],
            $row['owner_name'],
            $row['owner_email'],
            round(settype($row['file_size'], "float"), 1) == 0 ? '0' : round(settype($row['file_size'], "float"), 1),
            $row['download_count'],
            $row['verified'] ? 'Verified' : 'Banned',
            date('Y-m-d', strtotime($row['upload_date']))
        ]);
    }

    fclose($output);
    exit();
}

if ($format === 'csv' && $type === 'books') {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="files_export.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Subject', 'Board', 'Owner', 'Email', 'Location', 'Status', 'Added_at']);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['id'],
            $row['title'],
            $row['subject'],
            $row['board'],
            $row['owner_name'],
            $row['owner_email'],
            $row['location'],
            $row['status'],
            date('Y-m-d', strtotime($row['created_at']))
        ]);
    }

    fclose($output);
    exit();
}

// Export PDF
if ($format === 'pdf') {
    require_once '../vendor/autoload.php'; // Assuming you use Composer & mpdf/mpdf installed

    $mpdf = new \Mpdf\Mpdf();
    $html = '<h2>Files Export</h2><table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $html .= '<thead><tr>
                <th>ID</th><th>Title</th><th>Type</th><th>Subject</th>
                <th>Owner</th><th>Email</th><th>Size (MB)</th>
                <th>Downloads</th><th>Verified</th><th>Uploaded</th>
              </tr></thead><tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>';
        $html .= '<td>' . $row['id'] . '</td>';
        $html .= '<td>' . htmlspecialchars($row['title']) . '</td>';
        $html .= '<td>' . strtoupper($row['file_type']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['subject']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['owner_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['owner_email']) . '</td>';
        $html .= '<td>' . round($row['file_size'] / (1024 * 1024), 2) . ' MB</td>';
        $html .= '<td>' . $row['download_count'] . '</td>';
        $html .= '<td>' . ($row['verified'] ? 'Verified' : 'Banned') . '</td>';
        $html .= '<td>' . date('Y-m-d', strtotime($row['upload_date'])) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    $mpdf->WriteHTML($html);
    $mpdf->Output('files_export.pdf', 'D');
    exit();
}
