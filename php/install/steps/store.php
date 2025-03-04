<?php
// Check if database and admin are configured
if (!file_exists(__DIR__ . '/../../config/database.php')) {
    header('Location: ?step=database');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = $_POST['store_name'] ?? '';
    $errors = [];
    
    // Validate store name
    if (empty($store_name)) {
        $errors[] = "O nome da loja é obrigatório.";
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
        $errors[] = "É necessário fazer upload do logo da loja.";
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
            $upload_dir = __DIR__ . '/../../uploads/store/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo_filename = 'logo_' . time() . '.' . $file_extension;
            $logo_path = $upload_dir . $logo_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                // Save store settings
                $stmt = $pdo->prepare("
                    INSERT INTO store_settings (setting_key, setting_value)
                    VALUES 
                    ('store_name', :store_name),
                    ('store_logo', :store_logo)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                
                $stmt->execute([
                    'store_name' => $store_name,
                    'store_logo' => $logo_filename
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
            $errors[] = "Erro ao salvar as informações da loja: " . $e->getMessage();
        }
    }
}
?>

<div class="store-setup">
    <h2>Configuração da Loja</h2>
    
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
            <label for="store_name">Nome da Loja:</label>
            <input type="text" id="store_name" name="store_name" class="form-control" 
                   value="<?php echo htmlspecialchars($store_name ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="logo">Logo da Loja:</label>
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
.logo-upload-container {
    margin-top: 10px;
}

.logo-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed #ccc;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
</style>