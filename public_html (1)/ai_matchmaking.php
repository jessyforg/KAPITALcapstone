<?php
include('fetch_data.php');

// Fetch investor data and startups
$investor = get_investor_data($_SESSION['user_id']);
$startups = get_startups();

// Prepare data for Python script
$data = [
    'investor' => $investor,
    'startups' => $startups
];

// Call Python script
$command = escapeshellcmd("python3 python_matchmaking.py '" . json_encode($data) . "'");
$output = shell_exec($command);

// Return results to frontend
echo $output;

function get_ai_matches($user_id, $conn) {
    // Get user data
    $user_query = "SELECT u.* 
                  FROM Users u 
                  WHERE u.user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_data = $user_result->fetch_assoc();

    // Get all approved startups
    $startups_query = "SELECT * FROM Startups WHERE approval_status = 'approved'";
    $startups_result = mysqli_query($conn, $startups_query);
    $startups = [];
    while ($startup = mysqli_fetch_assoc($startups_result)) {
        $startups[] = $startup;
    }

    // Prepare data for Python script
    $data = [
        'user' => $user_data,
        'startups' => $startups
    ];

    // Convert data to JSON
    $json_data = json_encode($data);

    // Execute Python script
    $command = "python3 ai_matchmaking.py " . escapeshellarg($json_data);
    $output = shell_exec($command);

    // Parse results
    $matches = json_decode($output, true);

    // Update match scores in database
    foreach ($matches as $match) {
        $update_query = "UPDATE Matches 
                        SET match_score = ? 
                        WHERE investor_id = ? AND startup_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("dii", $match['score'], $user_id, $match['startup_id']);
        $stmt->execute();
    }

    return $matches;
}

function get_matched_startups($user_id, $conn, $limit = 10) {
    // Get AI-matched startups
    $matches = get_ai_matches($user_id, $conn);
    
    // Get startup details for matched startups
    $startup_ids = array_column($matches, 'startup_id');
    if (empty($startup_ids)) {
        return [];
    }

    $placeholders = str_repeat('?,', count($startup_ids) - 1) . '?';
    $query = "SELECT s.*, m.match_score 
              FROM Startups s 
              JOIN Matches m ON s.startup_id = m.startup_id 
              WHERE s.startup_id IN ($placeholders) 
              AND m.investor_id = ? 
              ORDER BY m.match_score DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($startup_ids)) . 'ii';
    $params = array_merge($startup_ids, [$user_id, $limit]);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $matched_startups = [];
    while ($startup = $result->fetch_assoc()) {
        $matched_startups[] = $startup;
    }
    
    return $matched_startups;
}

function get_match_details($user_id, $startup_id, $conn) {
    $query = "SELECT match_score, created_at 
              FROM Matches 
              WHERE investor_id = ? AND startup_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $startup_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}
?>
