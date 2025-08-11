<?php
session_start();
include('db_connection.php');
include('verification_check.php');
require_once 'config.php';
include('navbar.php');
include('user_details_modal.php');

// Redirect if the user is not logged in or does not have the investor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'investor') {
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

// Fetch saved startups for the investor, ensuring the startup is approved
$saved_startups_query = "
    SELECT Startups.* 
    FROM Matches
    JOIN Startups ON Matches.startup_id = Startups.startup_id
    WHERE Matches.investor_id = '$user_id'
    AND Startups.approval_status = 'approved' 
    ORDER BY Matches.created_at DESC";
$saved_startups_result = mysqli_query($conn, $saved_startups_query);

// Build an array of matched startup IDs
$matched_startup_ids = [];
if ($saved_startups_result && mysqli_num_rows($saved_startups_result) > 0) {
    mysqli_data_seek($saved_startups_result, 0); // Reset pointer
    while ($row = mysqli_fetch_assoc($saved_startups_result)) {
        $matched_startup_ids[] = $row['startup_id'];
    }
    // Reset pointer again for later use in HTML
    mysqli_data_seek($saved_startups_result, 0);
}

// Get AI-matched startups (excluding already matched ones)
$matched_startups = get_matched_startups($user_id, $conn);
$matched_startups = array_filter($matched_startups, function($startup) use ($matched_startup_ids) {
    return !in_array($startup['startup_id'], $matched_startup_ids);
});

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
        'Valenzuela',
        'Pateros'
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
        'Passi City',
        'Silay City',
        'Talisay City',
        'Escalante City',
        'Sagay City',
        'Cadiz City',
        'Bago City',
        'La Carlota City',
        'Kabankalan City',
        'San Carlos City',
        'Sipalay City',
        'Himamaylan City'
    ],
    'Central Visayas (Region VII)' => [
        'Cebu City',
        'Mandaue City',
        'Lapu-Lapu City',
        'Talisay City',
        'Toledo City',
        'Dumaguete City',
        'Bais City',
        'Bayawan City',
        'Canlaon City',
        'Guihulngan City',
        'Tanjay City'
    ],
    'Eastern Visayas (Region VIII)' => [
        'Tacloban City',
        'Ormoc City',
        'Calbayog City',
        'Catbalogan City',
        'Maasin City',
        'Baybay City',
        'Borongan City'
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
        'Oroquieta City',
        'Ozamiz City',
        'Tangub City',
        'Gingoog City',
        'El Salvador',
        'Malaybalay City',
        'Valencia City'
    ],
    'Davao Region (Region XI)' => [
        'Davao City',
        'Digos City',
        'Mati City',
        'Panabo City',
        'Samal City',
        'Tagum City'
    ],
    'SOCCSKSARGEN (Region XII)' => [
        'Koronadal City',
        'Cotabato City',
        'General Santos City',
        'Kidapawan City',
        'Tacurong City'
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

// Fetch all startups by default (without filters)
$filter_conditions = "";
if (isset($_GET['industry']) && $_GET['industry'] != "") {
    $industry = mysqli_real_escape_string($conn, $_GET['industry']);
    $filter_conditions .= " AND Startups.industry LIKE '%$industry%'";
}
if (isset($_GET['location']) && $_GET['location'] != "") {
    $location = mysqli_real_escape_string($conn, $_GET['location']);
    $filter_conditions .= " AND Startups.location LIKE '%$location%'";
}
if (isset($_GET['funding_stage']) && $_GET['funding_stage'] != "") {
    $funding_stage = mysqli_real_escape_string($conn, $_GET['funding_stage']);
    $filter_conditions .= " AND Startups.funding_stage = '$funding_stage'";
}

// Query to fetch all startups that match the filters or no filters
// Exclude startups that are already matched with the investor
$startups_query = "
    SELECT * 
    FROM Startups
    WHERE approval_status = 'approved' 
    AND startup_id NOT IN (
        SELECT startup_id 
        FROM Matches 
        WHERE investor_id = '$user_id'
    )
    $filter_conditions
    ORDER BY created_at DESC";
$startups_result = mysqli_query($conn, $startups_query);

// Handle the match action (button click)
if (isset($_POST['match_startup_id'])) {
    $startup_id = mysqli_real_escape_string($conn, $_POST['match_startup_id']);
    
    // Log the match attempt
    error_log("Match attempt: investor_id=$user_id, startup_id=$startup_id");

    // Check if this match already exists
    $check_match_query = "SELECT * FROM Matches WHERE investor_id = '$user_id' AND startup_id = '$startup_id'";
    $check_match_result = mysqli_query($conn, $check_match_query);

    if (mysqli_num_rows($check_match_result) == 0) {
        // Insert the match into the Matches table
        $insert_match_query = "
            INSERT INTO Matches (investor_id, startup_id, created_at) 
            VALUES ('$user_id', '$startup_id', NOW())";
        $insert_result = mysqli_query($conn, $insert_match_query);
        
        // Log the match insertion result
        if ($insert_result) {
            error_log("Match inserted successfully");
        } else {
            error_log("Error inserting match: " . mysqli_error($conn));
        }

        // Get the last inserted match_id
        $match_id = mysqli_insert_id($conn); // Get the match_id from the Matches table
        error_log("Match ID: $match_id");

        // Fetch the entrepreneur's user_id and email for the notification
        $entrepreneur_query = "
            SELECT Users.email, Users.user_id
            FROM Startups
            JOIN Users ON Startups.entrepreneur_id = Users.user_id
            WHERE Startups.startup_id = '$startup_id'";
        $entrepreneur_result = mysqli_query($conn, $entrepreneur_query);
        
        if (mysqli_num_rows($entrepreneur_result) > 0) {
            $entrepreneur = mysqli_fetch_assoc($entrepreneur_result);
            error_log("Entrepreneur found: user_id=" . $entrepreneur['user_id'] . ", email=" . $entrepreneur['email']);
            
            // Insert the notification for the entrepreneur
            $notification_message = "Your startup has been matched with an investor!";
            $notification_url = "match_details.php?match_id=$match_id"; // Use the match_id here
            $insert_notification_query = "
                INSERT INTO Notifications (user_id, sender_id, type, message, url, match_id) 
                VALUES ('" . $entrepreneur['user_id'] . "', '$user_id', 'investment_match', '$notification_message', '$notification_url', '$match_id')";
            $entrepreneur_notification_result = mysqli_query($conn, $insert_notification_query);
            
            // Log the entrepreneur notification result
            if ($entrepreneur_notification_result) {
                error_log("Entrepreneur notification inserted successfully");
            } else {
                error_log("Error inserting entrepreneur notification: " . mysqli_error($conn));
            }
        } else {
            error_log("No entrepreneur found for startup_id: $startup_id");
        }

        // Fetch startup details for the investor notification
        $startup_query = "SELECT name FROM Startups WHERE startup_id = '$startup_id'";
        $startup_result = mysqli_query($conn, $startup_query);
        
        if (mysqli_num_rows($startup_result) > 0) {
            $startup = mysqli_fetch_assoc($startup_result);
            error_log("Startup found: name=" . $startup['name']);
            
            // Insert the notification for the investor
            $notification_message_investor = "You have successfully matched with the startup: " . htmlspecialchars($startup['name']);
            $insert_notification_investor_query = "
                INSERT INTO Notifications (user_id, sender_id, type, message, match_id) 
                VALUES ('$user_id', NULL, 'investment_match', '$notification_message_investor', '$match_id')";
            $investor_notification_result = mysqli_query($conn, $insert_notification_investor_query);
            
            // Log the investor notification result
            if ($investor_notification_result) {
                error_log("Investor notification inserted successfully");
            } else {
                error_log("Error inserting investor notification: " . mysqli_error($conn));
            }
        } else {
            error_log("No startup found for startup_id: $startup_id");
        }
    } else {
        error_log("Match already exists");
    }

    // After the match is processed, redirect to avoid resubmission
    header("Location: investors.php");  // Redirect to the same page
    exit();  // Stop the script to avoid any further execution
}

// Handle unmatch action (delete match)
if (isset($_POST['unmatch_startup_id'])) {
    $startup_id = mysqli_real_escape_string($conn, $_POST['unmatch_startup_id']);

    // Delete the match from the Matches table
    $delete_match_query = "DELETE FROM Matches WHERE investor_id = '$user_id' AND startup_id = '$startup_id'";
    mysqli_query($conn, $delete_match_query);

    // After unmatch is processed, redirect to avoid resubmission
    header("Location: investors.php");  // Redirect to the same page
    exit();  // Stop the script to avoid any further execution
}

// Function to get AI-matched startups for an investor
function get_matched_startups($investor_id, $conn) {
    // Get investor preferences
    $investor_query = "SELECT * FROM Investors WHERE investor_id = ?";
    $stmt = mysqli_prepare($conn, $investor_query);
    mysqli_stmt_bind_param($stmt, "i", $investor_id);
    mysqli_stmt_execute($stmt);
    $investor_result = mysqli_stmt_get_result($stmt);
    $investor = mysqli_fetch_assoc($investor_result);

    if (!$investor) {
        return [];
    }

    // Get all approved startups that haven't been matched with this investor
    $startups_query = "
        SELECT s.*, 
        CASE 
            WHEN s.industry = ? THEN 0.3
            WHEN s.industry IN (SELECT industry FROM Investors WHERE investor_id = ?) THEN 0.2
            ELSE 0.1
        END as industry_score,
        CASE 
            WHEN s.location = ? THEN 0.3
            WHEN s.location IN (SELECT preferred_locations FROM Investors WHERE investor_id = ?) THEN 0.2
            ELSE 0.1
        END as location_score,
        CASE 
            WHEN s.funding_stage = ? THEN 0.4
            ELSE 0.1
        END as funding_score
        FROM Startups s
        WHERE s.approval_status = 'approved'
        AND s.startup_id NOT IN (
            SELECT startup_id 
            FROM Matches 
            WHERE investor_id = ?
        )
        ORDER BY (industry_score + location_score + funding_score) DESC
        LIMIT 10";

    $stmt = mysqli_prepare($conn, $startups_query);
    mysqli_stmt_bind_param($stmt, "sssssi", 
        $investor['industry'],
        $investor_id,
        $investor['preferred_locations'],
        $investor_id,
        $investor['preferred_funding_stage'],
        $investor_id
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $matched_startups = [];
    while ($startup = mysqli_fetch_assoc($result)) {
        // Calculate total match score
        $match_score = ($startup['industry_score'] + $startup['location_score'] + $startup['funding_score']);
        $startup['match_score'] = $match_score;
        $matched_startups[] = $startup;
    }

    return $matched_startups;
}

// Function to get match details between investor and startup
function get_match_details($investor_id, $startup_id, $conn) {
    // Get investor preferences
    $investor_query = "SELECT * FROM Investors WHERE investor_id = ?";
    $stmt = mysqli_prepare($conn, $investor_query);
    mysqli_stmt_bind_param($stmt, "i", $investor_id);
    mysqli_stmt_execute($stmt);
    $investor_result = mysqli_stmt_get_result($stmt);
    $investor = mysqli_fetch_assoc($investor_result);

    // Get startup details
    $startup_query = "SELECT * FROM Startups WHERE startup_id = ?";
    $stmt = mysqli_prepare($conn, $startup_query);
    mysqli_stmt_bind_param($stmt, "i", $startup_id);
    mysqli_stmt_execute($stmt);
    $startup_result = mysqli_stmt_get_result($stmt);
    $startup = mysqli_fetch_assoc($startup_result);

    if (!$investor || !$startup) {
        return ['match_score' => 0];
    }

    // Safely get investor preferences with default values
    $investor_industry = $investor['industry'] ?? '';
    $investor_locations = $investor['preferred_locations'] ?? '';
    $investor_funding_stage = $investor['preferred_funding_stage'] ?? null;

    // Calculate match scores
    $industry_score = ($startup['industry'] === $investor_industry) ? 0.3 : 
                     ((strpos($investor_industry, $startup['industry']) !== false) ? 0.2 : 0.1);

    $location_score = ($startup['location'] === $investor_locations) ? 0.3 :
                     ((strpos($investor_locations, $startup['location']) !== false) ? 0.2 : 0.1);

    $funding_score = ($startup['funding_stage'] === $investor_funding_stage) ? 0.4 : 0.1;

    // Calculate total match score
    $match_score = $industry_score + $location_score + $funding_score;

    return [
        'match_score' => $match_score,
        'industry_score' => $industry_score,
        'location_score' => $location_score,
        'funding_score' => $funding_score
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Dashboard - Kapital</title>
    <link rel="icon" type="image/png" href="imgs/headerlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
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
            display: flex;
            flex-direction: column;
            height: 100%;
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
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .startup-logo i {
            font-size: 40px;
            color: #ea580c;
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
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: auto;
        }

        .startup-post .btn {
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .startup-post .btn-info {
            background-color: #2d2d2d;
            color: #ffffff;
            border: 1px solid #ea580c;
        }

        .startup-post .btn-info:hover {
            background-color: #ea580c;
            transform: translateY(-2px);
        }

        .startup-post .btn-warning {
            background-color: #ea580c;
            color: #ffffff;
            border: none;
        }

        .startup-post .btn-warning:hover {
            background-color: #c2410c;
            transform: translateY(-2px);
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

        .search-section {
            background: #2d2d2d;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #404040;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .search-section h2 {
            color: #ea580c;
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 30px;
        }

        .search-grid .form-group {
            flex: 1 1 140px;
            min-width: 120px;
            max-width: 220px;
            padding: 8px 10px;
            font-size: 0.97rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #23272A;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #404040;
            transition: all 0.3s ease;
        }

        .form-group:hover {
            border-color: #ea580c;
            box-shadow: 0 4px 8px rgba(234, 88, 12, 0.15);
        }

        .form-group label {
            color: #b0b0b0;
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #ea580c;
            font-size: 1.1rem;
        }

        .form-control {
            background: #23272A;
            border: 1px solid #404040;
            color: #ffffff;
            padding: 14px;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        .form-control:focus {
            border-color: #ea580c;
            outline: none;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
            background: #2C2F33;
        }

        .form-control::placeholder {
            color: #72767D;
        }

        .search-button {
            background: #ea580c;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-button:hover {
            background: #c2410c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(234, 88, 12, 0.2);
        }

        .clear-filters {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #b0b0b0;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 6px;
        }

        .clear-filters:hover {
            color: #ffffff;
            background: rgba(234, 88, 12, 0.1);
        }

        .match-score {
            margin-top: 15px;
            padding: 10px;
            background: #23272A;
            border-radius: 8px;
            border: 1px solid #404040;
        }

        .score-label {
            font-weight: 500;
            color: #b0b0b0;
            margin-right: 10px;
        }

        .score-bar {
            display: inline-block;
            width: 100px;
            height: 8px;
            background: #404040;
            border-radius: 4px;
            overflow: hidden;
            margin: 0 10px;
            vertical-align: middle;
        }

        .score-fill {
            height: 100%;
            background: linear-gradient(90deg, #ea580c, #c2410c);
            transition: width 0.3s ease;
        }

        .score-value {
            font-weight: 500;
            color: #ea580c;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
            }

            .startup-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 20px;
            }

            .startup-logo {
                width: 80px;
                height: 80px;
            }

            .startup-actions {
                justify-content: center;
            }

            .search-section {
                padding: 20px;
                margin: 15px;
            }

            .search-grid {
                flex-direction: column;
                gap: 15px;
            }

            .search-grid .form-group {
                max-width: 100%;
            }

            .search-button {
                width: 100%;
                justify-content: center;
                padding: 12px;
            }

            .clear-filters {
                display: flex;
                justify-content: center;
                margin: 15px 0 0 0;
                width: 100%;
                padding: 12px;
                background: rgba(234, 88, 12, 0.05);
            }
        }

        /* Select2 Custom Styles */
        .select2-container--default .select2-selection--single {
            background-color: #23272A;
            border: 1px solid #404040;
            border-radius: 6px;
            color: #ffffff;
            height: 42px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #ffffff;
            line-height: 42px;
            padding-left: 15px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-results__option {
            background-color: #23272A;
            color: #ffffff;
            padding: 10px 15px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ea580c;
            color: #FFFFFF;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #23272A;
            color: #FFFFFF;
            border: 1px solid #404040;
            border-radius: 4px;
            padding: 8px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            outline: none;
            border-color: #ea580c;
        }

        .select2-dropdown {
            background-color: #23272A;
            border: 1px solid #404040;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(234, 88, 12, 0.2);
            color: #ea580c;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #b0b0b0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #ea580c transparent transparent transparent;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #ea580c transparent;
        }

        /* Style for optgroups */
        .select2-results__group {
            background-color: #23272A;
            color: #ea580c;
            font-weight: bold;
            padding: 8px 10px;
        }

        /* Style for options within optgroups */
        .select2-results__option {
            padding-left: 20px;
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
        .main-content {
            flex: 1;
            padding: 40px 0 40px 0;
            max-width: 100vw;
            min-width: 0;
            box-sizing: border-box;
            margin-left: 260px;
            margin-top: 88px;
        }
        @media (max-width: 900px) {
            .sidebar {
                width: 70px;
                padding: 0;
                margin-top: 16px;
            }
            .main-content {
                padding: 10px 0;
                margin-left: 70px;
                margin-top: 88px;
            }
        }
        @media (max-width: 700px) {
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
            .main-content {
                padding: 10px 0;
                margin-left: 0;
                margin-top: 88px;
            }
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
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
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
        @media (max-width: 700px) {
            .user-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 12px;
            }
            .user-avatar {
                width: 60px !important;
                height: 60px !important;
                margin-bottom: 8px;
            }
            .user-info h3 {
                font-size: 1.1rem;
            }
            .user-card {
                padding: 12px;
                margin: 10px 0;
            }
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
            color: #b0b0b0;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .startup-card-info p {
            margin: 8px 0;
            font-size: 0.95rem;
            line-height: 1.5;
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
            background-color: transparent;
            color: #ea580c;
        }

        .startup-card-actions .btn-info:hover {
            background-color: #ea580c;
            color: #ffffff;
            text-decoration: none;
        }

        .startup-card-actions .btn-warning {
            background-color: #ea580c;
            color: #ffffff;
        }

        .startup-card-actions .btn-warning:hover {
            background-color: #c2410c;
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

        .match-score {
            background: #23272A;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            border: 1px solid #404040;
        }

        .score-bar {
            height: 6px;
            background: #404040;
            border-radius: 3px;
            overflow: hidden;
            margin: 5px 0;
        }

        .score-fill {
            height: 100%;
            background: linear-gradient(90deg, #ea580c, #c2410c);
            transition: width 0.3s ease;
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
            color: #b0b0b0;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .user-card-info p {
            margin: 8px 0;
            font-size: 0.95rem;
            line-height: 1.5;
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
            background-color: transparent;
            color: #ea580c;
        }

        .user-card-actions .btn-info:hover {
            background-color: #ea580c;
            color: #ffffff;
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
    </style>
</head>

<body>

<div class="dashboard-wrapper">
    <?php include('sidebar.php'); ?>
    <div class="main-content">
        <div class="container">
            <!-- Startups Section -->
            <div id="section-startups" class="dashboard-section">
                <h1>Welcome, <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>!</h1>

                <?php if ($verification_status !== 'verified'): ?>
                    <div class="verification-notice">
                        <h3><i class="fas fa-exclamation-triangle"></i> Account Verification Required</h3>
                        <p>Your account needs to be verified to access the following features:</p>
                        <ul>
                            <li>Matching with startups</li>
                            <li>Viewing startup details</li>
                            <li>Communicating with entrepreneurs</li>
                            <li>Accessing investment opportunities</li>
                        </ul>
                        <a href="verify_account.php" class="btn btn-warning">Verify Your Account</a>
                    </div>
                <?php endif; ?>

                <div class="search-section">
                    <h2><i class="fas fa-search"></i> Search & Filter Startups</h2>
                    <form id="search-filter-form" method="GET" action="investors.php">
                        <div class="search-grid">
                            <div class="form-group">
                                <label for="industry"><i class="fas fa-industry"></i> Industry</label>
                                <select id="industry" name="industry" class="select2">
                                    <option value="">All Industries</option>
                                    <?php foreach ($industries as $category => $subcategories): ?>
                                        <optgroup label="<?php echo htmlspecialchars($category); ?>">
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?php echo htmlspecialchars($subcategory); ?>" <?php echo isset($_GET['industry']) && $_GET['industry'] == $subcategory ? 'selected' : ''; ?>>
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
                                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo isset($_GET['location']) && $_GET['location'] == $city ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($city); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="funding_stage"><i class="fas fa-chart-line"></i> Funding Stage</label>
                                <select id="funding_stage" name="funding_stage" class="select2">
                                    <option value="">All Stages</option>
                                    <option value="startup" <?php echo isset($_GET['funding_stage']) && $_GET['funding_stage'] == 'startup' ? 'selected' : ''; ?>>Startup Stage</option>
                                    <option value="seed" <?php echo isset($_GET['funding_stage']) && $_GET['funding_stage'] == 'seed' ? 'selected' : ''; ?>>Seed</option>
                                    <option value="series_a" <?php echo isset($_GET['funding_stage']) && $_GET['funding_stage'] == 'series_a' ? 'selected' : ''; ?>>Series A</option>
                                    <option value="series_b" <?php echo isset($_GET['funding_stage']) && $_GET['funding_stage'] == 'series_b' ? 'selected' : ''; ?>>Series B</option>
                                    <option value="series_c" <?php echo isset($_GET['funding_stage']) && $_GET['funding_stage'] == 'series_c' ? 'selected' : ''; ?>>Series C</option>
                                    <option value="exit" <?php echo isset($_GET['funding_stage']) && $_GET['funding_stage'] == 'exit' ? 'selected' : ''; ?>>Exit</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="search-button">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>

                        <?php if (isset($_GET['industry']) || isset($_GET['location']) || isset($_GET['funding_stage'])): ?>
                            <a href="investors.php" class="clear-filters">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <h2>Matched Startups</h2>
                <div class="startups-grid">
                    <?php if (mysqli_num_rows($saved_startups_result) > 0): ?>
                        <?php while ($startup = mysqli_fetch_assoc($saved_startups_result)): ?>
                            <?php
                            $match_details = get_match_details($user_id, $startup['startup_id'], $conn);
                            $match_score = $match_details['match_score'] ?? 0;
                            $match_percentage = round($match_score * 100);
                            ?>
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
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($startup['location']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($startup['description'], 0, 100)) . '...'; ?></p>
                                        <div class="match-score">
                                            <div class="score-label">Match Level</div>
                                            <div class="score-bar">
                                                <div class="score-fill" style="width: <?php echo $match_percentage; ?>%"></div>
                                            </div>
                                            <div class="score-value"><?php echo $match_percentage; ?>%</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="startup-card-actions">
                                    <a href="startup_detail.php?startup_id=<?php echo htmlspecialchars($startup['startup_id']); ?>" class="btn btn-info">View Details</a>
                                    <?php if ($verification_status === 'verified'): ?>
                                        <form method="POST" action="investors.php" style="display:inline;" class="match-form">
                                            <input type="hidden" name="unmatch_startup_id" value="<?php echo htmlspecialchars($startup['startup_id']); ?>">
                                            <button type="submit" class="btn btn-warning">Unmatch</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No saved startups found or no approved startups available.</p>
                    <?php endif; ?>
                </div>

                <h2>Explore Startups</h2>
                <div class="startups-grid">
                    <?php if (mysqli_num_rows($startups_result) > 0): ?>
                        <?php while ($startup = mysqli_fetch_assoc($startups_result)): ?>
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
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($startup['location']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($startup['description'], 0, 100)) . '...'; ?></p>
                                        <?php if (isset($startup['match_score'])): ?>
                                            <div class="match-score">
                                                <div class="score-label">Match Score</div>
                                                <div class="score-bar">
                                                    <div class="score-fill" style="width: <?php echo $startup['match_score'] * 100; ?>%"></div>
                                                </div>
                                                <div class="score-value"><?php echo number_format($startup['match_score'] * 100, 1); ?>%</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="startup-card-actions">
                                    <?php if ($verification_status === 'verified'): ?>
                                        <form method="POST" action="investors.php" style="display:inline;" class="match-form">
                                            <input type="hidden" name="match_startup_id" value="<?php echo htmlspecialchars($startup['startup_id']); ?>">
                                            <button type="submit" class="btn btn-primary">Match</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="startup_detail.php?startup_id=<?php echo htmlspecialchars($startup['startup_id']); ?>" class="btn btn-info">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No approved startups found with the current filter.</p>
                    <?php endif; ?>
                </div>

                <div class="matched-startups">
                    <h3>AI-Matched Startups</h3>
                    <?php
                    if (!empty($matched_startups)) {
                        foreach ($matched_startups as $startup) {
                            $match_details = get_match_details($user_id, $startup['startup_id'], $conn);
                            $match_score = $match_details['match_score'] ?? 0;
                            $match_percentage = round($match_score * 100);
                            ?>
                            <div class="startup-post">
                                <div class="startup-header">
                                    <div class="startup-logo">
                                        <?php if (!empty($startup['logo_url']) && file_exists($startup['logo_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($startup['logo_url']); ?>" alt="<?php echo htmlspecialchars($startup['name']); ?> logo">
                                        <?php else: ?>
                                            <i class="fas fa-building"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="startup-info">
                                        <h3><?php echo htmlspecialchars($startup['name']); ?></h3>
                                        <p><strong>Industry:</strong> <?php echo htmlspecialchars($startup['industry']); ?></p>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($startup['location']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($startup['description'], 0, 150)) . '...'; ?></p>
                                        <div class="match-score">
                                            <span class="score-label">Match Level:</span>
                                            <div class="score-bar">
                                                <div class="score-fill" style="width: <?php echo $match_percentage; ?>%"></div>
                                            </div>
                                            <span class="score-value"><?php echo $match_percentage; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="startup-actions">
                                    <?php if ($verification_status === 'verified'): ?>
                                        <form action="investors.php" method="POST" class="match-form">
                                            <input type="hidden" name="startup_id" value="<?php echo $startup['startup_id']; ?>">
                                            <button type="submit" name="match_action" value="match" class="btn btn-primary">Match</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="startup_detail.php?startup_id=<?php echo $startup['startup_id']; ?>" class="btn btn-info">View Details</a>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p>No matching startups found at the moment.</p>';
                    }
                    ?>
                </div>
            </div>
            <!-- Entrepreneurs Section -->
            <div id="section-entrepreneurs" class="dashboard-section" style="display:none;">
                <h2>Entrepreneurs</h2>
                <div class="search-section">
                    <h3><i class="fas fa-search"></i> Search & Filter Entrepreneurs</h3>
                    <form id="search-filter-form-entrepreneurs" method="GET" action="investors.php">
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
                <div class="startups-grid">
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
                    if (mysqli_num_rows($entrepreneurs_result) > 0):
                        while ($entrepreneur_row = mysqli_fetch_assoc($entrepreneurs_result)):
                    ?>
                        <div class="startup-card">
                            <div class="startup-card-logo">
                                <?php if (!empty($entrepreneur_row['profile_picture_url']) && file_exists($entrepreneur_row['profile_picture_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($entrepreneur_row['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($entrepreneur_row['name']); ?>'s profile picture">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="startup-card-content">
                                <h3 class="startup-card-title"><?php echo htmlspecialchars($entrepreneur_row['name']); ?></h3>
                                <div class="startup-card-info">
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($entrepreneur_row['email']); ?></p>
                                    <?php if (!empty($entrepreneur_row['bio'])): ?>
                                        <p><strong>Bio:</strong> <?php echo htmlspecialchars(substr($entrepreneur_row['bio'], 0, 100)) . '...'; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($entrepreneur_row['location'])): ?>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($entrepreneur_row['location']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="startup-card-actions">
                                <a href="profile.php?user_id=<?php echo $entrepreneur_row['user_id']; ?>" class="btn btn-info">View Profile</a>
                                <a href="messages.php?chat_with=<?php echo $entrepreneur_row['user_id']; ?>" class="btn btn-info">Message</a>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                        echo "<p>No entrepreneurs found.</p>";
                    endif;
                    ?>
                </div>
            </div>
            <!-- Investors Section -->
            <div id="section-investors" class="dashboard-section" style="display:none;">
                <h2>Relevant Investors</h2>
                <div class="search-section">
                    <h3><i class="fas fa-search"></i> Search & Filter Investors</h3>
                    <form id="search-filter-form-investors" method="GET" action="investors.php">
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
                <div class="startups-grid">
                    <?php
                    $investors_query = "
                        SELECT DISTINCT u.*, i.*, u.profile_picture_url, u.industry
                        FROM Users u
                        JOIN Investors i ON u.user_id = i.investor_id
                        WHERE u.verification_status = 'verified'";
                    if (isset($_GET['industry']) && $_GET['industry'] != "") {
                        $industry = mysqli_real_escape_string($conn, $_GET['industry']);
                        $investors_query .= " AND u.industry = '$industry'";
                    }
                    if (isset($_GET['location']) && $_GET['location'] != "") {
                        $location = mysqli_real_escape_string($conn, $_GET['location']);
                        $investors_query .= " AND i.preferred_locations LIKE '%$location%'";
                    }
                    $investors_query .= " GROUP BY u.user_id ORDER BY u.name ASC LIMIT 20";
                    $investors_result = mysqli_query($conn, $investors_query);
                    if (mysqli_num_rows($investors_result) > 0):
                        while ($investor = mysqli_fetch_assoc($investors_result)):
                    ?>
                        <div class="startup-card">
                            <div class="startup-card-logo">
                                <?php if (!empty($investor['profile_picture_url']) && file_exists($investor['profile_picture_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($investor['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($investor['name']); ?>'s profile picture">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="startup-card-content">
                                <h3 class="startup-card-title"><?php echo htmlspecialchars($investor['name']); ?></h3>
                                <div class="startup-card-info">
                                    <p><strong>Industry:</strong> <?php echo htmlspecialchars($investor['industry'] ?? 'Not specified'); ?></p>
                                    <?php if (!empty($investor['bio'])): ?>
                                        <p><strong>Introduction:</strong> <?php echo htmlspecialchars(substr($investor['bio'], 0, 100)) . '...'; ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($investor['preferred_locations'])): ?>
                                        <p><strong>Preferred Locations:</strong> <?php echo htmlspecialchars($investor['preferred_locations']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="startup-card-actions">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 on all filter dropdowns
        $('#industry, #location, #funding_stage, #industry-entrepreneurs, #location-entrepreneurs, #industry-investors, #location-investors').select2({
            theme: 'default',
            width: '100%',
            placeholder: 'Search or select an option',
            allowClear: true,
            minimumInputLength: 1,
            dropdownParent: $('.search-section')
        });
    });

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

    // Update Select2 initialization to match entrepreneurs.php:
    $(document).ready(function() {
        $('#industry, #location, #industry-entrepreneurs, #location-entrepreneurs, #industry-investors, #location-investors, #funding_stage').select2({
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
