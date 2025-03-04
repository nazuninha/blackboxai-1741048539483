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
        $database_path = $db_dir . $database_name . '.sqlite'; // Use .sqlite extension

        // Create PDO connection
        $pdo = new PDO(
            "sqlite:" . $database_path,
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
        $database_name = str_replace(['..', '/', '\\'], '', $config['database']);
        
        $connection = new PDO(
            "sqlite:" . __DIR__ . '/../../../database/' . $database_name . '.sqlite',
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