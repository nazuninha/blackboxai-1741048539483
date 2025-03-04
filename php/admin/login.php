<?php
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/input.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/config.php';

// Initialize authentication
$auth = Auth::getInstance();

// Check if user is already logged in
if ($auth->isAuthenticated()) {
    Response::redirect('/admin');
}

// Handle login form submission
if (Input::isPost()) {
    $email = Input::post('email', '', [
        'required' => true,
        'type' => 'email'
    ]);
    
    $password = Input::post('password', '', [
        'required' => true,
        'type' => 'string',
        'min' => 8
    ]);

    $result = $auth->login($email, $password);

    if ($result['success']) {
        Response::redirect('/admin');
    }

    $error = $result['error']['message'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --background-color: #f8fafc;
            --text-color: #1e293b;
            --error-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            animation: fadeIn 0.5s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.875rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--secondary-color);
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 4px;
            background: rgba(239,68,68,0.1);
            animation: shake 0.5s ease-in-out;
        }

        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
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

        @media (prefers-color-scheme: dark) {
            :root {
                --background-color: #0f172a;
                --text-color: #f1f5f9;
            }

            body {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            }

            .login-container {
                background: #1e293b;
            }

            .form-control {
                background: #0f172a;
                border-color: #334155;
                color: var(--text-color);
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Panel</h1>
            <p>Faça login para acessar o painel</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">E-mail</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars(Input::post('email', '')); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos.');
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('A senha deve ter no mínimo 8 caracteres.');
            return;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Por favor, insira um e-mail válido.');
            return;
        }
    });
    </script>
</body>
</html>