<?php

function test_database_connection($config) {
    try {
        // Ensure database directory exists
        $db_dir = __DIR__ . '/../../../database/';
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }

        // Create database path and name
        $database_name = str_replace(['..', '/', '\\'], '', $config['database']);
        $database_path = $db_dir . $database_name;

        // Create data directory if it doesn't exist
        if (!is_dir($database_path)) {
            mkdir($database_path, 0755, true);
        }

        // Initialize database file
        $db_file = $database_path . '/data.sql';
        if (!file_exists($db_file)) {
            file_put_contents($db_file, '');
            chmod($db_file, 0644);
        }

        // Create PDO connection
        $pdo = new PDO(
            "mysql:unix_socket=/tmp/mysql.sock;charset=utf8mb4",
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $database_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . $database_name . "`");

        // Create necessary tables
        create_tables($pdo);

        // Save database configuration
        $config_file = $database_path . '/config.php';
        file_put_contents($config_file, '<?php return ' . var_export([
            'database' => $database_name,
            'created_at' => date('Y-m-d H:i:s')
        ], true) . ';');
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

function create_tables($pdo) {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Store settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS store_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Add other necessary tables here
}

function get_connection($config) {
    static $connection = null;
    
    if ($connection === null) {
        $database_name = str_replace(['..', '/', '\\'], '', $config['database']);
        
        $connection = new PDO(
            "mysql:unix_socket=/tmp/mysql.sock;dbname={$database_name};charset=utf8mb4",
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    return $connection;
}

// Backup function
function backup_database($config) {
    $database_name = str_replace(['..', '/', '\\'], '', $config['database']);
    $database_path = __DIR__ . '/../../../database/' . $database_name;
    $backup_file = $database_path . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Create backup using mysqldump
    $command = sprintf(
        'mysqldump --user=root --socket=/tmp/mysql.sock %s > %s',
        escapeshellarg($database_name),
        escapeshellarg($backup_file)
    );
    
    exec($command, $output, $return_var);
    
    if ($return_var !== 0) {
        throw new Exception('Failed to create database backup');
    }
    
    return $backup_file;
}

// Restore function
function restore_database($config, $backup_file) {
    $database_name = str_replace(['..', '/', '\\'], '', $config['database']);
    
    // Restore from backup using mysql
    $command = sprintf(
        'mysql --user=root --socket=/tmp/mysql.sock %s < %s',
        escapeshellarg($database_name),
        escapeshellarg($backup_file)
    );
    
    exec($command, $output, $return_var);
    
    if ($return_var !== 0) {
        throw new Exception('Failed to restore database from backup');
    }
    
    return true;
}