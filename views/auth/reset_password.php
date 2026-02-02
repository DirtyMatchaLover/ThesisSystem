<?php require_once __DIR__ . '/../../helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PCC Thesis Hub</title>
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
        }

        .reset-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 450px;
            padding: 40px 20px;
            background: linear-gradient(135deg, rgba(245, 241, 232, 0.1) 0%, rgba(212, 165, 116, 0.05) 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(212, 165, 116, 0.3);
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
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Georgia', serif;
        }

        .submit-button:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #8b6f47 0%, #6f5635 100%);
        }

        .message {
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message.success {
            background: linear-gradient(135deg, rgba(0, 128, 0, 0.9) 0%, rgba(0, 100, 0, 0.9) 100%);
            border: 2px solid rgba(212, 165, 116, 0.3);
        }

        .message.error {
            background: linear-gradient(135deg, rgba(139, 0, 0, 0.9) 0%, rgba(100, 0, 0, 0.9) 100%);
            border: 2px solid rgba(212, 165, 116, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #d4a574;
            text-decoration: none;
        }

        .back-link a:hover {
            color: #e6c080;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>Reset Your Password</h1>
        <p class="subtitle">Enter your new password below</p>

        <?php if (isset($success)): ?>
            <div class="message success">
                <?= htmlspecialchars($success) ?>
            </div>
            <div class="back-link">
                <a href="<?= route('auth/select') ?>">← Go to Login</a>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php if (!isset($token) || empty($token)): ?>
                <div class="back-link">
                    <a href="<?= route('auth/forgot-password') ?>">← Request New Reset Link</a>
                </div>
            <?php else: ?>
                <form method="POST" style="width: 100%;">
                    <?php csrf_field(); ?>

                    <div class="form-group">
                        <input
                            type="password"
                            name="password"
                            class="form-input"
                            placeholder="New Password (min. 8 characters)"
                            required
                            minlength="8"
                        >
                    </div>

                    <div class="form-group">
                        <input
                            type="password"
                            name="confirm_password"
                            class="form-input"
                            placeholder="Confirm New Password"
                            required
                            minlength="8"
                        >
                    </div>

                    <button type="submit" class="submit-button">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" style="width: 100%;">
                <?php csrf_field(); ?>

                <div class="form-group">
                    <input
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="New Password (min. 8 characters)"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>

                <div class="form-group">
                    <input
                        type="password"
                        name="confirm_password"
                        class="form-input"
                        placeholder="Confirm New Password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="submit-button">
                    Reset Password
                </button>
            </form>

            <div class="back-link">
                <a href="<?= route('auth/select') ?>">← Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
