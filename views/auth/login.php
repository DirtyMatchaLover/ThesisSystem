<?php require_once __DIR__ . '/../../helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - PCC Thesis Hub</title>
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #500303; /* Same maroon as role selection */
        color: #fff;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    .login-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        max-width: 450px;
        padding: 40px 20px;
    }

    .login-logo {
        margin-bottom: 60px;
        animation: fadeInDown 0.8s ease-out;
    }

    .login-logo img {
        width: 180px;
        height: 180px;
        filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.3));
        transition: transform 0.3s ease;
    }

    .login-logo:hover img {
        transform: scale(1.05);
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
        border: none;
        border-radius: 8px;
        font-size: 16px;
        background: #fff;
        color: #333;
        outline: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .form-input:focus {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        border: 2px solid #F4A21D;
    }

    .form-input::placeholder {
        color: #999;
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
        color: #666;
        transition: color 0.3s ease;
        user-select: none;
    }

    .password-toggle:hover {
        color: #F4A21D;
    }

    .login-button {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #F4A21D 0%, #E8941A 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 6px 20px rgba(244, 162, 29, 0.3);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .login-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(244, 162, 29, 0.4);
        background: linear-gradient(135deg, #E8941A 0%, #D6851A 100%);
    }

    .login-button:active {
        transform: translateY(0px);
    }

    .forgot-password {
        text-align: center;
        margin-top: 25px;
    }

    .forgot-password a {
        color: #fff;
        text-decoration: none;
        font-size: 14px;
        opacity: 0.9;
        transition: all 0.3s ease;
    }

    .forgot-password a:hover {
        opacity: 1;
        text-decoration: underline;
        color: #F4A21D;
    }

    .error-message {
        background: rgba(244, 67, 54, 0.9);
        color: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
        animation: shake 0.5s ease-in-out;
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
    @media (max-width: 480px) {
        .login-container {
            padding: 20px 15px;
            max-width: 350px;
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
      <span class="password-toggle" onclick="togglePassword()">👁</span>
    </div>

    <!-- Login Button -->
    <button type="submit" class="login-button">
      Login
    </button>

    <!-- Forgot Password Link -->
    <div class="forgot-password">
      <a href="#">Forgot Password?</a>
    </div>
  </form>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.password-toggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.innerHTML = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleIcon.innerHTML = '👁';
    }
}

// Auto-focus on first input
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('identifier').focus();
});
</script>

</body>
</html>