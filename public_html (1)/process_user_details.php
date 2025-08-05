<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle skip action
if (isset($_POST['action']) && $_POST['action'] === 'skip') {
    unset($_SESSION['show_details_modal']);
    echo json_encode(['success' => true, 'skipped' => true]);
    exit;
}

// Sanitize and prepare the data
$contact_number = mysqli_real_escape_string($conn, $_POST['contact_number'] ?? '');
$location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
$industry = mysqli_real_escape_string($conn, $_POST['industry'] ?? '');
$introduction = mysqli_real_escape_string($conn, $_POST['introduction'] ?? '');
$accomplishments = mysqli_real_escape_string($conn, $_POST['accomplishments'] ?? '');
$education = mysqli_real_escape_string($conn, $_POST['education'] ?? '');
$employment = mysqli_real_escape_string($conn, $_POST['employment'] ?? '');
$gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
$birthdate = mysqli_real_escape_string($conn, $_POST['birthdate'] ?? '');
$facebook_url = mysqli_real_escape_string($conn, $_POST['facebook_url'] ?? '');
$twitter_url = mysqli_real_escape_string($conn, $_POST['twitter_url'] ?? '');
$instagram_url = mysqli_real_escape_string($conn, $_POST['instagram_url'] ?? '');
$linkedin_url = mysqli_real_escape_string($conn, $_POST['linkedin_url'] ?? '');

// Validate required fields
if (empty($contact_number) || empty($location)) {
    echo json_encode(['success' => false, 'message' => 'Contact number and location are required']);
    exit;
}

// Update user details
$query = "UPDATE Users SET 
    contact_number = ?,
    location = ?,
    industry = ?,
    introduction = ?,
    accomplishments = ?,
    education = ?,
    employment = ?,
    gender = ?,
    birthdate = ?
    WHERE user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("sssssssssi", 
    $contact_number,
    $location,
    $industry,
    $introduction,
    $accomplishments,
    $education,
    $employment,
    $gender,
    $birthdate,
    $user_id
);

if ($stmt->execute()) {
    // Check if social links exist
    $check_social = "SELECT COUNT(*) as count FROM User_Social_Links WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_social);
    if (!$check_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $check_stmt->bind_param("i", $user_id);
    if (!$check_stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error checking social links: ' . $check_stmt->error]);
        exit;
    }
    
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Update existing social links
        $social_query = "UPDATE User_Social_Links SET 
            facebook_url = ?,
            twitter_url = ?,
            instagram_url = ?,
            linkedin_url = ?
            WHERE user_id = ?";
    } else {
        // Insert new social links
        $social_query = "INSERT INTO User_Social_Links 
            (facebook_url, twitter_url, instagram_url, linkedin_url, user_id) 
            VALUES (?, ?, ?, ?, ?)";
    }

    $social_stmt = $conn->prepare($social_query);
    if (!$social_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $social_stmt->bind_param("ssssi", 
        $facebook_url,
        $twitter_url,
        $instagram_url,
        $linkedin_url,
        $user_id
    );

    if ($social_stmt->execute()) {
        unset($_SESSION['show_details_modal']);
        echo json_encode(['success' => true, 'message' => 'Details saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating social links: ' . $social_stmt->error]);
    }
    $social_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating user details: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?> 