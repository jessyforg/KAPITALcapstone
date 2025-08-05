<?php
session_start();
include('db_connection.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit("Redirecting to login page...");
}

$hide_navbar_search = true;
include('navbar.php');

$user_id = $_SESSION['user_id'];

// Add this function after the session_start() and before the database queries
function formatRole($role) {
    switch($role) {
        case 'job_seeker':
            return 'Job Seeker';
        case 'entrepreneur':
            return 'Entrepreneur';
        case 'investor':
            return 'Investor';
        case 'admin':
            return 'TARAKI Admin';
        default:
            return ucfirst($role);
    }
}

// Handle message request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['sender_id'])) {
    $sender_id = (int)$_POST['sender_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        // Update the conversation request status
        $update_query = "UPDATE Conversation_Requests 
                        SET status = ? 
                        WHERE sender_id = ? AND receiver_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('sii', $status, $sender_id, $user_id);
        
        if ($update_stmt->execute()) {
            // Also update the message status
            $update_message_query = "UPDATE Messages 
                                   SET request_status = ? 
                                   WHERE sender_id = ? AND receiver_id = ? 
                                   AND is_intro_message = TRUE";
            $update_message_stmt = $conn->prepare($update_message_query);
            $update_message_stmt->bind_param('sii', $status, $sender_id, $user_id);
            $update_message_stmt->execute();
            
            $_SESSION['success'] = $action === 'approve' ? 
                'Message request accepted.' : 
                'Message request declined.';
                
            // Redirect to chat if approved
            if ($action === 'approve') {
                header("Location: messages.php?chat_with=" . $sender_id);
                exit();
            }
        } else {
            $_SESSION['error'] = 'Failed to process the request. Please try again.';
        }
        
        header("Location: messages.php");
        exit();
    }
}

// Define $has_file before using it
$has_file = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;

// Modify the message sending handler to check for intro messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $receiver_id = $_POST['receiver_id'] ?? null;

    if ($receiver_id && (!empty($message) || $has_file)) {
        try {
            $conn->begin_transaction();

            // Check if there are any existing approved messages between these users
            $check_query = "SELECT COUNT(*) as count FROM Messages 
                          WHERE ((sender_id = ? AND receiver_id = ?) 
                          OR (sender_id = ? AND receiver_id = ?)) 
                          AND request_status = 'approved'";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("iiii", $_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                // If there are approved messages, send directly
                $insert_query = "INSERT INTO Messages (sender_id, receiver_id, content, request_status) 
                               VALUES (?, ?, ?, 'approved')";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("iis", $_SESSION['user_id'], $receiver_id, $message);
                $insert_stmt->execute();
                $message_id = $conn->insert_id;
                // Handle file upload for normal messages
                if ($has_file && $message_id) {
                    $upload_dir = 'uploads/messages/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $original_name = basename($_FILES['file']['name']);
                    $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                    $new_name = uniqid('file_', true) . '.' . $ext;
                    $target_path = $upload_dir . $new_name;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
                        $insert_file = "INSERT INTO Message_Files (message_id, file_name, file_path) VALUES (?, ?, ?)";
                        $insert_file_stmt = $conn->prepare($insert_file);
                        $insert_file_stmt->bind_param("iss", $message_id, $original_name, $target_path);
                        $insert_file_stmt->execute();
                    } else {
                        throw new Exception("File upload failed.");
                    }
                }
                $_SESSION['success'] = "Message sent successfully.";
            } else {
                // Check for any existing requests
                $request_query = "SELECT request_id, sender_id, receiver_id, status FROM Conversation_Requests 
                                WHERE ((sender_id = ? AND receiver_id = ?) 
                                OR (sender_id = ? AND receiver_id = ?))";
                $request_stmt = $conn->prepare($request_query);
                $request_stmt->bind_param("iiii", $_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']);
                $request_stmt->execute();
                $request_result = $request_stmt->get_result();

                if ($request_result->num_rows > 0) {
                    $request = $request_result->fetch_assoc();
                    
                    // Ensure we have valid request data
                    if (!$request || !isset($request['status'])) {
                        throw new Exception("Invalid request data retrieved from database.");
                    }
                    
                    if ($request['status'] === 'pending') {
                        $_SESSION['error'] = "A message request is already pending.";
                    } else if ($request['status'] === 'rejected') {
                        // Delete old rejected request and create new one
                        if (isset($request['request_id']) && $request['request_id'] !== null) {
                            $delete_query = "DELETE FROM Conversation_Requests WHERE request_id = ?";
                            $delete_stmt = $conn->prepare($delete_query);
                            $delete_stmt->bind_param("i", $request['request_id']);
                            $delete_stmt->execute();
                        } else {
                            // If request_id is not available, delete by sender and receiver
                            if (isset($request['sender_id']) && isset($request['receiver_id'])) {
                                $delete_query = "DELETE FROM Conversation_Requests WHERE sender_id = ? AND receiver_id = ?";
                                $delete_stmt = $conn->prepare($delete_query);
                                $delete_stmt->bind_param("ii", $request['sender_id'], $request['receiver_id']);
                                $delete_stmt->execute();
                            } else {
                                throw new Exception("Missing sender_id or receiver_id in request data.");
                            }
                        }

                        // Create new request and intro message
                        $insert_request = "INSERT INTO Conversation_Requests (sender_id, receiver_id) VALUES (?, ?)";
                        $insert_request_stmt = $conn->prepare($insert_request);
                        $insert_request_stmt->bind_param("ii", $_SESSION['user_id'], $receiver_id);
                        $insert_request_stmt->execute();

                        // Insert the intro message directly without relying on trigger
                        $insert_message = "INSERT INTO Messages (sender_id, receiver_id, content, is_intro_message, request_status) 
                                         VALUES (?, ?, ?, TRUE, 'pending')";
                        $insert_message_stmt = $conn->prepare($insert_message);
                        $insert_message_stmt->bind_param("iis", $_SESSION['user_id'], $receiver_id, $message);
                        $insert_message_stmt->execute();
                        $message_id = $conn->insert_id;
                        // Handle file upload for intro messages
                        if ($has_file && $message_id) {
                            $upload_dir = 'uploads/messages/';
                            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                            $original_name = basename($_FILES['file']['name']);
                            $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                            $new_name = uniqid('file_', true) . '.' . $ext;
                            $target_path = $upload_dir . $new_name;
                            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
                                $insert_file = "INSERT INTO Message_Files (message_id, file_name, file_path) VALUES (?, ?, ?)";
                                $insert_file_stmt = $conn->prepare($insert_file);
                                $insert_file_stmt->bind_param("iss", $message_id, $original_name, $target_path);
                                $insert_file_stmt->execute();
                            } else {
                                throw new Exception("File upload failed.");
                            }
                        }
                        $_SESSION['success'] = "Message request sent successfully.";
                    }
                } else {
                    // Create new request and intro message
                    $insert_request = "INSERT INTO Conversation_Requests (sender_id, receiver_id) VALUES (?, ?)";
                    $insert_request_stmt = $conn->prepare($insert_request);
                    $insert_request_stmt->bind_param("ii", $_SESSION['user_id'], $receiver_id);
                    $insert_request_stmt->execute();

                    // Insert the intro message directly without relying on trigger
                    $insert_message = "INSERT INTO Messages (sender_id, receiver_id, content, is_intro_message, request_status) 
                                     VALUES (?, ?, ?, TRUE, 'pending')";
                    $insert_message_stmt = $conn->prepare($insert_message);
                    $insert_message_stmt->bind_param("iis", $_SESSION['user_id'], $receiver_id, $message);
                    $insert_message_stmt->execute();
                    $message_id = $conn->insert_id;
                    // Handle file upload for intro messages
                    if ($has_file && $message_id) {
                        $upload_dir = 'uploads/messages/';
                        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                        $original_name = basename($_FILES['file']['name']);
                        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
                        $new_name = uniqid('file_', true) . '.' . $ext;
                        $target_path = $upload_dir . $new_name;
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
                            $insert_file = "INSERT INTO Message_Files (message_id, file_name, file_path) VALUES (?, ?, ?)";
                            $insert_file_stmt = $conn->prepare($insert_file);
                            $insert_file_stmt->bind_param("iss", $message_id, $original_name, $target_path);
                            $insert_file_stmt->execute();
                        } else {
                            throw new Exception("File upload failed.");
                        }
                    }
                    $_SESSION['success'] = "Message request sent successfully.";
                }
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Failed to send message. Error: " . $e->getMessage();
        }
    }
}

// Add query to fetch pending message requests
$pending_requests_query = "
    SELECT 
        cr.*,
        u.name as sender_name,
        u.role as sender_role,
        m.content as intro_message,
        m.sent_at
    FROM Conversation_Requests cr
    JOIN Users u ON cr.sender_id = u.user_id
    JOIN Messages m ON cr.sender_id = m.sender_id 
        AND cr.receiver_id = m.receiver_id 
        AND m.is_intro_message = TRUE
    WHERE cr.receiver_id = ? 
    AND cr.status = 'pending'
    ORDER BY cr.created_at DESC";
$pending_stmt = $conn->prepare($pending_requests_query);
$pending_stmt->bind_param('i', $user_id);
$pending_stmt->execute();
$pending_requests = $pending_stmt->get_result();

// Fetch conversations (inbox or archived)
$show_archived = isset($_GET['archived']) && $_GET['archived'] == '1';
if ($show_archived) {
    $conversations_query = "
        SELECT DISTINCT Users.user_id, Users.name, Users.role
        FROM Messages
        JOIN Users ON (Messages.sender_id = Users.user_id OR Messages.receiver_id = Users.user_id)
        JOIN User_Conversations uc ON uc.user_id = ? AND uc.other_user_id = Users.user_id AND uc.archived = 1
        WHERE (Messages.sender_id = ? OR Messages.receiver_id = ?) AND Users.user_id != ?
        ORDER BY (SELECT MAX(Messages.sent_at) FROM Messages 
                  WHERE (Messages.sender_id = Users.user_id AND Messages.receiver_id = ?) 
                  OR (Messages.sender_id = ? AND Messages.receiver_id = Users.user_id)) DESC";
    $conversations_stmt = $conn->prepare($conversations_query);
    $conversations_stmt->bind_param('iiiiii', $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $conversations_stmt->execute();
    $conversations_result = $conversations_stmt->get_result();
} else {
    $conversations_query = "
        SELECT DISTINCT Users.user_id, Users.name, Users.role
        FROM Messages
        JOIN Users ON (Messages.sender_id = Users.user_id OR Messages.receiver_id = Users.user_id)
        LEFT JOIN User_Conversations uc ON uc.user_id = ? AND uc.other_user_id = Users.user_id
        WHERE (Messages.sender_id = ? OR Messages.receiver_id = ?) AND Users.user_id != ?
        AND (uc.archived IS NULL OR uc.archived = 0)
        ORDER BY (SELECT MAX(Messages.sent_at) FROM Messages 
                  WHERE (Messages.sender_id = Users.user_id AND Messages.receiver_id = ?) 
                  OR (Messages.sender_id = ? AND Messages.receiver_id = Users.user_id)) DESC";
    $conversations_stmt = $conn->prepare($conversations_query);
    $conversations_stmt->bind_param('iiiiii', $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $conversations_stmt->execute();
    $conversations_result = $conversations_stmt->get_result();
}

// Check if recipient_id is provided in URL parameters
if (isset($_GET['recipient_id']) && !empty($_GET['recipient_id'])) {
    $chat_with = (int)$_GET['recipient_id'];
    
    // Check if the user exists
    $check_user = $conn->prepare("SELECT user_id FROM Users WHERE user_id = ?");
    $check_user->bind_param("i", $chat_with);
    $check_user->execute();
    $user_exists = $check_user->get_result()->num_rows > 0;
    
    if ($user_exists) {
        // If valid recipient, use it as chat_with
        $_GET['chat_with'] = $chat_with;
    }
}

// Fetch chat messages and user details for the selected conversation
$chat_with = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : null;
$messages = [];
$chat_user = null;  // To store the chat user's details (name, role)
if ($chat_with) {
    $messages_query = "
        SELECT m.*, cr.status as request_status
        FROM Messages m
        LEFT JOIN Conversation_Requests cr ON 
            (m.sender_id = cr.sender_id AND m.receiver_id = cr.receiver_id)
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
        OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at ASC";
    $messages_stmt = $conn->prepare($messages_query);
    $messages_stmt->bind_param('iiii', $user_id, $chat_with, $chat_with, $user_id);
    $messages_stmt->execute();
    $messages_result = $messages_stmt->get_result();

    while ($row = $messages_result->fetch_assoc()) {
        $messages[] = $row;
    }

    // Fetch user details (name and role) of the person you're chatting with
    $user_query = "SELECT name, role FROM Users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param('i', $chat_with);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $chat_user = $user_result->fetch_assoc();

    // Add this section to show the approval buttons if there's a pending request
    $check_pending_query = "
        SELECT status 
        FROM Conversation_Requests 
        WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'";
    $check_pending_stmt = $conn->prepare($check_pending_query);
    $check_pending_stmt->bind_param('ii', $chat_with, $user_id);
    $check_pending_stmt->execute();
    $pending_result = $check_pending_stmt->get_result();
    $has_pending_request = $pending_result->num_rows > 0;

    // Fetch mute/archive state for this conversation
    $mute_state = 0;
    $archive_state = 0;
    $state_query = $conn->prepare("SELECT muted, archived FROM User_Conversations WHERE user_id = ? AND other_user_id = ?");
    $state_query->bind_param("ii", $user_id, $chat_with);
    $state_query->execute();
    $state_result = $state_query->get_result();
    if ($row = $state_result->fetch_assoc()) {
        $mute_state = (int)$row['muted'];
        $archive_state = (int)$row['archived'];
    }
}

// Handle user search
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : null;
$search_results = [];
if ($search_query) {
    $user_search_query = "SELECT user_id, name, role 
                         FROM Users 
                         WHERE name LIKE ? 
                         AND user_id != ? 
                         AND show_in_messages = 1  -- Only show users who allow messages
                         AND role != 'admin'       -- Don't show admin users in search
                         ORDER BY name ASC";
    $search_stmt = $conn->prepare($user_search_query);
    $like_query = '%' . $search_query . '%';
    $search_stmt->bind_param('si', $like_query, $user_id);
    $search_stmt->execute();
    $search_results = $search_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS Configuration -->
    <link rel="stylesheet" href="tailwind-config.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind-init.js"></script>
    
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        .messages-container {
            margin-left: 0; /* No sidebar */
            margin-top: 0; /* Remove margin since body has padding-top */
            transition: margin-left 0.3s, margin-top 0.3s;
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25);
        }
        .floating-navbar {
            margin-left: 16px !important;
            margin-top: 16px !important;
            transition: margin-left 0.3s, margin-top 0.3s;
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25);
            z-index: 1000 !important; /* Ensure navbar stays on top */
        }
        .messages-page {
            background-color: #1a1b1e !important;
            min-height: 100vh !important;
            height: 100vh !important;
            padding: 0 !important; /* Remove conflicting padding */
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: flex-start !important;
            padding-top: 16px !important; /* Add top padding only */
        }
        
        /* Responsive adjustments for messages */
        @media (max-width: 768px) {
            .messages-page {
                padding-top: 100px !important; /* Responsive padding for tablets */
            }
            .messages-container {
                margin-top: 0;
                margin-left: 8px;
                margin-right: 8px;
            }
            .floating-navbar {
                margin-left: 8px !important;
                margin-right: 8px !important;
            }
        }
        
        @media (max-width: 475px) {
            .messages-page {
                padding-top: 88px !important; /* Responsive padding for mobile */
            }
            .messages-container {
                margin-top: 0;
                margin-left: 4px;
                margin-right: 4px;
            }
            .floating-navbar {
                margin-left: 4px !important;
                margin-right: 4px !important;
            }
        }
        .messages-page .messages-container {
            display: flex !important;
            display: flex !important;
            flex: 1 1 auto !important;
            width: calc(100vw - 32px) !important;
            max-width: 1400px !important;
            min-height: 0 !important;
            height: calc(100vh - 140px) !important; /* Adjusted for proper navbar clearance */
            border-radius: 18px !important;
            margin-bottom: 16px !important;
            margin-top: 0 !important; /* Remove extra margin since parent handles spacing */
            margin-left: 16px !important;
            margin-right: 16px !important;
            overflow: hidden !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
            background-color: #1a1b1e !important;
            border: 1px solid #2c2e33 !important;
        }
        .messages-page .messages-sidebar {
            width: 350px !important;
            background-color: #1a1b1e !important;
            border-right: 1px solid #2c2e33 !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            overflow: hidden !important;
        }
        
        /* Responsive messages layout */
        @media (max-width: 768px) {
            .messages-page .messages-container {
                flex-direction: column !important;
                height: calc(100vh - 120px) !important; /* Adjusted for tablet navbar clearance */
                width: calc(100vw - 16px) !important;
                margin-left: 8px !important;
                margin-right: 8px !important;
            }
            .messages-page .messages-sidebar {
                width: 100% !important;
                height: 40% !important;
                border-right: none !important;
                border-bottom: 1px solid #2c2e33 !important;
            }
        }
        
        @media (max-width: 475px) {
            .messages-page .messages-container {
                height: calc(100vh - 108px) !important; /* Adjusted for mobile navbar clearance */
                width: calc(100vw - 8px) !important;
                border-radius: 12px !important;
                margin-left: 4px !important;
                margin-right: 4px !important;
            }
            .messages-page .messages-sidebar {
                height: 35% !important;
            }
        }
        .messages-page .conversations-container {
            flex: 1 !important;
            overflow-y: auto !important;
            padding: 16px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .messages-page .chat-area {
            flex: 1 1 0 !important;
            display: flex !important;
            flex-direction: column !important;
            background-color: #1a1b1e !important;
            overflow: hidden !important;
            padding-bottom: 24px !important;
        }
        .messages-page .messages {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            padding: 24px !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 16px !important;
            background-color: #1a1b1e !important;
        }
        .messages-page .message-input {
            padding: 24px !important;
            background-color: #1a1b1e !important;
            border-top: 1px solid #2c2e33 !important;
            display: flex !important;
            gap: 12px !important;
            align-items: center !important;
            flex-shrink: 0 !important;
        }
        .messages-page .search-container {
            padding: 16px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }
        .messages-page .search-form {
            position: relative !important;
            width: 100% !important;
        }
        .messages-page .search-input {
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 14px 54px 14px 18px !important;
            border: 1.5px solid #36393f !important;
            border-radius: 9999px !important;
            font-size: 16px !important;
            background-color: #2c2e33 !important;
            color: #ffffff !important;
            transition: border-color 0.2s, background 0.2s;
        }
        .messages-page .search-input:focus {
            outline: none !important;
            border-color: #36393f !important;
            background-color: #36393f !important;
            box-shadow: none !important;
        }
        .messages-page .search-input::placeholder {
            color: #b9bbbe !important;
        }
        .messages-page .search-icon {
            position: absolute !important;
            right: 22px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #b9bbbe !important;
            font-size: 20px !important;
            pointer-events: none !important;
            display: flex !important;
            align-items: center !important;
            height: 100% !important;
        }
        .messages-page .section-title {
            padding: 16px !important;
            color: #b9bbbe !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }
        .messages-page .conversation, .messages-page .search-result {
            padding: 16px !important;
            margin: 8px 0 !important;
            cursor: pointer !important;
            border-radius: 12px !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            color: #ffffff !important;
            background-color: #2c2e33 !important;
            border: 1px solid #36393f !important;
        }
        .messages-page .conversation:hover, .messages-page .search-result:hover {
            background-color: #36393f !important;
            transform: translateY(-2px) !important;
        }
        .messages-page .conversation.active {
            background-color: #36393f !important;
            border-left: 3px solid #ea580c !important;
        }
        .messages-page .profile-picture {
            width: 48px !important;
            height: 48px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            background-color: #2c2e33 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #72767d !important;
            font-size: 24px !important;
        }
        .messages-page .profile-picture img {
            width: 100% !important;
            height: 100% !important;
            border-radius: 50% !important;
            object-fit: cover !important;
        }
        .messages-page .conversation-info {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 4px !important;
        }
        .messages-page .conversation-name {
            color: #ffffff !important;
            font-weight: 500 !important;
            font-size: 15px !important;
        }
        .messages-page .conversation-role {
            color: #b9bbbe !important;
            font-size: 13px !important;
        }
        .messages-page .message {
            max-width: 70% !important;
            padding: 14px 18px !important;
            border-radius: 16px !important;
            position: relative !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            margin-bottom: 8px !important;
        }
        .messages-page .message.sent {
            background-color: #ea580c !important;
            color: white !important;
            align-self: flex-end !important;
            border-bottom-right-radius: 4px !important;
        }
        .messages-page .message.received {
            background-color: #2c2e33 !important;
            color: #ffffff !important;
            align-self: flex-start !important;
            border-bottom-left-radius: 4px !important;
        }
        .messages-page .message-input textarea {
            flex: 1 !important;
            padding: 14px !important;
            border: 1px solid #2c2e33 !important;
            border-radius: 12px !important;
            resize: none !important;
            font-size: 14px !important;
            font-family: inherit !important;
            height: 48px !important;
            transition: all 0.3s ease !important;
            background-color: #2c2e33 !important;
            color: #ffffff !important;
        }
        .messages-page .message-input textarea:focus {
            outline: none !important;
            border-color: #ea580c !important;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.1) !important;
        }
        .messages-page .message-input textarea::placeholder {
            color: #72767d !important;
        }
        .messages-page .message-input button {
            padding: 14px 28px !important;
            background-color: #ea580c !important;
            color: white !important;
            border: none !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }
        .messages-page .message-input button:hover {
            background-color: #c44a0a !important;
            transform: translateY(-2px) !important;
        }
        .messages-page .conversation-header {
            padding: 20px !important;
            background-color: #1a1b1e !important;
            border-bottom: 1px solid #2c2e33 !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            color: #ffffff !important;
        }
        .messages-page .conversation-header .back-button {
            background: none !important;
            border: none !important;
            color: #ea580c !important;
            cursor: pointer !important;
            padding: 8px !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
            border-radius: 8px !important;
        }
        .messages-page .conversation-header .back-button:hover {
            background-color: #2c2e33 !important;
            color: #ffffff !important;
        }
        .messages-page .conversation-header .header-profile-picture {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            background-color: #2c2e33 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #72767d !important;
            font-size: 20px !important;
        }
        .messages-page .conversation-header .header-profile-picture img {
            width: 100% !important;
            height: 100% !important;
            border-radius: 50% !important;
            object-fit: cover !important;
        }
        .messages-page .conversation-header .header-info {
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
        }
        .messages-page .conversation-header .header-name {
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 16px !important;
        }
        .messages-page .conversation-header .header-role {
            color: #b9bbbe !important;
            font-size: 13px !important;
        }
        .messages-page .no-chat-selected {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            height: 100% !important;
            color: #b9bbbe !important;
            text-align: center !important;
            padding: 20px !important;
        }
        .messages-page .no-chat-selected i {
            font-size: 48px !important;
            margin-bottom: 15px !important;
            color: #ea580c !important;
        }
        .messages-page .no-results {
            color: #b9bbbe !important;
            text-align: center !important;
            padding: 20px !important;
            font-style: italic !important;
        }
        .messages-page .requests-section {
            margin-bottom: 20px !important;
            background-color: #1a1b1e !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            border: 1px solid #2c2e33 !important;
        }
        .messages-page .requests-header {
            padding: 15px !important;
            background-color: #2c2e33 !important;
            cursor: pointer !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            transition: background-color 0.2s ease !important;
        }
        .messages-page .requests-header:hover {
            background-color: #36393f !important;
        }
        .messages-page .header-content {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }
        .messages-page .header-content i {
            color: #ea580c !important;
            font-size: 1.2em !important;
        }
        .messages-page .header-content h3 {
            color: #ffffff !important;
            margin: 0 !important;
            font-size: 16px !important;
            font-weight: 600 !important;
        }
        .messages-page #toggleIcon {
            color: #b9bbbe !important;
            transition: transform 0.3s ease !important;
        }
        .messages-page .requests-container {
            max-height: 0 !important;
            overflow: hidden !important;
            transition: max-height 0.3s ease-out !important;
        }
        .messages-page .requests-container.show {
            max-height: 500px !important;
            overflow-y: auto !important;
        }
        .messages-page .request-card {
            padding: 15px !important;
            border-bottom: 1px solid #2c2e33 !important;
            transition: background-color 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
        }
        .messages-page .request-content {
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            flex: 1 !important;
        }
        .messages-page .request-actions {
            display: flex !important;
            gap: 8px !important;
            margin-left: auto !important;
        }
        .messages-page .btn-approve, .messages-page .btn-reject {
            padding: 8px 15px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
        }
        .messages-page .btn-approve {
            background-color: #ea580c !important;
            color: white !important;
        }
        .messages-page .btn-approve:hover {
            background-color: #c44a0a !important;
        }
        .messages-page .btn-reject {
            background-color: #dc3545 !important;
            color: white !important;
        }
        .messages-page .btn-reject:hover {
            background-color: #bb2d3b !important;
        }
        .messages-page .pending-request-banner {
            background-color: #2c2e33 !important;
            padding: 15px !important;
            border-bottom: 1px solid #36393f !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
        .messages-page .banner-text {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            color: #ffffff !important;
        }
        .messages-page .banner-text i {
            color: #ea580c !important;
        }
        .messages-page .banner-actions {
            display: flex !important;
            gap: 10px !important;
        }
        .messages-page .banner-actions .request-form {
            display: flex !important;
            gap: 10px !important;
        }
        @media (max-width: 768px) {
            .messages-container {
                margin-left: 0;
                margin-top: 32px;
            }
        }
        @media (max-width: 1100px) {
            .messages-page .logo-search-container {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px !important;
            }
            .messages-page .search-container {
                width: 100% !important;
                min-width: 0 !important;
            }
        }
        @media (max-width: 900px) {
            .messages-page header {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
                padding: 10px 5px !important;
            }
            .messages-page .logo-search-container {
                width: 100% !important;
                gap: 8px !important;
            }
            .messages-page .search-container {
                width: 100% !important;
                min-width: 0 !important;
            }
            .messages-page .nav-container {
                flex-direction: column !important;
                width: 100% !important;
                gap: 10px !important;
            }
            .messages-page nav ul {
                flex-direction: column !important;
                width: 100% !important;
                gap: 8px !important;
            }
            .messages-page nav ul li a {
                width: 100% !important;
                text-align: center !important;
            }
            .messages-page .cta-buttons {
                flex-direction: column !important;
                width: 100% !important;
                align-items: center !important;
                gap: 10px !important;
                margin-top: 10px !important;
            }
        }
        @media (max-width: 600px) {
            .messages-page header {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
                padding: 8px 2px !important;
            }
            .messages-page .logo-search-container {
                width: 100% !important;
                gap: 6px !important;
            }
            .messages-page .search-container {
                width: 100% !important;
                min-width: 0 !important;
            }
            .messages-page .search-container input {
                font-size: 0.85em !important;
                padding: 6px 36px 6px 10px !important;
                height: 38px !important;
            }
            .messages-page .search-container .search-icon {
                font-size: 1.1em !important;
                right: 10px !important;
                height: 20px !important;
                width: 20px !important;
            }
            .messages-page .nav-container {
                flex-direction: column !important;
                width: 100% !important;
                gap: 8px !important;
            }
            .messages-page nav ul {
                flex-direction: column !important;
                width: 100% !important;
                gap: 6px !important;
            }
            .messages-page nav ul li a {
                width: 100% !important;
                text-align: center !important;
                padding: 10px 8px !important;
            }
            .messages-page .cta-buttons {
                flex-direction: column !important;
                width: 100% !important;
                align-items: center !important;
                gap: 8px !important;
                margin-top: 8px !important;
            }
        }
        @media screen and (max-width: 768px) {
            .messages-page .nav-container {
                transform: translateX(100%) !important;
                transition: transform 0.3s ease !important;
            }
            .messages-page .nav-container.active {
                transform: translateX(0) !important;
            }
            .messages-page .dropdown-content {
                position: fixed !important;
                top: auto !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                max-height: 60vh !important;
                border-radius: 12px 12px 0 0 !important;
                transform: translateY(100%) !important;
                transition: transform 0.3s ease !important;
            }
            .messages-page .dropdown-container:hover .dropdown-content {
                transform: translateY(0) !important;
            }
            .messages-page .search-results {
                position: fixed !important;
                top: 60px !important;
                left: 0 !important;
                width: 100% !important;
                max-height: calc(100vh - 60px) !important;
                border-radius: 0 !important;
                z-index: 1001 !important;
            }
        }
        @media screen and (max-width: 480px) {
            .messages-page .nav-container {
                width: 100% !important;
            }
            .messages-page .dropdown-content {
                max-height: 70vh !important;
            }
            .messages-page .search-results {
                top: 50px !important;
                max-height: calc(100vh - 50px) !important;
            }
        }
        .messages-page .messages-sidebar .search-container {
            padding: 15px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .messages-page .messages-sidebar .search-form {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            width: 100% !important;
        }
        .messages-page .messages-sidebar .search-input {
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 12px !important;
            border: 1px solid #40444B !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            background-color: #2C2F33 !important;
            color: #FFFFFF !important;
        }
        .messages-page .messages-sidebar .search-input:focus {
            outline: none !important;
            border-color: #7289DA !important;
            box-shadow: 0 0 0 2px rgba(114, 137, 218, 0.1) !important;
        }
        .messages-page .messages-sidebar .search-input::placeholder {
            color: #72767D !important;
        }
        .messages-page .messages-sidebar .search-button {
            width: 100% !important;
            padding: 12px !important;
            background-color: #7289DA !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }
        .messages-page .messages-sidebar .search-button:hover {
            background-color: #5b6eae !important;
            transform: translateY(-2px) !important;
        }
        .chat-info-sidebar {
            position: fixed;
            top: 0;
            right: -370px;
            width: 340px;
            height: 100vh;
            background: #18191c;
            color: #fff;
            box-shadow: -2px 0 16px rgba(0,0,0,0.18);
            z-index: 2000;
            transition: right 0.3s cubic-bezier(.4,0,.2,1);
            display: flex;
            flex-direction: column;
        }
        .chat-info-sidebar.open {
            right: 0;
        }
        .chat-info-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px 10px 22px;
            font-size: 1.1em;
            font-weight: 600;
            border-bottom: 1px solid #232428;
            background: #1a1b1e;
        }
        .chat-info-content {
            flex: 1;
            overflow-y: auto;
            padding: 18px 22px;
        }
        .chat-info-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 18px;
        }
        .chat-info-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #232428;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .chat-info-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .chat-info-avatar i {
            font-size: 2.5em;
            color: #72767d;
        }
        .chat-info-name {
            font-size: 1.15em;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .chat-info-role {
            font-size: 0.98em;
            color: #ea580c;
            margin-bottom: 8px;
        }
        .chat-info-divider {
            border: none;
            border-top: 1px solid #232428;
            margin: 12px 0;
        }
        .chat-info-section {
            margin-bottom: 18px;
        }
        .chat-info-section-title {
            font-size: 1em;
            font-weight: 500;
            color: #ea580c;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .chat-info-media-list {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .chat-info-files-list {
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 900px) {
            .chat-info-sidebar {
                width: 100vw;
                right: -100vw;
            }
            .chat-info-sidebar.open {
                right: 0;
            }
        }
        .chat-info-actions {
            display: flex;
            gap: 12px;
            margin: 10px 0 0 0;
            justify-content: center;
        }
        .chat-info-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: #ea580c;
            font-size: 1.2em;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.2s;
            min-width: 48px;
        }
        .chat-info-action-btn span {
            font-size: 0.82em;
            color: #fff;
            margin-top: 2px;
            font-weight: 400;
            letter-spacing: 0.01em;
        }
        .chat-info-action-btn:hover, .chat-info-action-btn:focus {
            color: #fff;
        }
        .modal {background:rgba(0,0,0,0.8);position:fixed;top:0;left:0;right:0;bottom:0;z-index:3000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(5px);}
        .modal-content {box-shadow:0 8px 32px rgba(0,0,0,0.3);}
        .close-modal:hover {transform:rotate(90deg);}
    </style>
</head>

<body class="messages-page" style="padding-top: 120px !important;">
    <?php $active_section = 'messages'; ?>
    <div class="messages-container">
        <!-- Sidebar -->
        <div class="messages-sidebar">
            <div class="search-container">
                <form method="GET" action="messages.php" class="search-form">
                    <input type="text" name="search" placeholder="Search users..." class="search-input" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                </form>
            </div>

            <div class="conversations-container">
                <?php if (isset($_GET['search']) && $_GET['search']): ?>
                    <h5 class="section-title">Search Results</h5>
                    <?php if ($search_results && $search_results->num_rows > 0): ?>
                        <?php while ($user = $search_results->fetch_assoc()): ?>
                            <div class="search-result" onclick="window.location.href='messages.php?chat_with=<?php echo $user['user_id']; ?>'">
                                <div class="profile-picture">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="conversation-info">
                                    <span class="conversation-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                    <span class="conversation-role"><?php echo formatRole($user['role']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-results">No users found</p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($pending_requests->num_rows > 0): ?>
                    <div class="requests-section">
                        <div class="requests-header" onclick="toggleRequests()">
                            <div class="header-content">
                                <i class="fas fa-envelope-open-text"></i>
                                <h3>Message Requests (<?php echo $pending_requests->num_rows; ?>)</h3>
                            </div>
                            <i class="fas fa-chevron-down" id="toggleIcon"></i>
                        </div>
                        <div class="requests-container" id="requestsContainer">
                            <?php while ($request = $pending_requests->fetch_assoc()): ?>
                                <div class="request-card">
                                    <div class="request-content" onclick="window.location.href='messages.php?chat_with=<?php echo $request['sender_id']; ?>'">
                                        <div class="profile-picture">
                                            <?php if (!empty($request['profile_picture'])): ?>
                                                <img src="<?php echo htmlspecialchars($request['profile_picture']); ?>" alt="Profile">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="conversation-info">
                                            <span class="conversation-name"><?php echo htmlspecialchars($request['sender_name']); ?></span>
                                            <span class="conversation-role"><?php echo formatRole($request['sender_role']); ?></span>
                                        </div>
                                    </div>
                                    <form method="POST" class="request-actions">
                                        <input type="hidden" name="sender_id" value="<?php echo $request['sender_id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn-approve">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn-reject">
                                            <i class="fas fa-times"></i> Decline
                                        </button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add Archived toggle above conversation list -->
                <div style="display:flex;align-items:center;gap:10px;padding:0 16px 8px 16px;">
                    <?php if ($show_archived): ?>
                        <a href="messages.php" class="back-to-inbox" style="background:none;border:none;color:#ea580c;font-size:1.1em;cursor:pointer;display:flex;align-items:center;gap:4px;padding:4px 8px;border-radius:4px;transition:all 0.2s ease;text-decoration:none;">
                            <i class="fas fa-arrow-left"></i>
                            <span style="font-size:0.9em;">Back to Inbox</span>
                        </a>
                    <?php else: ?>
                        <a href="messages.php?archived=1" class="view-archived" style="background:none;border:none;color:#ea580c;font-size:1.1em;cursor:pointer;display:flex;align-items:center;gap:4px;padding:4px 8px;border-radius:4px;transition:all 0.2s ease;text-decoration:none;">
                            <i class="fas fa-archive"></i>
                            <span style="font-size:0.9em;">Archived</span>
                        </a>
                    <?php endif; ?>
                </div>

                <h5 class="section-title" style="display:flex;align-items:center;gap:8px;">
                    <?php if ($show_archived): ?>
                        <i class="fas fa-archive" style="color:#ea580c;"></i>
                        Archived Conversations
                    <?php else: ?>
                        <i class="fas fa-inbox" style="color:#ea580c;"></i>
                        Inbox
                    <?php endif; ?>
                </h5>

                <?php while ($conversation = $conversations_result->fetch_assoc()): ?>
                    <div class="conversation conversation-item <?php echo ($chat_with == $conversation['user_id']) ? 'active' : ''; ?>" 
                         onclick="window.location.href='messages.php?chat_with=<?php echo $conversation['user_id']; ?>'">
                        <div class="profile-picture">
                            <?php if (!empty($conversation['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($conversation['profile_picture']); ?>" alt="Profile">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="conversation-info">
                            <span class="conversation-name"><?php echo htmlspecialchars($conversation['name']); ?></span>
                            <span class="conversation-role"><?php echo formatRole($conversation['role']); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($chat_with && $chat_user): ?>
                <div class="conversation-header" style="position:relative;">
                    <button onclick="window.location.href='messages.php'" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <div class="header-profile-picture">
                        <?php if (!empty($chat_user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($chat_user['profile_picture']); ?>" alt="Profile">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="header-info">
                        <span class="header-name"><?php echo htmlspecialchars($chat_user['name']); ?></span>
                        <span class="header-role"><?php echo formatRole($chat_user['role']); ?></span>
                    </div>
                    <!-- Chat Info Toggle Button -->
                    <button id="toggleChatInfo" style="position:absolute; right:18px; top:50%; transform:translateY(-50%); background:none; border:none; color:#ea580c; font-size:22px; cursor:pointer;">
                        <i class="fas fa-info-circle"></i>
                    </button>
                </div>

                <!-- Chat Info Sidebar -->
                <div id="chatInfoSidebar" class="chat-info-sidebar">
                    <div class="chat-info-header">
                        <span>Chat Info</span>
                        <button id="closeChatInfo" style="background:none; border:none; color:#ea580c; font-size:22px; cursor:pointer;"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="chat-info-content">
                        <div class="chat-info-profile">
                            <div class="chat-info-avatar">
                                <?php if (!empty($chat_user['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($chat_user['profile_picture']); ?>" alt="Profile">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="chat-info-name"><?php echo htmlspecialchars($chat_user['name']); ?></div>
                            <div class="chat-info-role"><?php echo formatRole($chat_user['role']); ?></div>
                            <div class="chat-info-actions">
                                <a href="profile.php?user_id=<?php echo $chat_with; ?>" class="chat-info-action-btn" title="View Profile">
                                    <i class="fas fa-user-circle"></i>
                                    <span>Profile</span>
                                </a>
                                <button class="chat-info-action-btn" id="muteBtn"
                                    title="<?php echo $mute_state ? 'Unmute' : 'Mute'; ?>"
                                    style="background:none; border:none;<?php if($mute_state): ?>color:#fff;background:#ea580c22;<?php endif; ?>"
                                    onclick="toggleMute(<?php echo $chat_with; ?>, this)">
                                    <i class="fas <?php echo $mute_state ? 'fa-bell' : 'fa-bell-slash'; ?>"></i>
                                    <span><?php echo $mute_state ? 'Unmute' : 'Mute'; ?></span>
                                </button>
                                <button class="chat-info-action-btn" id="archiveBtn"
                                    title="<?php echo $archive_state ? 'Unarchive' : 'Archive'; ?>"
                                    style="background:none; border:none;<?php if($archive_state): ?>color:#fff;background:#ea580c22;<?php endif; ?>"
                                    onclick="toggleArchive(<?php echo $chat_with; ?>, this)">
                                    <i class="fas <?php echo $archive_state ? 'fa-inbox' : 'fa-archive'; ?>"></i>
                                    <span><?php echo $archive_state ? 'Unarchive' : 'Archive'; ?></span>
                                </button>
                                <a href="#" class="chat-info-action-btn" title="Report this user to admin (bug/abuse/feature)" onclick="openReportModal();return false;">
                                    <i class="fas fa-flag"></i>
                                    <span>Report</span>
                                </a>
                            </div>
                        </div>
                        <hr class="chat-info-divider">
                        <div class="chat-info-section">
                            <div class="chat-info-section-title"><i class="fas fa-image"></i> Media</div>
                            <div class="chat-info-media-list">
                                <?php
                                // Show up to 6 recent images
                                $media_query = $conn->prepare("SELECT mf.* FROM Message_Files mf JOIN Messages m ON mf.message_id = m.message_id WHERE (m.sender_id = ? OR m.receiver_id = ?) AND mf.file_name REGEXP '\\.(jpg|jpeg|png|gif)$' ORDER BY mf.file_id DESC LIMIT 6");
                                $media_query->bind_param('ii', $user_id, $chat_with);
                                $media_query->execute();
                                $media_result = $media_query->get_result();
                                while ($media = $media_result->fetch_assoc()):
                                    if (preg_match('/^image\//', mime_content_type($media['file_path']))): ?>
                                        <a href="download.php?file_id=<?php echo $media['file_id']; ?>" target="_blank">
                                            <img src="<?php echo $media['file_path']; ?>" alt="Media" style="max-width:60px; max-height:60px; border-radius:6px; margin:2px; border:1.5px solid #2c2e33;">
                                        </a>
                                <?php endif; endwhile; ?>
                            </div>
                        </div>
                        <div class="chat-info-section">
                            <div class="chat-info-section-title"><i class="fas fa-file"></i> Files</div>
                            <div class="chat-info-files-list">
                                <?php
                                // Show up to 6 recent files (non-images)
                                $file_query = $conn->prepare("SELECT mf.* FROM Message_Files mf JOIN Messages m ON mf.message_id = m.message_id WHERE (m.sender_id = ? OR m.receiver_id = ?) AND mf.file_name NOT REGEXP '\\.(jpg|jpeg|png|gif)$' ORDER BY mf.file_id DESC LIMIT 6");
                                $file_query->bind_param('ii', $user_id, $chat_with);
                                $file_query->execute();
                                $file_result = $file_query->get_result();
                                while ($file = $file_result->fetch_assoc()): ?>
                                    <a href="download.php?file_id=<?php echo $file['file_id']; ?>" target="_blank" style="display:block; color:#ea580c; margin-bottom:4px;">
                                        <i class="fas fa-file"></i> <?php echo htmlspecialchars($file['file_name']); ?>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="messages">
                    <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo ($message['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                            <?php echo htmlspecialchars($message['content']); ?>
                            <?php
                            // Fetch files for this message
                            $file_query = $conn->prepare("SELECT * FROM Message_Files WHERE message_id = ?");
                            $file_query->bind_param("i", $message['message_id']);
                            $file_query->execute();
                            $file_result = $file_query->get_result();
                            $files = $file_result->fetch_all(MYSQLI_ASSOC);
                            ?>
                            <?php if (!empty($files)): ?>
                                <div class="message-files">
                                    <?php foreach ($files as $file): ?>
                                        <?php if (preg_match('/^image\//', mime_content_type($file['file_path']))): ?>
                                            <a href="download.php?file_id=<?php echo $file['file_id']; ?>" target="_blank">
                                                <img src="<?php echo $file['file_path']; ?>" alt="Image" style="max-width:180px; max-height:180px; display:block; border-radius:8px; margin-bottom:6px;" />
                                            </a>
                                        <?php endif; ?>
                                        <a href="download.php?file_id=<?php echo $file['file_id']; ?>" target="_blank">
                                            <i class="fas fa-file"></i> <?php echo htmlspecialchars($file['file_name']); ?>
                                        </a><br>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!$has_pending_request): ?>
                    <form method="POST" class="message-input" enctype="multipart/form-data" id="message-form">
                        <textarea name="message" placeholder="Type a message..." id="message-textarea"></textarea>
                        <input type="hidden" name="receiver_id" value="<?php echo $chat_with; ?>">
                        <label for="file-upload" style="cursor:pointer; margin-right:10px;">
                            <i class="fas fa-paperclip"></i>
                            <input type="file" name="file" id="file-upload" style="display:none;" onchange="showFilePreview(event)">
                        </label>
                        <button type="submit">Send</button>
                    </form>
                    <div id="file-preview" style="margin-left:24px; margin-bottom:10px;"></div>
                    <script>
                    function showFilePreview(event) {
                        const preview = document.getElementById('file-preview');
                        preview.innerHTML = '';
                        const file = event.target.files[0];
                        if (!file) return;
                        const fileName = document.createElement('div');
                        fileName.textContent = 'Selected file: ' + file.name;
                        preview.appendChild(fileName);
                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.style.maxWidth = '120px';
                            img.style.maxHeight = '120px';
                            img.style.display = 'block';
                            img.style.marginTop = '8px';
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                img.src = e.target.result;
                            };
                            reader.readAsDataURL(file);
                            preview.appendChild(img);
                        }
                    }
                    </script>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-chat-selected">
                    <i class="fas fa-comments"></i>
                    <p>Select a conversation or search for a user to start chatting</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="reportModal" class="modal" style="display:none;">
        <div class="modal-content" style="background:#23272a; color:#e4e6eb; border-radius:16px; max-width:400px; width:95%; margin:auto; padding:32px; position:relative;">
            <span class="close-modal" onclick="closeReportModal()" style="position:absolute;top:16px;right:24px;font-size:2rem;color:#ea580c;cursor:pointer;">&times;</span>
            <h2 style="color:#ea580c; font-size:1.3em; margin-bottom:18px;">Report User</h2>
            <form method="POST" action="process_ticket.php" class="ticket-form" onsubmit="return validateReportForm()">
                <input type="hidden" name="reported_user_id" value="<?php echo $chat_with; ?>">
                <div class="form-group" style="margin-bottom:14px;">
                    <label for="ticket_title" style="font-size:0.98em;">Title</label>
                    <input type="text" id="ticket_title" name="ticket_title" required class="form-control" placeholder="Brief description..." style="width:100%;padding:10px;border-radius:6px;border:1px solid #404040;background:#2d2d2d;color:#fff;">
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label for="ticket_type" style="font-size:0.98em;">Type</label>
                    <select id="ticket_type" name="ticket_type" required style="width:100%;padding:10px;border-radius:6px;background:#2d2d2d;color:#fff;border:1px solid #404040;">
                        <option value="bug">Bug Report</option>
                        <option value="feature">Feature Suggestion</option>
                        <option value="abuse">Abuse/Harassment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:18px;">
                    <label for="ticket_description" style="font-size:0.98em;">Description</label>
                    <textarea id="ticket_description" name="ticket_description" required placeholder="Please provide details..." style="width:100%;padding:10px;border-radius:6px;background:#2d2d2d;color:#fff;border:1px solid #404040;min-height:80px;"></textarea>
                </div>
                <button type="submit" name="submit_ticket" class="btn" style="background:#ea580c;color:#fff;border:none;padding:10px 24px;border-radius:6px;font-weight:500;">Submit Report</button>
            </form>
        </div>
    </div>

    <script>
        function toggleRequests() {
            const container = document.getElementById('requestsContainer');
            const icon = document.getElementById('toggleIcon');
            container.classList.toggle('show');
            icon.style.transform = container.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0)';
        }

        // Add Enter key functionality to send messages
        document.addEventListener('DOMContentLoaded', function() {
            const messageTextarea = document.getElementById('message-textarea');
            const messageForm = document.getElementById('message-form');
            
            if (messageTextarea && messageForm) {
                messageTextarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault(); // Prevent default Enter behavior (new line)
                        
                        // Check if there's text or a file selected
                        const messageText = messageTextarea.value.trim();
                        const fileInput = document.getElementById('file-upload');
                        const hasFile = fileInput && fileInput.files.length > 0;
                        
                        if (messageText || hasFile) {
                            messageForm.submit();
                        }
                    }
                });
            }
        });

        // Show requests by default when there are pending requests
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('requestsContainer');
            const icon = document.getElementById('toggleIcon');
            if (container) {
                container.classList.add('show');
                icon.style.transform = 'rotate(180deg)';
            }
        });

        const chatInfoSidebar = document.getElementById('chatInfoSidebar');
        const toggleChatInfo = document.getElementById('toggleChatInfo');
        const closeChatInfo = document.getElementById('closeChatInfo');
        toggleChatInfo.addEventListener('click', function(e) {
            e.stopPropagation();
            chatInfoSidebar.classList.add('open');
        });
        closeChatInfo.addEventListener('click', function() {
            chatInfoSidebar.classList.remove('open');
        });
        // Optional: close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (!chatInfoSidebar.contains(e.target) && !toggleChatInfo.contains(e.target)) {
                chatInfoSidebar.classList.remove('open');
            }
        });

        function openReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
        }
        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
        }
        function validateReportForm() {
            var title = document.getElementById('ticket_title').value.trim();
            var desc = document.getElementById('ticket_description').value.trim();
            if (!title || !desc) return false;
            return true;
        }
        // Close modal on outside click
        window.addEventListener('click', function(e) {
            var modal = document.getElementById('reportModal');
            if (e.target === modal) closeReportModal();
        });

        function toggleMute(otherUserId, btn) {
            const isMuted = btn.style.color === 'rgb(255, 255, 255)';
            fetch('mute_conversation.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'other_user_id=' + encodeURIComponent(otherUserId) + '&mute=' + (isMuted ? 0 : 1)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.muted) {
                        btn.style.color = '#fff';
                        btn.style.background = '#ea580c22';
                        btn.querySelector('i').className = 'fas fa-bell';
                        btn.querySelector('span').textContent = 'Unmute';
                        btn.title = 'Unmute';
                    } else {
                        btn.style.color = '#ea580c';
                        btn.style.background = 'none';
                        btn.querySelector('i').className = 'fas fa-bell-slash';
                        btn.querySelector('span').textContent = 'Mute';
                        btn.title = 'Mute';
                    }
                }
            });
        }
        function toggleArchive(otherUserId, btn) {
            const isArchived = btn.style.color === 'rgb(255, 255, 255)';
            
            // Find the conversation item in the sidebar by searching for the matching onclick attribute
            const conversationItems = document.querySelectorAll('.conversation-item');
            let targetConversationItem = null;
            
            conversationItems.forEach(item => {
                const onclick = item.getAttribute('onclick');
                if (onclick && onclick.includes('chat_with=' + otherUserId)) {
                    targetConversationItem = item;
                }
            });
            
            fetch('archive_conversation.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'other_user_id=' + encodeURIComponent(otherUserId) + '&archive=' + (isArchived ? 0 : 1)
            })
            .then(res => res.json())
            .then(data => {
                console.log('Archive response:', data); // Debug logging
                
                if (data.success) {
                    // Verify the operation was successful
                    if (data.verified_archived !== data.archived) {
                        console.error('Archive verification failed!', data);
                        throw new Error('Archive operation verification failed');
                    }
                    
                    if (data.archived) {
                        // Update button appearance first
                        btn.style.color = '#fff';
                        btn.style.background = '#ea580c22';
                        btn.querySelector('i').className = 'fas fa-inbox';
                        btn.querySelector('span').textContent = 'Unarchive';
                        btn.title = 'Unarchive';
                        
                        // Remove from conversation list with animation
                        if (targetConversationItem) {
                            targetConversationItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            targetConversationItem.style.opacity = '0';
                            targetConversationItem.style.transform = 'translateX(-100%)';
                            setTimeout(() => {
                                targetConversationItem.remove();
                            }, 300);
                        }
                        
                        // Show success message and redirect after a short delay
                        const successMsg = document.createElement('div');
                        successMsg.className = 'success-message';
                        successMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:12px 20px;border-radius:8px;z-index:1000;';
                        successMsg.textContent = 'Conversation archived successfully ✓';
                        document.body.appendChild(successMsg);
                        setTimeout(() => successMsg.remove(), 3000);
                        
                        // Redirect to main messages page after archiving
                        setTimeout(() => {
                            window.location.href = 'messages.php';
                        }, 1500);
                        
                    } else {
                        // If unarchiving, update button appearance
                        btn.style.color = '#ea580c';
                        btn.style.background = 'none';
                        btn.querySelector('i').className = 'fas fa-archive';
                        btn.querySelector('span').textContent = 'Archive';
                        btn.title = 'Archive';
                        
                        // Remove from archived list if we're in archived view
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('archived') === '1' && targetConversationItem) {
                            targetConversationItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            targetConversationItem.style.opacity = '0';
                            targetConversationItem.style.transform = 'translateX(-100%)';
                            setTimeout(() => targetConversationItem.remove(), 300);
                            
                            // Redirect to main messages page after unarchiving from archived view
                            setTimeout(() => {
                                window.location.href = 'messages.php';
                            }, 1500);
                        }
                        
                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'success-message';
                        successMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#4CAF50;color:white;padding:12px 20px;border-radius:8px;z-index:1000;';
                        successMsg.textContent = 'Conversation unarchived successfully ✓';
                        document.body.appendChild(successMsg);
                        setTimeout(() => successMsg.remove(), 3000);
                    }
                } else {
                    throw new Error(data.error || 'Archive operation failed');
                }
            })
            .catch(error => {
                console.error('Archive operation failed:', error);
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:#f44336;color:white;padding:12px 20px;border-radius:8px;z-index:1000;';
                errorMsg.textContent = 'Failed to archive conversation: ' + error.message;
                document.body.appendChild(errorMsg);
                setTimeout(() => errorMsg.remove(), 5000);
            });
        }

        // Add form submission handler for message sending
        document.addEventListener('DOMContentLoaded', function() {
            const messageForm = document.getElementById('message-form');
            if (messageForm && !messageForm.dataset.handlerAdded) {
                messageForm.dataset.handlerAdded = 'true';
                messageForm.addEventListener('submit', function(e) {
                    // Let the form submit normally, but check if we need to refresh badges
                    setTimeout(() => {
                        // Refresh badge counts after a short delay to allow for server processing
                        if (typeof updateMessageBadgeCount === 'function') {
                            updateMessageBadgeCount();
                        } else {
                            // If the function doesn't exist, try to reload the page badges
                            fetch('get_unread_count.php')
                                .then(response => response.json())
                                .then(data => {
                                    const badgeElements = document.querySelectorAll('.badge');
                                    badgeElements.forEach(badge => {
                                        if (badge.closest('.fa-envelope')) {
                                            if (data.unread_count > 0) {
                                                badge.textContent = data.unread_count;
                                            } else {
                                                badge.remove();
                                            }
                                        }
                                    });
                                })
                                .catch(error => console.log('Badge update error:', error));
                        }
                    }, 1000);
                });
            }
        });
    </script>
</body>

</html>
