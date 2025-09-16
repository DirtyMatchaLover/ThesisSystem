<?php require_once __DIR__ . '/layout/header.php'; ?>

<div class="main-content">
    <div class="container">
        <section class="about-section">
            <div class="about-container">
                <h1 class="about-title">About PCC Thesis Management System</h1>
                
                <div class="about-content">
                    <p class="about-description">
                        The Pasig Catholic College Thesis Management System is a comprehensive digital platform designed to streamline the submission, review, and publication of student research papers. Our system provides a secure, user-friendly environment where students can easily submit their academic work, faculty members can efficiently review and approve submissions, and the wider academic community can access approved research papers. Built with modern web technologies, this platform ensures data security, accessibility, and seamless collaboration between students, faculty, and administrators while maintaining the highest standards of academic integrity and research excellence.
                    </p>
                </div>

                <!-- Redirect Options -->
                <div class="redirect-section">
                    <h3>Quick Navigation</h3>
                    <div class="redirect-buttons">
                        <a href="<?= route('home') ?>" class="redirect-btn primary">
                            🏠 Back to Home
                        </a>
                        <a href="<?= route('research') ?>" class="redirect-btn secondary">
                            📚 Browse Research
                        </a>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= route('thesis/create') ?>" class="redirect-btn success">
                                📝 Submit Thesis
                            </a>
                        <?php else: ?>
                            <a href="<?= route('auth/select') ?>" class="redirect-btn success">
                                🚀 Get Started
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Auto-redirect option -->
                <div class="auto-redirect-section">
                    <p class="redirect-info">
                        <small>
                            Want to go somewhere specific? 
                            <a href="#" onclick="showRedirectOptions()" class="redirect-link">Click here for more options</a>
                        </small>
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Additional redirect modal -->
<div id="redirectModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeRedirectModal()">&times;</span>
        <h3>Where would you like to go?</h3>
        <div class="redirect-options">
            <a href="<?= route('home') ?>" class="option-link">🏠 Home Page</a>
            <a href="<?= route('research') ?>" class="option-link">🔍 Research Papers</a>
            <?php if (is_logged_in()): ?>
                <?php $user = current_user(); ?>
                <?php if ($user && $user['role'] === 'student'): ?>
                    <a href="<?= route('thesis/list') ?>" class="option-link">📄 My Submissions</a>
                <?php endif; ?>
                <?php if ($user && in_array($user['role'], ['faculty', 'admin', 'librarian'])): ?>
                    <a href="<?= route('admin/dashboard') ?>" class="option-link">⚙️ Dashboard</a>
                <?php endif; ?>
                <a href="<?= route('thesis/create') ?>" class="option-link">📝 Submit New Thesis</a>
            <?php else: ?>
                <a href="<?= route('auth/select') ?>" class="option-link">🔐 Login / Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* About Page Styles */
.about-section {
    padding: 40px 0;
    min-height: 60vh;
}

.about-container {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.about-title {
    color: #d32f2f;
    font-size: 2.5em;
    margin-bottom: 30px;
    font-weight: 600;
}

.about-content {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
}

.about-description {
    font-size: 1.1em;
    line-height: 1.8;
    color: #444;
    text-align: justify;
    margin: 0;
}

.redirect-section {
    margin: 40px 0;
}

.redirect-section h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.4em;
}

.redirect-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.redirect-btn {
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 1em;
}

.redirect-btn.primary {
    background: #d32f2f;
    color: white;
}

.redirect-btn.primary:hover {
    background: #b71c1c;
    transform: translateY(-2px);
}

.redirect-btn.secondary {
    background: #1976d2;
    color: white;
}

.redirect-btn.secondary:hover {
    background: #1565c0;
    transform: translateY(-2px);
}

.redirect-btn.success {
    background: #388e3c;
    color: white;
}

.redirect-btn.success:hover {
    background: #2e7d32;
    transform: translateY(-2px);
}

.auto-redirect-section {
    margin-top: 30px;
}

.redirect-info {
    color: #666;
    font-style: italic;
}

.redirect-link {
    color: #d32f2f;
    text-decoration: none;
    font-weight: 500;
}

.redirect-link:hover {
    text-decoration: underline;
}

/* Modal Styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 400px;
    width: 90%;
    position: relative;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.close {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #666;
}

.close:hover {
    color: #d32f2f;
}

.redirect-options {
    margin-top: 20px;
}

.option-link {
    display: block;
    padding: 12px 15px;
    margin: 8px 0;
    background: #f5f5f5;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.option-link:hover {
    background: #d32f2f;
    color: white;
    transform: translateX(5px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .about-title {
        font-size: 2em;
    }
    
    .about-content {
        padding: 20px;
    }
    
    .about-description {
        font-size: 1em;
        text-align: left;
    }
    
    .redirect-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .redirect-btn {
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }
}
</style>

<script>
function showRedirectOptions() {
    document.getElementById('redirectModal').style.display = 'flex';
}

function closeRedirectModal() {
    document.getElementById('redirectModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('redirectModal');
    if (event.target === modal) {
        closeRedirectModal();
    }
}

// Auto-focus effect
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll animation
    const aboutSection = document.querySelector('.about-section');
    aboutSection.style.opacity = '0';
    aboutSection.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        aboutSection.style.transition = 'all 0.6s ease';
        aboutSection.style.opacity = '1';
        aboutSection.style.transform = 'translateY(0)';
    }, 100);
});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>