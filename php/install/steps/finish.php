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
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-animation {
    margin: 20px auto;
    width: 100px;
    height: 100px;
    position: relative;
}

.checkmark {
    width: 100px;
    height: 100px;
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
    font-size: 2rem;
    color: var(--text-color);
    margin: 1.5rem 0;
    font-weight: 600;
}

.installation-summary,
.next-steps {
    background: var(--background-color);
    border-radius: 12px;
    padding: 2rem;
    margin: 2rem 0;
    text-align: left;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.installation-summary:hover,
.next-steps:hover {
    transform: translateY(-2px);
}

.installation-summary h3,
.next-steps h3 {
    color: var(--primary-color);
    margin-top: 0;
    font-size: 1.5rem;
}

.installation-summary ul,
.next-steps ul {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.installation-summary li,
.next-steps li {
    margin: 1rem 0;
    padding-left: 2rem;
    position: relative;
    font-size: 1.1rem;
}

.installation-summary li:before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--success-color);
    font-weight: bold;
}

.next-steps li:before {
    content: '→';
    position: absolute;
    left: 0;
    color: var(--primary-color);
    font-weight: bold;
}

.form-actions {
    margin-top: 3rem;
}

.btn-primary {
    display: inline-block;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: linear-gradient(45deg, var(--primary-color), var(--primary-hover));
    color: white;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
    .panel-name {
        font-size: 1.5rem;
    }

    .installation-summary,
    .next-steps {
        padding: 1.5rem;
    }

    .installation-summary li,
    .next-steps li {
        font-size: 1rem;
    }

    .btn-primary {
        width: 100%;
        text-align: center;
    }
}
</style>