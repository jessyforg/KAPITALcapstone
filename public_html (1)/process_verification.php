<?php
// Include database connection
include 'db_connection.php';

// Start session
session_start();

// Check if the logged-in user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Function to update user verification status based on document statuses
function updateUserVerificationStatus($user_id) {
    global $conn;
    
    // First check if there are any pending documents
    $check_pending = "SELECT COUNT(*) as pending_count 
                     FROM Verification_Documents 
                     WHERE user_id = ? AND status = 'pending'";
    
    $stmt = $conn->prepare($check_pending);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_count = $result->fetch_assoc()['pending_count'];
    
    if ($pending_count > 0) {
        // If there are pending documents, set status to pending
        $new_status = 'pending';
    } else {
        // Check if there are any approved documents
        $check_approved = "SELECT COUNT(*) as approved_count 
                          FROM Verification_Documents 
                          WHERE user_id = ? AND status = 'approved'";
        
        $stmt = $conn->prepare($check_approved);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $approved_count = $result->fetch_assoc()['approved_count'];
        
        if ($approved_count > 0) {
            // If there are approved documents and no pending ones, set to verified
            $new_status = 'verified';
        } else {
            // If no approved documents and no pending ones, set to not approved
            $new_status = 'not approved';
        }
    }
    
    // Update the user's verification status
    $update_user = "UPDATE Users SET verification_status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("si", $new_status, $user_id);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['document_id']) && isset($_POST['action'])) {
    $document_id = intval($_POST['document_id']);
    $action = $_POST['action'];
    $admin_id = $_SESSION['user_id'];

    // Get the document details
    $query = "SELECT vd.*, u.user_id, u.verification_status 
              FROM Verification_Documents vd 
              JOIN Users u ON vd.user_id = u.user_id 
              WHERE vd.document_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $document_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $document = $result->fetch_assoc();

    if ($document) {
        if ($action === 'approve') {
            // Update document status
            $update_doc = "UPDATE Verification_Documents 
                          SET status = 'approved', 
                              reviewed_by = ?, 
                              reviewed_at = CURRENT_TIMESTAMP 
                          WHERE document_id = ?";
            $stmt = $conn->prepare($update_doc);
            $stmt->bind_param("ii", $admin_id, $document_id);
            $stmt->execute();

            // Update user verification status based on all documents
            updateUserVerificationStatus($document['user_id']);

            // Create notification
            $notification_message = "Your verification document has been approved. Your account is now verified.";
            $notification_query = "INSERT INTO Notifications (user_id, sender_id, type, message, status, created_at) 
                                 VALUES (?, ?, 'system_alert', ?, 'unread', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($notification_query);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                $_SESSION['error_message'] = "Error creating notification: " . $conn->error;
                header("Location: admin-panel.php");
                exit;
            }
            
            if (!$stmt->bind_param("iis", $document['user_id'], $admin_id, $notification_message)) {
                error_log("Binding parameters failed: " . $stmt->error);
                $_SESSION['error_message'] = "Error binding notification parameters: " . $stmt->error;
                header("Location: admin-panel.php");
                exit;
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $_SESSION['error_message'] = "Error executing notification query: " . $stmt->error;
                header("Location: admin-panel.php");
                exit;
            }

        } elseif ($action === 'reject') {
            $rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : '';
            
            // Update document status
            $update_doc = "UPDATE Verification_Documents 
                          SET status = 'not approved', 
                              reviewed_by = ?, 
                              reviewed_at = CURRENT_TIMESTAMP,
                              rejection_reason = ?
                          WHERE document_id = ?";
            $stmt = $conn->prepare($update_doc);
            $stmt->bind_param("isi", $admin_id, $rejection_reason, $document_id);
            $stmt->execute();

            // Update user verification status based on all documents
            updateUserVerificationStatus($document['user_id']);

            // Create notification
            $notification_message = "Your verification document was not approved." . 
                                  (!empty($rejection_reason) ? " Reason: " . $rejection_reason : " Please upload a valid document.");
            $notification_query = "INSERT INTO Notifications (user_id, sender_id, type, message, status, url, created_at) 
                                 VALUES (?, ?, 'system_alert', ?, 'unread', 'verify_account.php', CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($notification_query);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                $_SESSION['error_message'] = "Error creating notification: " . $conn->error;
                header("Location: admin-panel.php");
                exit;
            }
            
            if (!$stmt->bind_param("iis", $document['user_id'], $admin_id, $notification_message)) {
                error_log("Binding parameters failed: " . $stmt->error);
                $_SESSION['error_message'] = "Error binding notification parameters: " . $stmt->error;
                header("Location: admin-panel.php");
                exit;
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $_SESSION['error_message'] = "Error executing notification query: " . $stmt->error;
                header("Location: admin-panel.php");
                exit;
            }
        }
    }
}

// Redirect back to admin panel user verifications section
header("Location: admin-panel.php?section=user-verifications");
exit; 