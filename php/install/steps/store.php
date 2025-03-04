<?php
// Check if database and admin are configured
if (!file_exists(__DIR__ . '/../../config/database.php')) {
    header('Location: ?step=database');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $panel_name = $_POST['panel_name'] ?? '';
    $errors = [];
    
    // Validate panel name
    if (empty($panel_name)) {
        $errors[] = "O nome do painel é obrigatório.";
    }
    
    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type and size
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Tipo de arquivo não permitido. Use JPG, PNG ou GIF.";
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = "O arquivo é muito grande. Tamanho máximo: 5MB.";
        }
    } else {
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
            
            // Process logo upload
            $upload_dir = __DIR__ . '/../../uploads/panel/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_filename = 'logo_' . time() . '.' . $file_extension;
            $logo_path = $upload_dir . $logo_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                // Save panel settings
                $stmt = $pdo->prepare("
                    INSERT INTO store_settings (setting_key, setting_value)
                    VALUES 
                    ('panel_name', :panel_name),
                    ('panel_logo', :panel_logo)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                
                $stmt->execute([
                    'panel_name' => $panel_name,
                    'panel_logo' => $logo_filename
                ]);
                
                // Create installed flag file
                file_put_contents(
                    __DIR__ . '/../../config/installed.php',
                    '<?php return ' . var_export(['installed_at' => date('Y-m-d H:i:s')], true) . ';'
                );
                
                // Redirect to finish step
                header('Location: ?step=finish');
                exit;
            } else {
                $errors[] = "Erro ao fazer upload do logo.";
            }
            
        } catch (Exception $e) {
            $errors[] = "Erro ao salvar as informações do painel: " . $e->getMessage();
        }
    }
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
                   value="<?php echo htmlspecialchars($panel_name ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="logo">Logo do Painel:</label>
            <div class="logo-upload-container">
                <div class="logo-preview">
                    <img id="logo-preview-img" src="/assets/images/placeholder-logo.png" alt="Preview">
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

.logo-upload-container {
    margin-top: 10px;
}

.logo-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed #ccc;
    border-radius: 0.75rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f9f9f9;
}

.logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
</style>