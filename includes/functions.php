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


?>