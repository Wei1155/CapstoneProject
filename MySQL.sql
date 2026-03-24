CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(50) NOT NULL,
    user_role VARCHAR(20) NOT NULL,
    action VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS issue_reports (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(50) NOT NULL,
    issue_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    admin_response TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS system_settings (
    config_key VARCHAR(50) PRIMARY KEY,
    config_value VARCHAR(255) NOT NULL
);


INSERT INTO system_settings (config_key, config_value) VALUES 
('system_name', 'Learning Management System'), 
('enable_leaderboard', 'True'), 
('max_quiz_attempts', '3')
ON DUPLICATE KEY UPDATE config_value=VALUES(config_value);