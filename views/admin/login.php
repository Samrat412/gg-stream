<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/admin.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0F171E 0%, #1A242F 100%);
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }
        .login-card {
            background: #1A242F;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 1px solid #2A3A4A;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-logo svg {
            width: 64px;
            height: 64px;
        }
        .login-logo h1 {
            color: #FFFFFF;
            font-size: 24px;
            font-weight: 700;
            margin-top: 16px;
        }
        .login-logo p {
            color: #8D9BAA;
            font-size: 14px;
            margin-top: 8px;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group label {
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            background: #243040;
            border: 1px solid #2A3A4A;
            border-radius: 8px;
            padding: 12px 16px;
            color: #FFFFFF;
            font-size: 16px;
            transition: all 0.2s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #00A8E1;
            box-shadow: 0 0 0 3px rgba(0, 168, 225, 0.1);
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #8D9BAA;
            font-size: 14px;
            cursor: pointer;
        }
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #00A8E1;
        }
        .login-btn {
            background: #00A8E1;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }
        .login-btn:hover {
            background: #009FD1;
            transform: translateY(-1px);
        }
        .login-btn:active {
            transform: translateY(0);
        }
        .error-message {
            background: rgba(255, 77, 77, 0.1);
            border: 1px solid #FF4D4D;
            border-radius: 8px;
            padding: 12px 16px;
            color: #FF4D4D;
            font-size: 14px;
        }
        .login-footer {
            text-align: center;
            margin-top: 24px;
            color: #8D9BAA;
            font-size: 13px;
        }
        .login-footer a {
            color: #00A8E1;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <svg viewBox="0 0 64 64" fill="none">
                    <rect width="64" height="64" rx="16" fill="#00A8E1"/>
                    <path d="M20 20L44 20M20 32L44 32M20 44L36 44" stroke="white" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <h1>Admin Panel</h1>
                <p>Sign in to manage your site</p>
            </div>

            <?php if (isset($_SESSION['login_error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['login_error']) ?>
            </div>
            <?php 
                unset($_SESSION['login_error']);
            endif; ?>

            <form class="login-form" method="POST" action="/admin/login">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)) ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username" placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        Remember me for 30 days
                    </label>
                </div>

                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="login-footer">
                <a href="/">← Back to website</a>
            </div>
        </div>
    </div>
</body>
</html>
