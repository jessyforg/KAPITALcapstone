<?php
include('db_connection.php');
include('ai_service.php');

echo "<h1>API Key Validation Test</h1>";

try {
    $ai_service = new AIService($conn);
    
    // Use reflection to access the private method for testing
    $reflection = new ReflectionClass($ai_service);
    $method = $reflection->getMethod('isValidAPIKey');
    $method->setAccessible(true);
    
    $is_valid = $method->invoke($ai_service);
    
    echo "<p><strong>API Key:</strong> " . substr(AI_API_KEY, 0, 10) . "..." . substr(AI_API_KEY, -10) . "</p>";
    echo "<p><strong>Key Length:</strong> " . strlen(AI_API_KEY) . "</p>";
    echo "<p><strong>Starts with 'sk-':</strong> " . (preg_match('/^sk-/', AI_API_KEY) ? 'Yes' : 'No') . "</p>";
    
    if ($is_valid) {
        echo "<p style='color: green;'>✅ API Key validation: PASSED</p>";
        echo "<p>The AI service should now work correctly!</p>";
    } else {
        echo "<p style='color: red;'>❌ API Key validation: FAILED</p>";
        echo "<p>There's still an issue with the API key validation.</p>";
    }
    
    echo "<h2>Test Real API Call</h2>";
    echo "<p>Now testing actual OpenAI API call...</p>";
    
    // Test a simple question
    $test_response = $ai_service->getAIResponse(1, "Hello, can you briefly introduce yourself?");
    
    if ($test_response && !strpos($test_response, 'not properly configured')) {
        echo "<p style='color: green;'>✅ API call successful!</p>";
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Response:</strong><br>";
        echo nl2br(htmlspecialchars(substr($test_response, 0, 300)));
        if (strlen($test_response) > 300) {
            echo "...";
        }
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ API call failed or returned error message</p>";
        echo "<p>Response: " . htmlspecialchars($test_response) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='startup_ai_advisor.php'>Go to AI Advisor</a></p>";
?> 