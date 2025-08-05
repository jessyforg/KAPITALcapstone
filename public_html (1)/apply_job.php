<?php
// Include the database connection file
include 'db_connection.php';

// Start the session
session_start();

// Set the current page for navbar
$currentPage = 'job-seekers';

// Include the navbar
include 'navbar.php';

// Check if the user is logged in and has the job seeker role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker') {
    header('Location: sign_in.php'); // Redirect to login page if not logged in
    exit;
}

// Check if a job ID is passed in the URL
if (isset($_GET['job_id'])) {
    $job_id = mysqli_real_escape_string($conn, $_GET['job_id']);
    $user_id = $_SESSION['user_id'];

    // Check if user has already applied
    $check_application = "SELECT status, created_at, cover_letter FROM Applications 
                         WHERE job_id = '$job_id' AND job_seeker_id = '$user_id'";
    $application_result = mysqli_query($conn, $check_application);
    $existing_application = mysqli_fetch_assoc($application_result);

    // Fetch the job details along with startup details
    $query = "
        SELECT Jobs.job_id, Jobs.role, Jobs.description, Jobs.requirements, Jobs.location, Jobs.salary_range_min, Jobs.salary_range_max, 
               Startups.name AS startup_name, Startups.industry, Startups.logo_url 
        FROM Jobs 
        JOIN Startups ON Jobs.startup_id = Startups.startup_id
        WHERE job_id = '$job_id'
    ";
    $result = mysqli_query($conn, $query);

    // If the job exists, fetch and display the details
    if (mysqli_num_rows($result) > 0) {
        $job = mysqli_fetch_assoc($result);
    } else {
        echo "Job not found.";
        exit;
    }
} else {
    echo "Job ID is missing.";
    exit;
}

// Flag to check if the application was successful
$application_status = '';

// Only process form submission if user hasn't already applied
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$existing_application) {
    // Get the application data from the form
    $user_id = $_SESSION['user_id']; // This is the job seeker's ID
    $cover_letter = mysqli_real_escape_string($conn, $_POST['cover_letter']);

    // Handle file upload (resume)
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $resume_name = $_FILES['resume']['name'];
        $resume_tmp_name = $_FILES['resume']['tmp_name'];
        $resume_size = $_FILES['resume']['size'];
        $resume_type = $_FILES['resume']['type'];

        // Define allowed file types
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_file_size = 10 * 1024 * 1024; // 10MB limit

        if (in_array($resume_type, $allowed_types) && $resume_size <= $max_file_size) {
            // Ensure the uploads/resumes directory exists
            $upload_dir = 'uploads/resumes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
            }

            $resume_path = $upload_dir . basename($resume_name);

            // Check if the file is uploaded successfully
            if (move_uploaded_file($resume_tmp_name, $resume_path)) {
                // Insert the job application into the database, including the cover letter and resume
                $query = "INSERT INTO Applications (job_id, job_seeker_id, status, cover_letter) 
                          VALUES ('$job_id', '$user_id', 'applied', '$cover_letter')";

                if (mysqli_query($conn, $query)) {
                    // Get the application ID that was just inserted
                    $application_id = mysqli_insert_id($conn);
                    
                    // Insert the resume information into the Resumes table
                    $resume_query = "INSERT INTO Resumes (job_seeker_id, file_name, file_path, file_type, file_size, is_active) 
                                    VALUES ('$user_id', '$resume_name', '$resume_path', '$resume_type', '$resume_size', TRUE)";
                    
                    if (mysqli_query($conn, $resume_query)) {
                        $_SESSION['status_message'] = 'Your application has been submitted successfully!';
                        header("Location: job-seekers.php");
                        exit();
                    } else {
                        $application_status = 'failed';
                    }
                } else {
                    $application_status = 'failed';
                }
            } else {
                $application_status = 'failed'; // Mark as failed
            }
        } else {
            $application_status = 'invalid_file'; // Invalid file type or size
        }
    } else {
        $application_status = 'no_resume'; // No resume uploaded
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg, #343131, #808080);
            color: #fff;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .section-title {
            font-size: 2em;
            font-weight: 600;
            color: #fff;
            margin-bottom: 30px;
        }

        .job-details {
            background: #2C2F33;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #40444B;
        }

        .job-details p {
            margin: 15px 0;
            color: #B9BBBE;
            line-height: 1.6;
        }

        .job-details strong {
            color: #ea580c;
            font-weight: 500;
        }

        form label {
            display: block;
            font-weight: 500;
            margin-top: 20px;
            color: #fff;
            margin-bottom: 10px;
        }

        form textarea {
            width: 100%;
            padding: 15px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #40444B;
            background: #2C2F33;
            color: #fff;
            font-size: 1em;
            resize: vertical;
            min-height: 150px;
            font-family: 'Poppins', sans-serif;
        }

        form textarea:focus {
            outline: none;
            border-color: #ea580c;
        }

        form input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            background: #2C2F33;
            border: 1px solid #40444B;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
        }

        form input[type="file"]::-webkit-file-upload-button {
            background: #ea580c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 15px;
            transition: background 0.3s ease;
        }

        form input[type="file"]::-webkit-file-upload-button:hover {
            background: #c2410c;
        }

        form button {
            background: #ea580c;
            color: #fff;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 25px;
            transition: background 0.3s ease;
            font-weight: 500;
            width: auto;
            display: inline-block;
        }

        form button:hover {
            background: #c2410c;
            transform: translateY(-2px);
        }

        .error {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 10px;
        }

        /* Custom scrollbar for textarea */
        textarea::-webkit-scrollbar {
            width: 8px;
        }

        textarea::-webkit-scrollbar-track {
            background: #2C2F33;
            border-radius: 4px;
        }

        textarea::-webkit-scrollbar-thumb {
            background: #ea580c;
            border-radius: 4px;
        }

        textarea::-webkit-scrollbar-thumb:hover {
            background: #c2410c;
        }

        /* Placeholder styling */
        textarea::placeholder {
            color: #B9BBBE;
        }

        @media (max-width: 768px) {
            .container {
                margin: 0 10px;
                padding: 20px;
            }

            .section-title {
                font-size: 1.8em;
            }

            form button {
                width: 100%;
            }
        }

        .startup-header {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            align-items: flex-start;
        }

        .startup-logo {
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 50%;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 2px solid #ea580c;
            padding: 10px;
        }

        .startup-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .startup-logo .default-logo-icon {
            font-size: 40px;
            color: #ea580c;
        }

        .startup-info {
            flex-grow: 1;
        }

        .startup-info h3 {
            font-size: 1.8rem;
            color: #ea580c;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .startup-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 15px;
            }

            .startup-logo {
                width: 80px;
                height: 80px;
            }

            .startup-logo .default-logo-icon {
                font-size: 32px;
            }

            .startup-info h3 {
                font-size: 1.5rem;
            }
        }

        .application-status {
            background: #23272A;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #40444B;
        }

        .application-status h2 {
            color: #ea580c;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            text-transform: capitalize;
            margin: 10px 0;
        }

        .status-applied { background: #7289DA; color: white; }
        .status-reviewed { background: #ea580c; color: white; }
        .status-interviewed { background: #43B581; color: white; }
        .status-hired { background: #43B581; color: white; }
        .status-rejected { background: #F04747; color: white; }

        .application-details {
            margin-top: 20px;
            color: #B9BBBE;
        }

        .application-details p {
            margin: 10px 0;
        }

        .application-date {
            color: #ea580c;
            font-style: italic;
        }
    </style>

    <script>
        // Remove all the old alert-based notifications
    </script>
</head>

<body>
    <div class="container">
        <h1 class="section-title">Apply for <?php echo htmlspecialchars($job['role']); ?></h1>

        <?php if ($existing_application): ?>
            <div class="application-status">
                <h2>Your Application Status</h2>
                <span class="status-badge status-<?php echo strtolower($existing_application['status']); ?>">
                    <?php echo ucfirst(htmlspecialchars($existing_application['status'])); ?>
                </span>
                <div class="application-details">
                    <p class="application-date">Applied on: <?php echo date('F j, Y', strtotime($existing_application['created_at'])); ?></p>
                    <p><strong>Your Cover Letter:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($existing_application['cover_letter'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Job Details Section -->
        <div class="job-details">
            <div class="startup-header">
                <div class="startup-logo">
                    <?php if (!empty($job['logo_url']) && file_exists($job['logo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($job['logo_url']); ?>" alt="<?php echo htmlspecialchars($job['startup_name']); ?> logo">
                    <?php else: ?>
                        <i class="fas fa-building default-logo-icon"></i>
                    <?php endif; ?>
                </div>
                <div class="startup-info">
                    <h3><?php echo htmlspecialchars($job['startup_name']); ?></h3>
                    <p><strong>Industry:</strong> <?php echo htmlspecialchars($job['industry']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p><strong>Salary:</strong> ₱<?php echo number_format($job['salary_range_min'], 2); ?> - ₱<?php echo number_format($job['salary_range_max'], 2); ?></p>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                    <p><strong>Requirements:</strong> <?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
                </div>
            </div>
        </div>

        <?php if (!$existing_application): ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="cover_letter">Cover Letter:</label>
                <textarea id="cover_letter" name="cover_letter" required></textarea>

                <label for="resume">Resume (PDF or Word document):</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>

                <button type="submit">Submit Application</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>
