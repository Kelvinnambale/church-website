CREATE DATABASE IF NOT EXISTS changochurch_db;

-- Use the database
USE changochurch_db;

-- Create ministry registrations table
CREATE TABLE IF NOT EXISTS ministry_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    dob DATE NOT NULL,
    age INT NOT NULL,
    gender VARCHAR(20) NOT NULL,
    ministry VARCHAR(50) NOT NULL,
    membership VARCHAR(5) NOT NULL,
    attendance VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    availability TEXT,
    skills TEXT,
    motivation TEXT,
    consent TINYINT(1) NOT NULL DEFAULT 0,
    registration_date DATETIME NOT NULL,
    processed TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create index on ministry for faster queries
CREATE INDEX idx_ministry ON ministry_registrations (ministry);

-- Create index on registration date for reports
CREATE INDEX idx_registration_date ON ministry_registrations (registration_date);

-- Optional: Create a sample admin user for managing registrations
-- CREATE TABLE IF NOT EXISTS admin_users (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     username VARCHAR(50) NOT NULL UNIQUE,
--     password VARCHAR(255) NOT NULL,
--     fullname VARCHAR(100) NOT NULL,
--     email VARCHAR(100) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing (comment out in production)
-- INSERT INTO ministry_registrations 
--     (name, phone, email, dob, age, gender, ministry, membership, attendance, address, availability, skills, motivation, consent, registration_date)
-- VALUES
--     ('John Doe', '+254712345678', 'john@example.com', '1995-05-15', 30, 'Male', 'YFP Ministry', 'Yes', '1-3 years', '123 Chango Road, Nairobi', 'Sundays, Weekdays Evening', 'Teaching, Music', 'I want to serve God through youth mentorship', 1, NOW()),
--     ('Jane Smith', '+254723456789', 'jane@example.com', '1990-08-22', 35, 'Female', 'YFP Ministry', 'Yes', '>3 years', '456 Faith Street, Nairobi', 'Saturdays, Sundays', 'Media, Hospitality', 'I have a passion for youth work', 1, NOW()),
--     ('Michael Odhiambo', '+254734567890', 'michael@example.com', '1980-01-10', 45, 'Male', 'Quakermen', 'Yes', '>3 years', '789 Blessing Ave, Nairobi', 'Weekdays Morning, Sundays', 'Counseling, Teaching', 'I want to share my experience with others', 1, NOW());

-- Grant privileges (adjust as needed for your hosting environment)
-- GRANT ALL PRIVILEGES ON changochurch_db.* TO 'your_username'@'localhost';
-- FLUSH PRIVILEGES;

-- Log setup completion
SELECT 'Chango Church Ministry Registration Database setup completed successfully' AS 'Setup Status';