-- Post-Import Setup for Localhost Development
-- Run this after importing u882993081_Kapital_System (3).sql
-- This adds test users and ensures localhost compatibility

USE `kapitalcapstone`;

-- Add test users if they don't exist (for localhost testing)
-- Password is 'password' hashed with password_hash() using PASSWORD_DEFAULT

INSERT IGNORE INTO `Users` (
    `name`, 
    `email`, 
    `password`, 
    `role`, 
    `verification_status`,
    `location`,
    `industry`,
    `contact_number`,
    `introduction`,
    `created_at`
) VALUES
('Test Entrepreneur', 'entrepreneur@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'entrepreneur', 'verified', 'Philippines', 'Technology', '+63-900-000-0001', 'Test entrepreneur account for localhost development', NOW()),
('Test Investor', 'investor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'investor', 'verified', 'Philippines', 'Investment', '+63-900-000-0002', 'Test investor account for localhost development', NOW()),
('Test Job Seeker', 'jobseeker@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker', 'verified', 'Philippines', 'Technology', '+63-900-000-0003', 'Test job seeker account for localhost development', NOW());

-- Create Entrepreneurs record for the test entrepreneur
INSERT IGNORE INTO `Entrepreneurs` (`entrepreneur_id`, `created_at`)
SELECT `user_id`, NOW() FROM `Users` WHERE `email` = 'entrepreneur@test.com';

-- Create Investors record for the test investor
INSERT IGNORE INTO `Investors` (
    `investor_id`, 
    `investment_range_min`, 
    `investment_range_max`,
    `preferred_industries`,
    `preferred_locations`,
    `funding_stage_preferences`,
    `bio`,
    `created_at`
)
SELECT 
    `user_id`, 
    50000.00, 
    1000000.00,
    '["Technology", "Healthcare", "Fintech"]',
    '["Philippines", "Southeast Asia"]',
    '["seed", "series_a"]',
    'Experienced investor focused on early-stage startups in Southeast Asia',
    NOW() 
FROM `Users` WHERE `email` = 'investor@test.com';

-- Create job_seekers record for the test job seeker
INSERT IGNORE INTO `job_seekers` (
    `job_seeker_id`,
    `skills`,
    `preferred_industries`,
    `desired_role`,
    `experience_level`,
    `location_preference`,
    `bio`,
    `created_at`
)
SELECT 
    `user_id`,
    '["PHP", "JavaScript", "MySQL", "React", "Node.js"]',
    '["Technology", "Startups"]',
    'Full Stack Developer',
    'mid',
    'Philippines',
    'Experienced full-stack developer passionate about startup environments',
    NOW()
FROM `Users` WHERE `email` = 'jobseeker@test.com';

-- Add a test startup for the entrepreneur
INSERT IGNORE INTO `Startups` (
    `entrepreneur_id`,
    `name`,
    `industry`,
    `description`,
    `location`,
    `funding_needed`,
    `approval_status`,
    `funding_stage`,
    `startup_stage`,
    `website`,
    `created_at`
)
SELECT 
    `user_id`,
    'TestTech Innovations',
    'Technology',
    'A revolutionary AI-powered platform for startup development and testing',
    'Manila, Philippines',
    500000.00,
    'approved',
    'seed',
    'mvp',
    'https://testtech.example.com',
    NOW()
FROM `Users` WHERE `email` = 'entrepreneur@test.com';

-- Add a test job posting
INSERT IGNORE INTO `Jobs` (
    `startup_id`,
    `role`,
    `description`,
    `requirements`,
    `location`,
    `salary_range_min`,
    `salary_range_max`,
    `status`,
    `created_at`
)
SELECT 
    s.`startup_id`,
    'Senior Full Stack Developer',
    'We are looking for an experienced full-stack developer to join our innovative team',
    'PHP, JavaScript, MySQL, React, 3+ years experience',
    'Manila, Philippines',
    60000.00,
    100000.00,
    'active',
    NOW()
FROM `Startups` s 
JOIN `Users` u ON s.`entrepreneur_id` = u.`user_id`
WHERE u.`email` = 'entrepreneur@test.com';

-- Ensure Token_Usage table exists with correct structure
CREATE TABLE IF NOT EXISTS `Token_Usage` (
  `usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tokens_used` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT (CURDATE()),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  UNIQUE KEY `user_date` (`user_id`, `date`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure all necessary tables for AI functionality exist
-- (They should already exist from your import, but this ensures compatibility)

-- Display completion message
SELECT 'Localhost setup completed successfully!' as 'Status';
SELECT 'Test accounts created with password: password' as 'Login Info';
SELECT 
    'entrepreneur@test.com (Entrepreneur)' as 'Account 1',
    'investor@test.com (Investor)' as 'Account 2', 
    'jobseeker@test.com (Job Seeker)' as 'Account 3';

-- Show some statistics
SELECT 
    (SELECT COUNT(*) FROM Users) as 'Total Users',
    (SELECT COUNT(*) FROM AI_Conversations) as 'AI Conversations',
    (SELECT COUNT(*) FROM Startups) as 'Total Startups',
    (SELECT COUNT(*) FROM Jobs) as 'Active Jobs'; 