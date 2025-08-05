<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db_connection.php';

// Sanitize and get the form data
$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];

// Check if the user exists in the Users table
$sql = "SELECT user_id, password, role FROM Users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User found, now verify password
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        // Password is correct, start session
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];

        // Redirect based on role
        switch ($_SESSION['role']) {
            case 'entrepreneur':
                header("Location: entrepreneurs.php");
                break;
            case 'investor':
                header("Location: investors.php");
                break;
            case 'job_seeker':
                header("Location: job-seekers.php");
                break;
            case 'admin': // Admin role
                header("Location: admin-panel.php");
                break;
            default:
                header("Location: sign_in.php?error=" . urlencode("Invalid role."));
                exit();
        }
    } else {
        header("Location: sign_in.php?error=" . urlencode("Incorrect password, please try again."));
        exit();
    }
} else {
    header("Location: sign_in.php?error=" . urlencode("No user found with that email, please try again."));
    exit();
}

$conn->close();
?>