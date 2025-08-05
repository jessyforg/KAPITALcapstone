<?php
session_start();
include('navbar.php');
include('db_connection.php');
include('ai_service.php');
include('html_formatter.php');

// Clean up any old session variables that might cause duplicates
if (isset($_SESSION['last_ai_response'])) {
    unset($_SESSION['last_ai_response']);
}

// Ensure user is logged in and is an entrepreneur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'entrepreneur') {
    header("Location: sign_in.php");
    exit("Redirecting to login page...");
}

// Function to ensure database connection is active
function ensure_db_connection() {
    global $conn;
    
    // Check if connection is still alive
    if (!isset($conn) || !$conn || $conn->connect_error) {
        // Connection is dead or not set, create a new one
        if (isset($conn) && $conn) {
            $conn->close();
        }
        
        // Get database credentials from db_connection.php
        $host = defined('DB_HOST') ? DB_HOST : "localhost";
        $username = defined('DB_USER') ? DB_USER : "root";
        $password = defined('DB_PASS') ? DB_PASS : "";
        $dbname = defined('DB_NAME') ? DB_NAME : "kapitalcapstone";
        
        // Create a new connection
        $conn = new mysqli($host, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Database reconnection failed: " . $conn->connect_error);
            return false;
        }
        return true;
    }
    
    // Try a simple query to check if connection is alive
    $result = @$conn->query("SELECT 1");
    if (!$result) {
        // Connection is dead, try to reconnect
        $conn->close();
        
        // Get database credentials from db_connection.php
        $host = defined('DB_HOST') ? DB_HOST : "localhost";
        $username = defined('DB_USER') ? DB_USER : "root";
        $password = defined('DB_PASS') ? DB_PASS : "";
        $dbname = defined('DB_NAME') ? DB_NAME : "kapitalcapstone";
        
        // Create a new connection
        $conn = new mysqli($host, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Database reconnection failed: " . $conn->connect_error);
            return false;
        }
    }
    return true;
}

// Handle AI conversation
if (isset($_POST['question'])) {
    $question = trim($_POST['question']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $ai_service = new AIService($conn);
        $response = $ai_service->getAIResponse($user_id, $question);
        
        // Redirect to prevent form resubmission
        header("Location: startup_ai_advisor.php");
        exit();
    } catch (Exception $e) {
        error_log("Error in AI conversation: " . $e->getMessage());
        $_SESSION['ai_error'] = "I apologize, but I'm experiencing technical difficulties. Please try again later.";
        header("Location: startup_ai_advisor.php");
        exit();
    }
}

// Ensure database connection is active before fetching conversations
ensure_db_connection();

// Fetch previous conversations
$conversations = [];
try {
    $stmt = $conn->prepare("
        SELECT question, response, created_at, responded_at 
        FROM AI_Conversations 
        WHERE user_id = ? 
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching conversations: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Startup AI Advisor - Kapital</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .ai-chat-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(243, 192, 0, 0.2);
        }

        .ai-chat-container * {
            box-sizing: border-box;
        }

        .chat-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .chat-header h1 {
            color: #ea580c;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .chat-header p {
            color: rgba(255, 255, 255, 0.8);
        }

        .question-form {
            margin-bottom: 30px;
        }

        .question-input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(234, 88, 12, 0.3);
            border-radius: 8px;
            color: #fff;
            font-size: 1em;
            margin-bottom: 15px;
            resize: vertical;
            min-height: 100px;
        }

        .question-input:focus {
            outline: none;
            border-color: #ea580c;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2);
        }

        .submit-button {
            background: linear-gradient(45deg, #ea580c, #c44a0a);
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(234, 88, 12, 0.3);
        }

        .conversation-history {
            margin-top: 40px;
        }

        .conversation-item {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border: 1px solid rgba(234, 88, 12, 0.1);
        }

        .question {
            color: #ea580c;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .response {
            color: #fff;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .response h1 {
            color: #ea580c;
            font-size: 2em;
            margin: 20px 0 15px;
            font-weight: 600;
        }

        .response h2 {
            color: #ea580c;
            font-size: 1.5em;
            margin: 15px 0 10px;
            font-weight: 500;
        }

        .response h3 {
            color: #ea580c;
            font-size: 1.2em;
            margin: 12px 0 8px;
            font-weight: 500;
        }

        .response p {
            margin: 10px 0;
        }

        .response ul {
            list-style-type: none;
            padding-left: 20px;
        }

        .response li {
            margin: 5px 0;
        }

        .timestamp {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9em;
        }

        .suggested-topics {
            margin-top: 30px;
            padding: 20px;
            background: rgba(234, 88, 12, 0.1);
            border-radius: 10px;
        }

        .suggested-topics h3 {
            color: #ea580c;
            margin-bottom: 15px;
        }

        .topic-list {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .topic-item {
            background: rgba(234, 88, 12, 0.2);
            color: #ea580c;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .topic-item:hover {
            background: rgba(234, 88, 12, 0.3);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .ai-chat-container {
                margin: 20px;
            }
        }

        .error-message {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }

        .conversation-item.latest-response {
            animation: fadeIn 0.5s ease-in-out;
            border: 1px solid rgba(234, 88, 12, 0.3);
            background: rgba(234, 88, 12, 0.05);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .conversation-item {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border: 1px solid rgba(234, 88, 12, 0.1);
            transition: all 0.3s ease;
        }

        .conversation-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(234, 88, 12, 0.1);
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #ea580c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            color: #fff;
            margin-top: 20px;
            font-size: 1.2em;
            text-align: center;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .question-form {
            position: relative;
        }

        .submit-button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }

        .connection-status {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }

        .connection-status.online {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .connection-status.offline {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Processing your question...</div>
    </div>

    <div class="connection-status">
        Checking connection...
    </div>

    <div class="ai-chat-container">
        <div class="chat-header">
            <h1><i class="fas fa-robot"></i> Startup AI Advisor</h1>
            <p>Ask anything about starting and growing your business</p>
        </div>

        <form method="POST" class="question-form">
            <textarea 
                name="question" 
                class="question-input" 
                placeholder="Ask about business planning, market analysis, funding strategies, or any other startup-related questions..."
                required
            ></textarea>
            <button type="submit" class="submit-button">
                <i class="fas fa-paper-plane"></i> Ask Question
            </button>
        </form>

        <div class="suggested-topics">
            <h3>Suggested Topics</h3>
            <div class="topic-list">
                <div class="topic-item" onclick="setQuestion('How do I create a compelling business plan?')">
                    Business Plan
                </div>
                <div class="topic-item" onclick="setQuestion('What are effective strategies for market research?')">
                    Market Research
                </div>
                <div class="topic-item" onclick="setQuestion('How can I attract potential investors?')">
                    Investment Strategy
                </div>
                <div class="topic-item" onclick="setQuestion('What are the key financial metrics I should track?')">
                    Financial Planning
                </div>
                <div class="topic-item" onclick="setQuestion('How do I identify my target market?')">
                    Target Market
                </div>
            </div>
        </div>

        <div class="conversation-history">
            <?php foreach ($conversations as $index => $conv): ?>
                <div class="conversation-item <?php echo $index === 0 ? 'latest-response' : ''; ?>">
                    <div class="question">
                        <i class="fas fa-question-circle"></i> 
                        <?php echo htmlspecialchars($conv['question']); ?>
                    </div>
                    <div class="response">
                        <i class="fas fa-robot"></i> 
                        <?php echo nl2br(htmlspecialchars($conv['response'] ?? '')); ?>
                    </div>
                    <div class="timestamp">
                        Asked: <?php echo date('M d, Y H:i', strtotime($conv['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['ai_error'])): ?>
            <div class="error-message">
                <?php 
                echo htmlspecialchars($_SESSION['ai_error']);
                unset($_SESSION['ai_error']);
                ?>
            </div>
        <?php endif; ?>


    </div>

    <script>
        function setQuestion(question) {
            document.querySelector('.question-input').value = question;
        }

        // Check internet connection
        function checkConnection() {
            const statusDiv = document.querySelector('.connection-status');
            statusDiv.style.display = 'block';
            
            if (navigator.onLine) {
                statusDiv.className = 'connection-status online';
                statusDiv.textContent = 'Connected to the internet';
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 3000);
            } else {
                statusDiv.className = 'connection-status offline';
                statusDiv.textContent = 'No internet connection. Please check your connection and try again.';
            }
        }

        // Add event listeners for online/offline events
        window.addEventListener('online', checkConnection);
        window.addEventListener('offline', checkConnection);

        // Check connection on page load
        document.addEventListener('DOMContentLoaded', checkConnection);

        // Handle form submission
        document.querySelector('.question-form').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('.submit-button');
            const loadingOverlay = document.querySelector('.loading-overlay');
            
            if (!navigator.onLine) {
                e.preventDefault();
                alert('No internet connection. Please check your connection and try again.');
                return;
            }
            
            submitButton.disabled = true;
            loadingOverlay.style.display = 'flex';
            
            // Set a timeout to re-enable the button if the request takes too long
            setTimeout(() => {
                submitButton.disabled = false;
                loadingOverlay.style.display = 'none';
            }, 60000); // 60 seconds timeout
        });
    </script>
</body>
</html> 