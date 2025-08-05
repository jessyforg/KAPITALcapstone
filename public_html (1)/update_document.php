<?php
session_start();
require_once 'db_connection.php';

// Set Content Security Policy header
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'");

// Set proper headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the POST data
$document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
$document_type = isset($_POST['document_type']) ? $_POST['document_type'] : '';
$document_number = isset($_POST['document_number']) ? $_POST['document_number'] : '';
$issue_date = isset($_POST['issue_date']) ? $_POST['issue_date'] : '';
$expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';
$issuing_authority = isset($_POST['issuing_authority']) ? $_POST['issuing_authority'] : '';
$user_id = $_SESSION['user_id'];

// Log the received data for debugging
error_log("Received data: " . print_r($_POST, true));
error_log("Document ID: " . $document_id . ", Type: " . gettype($document_id));

// Validate required fields
if ($document_id <= 0 || empty($document_type)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing. Document ID: ' . $document_id . ', Document Type: ' . $document_type]);
    exit;
}

try {
    // First check if the document belongs to the logged-in user
    $check_query = "SELECT user_id FROM Verification_Documents WHERE document_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("ii", $document_id, $user_id);
    if (!$check_stmt->execute()) {
        throw new Exception("Execute failed: " . $check_stmt->error);
    }
    
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Document not found or unauthorized access']);
        exit;
    }

    // Update the document details
    $update_query = "UPDATE Verification_Documents SET 
                    document_type = ?,
                    document_number = ?,
                    issue_date = ?,
                    expiry_date = ?,
                    issuing_authority = ?,
                    status = 'pending'
                    WHERE document_id = ? AND user_id = ?";

    $update_stmt = $conn->prepare($update_query);
    if (!$update_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $update_stmt->bind_param("sssssii", 
        $document_type,
        $document_number,
        $issue_date,
        $expiry_date,
        $issuing_authority,
        $document_id,
        $user_id
    );

    if (!$update_stmt->execute()) {
        throw new Exception("Execute failed: " . $update_stmt->error);
    }

    // Handle file upload if a new file was provided
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        // Validate file
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }

        if ($file['size'] > $max_file_size) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            exit;
        }

        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/verification_documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate new filename
        $new_filename = uniqid('doc_') . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Update document file path in database
            $update_file_query = "UPDATE Verification_Documents SET 
                file_name = ?,
                file_path = ?,
                file_type = ?,
                file_size = ?
                WHERE document_id = ?";
            $update_file_stmt = $conn->prepare($update_file_query);
            if (!$update_file_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $update_file_stmt->bind_param("sssii", 
                $new_filename,
                $upload_path,
                $file['type'],
                $file['size'],
                $document_id
            );
            if (!$update_file_stmt->execute()) {
                throw new Exception("Execute failed: " . $update_file_stmt->error);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Document updated successfully']);

} catch (Exception $e) {
    error_log("Error updating document: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the document: ' . $e->getMessage()]);
}

$conn->close();
?> 