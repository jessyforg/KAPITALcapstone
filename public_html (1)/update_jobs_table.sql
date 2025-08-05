ALTER TABLE Jobs
ADD COLUMN status ENUM('pending', 'active', 'rejected') DEFAULT 'pending',
ADD COLUMN rejection_reason TEXT DEFAULT NULL; 