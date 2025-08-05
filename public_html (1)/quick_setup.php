<?php
// Quick Setup Script for Localhost
// This script automates the initial setup process

echo "<h1>Kapital System - Quick Localhost Setup</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:40px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";

// Include the localhost configuration
include_once 'localhost_config.php';

echo "<h2>Running Quick Setup...</h2>";

// Step 1: Create directories
echo "<h3>Step 1: Creating directories</h3>";
$directories = [
    'uploads',
    'uploads/logos',
    'uploads/files',
    'uploads/verification_documents',
    'uploads/profile_pictures',
    'uploads/messages',
    'uploads/resumes',
    'uploads/resumes/temp',
    'logs'
];

foreach ($directories as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (!file_exists($full_path)) {
        if (mkdir($full_path, 0777, true)) {
            echo "<div class='success'>✓ Created directory: $dir</div>";
            // Create index.php to prevent directory browsing
            file_put_contents($full_path . '/index.php', '<?php header("Location: ../index.php"); exit(); ?>');
        } else {
            echo "<div class='error'>✗ Failed to create directory: $dir</div>";
        }
    } else {
        echo "<div class='info'>Directory already exists: $dir</div>";
    }
}

// Step 2: Create .htaccess for security
echo "<h3>Step 2: Setting up security</h3>";
$htaccess_content = "Options -Indexes\n";
$htaccess_content .= "DirectoryIndex index.php\n";
$htaccess_content .= "<FilesMatch \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)\$\">\n";
$htaccess_content .= "Order deny,allow\n";
$htaccess_content .= "Deny from all\n";
$htaccess_content .= "</FilesMatch>\n";

if (file_put_contents(__DIR__ . '/uploads/.htaccess', $htaccess_content)) {
    echo "<div class='success'>✓ Created upload security (.htaccess)</div>";
} else {
    echo "<div class='warning'>⚠ Could not create .htaccess file</div>";
}

// Step 3: Test database connection
echo "<h3>Step 3: Testing database connection</h3>";
try {
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $username = defined('DB_USER') ? DB_USER : 'root';
    $password = defined('DB_PASS') ? DB_PASS : '';
    $dbname = defined('DB_NAME') ? DB_NAME : 'kapitalcapstone';
    
    // Test connection without database first
    $conn = new mysqli($host, $username, $password);
    if ($conn->connect_error) {
        echo "<div class='error'>✗ Cannot connect to MySQL server</div>";
        echo "<div class='warning'>Make sure XAMPP MySQL service is running</div>";
    } else {
        echo "<div class='success'>✓ MySQL server connection successful</div>";
        
        // Check if database exists
        $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        if ($db_check && $db_check->num_rows > 0) {
            echo "<div class='success'>✓ Database '$dbname' exists</div>";
            
            // Check for production tables
            $conn->select_db($dbname);
            $table_check = $conn->query("SHOW TABLES LIKE 'Users'");
            if ($table_check && $table_check->num_rows > 0) {
                echo "<div class='success'>✓ Production database structure detected</div>";
                
                // Check for test users
                $user_check = $conn->query("SELECT COUNT(*) as count FROM Users WHERE email IN ('entrepreneur@test.com', 'investor@test.com', 'jobseeker@test.com')");
                if ($user_check) {
                    $user_row = $user_check->fetch_assoc();
                    if ($user_row['count'] > 0) {
                        echo "<div class='success'>✓ Test users found ({$user_row['count']}/3)</div>";
                    } else {
                        echo "<div class='info'>ℹ No test users found - import 'localhost_post_import_setup.sql' for test accounts</div>";
                    }
                }
            } else {
                echo "<div class='warning'>⚠ Database exists but no tables found</div>";
            }
        } else {
            echo "<div class='error'>✗ Database '$dbname' does not exist</div>";
            echo "<div class='info'>Please create the database and import your SQL file</div>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Database test failed: " . $e->getMessage() . "</div>";
}

// Step 4: Test AI configuration
echo "<h3>Step 4: Testing AI configuration</h3>";
if (defined('AI_API_KEY') && !empty(AI_API_KEY)) {
    echo "<div class='success'>✓ OpenAI API key configured</div>";
    echo "<div class='info'>Model: " . (defined('AI_MODEL') ? AI_MODEL : 'Default') . "</div>";
} else {
    echo "<div class='error'>✗ OpenAI API key not configured</div>";
}

// Step 5: Test file permissions
echo "<h3>Step 5: Testing file permissions</h3>";
$test_file = __DIR__ . '/uploads/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "<div class='success'>✓ Upload directory is writable</div>";
    unlink($test_file); // Clean up
} else {
    echo "<div class='error'>✗ Upload directory is not writable</div>";
}

// Summary and next steps
echo "<h2>Setup Complete!</h2>";
echo "<div class='info'>";
echo "<strong>What's been set up:</strong><br>";
echo "• Directory structure created<br>";
echo "• Security files configured<br>";
echo "• Configuration tested<br>";
echo "<br>";
echo "<strong>Next steps:</strong><br>";
echo "1. Import <code>localhost_post_import_setup.sql</code> for test users (optional)<br>";
echo "2. Visit <a href='test_localhost_setup.php'>test_localhost_setup.php</a> to verify everything<br>";
echo "3. Access the application at <a href='index.php'>index.php</a><br>";
echo "4. Login with: entrepreneur@test.com / password (if test users imported)<br>";
echo "</div>";

echo "<hr>";
echo "<div class='success'><strong>Quick Setup Completed!</strong></div>";
echo "<div class='info'>The Kapital System is ready for localhost development with full AI chatbot functionality.</div>";
?> 