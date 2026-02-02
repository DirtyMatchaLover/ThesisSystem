<?php require_once __DIR__ . '/../../helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - ResearchHub</title>
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Georgia', 'Garamond', serif;
        background: linear-gradient(135deg, #3d2817 0%, #5a2d00 100%);
        color: #f5e6d3;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        position: relative;
    }

    /* Decorative background pattern */
    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect fill="%23d4a574" opacity="0.03" width="100" height="100"/></svg>');
        pointer-events: none;
        z-index: 0;
    }

    .login-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        max-width: 450px;
        padding: 40px 20px;
        position: relative;
        z-index: 1;
        background: linear-gradient(135deg, rgba(245, 241, 232, 0.1) 0%, rgba(212, 165, 116, 0.05) 100%);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), inset 0 0 50px rgba(212, 165, 116, 0.1);
        border: 2px solid rgba(212, 165, 116, 0.3);
    }

    .login-logo {
        margin-bottom: 60px;
        animation: fadeInDown 0.8s ease-out;
    }

    .login-logo img {
        width: 180px;
        height: 180px;
        filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.5)) drop-shadow(0 0 30px rgba(212, 165, 116, 0.3));
        transition: all 0.3s ease;
    }

    .login-logo:hover img {
        transform: scale(1.05) rotate(2deg);
        filter: drop-shadow(0 15px 35px rgba(0, 0, 0, 0.6)) drop-shadow(0 0 40px rgba(212, 165, 116, 0.5));
    }

    .login-form {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 20px;
        animation: fadeInUp 0.8s ease-out;
    }

    .form-group {
        position: relative;
        width: 100%;
    }

    .form-input {
        width: 100%;
        padding: 18px 20px;
        border: 2px solid #d4a574;
        border-radius: 8px;
        font-size: 16px;
        background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
        color: #3d2817;
        outline: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(61, 40, 23, 0.2);
        font-family: 'Georgia', serif;
    }

    .form-input:focus {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(123, 63, 0, 0.4), 0 0 20px rgba(212, 165, 116, 0.3);
        border: 2px solid #c9955f;
        background: #fff;
    }

    .form-input::placeholder {
        color: #8b6f47;
        font-size: 15px;
    }

    .password-group {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #8b6f47;
        transition: color 0.3s ease;
        user-select: none;
    }

    .password-toggle:hover {
        color: #7b3f00;
    }

    .login-button {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
        color: #f5e6d3;
        border: 2px solid #d4a574;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(123, 63, 0, 0.4);
        text-transform: uppercase;
        letter-spacing: 2px;
        font-family: 'Georgia', serif;
    }

    .login-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(123, 63, 0, 0.6), 0 0 30px rgba(212, 165, 116, 0.3);
        background: linear-gradient(135deg, #8b6f47 0%, #6f5635 100%);
    }

    .login-button:active {
        transform: translateY(0px);
    }

    .forgot-password {
        text-align: center;
        margin-top: 25px;
    }

    .forgot-password a {
        color: #d4a574;
        text-decoration: none;
        font-size: 14px;
        opacity: 0.9;
        transition: all 0.3s ease;
        font-family: 'Georgia', serif;
    }

    .forgot-password a:hover {
        opacity: 1;
        text-decoration: underline;
        color: #e6c080;
    }

    /* Back to Home Button */
    .back-home-btn {
        position: absolute;
        top: 30px;
        left: 30px;
        padding: 12px 24px;
        background: linear-gradient(135deg, rgba(212, 165, 116, 0.2) 0%, rgba(139, 111, 71, 0.2) 100%);
        color: #d4a574;
        border: 2px solid rgba(212, 165, 116, 0.5);
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Georgia', serif;
        backdrop-filter: blur(10px);
        z-index: 10;
    }

    .back-home-btn:hover {
        background: linear-gradient(135deg, #d4a574 0%, #c9955f 100%);
        color: #3d2817;
        border-color: #d4a574;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
    }

    .error-message {
        background: linear-gradient(135deg, rgba(139, 0, 0, 0.9) 0%, rgba(100, 0, 0, 0.9) 100%);
        color: #f5e6d3;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(139, 0, 0, 0.4);
        border: 2px solid rgba(212, 165, 116, 0.3);
        animation: shake 0.5s ease-in-out;
        font-family: 'Georgia', serif;
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .back-home-btn {
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .login-container {
            padding: 20px 15px;
            max-width: 350px;
        }

        .back-home-btn {
            top: 15px;
            left: 15px;
            padding: 8px 16px;
            font-size: 12px;
            gap: 6px;
        }

        .login-logo {
            margin-bottom: 40px;
        }

        .login-logo img {
            width: 140px;
            height: 140px;
        }

        .form-input {
            padding: 16px 18px;
            font-size: 15px;
        }

        .login-button {
            padding: 16px;
            font-size: 16px;
        }
    }

    /* Hidden role input */
    .hidden-role {
        display: none;
    }
  </style>
</head>
<body>

<!-- Back to Home Button -->
<a href="<?= route('home') ?>" class="back-home-btn">
  <span>←</span>
  <span>Back to Home</span>
</a>

<div class="login-container">
  <!-- PCC Logo -->
  <div class="login-logo">
    <img src="<?= asset('assets/images/pcc-logo.png') ?>" alt="PCC Logo">
  </div>

  <!-- Error Message -->
  <?php if (!empty($error)): ?>
    <div class="error-message">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- Login Form -->
  <form class="login-form" method="post" action="<?= route('auth/login') ?>">
    <!-- CSRF Protection -->
    <?php csrf_field(); ?>

    <!-- Hidden role field -->
    <input type="hidden" name="role" value="<?= htmlspecialchars($role ?? '') ?>" class="hidden-role">
    
    <!-- Employee ID / Email Input -->
    <div class="form-group">
      <input 
        type="text" 
        id="identifier" 
        name="identifier" 
        class="form-input"
        placeholder="Employee ID or Email"
        required
        autocomplete="username"
      >
    </div>

    <!-- Password Input -->
    <div class="form-group password-group">
      <input 
        type="password" 
        id="password" 
        name="password" 
        class="form-input"
        placeholder="Password"
        required
        autocomplete="current-password"
      >
      <span class="password-toggle" onclick="togglePassword()"></span>
    </div>

    <!-- Login Button -->
    <button type="submit" class="login-button">
      Login
    </button>

    <!-- Forgot Password Link -->
    <div class="forgot-password">
      <a href="<?= route('auth/forgot-password') ?>">Forgot Password?</a>
    </div>
  </form>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.password-toggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.innerHTML = '';
    } else {
        passwordInput.type = 'password';
        toggleIcon.innerHTML = '';
    }
}

// Auto-focus on first input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('identifier').focus();
});
</script>

</body>
</html>