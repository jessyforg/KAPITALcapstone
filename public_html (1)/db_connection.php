<?php
// Include localhost configuration if it exists
if (file_exists(__DIR__ . '/localhost_config.php')) {
    require_once __DIR__ . '/localhost_config.php';
}

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

// Database configuration - will be overridden for localhost
$host = defined('DB_HOST') ? DB_HOST : "localhost"; // Replace with Hostinger's database host
$username = defined('DB_USER') ? DB_USER : "u882993081_tarakikapital"; // Replace with your Hostinger database username  
$password = defined('DB_PASS') ? DB_PASS : "Tarakikapital2025"; // Replace with your Hostinger database password
$dbname = defined('DB_NAME') ? DB_NAME : "u882993081_Kapital_System"; // Replace with your Hostinger database name

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

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
                $conn->close();
                $conn = new mysqli($host, $username, $password, $dbname);
                
                // Set connection timeout and other parameters
                $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
                $conn->options(MYSQLI_OPT_READ_TIMEOUT, 30);
                $conn->options(MYSQLI_OPT_WRITE_TIMEOUT, 30);
                
                if ($conn->connect_error) {
                    throw new Exception("Database reconnection failed: " . $conn->connect_error);
                }
                
                return $conn;
            } catch (Exception $e) {
                $retry_count++;
                if ($retry_count >= $max_retries) {
                    error_log("Failed to reconnect to database after $max_retries attempts: " . $e->getMessage());
                    throw $e;
                }
                // Wait before retrying (exponential backoff)
                sleep(pow(2, $retry_count));
            }
        }
    }
}
// Site configuration
if (!defined('SITE_URL')) {
    define('SITE_URL', 'kapital-taraki.org'); // Replace with your actual domain
}
// Define upload directories with absolute paths
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', '/home/u882993081/domains/kapital-taraki.org/public_html/uploads');
}
if (!defined('UPLOAD_LOGOS_DIR')) {
    define('UPLOAD_LOGOS_DIR', UPLOAD_DIR . '/logos');
}
if (!defined('UPLOAD_FILES_DIR')) {
    define('UPLOAD_FILES_DIR', UPLOAD_DIR . '/files');
}

// Create upload directories if they don't exist
if (!function_exists('ensureUploadDirectoriesExist')) {
    function ensureUploadDirectoriesExist() {
        $directories = [UPLOAD_DIR, UPLOAD_LOGOS_DIR, UPLOAD_FILES_DIR];
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    error_log("Failed to create directory: " . $dir);
                    return false;
                }
                chmod($dir, 0755); // Ensure proper permissions
            }
        }
        return true;
    }
}

// Ensure upload directories exist
ensureUploadDirectoriesExist();
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . 'public_html/uploads');
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add industry field to Users table
$alter_query = "ALTER TABLE Users ADD COLUMN IF NOT EXISTS industry VARCHAR(255) DEFAULT NULL";
mysqli_query($conn, $alter_query);
?>