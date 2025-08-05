<?php
include 'db_connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign_in.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all notifications for the current user
$stmt_notifications = $conn->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt_notifications->bind_param('i', $user_id);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();
$notifications = $result_notifications->fetch_all(MYSQLI_ASSOC);

// Count unread notifications
$notification_count = count(array_filter($notifications, function($notification) {
    return $notification['status'] == 'unread';
}));

// Get user information
$user_query = "SELECT name, role FROM Users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Navbar Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .notification-item {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .notification-item.unread {
            background-color: #f0f8ff;
            border-left: 4px solid #4CAF50;
        }
        .notification-item.read {
            background-color: #f9f9f9;
            border-left: 4px solid #ddd;
        }
        .badge {
            background-color: #4CAF50;
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            font-size: 12px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Check Navbar Notifications</h1>
        
        <div class="user-info">
            <h2>User Information</h2>
            <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>
        
        <div class="notification-count">
            <h2>Notification Count</h2>
            <p>Total Notifications: <?php echo count($notifications); ?></p>
            <p>Unread Notifications: <?php echo $notification_count; ?></p>
        </div>
        
        <div class="notifications-list">
            <h2>Notifications List</h2>
            
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo ($notification['status'] == 'unread') ? 'unread' : 'read'; ?>">
                        <p><strong>ID:</strong> <?php echo $notification['notification_id']; ?></p>
                        <p><strong>Type:</strong> <?php echo $notification['type']; ?></p>
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($notification['message']); ?></p>
                        <p><strong>Status:</strong> <?php echo $notification['status']; ?></p>
                        <p><strong>Created At:</strong> <?php echo $notification['created_at']; ?></p>
                        <p><strong>URL:</strong> <?php echo $notification['url'] ? htmlspecialchars($notification['url']) : 'None'; ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No notifications found.</p>
            <?php endif; ?>
        </div>
        
        <div class="navbar-simulation">
            <h2>Navbar Simulation</h2>
            <div style="background-color: #333; color: white; padding: 10px; border-radius: 4px;">
                <span>Notifications</span>
                <?php if ($notification_count > 0): ?>
                    <span class="badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 