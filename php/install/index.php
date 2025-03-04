<?php
session_start();

// Installation steps
$steps = [
    'welcome' => 'Boas-vindas',
    'database' => 'Configuração do Banco de Dados',
    'admin' => 'Usuário Administrador',
    'store' => 'Informações da Loja',
    'finish' => 'Conclusão'
];

// Get current step
$current_step = isset($_GET['step']) ? $_GET['step'] : 'welcome';

// If installation is already complete, redirect to home
if (file_exists('../config/installed.php') && $current_step !== 'finish') {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do Sistema</title>
    <link rel="stylesheet" href="/assets/css/install.css">
</head>
<body>
    <div class="install-container">
        <!-- Progress bar -->
        <div class="progress-bar">
            <?php
            $step_count = 0;
            foreach ($steps as $step => $label) {
                $is_active = ($step === $current_step) ? 'active' : '';
                $is_complete = array_search($current_step, array_keys($steps)) > array_search($step, array_keys($steps)) ? 'complete' : '';
                echo "<div class='step {$is_active} {$is_complete}'>{$label}</div>";
            }
            ?>
        </div>

        <!-- Content area -->
        <div class="content">
            <?php
            switch ($current_step) {
                case 'welcome':
                    include 'steps/welcome.php';
                    break;
                case 'database':
                    include 'steps/database.php';
                    break;
                case 'admin':
                    include 'steps/admin.php';
                    break;
                case 'store':
                    include 'steps/store.php';
                    break;
                case 'finish':
                    include 'steps/finish.php';
                    break;
            }
            ?>
        </div>
    </div>
    <script src="/assets/js/install.js"></script>
</body>
</html>