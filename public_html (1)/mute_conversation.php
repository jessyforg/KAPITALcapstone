<?php
session_start();
include('db_connection.php');
if (!isset($_SESSION['user_id']) || !isset($_POST['other_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
$user_id = $_SESSION['user_id'];
$other_user_id = (int)$_POST['other_user_id'];
$mute = isset($_POST['mute']) ? (int)$_POST['mute'] : 1;

// First, get existing archived state if record exists
$check_stmt = $conn->prepare("SELECT archived FROM User_Conversations WHERE user_id = ? AND other_user_id = ?");
$check_stmt->bind_param("ii", $user_id, $other_user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$existing_archived = 0; // default
if ($row = $check_result->fetch_assoc()) {
    $existing_archived = (int)$row['archived'];
}

// Now insert/update with both fields to prevent conflicts
$stmt = $conn->prepare("INSERT INTO User_Conversations (user_id, other_user_id, muted, archived) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE muted = VALUES(muted), archived = VALUES(archived)");
$stmt->bind_param("iiii", $user_id, $other_user_id, $mute, $existing_archived);
$success = $stmt->execute();
echo json_encode(['success' => $success, 'muted' => $mute]); 