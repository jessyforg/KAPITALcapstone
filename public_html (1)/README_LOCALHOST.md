# Kapital System - Localhost Setup Guide

This guide will help you set up the Kapital System on your local XAMPP environment for development and testing purposes.

## Prerequisites

1. **XAMPP** installed on your system
   - Download from: https://www.apachefriends.org/
   - Make sure Apache and MySQL services are running

2. **PHP 7.4 or higher** (included with XAMPP)

3. **OpenAI API Key** (the system includes one, but you may want to use your own for production)

## Installation Steps

### 1. Project Setup

1. Extract or copy the project files to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\KAPITALCapstone\
   ```

2. Navigate to the `public_html (1)` folder - this contains all the application files.

### 2. Database Setup

1. **Start XAMPP Services:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Access phpMyAdmin:**
   - Open your browser and go to: `http://localhost/phpmyadmin`
   - Login with default credentials (usually no password for root)

3. **Import Database:**
   Since you've already imported the production database:
   - Your database should be named `kapitalcapstone`
   - All production tables and data are already available
   - Optionally, import `localhost_post_import_setup.sql` to add test users:
     - Click "Import" in phpMyAdmin
     - Choose the file `localhost_post_import_setup.sql`
     - Click "Go" to add test accounts

### 3. Configuration

The system automatically detects localhost and applies appropriate configurations. No manual configuration changes are needed!

**Automatic Configuration includes:**
- Database: `localhost` / `root` / (no password) / `kapitalcapstone`
- Upload directories are automatically created
- Error reporting enabled for development
- AI/Chatbot functionality preserved

### 4. Test the Installation

1. **Access the Application:**
   - Open your browser and navigate to:
   ```
   http://localhost/KAPITALCapstone/public_html%20(1)/
   ```

2. **Test Login:**
   Use these pre-created test accounts:
   
   **Entrepreneur Account:**
   - Email: `entrepreneur@test.com`
   - Password: `password`
   
   **Investor Account:**
   - Email: `investor@test.com`
   - Password: `password`
   
   **Job Seeker Account:**
   - Email: `jobseeker@test.com`
   - Password: `password`

3. **Test AI Chatbot:**
   - Login as an entrepreneur
   - Navigate to the AI Advisor section
   - Ask a business-related question
   - The chatbot should respond using the OpenAI API

## AI/Chatbot Functionality

✅ **Yes, the chatbot will work on localhost!**

The system includes:
- Pre-configured OpenAI API integration
- Token usage tracking
- Response caching for better performance
- Conversation history

**Features:**
- Business advice and startup guidance
- Market analysis suggestions
- Funding strategy recommendations
- Investor pitch assistance

## File Structure

```
public_html (1)/
├── localhost_config.php          # Localhost-specific configuration
├── db_connection_localhost.php   # Localhost database connection
├── setup_localhost.sql          # Database setup script
├── startup_ai_advisor.php       # AI chatbot interface
├── ai_service.php               # AI service implementation
├── uploads/                     # File upload directory (auto-created)
├── logs/                        # Application logs (auto-created)
└── [other application files]
```

## Common Issues & Solutions

### Database Connection Issues
- **Error:** "Database connection failed"
- **Solution:** Ensure MySQL service is running in XAMPP Control Panel

### File Upload Issues
- **Error:** Permission denied for uploads
- **Solution:** Check that the `uploads` directory has write permissions

### AI Chatbot Not Working
- **Error:** "API key invalid" or "Connection timeout"
- **Solution:** 
  1. Check your internet connection
  2. Verify the OpenAI API key in `localhost_config.php`
  3. Check the logs in the `logs/` directory for detailed error messages

### User Registration Issues
- **Error:** "Column user_id cannot be null" or "AUTO_INCREMENT issues"
- **Solution:** 
  1. Import `fix_database_issues.sql` in phpMyAdmin
  2. This fixes the AUTO_INCREMENT issue in the Users table
  3. Try registering again after the fix

### Page Not Found (404)
- **Error:** Browser shows "Page not found"
- **Solution:** Make sure you're accessing the correct URL with the proper folder name

## Development Features

When running on localhost, the system enables:
- Detailed error reporting and logging
- Debug information in logs
- File upload debugging
- Database query logging

## Security Notes

⚠️ **Important for Production:**
- Change all default passwords
- Use your own OpenAI API key
- Configure proper file permissions
- Enable HTTPS
- Update database credentials
- Review and update security settings

## Directory Permissions

The system automatically creates these directories with proper permissions:
- `uploads/` - File uploads
- `uploads/logos/` - Company logos
- `uploads/verification_documents/` - ID verification files
- `uploads/profile_pictures/` - User profile images
- `uploads/messages/` - Message attachments
- `uploads/resumes/` - Resume files
- `logs/` - Application logs

## Support

If you encounter any issues:

1. Check the logs in the `logs/` directory
2. Verify XAMPP services are running
3. Ensure the database is properly imported
4. Check file permissions on upload directories

## API Configuration

The OpenAI API is pre-configured with:
- Model: `gpt-4-turbo-preview`
- Daily token limit: 1000 tokens per user
- Response caching enabled
- Connection timeout: 60 seconds

You can modify these settings in `localhost_config.php` if needed.

---

**Ready to start developing!** 🚀

The Kapital System is now ready for localhost development with full AI chatbot functionality. 