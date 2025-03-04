<?php

function test_database_connection($config) {
    try {
        // Ensure database directory exists
        $db_dir = __DIR__ . '/../../../database/';
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }

        // Create database path
        $database = $db_dir . str_replace(['..', '/', '\\'], '', $config['database']) . '.sqlite';
        
        // Connect to SQLite database
        $pdo = new PDO(
            "sqlite:" . $database,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // Enable foreign key support
        $pdo->exec('PRAGMA foreign_keys = ON');

        // Create necessary tables
        create_tables($pdo);
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage());
    }
}

function create_tables($pdo) {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Store settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS store_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add other necessary tables here
}

function get_connection($config) {
    static $connection = null;
    
    if ($connection === null) {
        $database = __DIR__ . '/../../../database/' . 
                   str_replace(['..', '/', '\\'], '', $config['database']) . 
                   '.sqlite';
        
        $connection = new PDO(
            "sqlite:" . $database,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // Enable foreign key support
        $connection->exec('PRAGMA foreign_keys = ON');
    }
    
    return $connection;
}

// Create trigger for updated_at
function create_update_trigger($pdo, $table) {
    $pdo->exec("
        CREATE TRIGGER IF NOT EXISTS {$table}_updated_at 
        AFTER UPDATE ON {$table}
        BEGIN
            UPDATE {$table} SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
        END;
    ");
}