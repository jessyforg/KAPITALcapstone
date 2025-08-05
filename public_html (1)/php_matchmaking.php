<?php

class MatchmakingSystem {
    private $weights = [
        'industry' => 0.6,
        'location' => 0.4
    ];

    private $funding_stages = ['startup', 'seed', 'series_a', 'series_b', 'series_c', 'exit'];

    public function calculateIndustrySimilarity($userIndustry, $startupIndustry) {
        // Handle null values
        if ($userIndustry === null || $startupIndustry === null) {
            return 0.0;
        }

        // Convert to lowercase for case-insensitive comparison
        $userIndustry = strtolower(trim((string)$userIndustry));
        $startupIndustry = strtolower(trim((string)$startupIndustry));

        // If either industry is empty after trimming, return 0
        if (empty($userIndustry) || empty($startupIndustry)) {
            return 0.0;
        }

        // Exact match
        if ($userIndustry === $startupIndustry) {
            return 1.0;
        }

        // Check if one industry contains the other
        if (strpos($userIndustry, $startupIndustry) !== false || 
            strpos($startupIndustry, $userIndustry) !== false) {
            return 0.8;
        }

        // Check for similar industries
        $similarIndustries = [
            'tech' => ['technology', 'software', 'it', 'digital'],
            'health' => ['healthcare', 'medical', 'wellness', 'fitness'],
            'finance' => ['financial', 'banking', 'investment', 'fintech'],
            'education' => ['edtech', 'learning', 'training', 'educational'],
            'retail' => ['ecommerce', 'commerce', 'shopping', 'marketplace']
        ];

        foreach ($similarIndustries as $category => $terms) {
            if (in_array($userIndustry, $terms) && in_array($startupIndustry, $terms)) {
                return 0.6;
            }
        }

        return 0.0;
    }

    public function calculateLocationSimilarity($userLocation, $startupLocation) {
        // Handle null values
        if ($userLocation === null || $startupLocation === null) {
            return 0.5; // Neutral score if location is missing
        }

        // Convert to lowercase for case-insensitive comparison
        $userLocation = strtolower(trim((string)$userLocation));
        $startupLocation = strtolower(trim((string)$startupLocation));

        // If either location is empty after trimming, return neutral score
        if (empty($userLocation) || empty($startupLocation)) {
            return 0.5;
        }

        // Exact match
        if ($userLocation === $startupLocation) {
            return 1.0;
        }

        // Check if one location contains the other
        if (strpos($userLocation, $startupLocation) !== false || 
            strpos($startupLocation, $userLocation) !== false) {
            return 0.8;
        }

        // Check for same region
        $regions = [
            'ncr' => ['manila', 'quezon city', 'makati', 'pasig', 'mandaluyong'],
            'luzon' => ['baguio', 'pampanga', 'laguna', 'cavite', 'batangas'],
            'visayas' => ['cebu', 'iloilo', 'bacolod', 'tacloban'],
            'mindanao' => ['davao', 'cagayan de oro', 'general santos', 'zamboanga']
        ];

        foreach ($regions as $region => $cities) {
            if (in_array($userLocation, $cities) && in_array($startupLocation, $cities)) {
                return 0.6;
            }
        }

        return 0.0;
    }

    public function calculateFundingStageSimilarity($userPreference, $startupStage) {
        if (empty($userPreference) || empty($startupStage)) {
            return 0.5; // Neutral score if preference is missing
        }

        $userPreference = strtolower(trim($userPreference));
        $startupStage = strtolower(trim($startupStage));

        try {
            $userIdx = array_search($userPreference, $this->funding_stages);
            $startupIdx = array_search($startupStage, $this->funding_stages);

            if ($userIdx !== false && $startupIdx !== false) {
                // Calculate similarity based on stage proximity
                $maxDistance = count($this->funding_stages) - 1;
                $distance = abs($userIdx - $startupIdx);
                return 1 - ($distance / $maxDistance);
            }
        } catch (Exception $e) {
            // If there's any error in comparison, return neutral score
            return 0.5;
        }

        return 0.5;
    }

    public function calculateMatchScore($userData, $startupData) {
        // Ensure we have valid data
        if (!is_array($userData) || !is_array($startupData)) {
            return [
                'match_score' => 0.0,
                'details' => [
                    'industry_similarity' => 0.0,
                    'location_similarity' => 0.0
                ]
            ];
        }

        // Calculate individual similarity scores
        $industrySimilarity = $this->calculateIndustrySimilarity(
            $userData['industry'] ?? null,
            $startupData['industry'] ?? null
        );

        $locationSimilarity = $this->calculateLocationSimilarity(
            $userData['location'] ?? null,
            $startupData['location'] ?? null
        );

        // Calculate weighted score
        $matchScore = (
            $this->weights['industry'] * $industrySimilarity +
            $this->weights['location'] * $locationSimilarity
        );

        return [
            'match_score' => $matchScore,
            'details' => [
                'industry_similarity' => $industrySimilarity,
                'location_similarity' => $locationSimilarity
            ]
        ];
    }

    public function getMatches($userData, $startups) {
        $matches = [];
        
        foreach ($startups as $startup) {
            $matchResult = $this->calculateMatchScore($userData, $startup);
            $matches[] = [
                'startup_id' => $startup['startup_id'],
                'name' => $startup['name'],
                'score' => $matchResult['match_score'],
                'details' => $matchResult['details']
            ];
        }

        // Sort matches by score
        usort($matches, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $matches;
    }
}

// Function to get AI matches (replaces the Python version)
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

    // Initialize matchmaking system
    $matchmaking = new MatchmakingSystem();
    
    // Get matches
    $matches = $matchmaking->getMatches($user_data, $startups);

    // Update match scores in database
    foreach ($matches as $match) {
        // First check if the match exists
        $check_query = "SELECT * FROM Matches WHERE investor_id = ? AND startup_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $match['startup_id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing match
            $update_query = "UPDATE Matches 
                            SET match_score = ? 
                            WHERE investor_id = ? AND startup_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("dii", $match['score'], $user_id, $match['startup_id']);
            $stmt->execute();
        } else {
            // Insert new match
            $insert_query = "INSERT INTO Matches (investor_id, startup_id, match_score, created_at) 
                            VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iid", $user_id, $match['startup_id'], $match['score']);
            $stmt->execute();
        }
    }

    return $matches;
}

// Function to get matched startups (remains the same)
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

// Function to get match details (remains the same)
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