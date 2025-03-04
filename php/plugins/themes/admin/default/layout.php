<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/plugins/themes/default/assets/css/style.css">
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        <a href="/admin">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo $page === 'settings' ? 'active' : ''; ?>">
                        <a href="/admin?page=settings">
                            <i class="fas fa-cog"></i>
                            <span>Configurações</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="main-header">
                <div class="header-content">
                    <div class="panel-info">
                        <h1 class="panel-name">
                            <?php echo htmlspecialchars($settings['panel_name'] ?? 'Admin Panel'); ?>
                        </h1>
                        <span class="panel-type">Admin Panel</span>
                    </div>
                    <div class="panel-logo">
                        <?php if (isset($settings['panel_logo'])): ?>
                            <img src="/uploads/panel/<?php echo htmlspecialchars($settings['panel_logo']); ?>" 
                                 alt="Logo" class="logo-img">
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="content">
                <?php
                $page_file = __DIR__ . '/pages/' . $page . '.php';
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    echo '<div class="error">Página não encontrada</div>';
                }
                ?>
            </div>
        </main>
    </div>

    <script src="/plugins/themes/default/assets/js/script.js"></script>
</body>
</html>