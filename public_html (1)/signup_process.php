<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Validate passwords match
    if ($password !== $retype_password) {
        header("Location: sign_up.php?error=" . urlencode("Passwords do not match!"));
        exit();
    }

    // Check if email already exists
    $check_email = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    if ($check_email->get_result()->num_rows > 0) {
        header("Location: sign_up.php?error=" . urlencode("Email already exists!"));
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into Users table
        $stmt = $conn->prepare("INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        
        if (!$stmt->execute()) {
            // If AUTO_INCREMENT is not working, try to get the next available ID
            $result = $conn->query("SELECT MAX(user_id) + 1 as next_id FROM Users");
            if ($result && $row = $result->fetch_assoc()) {
                $next_id = $row['next_id'] ?: 1;
                $stmt = $conn->prepare("INSERT INTO Users (user_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $next_id, $name, $email, $hashed_password, $role);
                $stmt->execute();
                $user_id = $next_id;
            } else {
                throw new Exception("Failed to create user account");
            }
        } else {
            $user_id = $conn->insert_id;
        }

        // Handle role-specific data
        if ($role === 'job_seeker') {
            // Insert into Job_Seekers table
            $skills = isset($_POST['skills']) ? json_encode(array_map('trim', explode(',', $_POST['skills']))) : NULL;
            $experience_level = mysqli_real_escape_string($conn, $_POST['experience_level']);
            $desired_role = isset($_POST['desired_role']) ? mysqli_real_escape_string($conn, $_POST['desired_role']) : NULL;
            $location_preference = isset($_POST['location_preference']) ? mysqli_real_escape_string($conn, $_POST['location_preference']) : NULL;

            $stmt = $conn->prepare("INSERT INTO job_seekers (job_seeker_id, skills, desired_role, experience_level, location_preference) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $skills, $desired_role, $experience_level, $location_preference);
            $stmt->execute();

            // Handle resume upload if provided
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/resumes/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Validate file
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                if ($_FILES['resume']['size'] > $maxSize) {
                    throw new Exception("Resume file size must be less than 5MB");
                }

                if (!in_array($_FILES['resume']['type'], $allowedTypes)) {
                    throw new Exception("Only PDF, DOC, and DOCX files are allowed for resume");
                }

                // Generate unique filename
                $fileExtension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                $newFilename = 'resume_' . uniqid() . '.' . $fileExtension;
                $filePath = $upload_dir . $newFilename;

                // Move uploaded file
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
                    // Insert into Resumes table
                    $stmt = $conn->prepare("INSERT INTO Resumes (job_seeker_id, file_name, file_path, file_type, file_size, is_active) VALUES (?, ?, ?, ?, ?, TRUE)");
                    $stmt->bind_param("isssi", 
                        $user_id,
                        $_FILES['resume']['name'],
                        $filePath,
                        $_FILES['resume']['type'],
                        $_FILES['resume']['size']
                    );
                    $stmt->execute();
                } else {
                    throw new Exception("Error uploading resume file");
                }
            }
        } elseif ($role === 'entrepreneur') {
            // Insert into Entrepreneurs table
            $stmt = $conn->prepare("INSERT INTO Entrepreneurs (entrepreneur_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        } elseif ($role === 'investor') {
            // Insert into Investors table with default values
            $stmt = $conn->prepare("INSERT INTO Investors (investor_id, investment_range_min, investment_range_max) VALUES (?, 0, 0)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['show_details_modal'] = true; // Set flag to show the details modal
        
        // Redirect based on role
        switch ($role) {
            case 'entrepreneur':
                header("Location: entrepreneurs.php");
                break;
            case 'investor':
                header("Location: investors.php");
                break;
            case 'job_seeker':
                header("Location: job-seekers.php");
                break;
            case 'admin':
                header("Location: admin-panel.php");
                break;
            default:
                header("Location: index.php");
        }
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        header("Location: sign_up.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>