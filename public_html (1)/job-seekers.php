<?php
session_start();
include('db_connection.php');
include('verification_check.php');
require_once 'config.php';
include('navbar.php');
include('user_details_modal.php');

// Redirect if the user is not logged in or does not have the job_seeker role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker') {
    header("Location: sign_in.php");
    exit("Redirecting to login page...");
}

$user_id = $_SESSION['user_id'];

// Set active section based on URL parameter
$active_section = isset($_GET['section']) ? $_GET['section'] : 'startups';

// Get the user's name
$name_query = "SELECT name FROM Users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $name_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$name_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($name_result);

// Check verification status
$verification_status = checkVerification(false);

// Define industries
$industries = [
    'Technology' => [
        'Software Development',
        'E-commerce',
        'FinTech',
        'EdTech',
        'HealthTech',
        'AI/ML',
        'Cybersecurity',
        'Cloud Computing',
        'Digital Marketing',
        'Mobile Apps'
    ],
    'Healthcare' => [
        'Medical Services',
        'Healthcare Technology',
        'Wellness & Fitness',
        'Mental Health',
        'Telemedicine',
        'Medical Devices',
        'Healthcare Analytics'
    ],
    'Finance' => [
        'Banking',
        'Insurance',
        'Investment',
        'Financial Services',
        'Payment Solutions',
        'Cryptocurrency',
        'Financial Planning'
    ],
    'Education' => [
        'Online Learning',
        'Educational Technology',
        'Skills Training',
        'Language Learning',
        'Professional Development',
        'Educational Content'
    ],
    'Retail' => [
        'E-commerce',
        'Fashion',
        'Food & Beverage',
        'Consumer Goods',
        'Marketplace',
        'Retail Technology'
    ],
    'Manufacturing' => [
        'Industrial Manufacturing',
        'Clean Technology',
        '3D Printing',
        'Supply Chain',
        'Smart Manufacturing'
    ],
    'Agriculture' => [
        'AgTech',
        'Organic Farming',
        'Food Processing',
        'Agricultural Services',
        'Sustainable Agriculture'
    ],
    'Transportation' => [
        'Logistics',
        'Ride-sharing',
        'Delivery Services',
        'Transportation Technology',
        'Smart Mobility'
    ],
    'Real Estate' => [
        'Property Technology',
        'Real Estate Services',
        'Property Management',
        'Real Estate Investment',
        'Smart Homes'
    ],
    'Other' => [
        'Social Impact',
        'Environmental',
        'Creative Industries',
        'Sports & Entertainment',
        'Other Services'
    ]
];

// Define Philippine regions and cities
$locations = [
    'National Capital Region (NCR)' => [
        'Manila',
        'Quezon City',
        'Caloocan',
        'Las Piñas',
        'Makati',
        'Malabon',
        'Mandaluyong',
        'Marikina',
        'Muntinlupa',
        'Navotas',
        'Parañaque',
        'Pasay',
        'Pasig',
        'Pateros',
        'San Juan',
        'Taguig',
        'Valenzuela'
    ],
    'Cordillera Administrative Region (CAR)' => [
        'Baguio City',
        'Tabuk City',
        'La Trinidad',
        'Bangued',
        'Lagawe',
        'Bontoc'
    ],
    'Ilocos Region (Region I)' => [
        'San Fernando City',
        'Laoag City',
        'Vigan City',
        'Dagupan City',
        'San Carlos City',
        'Urdaneta City'
    ],
    'Cagayan Valley (Region II)' => [
        'Tuguegarao City',
        'Cauayan City',
        'Santiago City',
        'Ilagan City'
    ],
    'Central Luzon (Region III)' => [
        'San Fernando City',
        'Angeles City',
        'Olongapo City',
        'Malolos City',
        'Cabanatuan City',
        'San Jose City',
        'Science City of Muñoz',
        'Palayan City'
    ],
    'CALABARZON (Region IV-A)' => [
        'Calamba City',
        'San Pablo City',
        'Antipolo City',
        'Batangas City',
        'Cavite City',
        'Lipa City',
        'San Pedro',
        'Santa Rosa',
        'Tagaytay City',
        'Trece Martires City'
    ],
    'MIMAROPA (Region IV-B)' => [
        'Calapan City',
        'Puerto Princesa City',
        'San Jose',
        'Romblon'
    ],
    'Bicol Region (Region V)' => [
        'Naga City',
        'Legazpi City',
        'Iriga City',
        'Ligao City',
        'Tabaco City',
        'Sorsogon City'
    ],
    'Western Visayas (Region VI)' => [
        'Iloilo City',
        'Bacolod City',
        'Roxas City',
        'Passi City'
    ],
    'Central Visayas (Region VII)' => [
        'Cebu City',
        'Mandaue City',
        'Lapu-Lapu City',
        'Talisay City',
        'Toledo City',
        'Dumaguete City'
    ],
    'Eastern Visayas (Region VIII)' => [
        'Tacloban City',
        'Ormoc City',
        'Calbayog City',
        'Catbalogan City',
        'Maasin City'
    ],
    'Zamboanga Peninsula (Region IX)' => [
        'Zamboanga City',
        'Dipolog City',
        'Dapitan City',
        'Isabela City',
        'Pagadian City'
    ],
    'Northern Mindanao (Region X)' => [
        'Cagayan de Oro City',
        'Iligan City',
        'Malaybalay City',
        'Valencia City',
        'Gingoog City'
    ],
    'Davao Region (Region XI)' => [
        'Davao City',
        'Digos City',
        'Mati City',
        'Panabo City',
        'Tagum City'
    ],
    'SOCCSKSARGEN (Region XII)' => [
        'Koronadal City',
        'General Santos City',
        'Kidapawan City',
        'Tacurong City'
    ],
    'Caraga (Region XIII)' => [
        'Butuan City',
        'Surigao City',
        'Bislig City',
        'Tandag City',
        'Cabadbaran City'
    ],
    'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' => [
        'Cotabato City',
        'Marawi City',
        'Lamitan City'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* --- Unified styling for sidebar, startups, entrepreneurs, job seekers --- */
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
            min-height: 100vh;
            border-right: 1px solid #3a3b3c;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: width 0.3s;
            box-sizing: border-box;
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
            padding: 0 24px;
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
            max-width: 100vw;
            min-width: 0;
            box-sizing: border-box;
            margin-left: 260px;
            margin-top: 88px;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 32px;
            box-sizing: border-box;
        }
        @media (max-width: 1100px) {
            .container {
                padding: 0 10px;
            }
            .main-content {
                padding: 24px 0;
            }
        }
        @media (max-width: 900px) {
            .sidebar-toggle {
                display: block;
            }
            .sidebar {
                width: 70px;
                padding: 0;
            }
            .sidebar.collapsed {
                width: 0 !important;
                min-width: 0 !important;
                overflow: hidden;
                padding: 0 !important;
            }
            .sidebar-profile {
                margin-bottom: 16px;
            }
            .sidebar-username {
                display: none;
            }
            .sidebar-link {
                justify-content: center;
                padding: 12px 0;
                font-size: 1.2rem;
            }
            .sidebar-link span {
                display: none;
            }
            .main-content {
                padding: 10px 0;
                margin-left: 70px;
                margin-top: 88px;
            }
            .container {
                padding: 0 4px;
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
                border-right: none;
                border-bottom: 1px solid #3a3b3c;
                padding: 0;
                align-items: center;
                justify-content: space-between;
            }
            .sidebar-profile {
                flex-direction: row;
                margin-bottom: 0;
                margin-left: 16px;
            }
            .sidebar-avatar {
                width: 40px;
                height: 40px;
                font-size: 28px;
                margin-bottom: 0;
            }
            .sidebar-nav {
                flex-direction: row;
                gap: 0;
                padding: 0 8px;
                width: auto;
            }
            .sidebar-link {
                padding: 8px 10px;
                font-size: 1.1rem;
            }
            .main-content {
                padding: 10px 0;
                margin-left: 0;
                margin-top: 88px;
            }
            .container {
                padding: 0 2px;
            }
            .startup-header, .user-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 12px;
            }
            .startup-logo, .user-avatar {
                width: 60px !important;
                height: 60px !important;
                margin-bottom: 8px;
            }
            .startup-info h3, .user-info h3 {
                font-size: 1.1rem;
            }
            .startup-post, .user-card {
                padding: 12px;
                margin: 10px 0;
            }
            .action-buttons {
                flex-direction: row;
                gap: 6px;
            }
            .action-buttons .btn {
                font-size: 0.9rem;
                padding: 5px 10px;
            }
            h2 {
                font-size: 1.2rem;
            }
        }
        @media (max-width: 500px) {
            .container {
                padding: 0 1px;
            }
            .main-content {
                padding: 4px 0;
            }
            .startup-post, .user-card {
                padding: 6px;
            }
        }
        h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #ffffff;
        }
        .user-name {
            color: #ea580c;
        }
        h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #ea580c;
            margin-bottom: 20px;
            text-align: center;
        }
        .startup-post, .user-card {
            background-color: #2d2d2d;
            padding: 25px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
            border: 1px solid #404040;
            width: 100%;
            box-sizing: border-box;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .startup-post:hover, .user-card:hover {
            box-shadow: 0 8px 24px rgba(234, 88, 12, 0.15);
            transform: translateY(-2px);
            border-color: #ea580c;
        }
        .startup-header, .user-header {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .startup-logo, .user-avatar {
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 50%;
            overflow: hidden;
            background-color: #333333;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 2px solid #ea580c;
            box-shadow: 0 4px 8px rgba(234, 88, 12, 0.2);
        }
        .startup-logo img, .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }
        .startup-logo i, .user-avatar i {
            font-size: 40px;
            color: #ea580c;
        }
        .startup-info h3, .user-info h3 {
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 12px;
            margin-top: 0;
        }
        .startup-info p, .user-info p {
            font-size: 1rem;
            color: #b0b0b0;
            margin: 8px 0;
            line-height: 1.5;
        }
        .startup-actions, .user-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: center;
        }
        .startup-actions .btn, .user-actions .btn, .action-buttons .btn {
            padding: 6px 16px;
            font-size: 0.95rem;
            border-radius: 12px;
            font-weight: 500;
            background: transparent;
            color: #ea580c;
            border: 1.5px solid #ea580c;
            box-shadow: none;
            transition: background 0.2s, color 0.2s, border 0.2s;
            min-width: unset;
            text-decoration: none;
        }
        .startup-actions .btn-warning, .user-actions .btn-warning, .action-buttons .btn-warning {
            color: #ea580c;
            background: transparent;
            border: 1.5px solid #ea580c;
        }
        .startup-actions .btn-info, .user-actions .btn-info, .action-buttons .btn-info {
            color: #ea580c;
            background: transparent;
            border: 1.5px solid #ea580c;
        }
        .startup-actions .btn:hover, .user-actions .btn:hover, .action-buttons .btn:hover {
            background: #ea580c;
            color: #fff;
            border-color: #ea580c;
        }
        .btn-secondary {
            background-color: #2d2d2d;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #ea580c;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #ea580c;
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: #ea580c;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #c2410c;
            transform: translateY(-2px);
        }
        .verification-notice {
            background: #2d2d2d;
            border: 1px solid #ea580c;
            color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.15);
        }
        .verification-notice h3 {
            color: #ea580c;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .verification-notice p {
            color: #b0b0b0;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .verification-notice ul li {
            color: #b0b0b0;
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.5;
        }
        .verification-notice .btn-warning {
            background: #ea580c;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        .verification-notice .btn-warning:hover {
            background: #c2410c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
        }
        .tab-navigation {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            justify-content: center;
            background: #2d2d2d;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #404040;
        }
        .tab-btn {
            padding: 12px 24px;
            border: none;
            background-color: transparent;
            color: #b0b0b0;
            cursor: pointer;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .tab-btn:hover {
            background-color: #404040;
            color: #ffffff;
        }
        .tab-btn.active {
            background-color: #ea580c;
            color: #ffffff;
        }
        .tab {
            padding: 15px 30px;
            font-size: 1.1em;
            color: #B9BBBE;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        .tab:hover {
            color: #FFFFFF;
        }
        .tab.active {
            color: #ea580c;
        }
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #ea580c;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-badge {
            background: #7289DA;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8em;
            margin-left: 8px;
        }
        /* --- END unified styling --- */

        /* --- Restore original jobs section styling --- */
        /* Place the original .job-post, .job-header, .job-details, .btn-apply, .btn-view-application, .application-badge, etc. styles here (copied from previous version) */
        .job-post {
            background-color: #2C2F33;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #40444B;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
        }
        .job-post:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .job-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #40444B;
        }
        .startup-logo {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            border-radius: 50%;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ea580c;
            padding: 10px;
        }
        .startup-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .startup-logo .default-logo-icon {
            font-size: 32px;
            color: #ea580c;
        }
        .job-title-info {
            flex-grow: 1;
        }
        .job-title-info h3 {
            font-size: 1.5rem;
            color: #ea580c;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        .startup-name {
            color: #B9BBBE;
            font-size: 1.1rem;
            margin: 0;
        }
        .job-details {
            color: #B9BBBE;
        }
        .job-details p {
            margin: 10px 0;
            line-height: 1.6;
        }
        .job-details strong {
            color: #ea580c;
            font-weight: 500;
        }
        .application-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            text-transform: capitalize;
            font-size: 0.9em;
            white-space: nowrap;
        }
        .status-applied { background: #7289DA; color: white; }
        .status-reviewed { background: #ea580c; color: white; }
        .status-interviewed { background: #43B581; color: white; }
        .status-hired { background: #43B581; color: white; }
        .status-rejected { background: #F04747; color: white; }
        .btn-apply {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ea580c;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }
        .btn-apply:hover {
            background-color: #c2410c;
        }
        .btn-view-application {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2C2F33;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #ea580c;
            transition: all 0.3s ease;
            margin-top: 15px;
        }
        .btn-view-application:hover {
            background-color: #ea580c;
            border-color: #ea580c;
        }
        /* Verification Notice Styles */
        .verification-notice {
            background: #2d2d2d;
            border: 1px solid #ea580c;
            color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.15);
        }
        .verification-notice h3 {
            color: #ea580c;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .verification-notice p {
            color: #b0b0b0;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .verification-notice ul li {
            color: #b0b0b0;
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.5;
        }
        .verification-notice .btn-warning {
            background: #ea580c;
            color: #ffffff;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        .verification-notice .btn-warning:hover {
            background: #c2410c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
        }
        /* --- END jobs section override --- */

        /* --- Restore jobs section filter and Select2 styles --- */
        #section-jobs h1 {
            color: #ea580c;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 16px;
            text-align: left;
            border-bottom: 3px solid #ea580c;
            padding-bottom: 8px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filter-form input,
        .filter-form select,
        .filter-form button {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #40444B;
            background: #fff;
            color: #222;
        }
        .filter-form input {
            min-width: 150px;
        }
        .filter-form button {
            background: #ea580c;
            color: white;
            cursor: pointer;
            border: none;
            transition: background 0.3s ease;
        }
        .filter-form button:hover {
            background: #c2410c;
        }
        /* Select2 Custom Styles for jobs section */
        #section-jobs .select2-container--default .select2-selection--single {
            background-color: #fff;
            border: 1px solid #40444B;
            border-radius: 6px;
            color: #222;
            height: 42px;
            overflow: hidden;
        }
        #section-jobs .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #222;
            line-height: 42px;
            padding-left: 15px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #section-jobs .select2-container--default .select2-results__option {
            background-color: #fff;
            color: #222;
            padding: 10px 15px;
            text-align: left;
        }
        #section-jobs .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ea580c;
            color: #fff;
        }
        #section-jobs .select2-dropdown {
            background-color: #fff;
            border: 1px solid #40444B;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: auto;
        }
        #section-jobs .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #888;
        }
        #section-jobs .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #ea580c transparent transparent transparent;
        }
        #section-jobs .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #ea580c transparent;
        }
        #section-jobs .select2-results__group {
            background-color: #fff;
            color: #ea580c;
            font-weight: bold;
            padding: 8px 10px;
        }
        #section-jobs .select2-results__option {
            padding-left: 20px;
        }
        /* --- END jobs section filter and Select2 styles --- */

        /* Modern card-based styling for startups, entrepreneurs, and job seekers */
        .startups-grid, .users-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin: 30px 0;
        }

        .startup-card, .user-card {
            background-color: #2d2d2d;
            border-radius: 12px;
            border: 1px solid #404040;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        .startup-card:hover, .user-card:hover {
            transform: translateY(-5px);
            border-color: #ea580c;
            box-shadow: 0 8px 24px rgba(234, 88, 12, 0.15);
        }

        .startup-card-logo, .user-card-avatar {
            width: 100%;
            height: 180px;
            background-color: #333333;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-bottom: 1px solid #404040;
            overflow: hidden;
            box-sizing: border-box;
        }

        .startup-card-logo img, .user-card-avatar img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            position: static;
        }

        .startup-card-logo i, .user-card-avatar i {
            font-size: 60px;
            color: #ea580c;
            position: static;
            max-width: 100%;
            max-height: 100%;
        }

        .startup-card-content, .user-card-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .startup-card-title, .user-card-title {
            font-size: 1.3rem;
            color: #ffffff;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .startup-card-info, .user-card-info {
            color: #e4e6eb;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .startup-card-info p, .user-card-info p {
            margin: 8px 0;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .startup-card-info strong, .user-card-info strong {
            color: #ffffff;
        }

        .startup-card-actions, .user-card-actions {
            padding: 15px 20px;
            background-color: #23272A;
            border-top: 1px solid #404040;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .startup-card-actions .btn, .user-card-actions .btn {
            flex: 1;
            text-align: center;
            padding: 10px;
            font-size: 0.9rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            border: 1px solid #ea580c;
            text-decoration: none;
            cursor: pointer;
        }

        .startup-card-actions .btn-info, .user-card-actions .btn-info {
            background-color: #ea580c;
            color: #ffffff;
        }

        .startup-card-actions .btn-info:hover, .user-card-actions .btn-info:hover {
            background-color: #c2410c;
            text-decoration: none;
        }

        .startup-card-actions .btn-warning, .user-card-actions .btn-warning {
            background-color: transparent;
            color: #ffffff;
            border: 1px solid #ffffff;
        }

        .startup-card-actions .btn-warning:hover, .user-card-actions .btn-warning:hover {
            background-color: #ffffff;
            color: #2d2d2d;
            text-decoration: none;
        }

        @media (max-width: 1200px) {
            .startups-grid, .users-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .startups-grid, .users-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Search section styling */
        .search-section {
            background: #2d2d2d;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border: 1px solid #404040;
        }

        .search-section h3 {
            margin-bottom: 15px;
            color: #ffffff;
            font-size: 1.1em;
        }

        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #e4e6eb;
            font-weight: 500;
        }

        .form-group label i {
            margin-right: 5px;
            color: #ea580c;
        }

        .search-button {
            background-color: #ea580c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .search-button:hover {
            background-color: #c2410c;
        }

        .search-button i {
            margin-right: 5px;
        }

        .clear-filters {
            display: inline-block;
            margin-left: 10px;
            color: #ea580c;
            text-decoration: none;
            font-size: 0.9em;
        }

        .clear-filters:hover {
            text-decoration: underline;
        }

        .clear-filters i {
            margin-right: 5px;
        }

        /* Section headers */
        .dashboard-section h2 {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        .dashboard-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: #ea580c;
            border-radius: 2px;
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #404040;
            border-radius: 6px;
            background-color: #2d2d2d;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
            color: #e4e6eb;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #e4e6eb transparent transparent transparent;
        }
        
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #e4e6eb transparent;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ea580c;
            color: #ffffff;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #404040;
            border-radius: 6px;
            background-color: #2d2d2d;
            color: #e4e6eb;
        }
        
        .select2-dropdown {
            border: 1px solid #404040;
            border-radius: 6px;
            background-color: #2d2d2d;
        }
        
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #404040;
            color: #ffffff;
        }
        
        .select2-container--default .select2-results__option {
            padding: 8px 12px;
            color: #e4e6eb;
        }
        
        .select2-container--default .select2-results__group {
            padding: 6px 12px;
            font-weight: bold;
            background-color: #23272a;
            color: #e4e6eb;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #b0b0b0;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            color: #b0b0b0;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #ffffff;
        }
    </style>
</head>

<body>

<div class="dashboard-wrapper">
    <?php include('sidebar.php'); ?>
    <div class="main-content">
        <div class="container">
            <?php if ($verification_status !== 'verified'): ?>
                <div class="verification-notice">
                    <h3><i class="fas fa-exclamation-triangle"></i> Account Verification Required</h3>
                    <p>Your account needs to be verified to access the following features:</p>
                    <ul>
                        <li>Applying for job positions</li>
                        <li>Viewing detailed job descriptions</li>
                        <li>Communicating with employers</li>
                        <li>Managing job applications</li>
                        <li>Accessing career opportunities</li>
                    </ul>
                    <a href="verify_account.php" class="btn btn-warning">Verify Your Account</a>
                </div>
            <?php endif; ?>

            <!-- Startups Section -->
            <div id="section-startups" class="dashboard-section" style="display:none;">
                <h2>Startup Showcase</h2>
                <div class="startups-grid">
                    <?php
                    $startups_query = "
                        SELECT * FROM Startups 
                        WHERE approval_status = 'approved'
                        ORDER BY created_at DESC";
                    $startups_result = mysqli_query($conn, $startups_query);
                    if (mysqli_num_rows($startups_result) > 0):
                        while ($startup = mysqli_fetch_assoc($startups_result)): ?>
                            <div class="startup-card">
                                <div class="startup-card-logo">
                                    <?php if (!empty($startup['logo_url']) && file_exists($startup['logo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($startup['logo_url']); ?>" alt="<?php echo htmlspecialchars($startup['name']); ?> logo">
                                    <?php else: ?>
                                        <i class="fas fa-building"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="startup-card-content">
                                    <h3 class="startup-card-title"><?php echo htmlspecialchars($startup['name']); ?></h3>
                                    <div class="startup-card-info">
                                        <p><strong>Industry:</strong> <?php echo htmlspecialchars($startup['industry']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($startup['description'], 0, 100)) . '...'; ?></p>
                                    </div>
                                </div>
                                <div class="startup-card-actions">
                                    <a href="startup_detail.php?startup_id=<?php echo $startup['startup_id']; ?>" class="btn btn-info">View Details</a>
                                </div>
                            </div>
                    <?php endwhile;
                    else:
                        echo "<p>No startups found.</p>";
                    endif;
                    ?>
                </div>
            </div>

            <!-- Entrepreneurs Section -->
            <div id="section-entrepreneurs" class="dashboard-section" style="display:none;">
                <h2>Entrepreneurs</h2>
                <div class="search-section">
                    <h3><i class="fas fa-search"></i> Search & Filter Entrepreneurs</h3>
                    <form id="search-filter-form-entrepreneurs" method="GET" action="job-seekers.php">
                        <input type="hidden" name="section" value="entrepreneurs">
                        <div class="search-grid">
                            <div class="form-group">
                                <label for="industry-entrepreneurs"><i class="fas fa-industry"></i> Industry</label>
                                <select id="industry-entrepreneurs" name="industry" class="select2">
                                    <option value="">All Industries</option>
                                    <?php foreach ($industries as $category => $subcategories): ?>
                                        <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?php echo htmlspecialchars($subcategory); ?>" <?php echo (isset($_GET['industry']) && $_GET['industry'] === $subcategory) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($subcategory); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location-entrepreneurs"><i class="fas fa-map-marker-alt"></i> Location</label>
                                <select id="location-entrepreneurs" name="location" class="select2">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations as $region => $cities): ?>
                                        <optgroup label="<?php echo htmlspecialchars($region); ?>">
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo (isset($_GET['location']) && $_GET['location'] === $city) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($city); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="search-button">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <?php if (isset($_GET['industry']) || isset($_GET['location'])): ?>
                            <a href="?section=entrepreneurs" class="clear-filters">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <?php
                $entrepreneurs_query = "
                    SELECT u.*, e.*
                    FROM Users u
                    JOIN Entrepreneurs e ON u.user_id = e.entrepreneur_id
                    WHERE u.verification_status = 'verified'";
                if (isset($_GET['industry']) && $_GET['industry'] != "") {
                    $industry = mysqli_real_escape_string($conn, $_GET['industry']);
                    $entrepreneurs_query .= " AND u.industry = '$industry'";
                }
                if (isset($_GET['location']) && $_GET['location'] != "") {
                    $location = mysqli_real_escape_string($conn, $_GET['location']);
                    $entrepreneurs_query .= " AND u.location = '$location'";
                }
                $entrepreneurs_query .= " ORDER BY u.name ASC LIMIT 20";
                $entrepreneurs_result = mysqli_query($conn, $entrepreneurs_query);
                ?>
                <div class="users-grid">
                    <?php if (mysqli_num_rows($entrepreneurs_result) > 0):
                        while ($entrepreneur_row = mysqli_fetch_assoc($entrepreneurs_result)):
                    ?>
                            <div class="user-card">
                                <div class="user-card-avatar">
                                    <?php if (!empty($entrepreneur_row['profile_picture_url']) && file_exists($entrepreneur_row['profile_picture_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($entrepreneur_row['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($entrepreneur_row['name']); ?>'s profile picture">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="user-card-content">
                                    <h3 class="user-card-title"><?php echo htmlspecialchars($entrepreneur_row['name']); ?></h3>
                                    <div class="user-card-info">
                                        <p><strong>Industry:</strong> <?php echo htmlspecialchars($entrepreneur_row['industry'] ?? 'Not specified'); ?></p>
                                        <?php if (!empty($entrepreneur_row['bio'])): ?>
                                            <p><strong>Bio:</strong> <?php echo htmlspecialchars(substr($entrepreneur_row['bio'], 0, 100)) . '...'; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($entrepreneur_row['location'])): ?>
                                            <p><strong>Location:</strong> <?php echo htmlspecialchars($entrepreneur_row['location']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-card-actions">
                                    <a href="profile.php?user_id=<?php echo $entrepreneur_row['user_id']; ?>" class="btn btn-info">View Profile</a>
                                    <a href="messages.php?chat_with=<?php echo $entrepreneur_row['user_id']; ?>" class="btn btn-info">Message</a>
                                </div>
                            </div>
                    <?php endwhile;
                    else:
                        echo "<p>No entrepreneurs found.</p>";
                    endif;
                    ?>
                </div>
            </div>

            <!-- Jobs Section -->
            <div id="section-jobs" class="dashboard-section">
                <!-- Tabs -->
                <div class="tabs">
                    <div class="tab active" onclick="switchTab('available-jobs')">
                        Available Jobs
                    </div>
                    <div class="tab" onclick="switchTab('my-applications')">
                        My Applications
                        <?php
                        // Count user's applications
                        $applications_count_query = "SELECT COUNT(*) as count FROM Applications WHERE job_seeker_id = '$user_id'";
                        $applications_count_result = mysqli_query($conn, $applications_count_query);
                        $applications_count = mysqli_fetch_assoc($applications_count_result)['count'];
                        if ($applications_count > 0): ?>
                            <span class="tab-badge"><?php echo $applications_count; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Available Jobs Tab -->
                <div id="available-jobs" class="tab-content active">
                    <!-- Filter Form -->
                    <div class="search-section">
                        <h3><i class="fas fa-search"></i> Search & Filter Jobs</h3>
                        <form id="search-filter-form-jobs" method="GET" action="job-seekers.php">
                            <input type="hidden" name="tab" value="available-jobs">
                            <div class="search-grid">
                                <div class="form-group">
                                    <label for="industry-jobs"><i class="fas fa-industry"></i> Industry</label>
                                    <select id="industry-jobs" name="industry" class="select2">
                                        <option value="">All Industries</option>
                                        <?php foreach ($industries as $category => $subcategories): ?>
                                            <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                                <?php foreach ($subcategories as $subcategory): ?>
                                                    <option value="<?php echo htmlspecialchars($subcategory); ?>" 
                                                        <?php echo (isset($_GET['industry']) && $_GET['industry'] == $subcategory) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($subcategory); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="location-jobs"><i class="fas fa-map-marker-alt"></i> Location</label>
                                    <select id="location-jobs" name="location" class="select2">
                                        <option value="">All Locations</option>
                                        <?php foreach ($locations as $region => $cities): ?>
                                            <optgroup label="<?php echo htmlspecialchars($region); ?>">
                                                <?php foreach ($cities as $city): ?>
                                                    <option value="<?php echo htmlspecialchars($city); ?>"
                                                        <?php echo (isset($_GET['location']) && $_GET['location'] == $city) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($city); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="salary_min-jobs">Min Salary</label>
                                    <input type="number" id="salary_min-jobs" name="salary_min" placeholder="Min Salary"
                                        value="<?php echo isset($_GET['salary_min']) ? htmlspecialchars($_GET['salary_min']) : ''; ?>">
                                </div>
                            </div>
                            <button type="submit" class="search-button">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </form>
                    </div>

                    <?php
                    // Build query with filters
                    $filter_conditions = "1=1"; // Default condition to simplify concatenation
                    if (isset($_GET['industry']) && $_GET['industry'] != "") {
                        $industry = mysqli_real_escape_string($conn, $_GET['industry']);
                        $filter_conditions .= " AND Startups.industry = '$industry'";
                    }
                    if (isset($_GET['location']) && $_GET['location'] != "") {
                        $location = mysqli_real_escape_string($conn, $_GET['location']);
                        $filter_conditions .= " AND Jobs.location = '$location'";
                    }
                    if (isset($_GET['salary_min']) && $_GET['salary_min'] != "") {
                        $salary_min = (int) $_GET['salary_min'];
                        $filter_conditions .= " AND Jobs.salary_range_max >= $salary_min";
                    }

                    // Query to fetch available jobs (excluding applied ones)
                    $available_jobs_query = "
                        SELECT Jobs.job_id, Jobs.role, Jobs.description, Jobs.requirements, Jobs.location, 
                               Jobs.salary_range_min, Jobs.salary_range_max, 
                               Startups.name AS startup_name, Startups.industry, Startups.logo_url
                        FROM Jobs 
                        JOIN Startups ON Jobs.startup_id = Startups.startup_id
                        LEFT JOIN Applications ON Jobs.job_id = Applications.job_id 
                            AND Applications.job_seeker_id = '$user_id'
                        WHERE Applications.job_id IS NULL AND Jobs.status = 'active' AND $filter_conditions
                    ";
                    $available_jobs_result = mysqli_query($conn, $available_jobs_query);

                    if (mysqli_num_rows($available_jobs_result) > 0) {
                        while ($job = mysqli_fetch_assoc($available_jobs_result)): ?>
                            <div class="job-post">
                                <div class="job-header">
                                    <div class="startup-logo">
                                        <?php if (!empty($job['logo_url']) && file_exists($job['logo_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($job['logo_url']); ?>" alt="<?php echo htmlspecialchars($job['startup_name']); ?> logo">
                                        <?php else: ?>
                                            <i class="fas fa-building default-logo-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="job-title-info">
                                        <h3><?php echo htmlspecialchars($job['role']); ?></h3>
                                        <p class="startup-name"><?php echo htmlspecialchars($job['startup_name']); ?></p>
                                    </div>
                                </div>
                                <div class="job-details">
                                    <p><strong>Industry:</strong> <?php echo htmlspecialchars($job['industry']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                                    <p><strong>Salary:</strong> ₱<?php echo number_format($job['salary_range_min'], 2); ?> -
                                        ₱<?php echo number_format($job['salary_range_max'], 2); ?></p>
                                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                                    <p><strong>Requirements:</strong> <?php echo nl2br(htmlspecialchars($job['requirements'])); ?></p>
                                    <?php if ($verification_status === 'verified'): ?>
                                        <a href="apply_job.php?job_id=<?php echo $job['job_id']; ?>" class="btn-apply">Apply</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile;
                    } else {
                        echo "<p>No available jobs found with the current filters.</p>";
                    }
                    ?>
                </div>
                <!-- My Applications Tab -->
                <div id="my-applications" class="tab-content">
                    <?php
                    // Query to fetch user's applications
                    $applications_query = "
                        SELECT Jobs.job_id, Jobs.role, Jobs.description, Jobs.requirements, Jobs.location, 
                               Jobs.salary_range_min, Jobs.salary_range_max, 
                               Startups.name AS startup_name, Startups.industry, Startups.logo_url,
                               Applications.status as application_status,
                               Applications.created_at as applied_date
                        FROM Applications
                        JOIN Jobs ON Applications.job_id = Jobs.job_id
                        JOIN Startups ON Jobs.startup_id = Startups.startup_id
                        WHERE Applications.job_seeker_id = '$user_id'
                        ORDER BY Applications.created_at DESC
                    ";
                    $applications_result = mysqli_query($conn, $applications_query);

                    if (mysqli_num_rows($applications_result) > 0) {
                        while ($application = mysqli_fetch_assoc($applications_result)): ?>
                            <div class="job-post">
                                <div class="job-header">
                                    <div class="startup-logo">
                                        <?php if (!empty($application['logo_url']) && file_exists($application['logo_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($application['logo_url']); ?>" alt="<?php echo htmlspecialchars($application['startup_name']); ?> logo">
                                        <?php else: ?>
                                            <i class="fas fa-building default-logo-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="job-title-info">
                                        <h3><?php echo htmlspecialchars($application['role']); ?></h3>
                                        <p class="startup-name"><?php echo htmlspecialchars($application['startup_name']); ?></p>
                                    </div>
                                    <div class="application-badge status-<?php echo strtolower($application['application_status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($application['application_status'])); ?>
                                    </div>
                                </div>
                                <div class="job-details">
                                    <p><strong>Applied on:</strong> <?php echo date('F j, Y', strtotime($application['applied_date'])); ?></p>
                                    <p><strong>Industry:</strong> <?php echo htmlspecialchars($application['industry']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($application['location']); ?></p>
                                    <p><strong>Salary:</strong> ₱<?php echo number_format($application['salary_range_min'], 2); ?> -
                                        ₱<?php echo number_format($application['salary_range_max'], 2); ?></p>
                                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($application['description'])); ?></p>
                                    <p><strong>Requirements:</strong> <?php echo nl2br(htmlspecialchars($application['requirements'])); ?></p>
                                    <a href="apply_job.php?job_id=<?php echo $application['job_id']; ?>" class="btn-view-application">View Application</a>
                                </div>
                            </div>
                        <?php endwhile;
                    } else {
                        echo "<p>You haven't applied to any jobs yet.</p>";
                    }
                    ?>
                </div>
            </div>
            <!-- Job Seekers Section -->
            <div id="section-job-seekers" class="dashboard-section" style="display:none;">
                <h2>Relevant Job Seekers</h2>
                <div class="search-section">
                    <h3><i class="fas fa-search"></i> Search & Filter Job Seekers</h3>
                    <form id="search-filter-form-jobseekers" method="GET" action="job-seekers.php">
                        <input type="hidden" name="section" value="job-seekers">
                        <div class="search-grid">
                            <div class="form-group">
                                <label for="industry-jobseekers"><i class="fas fa-industry"></i> Industry</label>
                                <select id="industry-jobseekers" name="industry" class="select2">
                                    <option value="">All Industries</option>
                                    <?php foreach ($industries as $category => $subcategories): ?>
                                        <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?php echo htmlspecialchars($subcategory); ?>" <?php echo (isset($_GET['industry']) && $_GET['industry'] === $subcategory) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($subcategory); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location-jobseekers"><i class="fas fa-map-marker-alt"></i> Location</label>
                                <select id="location-jobseekers" name="location" class="select2">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations as $region => $cities): ?>
                                        <optgroup label="<?php echo htmlspecialchars($region); ?>">
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo (isset($_GET['location']) && $_GET['location'] === $city) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($city); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="search-button">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <?php if (isset($_GET['industry']) || isset($_GET['location'])): ?>
                            <a href="?section=job-seekers" class="clear-filters">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="users-grid">
                    <?php
                    $job_seekers_query = "
                        SELECT DISTINCT u.*, js.*, u.profile_picture_url, u.industry
                        FROM Users u
                        JOIN job_seekers js ON u.user_id = js.job_seeker_id
                        WHERE u.verification_status = 'verified'";
                    if (isset($_GET['industry']) && $_GET['industry'] != "") {
                        $industry = mysqli_real_escape_string($conn, $_GET['industry']);
                        $job_seekers_query .= " AND u.industry = '$industry'";
                    }
                    if (isset($_GET['location']) && $_GET['location'] != "") {
                        $location = mysqli_real_escape_string($conn, $_GET['location']);
                        $job_seekers_query .= " AND js.location_preference = '$location'";
                    }
                    $job_seekers_query .= " GROUP BY u.user_id ORDER BY u.name ASC LIMIT 20";
                    $job_seekers_result = mysqli_query($conn, $job_seekers_query);
                    if (mysqli_num_rows($job_seekers_result) > 0):
                        while ($job_seeker = mysqli_fetch_assoc($job_seekers_result)):
                    ?>
                            <div class="user-card">
                                <div class="user-card-avatar">
                                    <?php if (!empty($job_seeker['profile_picture_url']) && file_exists($job_seeker['profile_picture_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($job_seeker['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($job_seeker['name']); ?>'s profile picture">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="user-card-content">
                                    <h3 class="user-card-title"><?php echo htmlspecialchars($job_seeker['name']); ?></h3>
                                    <div class="user-card-info">
                                        <p><strong>Industry:</strong> <?php echo htmlspecialchars($job_seeker['industry'] ?? 'Not specified'); ?></p>
                                        <?php if (!empty($job_seeker['bio'])): ?>
                                            <p><strong>Introduction:</strong> <?php echo htmlspecialchars(substr($job_seeker['bio'], 0, 100)) . '...'; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($job_seeker['location_preference'])): ?>
                                            <p><strong>Location:</strong> <?php echo htmlspecialchars($job_seeker['location_preference']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-card-actions">
                                    <a href="profile.php?user_id=<?php echo $job_seeker['user_id']; ?>" class="btn btn-info">View Profile</a>
                                    <a href="messages.php?chat_with=<?php echo $job_seeker['user_id']; ?>" class="btn btn-info">Message</a>
                                </div>
                            </div>
                    <?php 
                        endwhile;
                    else:
                        echo "<p>No job seekers found.</p>";
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for all filter dropdowns in jobs section and new filters
        $('#industry-jobs, #location-jobs, #industry-entrepreneurs, #location-entrepreneurs, #industry-jobseekers, #location-jobseekers').select2({
            theme: 'default',
            width: '100%',
            placeholder: 'Search or select an option',
            allowClear: true,
            minimumInputLength: 0,
            dropdownAutoWidth: true,
            scrollAfterSelect: true,
            closeOnSelect: true,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (typeof data.text === 'undefined') {
                    return null;
                }
                var searchStr = data.text.toLowerCase();
                if (data.element && data.element.parentElement) {
                    searchStr += ' ' + data.element.parentElement.label.toLowerCase();
                }
                if (searchStr.indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }
                return null;
            }
        });
        $('.select2-container').addClass('custom-select2');

        // Sidebar navigation functionality
        const links = document.querySelectorAll('.sidebar-link');
        const sections = document.querySelectorAll('.dashboard-section');
        
        // Show the active section based on URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeSection = urlParams.get('section') || 'startups';
        
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
                
                // Handle common links (messages, about-us, settings, logout)
                if (href.includes('messages.php') || 
                    href.includes('about-us.php') || 
                    href.includes('settings.php') || 
                    href.includes('logout.php')) {
                    return true; // Allow normal navigation
                }
                
                // Handle section-based navigation
                if (href.includes('section=')) {
                    e.preventDefault();
                    
                    // Get the section from the href
                    const section = href.split('section=')[1]?.split('&')[0] || 'startups';
                    
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

    function switchTab(tabId) {
        // Update tab buttons
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`.tab[onclick="switchTab('${tabId}')"]`).classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(tabId).classList.add('active');

        // Store active tab in session storage
        sessionStorage.setItem('activeTab', tabId);
    }

    // Restore active tab on page load
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            switchTab(tabParam);
        } else {
            const activeTab = sessionStorage.getItem('activeTab');
            if (activeTab) {
                switchTab(activeTab);
            }
        }

        <?php if (isset($_SESSION['status_message'])): ?>
            showSuccessModal("<?php echo htmlspecialchars($_SESSION['status_message']); ?>");
            <?php unset($_SESSION['status_message']); ?>
        <?php endif; ?>
    }

    // Success Modal Functions
    function showSuccessModal(message) {
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').style.display = 'block';
    }

    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('successModal');
        if (event.target == modal) {
            closeSuccessModal();
        }
    }
</script>
</body>

</html>