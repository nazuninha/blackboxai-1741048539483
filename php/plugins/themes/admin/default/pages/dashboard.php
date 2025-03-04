<?php
// Get database connection
$db_config = require __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../plugins/db/' . $db_config['type'] . '/connect.php';
$pdo = get_connection($db_config['config']);

// Fetch statistics
try {
    // Count total admins
    $admin_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Count total clients (if table exists)
    try {
        $client_count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    } catch (Exception $e) {
        $client_count = 0;
    }
    
    // Get last login date
    $last_login = $pdo->query("
        SELECT MAX(last_login) as last_login 
        FROM users
    ")->fetch()['last_login'];
    
} catch (Exception $e) {
    $error = "Erro ao carregar estatísticas: " . $e->getMessage();
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h2>Dashboard</h2>
        <p class="last-update">Última atualização: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="stats-grid">
        <!-- Admin Users Stat -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-title">Administradores</h3>
                <p class="stat-value"><?php echo number_format($admin_count); ?></p>
            </div>
        </div>

        <!-- Clients Stat -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-title">Clientes</h3>
                <p class="stat-value"><?php echo number_format($client_count); ?></p>
            </div>
        </div>

        <!-- Last Login Stat -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="stat-title">Último Login</h3>
                <p class="stat-value">
                    <?php echo $last_login ? date('d/m/Y H:i', strtotime($last_login)) : 'N/A'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>Ações Rápidas</h3>
        <div class="actions-grid">
            <a href="/admin?page=settings" class="action-card">
                <i class="fas fa-cog"></i>
                <span>Configurações</span>
            </a>
            <a href="/admin?page=users" class="action-card">
                <i class="fas fa-user-plus"></i>
                <span>Novo Usuário</span>
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
</div>

<style>
.dashboard {
    animation: fadeIn 0.5s ease-out;
}

.dashboard-header {
    margin-bottom: 2rem;
}

.dashboard-header h2 {
    font-size: 1.875rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.5rem;
}

.last-update {
    color: var(--secondary-color);
    font-size: 0.875rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-info {
    flex: 1;
}

.stat-title {
    font-size: 0.875rem;
    color: var(--secondary-color);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-color);
}

.quick-actions {
    margin-top: 2rem;
}

.quick-actions h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    text-decoration: none;
    color: var(--text-color);
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: var(--primary-color);
}

.action-card i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.action-card span {
    display: block;
    font-weight: 500;
}

@media (max-width: 768px) {
    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }

    .stat-value {
        font-size: 1.25rem;
    }
}
</style>