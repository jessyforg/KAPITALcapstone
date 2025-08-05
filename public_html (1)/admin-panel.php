<?php
session_start();
include('db_connection.php');
include('verification_check.php');
require_once 'config.php';
include('navbar.php');

// Redirect if the user is not logged in or does not have the admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: sign_in.php");
    exit("Redirecting to login page...");
}

$user_id = $_SESSION['user_id'];

// Set active section based on URL parameter
$active_section = isset($_GET['section']) ? $_GET['section'] : 'startup-applications';

// Get the user's name
// ... existing code ...

// Fetch pending startups
$startup_query = "SELECT s.startup_id, s.name, s.industry, s.description, u.name AS entrepreneur_name
                  FROM Startups s
                  JOIN Entrepreneurs e ON s.entrepreneur_id = e.entrepreneur_id
                  JOIN Users u ON e.entrepreneur_id = u.user_id
                  WHERE s.approval_status = 'pending'";
$startup_result = mysqli_query($conn, $startup_query);

// Fetch pending verifications
$verification_query = "SELECT vd.*, u.name AS user_name, u.email, u.role
                      FROM Verification_Documents vd
                      JOIN Users u ON vd.user_id = u.user_id
                      WHERE vd.status = 'pending'
                      ORDER BY vd.uploaded_at DESC";
$verification_result = mysqli_query($conn, $verification_query);

// Fetch all users
$users_query = "SELECT * FROM Users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_query);

// Fetch pending jobs
$jobs_query = "SELECT j.*, s.name AS startup_name, u.name AS entrepreneur_name
               FROM Jobs j
               JOIN Startups s ON j.startup_id = s.startup_id
               JOIN Entrepreneurs e ON s.entrepreneur_id = e.entrepreneur_id
               JOIN Users u ON e.entrepreneur_id = u.user_id
               WHERE j.status = 'pending'
               ORDER BY j.created_at DESC";
$jobs_result = mysqli_query($conn, $jobs_query);

// Fetch all tickets
$tickets_query = "SELECT t.*, u.name AS user_name, u.email 
                  FROM Tickets t 
                  JOIN Users u ON t.user_id = u.user_id 
                  ORDER BY t.created_at DESC";
$tickets_result = mysqli_query($conn, $tickets_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS Configuration -->
    <link rel="stylesheet" href="tailwind-config.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind-init.js"></script>
    
    <!-- Add PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        /* Unified modern dashboard styling for admin panel */
        body {
            background: #18191a;
            color: #e4e6eb;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100vw;
            box-sizing: border-box;
        }
        .sidebar {
            width: 260px;
            background: #242526;
            padding: 0;
            height: calc(100vh - 32px); /* Fixed height accounting for margins */
            max-height: calc(100vh - 32px);
            border-right: 1px solid #3a3b3c;
            position: sticky;
            top: 16px; /* Consistent with margin-top */
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: width 0.3s;
            box-sizing: border-box;
            margin-top: 16px;
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: hidden; /* Prevent horizontal scrolling */
            /* Custom scrollbar styling */
            scrollbar-width: thin;
            scrollbar-color: #ea580c #2d2d2d;
        }
        
        /* Webkit scrollbar styling for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #2d2d2d;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #ea580c;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #c2410c;
        }
        
        .sidebar.collapsed {
            width: 0 !important;
            min-width: 0 !important;
            overflow: hidden;
            padding: 0 !important;
        }
        .sidebar-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
            margin-top: 20px; /* Add top margin for proper spacing */
            padding: 0 16px; /* Add horizontal padding */
        }
        .sidebar-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #3a3b3c;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 12px;
            font-size: 60px;
            color: #ea580c;
        }
        .sidebar-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }
        .sidebar-username {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            padding: 0 16px 20px 16px; /* Add bottom padding to prevent cutoff */
            flex: 1; /* Allow nav to take remaining space */
            overflow-y: auto; /* Allow nav items to scroll if needed */
        }
        .sidebar-link {
            color: #e4e6eb;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s, color 0.2s;
            background: none;
        }
        .sidebar-link.active {
            background: #3a3b3c;
            color: #ea580c;
        }
        .sidebar-link:not(.active):hover {
            background: #23272a;
            color: #ea580c;
        }
        .main-content {
            flex: 1;
            padding: 40px 0 40px 0;
            max-width: calc(100vw - 260px);
            min-width: 0;
            box-sizing: border-box;
            margin-left: 260px;
            margin-top: 120px; /* Increased for proper navbar clearance */
            min-height: calc(100vh - 120px); /* Ensure full height */
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 32px;
            box-sizing: border-box;
        }
        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 32px;
            text-align: center;
            color: #fff;
        }
        h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #ea580c;
            margin-bottom: 20px;
            text-align: left;
        }
        .startup, .modal-content, table, .document-details, .filter-section {
            background: #23272a;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 1px solid #404040;
            margin-bottom: 24px;
            padding: 24px;
        }
        .startup h2 {
            color: #ea580c;
            margin-bottom: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            background: transparent;
            color: #ea580c;
            border: 1.5px solid #ea580c;
            transition: all 0.3s ease;
            min-width: unset;
            text-decoration: none;
            margin-right: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
        }
        .btn.approve {
            background: #43B581;
            color: #fff;
            border: none;
        }
        .btn.approve:hover {
            background: #3ca374;
        }
        .btn.reject {
            background: #F04747;
            color: #fff;
            border: none;
        }
        .btn.reject:hover {
            background: #d63c3c;
        }
        .btn.view-details {
            background: #2d2d2d;
            color: #ea580c;
            border: 1.5px solid #ea580c;
        }
        .btn.view-details:hover {
            background: #ea580c;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #23272a;
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 16px;
            border-bottom: 1px solid #404040;
            color: #e4e6eb;
            text-align: left;
        }
        th {
            background: #2d2d2d;
            color: #ea580c;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background: #2d2d2d;
        }
        .modal {
            background: rgba(0,0,0,0.8);
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        .modal-content {
            max-width: 800px;
            width: 90%;
            margin: auto;
            padding: 32px;
            border-radius: 16px;
            background: #23272a;
            color: #e4e6eb;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        .close-modal {
            position: absolute;
            top: 16px;
            right: 24px;
            font-size: 2rem;
            color: #ea580c;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .close-modal:hover {
            transform: rotate(90deg);
        }
        .document-preview-container {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
            max-height: 400px;
            background: #2d2d2d;
            border-radius: 12px;
            padding: 20px;
            overflow: hidden;
        }
        .document-preview img,
        .document-preview-container img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            border-radius: 12px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        /* Table document preview in verification list */
        .document-preview {
            max-width: 80px;
            max-height: 60px;
            border-radius: 6px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .document-preview:hover {
            transform: scale(1.1);
        }
        .pdf-preview-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }
        .pdf-preview-container canvas {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .pdf-icon-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            color: #ea580c;
            font-size: 3rem;
        }
        .pdf-icon-container p {
            margin: 0;
            font-size: 1rem;
            color: #e4e6eb;
        }
        .filter-section {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            padding: 20px;
        }
        .filter-section select {
            padding: 10px 16px;
            border-radius: 6px;
            background: #2d2d2d;
            color: #e4e6eb;
            border: 1px solid #404040;
            font-size: 0.9rem;
            min-width: 200px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-section select:hover {
            border-color: #ea580c;
        }
        .filter-section select:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .status-pending {
            background: #f59e0b;
            color: #fff;
        }
        .status-verified {
            background: #43B581;
            color: #fff;
        }
        .status-rejected {
            background: #F04747;
            color: #fff;
        }
        .status-in_progress {
            background: #5865F2;
            color: #fff;
        }
        .status-resolved {
            background: #43B581;
            color: #fff;
        }
        .rejection-reason {
            margin: 20px 0;
        }
        .rejection-reason textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: #2d2d2d;
            color: #e4e6eb;
            border: 1px solid #404040;
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
            margin-top: 8px;
        }
        .rejection-reason textarea:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
        }
        .document-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 24px;
        }
        .sortable::after {
            content: '↕';
            position: absolute;
            right: 8px;
            color: #ea580c;
        }
        .sortable.asc::after {
            content: '↑';
        }
        .sortable.desc::after {
            content: '↓';
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ea580c;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background: #2d2d2d;
            color: #e4e6eb;
            border: 1px solid #404040;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        @media (max-width: 900px) {
            .sidebar {
                width: 70px;
                padding: 0;
                margin-top: 16px;
                height: calc(100vh - 32px);
                overflow-y: auto;
            }
            .container {
                padding: 0 16px;
            }
            .main-content {
                padding: 10px 0;
                margin-left: 70px;
                margin-top: 100px; /* Adjusted for tablet */
                max-width: calc(100vw - 70px);
            }
            .modal-content {
                width: 95%;
                padding: 24px;
            }
            .document-preview img,
            .document-preview-container img {
                max-height: 300px;
            }
        }
        @media (max-width: 700px) {
            .dashboard-wrapper {
                flex-direction: column;
                width: 100vw;
            }
            .sidebar {
                flex-direction: row;
                width: 100vw;
                height: 60px;
                min-height: 0;
                max-height: 60px;
                border-right: none;
                border-bottom: 1px solid #3a3b3c;
                padding: 0 16px;
                align-items: center;
                justify-content: space-between;
                margin-top: 88px; /* Account for navbar on mobile */
                position: fixed;
                top: 0;
                z-index: 999;
                overflow: hidden; /* No scrolling needed in horizontal layout */
            }
            .container {
                padding: 0 12px;
            }
            .main-content {
                padding: 10px 0;
                margin-left: 0;
                margin-top: 148px; /* Account for navbar + horizontal sidebar */
                max-width: 100vw;
            }
            .filter-section {
                flex-direction: column;
                gap: 12px;
            }
            .filter-section select {
                width: 100%;
            }
            .document-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                margin-right: 0;
            }
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            /* Hide sidebar text on mobile horizontal layout */
            .sidebar span {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-wrapper">
    <?php include('sidebar.php'); ?>
    <div class="main-content">
        <div class="container">
            <!-- Admin Panel title and sidebar only, no duplicate tab navigation -->
            <h1 style="font-size:2.5rem;font-weight:bold;margin-bottom:32px;text-align:center;color:#fff;">Admin Panel</h1>

            <!-- Startup Applications Tab -->
            <div id="section-startup-applications" class="dashboard-section" style="display:block;">
                <h2>Pending Startup Applications</h2>
                <?php if (mysqli_num_rows($startup_result) > 0): ?>
                    <?php while ($startup = mysqli_fetch_assoc($startup_result)): ?>
                        <div class="startup">
                            <h2><?php echo htmlspecialchars($startup['name']); ?></h2>
                            <p><strong>Industry:</strong> <?php echo htmlspecialchars($startup['industry']); ?></p>
                            <p><strong>Entrepreneur:</strong> <?php echo htmlspecialchars($startup['entrepreneur_name']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($startup['description'])); ?></p>

                            <form action="process-startup.php" method="post" style="display: inline;">
                                <input type="hidden" name="startup_id" value="<?php echo $startup['startup_id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                            </form>
                            <form action="process-startup.php" method="post" style="display: inline;">
                                <input type="hidden" name="startup_id" value="<?php echo $startup['startup_id']; ?>">
                                <button type="submit" name="action" value="not_approved" class="btn reject">Not Approved</button>
                            </form>
                            <a href="startup_detail.php?startup_id=<?php echo $startup['startup_id']; ?>" class="btn view-details">View Details</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No pending startups to review.</p>
                <?php endif; ?>
            </div>

            <!-- User Verifications Tab -->
            <div id="section-user-verifications" class="dashboard-section" style="display:none;">
                <h2>Pending User Verifications</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Document Type</th>
                            <th>Preview</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($verification = mysqli_fetch_assoc($verification_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($verification['user_name']); ?></td>
                                <td><?php 
                                    $role = htmlspecialchars($verification['role']);
                                    switch($role) {
                                        case 'entrepreneur':
                                            echo 'Entrepreneur';
                                            break;
                                        case 'job_seeker':
                                            echo 'Job Seeker';
                                            break;
                                        case 'investor':
                                            echo 'Investor';
                                            break;
                                        case 'admin':
                                            echo 'TARAKI Admin';
                                            break;
                                        default:
                                            echo ucfirst($role);
                                    }
                                ?></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $verification['document_type'])); ?></td>
                                <td>
                                    <?php 
                                        if (!empty($verification['file_path'])) {
                                            $file_extension = strtolower(pathinfo($verification['file_path'], PATHINFO_EXTENSION));
                                            if (in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                                                echo '<img src="' . htmlspecialchars($verification['file_path']) . '" alt="Document Preview" class="document-preview">';
                                            } else if ($file_extension === 'pdf') {
                                                echo '<div class="pdf-icon-container">';
                                                echo '<i class="fas fa-file-pdf"></i>';
                                                echo '<p>PDF Document</p>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<div class="pdf-icon-container">';
                                            echo '<i class="fas fa-file-circle-exclamation"></i>';
                                            echo '<p>No Document Available</p>';
                                            echo '</div>';
                                        }
                                    ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($verification['uploaded_at'])); ?></td>
                                <td>
                                    <button class="btn view-details" onclick='openVerificationModal(<?php echo json_encode($verification); ?>)'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Verification Details Modal -->
            <div id="verificationModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeVerificationModal()">&times;</span>
                    <h2>Verification Details</h2>
                    <div class="document-details">
                        <p><strong>User Name:</strong> <span id="modal-user-name"></span></p>
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                        <p><strong>Role:</strong> <span id="modal-role"></span></p>
                        <p><strong>Document Type:</strong> <span id="modal-document-type"></span></p>
                        <p><strong>Document Number:</strong> <span id="modal-document-number"></span></p>
                        <p><strong>Issue Date:</strong> <span id="modal-issue-date"></span></p>
                        <p><strong>Expiry Date:</strong> <span id="modal-expiry-date"></span></p>
                        <p><strong>Issuing Authority:</strong> <span id="modal-issuing-authority"></span></p>
                        <p><strong>Uploaded:</strong> <span id="modal-uploaded"></span></p>
                        
                        <div id="document-preview-container" class="document-preview-container">
                            <!-- Preview content will be dynamically inserted here -->
                        </div>

                        <form action="process_verification.php" method="post" id="verification-form">
                            <input type="hidden" name="document_id" id="modal-document-id">
                            <div class="rejection-reason">
                                <label for="rejection_reason">Rejection Reason (if applicable):</label>
                                <textarea name="rejection_reason" id="rejection_reason"></textarea>
                            </div>
                            <div class="document-actions">
                                <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Job Verifications Tab -->
            <div id="section-job-verifications" class="dashboard-section" style="display:none;">
                <h2>Pending Job Verifications</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Startup</th>
                            <th>Job Role</th>
                            <th>Location</th>
                            <th>Salary Range</th>
                            <th>Posted By</th>
                            <th>Posted Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['startup_name']); ?></td>
                                <td><?php echo htmlspecialchars($job['role']); ?></td>
                                <td><?php echo htmlspecialchars($job['location']); ?></td>
                                <td>₱<?php echo number_format($job['salary_range_min'], 2); ?> - ₱<?php echo number_format($job['salary_range_max'], 2); ?></td>
                                <td><?php echo htmlspecialchars($job['entrepreneur_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                                <td>
                                    <button class="btn view-details" onclick='openJobModal(<?php echo json_encode($job); ?>)'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Job Details Modal -->
            <div id="jobModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeJobModal()">&times;</span>
                    <h2>Job Details</h2>
                    <div class="document-details">
                        <p><strong>Startup:</strong> <span id="modal-startup-name"></span></p>
                        <p><strong>Job Role:</strong> <span id="modal-job-role"></span></p>
                        <p><strong>Location:</strong> <span id="modal-location"></span></p>
                        <p><strong>Salary Range:</strong> <span id="modal-salary"></span></p>
                        <p><strong>Posted By:</strong> <span id="modal-entrepreneur"></span></p>
                        <p><strong>Posted Date:</strong> <span id="modal-posted-date"></span></p>
                        <p><strong>Description:</strong> <span id="modal-description"></span></p>
                        <p><strong>Requirements:</strong> <span id="modal-requirements"></span></p>

                        <form action="process_job.php" method="post" id="job-form">
                            <input type="hidden" name="job_id" id="modal-job-id">
                            <div class="rejection-reason">
                                <label for="job_rejection_reason">Rejection Reason (if applicable):</label>
                                <textarea name="rejection_reason" id="job_rejection_reason"></textarea>
                            </div>
                            <div class="document-actions">
                                <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Users List Tab -->
            <div id="section-users-list" class="dashboard-section" style="display:none;">
                <h2>All Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="sortable" data-sort="role">Role</th>
                            <th class="sortable" data-sort="verification_status">Verification Status</th>
                            <th class="sortable" data-sort="created_at">Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php 
                                    $role = htmlspecialchars($user['role']);
                                    switch($role) {
                                        case 'entrepreneur':
                                            echo 'Entrepreneur';
                                            break;
                                        case 'job_seeker':
                                            echo 'Job Seeker';
                                            break;
                                        case 'investor':
                                            echo 'Investor';
                                            break;
                                        case 'admin':
                                            echo 'TARAKI Admin';
                                            break;
                                        default:
                                            echo ucfirst($role);
                                    }
                                ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['verification_status']; ?>">
                                        <?php echo ucfirst($user['verification_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="user_details.php?user_id=<?php echo $user['user_id']; ?>" class="btn view-details">View Details</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tickets Tab -->
            <div id="section-tickets" class="dashboard-section" style="display:none;">
                <h2>User Tickets</h2>
                <div class="filter-section">
                    <div>
                        <label for="ticketStatusFilter">Status:</label>
                        <select id="ticketStatusFilter" onchange="filterTickets()">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label for="ticketTypeFilter">Type:</label>
                        <select id="ticketTypeFilter" onchange="filterTickets()">
                            <option value="all">All Types</option>
                            <option value="bug">Bug Reports</option>
                            <option value="feature">Feature Suggestions</option>
                            <option value="improvement">Improvements</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = mysqli_fetch_assoc($tickets_result)): ?>
                            <tr class="ticket-row" data-status="<?php echo $ticket['status']; ?>" data-type="<?php echo $ticket['type']; ?>">
                                <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                <td><?php echo ucfirst($ticket['type']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['user_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                <td>
                                    <button class="btn view-details" onclick='openTicketModal(<?php echo json_encode($ticket); ?>)'>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ticket Details Modal -->
            <div id="ticketModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeTicketModal()">&times;</span>
                    <h2>Ticket Details</h2>
                    <div class="document-details">
                        <p><strong>Title:</strong> <span id="modal-ticket-title"></span></p>
                        <p><strong>Type:</strong> <span id="modal-ticket-type"></span></p>
                        <p><strong>Status:</strong> <span id="modal-ticket-status"></span></p>
                        <p><strong>Submitted By:</strong> <span id="modal-ticket-user"></span></p>
                        <p><strong>Email:</strong> <span id="modal-ticket-email"></span></p>
                        <p><strong>Date:</strong> <span id="modal-ticket-date"></span></p>
                        <p><strong>Description:</strong> <span id="modal-ticket-description"></span></p>

                        <form action="process_ticket.php" method="post" id="ticket-form">
                            <input type="hidden" name="ticket_id" id="modal-ticket-id">
                            <div class="form-group">
                                <label for="new_status">Update Status</label>
                                <select name="new_status" id="new_status">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="admin_notes">Admin Notes</label>
                                <textarea name="admin_notes" id="admin_notes" rows="3"></textarea>
                            </div>
                            <div class="document-actions">
                                <button type="submit" name="update_status" class="btn approve">Update Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openTab(evt, tabName) {
        const tabSections = document.querySelectorAll('.dashboard-section');
        tabSections.forEach(sec => {
            if (sec.id === 'section-' + tabName) {
                sec.style.display = '';
            } else {
                sec.style.display = 'none';
            }
        });
        const tabLinks = document.querySelectorAll('.tab');
        tabLinks.forEach(tab => tab.classList.remove('active'));
        evt.currentTarget.classList.add('active');
    }

    // Helper function to format document types
    function formatDocumentType(type) {
        const formatMap = {
            'drivers_license': "Driver's License",
            'government_id': "Government ID",
            'business_registration': "Business Registration",
            'professional_license': "Professional License",
            'tax_certificate': "Tax Certificate",
            'bank_statement': "Bank Statement",
            'utility_bill': "Utility Bill",
            'proof_of_address': "Proof of Address",
            'employment_certificate': "Employment Certificate",
            'educational_certificate': "Educational Certificate",
            'passport': "Passport",
            'other': "Other Document"
        };
        
        return formatMap[type] || type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }

    function openVerificationModal(verification) {
        document.getElementById('modal-user-name').textContent = verification.user_name;
        document.getElementById('modal-email').textContent = verification.email;
        document.getElementById('modal-role').textContent = verification.role;
        document.getElementById('modal-document-type').textContent = formatDocumentType(verification.document_type);
        document.getElementById('modal-document-number').textContent = verification.document_number || 'N/A';
        document.getElementById('modal-issue-date').textContent = verification.issue_date || 'N/A';
        document.getElementById('modal-expiry-date').textContent = verification.expiry_date || 'N/A';
        document.getElementById('modal-issuing-authority').textContent = verification.issuing_authority || 'N/A';
        document.getElementById('modal-uploaded').textContent = new Date(verification.uploaded_at).toLocaleDateString();
        document.getElementById('modal-document-id').value = verification.document_id;

        // Handle document preview
        const previewContainer = document.getElementById('document-preview-container');
        if (verification.file_path) {
            const fileExtension = verification.file_path.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
                previewContainer.innerHTML = `
                    <img src="${verification.file_path}" alt="Document Preview" style="max-width: 100%; max-height: 500px; border-radius: 8px;">
                `;
            } else if (fileExtension === 'pdf') {
                previewContainer.innerHTML = `
                    <div id="pdf-preview-container" class="pdf-preview-container">
                        <canvas id="pdf-preview"></canvas>
                    </div>
                `;
                renderPDF(verification.file_path);
            }
        } else {
            previewContainer.innerHTML = `
                <div class="pdf-icon-container">
                    <i class="fas fa-file-circle-exclamation"></i>
                    <p>No Document Available</p>
                </div>
            `;
        }
        
        document.getElementById('verificationModal').style.display = 'block';
    }

    function closeVerificationModal() {
        document.getElementById('verificationModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const verificationModal = document.getElementById('verificationModal');
        const jobModal = document.getElementById('jobModal');
        const ticketModal = document.getElementById('ticketModal');
        if (event.target == verificationModal) {
            verificationModal.style.display = 'none';
        }
        if (event.target == jobModal) {
            jobModal.style.display = 'none';
        }
        if (event.target == ticketModal) {
            ticketModal.style.display = 'none';
        }
    }

    // Initialize PDF.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    async function renderPDF(url) {
        try {
            const loadingTask = pdfjsLib.getDocument(url);
            const pdf = await loadingTask.promise;
            
            // Get the first page
            const page = await pdf.getPage(1);
            
            // Set scale for better quality
            const scale = 1.5;
            const viewport = page.getViewport({ scale });
            
            // Prepare canvas for rendering
            const canvas = document.getElementById('pdf-preview');
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            // Render PDF page
            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            await page.render(renderContext);
        } catch (error) {
            console.error('Error rendering PDF:', error);
            const container = document.getElementById('pdf-preview-container');
            container.innerHTML = `
                <div class="pdf-icon-container">
                    <i class="fas fa-file-pdf"></i>
                    <p>PDF Document</p>
                </div>`;
        }
    }

    function openJobModal(job) {
        document.getElementById('modal-startup-name').textContent = job.startup_name;
        document.getElementById('modal-job-role').textContent = job.role;
        document.getElementById('modal-location').textContent = job.location;
        document.getElementById('modal-salary').textContent = `₱${parseFloat(job.salary_range_min).toLocaleString()} - ₱${parseFloat(job.salary_range_max).toLocaleString()}`;
        document.getElementById('modal-entrepreneur').textContent = job.entrepreneur_name;
        document.getElementById('modal-posted-date').textContent = new Date(job.created_at).toLocaleDateString();
        document.getElementById('modal-description').textContent = job.description;
        document.getElementById('modal-requirements').textContent = job.requirements;
        document.getElementById('modal-job-id').value = job.job_id;
        
        document.getElementById('jobModal').style.display = 'block';
    }

    function closeJobModal() {
        document.getElementById('jobModal').style.display = 'none';
    }

    function openTicketModal(ticket) {
        document.getElementById('modal-ticket-title').textContent = ticket.title;
        document.getElementById('modal-ticket-type').textContent = ticket.type.charAt(0).toUpperCase() + ticket.type.slice(1);
        document.getElementById('modal-ticket-status').textContent = ticket.status.replace('_', ' ').charAt(0).toUpperCase() + ticket.status.slice(1);
        document.getElementById('modal-ticket-user').textContent = ticket.user_name;
        document.getElementById('modal-ticket-email').textContent = ticket.email;
        document.getElementById('modal-ticket-date').textContent = new Date(ticket.created_at).toLocaleDateString();
        document.getElementById('modal-ticket-description').textContent = ticket.description;
        document.getElementById('modal-ticket-id').value = ticket.ticket_id;
        document.getElementById('new_status').value = ticket.status;
        document.getElementById('admin_notes').value = ticket.admin_notes || '';
        
        document.getElementById('ticketModal').style.display = 'block';
    }

    function closeTicketModal() {
        document.getElementById('ticketModal').style.display = 'none';
    }

    function filterTickets() {
        const statusFilter = document.getElementById('ticketStatusFilter').value;
        const typeFilter = document.getElementById('ticketTypeFilter').value;
        const tickets = document.querySelectorAll('.ticket-row');

        tickets.forEach(ticket => {
            const status = ticket.dataset.status;
            const type = ticket.dataset.type;
            const statusMatch = statusFilter === 'all' || status === statusFilter;
            const typeMatch = typeFilter === 'all' || type === typeFilter;

            ticket.style.display = statusMatch && typeMatch ? '' : 'none';
        });
    }

    // Add sorting functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sortableHeaders = document.querySelectorAll('.sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sortBy = this.dataset.sort;
                const isAsc = this.classList.contains('asc');
                const isDesc = this.classList.contains('desc');
                
                // Reset all headers
                sortableHeaders.forEach(h => {
                    h.classList.remove('asc', 'desc');
                });
                
                // Set new sort direction
                if (!isAsc && !isDesc) {
                    this.classList.add('asc');
                    sortTable(sortBy, 'asc');
                } else if (isAsc) {
                    this.classList.add('desc');
                    sortTable(sortBy, 'desc');
                } else {
                    this.classList.add('asc');
                    sortTable(sortBy, 'asc');
                }
            });
        });
    });

    function sortTable(sortBy, direction) {
        const tbody = document.getElementById('usersTableBody');
        const rows = Array.from(tbody.getElementsByTagName('tr'));
        
        rows.sort((a, b) => {
            let aValue, bValue;
            
            switch(sortBy) {
                case 'role':
                    aValue = a.cells[2].textContent;
                    bValue = b.cells[2].textContent;
                    break;
                case 'verification_status':
                    aValue = a.cells[3].querySelector('.status-badge').textContent;
                    bValue = b.cells[3].querySelector('.status-badge').textContent;
                    break;
                case 'created_at':
                    aValue = new Date(a.cells[4].textContent);
                    bValue = new Date(b.cells[4].textContent);
                    break;
            }
            
            if (direction === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
        
        // Clear and re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    }

    // Sidebar navigation functionality
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.sidebar-link');
        const sections = document.querySelectorAll('.dashboard-section');
        
        // Show the active section based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeSection = urlParams.get('section') || 'startup-applications';
        
        // Hide all sections first
        sections.forEach(sec => {
            sec.style.display = 'none';
        });
        
        // Show the active section
        const activeSectionElement = document.getElementById('section-' + activeSection);
        if (activeSectionElement) {
            activeSectionElement.style.display = '';
        }
        
        // Add click event listeners to all sidebar links
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Allow navigation for external pages (messages, about-us, settings, logout)
                if (this.classList.contains('logout-link') || 
                    href.includes('messages.php') || 
                    href.includes('about-us.php') || 
                    href.includes('settings.php') || 
                    href.startsWith('http')) {
                    return true; // Allow normal navigation
                }
                
                // Handle admin panel section navigation
                if (href.includes('admin-panel.php') && href.includes('section=')) {
                    e.preventDefault();
                    
                    // Get the section from the href
                    const section = href.split('section=')[1]?.split('&')[0] || 'startup-applications';
                    
                    // Update active state
                    links.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show the corresponding section
                    sections.forEach(sec => {
                        if (sec.id === 'section-' + section) {
                            sec.style.display = '';
                        } else {
                            sec.style.display = 'none';
                        }
                    });
                    
                    // Update URL without page reload
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('section', section);
                    window.history.pushState({}, '', newUrl);
                }
            });
        });
    });
</script>
</body>

</html>
