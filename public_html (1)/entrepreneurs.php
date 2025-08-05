<?php
session_start();
include('db_connection.php');
include('verification_check.php');
require_once 'config.php';
require_once 'entrepreneur_job_seeker_matchmaking.php';
include('navbar.php');

// Define industries array
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
        'Legazpi City',
        'Naga City',
        'Iriga City',
        'Tabaco City',
        'Sorsogon City',
        'Ligao City'
    ],
    'Western Visayas (Region VI)' => [
        'Iloilo City',
        'Bacolod City',
        'Roxas City',
        'San Carlos City',
        'Silay City',
        'Talisay City'
    ],
    'Central Visayas (Region VII)' => [
        'Cebu City',
        'Mandaue City',
        'Lapu-Lapu City',
        'Talisay City',
        'Danao City',
        'Bogo City'
    ],
    'Eastern Visayas (Region VIII)' => [
        'Tacloban City',
        'Ormoc City',
        'Calbayog City',
        'Baybay City',
        'Maasin City',
        'Catbalogan City'
    ],
    'Zamboanga Peninsula (Region IX)' => [
        'Zamboanga City',
        'Dipolog City',
        'Pagadian City',
        'Isabela City'
    ],
    'Northern Mindanao (Region X)' => [
        'Cagayan de Oro City',
        'Iligan City',
        'Oroquieta City',
        'Ozamiz City',
        'Tangub City',
        'Gingoog City'
    ],
    'Davao Region (Region XI)' => [
        'Davao City',
        'Digos City',
        'Tagum City',
        'Panabo City',
        'Samal City',
        'Mati City'
    ],
    'SOCCSKSARGEN (Region XII)' => [
        'General Santos City',
        'Koronadal City',
        'Tacurong City',
        'Kidapawan City'
    ],
    'Caraga (Region XIII)' => [
        'Butuan City',
        'Surigao City',
        'Bislig City',
        'Tandag City',
        'Bayugan City',
        'Cabadbaran City'
    ],
    'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' => [
        'Cotabato City',
        'Marawi City',
        'Lamitan City'
    ]
];

// Redirect if the user is not logged in or does not have the entrepreneur role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entrepreneur') {
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

// Retrieve the entrepreneur details
$query = "SELECT * FROM Entrepreneurs WHERE entrepreneur_id = '$user_id'";
$result = mysqli_query($conn, $query);
$entrepreneur = mysqli_fetch_assoc($result);

// Fetch startups posted by the entrepreneur and others
$startups_query = "
    SELECT * FROM Startups 
    WHERE entrepreneur_id = '$user_id' 
    OR (entrepreneur_id != '$user_id' AND approval_status = 'approved')
    ORDER BY created_at DESC";
$startups_result = mysqli_query($conn, $startups_query);

// Get filter parameters
$industry = isset($_GET['industry']) ? $_GET['industry'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$startup_stage = isset($_GET['startup_stage']) ? $_GET['startup_stage'] : '';

// Initialize query variables with default values
$startups_query = "
    SELECT s.* 
    FROM Startups s 
    WHERE (s.entrepreneur_id = '$user_id' OR (s.entrepreneur_id != '$user_id' AND s.approval_status = 'approved'))
    ORDER BY s.created_at DESC";

$cofounders_query = "
    SELECT u.*, e.*
    FROM Users u
    JOIN Entrepreneurs e ON u.user_id = e.entrepreneur_id
    WHERE u.verification_status = 'verified'
    AND u.user_id != '$user_id'
    ORDER BY u.name ASC
    LIMIT 20";

$job_seekers_query = "
    SELECT DISTINCT u.*, js.*, u.profile_picture_url, u.industry
    FROM Users u
    JOIN job_seekers js ON u.user_id = js.job_seeker_id
    WHERE u.verification_status = 'verified'
    GROUP BY u.user_id
    ORDER BY u.name ASC
    LIMIT 20";

$investors_query = "
    SELECT DISTINCT u.*, i.*, u.profile_picture_url, u.industry
    FROM Users u
    JOIN Investors i ON u.user_id = i.investor_id
    WHERE u.verification_status = 'verified'
    GROUP BY u.user_id
    ORDER BY u.name ASC
    LIMIT 20";

// Build filter conditions based on the active section
$filter_conditions = [];
$active_section = isset($_GET['section']) ? $_GET['section'] : 'startups';

switch ($active_section) {
    case 'startups':
        if (!empty($industry)) {
            $filter_conditions[] = "s.industry = '" . mysqli_real_escape_string($conn, $industry) . "'";
        }
        if (!empty($location)) {
            $filter_conditions[] = "s.location = '" . mysqli_real_escape_string($conn, $location) . "'";
        }
        if (!empty($startup_stage)) {
            $filter_conditions[] = "s.startup_stage = '" . mysqli_real_escape_string($conn, $startup_stage) . "'";
        }
        $filter_sql = !empty($filter_conditions) ? " AND " . implode(" AND ", $filter_conditions) : "";
        $startups_query = "
            SELECT s.* 
            FROM Startups s 
            WHERE (s.entrepreneur_id = '$user_id' OR (s.entrepreneur_id != '$user_id' AND s.approval_status = 'approved'))
            $filter_sql
            ORDER BY s.created_at DESC";
        break;

    case 'cofounders':
        if (!empty($industry)) {
            $filter_conditions[] = "u.industry = '" . mysqli_real_escape_string($conn, $industry) . "'";
        }
        if (!empty($location)) {
            $filter_conditions[] = "u.location = '" . mysqli_real_escape_string($conn, $location) . "'";
        }
        $filter_sql = !empty($filter_conditions) ? " AND " . implode(" AND ", $filter_conditions) : "";
        $cofounders_query = "
            SELECT u.*, e.*
            FROM Users u
            JOIN Entrepreneurs e ON u.user_id = e.entrepreneur_id
            WHERE u.verification_status = 'verified'
            AND u.user_id != '$user_id'
            $filter_sql
            ORDER BY u.name ASC
            LIMIT 20";
        break;

    case 'job-seekers':
        if (!empty($industry)) {
            $filter_conditions[] = "u.industry = '" . mysqli_real_escape_string($conn, $industry) . "'";
        }
        if (!empty($location)) {
            $filter_conditions[] = "js.location_preference = '" . mysqli_real_escape_string($conn, $location) . "'";
        }
        $filter_sql = !empty($filter_conditions) ? " AND " . implode(" AND ", $filter_conditions) : "";
        $job_seekers_query = "
            SELECT DISTINCT u.*, js.*, u.profile_picture_url, u.industry
            FROM Users u
            JOIN job_seekers js ON u.user_id = js.job_seeker_id
            WHERE u.verification_status = 'verified'
            $filter_sql
            GROUP BY u.user_id
            ORDER BY u.name ASC
            LIMIT 20";
        break;

    case 'investors':
        if (!empty($industry)) {
            $filter_conditions[] = "u.industry = '" . mysqli_real_escape_string($conn, $industry) . "'";
        }
        if (!empty($location)) {
            $filter_conditions[] = "i.preferred_locations LIKE '%" . mysqli_real_escape_string($conn, $location) . "%'";
        }
        $filter_sql = !empty($filter_conditions) ? " AND " . implode(" AND ", $filter_conditions) : "";
        $investors_query = "
            SELECT DISTINCT u.*, i.*, u.profile_picture_url, u.industry
            FROM Users u
            JOIN Investors i ON u.user_id = i.investor_id
            WHERE u.verification_status = 'verified'
            $filter_sql
            GROUP BY u.user_id
            ORDER BY u.name ASC
            LIMIT 20";
        break;
}

// Debug information
echo "<!-- Debug Info:";
echo "\nActive section: " . $active_section;
echo "\nFilter conditions: ";
print_r($filter_conditions);
echo "\nFilter SQL: " . $filter_sql;
echo "\nIndustry: " . $industry;
echo "\nLocation: " . $location;
echo "\nStartup stage: " . $startup_stage;
echo "\n-->";

// Execute the queries
$startups_result = mysqli_query($conn, $startups_query);
if (!$startups_result) {
    echo "<!-- Query Error: " . mysqli_error($conn) . " -->";
}
$cofounders_result = mysqli_query($conn, $cofounders_query);
if (!$cofounders_result) {
    echo "<!-- Query Error: " . mysqli_error($conn) . " -->";
}
$job_seekers_result = mysqli_query($conn, $job_seekers_query);
if (!$job_seekers_result) {
    echo "<!-- Query Error: " . mysqli_error($conn) . " -->";
}
$investors_result = mysqli_query($conn, $investors_query);
if (!$investors_result) {
    echo "<!-- Query Error: " . mysqli_error($conn) . " -->";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrepreneur Dashboard - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    <style>
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
        margin-top: 16px;
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
        .sidebar {
            margin-top: 16px;
        }
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
            margin-top: 0;
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
    .startup-post {
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
    .startup-post:hover {
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.15);
        transform: translateY(-2px);
        border-color: #ea580c;
    }
    .startup-header {
        display: flex;
        gap: 25px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .startup-logo {
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
    .startup-logo img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
        position: static;
    }
    .startup-logo i {
        font-size: 60px;
        color: #ea580c;
        position: static;
    }
    .startup-info h3 {
        font-size: 1.5rem;
        color: #ffffff;
        margin-bottom: 12px;
        margin-top: 0;
    }
    .startup-info p {
        font-size: 1rem;
        color: #b0b0b0;
        margin: 8px 0;
        line-height: 1.5;
    }
    .startup-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: center;
    }
    .startup-actions .btn {
        padding: 6px 16px;
        font-size: 0.95rem;
        border-radius: 6px;
        font-weight: 500;
        background: transparent;
        color: #ea580c;
        border: 1.5px solid #ea580c;
        box-shadow: none;
        transition: background 0.2s, color 0.2s, border 0.2s;
        min-width: unset;
        text-decoration: none;
    }
    .startup-actions .btn-warning {
        color: #ea580c;
        background: transparent;
        border: 1.5px solid #ea580c;
    }
    .startup-actions .btn-info {
        color: #ea580c;
        background: transparent;
        border: 1.5px solid #ea580c;
    }
    .startup-actions .btn:hover, .startup-actions .btn:focus {
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
    .user-card {
        background-color: #2d2d2d;
        padding: 25px;
        margin: 20px 0;
        border-radius: 12px;
        border: 1px solid #404040;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
        overflow-wrap: break-word;
        word-break: break-word;
    }
    .user-card:hover {
        border-color: #ea580c;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.15);
    }
    .user-header {
        display: flex;
        gap: 25px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .user-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background-color: #404040;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid #ea580c;
        box-shadow: 0 4px 8px rgba(234, 88, 12, 0.2);
    }
    .user-avatar img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
        position: static;
    }
    .user-avatar i {
        font-size: 60px;
        color: #ea580c;
        position: static;
    }
    .user-info h3 {
        margin: 0 0 12px 0;
        color: #ffffff;
        font-size: 1.4rem;
    }
    .user-details p {
        margin: 8px 0;
        color: #b0b0b0;
        line-height: 1.5;
    }
    .user-actions .btn-info {
        background-color: #ea580c;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        border: none;
    }
    .user-actions .btn-info:hover {
        background-color: #c2410c;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
    }
    .success-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.7);
    }
    .success-modal-content {
        background-color: #2d2d2d;
        margin: 10% auto;
        padding: 30px;
        border: 1px solid #ea580c;
        border-radius: 12px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        position: relative;
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.2);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .welcome-section {
        text-align: center;
        margin-bottom: 40px;
        padding: 30px 0;
        background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
        border-radius: 16px;
        border: 1px solid #404040;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }
    .welcome-section h1 {
        font-size: 2.8rem;
        font-weight: 600;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .welcome-subtitle {
        color: #b0b0b0;
        font-size: 1.1rem;
        margin-top: 10px;
        }
    .user-name {
        color: #ea580c;
        -webkit-text-fill-color: #ea580c;
        font-weight: 700;
    }
    .action-buttons-container {
        margin: 20px 0 24px;
        padding: 0;
        background: none;
        border-radius: 0;
        border: none;
        box-shadow: none;
        display: flex;
        justify-content: flex-start;
    }
    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-start;
            align-items: center;
    }
    .action-buttons .btn {
        padding: 6px 14px;
        font-size: 0.95rem;
        border-radius: 6px;
        min-width: unset;
        box-shadow: none;
        font-weight: 500;
        background: #23272a;
        color: #ea580c;
        border: 1px solid #ea580c;
        transition: background 0.2s, color 0.2s;
    }
    .action-buttons .btn-primary {
        background: transparent;
        color: #ea580c;
        border: 1.5px solid #ea580c;
        }
    .action-buttons .btn-primary:hover, .action-buttons .btn-primary:focus {
        background: #ea580c;
        color: #fff;
        border-color: #ea580c;
    }
    .action-buttons .btn-secondary {
        background: #23272a;
        color: #ea580c;
        border: 1px solid #ea580c;
    }
    .action-buttons .btn:hover {
        background: #ea580c;
        color: #fff;
        }
        .tab-navigation {
        margin-top: 40px;
        background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
        border: 1px solid #404040;
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }
    @media (max-width: 768px) {
        .welcome-section {
            padding: 20px;
            margin-bottom: 30px;
        }
        .welcome-section h1 {
            font-size: 2.2rem;
        }
        .action-buttons-container {
            margin: 20px 0 30px;
            padding: 15px;
        }
        .action-buttons {
            flex-direction: column;
        }
        .action-buttons .btn {
            width: 100%;
            justify-content: center;
        }
    }
    /* Sidebar toggle button */
    .sidebar-toggle {
        display: none;
        position: absolute;
        top: 16px;
        left: 16px;
        background: none;
        border: none;
        color: #ea580c;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 10;
    }
    html, body {
        max-width: 100vw;
        overflow-x: hidden;
    }
    .startups-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin: 30px 0;
    }

    .startup-card {
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

    .startup-card:hover {
        transform: translateY(-5px);
        border-color: #ea580c;
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.15);
    }

    .startup-card-logo,
    .user-card-avatar {
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

    .startup-card-logo img,
    .user-card-avatar img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        position: static;
    }

    .startup-card-logo i,
    .user-card-avatar i {
        font-size: 60px;
        color: #ea580c;
        position: static;
        max-width: 100%;
        max-height: 100%;
    }

    .startup-card-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .startup-card-title {
        font-size: 1.3rem;
        color: #ffffff;
        margin: 0 0 15px 0;
        font-weight: 600;
    }

    .startup-card-info {
        color: #e4e6eb;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .startup-card-info p {
        margin: 8px 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .startup-card-info strong {
        color: #ffffff;
    }

    .startup-card-actions {
        padding: 15px 20px;
        background-color: #23272A;
        border-top: 1px solid #404040;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .startup-card-actions .btn {
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

    .startup-card-actions .btn-info {
        background-color: #ea580c;
        color: #ffffff;
    }

    .startup-card-actions .btn-info:hover {
        background-color: #c2410c;
        text-decoration: none;
    }

    .startup-card-actions .btn-warning {
        background-color: transparent;
        color: #ffffff;
        border: 1px solid #ffffff;
    }

    .startup-card-actions .btn-warning:hover {
        background-color: #ffffff;
        color: #2d2d2d;
        text-decoration: none;
    }

    .startup-card-actions .btn-primary {
        background-color: #ea580c;
        color: #ffffff;
    }

    .startup-card-actions .btn-primary:hover {
        background-color: #c2410c;
        text-decoration: none;
    }

    /* Add styles for form buttons */
    .startup-card-actions form {
        flex: 1;
    }

    .startup-card-actions form button {
        width: 100%;
        text-align: center;
        padding: 10px;
        font-size: 0.9rem;
        border-radius: 6px;
        transition: all 0.3s ease;
        border: 1px solid #ea580c;
        text-decoration: none;
        cursor: pointer;
        background-color: #ea580c;
        color: #ffffff;
    }

    .startup-card-actions form button:hover {
        background-color: #c2410c;
        text-decoration: none;
    }

    @media (max-width: 1200px) {
        .startups-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .startups-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Add these new styles for user cards */
    .users-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin: 30px 0;
    }

    .user-card {
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

    .user-card:hover {
        transform: translateY(-5px);
        border-color: #ea580c;
        box-shadow: 0 8px 24px rgba(234, 88, 12, 0.15);
    }

    .user-card-avatar {
        width: 100%;
        height: 180px;
        background-color: #333333;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border-bottom: 1px solid #404040;
        position: relative;
        overflow: hidden;
    }

    .user-card-avatar img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
        position: static;
    }

    .user-card-avatar i {
        font-size: 60px;
        color: #ea580c;
        position: static;
    }

    .user-card-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .user-card-title {
        font-size: 1.3rem;
        color: #ffffff;
        margin: 0 0 15px 0;
        font-weight: 600;
    }

    .user-card-info {
        color: #e4e6eb;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .user-card-info p {
        margin: 8px 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .user-card-info strong {
        color: #ffffff;
    }

    .user-card-actions {
        padding: 15px 20px;
        background-color: #23272A;
        border-top: 1px solid #404040;
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .user-card-actions .btn {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 0.9rem;
        border-radius: 12px;
        transition: all 0.3s ease;
        border: 1px solid #ea580c;
        text-decoration: none;
        cursor: pointer;
    }

    .user-card-actions .btn-info {
        background-color: #ea580c;
        color: #ffffff;
    }

    .user-card-actions .btn-info:hover {
        background-color: #c2410c;
        text-decoration: none;
    }

    @media (max-width: 1200px) {
        .users-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .users-grid {
            grid-template-columns: 1fr;
        }
    }

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
        background-color: #333333;
        border-bottom: 1px solid #404040;
    }

    .startup-card-logo i, .user-card-avatar i {
        color: #ffffff;
    }

    .startup-card-actions, .user-card-actions {
        background-color: #23272A;
        border-top: 1px solid #404040;
    }

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

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #ea580c;
        color: #ffffff;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #404040;
        color: #ffffff;
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
        <div class="entrepreneurs-dashboard">
            <div class="container">
                <?php if ($verification_status !== 'verified'): ?>
                    <div class="verification-notice">
                        <h3><i class="fas fa-exclamation-triangle"></i> Account Verification Required</h3>
                        <p>Your account needs to be verified to access the following features:</p>
                        <ul>
                            <li>Creating new startups</li>
                            <li>Posting jobs</li>
                            <li>Managing startup profiles</li>
                            <li>Viewing applicant details</li>
                        </ul>
                        <a href="verify_account.php" class="btn btn-warning">Verify Your Account</a>
                    </div>
                <?php endif; ?>
                        <div class="action-buttons-container">
                    <?php if ($verification_status === 'verified'): ?>
                                <div class="action-buttons">
                                    <a href="create_startup.php" class="btn btn-secondary">
                                        <i class="fas fa-plus-circle"></i>
                                        Create New Startup
                                    </a>
                                    <a href="post-job.php" class="btn btn-primary">
                                        <i class="fas fa-briefcase"></i>
                                        Post a Job
                                    </a>
                                </div>
                    <?php endif; ?>
                </div>
                        <!-- Startup Showcase Section -->
                        <div id="section-startups" class="dashboard-section">
                    <h2>Startup Showcase</h2>
                    <div class="search-section">
                        <h3><i class="fas fa-search"></i> Search & Filter Startups</h3>
                        <form id="search-filter-form" method="GET" action="entrepreneurs.php">
                            <input type="hidden" name="section" value="startups">
                            <div class="search-grid">
                                <div class="form-group">
                                    <label for="industry"><i class="fas fa-industry"></i> Industry</label>
                                    <select id="industry" name="industry" class="select2">
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
                                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                                    <select id="location" name="location" class="select2">
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

                                <div class="form-group">
                                    <label for="startup_stage"><i class="fas fa-chart-line"></i> Startup Stage</label>
                                    <select id="startup_stage" name="startup_stage" class="select2">
                                        <option value="">All Stages</option>
                                        <option value="ideation" <?php echo (isset($_GET['startup_stage']) && $_GET['startup_stage'] === 'ideation') ? 'selected' : ''; ?>>Ideation Stage</option>
                                        <option value="validation" <?php echo (isset($_GET['startup_stage']) && $_GET['startup_stage'] === 'validation') ? 'selected' : ''; ?>>Validation Stage</option>
                                        <option value="mvp" <?php echo (isset($_GET['startup_stage']) && $_GET['startup_stage'] === 'mvp') ? 'selected' : ''; ?>>MVP Stage</option>
                                        <option value="growth" <?php echo (isset($_GET['startup_stage']) && $_GET['startup_stage'] === 'growth') ? 'selected' : ''; ?>>Growth Stage</option>
                                        <option value="maturity" <?php echo (isset($_GET['startup_stage']) && $_GET['startup_stage'] === 'maturity') ? 'selected' : ''; ?>>Maturity Stage</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="search-button">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>

                            <?php if (isset($_GET['industry']) || isset($_GET['location']) || isset($_GET['startup_stage'])): ?>
                                <a href="?section=startups" class="clear-filters">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="startups-grid">
                        <?php while ($startup = mysqli_fetch_assoc($startups_result)): ?>
                            <?php $is_entrepreneur_post = $startup['entrepreneur_id'] == $user_id; ?>
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
                                    <?php if ($is_entrepreneur_post && $verification_status === 'verified'): ?>
                                        <a href="edit_startup.php?startup_id=<?php echo $startup['startup_id']; ?>" class="btn btn-warning">Edit</a>
                                        <a href="view_applicants.php?startup_id=<?php echo $startup['startup_id']; ?>" class="btn btn-info">View Applicants</a>
                                    <?php endif; ?>
                                    <a href="startup_detail.php?startup_id=<?php echo $startup['startup_id']; ?>" class="btn btn-info">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Co-Founders Section -->
                <div id="section-cofounders" class="dashboard-section">
                    <h2>Co-Founders</h2>
                    <div class="search-section">
                        <h3><i class="fas fa-search"></i> Search & Filter Co-Founders</h3>
                        <form id="search-filter-form-cofounders" method="GET" action="entrepreneurs.php">
                            <input type="hidden" name="section" value="cofounders">
                            <div class="search-grid">
                                <div class="form-group">
                                    <label for="industry-cofounders"><i class="fas fa-industry"></i> Industry</label>
                                    <select id="industry-cofounders" name="industry" class="select2">
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
                                    <label for="location-cofounders"><i class="fas fa-map-marker-alt"></i> Location</label>
                                    <select id="location-cofounders" name="location" class="select2">
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
                                <a href="?section=cofounders" class="clear-filters">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="startups-grid">
                        <?php
                        // Query for other verified entrepreneurs (Co-Founders)
                        $cofounders_query = "
                            SELECT u.*, e.*
                            FROM Users u
                            JOIN Entrepreneurs e ON u.user_id = e.entrepreneur_id
                            WHERE u.verification_status = 'verified'
                            AND u.user_id != '$user_id'
                            ORDER BY u.name ASC
                            LIMIT 20";
                        $cofounders_result = mysqli_query($conn, $cofounders_query);

                        if (mysqli_num_rows($cofounders_result) > 0):
                            while ($cofounder = mysqli_fetch_assoc($cofounders_result)):
                        ?>
                                <div class="startup-card">
                                    <div class="startup-card-logo">
                                        <?php if (!empty($cofounder['profile_picture_url']) && file_exists($cofounder['profile_picture_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($cofounder['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($cofounder['name']); ?>'s profile picture">
                                        <?php else: ?>
                                            <i class="fas fa-user-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="startup-card-content">
                                        <h3 class="startup-card-title"><?php echo htmlspecialchars($cofounder['name']); ?></h3>
                                        <div class="startup-card-info">
                                            <p><strong>Industry:</strong> <?php echo htmlspecialchars($cofounder['industry'] ?? 'Not specified'); ?></p>
                                            <?php if (!empty($cofounder['bio'])): ?>
                                                <p><strong>Introduction:</strong> <?php echo htmlspecialchars(substr($cofounder['bio'], 0, 100)) . '...'; ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($cofounder['location'])): ?>
                                                <p><strong>Location:</strong> <?php echo htmlspecialchars($cofounder['location']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="startup-card-actions">
                                        <a href="profile.php?user_id=<?php echo $cofounder['user_id']; ?>" class="btn btn-info">View Profile</a>
                                        <a href="messages.php?chat_with=<?php echo $cofounder['user_id']; ?>" class="btn btn-info">Message</a>
                                    </div>
                                </div>
                            <?php 
                            endwhile;
                        else:
                            echo "<p>No potential co-founders found at the moment.</p>";
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
                    </div>
                    <!-- My Applications Tab -->
                    <div id="my-applications" class="tab-content">
                        <?php
                        // ... original job applications listing code ...
                        ?>
                    </div>
                </div>

                <!-- Job Seekers Section -->
                <div id="section-job-seekers" class="dashboard-section">
                    <h2>Relevant Job Seekers</h2>
                    <div class="search-section">
                        <h3><i class="fas fa-search"></i> Search & Filter Job Seekers</h3>
                        <form id="search-filter-form-jobseekers" method="GET" action="entrepreneurs.php">
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
                            WHERE u.verification_status = 'verified'
                            GROUP BY u.user_id
                            ORDER BY u.name ASC
                            LIMIT 20";
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

                <!-- Investors Section -->
                <div id="section-investors" class="dashboard-section">
                    <h2>Relevant Investors</h2>
                    <div class="search-section">
                        <h3><i class="fas fa-search"></i> Search & Filter Investors</h3>
                        <form id="search-filter-form-investors" method="GET" action="entrepreneurs.php">
                            <input type="hidden" name="section" value="investors">
                            <div class="search-grid">
                                <div class="form-group">
                                    <label for="industry-investors"><i class="fas fa-industry"></i> Industry</label>
                                    <select id="industry-investors" name="industry" class="select2">
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
                                    <label for="location-investors"><i class="fas fa-map-marker-alt"></i> Location</label>
                                    <select id="location-investors" name="location" class="select2">
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
                                <a href="?section=investors" class="clear-filters">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <div class="users-grid">
                        <?php
                        $investors_query = "
                            SELECT DISTINCT u.*, i.*, u.profile_picture_url, u.industry
                            FROM Users u
                            JOIN Investors i ON u.user_id = i.investor_id
                            WHERE u.verification_status = 'verified'
                            GROUP BY u.user_id
                            ORDER BY u.name ASC
                            LIMIT 20";
                        $investors_result = mysqli_query($conn, $investors_query);
                        if (mysqli_num_rows($investors_result) > 0):
                            while ($investor = mysqli_fetch_assoc($investors_result)):
                        ?>
                            <div class="user-card">
                                <div class="user-card-avatar">
                                    <?php if (!empty($investor['profile_picture_url']) && file_exists($investor['profile_picture_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($investor['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($investor['name']); ?>'s profile picture">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="user-card-content">
                                    <h3 class="user-card-title"><?php echo htmlspecialchars($investor['name']); ?></h3>
                                    <div class="user-card-info">
                                        <p><strong>Industry:</strong> <?php echo htmlspecialchars($investor['industry'] ?? 'Not specified'); ?></p>
                                        <?php if (!empty($investor['bio'])): ?>
                                            <p><strong>Introduction:</strong> <?php echo htmlspecialchars(substr($investor['bio'], 0, 100)) . '...'; ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($investor['preferred_locations'])): ?>
                                            <p><strong>Preferred Locations:</strong> <?php echo htmlspecialchars($investor['preferred_locations']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-card-actions">
                                    <a href="profile.php?user_id=<?php echo $investor['user_id']; ?>" class="btn btn-info">View Profile</a>
                                    <a href="messages.php?chat_with=<?php echo $investor['user_id']; ?>" class="btn btn-info">Message</a>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else:
                            echo "<p>No investors found.</p>";
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sidebar navigation functionality
document.addEventListener('DOMContentLoaded', function() {
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
</script>

<!-- Success Modal (temporarily disabled) -->
<!--
<div id="successModal" class="success-modal">
    <div class="success-modal-content">
        <h3><i class="fas fa-check-circle"></i> Success!</h3>
        <p id="successMessage"></p>
        <button class="success-modal-btn" onclick="closeSuccessModal()">OK</button>
    </div>
</div>
-->

<script>
// Success modal JS temporarily disabled
// function showSuccessModal(message) {
//     document.getElementById('successMessage').textContent = message;
//     document.getElementById('successModal').style.display = 'block';
// }
// function closeSuccessModal() {
//     document.getElementById('successModal').style.display = 'none';
// }
// window.onclick = function(event) {
//     const modal = document.getElementById('successModal');
//     if (event.target == modal) {
//         modal.style.display = 'none';
//     }
// }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for all filter dropdowns
        $('#industry, #location, #industry-cofounders, #location-cofounders, #industry-jobseekers, #location-jobseekers, #industry-investors, #location-investors, #startup_stage').select2({
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
    });
</script>
</body>
</html>