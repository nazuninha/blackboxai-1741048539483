<?php
require_once __DIR__ . '/../../core/error_handler.php';
require_once __DIR__ . '/../../core/input.php';
require_once __DIR__ . '/../../core/response.php';

// Check if database and admin are configured
if (!file_exists(__DIR__ . '/../../config/database.php')) {
    header('Location: ?step=database');
    exit;
}

// Handle form submission
if (Input::isPost()) {
    $panel_name = Input::post('panel_name', '', ['required' => true]);
    $errors = [];
    
    // Handle logo upload
    $logo = Input::file('logo', [
        'types' => ['image/jpeg', 'image/png', 'image/gif'],
        'max_size' => 5 * 1024 * 1024, // 5MB
        'extensions' => ['jpg', 'jpeg', 'png', 'gif']
    ]);

    if (!$logo) {
        $errors[] = "É necessário fazer upload do logo do painel.";
    }
    
    if (empty($errors)) {
        try {
            // Load database configuration
            $db_config = require __DIR__ . '/../../config/database.php';
            
            // Include database connection
            require_once __DIR__ . '/../../plugins/db/' . $db_config['type'] . '/connect.php';
            
            // Get database connection
            $pdo = get_connection($db_config['config']);
            
            if (!$pdo || (is_array($pdo) && !$pdo['success'])) {
                throw new Exception("Erro ao conectar ao banco de dados");
            }
            
            // Process logo upload
            $upload_dir = __DIR__ . '/../../uploads/panel/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
            $logo_filename = 'logo_' . time() . '.' . $file_extension;
            $logo_path = $upload_dir . $logo_filename;
            
            // Move uploaded file
            if (move_uploaded_file($logo['tmp_name'], $logo_path)) {
                // Save panel settings based on database type
                switch ($db_config['type']) {
                    case 'sqlite':
                        // Use the upsert function for SQLite
                        $result = upsert($pdo, 'store_settings', [
                            'setting_key' => 'panel_name',
                            'setting_value' => $panel_name
                        ], 'setting_key');
                        
                        if ($result['success']) {
                            $result = upsert($pdo, 'store_settings', [
                                'setting_key' => 'panel_logo',
                                'setting_value' => $logo_filename
                            ], 'setting_key');
                        }
                        break;

                    default:
                        // MySQL and others support ON DUPLICATE KEY UPDATE
                        $stmt = $pdo->prepare("
                            INSERT INTO store_settings (setting_key, setting_value)
                            VALUES 
                            ('panel_name', :panel_name),
                            ('panel_logo', :panel_logo)
                            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                        ");
                        
                        $result = $stmt->execute([
                            'panel_name' => $panel_name,
                            'panel_logo' => $logo_filename
                        ]);
                        break;
                }
                
                if (!$result) {
                    throw new Exception("Erro ao salvar configurações do painel");
                }
                
                // Create installed flag file
                $installed = [
                    'installed_at' => date('Y-m-d H:i:s'),
                    'version' => '1.0.0',
                    'database_type' => $db_config['type']
                ];
                
                file_put_contents(
                    __DIR__ . '/../../config/installed.php',
                    '<?php return ' . var_export($installed, true) . ';'
                );
                
                // Redirect to finish step
                header('Location: ?step=finish');
                exit;
            } else {
                $errors[] = "Erro ao fazer upload do logo.";
            }
            
        } catch (Exception $e) {
            $errors[] = ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY)['error']['message'];
        }
    }
}

// Get any existing panel settings
try {
    $db_config = require __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../plugins/db/' . $db_config['type'] . '/connect.php';
    $pdo = get_connection($db_config['config']);
    
    if ($pdo && !is_array($pdo)) {
        $stmt = $pdo->query("
            SELECT setting_key, setting_value 
            FROM store_settings 
            WHERE setting_key IN ('panel_name', 'panel_logo')
        ");
        
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
    // Silently handle errors when loading existing settings
}
?>

<div class="store-setup">
    <h2>Configuração do Painel</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="panel_name">Nome do Painel:</label>
            <input type="text" id="panel_name" name="panel_name" class="form-control" 
                   value="<?php echo htmlspecialchars($settings['panel_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="logo">Logo do Painel:</label>
            <div class="logo-upload-container">
                <div class="logo-preview">
                    <?php if (isset($settings['panel_logo'])): ?>
                        <img id="logo-preview-img" 
                             src="/uploads/panel/<?php echo htmlspecialchars($settings['panel_logo']); ?>" 
                             alt="Logo atual">
                    <?php else: ?>
                        <img id="logo-preview-img" 
                             src="/assets/images/placeholder-logo.png" 
                             alt="Preview">
                    <?php endif; ?>
                </div>
                <input type="file" id="logo" name="logo" class="form-control" 
                       accept="image/jpeg,image/png,image/gif" required>
                <small class="form-text text-muted">
                    Formatos permitidos: JPG, PNG, GIF. Tamanho máximo: 5MB.
                </small>
            </div>
        </div>

        <div class="form-actions">
            <a href="?step=admin" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn-primary">Finalizar Instalação</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoInput = document.getElementById('logo');
    const previewImg = document.getElementById('logo-preview-img');

    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                alert('O arquivo é muito grande. Tamanho máximo: 5MB.');
                this.value = '';
                return;
            }

            // Validate file type
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                alert('Tipo de arquivo não permitido. Use JPG, PNG ou GIF.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<style>
.store-setup {
    text-align: center;
    padding: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.alert {
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
    text-align: left;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
}

.btn-primary {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #007bff;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-1px);
}

.btn-secondary {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.logo-upload-container {
    margin-top: 1rem;
}

.logo-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed #ddd;
    border-radius: 1rem;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.logo-preview:hover {
    border-color: #007bff;
}

.logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

@media (max-width: 768px) {
    .store-setup {
        padding: 1rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-primary,
    .btn-secondary {
        width: 100%;
        text-align: center;
    }
}
</style>