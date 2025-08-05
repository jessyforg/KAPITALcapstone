<?php
class EntrepreneurJobSeekerMatchmaking {
    private $weights = [
        'industry' => 0.6,
        'location' => 0.4
    ];

    public function calculateMatchScore($entrepreneurData, $jobSeekerData) {
        // Calculate industry similarity
        $industrySimilarity = $this->calculateIndustrySimilarity(
            $entrepreneurData['industry'],
            $jobSeekerData['preferred_industries']
        );

        // Calculate location similarity
        $locationSimilarity = $this->calculateLocationSimilarity(
            $entrepreneurData['location'],
            $jobSeekerData['location_preference']
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

    private function calculateIndustrySimilarity($entrepreneurIndustry, $jobSeekerIndustries) {
        if (empty($entrepreneurIndustry) || empty($jobSeekerIndustries)) {
            return 0.0;
        }

        $entrepreneurIndustry = strtolower(trim($entrepreneurIndustry));
        $jobSeekerIndustries = json_decode($jobSeekerIndustries, true);

        if (in_array($entrepreneurIndustry, $jobSeekerIndustries)) {
            return 1.0;
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
            if (in_array($entrepreneurIndustry, $terms)) {
                foreach ($jobSeekerIndustries as $jobSeekerIndustry) {
                    if (in_array($jobSeekerIndustry, $terms)) {
                        return 0.6;
                    }
                }
            }
        }

        return 0.0;
    }

    private function calculateLocationSimilarity($entrepreneurLocation, $jobSeekerLocation) {
        if (empty($entrepreneurLocation) || empty($jobSeekerLocation)) {
            return 0.5;
        }

        $entrepreneurLocation = strtolower(trim($entrepreneurLocation));
        $jobSeekerLocation = strtolower(trim($jobSeekerLocation));

        if ($entrepreneurLocation === $jobSeekerLocation) {
            return 1.0;
        }

        // Check for same region
        $regions = [
            'ncr' => ['manila', 'quezon city', 'makati', 'pasig', 'mandaluyong'],
            'luzon' => ['baguio', 'pampanga', 'laguna', 'cavite', 'batangas'],
            'visayas' => ['cebu', 'iloilo', 'bacolod', 'tacloban'],
            'mindanao' => ['davao', 'cagayan de oro', 'general santos', 'zamboanga']
        ];

        foreach ($regions as $region => $cities) {
            if (in_array($entrepreneurLocation, $cities) && in_array($jobSeekerLocation, $cities)) {
                return 0.6;
            }
        }

        return 0.0;
    }
}

function get_entrepreneur_job_seeker_matches($entrepreneur_id, $conn) {
    // Get entrepreneur's startup data
    $startup_query = "SELECT * FROM Startups WHERE entrepreneur_id = ?";
    $stmt = $conn->prepare($startup_query);
    $stmt->bind_param("i", $entrepreneur_id);
    $stmt->execute();
    $startup_result = $stmt->get_result();
    $startup_data = $startup_result->fetch_assoc();

    // Get all verified job seekers
    $job_seekers_query = "SELECT u.*, js.* 
                         FROM Users u 
                         JOIN job_seekers js ON u.user_id = js.job_seeker_id 
                         WHERE u.verification_status = 'verified'";
    $job_seekers_result = mysqli_query($conn, $job_seekers_query);
    $job_seekers = [];
    while ($job_seeker = mysqli_fetch_assoc($job_seekers_result)) {
        $job_seekers[] = $job_seeker;
    }

    // Initialize matchmaking system
    $matchmaking = new EntrepreneurJobSeekerMatchmaking();
    $matches = [];

    // Calculate matches
    foreach ($job_seekers as $job_seeker) {
        $match_result = $matchmaking->calculateMatchScore($startup_data, $job_seeker);
        
        // Only include matches with score > 0.3 (30%)
        if ($match_result['match_score'] > 0.3) {
            $matches[] = [
                'job_seeker_id' => $job_seeker['job_seeker_id'],
                'name' => $job_seeker['name'],
                'score' => $match_result['match_score'],
                'details' => $match_result['details']
            ];

            // Store match in database using the existing matches table
            $insert_query = "INSERT INTO matches 
                           (startup_id, investor_id, match_score, created_at) 
                           VALUES (?, ?, ?, NOW())
                           ON DUPLICATE KEY UPDATE 
                           match_score = VALUES(match_score),
                           created_at = VALUES(created_at)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iid", $startup_data['startup_id'], $job_seeker['job_seeker_id'], $match_result['match_score']);
            $stmt->execute();
        }
    }

    // Sort matches by score
    usort($matches, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $matches;
}

function get_entrepreneur_investor_matches($entrepreneur_id, $conn) {
    // Get entrepreneur's startup data
    $startup_query = "SELECT * FROM Startups WHERE entrepreneur_id = ?";
    $stmt = $conn->prepare($startup_query);
    $stmt->bind_param("i", $entrepreneur_id);
    $stmt->execute();
    $startup_result = $stmt->get_result();
    $startup_data = $startup_result->fetch_assoc();

    // Get all verified investors
    $investors_query = "SELECT u.*, i.* 
                       FROM Users u 
                       JOIN investors i ON u.user_id = i.investor_id 
                       WHERE u.verification_status = 'verified'";
    $investors_result = mysqli_query($conn, $investors_query);
    $investors = [];
    while ($investor = mysqli_fetch_assoc($investors_result)) {
        $investors[] = $investor;
    }

    // Initialize matchmaking system
    $matchmaking = new EntrepreneurJobSeekerMatchmaking();
    $matches = [];

    // Calculate matches
    foreach ($investors as $investor) {
        $match_result = $matchmaking->calculateMatchScore($startup_data, $investor);
        
        // Only include matches with score > 0.3 (30%)
        if ($match_result['match_score'] > 0.3) {
            $matches[] = [
                'investor_id' => $investor['investor_id'],
                'name' => $investor['name'],
                'score' => $match_result['match_score'],
                'details' => $match_result['details'],
                'investment_range' => $investor['investment_range']
            ];

            // Store match in database using the existing matches table
            $insert_query = "INSERT INTO matches 
                           (startup_id, investor_id, match_score, created_at) 
                           VALUES (?, ?, ?, NOW())
                           ON DUPLICATE KEY UPDATE 
                           match_score = VALUES(match_score),
                           created_at = VALUES(created_at)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iid", $startup_data['startup_id'], $investor['investor_id'], $match_result['match_score']);
            $stmt->execute();
        }
    }

    // Sort matches by score
    usort($matches, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $matches;
}
?> 