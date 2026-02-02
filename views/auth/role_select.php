<?php require_once __DIR__ . '/../../helpers.php'; ?>
<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<style>
  /* Role Select Page Styles - Book Theme */
  .role-select-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    border: 3px solid var(--border-primary);
    border-top: none;
    margin: 0;
    min-height: calc(100vh - 200px);
    padding: 60px 40px;
    position: relative;
  }

  .role-select-container > * {
    position: relative;
    z-index: 1;
  }

  /* Animated background particles - Themed */
  .particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
    pointer-events: none;
  }

  .particle {
    position: absolute;
    background: var(--accent-primary);
    border-radius: 50%;
    animation: float 15s infinite;
    opacity: 0.1;
  }

  body.dark-theme .particle {
    background: var(--accent-primary);
    opacity: 0.15;
  }

  @keyframes float {
    0%, 100% {
      transform: translateY(0) translateX(0) rotate(0deg);
      opacity: 0;
    }
    10% {
      opacity: 0.1;
    }
    90% {
      opacity: 0.1;
    }
    100% {
      transform: translateY(-100vh) translateX(100px) rotate(360deg);
      opacity: 0;
    }
  }

  body.dark-theme .particle {
    opacity: 0.2;
  }

  /* Page Header */
  .role-header {
    text-align: center;
    margin-bottom: 50px;
    animation: slideDown 0.8s ease-out;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-50px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .role-header h1 {
    font-size: 2.5rem;
    color: var(--text-secondary);
    font-weight: 600;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: 'Georgia', 'Garamond', serif;
    text-shadow: 2px 2px 4px var(--shadow-color);
    border-bottom: 3px solid var(--border-primary);
    padding-bottom: 15px;
    display: inline-block;
  }

  .role-header p {
    font-size: 1.2rem;
    color: var(--text-tertiary);
    font-family: 'Georgia', serif;
    margin-top: 15px;
  }

  /* Cards Grid */
  .cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    margin: 0 auto;
    max-width: 1200px;
  }

  /* Role Card - Book Cover Style */
  .role-card {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 40px 30px;
    text-align: center;
    text-decoration: none;
    color: var(--text-secondary);
    border: 4px solid var(--card-border);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    animation: cardEntrance 0.6s ease-out backwards;
    box-shadow:
      0 8px 24px var(--shadow-color),
      inset 0 0 0 1px rgba(139, 69, 19, 0.1);
  }

  .role-card:nth-child(1) {
    animation-delay: 0.1s;
  }

  .role-card:nth-child(2) {
    animation-delay: 0.2s;
  }

  .role-card:nth-child(3) {
    animation-delay: 0.3s;
  }

  @keyframes cardEntrance {
    from {
      opacity: 0;
      transform: translateY(50px) scale(0.9);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  /* Decorative Book Border */
  .role-card::before {
    content: '';
    position: absolute;
    top: 8px;
    left: 8px;
    right: 8px;
    bottom: 8px;
    border: 2px solid rgba(139, 69, 19, 0.3);
    border-radius: 8px;
    pointer-events: none;
    z-index: 1;
    transition: all 0.4s;
  }

  body.dark-theme .role-card::before {
    border-color: rgba(255, 167, 38, 0.3);
  }

  /* Corner Ornament */
  .role-card::after {
    content: '✦';
    position: absolute;
    top: 15px;
    left: 15px;
    color: var(--accent-primary);
    font-size: 14px;
    opacity: 0.5;
    z-index: 2;
  }

  .role-card:hover {
    transform: translateY(-15px) scale(1.05);
    border-color: var(--accent-primary);
    box-shadow:
      0 20px 60px var(--shadow-color),
      inset 0 0 0 1px var(--accent-primary);
  }

  body.dark-theme .role-card:hover {
    box-shadow:
      0 20px 60px rgba(0, 0, 0, 0.9),
      0 0 30px rgba(255, 167, 38, 0.3),
      inset 0 0 0 2px var(--accent-primary);
  }

  /* Icon Container */
  .role-icon {
    width: 140px;
    height: 140px;
    margin: 0 auto 25px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(139, 69, 19, 0.15), rgba(212, 165, 116, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--border-secondary);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    z-index: 3;
  }

  body.dark-theme .role-icon {
    background: linear-gradient(135deg, rgba(255, 167, 38, 0.2), rgba(255, 183, 77, 0.1));
    border-color: var(--accent-primary);
  }

  .role-card:hover .role-icon {
    transform: rotate(360deg) scale(1.1);
    border-color: var(--accent-primary);
    box-shadow: 0 0 30px var(--accent-primary);
  }

  body.dark-theme .role-card:hover .role-icon {
    box-shadow: 0 0 40px rgba(255, 167, 38, 0.5);
  }

  .role-icon img {
    width: 85%;
    height: 85%;
    object-fit: contain;
    filter: drop-shadow(0 4px 10px var(--shadow-color));
    transition: all 0.4s ease;
  }

  body.dark-theme .role-icon img {
    filter: brightness(1.2) drop-shadow(0 4px 15px rgba(255, 167, 38, 0.3));
  }

  .role-card:hover .role-icon img {
    transform: scale(1.1);
  }

  /* Role Info */
  .role-info h2 {
    font-size: 2rem;
    margin-bottom: 10px;
    font-weight: 600;
    color: var(--text-secondary);
    text-shadow: 2px 2px 4px var(--shadow-color);
    transition: all 0.3s ease;
    font-family: 'Georgia', serif;
    z-index: 3;
    position: relative;
  }

  .role-card:hover .role-info h2 {
    transform: scale(1.05);
    color: var(--accent-primary);
  }

  .role-info p {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    color: var(--text-tertiary);
    font-family: 'Georgia', serif;
  }

  .role-card:hover .role-info p {
    opacity: 1;
  }

  /* Access Badge */
  .access-badge {
    display: inline-block;
    padding: 10px 24px;
    background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    border: 2px solid var(--border-secondary);
    transition: all 0.3s ease;
    color: var(--header-text);
    font-family: 'Georgia', serif;
    box-shadow: 0 4px 15px var(--shadow-color);
  }

  body.dark-theme .access-badge {
    color: #fff;
    border-color: var(--accent-primary);
  }

  .role-card:hover .access-badge {
    transform: scale(1.1);
    box-shadow: 0 6px 25px var(--shadow-color);
  }

  body.dark-theme .role-card:hover .access-badge {
    box-shadow: 0 6px 30px rgba(255, 167, 38, 0.5);
  }

  /* Color accents for each role */
  .role-card.admin .role-icon {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
    border-color: rgba(239, 68, 68, 0.5);
  }

  .role-card.admin:hover {
    border-color: rgba(239, 68, 68, 0.8);
  }

  .role-card.admin:hover .role-icon {
    border-color: rgba(239, 68, 68, 1);
    box-shadow: 0 0 40px rgba(239, 68, 68, 0.6);
  }

  .role-card.faculty .role-icon {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
    border-color: rgba(59, 130, 246, 0.5);
  }

  .role-card.faculty:hover {
    border-color: rgba(59, 130, 246, 0.8);
  }

  .role-card.faculty:hover .role-icon {
    border-color: rgba(59, 130, 246, 1);
    box-shadow: 0 0 40px rgba(59, 130, 246, 0.6);
  }

  .role-card.student .role-icon {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1));
    border-color: rgba(16, 185, 129, 0.5);
  }

  .role-card.student:hover {
    border-color: rgba(16, 185, 129, 0.8);
  }

  .role-card.student:hover .role-icon {
    border-color: rgba(16, 185, 129, 1);
    box-shadow: 0 0 40px rgba(16, 185, 129, 0.6);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .role-select-container {
      padding: 40px 20px;
    }

    .role-header h1 {
      font-size: 1.8rem;
    }

    .role-header p {
      font-size: 1rem;
    }

    .cards-grid {
      grid-template-columns: 1fr;
      gap: 25px;
    }

    .role-card {
      padding: 30px 20px;
    }

    .role-icon {
      width: 120px;
      height: 120px;
    }

    .role-info h2 {
      font-size: 1.6rem;
    }

    .role-card:hover {
      transform: translateY(-10px) scale(1.02);
    }
  }

  @media (max-width: 480px) {
    .role-select-container {
      padding: 30px 15px;
    }

    .role-header h1 {
      font-size: 1.5rem;
    }

    .role-header {
      margin-bottom: 30px;
    }

    .role-icon {
      width: 100px;
      height: 100px;
    }

    .role-info h2 {
      font-size: 1.4rem;
    }

    .role-info p {
      font-size: 0.9rem;
    }
  }
</style>

<div class="main-container">
  <div class="role-select-container">
    <!-- Animated particles -->
    <div class="particles" id="particles"></div>

    <!-- Page Header -->
    <div class="role-header">
      <h1>Welcome to ResearchHub</h1>
      <p>Select your role to continue</p>
    </div>

    <!-- Role Cards -->
    <div class="cards-grid">
      <!-- Admin Card -->
      <a href="<?= route('auth/login') . '&role=admin' ?>" class="role-card admin">
        <div class="role-icon">
          <img src="<?= asset('assets/images/admin.png') ?>" alt="Admin">
        </div>
        <div class="role-info">
          <h2>Admin</h2>
          <p>System Management & Oversight</p>
          <span class="access-badge">Full Access</span>
        </div>
      </a>

      <!-- Faculty Card -->
      <a href="<?= route('auth/login') . '&role=faculty' ?>" class="role-card faculty">
        <div class="role-icon">
          <img src="<?= asset('assets/images/faculty.png') ?>" alt="Faculty">
        </div>
        <div class="role-info">
          <h2>Faculty</h2>
          <p>Review & Guide Research</p>
          <span class="access-badge">Reviewer Access</span>
        </div>
      </a>

      <!-- Student Card -->
      <a href="<?= route('auth/login') . '&role=student' ?>" class="role-card student">
        <div class="role-icon">
          <img src="<?= asset('assets/images/student.png') ?>" alt="Student">
        </div>
        <div class="role-info">
          <h2>Student</h2>
          <p>Submit & Track Research</p>
          <span class="access-badge">Student Access</span>
        </div>
      </a>
    </div>
  </div>
</div>

<script>
  // Create animated particles
  const particlesContainer = document.getElementById('particles');
  const particleCount = 30;

  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';

    const size = Math.random() * 60 + 20;
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.bottom = '-' + size + 'px';
    particle.style.animationDelay = Math.random() * 15 + 's';
    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';

    particlesContainer.appendChild(particle);
  }
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>