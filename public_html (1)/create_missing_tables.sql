-- Add missing tables for AI Advisor functionality
-- Run this in phpMyAdmin on your existing database

USE `kapitalcapstone`;

-- Create AI_Response_Cache table if it doesn't exist
CREATE TABLE IF NOT EXISTS `AI_Response_Cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_hash` varchar(32) NOT NULL,
  `question` text NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_hash` (`question_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create user_token_usage table if it doesn't exist
CREATE TABLE IF NOT EXISTS `user_token_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_count` int(11) NOT NULL,
  `usage_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_user_date` (`user_id`, `usage_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Show all tables to verify
SHOW TABLES; 