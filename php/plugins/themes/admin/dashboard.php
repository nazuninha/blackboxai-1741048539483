<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php');
    exit;
}

// Load panel settings
$db_config = require __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../plugins/db/' . $db_config['type'] . '/connect.php';
$pdo = get_connection($db_config['config']);

// Fetch statistics
$admin_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$client_count = 0; // Placeholder for client count, implement as needed

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="admin-panel">
        <header>
            <div class="logo">
                <img src="/uploads/panel/<?php echo htmlspecialchars($settings['panel_logo']); ?>" alt="Logo">
            </div>
            <h1><?php echo htmlspecialchars($settings['panel_name']); ?></h1>
            <h2>Admin Panel</h2>
            <button class="menu-toggle">☰</button>
        </header>
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a href="?step=settings">Configurações</a></li>
                </ul>
            </nav>
        </aside>
        <main>
            <h2>Dashboard</h2>
            <div class="summary">
                <div class="stat">
                    <h3>Admins Cadastrados</h3>
                    <p><?php echo $admin_count; ?></p>
                </div>
                <div class="stat">
                    <h3>Clientes Cadastrados</h3>
                    <p><?php echo $client_count; ?></p>
                </div>
                <!-- Add more statistics as needed -->
            </div>
        </main>
    </div>
    <script src="/assets/js/admin.js"></script>
</body>
</html>