<?php
include 'db_connection.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: sign_in.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get a list of users to send a test notification to
$users_query = "SELECT user_id, name, role FROM Users LIMIT 10";
$users_result = mysqli_query($conn, $users_query);
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $target_user_id = $_POST['user_id'];
    $notification_message = $_POST['message'];
    $notification_type = $_POST['type'];
    
    // Insert the test notification
    $insert_notification_query = "
        INSERT INTO Notifications (user_id, sender_id, type, message) 
        VALUES ('$target_user_id', '$user_id', '$notification_type', '$notification_message')";
    
    $result = mysqli_query($conn, $insert_notification_query);
    
    if ($result) {
        $success_message = "Test notification sent successfully!";
    } else {
        $error_message = "Error sending notification: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Notification System</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Notification System</h1>
        
        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="user_id">Select User to Notify:</label>
                <select name="user_id" id="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['name']) . ' (' . $user['role'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="type">Notification Type:</label>
                <select name="type" id="type" required>
                    <option value="system_alert">System Alert</option>
                    <option value="investment_match">Investment Match</option>
                    <option value="application_status">Application Status</option>
                    <option value="job_offer">Job Offer</option>
                    <option value="startup_status">Startup Status</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="message">Notification Message:</label>
                <textarea name="message" id="message" rows="4" required>This is a test notification.</textarea>
            </div>
            
            <button type="submit" name="send_notification">Send Test Notification</button>
        </form>
        
        <h2>Recent Notifications</h2>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Type</th>
                <th>Message</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
            <?php
            $recent_notifications_query = "SELECT * FROM Notifications ORDER BY created_at DESC LIMIT 10";
            $recent_notifications_result = mysqli_query($conn, $recent_notifications_query);
            
            while ($row = mysqli_fetch_assoc($recent_notifications_result)) {
                echo "<tr>";
                echo "<td>" . $row['notification_id'] . "</td>";
                echo "<td>" . $row['user_id'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . htmlspecialchars($row['message']) . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html> 