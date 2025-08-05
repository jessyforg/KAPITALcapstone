# AI Advisor Setup Guide for Localhost

## Quick Setup Steps

### 1. Database Setup
1. Open phpMyAdmin in XAMPP (`http://localhost/phpmyadmin`)
2. Import the SQL file: `setup_localhost_complete.sql`
3. This will create the `kapitalcapstone` database with all required tables

### 2. Get OpenAI API Key
1. Visit [OpenAI API Keys](https://platform.openai.com/api-keys)
2. Create an account if you don't have one
3. Generate a new API key
4. Copy the key (starts with `sk-`)

### 3. Configure API Key
1. Open `localhost_config.php`
2. Replace `YOUR_OPENAI_API_KEY_HERE` with your actual API key
3. Save the file

### 4. Test Setup
1. Run the test script: `http://localhost/your-project-path/test_ai_setup.php`
2. Check that all tests pass
3. Fix any issues reported by the test

### 5. Use AI Advisor
1. Access: `http://localhost/your-project-path/startup_ai_advisor.php`
2. The system will create a test user session automatically
3. Ask questions and get AI responses!

## Troubleshooting

### Error: "Technical difficulties"
- Check that your API key is correctly set
- Verify database tables exist
- Check the log files in the `logs/` directory

### Database Connection Issues
- Ensure XAMPP MySQL is running
- Check database name is `kapitalcapstone`
- Verify user credentials (root with no password by default)

### API Key Issues
- Make sure the key starts with `sk-`
- Check you have credits in your OpenAI account
- Verify the key hasn't expired

## Files Modified/Created
- `setup_localhost_complete.sql` - Database setup script
- `localhost_config.php` - Updated with API key placeholder
- `ai_service.php` - Added API key validation
- `test_ai_setup.php` - Setup verification script

## Database Tables Created
- `Users` - User accounts
- `AI_Conversations` - Chat history
- `AI_Response_Cache` - Response caching
- `user_token_usage` - Token usage tracking 