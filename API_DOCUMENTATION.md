# KAPITAL API Documentation

This document provides comprehensive documentation for KAPITAL's internal APIs and external service integrations.

## 📋 Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [User Management APIs](#user-management-apis)
4. [AI Service APIs](#ai-service-apis)
5. [File Management APIs](#file-management-apis)
6. [Messaging APIs](#messaging-apis)
7. [Search and Discovery APIs](#search-and-discovery-apis)
8. [External Service Integrations](#external-service-integrations)
9. [Error Handling](#error-handling)
10. [Rate Limiting](#rate-limiting)

## 🌐 Overview

KAPITAL provides both internal APIs for platform functionality and integrates with external services to deliver enhanced features.

### Base URL
```
Local Development: http://localhost/KAPITALcapstone/public_html%20(1)/
Production: https://yourdomain.com/
```

### API Conventions
- **Request Format**: Form data (`application/x-www-form-urlencoded`) or JSON
- **Response Format**: JSON or HTML (depending on endpoint)
- **Authentication**: Session-based authentication
- **HTTP Methods**: Primarily POST for data modifications, GET for retrieval

## 🔐 Authentication

### Session-Based Authentication

#### Login
```http
POST /signin_process.php
Content-Type: application/x-www-form-urlencoded

email=user@example.com&password=userpassword
```

**Response (Success)**:
```http
HTTP/1.1 302 Found
Location: /dashboard.php
Set-Cookie: PHPSESSID=abc123; Path=/; HttpOnly
```

**Response (Error)**:
```json
{
  "success": false,
  "message": "Invalid email or password"
}
```

#### Logout
```http
POST /logout.php
```

**Response**:
```http
HTTP/1.1 302 Found
Location: /index.php
```

#### Check Authentication Status
```php
// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is authenticated
    $user_id = $_SESSION['user_id'];
} else {
    // Redirect to login
    header('Location: signin.php');
}
```

## 👤 User Management APIs

### User Registration

#### Create New User
```http
POST /signup_process.php
Content-Type: application/x-www-form-urlencoded

name=John+Doe&email=john@example.com&password=securepassword&role=entrepreneur&contact_number=09123456789&location=Baguio+City&industry=Technology
```

**Response (Success)**:
```json
{
  "success": true,
  "message": "Account created successfully",
  "user_id": 123
}
```

**Response (Error)**:
```json
{
  "success": false,
  "message": "Email already exists",
  "errors": ["email_exists"]
}
```

### User Profile Management

#### Get User Profile
```http
GET /profile.php?user_id=123
```

**Response**:
```json
{
  "user_id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "entrepreneur",
  "verification_status": "verified",
  "location": "Baguio City",
  "industry": "Technology",
  "introduction": "Passionate entrepreneur...",
  "accomplishments": "Founded two startups...",
  "profile_picture_url": "/uploads/profile_pictures/user_123.jpg",
  "created_at": "2024-01-15T10:30:00Z"
}
```

#### Update User Profile
```http
POST /update_profile.php
Content-Type: application/x-www-form-urlencoded

introduction=Updated+introduction&accomplishments=New+accomplishments&industry=Fintech
```

**Response**:
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

### User Discovery

#### Search Users
```http
GET /discover_users.php?role=investor&industry=Technology&location=Baguio&page=1&limit=10
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "user_id": 456,
      "name": "Jane Investor",
      "role": "investor",
      "industry": "Technology",
      "location": "Baguio City",
      "profile_picture_url": "/uploads/profile_pictures/user_456.jpg",
      "verification_status": "verified"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_results": 47
  }
}
```

## 🤖 AI Service APIs

### Business Advisor

#### Get AI Business Advice
```http
POST /startup_ai_advisor.php
Content-Type: application/x-www-form-urlencoded

question=How+should+I+price+my+SaaS+product+for+small+businesses+in+the+Philippines?
```

**Response**:
```json
{
  "success": true,
  "response": "For pricing a SaaS product for small businesses in the Philippines, consider these strategies:\n\n## Market-Based Pricing\n1. **Local Market Research**\n   - Survey 50-100 target businesses\n   - Analyze competitor pricing\n   - Consider local purchasing power\n\n## Pricing Models\n1. **Freemium Model**: Free basic features, paid premium\n2. **Tiered Pricing**: Multiple plans based on features/usage\n3. **Per-User Pricing**: Scale with team size\n\n## Philippines-Specific Considerations\n- Price in PHP for local businesses\n- Consider 10-15% lower than US equivalents\n- Offer flexible payment terms\n- Account for VAT (12%) in pricing\n\nStart with a pilot program to validate pricing assumptions.",
  "conversation_id": 789,
  "tokens_used": 245,
  "remaining_tokens": 9755
}
```

**Response (Error - Token Limit)**:
```json
{
  "success": false,
  "message": "Daily token limit exceeded. Please try again tomorrow.",
  "error_code": "TOKEN_LIMIT_EXCEEDED"
}
```

#### Get AI Conversation History
```http
GET /ai_conversations.php?user_id=123&limit=10&offset=0
```

**Response**:
```json
{
  "success": true,
  "conversations": [
    {
      "conversation_id": 789,
      "question": "How should I price my SaaS product?",
      "response": "For pricing a SaaS product...",
      "created_at": "2024-01-15T14:30:00Z",
      "tokens_used": 245
    }
  ],
  "total_conversations": 25
}
```

### Resume Enhancement

#### Enhance Resume Content
```http
POST /ai_resume_helper.php
Content-Type: multipart/form-data

file=@resume.pdf&enhancement_type=content_optimization&target_role=Software+Developer
```

**Response**:
```json
{
  "success": true,
  "enhanced_content": {
    "work_experience": [
      {
        "original": "Managed social media accounts",
        "enhanced": "Managed 5 social media accounts, increasing follower engagement by 45% and brand visibility by 30%"
      }
    ],
    "skills": [
      {
        "original": "Programming",
        "enhanced": "Full-stack development with React, Node.js, and MongoDB"
      }
    ]
  },
  "suggestions": [
    "Add quantifiable achievements to your work experience",
    "Include specific technologies and frameworks",
    "Highlight leadership and project management experience"
  ],
  "tokens_used": 180
}
```

## 📁 File Management APIs

### File Upload

#### Upload Profile Picture
```http
POST /upload_profile_picture.php
Content-Type: multipart/form-data

profile_picture=@image.jpg
```

**Response**:
```json
{
  "success": true,
  "file_url": "/uploads/profile_pictures/user_123_1642248600.jpg",
  "message": "Profile picture uploaded successfully"
}
```

#### Upload Business Document
```http
POST /upload_business_document.php
Content-Type: multipart/form-data

document=@business_plan.pdf&document_type=business_plan
```

**Response**:
```json
{
  "success": true,
  "file_url": "/uploads/files/business_plan_123_1642248600.pdf",
  "document_type": "business_plan",
  "file_size": 2048576,
  "message": "Business plan uploaded successfully"
}
```

#### Upload Resume
```http
POST /upload_resume.php
Content-Type: multipart/form-data

resume=@resume.pdf
```

**Response**:
```json
{
  "success": true,
  "file_url": "/uploads/resumes/resume_123_1642248600.pdf",
  "parsed_content": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+63 912 345 6789",
    "skills": ["PHP", "JavaScript", "MySQL"],
    "experience": "3 years"
  },
  "message": "Resume uploaded and processed successfully"
}
```

### File Management

#### Get User Files
```http
GET /user_files.php?type=business_plan
```

**Response**:
```json
{
  "success": true,
  "files": [
    {
      "file_id": 1,
      "filename": "business_plan_v2.pdf",
      "file_url": "/uploads/files/business_plan_123_1642248600.pdf",
      "file_type": "business_plan",
      "file_size": 2048576,
      "uploaded_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

#### Delete File
```http
POST /delete_file.php
Content-Type: application/x-www-form-urlencoded

file_id=1&file_type=business_plan
```

**Response**:
```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

## 💬 Messaging APIs

### Send Message

#### Send Direct Message
```http
POST /send_message.php
Content-Type: application/x-www-form-urlencoded

recipient_id=456&subject=Investment+Opportunity&message=Hello%2C+I+would+like+to+discuss...&attachment_id=123
```

**Response**:
```json
{
  "success": true,
  "message_id": 789,
  "message": "Message sent successfully"
}
```

### Message Management

#### Get Inbox
```http
GET /messages.php?folder=inbox&page=1&limit=10
```

**Response**:
```json
{
  "success": true,
  "messages": [
    {
      "message_id": 789,
      "sender_id": 456,
      "sender_name": "Jane Investor",
      "sender_profile_picture": "/uploads/profile_pictures/user_456.jpg",
      "subject": "Investment Opportunity",
      "message_preview": "Hello, I would like to discuss...",
      "is_read": false,
      "sent_at": "2024-01-15T16:45:00Z",
      "has_attachment": true
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 3,
    "total_messages": 25
  }
}
```

#### Get Message Details
```http
GET /message_details.php?message_id=789
```

**Response**:
```json
{
  "success": true,
  "message": {
    "message_id": 789,
    "sender_id": 456,
    "sender_name": "Jane Investor",
    "recipient_id": 123,
    "subject": "Investment Opportunity",
    "content": "Hello John,\n\nI reviewed your startup profile and I'm interested in learning more about your business model and funding requirements...",
    "sent_at": "2024-01-15T16:45:00Z",
    "is_read": true,
    "attachments": [
      {
        "attachment_id": 123,
        "filename": "investment_terms.pdf",
        "file_url": "/uploads/messages/investment_terms_789.pdf",
        "file_size": 524288
      }
    ]
  }
}
```

## 🔍 Search and Discovery APIs

### Startup Discovery

#### Search Startups
```http
GET /discover_startups.php?industry=Technology&funding_stage=Seed&location=Baguio&team_size_min=2&team_size_max=10&page=1
```

**Response**:
```json
{
  "success": true,
  "startups": [
    {
      "startup_id": 101,
      "startup_name": "TechStart Solutions",
      "user_id": 123,
      "founder_name": "John Doe",
      "industry": "Technology",
      "funding_stage": "Seed",
      "team_size": 5,
      "location": "Baguio City",
      "description": "AI-powered business automation platform",
      "website_url": "https://techstart.ph",
      "logo_url": "/uploads/logos/startup_101.png",
      "verification_status": "verified"
    }
  ],
  "filters": {
    "industries": ["Technology", "Healthcare", "Finance"],
    "funding_stages": ["Pre-seed", "Seed", "Series A"],
    "locations": ["Baguio City", "Manila", "Cebu"]
  },
  "pagination": {
    "current_page": 1,
    "total_pages": 8,
    "total_results": 76
  }
}
```

### Investor Discovery

#### Search Investors
```http
GET /discover_investors.php?investment_range_min=100000&investment_range_max=1000000&preferred_industry=Technology&page=1
```

**Response**:
```json
{
  "success": true,
  "investors": [
    {
      "investor_id": 201,
      "name": "Jane Investor",
      "user_id": 456,
      "investment_range_min": 500000,
      "investment_range_max": 2000000,
      "preferred_industries": ["Technology", "Healthcare"],
      "preferred_funding_stages": ["Seed", "Series A"],
      "location": "Manila",
      "bio": "Experienced angel investor with 10+ years...",
      "profile_picture_url": "/uploads/profile_pictures/user_456.jpg"
    }
  ]
}
```

### Job Discovery

#### Search Jobs
```http
GET /discover_jobs.php?industry=Technology&experience_level=mid&location=Baguio&remote=true&page=1
```

**Response**:
```json
{
  "success": true,
  "jobs": [
    {
      "job_id": 301,
      "title": "Senior Full Stack Developer",
      "startup_id": 101,
      "startup_name": "TechStart Solutions",
      "description": "We're looking for a senior developer...",
      "requirements": ["5+ years experience", "React", "Node.js"],
      "location": "Baguio City",
      "remote_allowed": true,
      "experience_level": "senior",
      "salary_range": "₱60,000 - ₱100,000",
      "posted_at": "2024-01-14T09:00:00Z"
    }
  ]
}
```

## 🌐 External Service Integrations

### OpenAI API Integration

#### Configuration
```php
// AI Service Configuration
define('AI_API_KEY', 'sk-your-openai-api-key');
define('AI_MODEL', 'gpt-4');
define('AI_MAX_TOKENS', 1000);
define('AI_TEMPERATURE', 0.7);
```

#### API Request Structure
```php
class AIService {
    private function callOpenAI($question) {
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a startup advisor...'
                ],
                [
                    'role' => 'user',
                    'content' => $question
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->api_key
            ]
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
```

### Email Service Integration

#### SMTP Configuration
```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

#### Send Email
```http
POST /send_email.php
Content-Type: application/x-www-form-urlencoded

to=user@example.com&subject=Welcome+to+KAPITAL&template=welcome&user_name=John+Doe
```

**Response**:
```json
{
  "success": true,
  "message": "Email sent successfully",
  "email_id": "msg_123456"
}
```

## ❌ Error Handling

### Standard Error Response Format
```json
{
  "success": false,
  "message": "Human-readable error message",
  "error_code": "SPECIFIC_ERROR_CODE",
  "details": {
    "field": "email",
    "validation_error": "Invalid email format"
  },
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Common Error Codes

#### Authentication Errors
- `AUTH_REQUIRED`: User not authenticated
- `AUTH_INVALID`: Invalid credentials
- `AUTH_EXPIRED`: Session expired
- `PERMISSION_DENIED`: Insufficient permissions

#### Validation Errors
- `VALIDATION_FAILED`: Input validation failed
- `REQUIRED_FIELD`: Required field missing
- `INVALID_FORMAT`: Invalid field format
- `VALUE_TOO_LONG`: Field value exceeds maximum length

#### Resource Errors
- `NOT_FOUND`: Resource not found
- `ALREADY_EXISTS`: Resource already exists
- `CONFLICT`: Resource conflict

#### Service Errors
- `AI_SERVICE_UNAVAILABLE`: AI service temporarily unavailable
- `TOKEN_LIMIT_EXCEEDED`: Daily token limit reached
- `FILE_UPLOAD_FAILED`: File upload error
- `EMAIL_SEND_FAILED`: Email delivery failed

#### System Errors
- `DATABASE_ERROR`: Database operation failed
- `INTERNAL_ERROR`: Unexpected system error
- `SERVICE_UNAVAILABLE`: Service temporarily unavailable

### Error Response Examples

#### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "error_code": "VALIDATION_FAILED",
  "details": {
    "email": "Invalid email format",
    "password": "Password must be at least 8 characters"
  }
}
```

#### Token Limit Error
```json
{
  "success": false,
  "message": "Daily AI token limit exceeded. Please try again tomorrow.",
  "error_code": "TOKEN_LIMIT_EXCEEDED",
  "details": {
    "tokens_used": 10000,
    "daily_limit": 10000,
    "reset_time": "2024-01-16T00:00:00Z"
  }
}
```

## ⚡ Rate Limiting

### Token Usage Limits (AI Services)

#### User Tiers
- **Standard Users**: 5,000 tokens/day
- **Verified Users**: 10,000 tokens/day
- **Premium Users**: 25,000 tokens/day

#### Rate Limit Headers
```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 8500
X-RateLimit-Reset: 1642291200
```

### API Rate Limits

#### General API Limits
- **File Uploads**: 50 MB per file, 10 files per hour
- **Messages**: 100 messages per hour
- **Profile Updates**: 10 updates per hour
- **Search Requests**: 1000 requests per hour

#### Rate Limit Response
```json
{
  "success": false,
  "message": "Rate limit exceeded",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "details": {
    "limit": 100,
    "remaining": 0,
    "reset_time": "2024-01-15T11:00:00Z"
  }
}
```

## 🧪 Testing API Endpoints

### Using cURL

#### Test User Authentication
```bash
curl -X POST \
  http://localhost/KAPITALcapstone/public_html%20(1)/signin_process.php \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'email=test@example.com&password=testpassword' \
  -c cookies.txt
```

#### Test AI Service
```bash
curl -X POST \
  http://localhost/KAPITALcapstone/public_html%20(1)/startup_ai_advisor.php \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -b cookies.txt \
  -d 'question=How do I validate my startup idea?'
```

#### Test File Upload
```bash
curl -X POST \
  http://localhost/KAPITALcapstone/public_html%20(1)/upload_resume.php \
  -b cookies.txt \
  -F 'resume=@/path/to/resume.pdf'
```

### Using JavaScript (Frontend)

#### Make Authenticated Request
```javascript
async function makeAPIRequest(endpoint, data) {
    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data),
            credentials: 'same-origin' // Include session cookies
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API request failed:', error);
        throw error;
    }
}

// Example usage
const result = await makeAPIRequest('/startup_ai_advisor.php', {
    question: 'How do I validate my startup idea?'
});
```

---

## 📞 API Support

- **Documentation Issues**: Report via GitHub Issues
- **API Questions**: Use GitHub Discussions
- **Integration Help**: Check Developer Guide
- **Bug Reports**: Include request/response details

**Build amazing integrations with KAPITAL's APIs!** 