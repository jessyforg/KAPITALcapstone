-- Fix AI_Conversations table structure
USE `kapitalcapstone`;

-- First, check if the table exists and fix the AUTO_INCREMENT issue
ALTER TABLE `AI_Conversations` MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

-- Ensure the primary key is set
ALTER TABLE `AI_Conversations` ADD PRIMARY KEY IF NOT EXISTS (`conversation_id`);

-- Make sure the user_id foreign key relationship is correct
ALTER TABLE `AI_Conversations` ADD KEY IF NOT EXISTS `idx_user_id` (`user_id`);

-- Fix any null conversation_id values (set them to proper auto-increment values)
UPDATE `AI_Conversations` SET `conversation_id` = NULL WHERE `conversation_id` = 0;

-- Reset AUTO_INCREMENT to start from the next available ID
ALTER TABLE `AI_Conversations` AUTO_INCREMENT = 1;

-- Show the table structure to verify
DESCRIBE `AI_Conversations`; 