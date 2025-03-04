<?php
// Sidebar Menu for Admin Panel Default Theme
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <?php if (isset($settings['panel_name'])): ?>
            <h2><?php echo htmlspecialchars($settings['panel_name']); ?></h2>
        <?php else: ?>
            <h2>Admin Panel</h2>
        <?php endif; ?>
        
        <div class="current-time" id="currentTime"></div>
        
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                <a href="/admin?page=dashboard">
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
            <li class="<?php echo $page === 'users' ? 'active' : ''; ?>">
                <a href="/admin?page=users">
                    <i class="fas fa-users"></i>
                    <span>Usuários</span>
                </a>
            </li>
            <li class="<?php echo $page === 'store' ? 'active' : ''; ?>">
                <a href="/admin?page=store">
                    <i class="fas fa-store"></i>
                    <span>Loja</span>
                </a>
            </li>
            <li class="<?php echo $page === 'logout' ? 'active' : ''; ?>">
                <a href="/admin/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<script>
// Update time based on user's timezone
function updateTime() {
    const now = new Date();
    const options = {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZoneName: 'short'
    };
    document.getElementById('currentTime').textContent = now.toLocaleTimeString(undefined, options);
}

// Update time every second
updateTime();
setInterval(updateTime, 1000);

// Sidebar toggle functionality
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.body.classList.toggle('sidebar-collapsed');
});
</script>

<style>
.sidebar {
    width: 280px;
    background: #2c3e50;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    position: fixed;
    height: 100vh;
    transition: all 0.3s ease;
    z-index: 1000;
    color: #ecf0f1;
}

.sidebar-collapsed .sidebar {
    width: 70px;
}

.sidebar-collapsed .sidebar span,
.sidebar-collapsed .sidebar .current-time,
.sidebar-collapsed .sidebar h2 {
    display: none;
}

.sidebar-header {
    padding: 20px;
    background: #34495e;
    border-bottom: 1px solid #465c71;
}

.sidebar-header h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 500;
}

.current-time {
    font-size: 0.9rem;
    margin-top: 10px;
    color: #bdc3c7;
}

.sidebar-nav {
    padding: 20px 0;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin: 5px 0;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.sidebar-nav a:hover {
    background: #34495e;
    border-left-color: #3498db;
}

.sidebar-nav .active a {
    background: #34495e;
    border-left-color: #3498db;
    color: #3498db;
}

.sidebar-nav i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}

.sidebar-toggle {
    position: absolute;
    top: 20px;
    right: 20px;
    background: transparent;
    border: none;
    color: #ecf0f1;
    cursor: pointer;
    padding: 0;
    font-size: 1.2rem;
}

.sidebar-toggle:hover {
    color: #3498db;
}

/* Main content adjustment */
.main-content {
    margin-left: 280px;
    padding: 20px;
    transition: all 0.3s ease;
}

.sidebar-collapsed .main-content {
    margin-left: 70px;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar-collapsed .sidebar {
        transform: translateX(0);
        width: 280px;
    }
    
    .sidebar-collapsed .sidebar span,
    .sidebar-collapsed .sidebar .current-time,
    .sidebar-collapsed .sidebar h2 {
        display: block;
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .sidebar-collapsed .main-content {
        margin-left: 0;
    }
}
</style>