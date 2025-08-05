<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get count of unique users with unread messages received by the user
$stmt_unread_messages = $conn->prepare(
    "SELECT COUNT(DISTINCT m.sender_id) as unread_count
    FROM Messages m
    WHERE m.receiver_id = ? AND m.status = 'unread'"
);
$stmt_unread_messages->bind_param('i', $user_id);
$stmt_unread_messages->execute();
$result_unread_messages = $stmt_unread_messages->get_result();
$unread_message_data = $result_unread_messages->fetch_assoc();
$unread_message_count = $unread_message_data['unread_count'];

echo json_encode(['unread_count' => $unread_message_count]);
?> 