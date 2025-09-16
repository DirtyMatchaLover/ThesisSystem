<?php require_once __DIR__ . '/../../helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Select Your Role - PCC Thesis Hub</title>
  <style>
    /* ----------------------------
       Role Select Split Screen - Updated
    ----------------------------- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    .login-split {
        display: flex;
        height: 100vh;
        margin-top: 0;
    }

    .login-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        color: #fff;
        transition: all 0.4s ease;
        padding: 40px 30px 0 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    /* Individual panel styling with specific colors */
    .login-panel.admin { 
        background: #500303 !important; /* Dark burgundy for admin */
    }

    .login-panel.faculty { 
        background: #711d1d !important; /* Medium burgundy for faculty */
    }

    .login-panel.student { 
        background: #500303 !important; /* Dark burgundy for student */
    }

    /* Hover effects */
    .login-panel.admin:hover { 
        background: #711d1d !important; /* Lightens to faculty color on hover */
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .login-panel.faculty:hover { 
        background: #8B2635 !important; /* Lightens to lighter maroon on hover */
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .login-panel.student:hover { 
        background: #711d1d !important; /* Lightens to faculty color on hover */
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    /* Role labels - positioned at top */
    .login-panel h2 {
        font-size: 2.8rem;
        font-weight: 300;
        letter-spacing: 1px;
        margin-bottom: 0;
        text-transform: none; /* Keep original casing */
        opacity: 1;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        flex-shrink: 0;
        margin-top: 20px;
    }

    .login-panel:hover h2 {
        transform: translateY(-5px);
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
    }

    /* Character illustrations - positioned at bottom */
    .login-panel img {
        width: 320px;
        max-width: 90%;
        height: auto;
        margin-top: auto;
        margin-bottom: 0;
        transition: all 0.4s ease;
        filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
        align-self: center;
    }

    .login-panel:hover img {
        transform: translateY(-15px) scale(1.05);
        filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.4));
    }

    /* Links covering entire panel */
    .login-panel a {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        text-decoration: none;
        color: inherit;
        z-index: 2;
        padding: 40px 30px 0 30px;
    }

    /* Subtle animations */
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }

    .login-panel img {
        animation: float 8s ease-in-out infinite;
    }

    .login-panel.admin img { 
        animation-delay: 0s; 
    }

    .login-panel.faculty img { 
        animation-delay: 2.5s; 
    }

    .login-panel.student img { 
        animation-delay: 5s; 
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .login-split {
            flex-direction: column;
            height: auto;
            min-height: 100vh;
        }
        
        .login-panel {
            height: 33.33vh;
            min-height: 350px;
            padding: 30px 20px 0 20px;
        }
        
        .login-panel h2 {
            font-size: 2.2rem;
            margin-top: 15px;
        }
        
        .login-panel img {
            width: 250px;
            margin-bottom: 0;
        }
        
        .login-panel a {
            padding: 30px 20px 0 20px;
        }
    }

    @media (max-width: 480px) {
        .login-panel {
            min-height: 300px;
        }
        
        .login-panel h2 {
            font-size: 1.8rem;
        }
        
        .login-panel img {
            width: 200px;
        }
    }
  </style>
</head>
<body>

<div class="login-split">
  <!-- Admin Panel -->
  <div class="login-panel admin">
    <a href="<?= route('auth/login&role=admin') ?>">
      <h2>Admin</h2>
      <img src="<?= asset('assets/images/admin.png') ?>" alt="Admin Character">
    </a>
  </div>

  <!-- Faculty Panel -->
  <div class="login-panel faculty">
    <a href="<?= route('auth/login&role=faculty') ?>">
      <h2>Faculty</h2>
      <img src="<?= asset('assets/images/faculty.png') ?>" alt="Faculty Character">
    </a>
  </div>

  <!-- Student Panel -->
  <div class="login-panel student">
    <a href="<?= route('auth/login&role=student') ?>">
      <h2>Student</h2>
      <img src="<?= asset('assets/images/student.png') ?>" alt="Student Character">
    </a>
  </div>
</div>

</body>
</html>