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

// Add JSON local database option
$db_plugins['json_local'] = [
    'name' => 'JSON Local',
    'description' => 'Banco de dados local em formato JSON (não seguro, para testes)',
];

// Add Firebase option
$db_plugins['firebase'] = [
    'name' => 'Firebase',
    'description' => 'Banco de dados em tempo real do Firebase',
];

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

            <!-- JSON Local Form -->
            <div class="db-form" id="json_local_form" style="display: none;">
                <div class="form-group">
                    <label for="database">Nome do Arquivo JSON:</label>
                    <input type="text" name="db_config[database]" class="form-control" required>
                    <small class="form-text text-muted">O arquivo será criado na pasta /database do sistema</small>
                </div>
            </div>

            <!-- Firebase Form -->
            <div class="db-form" id="firebase_form" style="display: none;">
                <div class="form-group">
                    <label for="firebase_url">URL do Firebase:</label>
                    <input type="text" name="db_config[firebase_url]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="firebase_key">Chave do Firebase:</label>
                    <input type="text" name="db_config[firebase_key]" class="form-control" required>
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

<style>
.database-setup {
    text-align: center;
    padding: 20px;
}

.alert {
    color: red;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
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

.btn-secondary {
    display: inline-block;
    padding: 12px 30px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    transition: background 0.3s;
}

.btn-secondary:hover {
    background: #5a6268;
}

.database-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.database-option {
    border: 2px solid #ddd;
    border-radius: 0.75rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f9f9f9;
}

.database-option:hover {
    border-color: #007bff;
    transform: translateY(-2px);
}

.database-option.selected {
    border-color: #007bff;
    background: #e0f7ff;
}

.database-option h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.database-option p {
    margin: 0;
    color: #555;
    font-size: 0.875rem;
}
</style>