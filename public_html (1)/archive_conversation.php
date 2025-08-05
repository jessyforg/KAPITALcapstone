<?php
session_start();
include('db_connection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || !isset($_POST['other_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user_id = (int)$_POST['other_user_id'];
$archive = isset($_POST['archive']) ? (int)$_POST['archive'] : 1;

// Start transaction to ensure consistency
$conn->begin_transaction();

try {
    // Get current state
    $check_stmt = $conn->prepare("SELECT muted, archived FROM User_Conversations WHERE user_id = ? AND other_user_id = ?");
    $check_stmt->bind_param("ii", $user_id, $other_user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    $existing_muted = 0;
    $record_exists = false;
    
    if ($row = $check_result->fetch_assoc()) {
        $existing_muted = (int)$row['muted'];
        $record_exists = true;
    }
    
    if ($record_exists) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE User_Conversations SET archived = ? WHERE user_id = ? AND other_user_id = ?");
        $stmt->bind_param("iii", $archive, $user_id, $other_user_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO User_Conversations (user_id, other_user_id, muted, archived) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $user_id, $other_user_id, $existing_muted, $archive);
    }
    
    $success = $stmt->execute();
    
    if ($success) {
        $conn->commit();
        
        // Verify the change was applied
        $verify_stmt = $conn->prepare("SELECT archived FROM User_Conversations WHERE user_id = ? AND other_user_id = ?");
        $verify_stmt->bind_param("ii", $user_id, $other_user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $verify_row = $verify_result->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'archived' => $archive,
            'verified_archived' => (int)$verify_row['archived'],
            'debug' => "Record " . ($record_exists ? "updated" : "inserted") . " successfully"
        ]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Database operation failed: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
} 