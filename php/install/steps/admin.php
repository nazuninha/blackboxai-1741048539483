<?php
// Check if database is configured
if (!file_exists(__DIR__ . '/../../config/database.php')) {
    header('Location: ?step=database');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "O nome é obrigatório.";
    }
    
    if (empty($email)) {
        $errors[] = "O e-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "E-mail inválido.";
    }
    
    if (empty($password)) {
        $errors[] = "A senha é obrigatória.";
    } elseif (strlen($password) < 8) {
        $errors[] = "A senha deve ter no mínimo 8 caracteres.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "As senhas não conferem.";
    }
    
    if (empty($errors)) {
        try {
            // Load database configuration
            $db_config = require __DIR__ . '/../../config/database.php';
            
            // Include database connection
            require_once __DIR__ . '/../../plugins/db/' . $db_config['type'] . '/connect.php';
            
            // Get database connection
            $pdo = get_connection($db_config['config']);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin user
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");
            
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashed_password
            ]);
            
            // Redirect to next step
            header('Location: ?step=store');
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Erro ao criar usuário administrador: " . $e->getMessage();
        }
    }
}
?>

<div class="admin-setup">
    <h2>Configuração do Usuário Administrador</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" class="form-control" 
                   value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" class="form-control" required>
            <small class="form-text text-muted">A senha deve ter no mínimo 8 caracteres.</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar Senha:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>

        <div class="form-actions">
            <a href="?step=database" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn-primary">Continuar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('As senhas não conferem!');
        }
    });
});
</script>