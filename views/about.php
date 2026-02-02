<?php require_once __DIR__ . '/layout/header.php'; ?>
<?php require_once __DIR__ . '/layout/navigation.php'; ?>

<div class="main-content">
    <div class="container">
        <section class="about-section">
            <div class="about-container">
                <h1 class="about-title">About ResearchHub</h1>

                <div class="about-content">
                    <p class="about-description">
                        ResearchHub is a comprehensive digital platform designed to streamline the submission, review, and publication of student research papers. Our system provides a secure, user-friendly environment where students can easily submit their academic work, faculty members can efficiently review and approve submissions, and the wider academic community can access approved research papers. Built with modern web technologies, this platform ensures data security, accessibility, and seamless collaboration between students, faculty, and administrators while maintaining the highest standards of academic integrity and research excellence.
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
/* About Page Styles - Library Theme */
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
    color: #7b3f00;
    font-size: 2.5em;
    margin-bottom: 30px;
    font-weight: 600;
    font-family: 'Georgia', 'Garamond', serif;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.about-content {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    margin-bottom: 40px;
    border: 2px solid #d4a574;
}

.about-description {
    font-size: 1.1em;
    line-height: 1.8;
    color: #2c2416;
    text-align: justify;
    margin: 0;
    font-family: 'Georgia', serif;
}

.redirect-section {
    margin: 40px 0;
}

.redirect-section h3 {
    color: #3d2817;
    margin-bottom: 20px;
    font-size: 1.4em;
    font-family: 'Georgia', serif;
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
    font-family: 'Georgia', serif;
    border: 2px solid transparent;
}

.redirect-btn.primary {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border-color: #d4a574;
}

.redirect-btn.primary:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(123, 63, 0, 0.3);
}

.redirect-btn.secondary {
    background: linear-gradient(135deg, #8b6f47 0%, #6f5635 100%);
    color: #f5e6d3;
    border-color: #d4a574;
}

.redirect-btn.secondary:hover {
    background: linear-gradient(135deg, #6f5635 0%, #5a4429 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(139, 111, 71, 0.3);
}

.redirect-btn.success {
    background: linear-gradient(135deg, #d4a574 0%, #c9955f 100%);
    color: #3d2817;
    border-color: #d4a574;
}

.redirect-btn.success:hover {
    background: linear-gradient(135deg, #c9955f 0%, #b88750 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212, 165, 116, 0.4);
}

.auto-redirect-section {
    margin-top: 30px;
}

.redirect-info {
    color: #3d2817;
    font-style: italic;
    font-family: 'Georgia', serif;
}

.redirect-link {
    color: #7b3f00;
    text-decoration: none;
    font-weight: 500;
}

.redirect-link:hover {
    text-decoration: underline;
    color: #5a2d00;
}

/* Modal Styles - Library Theme */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(61, 40, 23, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    padding: 30px;
    border-radius: 10px;
    max-width: 400px;
    width: 90%;
    position: relative;
    box-shadow: 0 8px 30px rgba(61, 40, 23, 0.3);
    border: 2px solid #d4a574;
}

.modal-content h3 {
    color: #3d2817;
    font-family: 'Georgia', serif;
    margin-bottom: 20px;
}

.close {
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #8b6f47;
}

.close:hover {
    color: #7b3f00;
}

.redirect-options {
    margin-top: 20px;
}

.option-link {
    display: block;
    padding: 12px 15px;
    margin: 8px 0;
    background: rgba(212, 165, 116, 0.2);
    color: #3d2817;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
    border: 1px solid #d4a574;
    font-family: 'Georgia', serif;
}

.option-link:hover {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    transform: translateX(5px);
    box-shadow: 0 2px 10px rgba(123, 63, 0, 0.3);
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