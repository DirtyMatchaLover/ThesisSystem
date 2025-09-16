<?php
// sample_data_generator.php
// Run this file once to populate your system with realistic sample data for research testing

require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/helpers.php';

class SampleDataGenerator {
    private $db;
    
    // Sample data arrays
    private $sample_titles = [
        "The Impact of Artificial Intelligence on Modern Education Systems in Philippine Senior High Schools",
        "Digital Marketing Strategies for Small and Medium Enterprises in Metro Manila",
        "Sustainable Energy Solutions for Urban Development in Pasig City",
        "The Role of Social Media in Political Awareness Among Filipino Youth",
        "Machine Learning Applications in Healthcare Diagnostics",
        "Climate Change Effects on Agricultural Productivity in the Philippines",
        "Cybersecurity Challenges in Remote Learning Environments",
        "The Influence of K-Pop Culture on Filipino Teenagers' Identity Formation",
        "Blockchain Technology Implementation in Philippine Banking Systems",
        "Mental Health Awareness Programs in Senior High School Settings",
        "E-commerce Growth Trends in Post-Pandemic Philippines",
        "Renewable Energy Adoption in Rural Philippine Communities",
        "The Psychology of Consumer Behavior in Online Shopping",
        "Impact of COVID-19 on Small Business Operations in Pasig City",
        "Educational Technology Integration in Public Senior High Schools",
        "Factors Affecting Student Academic Performance in Mathematics",
        "Social Media Influence on Body Image Among High School Students",
        "Environmental Conservation Practices in Urban Communities",
        "The Role of Peer Tutoring in Improving Academic Achievement",
        "Digital Literacy Skills Assessment Among Senior High Students",
        "Effects of Part-time Work on Student Academic Performance",
        "Mobile Learning Applications in Science Education",
        "Parental Involvement Impact on Student Motivation and Success",
        "Waste Management Solutions for Educational Institutions",
        "The Effectiveness of Blended Learning in Post-Secondary Education"
    ];

    private $sample_abstracts = [
        "This research investigates the transformative effects of artificial intelligence implementation in Philippine educational systems. Through surveys and interviews with 150 educators and 300 students across 10 senior high schools in Metro Manila, this study examines how AI-powered learning platforms enhance personalized education experiences. The methodology includes quantitative analysis of academic performance data and qualitative assessment of user satisfaction surveys. Findings reveal significant improvements in student engagement rates (35% increase) and learning outcome assessments (28% improvement) when AI tools are properly integrated into curriculum design. The research concludes that strategic AI implementation can revolutionize traditional teaching methodologies while addressing challenges such as digital literacy gaps and infrastructure limitations.",
        
        "This study explores effective digital marketing strategies that small and medium enterprises (SMEs) in Metro Manila can implement to achieve sustainable growth in the competitive business landscape. The research methodology combines case study analysis of 50 successful Filipino SMEs with comprehensive surveys of 200 business owners across various industries. Key focus areas include social media marketing effectiveness, search engine optimization practices, e-commerce platform utilization, and customer relationship management systems. Results demonstrate that businesses utilizing integrated digital marketing approaches experienced 42% higher revenue growth compared to traditional marketing methods. The study provides actionable recommendations for SME owners seeking to enhance their digital presence and market competitiveness.",
        
        "This research examines sustainable energy solutions suitable for urban development projects in Pasig City, focusing on solar power integration, energy-efficient building design, and smart grid technologies. The methodology includes environmental impact assessments, cost-benefit analyses, and stakeholder interviews with city planners, developers, and residents. Data collection spans 18 months, analyzing energy consumption patterns across residential, commercial, and institutional sectors. Findings indicate that implementing renewable energy systems can reduce urban carbon footprints by 45% while providing long-term economic benefits. The study proposes a comprehensive framework for sustainable urban energy planning that balances environmental conservation with economic development goals."
    ];

    private $strands = ['STEM', 'ABM', 'HUMSS', 'GAS'];
    private $academic_years = ['2023-2024', '2024-2025'];
    private $statuses = ['approved', 'submitted', 'under_review', 'approved', 'approved']; // Weighted toward approved

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Generate all sample data
     */
    public function generateAll() {
        echo "<h2>🚀 Generating Sample Data for Research Testing</h2>";
        
        $this->createSampleStudents();
        $this->createSampleTheses();
        $this->createSampleComments();
        $this->updateDownloadCounts();
        
        echo "<h3>✅ Sample Data Generation Complete!</h3>";
        echo "<p><strong>You now have:</strong></p>";
        echo "<ul>";
        echo "<li>20 sample student accounts</li>";
        echo "<li>30 realistic thesis submissions</li>";
        echo "<li>Various approval statuses for testing</li>";
        echo "<li>Sample comments and feedback</li>";
        echo "<li>Download/view statistics</li>";
        echo "</ul>";
        echo "<p><a href='index.php?route=admin/dashboard'>👉 View Admin Dashboard</a></p>";
        echo "<p><a href='index.php?route=admin/analytics'>📊 View Analytics Dashboard</a></p>";
    }

    /**
     * Create sample student accounts
     */
    private function createSampleStudents() {
        echo "<p>📚 Creating sample student accounts...</p>";
        
        $first_names = ['Juan Carlos', 'Maria Isabella', 'Jose Miguel', 'Ana Sofia', 'Carlos Eduardo', 'Lucia Elena', 'Miguel Antonio', 'Sofia Isabela', 'Diego Alejandro', 'Valentina Rosa', 'Sebastian Luis', 'Camila Andrea', 'Nicolas David', 'Isabella Maria', 'Mateo Alexander', 'Gabriela Victoria', 'Daniel Fernando', 'Natalia Carmen', 'Alejandro Jose', 'Valeria Esperanza'];
        
        $last_names = ['Santos', 'Garcia', 'Rodriguez', 'Martinez', 'Lopez', 'Gonzalez', 'Perez', 'Sanchez', 'Ramirez', 'Torres', 'Flores', 'Rivera', 'Gomez', 'Diaz', 'Cruz', 'Morales', 'Ortiz', 'Gutierrez', 'Vargas', 'Castillo'];
        
        for ($i = 0; $i < 20; $i++) {
            $first = $first_names[array_rand($first_names)];
            $last = $last_names[array_rand($last_names)];
            $name = $first . ' ' . $last;
            $email = 'student' . ($i + 10) . '@pcc.edu.ph';
            $strand = $this->strands[array_rand($this->strands)];
            $employee_id = 'STU' . str_pad($i + 10, 3, '0', STR_PAD_LEFT);
            
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO users 
                (employee_id, name, email, password, role, department, strand, year_level, status) 
                VALUES (?, ?, ?, ?, 'student', 'Senior High School', ?, 'Grade 12', 'active')
            ");
            
            $stmt->execute([
                $employee_id,
                $name,
                $email,
                '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: "password"
                $strand
            ]);
        }
        
        echo "<p>✅ Created 20 sample student accounts</p>";
    }

    /**
     * Create sample thesis submissions
     */
    private function createSampleTheses() {
        echo "<p>📝 Creating sample thesis submissions...</p>";
        
        // Get student users
        $stmt = $this->db->prepare("SELECT id, strand FROM users WHERE role = 'student' ORDER BY id DESC LIMIT 25");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($students)) {
            echo "<p>❌ No student accounts found. Create students first.</p>";
            return;
        }
        
        for ($i = 0; $i < min(30, count($this->sample_titles)); $i++) {
            $student = $students[array_rand($students)];
            $title = $this->sample_titles[$i];
            $abstract = $this->sample_abstracts[array_rand($this->sample_abstracts)];
            $status = $this->statuses[array_rand($this->statuses)];
            $academic_year = $this->academic_years[array_rand($this->academic_years)];
            
            // Create submission date (random within last 6 months)
            $submission_date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 180) . ' days'));
            
            // Create approval date if approved (3-14 days after submission)
            $approval_date = null;
            $publication_date = null;
            $is_public = 0;
            
            if ($status === 'approved') {
                $approval_date = date('Y-m-d H:i:s', strtotime($submission_date . ' +' . rand(1, 14) . ' days'));
                $publication_date = $approval_date;
                $is_public = 1;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO theses 
                (user_id, title, abstract, file_path, status, academic_year, strand, 
                 submission_date, approval_date, publication_date, is_public, 
                 view_count, download_count, formatting_score, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $student['id'],
                $title,
                $abstract,
                'uploads/theses/sample_thesis_' . ($i + 1) . '.pdf',
                $status,
                $academic_year,
                $student['strand'],
                $submission_date,
                $approval_date,
                $publication_date,
                $is_public,
                rand(0, 150), // view count
                rand(0, 50),  // download count
                rand(30, 50) / 10, // formatting score (3.0-5.0)
                $submission_date
            ]);
        }
        
        echo "<p>✅ Created 30 sample thesis submissions</p>";
    }

    /**
     * Create sample comments and feedback
     */
    private function createSampleComments() {
        echo "<p>💬 Creating sample comments and feedback...</p>";
        
        // Get thesis IDs
        $stmt = $this->db->prepare("SELECT id FROM theses ORDER BY RAND() LIMIT 15");
        $stmt->execute();
        $thesis_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get faculty user ID
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role IN ('faculty', 'admin') LIMIT 1");
        $stmt->execute();
        $faculty_id = $stmt->fetchColumn();
        
        if (!$faculty_id) {
            echo "<p>❌ No faculty account found for comments</p>";
            return;
        }
        
        $sample_comments = [
            "Excellent research methodology and clear presentation of findings. Well done!",
            "The abstract needs to be more concise. Please revise and resubmit.",
            "Strong theoretical framework, but the conclusion could be expanded.",
            "Great use of primary sources and comprehensive analysis.",
            "Please ensure all citations follow APA format guidelines.",
            "Outstanding work with practical applications for real-world problems.",
            "The methodology section needs more detail about data collection procedures.",
            "Impressive research scope and thorough literature review.",
            "Minor formatting issues need to be addressed before final approval.",
            "Excellent contribution to the field with innovative approaches."
        ];
        
        foreach ($thesis_ids as $thesis_id) {
            if (rand(0, 100) < 70) { // 70% chance of having comments
                $comment = $sample_comments[array_rand($sample_comments)];
                $comment_type = rand(0, 100) < 80 ? 'feedback' : 'approval';
                
                $stmt = $this->db->prepare("
                    INSERT INTO thesis_comments 
                    (thesis_id, user_id, comment_type, content, created_at) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $thesis_id,
                    $faculty_id,
                    $comment_type,
                    $comment,
                    date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
                ]);
            }
        }
        
        echo "<p>✅ Created sample comments and feedback</p>";
    }

    /**
     * Update download and view counts for realism
     */
    private function updateDownloadCounts() {
        echo "<p>📊 Updating download and view statistics...</p>";
        
        $stmt = $this->db->prepare("
            UPDATE theses 
            SET view_count = FLOOR(RAND() * 100) + 10,
                download_count = FLOOR(RAND() * 30) + 1
            WHERE status = 'approved'
        ");
        $stmt->execute();
        
        echo "<p>✅ Updated statistics for approved theses</p>";
    }

    /**
     * Reset all sample data (for testing)
     */
    public function resetSampleData() {
        echo "<h2>🔄 Resetting Sample Data</h2>";
        
        // Delete sample theses
        $stmt = $this->db->prepare("DELETE FROM theses WHERE file_path LIKE 'uploads/theses/sample_%'");
        $stmt->execute();
        
        // Delete sample students (keep original test accounts)
        $stmt = $this->db->prepare("DELETE FROM users WHERE employee_id LIKE 'STU%' AND id > 10");
        $stmt->execute();
        
        // Delete sample comments
        $stmt = $this->db->prepare("DELETE FROM thesis_comments WHERE content LIKE '%sample%' OR created_at > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        $stmt->execute();
        
        echo "<p>✅ Sample data has been reset</p>";
        echo "<p><a href='?action=generate'>🚀 Generate New Sample Data</a></p>";
    }
}

// Handle actions
$action = $_GET['action'] ?? 'menu';
$generator = new SampleDataGenerator();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sample Data Generator - PCC Thesis System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .menu { background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .button { display: inline-block; padding: 10px 20px; background: #d32f2f; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .button:hover { background: #b71c1c; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>

<?php if ($action === 'menu'): ?>
    <h1>🎓 Sample Data Generator</h1>
    <p>Generate realistic sample data for testing your PCC Thesis Management System</p>
    
    <div class="menu">
        <h3>🚀 Quick Actions</h3>
        <a href="?action=generate" class="button">Generate Sample Data</a>
        <a href="?action=reset" class="button" style="background: #ff5722;">Reset Sample Data</a>
        <a href="index.php?route=admin/dashboard" class="button" style="background: #4caf50;">View Dashboard</a>
    </div>
    
    <div class="info">
        <h4>📋 What this will create:</h4>
        <ul>
            <li><strong>20 student accounts</strong> across different strands (STEM, ABM, HUMSS, GAS)</li>
            <li><strong>30 thesis submissions</strong> with realistic titles and abstracts</li>
            <li><strong>Mixed approval statuses</strong> for testing the approval workflow</li>
            <li><strong>Faculty comments</strong> and feedback on submissions</li>
            <li><strong>Download/view statistics</strong> for analytics testing</li>
        </ul>
    </div>
    
    <div class="warning">
        <h4>⚠️ Important Notes:</h4>
        <ul>
            <li>This will create test data in your database - use only for development/testing</li>
            <li>All sample accounts use password: <strong>"password"</strong></li>
            <li>Sample thesis files are just placeholders - real PDFs are not created</li>
            <li>Run this <strong>after</strong> setting up your database with the init.sql schema</li>
        </ul>
    </div>

<?php elseif ($action === 'generate'): ?>
    <?php $generator->generateAll(); ?>
    <p><a href="?" class="button">← Back to Menu</a></p>

<?php elseif ($action === 'reset'): ?>
    <?php $generator->resetSampleData(); ?>
    <p><a href="?" class="button">← Back to Menu</a></p>

<?php endif; ?>

</body>
</html>