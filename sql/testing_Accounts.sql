-- ===================================================================
-- TEST ACCOUNTS FOR THESIS MANAGEMENT SYSTEM
-- ===================================================================

-- Clear existing test accounts first (optional)
DELETE FROM users WHERE email LIKE '%@test.pcc%';

-- ===================================================================
-- TEST ADMIN ACCOUNT
-- ===================================================================
INSERT INTO users (
    employee_id, 
    name, 
    email, 
    password, 
    role, 
    department, 
    phone, 
    status
) VALUES (
    'ADMIN001',
    'Admin Test User',
    'admin@test.pcc',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'admin',
    'Administration',
    '09123456789',
    'active'
);

-- ===================================================================
-- TEST FACULTY ACCOUNTS
-- ===================================================================

-- Faculty Account 1 - Research Teacher
INSERT INTO users (
    employee_id, 
    name, 
    email, 
    password, 
    role, 
    department, 
    phone, 
    status
) VALUES (
    'FAC001',
    'Prof. Maria Santos',
    'faculty@test.pcc',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'faculty',
    'Senior High School',
    '09123456790',
    'active'
);

-- Faculty Account 2 - Librarian
INSERT INTO users (
    employee_id, 
    name, 
    email, 
    password, 
    role, 
    department, 
    phone, 
    status
) VALUES (
    'LIB001',
    'Ms. Catherine Cruz',
    'librarian@test.pcc',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'librarian',
    'Library Services',
    '09123456791',
    'active'
);

-- ===================================================================
-- TEST STUDENT ACCOUNTS
-- ===================================================================

-- Student Account 1 - STEM Strand
INSERT INTO users (
    employee_id, 
    name, 
    email, 
    password, 
    role, 
    department, 
    strand,
    year_level,
    phone, 
    status
) VALUES (
    'STU001',
    'Juan Carlos Dela Cruz',
    'student@test.pcc',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'student',
    'Senior High School',
    'STEM',
    'Grade 12',
    '09123456792',
    'active'
);

-- Student Account 2 - ABM Strand  
INSERT INTO users (
    employee_id, 
    name, 
    email, 
    password, 
    role, 
    department, 
    strand,
    year_level,
    phone, 
    status
) VALUES (
    'STU002',
    'Maria Isabelle Garcia',
    'student2@test.pcc',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'student',
    'Senior High School',
    'ABM',
    'Grade 12',
    '09123456793',
    'active'
);

-- Student Account 3 - HUMSS Strand
INSERT INTO users (
    employee_id, 
    name, 
    email, 
    password, 
    role, 
    department, 
    strand,
    year_level,
    phone, 
    status
) VALUES (
    'STU003',
    'Andre Miguel Torres',
    'student3@test.pcc',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'student',
    'Senior High School',
    'HUMSS',
    'Grade 12',
    '09123456794',
    'active'
);

-- ===================================================================
-- VERIFY TEST ACCOUNTS CREATED
-- ===================================================================
SELECT 
    employee_id,
    name,
    email,
    role,
    department,
    strand,
    year_level,
    status
FROM users 
WHERE email LIKE '%@test.pcc%'
ORDER BY role, employee_id;