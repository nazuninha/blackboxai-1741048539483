<?php
// Check if installation is complete
if (!file_exists(__DIR__ . '/../../config/installed.php')) {
    header('Location: ?step=welcome');
    exit;
}

// Load panel settings
try {
    $db_config = require __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../plugins/db/' . $db_config['type'] . '/connect.php';
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
    $error = "Erro ao carregar configurações: " . $e->getMessage();
}
?>

<div class="finish-setup">
    <div class="success-animation">
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
    </div>

    <h2>Instalação Concluída!</h2>
    
    <?php if (isset($settings['panel_name'])): ?>
        <p class="panel-name">
            <?php echo htmlspecialchars($settings['panel_name']); ?>
        </p>
    <?php endif; ?>

    <div class="installation-summary">
        <h3>Resumo da Instalação</h3>
        <ul>
            <li>✓ Banco de dados configurado</li>
            <li>✓ Usuário administrador criado</li>
            <li>✓ Informações do painel salvas</li>
        </ul>
    </div>

    <div class="next-steps">
        <h3>Próximos Passos</h3>
        <p>Você pode agora:</p>
        <ul>
            <li>Acessar o painel administrativo</li>
            <li>Personalizar seu painel</li>
            <li>Começar a adicionar funcionalidades</li>
        </ul>
    </div>

    <div class="form-actions">
        <a href="/" class="btn-primary">Ir para o Painel</a>
    </div>
</div>

<style>
.finish-setup {
    text-align: center;
    padding: 20px;
}

.success-animation {
    margin: 20px auto;
    width: 80px;
    height: 80px;
}

.checkmark {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #4bb71b;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #4bb71b;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}

.checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #4bb71b;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {
    0%, 100% {
        transform: none;
    }
    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 30px #4bb71b;
    }
}

.panel-name {
    font-size: 24px;
    color: #333;
    margin: 20px 0;
}

.installation-summary,
.next-steps {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
}

.installation-summary ul,
.next-steps ul {
    list-style: none;
    padding: 0;
}

.installation-summary li,
.next-steps li {
    margin: 10px 0;
    padding-left: 25px;
    position: relative;
}

.installation-summary li:before {
    content: '✓';
    position: absolute;
    left: 0;
    color: #4bb71b;
}

.next-steps li:before {
    content: '→';
    position: absolute;
    left: 0;
    color: #007bff;
}

.form-actions {
    margin-top: 30px;
}

.btn-primary {
    display: inline-block;
    padding: 12px 30px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #0056b3;
}
</style>