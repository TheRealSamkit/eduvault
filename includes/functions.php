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
        default:
            return "lines";
    }
}

?>