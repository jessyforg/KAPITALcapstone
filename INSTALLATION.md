# KAPITAL Installation Guide

This guide provides detailed instructions for setting up the KAPITAL startup ecosystem platform on your local development environment and production server.

## 📋 System Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.4+)
- **Apache**: 2.4 or higher
- **Memory**: 2GB RAM minimum
- **Storage**: 10GB free space
- **Internet**: For AI features and external dependencies

### Recommended Requirements
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Memory**: 4GB RAM
- **Storage**: 20GB free space
- **SSL**: Certificate for production deployment

## 🏠 Local Development Setup

### Step 1: Install XAMPP

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/
   - Download version with PHP 7.4+ for your operating system

2. **Install XAMPP**
   - Run the installer
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Install to default directory (C:\xampp on Windows)

3. **Start Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services
   - Verify by visiting `http://localhost`

### Step 2: Clone the Repository

```bash
# Option 1: Using Git
git clone https://github.com/jessyforg/KAPITALcapstone.git
cd KAPITALcapstone

# Option 2: Download ZIP
# Download from GitHub and extract to C:\xampp\htdocs\KAPITALcapstone
```

### Step 3: Database Setup

#### Method 1: Automatic Setup (Recommended)
1. Navigate to the quick setup page:
   ```
   http://localhost/KAPITALcapstone/public_html%20(1)/quick_setup.php
   ```
2. Follow the on-screen instructions
3. The system will automatically create and configure the database

#### Method 2: Manual Setup
1. **Access phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Create Database**
   ```sql
   CREATE DATABASE kapitalcapstone 
   CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Database Schema**
   - Click on `kapitalcapstone` database
   - Go to "Import" tab
   - Choose file: `public_html (1)/setup_localhost_complete.sql`
   - Click "Go"

4. **Add Test Data (Optional)**
   - Import: `public_html (1)/localhost_post_import_setup.sql`
   - This creates test user accounts for development

### Step 4: Configure OpenAI API (Optional)

The system includes a working OpenAI API key, but for production use, you should configure your own:

1. **Get OpenAI API Key**
   - Visit: https://platform.openai.com/account/api-keys
   - Create a new API key

2. **Configure API Key**
   - Edit `public_html (1)/config.php`
   - Replace the API key:
   ```php
   define('AI_API_KEY', 'your-openai-api-key-here');
   ```

### Step 5: Test Installation

1. **Access the Application**
   ```
   http://localhost/KAPITALcapstone/public_html%20(1)/
   ```

2. **Run System Test**
   ```
   http://localhost/KAPITALcapstone/public_html%20(1)/test_localhost_setup.php
   ```

3. **Test User Accounts** (if you imported test data)
   - Entrepreneur: `entrepreneur@test.com` / `password`
   - Investor: `investor@test.com` / `password`
   - Job Seeker: `jobseeker@test.com` / `password`

## 🚀 Production Deployment

### Step 1: Server Preparation

#### For cPanel/Shared Hosting
1. **Upload Files**
   - Upload all files from `public_html (1)/` to your domain's public_html folder
   - Or upload to a subdirectory for subdomain installation

2. **Database Setup**
   - Create MySQL database in cPanel
   - Import `u882993081_Kapital_System (3).sql`
   - Note database credentials

#### For VPS/Dedicated Server
1. **Install LAMP Stack**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql php-curl php-json php-mbstring
   
   # CentOS/RHEL
   sudo yum install httpd mysql-server php php-mysql php-curl php-json php-mbstring
   ```

2. **Configure Apache**
   - Enable mod_rewrite
   - Set proper permissions
   - Configure virtual host

### Step 2: Database Configuration

1. **Update Database Credentials**
   - Edit `config.php`
   - Update database connection settings:
   ```php
   // For production
   define('DB_HOST', 'your-db-host');
   define('DB_USER', 'your-db-user');
   define('DB_PASS', 'your-db-password');
   define('DB_NAME', 'your-db-name');
   ```

### Step 3: Security Configuration

1. **File Permissions**
   ```bash
   # Set proper permissions
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   chmod 666 logs/  # Make logs directory writable
   ```

2. **Secure Upload Directories**
   - Ensure upload directories are outside document root or properly protected
   - Configure .htaccess files for security

3. **SSL Certificate**
   - Install SSL certificate
   - Redirect HTTP to HTTPS
   - Update any hardcoded URLs

### Step 4: Performance Optimization

1. **PHP Configuration**
   ```ini
   memory_limit = 256M
   upload_max_filesize = 50M
   post_max_size = 50M
   max_execution_time = 300
   ```

2. **MySQL Optimization**
   - Configure proper buffer sizes
   - Enable query cache
   - Regular database maintenance

## 🔧 Configuration Options

### Environment Detection
The system automatically detects the environment:
- **Localhost**: Detects XAMPP/local development
- **Production**: Automatically configures for live server

### Custom Configuration
Edit `config.php` for custom settings:

```php
// AI Configuration
define('AI_MODEL', 'gpt-4');  // or gpt-3.5-turbo
define('AI_MAX_TOKENS', 1000);

// File Upload Settings
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'png']);

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
```

## 🧪 Testing the Installation

### Automated Tests
Run the built-in diagnostic tools:

1. **System Status Check**
   ```
   http://yourdomain.com/test_localhost_setup.php
   ```

2. **Database Connection Test**
   ```
   http://yourdomain.com/test_db_connection.php
   ```

3. **AI Service Test**
   ```
   http://yourdomain.com/startup_ai_advisor.php
   ```

### Manual Testing Checklist

- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] Login functionality works
- [ ] File upload works
- [ ] AI advisor responds
- [ ] Database connections are stable
- [ ] Email notifications work (if configured)

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Errors
```
Error: Could not connect to database
```
**Solution**: Check database credentials in `config.php`

#### File Upload Issues
```
Error: Unable to upload file
```
**Solution**: Check directory permissions and PHP upload settings

#### AI Service Not Working
```
Error: AI service unavailable
```
**Solution**: Verify OpenAI API key and internet connectivity

#### Session Issues
```
Error: Session expired
```
**Solution**: Check PHP session configuration and permissions

### Log Files
Check these log files for debugging:
- `logs/app_YYYY-MM-DD.log` - Application logs
- Apache error logs
- MySQL error logs
- PHP error logs

### Getting Help

1. **Check Documentation**: Review all .md files in the repository
2. **Check Issues**: Look at GitHub Issues for known problems
3. **Create Issue**: Report new bugs on GitHub
4. **Contact Support**: Reach out to the development team

## 📊 Monitoring and Maintenance

### Regular Maintenance
- **Database Backup**: Regular automated backups
- **Log Rotation**: Clean up old log files
- **Security Updates**: Keep PHP, MySQL, and dependencies updated
- **Performance Monitoring**: Monitor server resources and query performance

### Health Checks
Set up monitoring for:
- Database connectivity
- File system permissions
- API service availability
- User authentication systems

---

**Next Steps**: Once installation is complete, refer to the [User Manual](USER_MANUAL.md) to learn how to use the platform. 