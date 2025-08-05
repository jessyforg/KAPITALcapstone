-- Localhost Setup Script for Kapital System
-- This script creates the database and all necessary tables for localhost development

-- Note: Database 'kapitalcapstone' should already exist from your import
-- If you need to create it manually, uncomment the line below:
-- CREATE DATABASE IF NOT EXISTS `kapitalcapstone` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kapitalcapstone`;

-- Set SQL mode
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Table structure for table `AI_Conversations`
CREATE TABLE IF NOT EXISTS `AI_Conversations` (
  `conversation_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `response` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`conversation_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `AI_Response_Cache`
CREATE TABLE IF NOT EXISTS `AI_Response_Cache` (
  `cache_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_hash` varchar(32) NOT NULL,
  `question` text NOT NULL,
  `response` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cache_id`),
  UNIQUE KEY `question_hash` (`question_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Token_Usage`
CREATE TABLE IF NOT EXISTS `Token_Usage` (
  `usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tokens_used` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT (CURDATE()),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  UNIQUE KEY `user_date` (`user_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Users`
CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('entrepreneur','investor','job_seeker') NOT NULL,
  `verification_status` enum('unverified','pending','verified','rejected') DEFAULT 'unverified',
  `industry` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `public_email` varchar(255) DEFAULT NULL,
  `introduction` text DEFAULT NULL,
  `accomplishments` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `User_Privacy_Settings`
CREATE TABLE IF NOT EXISTS `User_Privacy_Settings` (
  `privacy_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `show_in_search` tinyint(1) DEFAULT 1,
  `show_in_messages` tinyint(1) DEFAULT 1,
  `show_in_pages` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`privacy_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Support_Tickets`
CREATE TABLE IF NOT EXISTS `Support_Tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('bug_report','feature_request','general_inquiry','technical_support') NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Startups`
CREATE TABLE IF NOT EXISTS `Startups` (
  `startup_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stage` enum('idea','prototype','startup','growth','established') DEFAULT 'idea',
  `funding_goal` decimal(15,2) DEFAULT NULL,
  `current_funding` decimal(15,2) DEFAULT 0.00,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`startup_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Job_Postings`
CREATE TABLE IF NOT EXISTS `Job_Postings` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `startup_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `job_type` enum('full_time','part_time','contract','internship') DEFAULT 'full_time',
  `status` enum('active','inactive','filled') DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_id`),
  KEY `startup_id` (`startup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Notifications`
CREATE TABLE IF NOT EXISTS `Notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Documents`
CREATE TABLE IF NOT EXISTS `Documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_type` enum('id_card','passport','drivers_license','business_permit','other') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`document_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `Messages`
CREATE TABLE IF NOT EXISTS `Messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data for testing (optional)
-- Insert a test user (password is 'password' hashed with password_hash())
INSERT IGNORE INTO `Users` (`user_id`, `name`, `email`, `password`, `role`, `verification_status`) VALUES
(1, 'Test Entrepreneur', 'entrepreneur@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'entrepreneur', 'verified'),
(2, 'Test Investor', 'investor@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'investor', 'verified'),
(3, 'Test Job Seeker', 'jobseeker@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker', 'verified');

-- Insert privacy settings for test users
INSERT IGNORE INTO `User_Privacy_Settings` (`user_id`, `show_in_search`, `show_in_messages`, `show_in_pages`) VALUES
(1, 1, 1, 1),
(2, 1, 1, 1),
(3, 1, 1, 1);

-- Create foreign key constraints
ALTER TABLE `AI_Conversations`
  ADD CONSTRAINT `ai_conversations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `Token_Usage`
  ADD CONSTRAINT `token_usage_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `User_Privacy_Settings`
  ADD CONSTRAINT `privacy_settings_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `Support_Tickets`
  ADD CONSTRAINT `support_tickets_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `Startups`
  ADD CONSTRAINT `startups_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `Job_Postings`
  ADD CONSTRAINT `job_postings_startup_fk` FOREIGN KEY (`startup_id`) REFERENCES `Startups` (`startup_id`) ON DELETE CASCADE;

ALTER TABLE `Notifications`
  ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `Documents`
  ADD CONSTRAINT `documents_user_fk` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `Messages`
  ADD CONSTRAINT `messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

-- Create triggers for AI_Conversations if needed
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `prevent_zero_conversation_id` BEFORE INSERT ON `AI_Conversations` FOR EACH ROW BEGIN
    IF NEW.conversation_id = 0 THEN
        SET NEW.conversation_id = NULL;
    END IF;
END$$
DELIMITER ;

COMMIT;

-- Display setup completion message
SELECT 'Database setup completed successfully! You can now use the Kapital System on localhost.' as 'Setup Status';

-- Show test user credentials
SELECT 'Test Users Created:' as 'Info', 
       'entrepreneur@test.com, investor@test.com, jobseeker@test.com' as 'Email',
       'password' as 'Password (for all test users)'; 