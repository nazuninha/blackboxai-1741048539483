<?php
// Sidebar Menu for Admin Panel
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
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

<style>
.sidebar {
    width: 250px;
    background: #fff;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    position: fixed;
    height: 100vh;
    transition: all 0.3s ease;
}

.sidebar-header {
    padding: 20px;
    background: #007bff;
    color: white;
    text-align: center;
}

.sidebar-nav {
    padding: 20px;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
}

.sidebar-nav li {
    margin-bottom: 10px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 10px;
    color: #333;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
}

.sidebar-nav a:hover {
    background: #f0f0f0;
}

.sidebar-nav .active a {
    background: #007bff;
    color: white;
}

.sidebar-nav i {
    margin-right: 10px;
}
</style>