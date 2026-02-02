<?php require_once __DIR__ . '/../../helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PCC Thesis Hub</title>
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

        .reset-container {
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

        .reset-logo {
            margin-bottom: 30px;
        }

        .reset-logo img {
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.5));
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #d4a574;
        }

        .subtitle {
            font-size: 14px;
            color: #c9955f;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.6;
        }

        .form-group {
            width: 100%;
            margin-bottom: 20px;
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
            box-shadow: 0 8px 25px rgba(123, 63, 0, 0.4);
            border: 2px solid #c9955f;
            background: #fff;
        }

        .submit-button {
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

        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(123, 63, 0, 0.6);
            background: linear-gradient(135deg, #8b6f47 0%, #6f5635 100%);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #d4a574;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #e6c080;
            text-decoration: underline;
        }

        .message {
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .message.success {
            background: linear-gradient(135deg, rgba(0, 128, 0, 0.9) 0%, rgba(0, 100, 0, 0.9) 100%);
            border: 2px solid rgba(212, 165, 116, 0.3);
        }

        .message.error {
            background: linear-gradient(135deg, rgba(139, 0, 0, 0.9) 0%, rgba(100, 0, 0, 0.9) 100%);
            border: 2px solid rgba(212, 165, 116, 0.3);
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-logo">
            <img src="<?= asset('assets/images/pcc-logo.png') ?>" alt="PCC Logo">
        </div>

        <h1>Forgot Password?</h1>
        <p class="subtitle">
            Enter your email address and we'll send you instructions to reset your password.
        </p>

        <?php if (isset($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= route('auth/forgot-password') ?>" style="width: 100%;">
            <?php csrf_field(); ?>

            <div class="form-group">
                <input
                    type="email"
                    name="email"
                    class="form-input"
                    placeholder="Enter your email address"
                    required
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <button type="submit" class="submit-button">
                Send Reset Instructions
            </button>
        </form>

        <div class="back-link">
            <a href="<?= route('auth/select') ?>">← Back to Login</a>
        </div>
    </div>
</body>
</html>
