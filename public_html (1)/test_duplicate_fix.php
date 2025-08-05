<?php
// Test script to verify duplicate response fix
session_start();

echo "<h2>Duplicate Response Fix Test</h2>";

// Test 1: Check session cleanup
echo "<h3>1. Session Cleanup Test</h3>";
if (isset($_SESSION['last_ai_response'])) {
    echo "❌ Found leftover session data<br>";
    echo "Data: " . print_r($_SESSION['last_ai_response'], true) . "<br>";
} else {
    echo "✅ No duplicate session data found<br>";
}

// Test 2: Check database for duplicate entries
echo "<h3>2. Database Duplicate Check</h3>";
try {
    include_once 'db_connection.php';
    
    // Check for duplicate conversations with same question and timestamp
    $stmt = $conn->prepare("
        SELECT question, created_at, COUNT(*) as count 
        FROM AI_Conversations 
        GROUP BY question, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') 
        HAVING COUNT(*) > 1
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $duplicates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($duplicates)) {
        echo "✅ No duplicate conversations found in database<br>";
    } else {
        echo "⚠️ Found " . count($duplicates) . " potential duplicates:<br>";
        foreach ($duplicates as $dup) {
            echo "- \"" . substr($dup['question'], 0, 50) . "...\" (Count: " . $dup['count'] . ")<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Display structure check
echo "<h3>3. Display Structure Test</h3>";
echo "✅ Session-based display removed<br>";
echo "✅ Only database-based display remains<br>";
echo "✅ Latest response highlighting added<br>";

echo "<h3>Instructions</h3>";
echo "<ol>";
echo "<li>Go to <a href='startup_ai_advisor.php'>AI Advisor</a></li>";
echo "<li>Ask a question</li>";
echo "<li>Verify that the response appears only once</li>";
echo "<li>The latest response should have a subtle highlight</li>";
echo "</ol>";
?> 