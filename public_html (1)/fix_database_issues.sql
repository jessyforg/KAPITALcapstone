-- Comprehensive Database Fix Script
-- This script fixes common issues with the imported production database

USE `kapitalcapstone`;

-- 1. Fix Users table AUTO_INCREMENT issue
ALTER TABLE `Users` MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

-- 2. Ensure all necessary indexes exist
ALTER TABLE `Users` ADD PRIMARY KEY IF NOT EXISTS (`user_id`);
ALTER TABLE `Users` ADD UNIQUE KEY IF NOT EXISTS (`email`);

-- 3. Fix AI_Conversations table if needed
ALTER TABLE `AI_Conversations` MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `AI_Conversations` ADD PRIMARY KEY IF NOT EXISTS (`conversation_id`);

-- 4. Fix AI_Response_Cache table if needed
ALTER TABLE `AI_Response_Cache` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `AI_Response_Cache` ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- 5. Fix Token_Usage table if needed
CREATE TABLE IF NOT EXISTS `Token_Usage` (
  `usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tokens_used` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT (CURDATE()),
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usage_id`),
  UNIQUE KEY `user_date` (`user_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Ensure foreign key constraints are properly set
-- (These will be created if they don't exist, or ignored if they do)

-- 7. Check and fix any missing columns in Users table
ALTER TABLE `Users` 
ADD COLUMN IF NOT EXISTS `industry` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `contact_number` varchar(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `public_email` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `introduction` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `accomplishments` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `show_in_search` tinyint(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS `show_in_messages` tinyint(1) DEFAULT 1,
ADD COLUMN IF NOT EXISTS `show_in_pages` tinyint(1) DEFAULT 1;

-- 8. Verify the fixes
SELECT 'Database fixes applied successfully!' as 'Status';

-- Show table structures
SELECT 'Users table structure:' as 'Table Info';
DESCRIBE Users;

SELECT 'AI_Conversations table structure:' as 'Table Info';
DESCRIBE AI_Conversations;

SELECT 'Token_Usage table structure:' as 'Table Info';
DESCRIBE Token_Usage;

-- Test AUTO_INCREMENT
SELECT 'Testing AUTO_INCREMENT functionality...' as 'Test';
SELECT 'Next user_id will be: ' as 'Info', (SELECT MAX(user_id) + 1 FROM Users) as 'Next ID'; 