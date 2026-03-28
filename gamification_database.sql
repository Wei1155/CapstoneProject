CREATE DATABASE IF NOT EXISTS capstone_project;
USE capstone_project;

-- Leader's exact activity_logs table (Module 3.4)
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_text VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table to store all available badges in the system (Module 3.1)
CREATE TABLE badges (
    badge_id INT PRIMARY KEY AUTO_INCREMENT,
    badge_name VARCHAR(100) NOT NULL,
    points_required INT NOT NULL
);

-- Table to track which user has earned which badge (Module 3.3)
CREATE TABLE user_badges (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    date_earned DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (badge_id) REFERENCES badges(badge_id)
);
