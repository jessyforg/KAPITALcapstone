<?php
session_start();

// Set a test user session if not already set
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'entrepreneur';
}

include('db_connection.php');
include('ai_service.php');

echo "<h1>AI Service Test</h1>";

try {
    // Test database connection
    echo "<h2>Database Connection Test</h2>";
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Database connected successfully</p>";
    }
    
    // Test table structure
    echo "<h2>Table Structure Test</h2>";
    $result = $conn->query("DESCRIBE ai_conversations");
    if ($result) {
        echo "<p style='color: green;'>✅ ai_conversations table exists</p>";
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'conversation_id' && $row['Extra'] === 'auto_increment') {
                echo "<p style='color: green;'>✅ conversation_id has auto_increment</p>";
                break;
            }
        }
    } else {
        echo "<p style='color: red;'>❌ ai_conversations table issue: " . $conn->error . "</p>";
    }
    
    // Test AI service initialization
    echo "<h2>AI Service Test</h2>";
    $ai_service = new AIService($conn);
    echo "<p style='color: green;'>✅ AI Service initialized successfully</p>";
    
    // Test simple insert (without calling OpenAI)
    echo "<h2>Database Insert Test</h2>";
    $test_question = "Test question for database insert";
    $stmt = $conn->prepare("INSERT INTO ai_conversations (user_id, question, response, created_at) VALUES (?, ?, 'Test response', NOW())");
    $stmt->bind_param("is", $_SESSION['user_id'], $test_question);
    
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        echo "<p style='color: green;'>✅ Database insert successful. Insert ID: $insert_id</p>";
        
        // Test fetching the record
        $stmt2 = $conn->prepare("SELECT * FROM ai_conversations WHERE conversation_id = ?");
        $stmt2->bind_param("i", $insert_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        if ($row = $result->fetch_assoc()) {
            echo "<p style='color: green;'>✅ Record fetched successfully: " . htmlspecialchars($row['question']) . "</p>";
        }
        
        // Clean up test record
        $conn->query("DELETE FROM ai_conversations WHERE conversation_id = $insert_id");
        echo "<p style='color: blue;'>🧹 Test record cleaned up</p>";
    } else {
        echo "<p style='color: red;'>❌ Database insert failed: " . $stmt->error . "</p>";
    }
    
    echo "<h2>Summary</h2>";
    echo "<p>If all tests passed, the AI advisor should now work correctly!</p>";
    echo "<p><a href='startup_ai_advisor.php'>Go to AI Advisor</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 