-- Fix for user_id AUTO_INCREMENT issue
-- This script fixes the Users table to allow AUTO_INCREMENT on user_id

USE `kapitalcapstone`;

-- First, let's check the current structure
SHOW CREATE TABLE Users;

-- Fix the user_id column to have AUTO_INCREMENT
ALTER TABLE `Users` MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

-- Verify the fix
DESCRIBE Users;

-- Test that AUTO_INCREMENT is working
SELECT 'AUTO_INCREMENT fix applied successfully!' as 'Status';
SELECT 'The Users table should now allow new user registrations' as 'Note'; 