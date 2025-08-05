<?php
require_once 'db_connection.php';
require_once 'config.php';

class TokenTracker {
    private $conn;
    private $daily_limit;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->daily_limit = DAILY_TOKEN_LIMIT;
        custom_log("TokenTracker initialized with daily limit: " . $this->daily_limit);
    }
    
    public function checkUserTokenLimit($user_id) {
        return true;
    }
    
    public function recordTokenUsage($user_id, $token_count) {
        $today = date('Y-m-d');
        custom_log("Recording token usage for user_id: $user_id, count: $token_count on date: $today");
        
        $max_retries = 3;
        $retry_count = 0;
        
        while ($retry_count < $max_retries) {
            try {
                // Reconnect to database before preparing statement
                custom_log("Reconnecting to database (attempt " . ($retry_count + 1) . ")");
                $this->conn = reconnect_db();
                
                // Explicitly specify the columns, excluding the id column which is auto-increment
                $stmt = $this->conn->prepare("
                    INSERT INTO user_token_usage (user_id, token_count, usage_date) 
                    VALUES (?, ?, ?)
                ");
                
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $this->conn->error);
                }
                
                $stmt->bind_param("iis", $user_id, $token_count, $today);
                $result = $stmt->execute();
                
                if (!$result) {
                    throw new Exception("Failed to execute statement: " . $stmt->error);
                }
                
                custom_log("Successfully recorded token usage");
                return true;
            } catch (Exception $e) {
                $retry_count++;
                custom_log("Error in recordTokenUsage (attempt $retry_count): " . $e->getMessage(), "ERROR");
                custom_log("Stack trace: " . $e->getTraceAsString(), "ERROR");
                
                if ($retry_count >= $max_retries) {
                    custom_log("Failed to record token usage after $max_retries attempts", "ERROR");
                    return false;
                }
                
                // Wait before retrying (exponential backoff)
                $wait_time = pow(2, $retry_count);
                custom_log("Waiting $wait_time seconds before retry");
                sleep($wait_time);
            }
        }
        
        return false;
    }
    
    public function getRemainingTokens($user_id) {
        $today = date('Y-m-d');
        custom_log("Getting remaining tokens for user_id: $user_id on date: $today");
        
        try {
            // Reconnect to database before preparing statement
            custom_log("Reconnecting to database");
            $this->conn = reconnect_db();
            
            $stmt = $this->conn->prepare("
                SELECT COALESCE(SUM(token_count), 0) as total_tokens 
                FROM user_token_usage 
                WHERE user_id = ? AND usage_date = ?
            ");
            
            if (!$stmt) {
                custom_log("Failed to prepare statement: " . $this->conn->error, "ERROR");
                return 0;
            }
            
            $stmt->bind_param("is", $user_id, $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $remaining = $this->daily_limit - $row['total_tokens'];
            custom_log("Remaining tokens: $remaining");
            
            return $remaining;
        } catch (Exception $e) {
            custom_log("Error in getRemainingTokens: " . $e->getMessage(), "ERROR");
            custom_log("Stack trace: " . $e->getTraceAsString(), "ERROR");
            return 0;
        }
    }
} 