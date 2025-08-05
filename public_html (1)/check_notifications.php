<?php
include 'db_connection.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: sign_in.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check the Notifications table structure
$table_structure_query = "DESCRIBE Notifications";
$table_structure_result = mysqli_query($conn, $table_structure_query);

echo "<h2>Notifications Table Structure</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($table_structure_result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check for recent notifications
$recent_notifications_query = "SELECT * FROM Notifications ORDER BY created_at DESC LIMIT 20";
$recent_notifications_result = mysqli_query($conn, $recent_notifications_query);

echo "<h2>Recent Notifications</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>User ID</th><th>Sender ID</th><th>Type</th><th>Message</th><th>Status</th><th>Created At</th></tr>";

while ($row = mysqli_fetch_assoc($recent_notifications_result)) {
    echo "<tr>";
    echo "<td>" . $row['notification_id'] . "</td>";
    echo "<td>" . $row['user_id'] . "</td>";
    echo "<td>" . ($row['sender_id'] ? $row['sender_id'] : 'NULL') . "</td>";
    echo "<td>" . $row['type'] . "</td>";
    echo "<td>" . htmlspecialchars($row['message']) . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check for recent matches
$recent_matches_query = "SELECT * FROM Matches ORDER BY created_at DESC LIMIT 20";
$recent_matches_result = mysqli_query($conn, $recent_matches_query);

echo "<h2>Recent Matches</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Investor ID</th><th>Startup ID</th><th>Created At</th></tr>";

while ($row = mysqli_fetch_assoc($recent_matches_result)) {
    echo "<tr>";
    echo "<td>" . $row['match_id'] . "</td>";
    echo "<td>" . $row['investor_id'] . "</td>";
    echo "<td>" . $row['startup_id'] . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check for any errors in the error log
echo "<h2>Recent Error Log Entries</h2>";
echo "<pre>";
$error_log_path = ini_get('error_log');
if (file_exists($error_log_path)) {
    $error_log_content = file_get_contents($error_log_path);
    $error_log_lines = explode("\n", $error_log_content);
    $recent_error_log_lines = array_slice($error_log_lines, -20);
    echo implode("\n", $recent_error_log_lines);
} else {
    echo "Error log file not found at: $error_log_path";
}
echo "</pre>";
?> 