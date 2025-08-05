-- Add privacy settings columns to Users table
ALTER TABLE Users
ADD COLUMN show_in_search TINYINT(1) DEFAULT 1,
ADD COLUMN show_in_messages TINYINT(1) DEFAULT 1,
ADD COLUMN show_in_pages TINYINT(1) DEFAULT 1;

-- Create Tickets table
CREATE TABLE IF NOT EXISTS Tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('bug', 'feature', 'improvement', 'other') NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
); 