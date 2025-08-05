<?php
// Localhost Configuration for XAMPP
// This file contains localhost-specific settings

// Detect if we're running on localhost
function isLocalhost() {
    $localhost_patterns = [
        '127.0.0.1',
        'localhost',
        '::1',
        '192.168.',
        '10.0.',
        '172.16.',
        '172.17.',
        '172.18.',
        '172.19.',
        '172.20.',
        '172.21.',
        '172.22.',
        '172.23.',
        '172.24.',
        '172.25.',
        '172.26.',
        '172.27.',
        '172.28.',
        '172.29.',
        '172.30.',
        '172.31.'
    ];
    
    $server_ip = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? '127.0.0.1';
    $http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    foreach ($localhost_patterns as $pattern) {
        if (strpos($server_ip, $pattern) === 0 || strpos($http_host, $pattern) === 0) {
            return true;
        }
    }
    
    return false;
}

// If we're on localhost, override the configuration
if (isLocalhost()) {
    // Database configuration for localhost (XAMPP)
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'kapitalcapstone');
    
    // Site configuration for localhost
    if (!defined('SITE_URL')) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        define('SITE_URL', $protocol . '://' . $host . $path);
    }
    
    // Upload directories for localhost
    $base_dir = __DIR__;
    if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', $base_dir . '/uploads');
    if (!defined('UPLOAD_LOGOS_DIR')) define('UPLOAD_LOGOS_DIR', UPLOAD_DIR . '/logos');
    if (!defined('UPLOAD_FILES_DIR')) define('UPLOAD_FILES_DIR', UPLOAD_DIR . '/files');
    if (!defined('UPLOAD_VERIFICATION_DIR')) define('UPLOAD_VERIFICATION_DIR', UPLOAD_DIR . '/verification_documents');
    if (!defined('UPLOAD_PROFILE_DIR')) define('UPLOAD_PROFILE_DIR', UPLOAD_DIR . '/profile_pictures');
    if (!defined('UPLOAD_MESSAGES_DIR')) define('UPLOAD_MESSAGES_DIR', UPLOAD_DIR . '/messages');
    if (!defined('UPLOAD_RESUMES_DIR')) define('UPLOAD_RESUMES_DIR', UPLOAD_DIR . '/resumes');
    
    // AI configuration (using the existing API key)
    // NOTE: The current API key may be expired or invalid
    // Replace with your own OpenAI API key from https://platform.openai.com/api-keys
    if (!defined('AI_API_KEY')) define('AI_API_KEY', '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');
    if (!defined('AI_MODEL')) define('AI_MODEL', 'gpt-3.5-turbo'); // Changed to more affordable model
    if (!defined('AI_PROVIDER')) define('AI_PROVIDER', 'openai');
    if (!defined('DAILY_TOKEN_LIMIT')) define('DAILY_TOKEN_LIMIT', 1000);
    
    // Error reporting for development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
    
    // Create necessary directories if they don't exist
    $directories = [
        UPLOAD_DIR,
        UPLOAD_LOGOS_DIR,
        UPLOAD_FILES_DIR,
        UPLOAD_VERIFICATION_DIR,
        UPLOAD_PROFILE_DIR,
        UPLOAD_MESSAGES_DIR,
        UPLOAD_RESUMES_DIR,
        UPLOAD_RESUMES_DIR . '/temp',
        __DIR__ . '/logs'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            // Create index.php to prevent directory browsing
            file_put_contents($dir . '/index.php', '<?php header("Location: ../index.php"); exit(); ?>');
        }
    }
    
    // Create .htaccess for upload security
    $htaccess_content = "Options -Indexes\n";
    $htaccess_content .= "DirectoryIndex index.php\n";
    $htaccess_content .= "<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)\$\">\n";
    $htaccess_content .= "Order deny,allow\n";
    $htaccess_content .= "Deny from all\n";
    $htaccess_content .= "</FilesMatch>\n";
    
    file_put_contents(UPLOAD_DIR . '/.htaccess', $htaccess_content);
}
?> 