-- Initialize Music Practice Tracker Database
CREATE DATABASE IF NOT EXISTS music_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user if not exists
CREATE USER IF NOT EXISTS 'music_tracker'@'%' IDENTIFIED BY 'music_tracker_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON music_tracker.* TO 'music_tracker'@'%';
GRANT ALL PRIVILEGES ON music_tracker.* TO 'music_tracker'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the database
USE music_tracker;

-- Set SQL mode
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';