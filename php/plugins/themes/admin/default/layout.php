<?php
// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Load settings
try {
    $db_config = require __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../plugins/db/' . $db_config['type'] . '/connect.php';
    $pdo = get_connection($db_config['config']);
    
    $stmt = $pdo->query("
        SELECT setting_key, setting_value 
        FROM store_settings 
        WHERE setting_key IN ('panel_name', 'panel_logo')
    ");
    
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Handle error silently
    $settings = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['panel_name'] ?? 'Admin Panel'); ?></title>
    
    <!-- Favicon -->
    <?php if (isset($settings['panel_logo'])): ?>
        <link rel="icon" type="image/png" href="/uploads/panel/<?php echo htmlspecialchars($settings['panel_logo']); ?>">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Theme Styles -->
    <link rel="stylesheet" href="/plugins/themes/admin/default/assets/css/style.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-header">
            <div class="header-left">
                <h1 class="page-title">
                    <?php
                    switch ($page) {
                        case 'dashboard':
                            echo 'Dashboard';
                            break;
                        case 'settings':
                            echo 'Configurações';
                            break;
                        case 'users':
                            echo 'Usuários';
                            break;
                        case 'store':
                            echo 'Loja';
                            break;
                        default:
                            echo ucfirst($page);
                    }
                    ?>
                </h1>
            </div>
            <div class="header-right">
                <div class="user-menu">
                    <img src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($_SESSION['user_email'] ?? ''))); ?>?d=mp" 
                         alt="User Avatar" 
                         class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                </div>
            </div>
        </header>

        <main class="content">
            <?php
            $page_file = __DIR__ . '/pages/' . $page . '.php';
            if (file_exists($page_file)) {
                require_once $page_file;
            } else {
                echo '<div class="error-message">Página não encontrada.</div>';
            }
            ?>
        </main>
    </div>

    <!-- Custom Theme Scripts -->
    <script src="/plugins/themes/admin/default/assets/js/script.js"></script>
</body>
</html>

<style>
:root {
    --primary-color: #3498db;
    --primary-dark: #2980b9;
    --secondary-color: #2c3e50;
    --background-color: #f5f6fa;
    --text-color: #2c3e50;
    --border-color: #dcdde1;
    --success-color: #2ecc71;
    --warning-color: #f1c40f;
    --danger-color: #e74c3c;
    --white: #ffffff;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.6;
    min-height: 100vh;
}

.top-header {
    background: var(--white);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    border-radius: 0.5rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--secondary-color);
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 1rem;
    background: var(--background-color);
    border-radius: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-menu:hover {
    background: var(--border-color);
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    font-weight: 500;
    color: var(--secondary-color);
}

.content {
    background: var(--white);
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-height: calc(100vh - 180px);
}

.error-message {
    padding: 1rem;
    background-color: #fff3f3;
    border: 1px solid #ffa7a7;
    border-radius: 0.5rem;
    color: var(--danger-color);
}

/* Responsive Design */
@media (max-width: 768px) {
    .top-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .content {
        padding: 1rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --background-color: #1a1a1a;
        --text-color: #ffffff;
        --border-color: #2c2c2c;
        --white: #2c2c2c;
    }

    .top-header {
        background: var(--secondary-color);
    }

    .page-title {
        color: var(--white);
    }

    .user-menu {
        background: var(--background-color);
    }

    .user-name {
        color: var(--white);
    }

    .content {
        background: var(--secondary-color);
    }
}
</style>