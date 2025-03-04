<?php

function test_database_connection($config) {
    try {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;charset=utf8mb4",
            $config['host'],
            $config['port'] ?? '3306'
        );
        
        // First connect without database to check if we can create it
        $pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        // Try to create database if it doesn't exist
        $database = $config['database'];
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $database) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Connect to the specific database
        $pdo = new PDO(
            $dsn . ";dbname=" . $database,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

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
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        last_login TIMESTAMP NULL DEFAULT NULL,  // Added last_login column
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
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
            $config['host'],
            $config['port'] ?? '3306',
            $config['database']
        );
        
        $connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    return $connection;
}