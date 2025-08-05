<?php
// Include localhost configuration first
require_once __DIR__ . '/localhost_config.php';

// Custom logging function
if (!function_exists('custom_log')) {
    function custom_log($message, $type = 'INFO') {
        $log_dir = __DIR__ . '/logs';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/app_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp][$type] $message" . PHP_EOL;
        
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }
}

// Database configuration (will be overridden by localhost_config.php if on localhost)
$host = defined('DB_HOST') ? DB_HOST : "localhost";
$username = defined('DB_USER') ? DB_USER : "root";
$password = defined('DB_PASS') ? DB_PASS : "";
$dbname = defined('DB_NAME') ? DB_NAME : "kapitalcapstone";

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    custom_log("Database connection failed: " . $conn->connect_error, "ERROR");
    die("Database connection failed. Please check your XAMPP MySQL service and database configuration.");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

custom_log("Database connected successfully to: $dbname on $host", "INFO");

// Function to reconnect to the database if needed
if (!function_exists('reconnect_db')) {
    function reconnect_db() {
        global $conn, $host, $username, $password, $dbname;
        
        $max_retries = 3;
        $retry_count = 0;
        
        while ($retry_count < $max_retries) {
            try {
                // Try a simple query to check if connection is alive
                $result = @$conn->query("SELECT 1");
                if ($result) {
                    return $conn;
                }
                
                // Connection is dead, try to reconnect
                if (isset($conn) && $conn) {
                    $conn->close();
                }
                $conn = new mysqli($host, $username, $password, $dbname);
                
                // Set connection timeout and other parameters
                $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
                $conn->options(MYSQLI_OPT_READ_TIMEOUT, 30);
                $conn->options(MYSQLI_OPT_WRITE_TIMEOUT, 30);
                
                if ($conn->connect_error) {
                    throw new Exception("Database reconnection failed: " . $conn->connect_error);
                }
                
                // Set charset
                $conn->set_charset("utf8mb4");
                
                custom_log("Database reconnected successfully", "INFO");
                return $conn;
            } catch (Exception $e) {
                $retry_count++;
                custom_log("Reconnection attempt $retry_count failed: " . $e->getMessage(), "WARNING");
                if ($retry_count >= $max_retries) {
                    custom_log("Failed to reconnect to database after $max_retries attempts", "ERROR");
                    throw $e;
                }
                // Wait before retrying (exponential backoff)
                sleep(pow(2, $retry_count));
            }
        }
        return $conn;
    }
}

// Function to ensure upload directories exist
if (!function_exists('ensureUploadDirectoriesExist')) {
    function ensureUploadDirectoriesExist() {
        $base_upload_dir = defined('UPLOAD_DIR') ? UPLOAD_DIR : __DIR__ . '/uploads';
        $directories = [
            $base_upload_dir,
            $base_upload_dir . '/logos',
            $base_upload_dir . '/files',
            $base_upload_dir . '/verification_documents',
            $base_upload_dir . '/profile_pictures',
            $base_upload_dir . '/messages',
            $base_upload_dir . '/resumes',
            $base_upload_dir . '/resumes/temp'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    custom_log("Failed to create directory: " . $dir, "ERROR");
                    return false;
                }
                // Create index.php to prevent directory browsing
                file_put_contents($dir . '/index.php', '<?php header("Location: ../index.php"); exit(); ?>');
                custom_log("Created directory: " . $dir, "INFO");
            }
        }
        return true;
    }
}

// Ensure upload directories exist
ensureUploadDirectoriesExist();

// Create tables if they don't exist (for fresh localhost installation)
$create_tables_sql = "
-- Create AI_Conversations table if not exists
CREATE TABLE IF NOT EXISTS `AI_Conversations` (
  `conversation_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `response` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`conversation_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create AI_Response_Cache table if not exists
CREATE TABLE IF NOT EXISTS `AI_Response_Cache` (
  `cache_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_hash` varchar(32) NOT NULL,
  `question` text NOT NULL,
  `response` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cache_id`),
  UNIQUE KEY `question_hash` (`question_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create Token_Usage table if not exists
CREATE TABLE IF NOT EXISTS `Token_Usage` (
  `usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tokens_used` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT (CURDATE()),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  UNIQUE KEY `user_date` (`user_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create Users table if not exists (basic structure)
CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('entrepreneur','investor','job_seeker') NOT NULL,
  `verification_status` enum('unverified','pending','verified','rejected') DEFAULT 'unverified',
  `industry` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

// Execute table creation
$queries = explode(';', $create_tables_sql);
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if (!$conn->query($query)) {
            custom_log("Error creating table: " . $conn->error, "WARNING");
        }
    }
}

custom_log("Database initialization completed", "INFO");
?> 