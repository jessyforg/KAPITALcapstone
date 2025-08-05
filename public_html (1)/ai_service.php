<?php
require_once 'db_connection.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'token_tracker.php';
require_once 'config.php'; // Explicitly include config.php

class AIService {
    private $conn;
    private $token_tracker;
    private $api_key;
    private $model;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $cache_duration = 3600; // Cache responses for 1 hour
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->token_tracker = new TokenTracker($conn);
        $this->api_key = AI_API_KEY;
        $this->model = AI_MODEL;
        
        custom_log("AIService initialized with model: " . $this->model);
        custom_log("API Key length: " . strlen($this->api_key));
        
        // Validate API key
        if (!$this->isValidAPIKey()) {
            custom_log("Invalid or missing API key detected", "WARNING");
        }
    }
    
    private function isValidAPIKey() {
        // Check if API key is set and has the correct format
        if (empty($this->api_key) || 
            $this->api_key === 'YOUR_OPENAI_API_KEY_HERE' ||
            strlen($this->api_key) < 20 ||
            !preg_match('/^sk-/', $this->api_key)) {
            return false;
        }
        return true;
    }
    
    private function getCachedResponse($question) {
        try {
            $stmt = $this->conn->prepare("
                SELECT response 
                FROM AI_Response_Cache 
                WHERE question_hash = ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            $question_hash = md5(strtolower(trim($question)));
            $stmt->bind_param("si", $question_hash, $this->cache_duration);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                custom_log("Cache hit for question hash: " . $question_hash);
                return $row['response'];
            }
            
            custom_log("Cache miss for question hash: " . $question_hash);
            return null;
        } catch (Exception $e) {
            custom_log("Error checking cache: " . $e->getMessage(), "WARNING");
            return null;
        }
    }

    private function cacheResponse($question, $response) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO AI_Response_Cache (question_hash, question, response, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            $question_hash = md5(strtolower(trim($question)));
            $stmt->bind_param("sss", $question_hash, $question, $response);
            $stmt->execute();
            
            custom_log("Cached response for question hash: " . $question_hash);
        } catch (Exception $e) {
            custom_log("Error caching response: " . $e->getMessage(), "WARNING");
        }
    }
    
    public function getAIResponse($user_id, $question) {
        custom_log("Starting getAIResponse for user_id: $user_id");
        custom_log("Question: " . substr($question, 0, 100) . "...");
        
        // Check if API key is valid first
        if (!$this->isValidAPIKey()) {
            $error_message = "I apologize, but the AI service is not properly configured. ";
            $error_message .= "Please ensure you have a valid OpenAI API key set in your configuration. ";
            $error_message .= "You can get an API key from https://platform.openai.com/api-keys";
            custom_log("API key validation failed", "ERROR");
            return $error_message;
        }
        
        try {
            // Check cache first
            $cached_response = $this->getCachedResponse($question);
            if ($cached_response) {
                custom_log("Using cached response");
                return $cached_response;
            }
            
            // Reconnect to database before preparing statement
            custom_log("Reconnecting to database");
            $this->conn = reconnect_db();
            
            // Store the question in the database
            custom_log("Storing question in database");
            $stmt = $this->conn->prepare("
                INSERT INTO AI_Conversations (user_id, question, response, created_at) 
                VALUES (?, ?, '', NOW())
            ");
            
            if (!$stmt) {
                custom_log("Failed to prepare statement: " . $this->conn->error, "ERROR");
                return "I apologize, but I'm having trouble connecting to the database. Please try again later.";
            }
            
            $stmt->bind_param("is", $user_id, $question);
            if (!$stmt->execute()) {
                custom_log("Failed to execute statement: " . $stmt->error, "ERROR");
                return "I apologize, but I'm having trouble saving your question. Please try again later.";
            }
            
            $conversation_id = $stmt->insert_id;
            custom_log("Stored question with conversation_id: $conversation_id");
            
            // Get AI response from OpenAI with retry logic
            custom_log("Calling OpenAI API");
            $max_retries = 3;
            $retry_count = 0;
            $response = null;
            
            while ($retry_count < $max_retries && !$response) {
                try {
                    $response = $this->callOpenAI($question);
                    if ($response) {
                        custom_log("Successfully received response from OpenAI");
                        // Cache the response
                        $this->cacheResponse($question, $response);
                        break;
                    }
                } catch (Exception $e) {
                    $retry_count++;
                    custom_log("OpenAI API call failed (attempt $retry_count): " . $e->getMessage(), "WARNING");
                    if ($retry_count < $max_retries) {
                        sleep(pow(2, $retry_count)); // Exponential backoff
                    }
                }
            }
            
            if (!$response) {
                throw new Exception("Failed to get response from OpenAI after $max_retries attempts");
            }
            
            // Estimate token usage (rough estimate)
            $token_count = strlen($question . $response) / 4;
            custom_log("Estimated token count: $token_count");
            
            // Record token usage with retry logic
            custom_log("Recording token usage");
            $token_recorded = false;
            $retry_count = 0;
            
            while (!$token_recorded && $retry_count < $max_retries) {
                if ($this->token_tracker->recordTokenUsage($user_id, $token_count)) {
                    $token_recorded = true;
                    custom_log("Successfully recorded token usage");
                } else {
                    $retry_count++;
                    custom_log("Failed to record token usage, attempt $retry_count of $max_retries", "WARNING");
                    if ($retry_count < $max_retries) {
                        sleep(pow(2, $retry_count));
                    }
                }
            }
            
            if (!$token_recorded) {
                custom_log("Failed to record token usage after $max_retries attempts", "ERROR");
            }
            
            // Reconnect to database before updating response
            custom_log("Reconnecting to database for response update");
            $this->conn = reconnect_db();
            
            // Update the response in the database
            custom_log("Updating response in database");
            $stmt = $this->conn->prepare("
                UPDATE AI_Conversations 
                SET response = ?, responded_at = NOW()
                WHERE conversation_id = ?
            ");
            
            if (!$stmt) {
                custom_log("Failed to prepare update statement: " . $this->conn->error, "ERROR");
                return $response;
            }
            
            $stmt->bind_param("si", $response, $conversation_id);
            if (!$stmt->execute()) {
                custom_log("Failed to update response: " . $stmt->error, "ERROR");
            } else {
                custom_log("Successfully updated response in database");
            }
            
            custom_log("Returning response to user");
            return $response;
        } catch (Exception $e) {
            custom_log("Error in getAIResponse: " . $e->getMessage(), "ERROR");
            custom_log("Stack trace: " . $e->getTraceAsString(), "ERROR");
            return "I apologize, but I'm experiencing technical difficulties. Please try again later.";
        }
    }
    
    private function callOpenAI($question) {
        custom_log("Starting callOpenAI");
        try {
            $ch = curl_init();
            
            // Create a detailed system prompt that guides the AI to provide helpful responses
            $system_prompt = "You are a startup advisor and entrepreneurial expert with deep knowledge of business planning, market analysis, funding strategies, and startup growth. 
            
Your role is to provide detailed, practical, and actionable advice to entrepreneurs. When responding to questions:

1. ALWAYS provide a direct answer to the question first, without asking for clarification
2. Include specific, actionable advice with concrete examples
3. Format your responses with clear headings, bullet points, and sections
4. NEVER respond with generic greetings or ask for clarification unless absolutely necessary
5. Focus on providing valuable insights that entrepreneurs can implement immediately
6. If the question is about attracting investors, include specific advice about pitch decks, financial projections, market analysis, and investor relations";

            $data = [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $question]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'stream' => false, // Ensure we're not using streaming
                'presence_penalty' => 0.6, // Encourage more focused responses
                'frequency_penalty' => 0.3, // Reduce repetition
                'top_p' => 0.9 // Focus on most likely tokens
            ];

            custom_log("Preparing OpenAI API request with model: " . $this->model);
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->api_key,
                    'Accept: application/json',
                    'Accept-Encoding: gzip, deflate'
                ],
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_ENCODING => 'gzip, deflate',
                CURLOPT_VERBOSE => true,
                CURLOPT_DNS_CACHE_TIMEOUT => 3600,
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 60,
                CURLOPT_TCP_KEEPINTVL => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_BUFFERSIZE => 16384,
                CURLOPT_TCP_NODELAY => 1, // Disable Nagle's algorithm
                CURLOPT_FORBID_REUSE => false, // Allow connection reuse
                CURLOPT_FRESH_CONNECT => false, // Allow connection reuse
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4 // Force IPv4 for faster resolution
            ]);

            // Enable compression
            if (function_exists('gzencode')) {
                curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
            }

            custom_log("Sending request to OpenAI API");
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            $connect_time = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
            $speed_download = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD);
            
            custom_log("Connection time: " . $connect_time . " seconds");
            custom_log("Total request time: " . $total_time . " seconds");
            custom_log("Download speed: " . $speed_download . " bytes/second");
            
            if ($curl_errno) {
                $error_message = "Curl error ($curl_errno): " . $curl_error;
                custom_log($error_message, "ERROR");
                
                switch ($curl_errno) {
                    case CURLE_COULDNT_CONNECT:
                        throw new Exception("Unable to connect to the AI service. Please check your internet connection and try again.");
                    case CURLE_OPERATION_TIMEDOUT:
                        throw new Exception("The request timed out. Your internet connection might be slow. Please try again.");
                    case CURLE_SSL_CONNECT_ERROR:
                        throw new Exception("There was a problem with the secure connection. Please try again.");
                    default:
                        throw new Exception("Connection error: " . $curl_error);
                }
            }
            
            custom_log("Received response from OpenAI API with HTTP code: " . $http_code);
            custom_log("Response: " . substr($response, 0, 200) . "...");
            
            curl_close($ch);
            
            if ($http_code !== 200) {
                custom_log("OpenAI API error response: " . $response, "ERROR");
                throw new Exception('OpenAI API error: ' . $response);
            }
            
            $response_data = json_decode($response, true);
            if (!isset($response_data['choices'][0]['message']['content'])) {
                custom_log("Invalid response format: " . $response, "ERROR");
                throw new Exception('Invalid response format from OpenAI API');
            }
            
            $content = $response_data['choices'][0]['message']['content'];
            custom_log("Successfully extracted content from response");
            return $content;
        } catch (Exception $e) {
            custom_log("Error in callOpenAI: " . $e->getMessage(), "ERROR");
            custom_log("Stack trace: " . $e->getTraceAsString(), "ERROR");
            throw $e;
        }
    }
    
    public function getPreviousConversations($user_id) {
        // Reconnect to database before fetching conversations
        $this->conn = reconnect_db();
        
        $stmt = $this->conn->prepare("
            SELECT question, response, created_at 
            FROM AI_Conversations 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        if (!$stmt) {
            error_log("Failed to prepare statement in getPreviousConversations: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        
        return $conversations;
    }
} 