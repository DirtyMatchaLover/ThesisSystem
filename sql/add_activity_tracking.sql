-- ===================================================================
-- USER ACTIVITY TRACKING SYSTEM
-- For collecting individual account data for analysis and SOP
-- ===================================================================

-- Activity Log Table - Track all user actions
CREATE TABLE IF NOT EXISTS user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL, -- login, logout, upload, view, download, search, etc.
    activity_description TEXT,

    -- Related entities
    thesis_id INT NULL,
    target_user_id INT NULL, -- For admin actions on other users

    -- Additional data (JSON format for flexibility)
    metadata JSON, -- Store extra info like search terms, file names, etc.

    -- Session tracking
    session_id VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,

    -- Timing
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration INT NULL, -- For activities with duration (in seconds)

    -- Indexes for fast queries
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_thesis_id (thesis_id),
    INDEX idx_created_at (created_at),
    INDEX idx_session_id (session_id),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE SET NULL,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- User Sessions Table - Track login sessions
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,

    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    is_active BOOLEAN DEFAULT TRUE,

    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_is_active (is_active),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- User Statistics Table - Aggregate stats per user
CREATE TABLE IF NOT EXISTS user_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,

    -- Login stats
    total_logins INT DEFAULT 0,
    last_login TIMESTAMP NULL,

    -- Thesis interaction stats
    theses_uploaded INT DEFAULT 0,
    theses_viewed INT DEFAULT 0,
    theses_downloaded INT DEFAULT 0,

    -- Search activity
    total_searches INT DEFAULT 0,

    -- Time tracking
    total_time_spent INT DEFAULT 0, -- in seconds

    -- For admins/faculty
    theses_reviewed INT DEFAULT 0,
    comments_made INT DEFAULT 0,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create views for easy reporting
-- View 1: User Activity Summary
CREATE OR REPLACE VIEW user_activity_summary AS
SELECT
    u.id AS user_id,
    u.name,
    u.email,
    u.role,
    u.strand,
    u.department,
    COUNT(DISTINCT ua.id) AS total_activities,
    COUNT(DISTINCT DATE(ua.created_at)) AS active_days,
    MIN(ua.created_at) AS first_activity,
    MAX(ua.created_at) AS last_activity,
    COUNT(DISTINCT CASE WHEN ua.activity_type = 'login' THEN ua.id END) AS total_logins,
    COUNT(DISTINCT CASE WHEN ua.activity_type = 'thesis_upload' THEN ua.id END) AS theses_uploaded,
    COUNT(DISTINCT CASE WHEN ua.activity_type = 'thesis_view' THEN ua.id END) AS theses_viewed,
    COUNT(DISTINCT CASE WHEN ua.activity_type = 'thesis_download' THEN ua.id END) AS theses_downloaded,
    COUNT(DISTINCT CASE WHEN ua.activity_type = 'search' THEN ua.id END) AS searches_performed
FROM users u
LEFT JOIN user_activities ua ON u.id = ua.user_id
GROUP BY u.id, u.name, u.email, u.role, u.strand, u.department;

-- View 2: Daily Activity Report
CREATE OR REPLACE VIEW daily_activity_report AS
SELECT
    DATE(created_at) AS activity_date,
    COUNT(DISTINCT user_id) AS unique_users,
    COUNT(*) AS total_activities,
    COUNT(DISTINCT CASE WHEN activity_type = 'login' THEN user_id END) AS users_logged_in,
    COUNT(CASE WHEN activity_type = 'thesis_upload' THEN 1 END) AS theses_uploaded,
    COUNT(CASE WHEN activity_type = 'thesis_view' THEN 1 END) AS theses_viewed,
    COUNT(CASE WHEN activity_type = 'thesis_download' THEN 1 END) AS theses_downloaded
FROM user_activities
GROUP BY DATE(created_at)
ORDER BY activity_date DESC;

-- View 3: Individual User Report (for SOP analysis)
CREATE OR REPLACE VIEW individual_user_report AS
SELECT
    u.id,
    u.name,
    u.email,
    u.role,
    u.strand,
    u.department,
    u.year_level,
    u.created_at AS account_created,

    -- Activity counts
    COALESCE(us.total_logins, 0) AS total_logins,
    COALESCE(us.last_login, NULL) AS last_login,
    COALESCE(us.theses_uploaded, 0) AS theses_uploaded,
    COALESCE(us.theses_viewed, 0) AS theses_viewed,
    COALESCE(us.theses_downloaded, 0) AS theses_downloaded,
    COALESCE(us.total_searches, 0) AS total_searches,
    COALESCE(us.theses_reviewed, 0) AS theses_reviewed,
    COALESCE(us.comments_made, 0) AS comments_made,

    -- Time spent (formatted)
    CONCAT(
        FLOOR(COALESCE(us.total_time_spent, 0) / 3600), 'h ',
        FLOOR((COALESCE(us.total_time_spent, 0) % 3600) / 60), 'm'
    ) AS total_time_spent,

    -- Engagement score (custom metric)
    (
        COALESCE(us.total_logins, 0) * 1 +
        COALESCE(us.theses_uploaded, 0) * 10 +
        COALESCE(us.theses_viewed, 0) * 2 +
        COALESCE(us.theses_downloaded, 0) * 3 +
        COALESCE(us.total_searches, 0) * 1 +
        COALESCE(us.theses_reviewed, 0) * 5 +
        COALESCE(us.comments_made, 0) * 3
    ) AS engagement_score

FROM users u
LEFT JOIN user_statistics us ON u.id = us.user_id
ORDER BY engagement_score DESC;
