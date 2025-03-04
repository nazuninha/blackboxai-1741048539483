<?php
// Get available database plugins
$db_plugins = [];
$plugins_dir = __DIR__ . '/../../plugins/db/';

if (is_dir($plugins_dir)) {
    foreach (scandir($plugins_dir) as $file) {
        if ($file !== '.' && $file !== '..' && is_dir($plugins_dir . $file)) {
            // Load plugin info from config.json if exists
            $config_file = $plugins_dir . $file . '/config.json';
            if (file_exists($config_file)) {
                $config = json_decode(file_get_contents($config_file), true);
                if ($config) {
                    $db_plugins[$file] = $config;
                }
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_db = $_POST['database_type'] ?? '';
    $db_config = $_POST['db_config'] ?? [];
    
    if (!empty($selected_db) && !empty($db_config)) {
        // Test database connection
        require_once $plugins_dir . $selected_db . '/connect.php';
        
        try {
            $connection = test_database_connection($db_config);
            
            if ($connection) {
                // Save configuration
                if (!is_dir(__DIR__ . '/../../config')) {
                    mkdir(__DIR__ . '/../../config', 0755, true);
                }
                
                $config = [
                    'type' => $selected_db,
                    'config' => $db_config
                ];
                
                file_put_contents(
                    __DIR__ . '/../../config/database.php',
                    '<?php return ' . var_export($config, true) . ';'
                );
                
                // Redirect to next step
                header('Location: ?step=admin');
                exit;
            }
        } catch (Exception $e) {
            $error = "Erro ao conectar ao banco de dados: " . $e->getMessage();
        }
    }
}
?>

<div class="database-setup">
    <h2>Configuração do Banco de Dados</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="dbForm">
        <div class="database-options">
            <?php foreach ($db_plugins as $type => $info): ?>
                <div class="database-option" data-type="<?php echo htmlspecialchars($type); ?>">
                    <h3><?php echo htmlspecialchars($info['name']); ?></h3>
                    <p><?php echo htmlspecialchars($info['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <input type="hidden" name="database_type" id="selected_db">

        <div id="db_config_forms" class="mt-4">
            <!-- SQL Server Form -->
            <div class="db-form" id="mysql_form" style="display: none;">
                <div class="form-group">
                    <label for="host">Host:</label>
                    <input type="text" name="db_config[host]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="port">Porta:</label>
                    <input type="text" name="db_config[port]" class="form-control" value="3306">
                </div>
                <div class="form-group">
                    <label for="database">Nome do Banco:</label>
                    <input type="text" name="db_config[database]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="username">Usuário:</label>
                    <input type="text" name="db_config[username]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" name="db_config[password]" class="form-control">
                </div>
            </div>

            <!-- SQLite Form -->
            <div class="db-form" id="sqlite_form" style="display: none;">
                <div class="form-group">
                    <label for="database">Nome do Arquivo do Banco:</label>
                    <input type="text" name="db_config[database]" class="form-control" required>
                    <small class="form-text text-muted">O arquivo será criado na pasta /database do sistema</small>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="?step=welcome" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn-primary">Continuar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dbOptions = document.querySelectorAll('.database-option');
    const dbForms = document.querySelectorAll('.db-form');
    const selectedDbInput = document.getElementById('selected_db');

    dbOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            dbOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Update hidden input
            const dbType = this.dataset.type;
            selectedDbInput.value = dbType;
            
            // Show appropriate form
            dbForms.forEach(form => form.style.display = 'none');
            const formId = dbType + '_form';
            const form = document.getElementById(formId);
            if (form) {
                form.style.display = 'block';
            }
        });
    });
});
</script>