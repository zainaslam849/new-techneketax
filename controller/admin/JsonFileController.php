<?php
// Specify the filename
$filename = "json/data.json";
if (file_exists($filename)) {
    header('Content-Type: application/json');
    readfile($filename);
} else {
    // File doesn't exist
    http_response_code(404);
    echo "File not found.";
}