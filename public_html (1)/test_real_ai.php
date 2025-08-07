<?php
session_start();

// Set a test user session if not already set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'entrepreneur';
}

include('db_connection.php');
include('ai_service.php');

echo "<h1>Real AI Service Test</h1>";

try {
    // Test AI service initialization
    echo "<h2>AI Service Initialization</h2>";
    $ai_service = new AIService($conn);
    echo "<p style='color: green;'>✅ AI Service initialized successfully</p>";
    
    // Test a simple question
    echo "<h2>OpenAI API Test</h2>";
    echo "<p>Testing with a simple question...</p>";
    
    $test_question = "What is the most important factor for startup success?";
    echo "<p><strong>Question:</strong> " . htmlspecialchars($test_question) . "</p>";
    
    $start_time = microtime(true);
    $response = $ai_service->getAIResponse($_SESSION['user_id'], $test_question);
    $end_time = microtime(true);
    $duration = round($end_time - $start_time, 2);
    
    if ($response && !empty($response)) {
        echo "<p style='color: green;'>✅ OpenAI API response received successfully in {$duration} seconds</p>";
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>AI Response:</strong><br>";
        echo nl2br(htmlspecialchars(substr($response, 0, 500)));
        if (strlen($response) > 500) {
            echo "... <em>(truncated for display)</em>";
        }
        echo "</div>";
        
        // Clean up test record
        $stmt = $conn->prepare("DELETE FROM ai_conversations WHERE user_id = ? AND question = ?");
        $stmt->bind_param("is", $_SESSION['user_id'], $test_question);
        $stmt->execute();
        echo "<p style='color: blue;'>🧹 Test record cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to get response from OpenAI API</p>";
        echo "<p>Check the logs for error details.</p>";
    }
    
    echo "<h2>Summary</h2>";
    if ($response && !empty($response)) {
        echo "<p style='color: green;'><strong>🎉 Success!</strong> Your OpenAI API key is working correctly!</p>";
        echo "<p><a href='startup_ai_advisor.php' style='background: #ea580c; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Go to AI Advisor</a></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ Error:</strong> There's still an issue with the AI service.</p>";
        echo "<p>Check the logs directory for detailed error information.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 