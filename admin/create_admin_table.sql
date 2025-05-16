-- SQL script to create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user with username 'admin' and password 'admin123'
INSERT INTO admin_users (username, password_hash) VALUES (
    'admin',
    -- Password hash for 'admin123' using PHP password_hash function with default bcrypt
    '$2y$10$e0NRzQ6v6Q6v6Q6v6Q6v6u6Q6v6Q6v6Q6v6Q6v6Q6v6Q6v6Q6v6Q6'
);
