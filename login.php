<?php
require_once 'config.php';

// Wenn bereits eingeloggt, zur Admin-Seite
if (isLoggedIn()) {
    header('Location: admin-server.php');
    exit;
}

$error = '';

// Login-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if ($username === $ADMIN_USERNAME && password_verify($password, $ADMIN_PASSWORD_HASH)) {
        // Login erfolgreich
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        // Remember Me Cookie setzen
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['remember_token'] = $token;
            setcookie('remember_me', $token, time() + REMEMBER_ME_DURATION, '/');
        }
        
        header('Location: admin-server.php');
        exit;
    } else {
        $error = 'Falscher Benutzername oder Passwort';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - BÜFA Composite Systems</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #003d7a 0%, #002d5c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 0;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
            padding: 50px 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            color: #003d7a;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .logo p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 0;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #003d7a;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me input[type="checkbox"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .remember-me label {
            margin: 0;
            font-weight: 400;
            cursor: pointer;
            color: #666;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: #003d7a;
            color: white;
            border: none;
            border-radius: 0;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-btn:hover {
            background: #002d5c;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 0;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 0.95rem;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 0.85rem;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            .logo h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>BÜFA Composite Systems</h1>
            <p>Admin Login</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Angemeldet bleiben (7 Tage)</label>
            </div>

            <button type="submit" class="login-btn">
                🔐 Anmelden
            </button>
        </form>

        <div class="footer">
            &copy; 2026 BÜFA Composite Systems GmbH & Co. KG
        </div>
    </div>
</body>
</html>
