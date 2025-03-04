<?php
// Check if already installed
if (file_exists(__DIR__ . '/../../config/installed.php')) {
    header('Location: /admin');
    exit;
}

// Check directory permissions
$required_dirs = [
    __DIR__ . '/../../config',
    __DIR__ . '/../../database',
    __DIR__ . '/../../uploads',
    __DIR__ . '/../../uploads/panel'
];

$permission_errors = [];
foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            $permission_errors[] = "Não foi possível criar o diretório: " . basename($dir);
        }
    } else if (!is_writable($dir)) {
        $permission_errors[] = "O diretório não tem permissão de escrita: " . basename($dir);
    }
}

// Check PHP version and extensions
$requirements = [
    'php' => [
        'version' => '7.4.0',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'pdo_sqlite' => extension_loaded('pdo_sqlite'),
        'gd' => extension_loaded('gd'),
        'json' => extension_loaded('json'),
        'curl' => extension_loaded('curl')
    ]
];

$missing_requirements = [];
if (!$requirements['php']['status']) {
    $missing_requirements[] = "PHP 7.4.0 ou superior é necessário. Versão atual: " . PHP_VERSION;
}

foreach ($requirements['extensions'] as $ext => $loaded) {
    if (!$loaded) {
        $missing_requirements[] = "A extensão PHP '$ext' é necessária.";
    }
}

// All checks passed?
$can_proceed = empty($permission_errors) && empty($missing_requirements);
?>

<div class="welcome-setup">
    <div class="welcome-header">
        <h1>Bem-vindo ao Painel Admin</h1>
        <p class="version">Versão Beta 0.0.1</p>
        <div class="author">Desenvolvido por Hiudy</div>
    </div>

    <?php if (!empty($permission_errors)): ?>
        <div class="alert alert-danger">
            <h3>Erros de Permissão</h3>
            <ul>
                <?php foreach ($permission_errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($missing_requirements)): ?>
        <div class="alert alert-danger">
            <h3>Requisitos não Atendidos</h3>
            <ul>
                <?php foreach ($missing_requirements as $req): ?>
                    <li><?php echo htmlspecialchars($req); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="requirements-check">
        <h3>Verificação do Sistema</h3>
        
        <div class="requirement-group">
            <h4>Versão do PHP</h4>
            <div class="requirement-item <?php echo $requirements['php']['status'] ? 'success' : 'error'; ?>">
                <span class="requirement-label">PHP <?php echo $requirements['php']['version']; ?> ou superior</span>
                <span class="requirement-status">
                    <?php echo $requirements['php']['status'] ? '✓' : '✗'; ?>
                </span>
                <span class="requirement-current">
                    Atual: <?php echo $requirements['php']['current']; ?>
                </span>
            </div>
        </div>

        <div class="requirement-group">
            <h4>Extensões PHP</h4>
            <?php foreach ($requirements['extensions'] as $ext => $loaded): ?>
                <div class="requirement-item <?php echo $loaded ? 'success' : 'error'; ?>">
                    <span class="requirement-label"><?php echo strtoupper($ext); ?></span>
                    <span class="requirement-status">
                        <?php echo $loaded ? '✓' : '✗'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="welcome-content">
        <p>Este assistente irá guiá-lo através do processo de instalação do painel administrativo.</p>
        <p>Durante a instalação, você poderá:</p>
        <ul>
            <li>Configurar o banco de dados</li>
            <li>Criar o usuário administrador</li>
            <li>Personalizar as informações do painel</li>
        </ul>
        
        <?php if ($can_proceed): ?>
            <div class="notice notice-info">
                <p>Todos os requisitos foram atendidos. Você pode prosseguir com a instalação.</p>
            </div>
            <div class="form-actions">
                <a href="?step=database" class="btn-primary">Iniciar Instalação</a>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <p>Por favor, corrija os problemas acima antes de prosseguir com a instalação.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.welcome-setup {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.welcome-header {
    text-align: center;
    margin-bottom: 3rem;
}

.welcome-header h1 {
    font-size: 2.5rem;
    color: #2d3748;
    margin: 0 0 0.5rem;
}

.version {
    font-size: 1rem;
    color: #718096;
    margin: 0;
}

.author {
    font-size: 0.875rem;
    color: #a0aec0;
    margin-top: 0.5rem;
}

.alert {
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background-color: #fff5f5;
    border: 1px solid #feb2b2;
    color: #c53030;
}

.alert h3 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

.requirements-check {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.requirements-check h3 {
    margin: 0 0 1.5rem;
    color: #2d3748;
}

.requirement-group {
    margin-bottom: 1.5rem;
}

.requirement-group h4 {
    margin: 0 0 1rem;
    color: #4a5568;
}

.requirement-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    background: #f7fafc;
}

.requirement-item.success {
    background: #f0fff4;
}

.requirement-item.error {
    background: #fff5f5;
}

.requirement-label {
    flex: 1;
    font-weight: 500;
}

.requirement-status {
    margin: 0 1rem;
}

.requirement-current {
    color: #718096;
    font-size: 0.875rem;
}

.success .requirement-status {
    color: #48bb78;
}

.error .requirement-status {
    color: #f56565;
}

.welcome-content {
    background: #fff;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-top: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.welcome-content p {
    margin: 0 0 1rem;
    color: #4a5568;
    line-height: 1.5;
}

.welcome-content ul {
    margin: 0 0 1.5rem;
    padding-left: 1.5rem;
    color: #4a5568;
}

.welcome-content li {
    margin-bottom: 0.5rem;
}

.notice {
    padding: 1rem;
    border-radius: 0.375rem;
    margin: 1.5rem 0;
}

.notice-info {
    background: #ebf8ff;
    border: 1px solid #90cdf4;
    color: #2b6cb0;
}

.notice-warning {
    background: #fffaf0;
    border: 1px solid #fbd38d;
    color: #c05621;
}

.form-actions {
    margin-top: 2rem;
    text-align: center;
}

.btn-primary {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #4299e1;
    color: white;
    text-decoration: none;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #3182ce;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .welcome-setup {
        padding: 1rem;
    }

    .welcome-header h1 {
        font-size: 2rem;
    }

    .requirement-item {
        flex-direction: column;
        text-align: center;
    }

    .requirement-status {
        margin: 0.5rem 0;
    }
}
</style>