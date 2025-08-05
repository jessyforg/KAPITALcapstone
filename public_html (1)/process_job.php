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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id']) && isset($_POST['action'])) {
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    $action = $_POST['action'];
    $rejection_reason = isset($_POST['rejection_reason']) ? mysqli_real_escape_string($conn, $_POST['rejection_reason']) : '';

    // Get job details for notification
    $job_query = "SELECT j.*, s.name AS startup_name, u.name AS entrepreneur_name, u.email AS entrepreneur_email
                  FROM Jobs j
                  JOIN Startups s ON j.startup_id = s.startup_id
                  JOIN Entrepreneurs e ON s.entrepreneur_id = e.entrepreneur_id
                  JOIN Users u ON e.entrepreneur_id = u.user_id
                  WHERE j.job_id = '$job_id'";
    $job_result = mysqli_query($conn, $job_query);
    $job = mysqli_fetch_assoc($job_result);

    if ($action === 'approve') {
        $query = "UPDATE Jobs SET status = 'active' WHERE job_id = '$job_id'";
        $message = "Your job posting for {$job['role']} at {$job['startup_name']} has been approved and is now visible to job seekers.";
    } else if ($action === 'reject') {
        $query = "UPDATE Jobs SET status = 'rejected', rejection_reason = '$rejection_reason' WHERE job_id = '$job_id'";
        $message = "Your job posting for {$job['role']} at {$job['startup_name']} has been rejected. Reason: $rejection_reason";
    }

    if (mysqli_query($conn, $query)) {
        // Send email notification to entrepreneur
        $to = $job['entrepreneur_email'];
        $subject = "Job Posting Status Update";
        $headers = "From: noreply@kapitalsystem.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $email_body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2>Job Posting Status Update</h2>
                <p>Dear {$job['entrepreneur_name']},</p>
                <p>$message</p>
                <p>Best regards,<br>Kapital System Team</p>
            </body>
            </html>
        ";
        
        mail($to, $subject, $email_body, $headers);
        
        $_SESSION['status_message'] = "Job has been " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
    } else {
        $_SESSION['error_message'] = "Error processing job verification: " . mysqli_error($conn);
    }
}

header("Location: admin-panel.php?section=job-verifications");
exit; 