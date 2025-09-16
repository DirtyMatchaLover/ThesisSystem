-- Enhanced Database Schema for Thesis Management System
-- This schema includes all tables needed for the admin dashboard functionality

-- ===================================================================
-- 1. DROP EXISTING TABLES (if they exist) - BE CAREFUL IN PRODUCTION
-- ===================================================================
-- DROP TABLE IF EXISTS thesis_revisions;
-- DROP TABLE IF EXISTS thesis_keywords;
-- DROP TABLE IF EXISTS thesis_categories;
-- DROP TABLE IF EXISTS thesis_comments;
-- DROP TABLE IF EXISTS keywords;
-- DROP TABLE IF EXISTS categories;
-- DROP TABLE IF EXISTS theses;
-- DROP TABLE IF EXISTS users;

-- ===================================================================
-- 2. USERS TABLE (Enhanced)
-- ===================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','faculty','admin','librarian') NOT NULL DEFAULT 'student',
    department VARCHAR(100),
    strand VARCHAR(50), -- For SHS students (STEM, ABM, HUMSS, etc.)
    year_level VARCHAR(20), -- Grade 11, Grade 12, etc.
    phone VARCHAR(20),
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role (role),
    INDEX idx_department (department),
    INDEX idx_strand (strand),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ===================================================================
-- 3. CATEGORIES TABLE (Subject Areas)
-- ===================================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    parent_id INT NULL, -- For subcategories
    color_code VARCHAR(7) DEFAULT '#d32f2f', -- For UI theming
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB;

-- ===================================================================
-- 4. KEYWORDS TABLE (For search optimization)
-- ===================================================================
CREATE TABLE IF NOT EXISTS keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL UNIQUE,
    usage_count INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_keyword (keyword),
    INDEX idx_usage_count (usage_count)
) ENGINE=InnoDB;

-- ===================================================================
-- 5. THESES TABLE (Enhanced with workflow support)
-- ===================================================================
CREATE TABLE IF NOT EXISTS theses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(500) NOT NULL,
    abstract TEXT NOT NULL,
    introduction TEXT,
    methodology TEXT,
    
    -- File management
    file_path VARCHAR(500) NOT NULL,
    file_size INT, -- in bytes
    file_type VARCHAR(10) DEFAULT 'pdf',
    original_filename VARCHAR(255),
    
    -- Academic metadata
    academic_year VARCHAR(10) NOT NULL DEFAULT '2024-2025', -- 2024-2025
    semester ENUM('1st','2nd','Summer') DEFAULT '2nd',
    strand VARCHAR(50),
    research_type ENUM('quantitative','qualitative','mixed') DEFAULT 'quantitative',
    
    -- Workflow status
    status ENUM(
        'draft',
        'submitted', 
        'under_review',
        'revision_required',
        'approved',
        'published',
        'rejected',
        'archived'
    ) NOT NULL DEFAULT 'submitted',
    
    -- Progress tracking
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    review_start_date TIMESTAMP NULL,
    approval_date TIMESTAMP NULL,
    publication_date TIMESTAMP NULL,
    
    -- Review metadata
    assigned_reviewer INT NULL, -- faculty member
    review_deadline DATE NULL,
    revision_count INT DEFAULT 0,
    
    -- Publication settings
    is_public BOOLEAN DEFAULT FALSE,
    allow_download BOOLEAN DEFAULT TRUE,
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    
    -- Compliance tracking (for research paper metrics)
    formatting_score DECIMAL(3,2) DEFAULT 0.00, -- 0.00-5.00 scale
    plagiarism_score DECIMAL(5,2) DEFAULT 0.00, -- percentage
    compliance_check_date TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_reviewer) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes for performance
    INDEX idx_status (status),
    INDEX idx_academic_year (academic_year),
    INDEX idx_strand (strand),
    INDEX idx_user_id (user_id),
    INDEX idx_submission_date (submission_date),
    INDEX idx_is_public (is_public),
    INDEX idx_title (title(100)), -- For search
    
    -- Full-text search index
    FULLTEXT(title, abstract)
) ENGINE=InnoDB;

-- ===================================================================
-- 6. THESIS_CATEGORIES TABLE (Many-to-many relationship)
-- ===================================================================
CREATE TABLE IF NOT EXISTS thesis_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    category_id INT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE, -- One primary category per thesis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_thesis_category (thesis_id, category_id),
    INDEX idx_thesis_id (thesis_id),
    INDEX idx_category_id (category_id)
) ENGINE=InnoDB;

-- ===================================================================
-- 7. THESIS_KEYWORDS TABLE (Many-to-many relationship)
-- ===================================================================
CREATE TABLE IF NOT EXISTS thesis_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    keyword_id INT NOT NULL,
    relevance_score DECIMAL(3,2) DEFAULT 1.00, -- For ranking
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
    FOREIGN KEY (keyword_id) REFERENCES keywords(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_thesis_keyword (thesis_id, keyword_id),
    INDEX idx_thesis_id (thesis_id),
    INDEX idx_keyword_id (keyword_id)
) ENGINE=InnoDB;

-- ===================================================================
-- 8. THESIS_COMMENTS TABLE (Feedback and review system)
-- ===================================================================
CREATE TABLE IF NOT EXISTS thesis_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    user_id INT NOT NULL, -- Who made the comment
    parent_id INT NULL, -- For replies
    
    comment_type ENUM('review','feedback','revision_request','approval','rejection','system') NOT NULL DEFAULT 'feedback',
    subject VARCHAR(200),
    content TEXT NOT NULL,
    
    -- Review-specific fields
    section VARCHAR(100), -- Which part of thesis (abstract, methodology, etc.)
    severity ENUM('minor','major','critical') DEFAULT 'minor',
    
    -- Status tracking
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_by INT NULL,
    resolved_at TIMESTAMP NULL,
    
    -- Visibility
    is_public BOOLEAN DEFAULT FALSE, -- Public comments vs private review notes
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES thesis_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_thesis_id (thesis_id),
    INDEX idx_user_id (user_id),
    INDEX idx_comment_type (comment_type),
    INDEX idx_is_resolved (is_resolved),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ===================================================================
-- 9. THESIS_REVISIONS TABLE (Version control)
-- ===================================================================
CREATE TABLE IF NOT EXISTS thesis_revisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thesis_id INT NOT NULL,
    version_number INT NOT NULL,
    
    -- File information
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    original_filename VARCHAR(255),
    
    -- Revision metadata
    revision_notes TEXT,
    changes_summary TEXT,
    uploaded_by INT NOT NULL,
    
    -- Previous version reference
    previous_revision_id INT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (previous_revision_id) REFERENCES thesis_revisions(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_thesis_version (thesis_id, version_number),
    INDEX idx_thesis_id (thesis_id),
    INDEX idx_version_number (version_number),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ===================================================================
-- 10. SYSTEM SETTINGS TABLE (For configuration)
-- ===================================================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB;

-- ===================================================================
-- 11. INSERT DEFAULT DATA
-- ===================================================================

-- Default categories (based on research areas)
INSERT IGNORE INTO categories (name, description, color_code) VALUES
('Computer Science', 'Information Technology, Programming, AI, Machine Learning', '#1e88e5'),
('Business Management', 'Organizational Behavior, Marketing, Economics', '#43a047'),
('Education', 'Pedagogy, Educational Psychology, Learning Methods', '#fb8c00'),
('Healthcare', 'Medical Technology, Patient Care, Health Innovation', '#e53935'),
('Environmental Science', 'Climate Change, Sustainability, Ecology', '#00acc1'),
('Psychology', 'Social Psychology, Behavioral Studies', '#8e24aa'),
('Engineering', 'Technical Innovation, Design, Systems', '#6d4c41'),
('Communication', 'Media Studies, Social Media Impact', '#ff7043'),
('Mathematics', 'Statistics, Applied Math, Data Analysis', '#5c6bc0'),
('Social Sciences', 'Sociology, Anthropology, Political Science', '#26a69a');

-- Default keywords
INSERT IGNORE INTO keywords (keyword, usage_count) VALUES
('Machine Learning', 15),
('Data Analysis', 12),
('Sustainable Energy', 8),
('Digital Marketing', 10),
('Healthcare Technology', 6),
('Educational Psychology', 9),
('Climate Change', 7),
('Social Media', 11),
('Business Strategy', 8),
('Innovation', 14),
('Research Methodology', 20),
('Statistical Analysis', 16),
('Case Study', 13),
('Survey Research', 11),
('Experimental Design', 9);

-- Default system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'PCC Thesis Management System', 'string', 'Name of the website'),
('max_file_size', '10485760', 'number', 'Maximum file size in bytes (10MB)'),
('allowed_file_types', 'pdf', 'string', 'Comma-separated list of allowed file extensions'),
('auto_approve_enabled', 'false', 'boolean', 'Whether to automatically approve theses'),
('email_notifications', 'true', 'boolean', 'Whether to send email notifications'),
('public_access_enabled', 'true', 'boolean', 'Whether public can view approved theses'),
('require_abstract', 'true', 'boolean', 'Whether abstract is required'),
('min_abstract_length', '100', 'number', 'Minimum abstract length in characters'),
('max_title_length', '500', 'number', 'Maximum title length in characters'),
('thesis_retention_years', '7', 'number', 'How many years to retain archived theses');

-- Default admin user (CHANGE THE PASSWORD!)
INSERT IGNORE INTO users (employee_id, name, email, password, role, department, status) VALUES
('ADMIN001', 'System Administrator', 'admin@pcc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'IT Department', 'active'),
('LIB001', 'Faculty Librarian', 'librarian@pcc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian', 'Library', 'active'),
('FAC001', 'Research Teacher', 'teacher@pcc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'Senior High School', 'active');

-- Sample student users for testing
INSERT IGNORE INTO users (employee_id, name, email, password, role, department, strand, year_level, status) VALUES
('STU001', 'Juan Dela Cruz', 'student1@pcc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Senior High School', 'STEM', 'Grade 12', 'active'),
('STU002', 'Maria Santos', 'student2@pcc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Senior High School', 'ABM', 'Grade 12', 'active'),
('STU003', 'Jose Garcia', 'student3@pcc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Senior High School', 'HUMSS', 'Grade 12', 'active');

-- ===================================================================
-- 12. SAMPLE THESIS DATA FOR TESTING (Optional)
-- ===================================================================

-- Sample theses for testing the dashboard
INSERT IGNORE INTO theses (
    user_id, title, abstract, file_path, status, academic_year, strand, 
    submission_date, view_count, download_count
) 
SELECT 
    u.id,
    'The Impact of Machine Learning on Modern Education Systems',
    'This research explores how machine learning technologies are transforming educational practices in senior high schools. The study examines the implementation of AI-powered learning platforms, their effectiveness in personalized education, and the challenges faced by educators in adopting these new technologies. Through surveys and interviews with 100 students and 20 teachers, we found significant improvements in learning outcomes when ML tools were properly integrated into the curriculum.',
    'uploads/theses/sample_thesis_1.pdf',
    'submitted',
    '2024-2025',
    'STEM',
    CURRENT_TIMESTAMP,
    25,
    5
FROM users u WHERE u.employee_id = 'STU001'
LIMIT 1;

INSERT IGNORE INTO theses (
    user_id, title, abstract, file_path, status, academic_year, strand,
    submission_date, approval_date, publication_date, is_public, view_count, download_count
) 
SELECT 
    u.id,
    'Digital Marketing Strategies for Small Business Growth in the Philippines',
    'This study investigates effective digital marketing strategies that small businesses in the Philippines can implement to achieve sustainable growth. The research analyzes social media marketing, search engine optimization, and e-commerce platforms through case studies of 50 successful Filipino SMEs. Results show that businesses utilizing integrated digital strategies experienced 40% higher growth rates compared to traditional marketing approaches.',
    'uploads/theses/sample_thesis_2.pdf',
    'approved',
    '2024-2025',
    'ABM',
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 DAY),
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY),
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY),
    1,
    150,
    35
FROM users u WHERE u.employee_id = 'STU002'
LIMIT 1;

INSERT IGNORE INTO theses (
    user_id, title, abstract, file_path, status, academic_year, strand,
    submission_date, view_count, download_count
) 
SELECT 
    u.id,
    'The Role of Literature in Developing Critical Thinking Skills Among Filipino Students',
    'This qualitative research examines how literature education contributes to the development of critical thinking skills among Grade 12 students in Philippine schools. Using focus group discussions and analytical essays, the study reveals that exposure to diverse literary works significantly enhances students ability to analyze, synthesize, and evaluate complex ideas. The findings suggest that literature-based pedagogy should be emphasized in developing 21st-century learning competencies.',
    'uploads/theses/sample_thesis_3.pdf',
    'under_review',
    '2024-2025',
    'HUMSS',
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY),
    45,
    12
FROM users u WHERE u.employee_id = 'STU003'
LIMIT 1;

-- ===================================================================
-- 13. VIEWS FOR REPORTING (Optional but useful)
-- ===================================================================

-- View for published theses with complete author information
CREATE OR REPLACE VIEW published_theses_view AS
SELECT 
    t.id,
    t.title,
    t.abstract,
    t.academic_year,
    t.strand,
    t.view_count,
    t.download_count,
    t.publication_date,
    t.file_path,
    u.name as author_name,
    u.strand as author_strand,
    u.year_level as author_year,
    GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as categories,
    GROUP_CONCAT(DISTINCT k.keyword SEPARATOR ', ') as keywords
FROM theses t
JOIN users u ON t.user_id = u.id
LEFT JOIN thesis_categories tc ON t.id = tc.thesis_id
LEFT JOIN categories c ON tc.category_id = c.id
LEFT JOIN thesis_keywords tk ON t.id = tk.thesis_id
LEFT JOIN keywords k ON tk.keyword_id = k.id
WHERE t.status = 'approved' AND t.is_public = TRUE
GROUP BY t.id
ORDER BY t.publication_date DESC;

-- View for thesis statistics by strand
CREATE OR REPLACE VIEW strand_statistics_view AS
SELECT 
    u.strand,
    COUNT(t.id) as total_submissions,
    SUM(CASE WHEN t.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN t.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
    SUM(CASE WHEN t.status IN ('submitted', 'under_review') THEN 1 ELSE 0 END) as pending_count,
    AVG(t.view_count) as avg_views,
    AVG(t.download_count) as avg_downloads,
    AVG(CASE 
        WHEN t.approval_date IS NOT NULL AND t.submission_date IS NOT NULL 
        THEN DATEDIFF(t.approval_date, t.submission_date) 
        ELSE NULL 
    END) as avg_approval_days
FROM theses t
JOIN users u ON t.user_id = u.id
WHERE u.strand IS NOT NULL
GROUP BY u.strand
ORDER BY total_submissions DESC;

-- View for monthly submission trends
CREATE OR REPLACE VIEW monthly_submission_trends AS
SELECT 
    DATE_FORMAT(submission_date, '%Y-%m') as submission_month,
    COUNT(*) as total_submissions,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_submissions,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_submissions,
    AVG(DATEDIFF(COALESCE(approval_date, CURRENT_DATE), submission_date)) as avg_processing_days
FROM theses 
WHERE submission_date >= DATE_SUB(CURRENT_DATE, INTERVAL 24 MONTH)
GROUP BY DATE_FORMAT(submission_date, '%Y-%m')
ORDER BY submission_month DESC;

-- ===================================================================
-- 14. STORED PROCEDURES (Advanced functionality)
-- ===================================================================

DELIMITER //

-- Procedure to automatically archive old theses
CREATE PROCEDURE ArchiveOldTheses()
BEGIN
    DECLARE retention_years INT DEFAULT 7;
    
    -- Get retention period from settings
    SELECT CAST(setting_value AS UNSIGNED) INTO retention_years 
    FROM system_settings 
    WHERE setting_key = 'thesis_retention_years';
    
    -- Archive theses older than retention period
    UPDATE theses 
    SET status = 'archived' 
    WHERE status = 'approved' 
    AND publication_date < DATE_SUB(CURRENT_DATE, INTERVAL retention_years YEAR)
    AND status != 'archived';
    
    SELECT ROW_COUNT() as archived_count;
END //

-- Procedure to generate thesis statistics
CREATE PROCEDURE GetThesisStatistics(IN academic_year VARCHAR(10))
BEGIN
    SELECT 
        'Total Submissions' as metric,
        COUNT(*) as value
    FROM theses t
    WHERE (academic_year IS NULL OR t.academic_year = academic_year)
    
    UNION ALL
    
    SELECT 
        'Approved Theses' as metric,
        COUNT(*) as value
    FROM theses t
    WHERE (academic_year IS NULL OR t.academic_year = academic_year)
    AND t.status = 'approved'
    
    UNION ALL
    
    SELECT 
        'Pending Review' as metric,
        COUNT(*) as value
    FROM theses t
    WHERE (academic_year IS NULL OR t.academic_year = academic_year)
    AND t.status IN ('submitted', 'under_review')
    
    UNION ALL
    
    SELECT 
        'Average Approval Time (days)' as metric,
        COALESCE(AVG(DATEDIFF(approval_date, submission_date)), 0) as value
    FROM theses t
    WHERE (academic_year IS NULL OR t.academic_year = academic_year)
    AND approval_date IS NOT NULL;
END //

DELIMITER ;

-- ===================================================================
-- 15. INDEXES FOR PERFORMANCE OPTIMIZATION
-- ===================================================================

-- Additional indexes for common queries
CREATE INDEX idx_theses_status_date ON theses(status, submission_date);
CREATE INDEX idx_theses_public_approved ON theses(is_public, status, publication_date);
CREATE INDEX idx_users_strand_role ON users(strand, role);
CREATE INDEX idx_comments_thesis_type ON thesis_comments(thesis_id, comment_type, created_at);

-- Composite index for search functionality
CREATE INDEX idx_theses_search ON theses(status, is_public, title(50), academic_year);

-- ===================================================================
-- SETUP COMPLETE
-- ===================================================================
-- Default password for all test accounts: 'password'
-- Remember to change these passwords in production!
-- 
-- Admin credentials:
-- Email: admin@pcc.edu.ph
-- Password: password
--
-- Faculty credentials:
-- Email: teacher@pcc.edu.ph
-- Password: password
--
-- Librarian credentials:
-- Email: librarian@pcc.edu.ph
-- Password: password