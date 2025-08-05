<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit();
}

// Get the file path from the URL
$file = isset($_GET['file']) ? $_GET['file'] : '';

// Validate that the file belongs to the current user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT file_path, file_type FROM Verification_Documents WHERE user_id = ? AND file_path = ?");
$stmt->bind_param("is", $user_id, $file);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // File doesn't belong to this user or doesn't exist
    header("HTTP/1.0 403 Forbidden");
    echo "Access denied";
    exit();
}

$document = $result->fetch_assoc();
$file_path = $document['file_path'];

// Verify the file exists
if (!file_exists($file_path)) {
    header("HTTP/1.0 404 Not Found");
    echo "File not found";
    exit();
}

// Get file information
$file_info = pathinfo($file_path);
$file_extension = strtolower($file_info['extension']);

// Set appropriate content type
switch ($file_extension) {
    case 'pdf':
        header('Content-Type: application/pdf');
        break;
    case 'jpg':
    case 'jpeg':
        header('Content-Type: image/jpeg');
        break;
    case 'png':
        header('Content-Type: image/png');
        break;
    default:
        header("HTTP/1.0 415 Unsupported Media Type");
        echo "Unsupported file type";
        exit();
}

// Set headers to prevent caching
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output the file
readfile($file_path);
exit();
?>