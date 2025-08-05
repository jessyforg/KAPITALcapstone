<?php
// Test script for localhost setup verification
echo "<h1>Kapital System - Localhost Setup Test</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:40px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";

// Test 1: PHP Configuration
echo "<h2>1. PHP Configuration</h2>";
echo "<div class='info'>PHP Version: " . PHP_VERSION . "</div>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "<div class='success'>✓ PHP version is compatible</div>";
} else {
    echo "<div class='error'>✗ PHP version 7.4+ required</div>";
}

// Test 2: Required Extensions
echo "<h2>2. Required PHP Extensions</h2>";
$required_extensions = ['mysqli', 'curl', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✓ {$ext} extension loaded</div>";
    } else {
        echo "<div class='error'>✗ {$ext} extension missing</div>";
    }
}

// Test 3: Configuration Files
echo "<h2>3. Configuration Files</h2>";
if (file_exists(__DIR__ . '/localhost_config.php')) {
    echo "<div class='success'>✓ localhost_config.php found</div>";
} else {
    echo "<div class='error'>✗ localhost_config.php missing</div>";
}

if (file_exists(__DIR__ . '/db_connection.php')) {
    echo "<div class='success'>✓ db_connection.php found</div>";
} else {
    echo "<div class='error'>✗ db_connection.php missing</div>";
}

// Test 4: Database Connection
echo "<h2>4. Database Connection</h2>";
try {
    include_once 'localhost_config.php';
    
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $username = defined('DB_USER') ? DB_USER : 'root';
    $password = defined('DB_PASS') ? DB_PASS : '';
    $dbname = defined('DB_NAME') ? DB_NAME : 'kapitalcapstone';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo "<div class='error'>✗ Database connection failed: " . $conn->connect_error . "</div>";
        echo "<div class='warning'>Make sure XAMPP MySQL is running and database 'kapital_system' exists</div>";
    } else {
        echo "<div class='success'>✓ Database connection successful</div>";
        
        // Test table existence
        $tables = ['Users', 'AI_Conversations', 'Token_Usage'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "<div class='success'>✓ Table '$table' exists</div>";
                
                // Check AUTO_INCREMENT for Users table
                if ($table === 'Users') {
                    $auto_inc_result = $conn->query("SHOW CREATE TABLE Users");
                    if ($auto_inc_result) {
                        $row = $auto_inc_result->fetch_assoc();
                        if (strpos($row['Create Table'], 'AUTO_INCREMENT') !== false) {
                            echo "<div class='success'>✓ Users table has AUTO_INCREMENT</div>";
                        } else {
                            echo "<div class='error'>✗ Users table missing AUTO_INCREMENT - run fix_database_issues.sql</div>";
                        }
                    }
                }
            } else {
                echo "<div class='error'>✗ Table '$table' missing - run setup_localhost.sql</div>";
            }
        }
        
        // Test sample data
        $result = $conn->query("SELECT COUNT(*) as count FROM Users");
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['count'] > 0) {
                echo "<div class='success'>✓ Sample users found ({$row['count']} users)</div>";
            } else {
                echo "<div class='warning'>⚠ No users found - import setup_localhost.sql for test users</div>";
            }
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Database test failed: " . $e->getMessage() . "</div>";
}

// Test 5: Directory Permissions
echo "<h2>5. Directory Permissions</h2>";
$directories = [
    'uploads',
    'uploads/logos',
    'uploads/verification_documents',
    'uploads/profile_pictures',
    'uploads/messages',
    'uploads/resumes',
    'logs'
];

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (file_exists($full_path)) {
        if (is_writable($full_path)) {
            echo "<div class='success'>✓ Directory '$dir' exists and is writable</div>";
        } else {
            echo "<div class='warning'>⚠ Directory '$dir' exists but not writable</div>";
        }
    } else {
        echo "<div class='info'>ℹ Directory '$dir' will be created automatically</div>";
    }
}

// Test 6: AI Configuration
echo "<h2>6. AI Configuration</h2>";
if (defined('AI_API_KEY') && !empty(AI_API_KEY)) {
    echo "<div class='success'>✓ OpenAI API key configured</div>";
    echo "<div class='info'>Model: " . (defined('AI_MODEL') ? AI_MODEL : 'Not set') . "</div>";
    echo "<div class='info'>Daily limit: " . (defined('DAILY_TOKEN_LIMIT') ? DAILY_TOKEN_LIMIT : 'Not set') . " tokens</div>";
} else {
    echo "<div class='error'>✗ OpenAI API key not configured</div>";
}

// Test 7: Vendor Dependencies
echo "<h2>7. Vendor Dependencies</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<div class='success'>✓ Composer vendor directory found</div>";
} else {
    echo "<div class='warning'>⚠ Vendor directory missing - some features may not work</div>";
}

// Test 8: Core Files
echo "<h2>8. Core Application Files</h2>";
$core_files = [
    'startup_ai_advisor.php',
    'ai_service.php',
    'sign_in.php',
    'index.php',
    'navbar.php'
];

foreach ($core_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<div class='success'>✓ {$file} found</div>";
    } else {
        echo "<div class='error'>✗ {$file} missing</div>";
    }
}

// Summary
echo "<h2>Setup Summary</h2>";
echo "<div class='info'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. If database connection failed, start XAMPP MySQL service<br>";
echo "2. If tables are missing, import setup_localhost.sql in phpMyAdmin<br>";
echo "3. Access the application at: <a href='index.php'>http://localhost/KAPITALCapstone/public_html%20(1)/</a><br>";
echo "4. Test login with: entrepreneur@test.com / password<br>";
echo "5. Test AI chatbot in the Startup AI Advisor section<br>";
echo "</div>";

echo "<div class='success'><strong>Ready to start developing!</strong></div>";
?> 