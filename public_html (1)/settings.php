<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle privacy settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_privacy'])) {
    $show_in_search = isset($_POST['show_in_search']) ? 1 : 0;
    $show_in_messages = isset($_POST['show_in_messages']) ? 1 : 0;
    $show_in_pages = isset($_POST['show_in_pages']) ? 1 : 0;

    $sql = "UPDATE Users SET 
            show_in_search = ?, 
            show_in_messages = ?, 
            show_in_pages = ? 
            WHERE user_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $show_in_search, $show_in_messages, $show_in_pages, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Privacy settings updated successfully!";
    } else {
        $error_message = "Error updating privacy settings.";
    }
    mysqli_stmt_close($stmt);
}

// Handle ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $title = mysqli_real_escape_string($conn, $_POST['ticket_title']);
    $description = mysqli_real_escape_string($conn, $_POST['ticket_description']);
    $type = mysqli_real_escape_string($conn, $_POST['ticket_type']);
    $status = 'pending';

    $sql = "INSERT INTO Tickets (user_id, title, description, type, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $title, $description, $type, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Ticket submitted successfully!";
    } else {
        $error_message = "Error submitting ticket.";
    }
    mysqli_stmt_close($stmt);
}

// Get current privacy settings
$sql = "SELECT show_in_search, show_in_messages, show_in_pages FROM Users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$privacy_settings = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Kapital</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #18191a;
            min-height: 100vh;
        }
        .settings-flex-wrapper {
            display: flex;
            min-height: calc(100vh - 70px); /* adjust 70px to your navbar height if needed */
            background: #18191a;
            margin-top: 88px; /* Add space below navbar */
        }
        .settings-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .settings-section {
            background: #23272a;
            border-radius: 14px;
            padding: 28px 24px 24px 24px;
            margin-bottom: 32px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            border: 1px solid #292b2f;
        }
        .settings-section h2 {
            color: #fff;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ea580c;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #e4e6eb;
            font-weight: 500;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
        }
        .checkbox-group label {
            color: #e4e6eb;
            margin-bottom: 0;
        }
        .ticket-form {
            width: 100%;
        }
        .ticket-form input[type="text"],
        .ticket-form select,
        .ticket-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #3a3b3c;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background-color: #18191a;
            color: #e4e6eb;
        }
        .ticket-form input[type="text"]:focus,
        .ticket-form select:focus,
        .ticket-form textarea:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.1);
        }
        .ticket-form input[type="text"] {
            height: 45px;
        }
        .ticket-form select {
            height: 45px;
            background-color: #18191a;
            cursor: pointer;
            color: #e4e6eb;
        }
        .ticket-form textarea {
            min-height: 150px;
            max-height: 300px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.5;
        }
        .ticket-form input[type="text"]::placeholder,
        .ticket-form textarea::placeholder {
            color: #8b949e;
        }
        .btn {
            background-color: #ea580c;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 15px;
            display: inline-block;
        }
        .btn:hover {
            background-color: #c44a0a;
            transform: translateY(-1px);
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #1a472a;
            color: #4ade80;
            border: 1px solid #2d5a3f;
        }
        .error {
            background-color: #471a1a;
            color: #f87171;
            border: 1px solid #5a2d2d;
        }
        @media (max-width: 900px) {
            .settings-flex-wrapper {
                flex-direction: column;
            }
            .settings-container {
                margin: 20px;
                padding: 15px;
            }
        }
        @media (max-width: 700px) {
            .settings-flex-wrapper {
                flex-direction: column;
            }
            .settings-container {
                margin: 10px;
                padding: 10px;
            }
        }
        .main-content {
            margin-left: 260px;
        }
        @media (max-width: 900px) {
            .main-content {
                margin-left: 70px;
            }
        }
        @media (max-width: 700px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="settings-flex-wrapper">
        <?php include 'sidebar.php'; ?>
        <div style="flex: 1; display: flex; justify-content: center; align-items: flex-start;">
            <div class="settings-container">
                <?php if ($success_message): ?>
                    <div class="message success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <!-- Privacy Settings Section -->
                <div class="settings-section">
                    <h2>Privacy Settings</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Profile Visibility</label>
                            <div class="checkbox-group">
                                <input type="checkbox" name="show_in_search" id="show_in_search" 
                                    <?php echo $privacy_settings['show_in_search'] ? 'checked' : ''; ?>>
                                <label for="show_in_search">Show profile in search results</label>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" name="show_in_messages" id="show_in_messages" 
                                    <?php echo $privacy_settings['show_in_messages'] ? 'checked' : ''; ?>>
                                <label for="show_in_messages">Show profile in messages search</label>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" name="show_in_pages" id="show_in_pages" 
                                    <?php echo $privacy_settings['show_in_pages'] ? 'checked' : ''; ?>>
                                <label for="show_in_pages">Show my profile information on startup detail pages and other public pages</label>
                            </div>
                        </div>
                        <button type="submit" name="update_privacy" class="btn">Update Privacy Settings</button>
                    </form>
                </div>
                <!-- Ticket Submission Section -->
                <div class="settings-section">
                    <h2>Report Bug or Suggest Feature</h2>
                    <form method="POST" action="" class="ticket-form">
                        <div class="form-group">
                            <label for="ticket_title">Title</label>
                            <input type="text" id="ticket_title" name="ticket_title" required 
                                class="form-control" placeholder="Brief description of the issue or suggestion">
                        </div>
                        <div class="form-group">
                            <label for="ticket_type">Type</label>
                            <select id="ticket_type" name="ticket_type" required>
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Suggestion</option>
                                <option value="improvement">Improvement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ticket_description">Description</label>
                            <textarea id="ticket_description" name="ticket_description" required 
                                    placeholder="Please provide detailed information about the issue or suggestion..."></textarea>
                        </div>
                        <button type="submit" name="submit_ticket" class="btn">Submit Ticket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 