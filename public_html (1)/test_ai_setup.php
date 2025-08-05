<?php
// Test script for AI Advisor setup on localhost
session_start();

echo "<h2>AI Advisor Setup Test</h2>";

// Test 1: Check if config files are properly loaded
echo "<h3>1. Configuration Test</h3>";
try {
    include_once 'config.php';
    include_once 'localhost_config.php';
    echo "✅ Configuration files loaded successfully<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "API Key configured: " . (defined('AI_API_KEY') ? 'Yes' : 'No') . "<br>";
    echo "AI Model: " . AI_MODEL . "<br>";
} catch (Exception $e) {
    echo "❌ Configuration error: " . $e->getMessage() . "<br>";
}

// Test 2: Database connection
echo "<h3>2. Database Connection Test</h3>";
try {
    include_once 'db_connection.php';
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    echo "✅ Database connection successful<br>";
    
    // Check for required tables
    $required_tables = ['Users', 'AI_Conversations', 'AI_Response_Cache', 'user_token_usage'];
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: AI Service initialization
echo "<h3>3. AI Service Test</h3>";
try {
    include_once 'ai_service.php';
    include_once 'token_tracker.php';
    
    $ai_service = new AIService($conn);
    echo "✅ AI Service initialized successfully<br>";
    
    // Check API key validity
    $reflection = new ReflectionClass($ai_service);
    $method = $reflection->getMethod('isValidAPIKey');
    $method->setAccessible(true);
    $is_valid = $method->invoke($ai_service);
    
    if ($is_valid) {
        echo "✅ API Key format is valid<br>";
    } else {
        echo "❌ API Key is invalid or missing<br>";
        echo "Current API Key: " . (defined('AI_API_KEY') ? substr(AI_API_KEY, 0, 10) . '...' : 'Not set') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ AI Service error: " . $e->getMessage() . "<br>";
}

// Test 4: Session and user check
echo "<h3>4. Session Test</h3>";
if (!isset($_SESSION['user_id'])) {
    echo "⚠️ No user session found. Creating test session...<br>";
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'entrepreneur';
    echo "✅ Test session created (user_id: 1)<br>";
} else {
    echo "✅ User session exists (user_id: " . $_SESSION['user_id'] . ")<br>";
}

echo "<h3>Setup Instructions</h3>";
echo "<ol>";
echo "<li><strong>Database Setup:</strong> Run the setup_localhost_complete.sql script in phpMyAdmin</li>";
echo "<li><strong>API Key:</strong> Get your OpenAI API key from <a href='https://platform.openai.com/api-keys' target='_blank'>https://platform.openai.com/api-keys</a></li>";
echo "<li><strong>Update Config:</strong> Replace 'YOUR_OPENAI_API_KEY_HERE' in localhost_config.php with your actual API key</li>";
echo "<li><strong>Test:</strong> Try accessing startup_ai_advisor.php</li>";
echo "</ol>";

echo "<p><a href='startup_ai_advisor.php'>Test AI Advisor →</a></p>";
?> 