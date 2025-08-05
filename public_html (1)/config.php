<?php
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'kapitalcapstone');

// AI configuration
if (!defined('AI_API_KEY')) define('AI_API_KEY', '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');
if (!defined('AI_MODEL')) define('AI_MODEL', 'gpt-4-turbo-preview');
if (!defined('AI_PROVIDER')) define('AI_PROVIDER', 'openai');
if (!defined('DAILY_TOKEN_LIMIT')) define('DAILY_TOKEN_LIMIT', 1000); // Maximum tokens per user per day

// Application settings
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', 'uploads/');
if (!defined('RESUME_UPLOAD_DIR')) define('RESUME_UPLOAD_DIR', UPLOAD_DIR . 'resumes/');
if (!defined('TEMP_DIR')) define('TEMP_DIR', RESUME_UPLOAD_DIR . 'temp/');

// Create necessary directories if they don't exist
$directories = [UPLOAD_DIR, RESUME_UPLOAD_DIR, TEMP_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); 