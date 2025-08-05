<?php
session_start();
include 'db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: sign_in.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['new_status'];
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);

    $sql = "UPDATE Tickets SET 
            status = ?, 
            admin_notes = ?,
            updated_at = NOW()
            WHERE ticket_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $new_status, $admin_notes, $ticket_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Ticket status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating ticket status.";
    }
    mysqli_stmt_close($stmt);
}

header("Location: admin-panel.php?section=tickets");
exit();
?> 