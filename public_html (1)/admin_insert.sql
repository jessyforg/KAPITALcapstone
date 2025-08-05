-- Insert admin user
INSERT INTO Users (name, email, password, role, verification_status)
VALUES (
    'Admin User',
    'admin@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is the hashed version of 'haha'
    'admin',
    'verified'
); 