<?php
// Get statistics
try {
    $stats = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'last_login' => $pdo->query("SELECT MAX(last_login) FROM users")->fetchColumn()
    ];
} catch (Exception $e) {
    $stats = [
        'users' => 0,
        'last_login' => null
    ];
}
?>

<div class="dashboard">
    <div class="stats-grid">
        <!-- Users Card -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Usuários</h3>
                <p class="stat-value"><?php echo number_format($stats['users']); ?></p>
            </div>
        </div>

        <!-- Last Login Card -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>Último Login</h3>
                <p class="stat-value">
                    <?php 
                    if ($stats['last_login']) {
                        $date = new DateTime($stats['last_login']);
                        echo $date->format('d/m/Y H:i');
                    } else {
                        echo 'Nenhum';
                    }
                    ?>
                </p>
            </div>
        </div>

        <!-- System Info Card -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-info">
                <h3>Sistema</h3>
                <p class="stat-value">v1.0.0</p>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Activity -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Atividade Recente</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <!-- Activity items will be dynamically added here -->
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-details">
                            <p>Novo usuário registrado</p>
                            <small>Há 2 horas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Ações Rápidas</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="/admin?page=users" class="quick-action-btn">
                        <i class="fas fa-user-plus"></i>
                        Novo Usuário
                    </a>
                    <a href="/admin?page=settings" class="quick-action-btn">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                    <a href="/admin?page=store" class="quick-action-btn">
                        <i class="fas fa-store"></i>
                        Gerenciar Loja
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.stat-card {
    background: var(--white);
    padding: 1.5rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info h3 {
    font-size: 0.875rem;
    color: var(--text-color);
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.dashboard-card {
    background: var(--white);
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    padding: 1.5rem;
    background: var(--secondary-color);
    color: white;
}

.card-header h3 {
    font-size: 1.1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--background-color);
    border-radius: 0.5rem;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.activity-details p {
    margin: 0;
    font-weight: 500;
}

.activity-details small {
    color: var(--text-color);
    opacity: 0.7;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: var(--background-color);
    border-radius: 0.5rem;
    color: var(--text-color);
    text-decoration: none;
    transition: all 0.3s ease;
    gap: 0.5rem;
}

.quick-action-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: 1.5rem;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .stat-card,
    .dashboard-card {
        background: var(--secondary-color);
    }

    .activity-item {
        background: rgba(255, 255, 255, 0.1);
    }

    .quick-action-btn {
        background: rgba(255, 255, 255, 0.1);
        color: var(--white);
    }

    .quick-action-btn:hover {
        background: var(--primary-color);
    }

    .stat-info h3,
    .activity-details small {
        color: var(--white);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Update activity times
function updateActivityTimes() {
    const activities = document.querySelectorAll('.activity-details small');
    activities.forEach(activity => {
        const time = activity.getAttribute('data-time');
        if (time) {
            activity.textContent = timeAgo(new Date(time));
        }
    });
}

// Time ago function
function timeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + ' anos atrás';
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + ' meses atrás';
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + ' dias atrás';
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + ' horas atrás';
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + ' minutos atrás';
    
    return Math.floor(seconds) + ' segundos atrás';
}

// Update times every minute
updateActivityTimes();
setInterval(updateActivityTimes, 60000);
</script>