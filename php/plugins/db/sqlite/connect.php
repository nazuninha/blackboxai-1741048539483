<?php
require_once __DIR__ . '/../../../core/error_handler.php';

function test_database_connection($config) {
    try {
        // Validate required configuration
        $validation = ErrorHandler::validateInput($config, [
            'database' => ['required' => true, 'type' => 'string']
        ]);

        if (!$validation['isValid']) {
            throw new Exception('Configuração inválida: ' . json_encode($validation['errors']));
        }

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
        
        return ErrorHandler::formatSuccess($pdo);
    } catch (PDOException $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
    } catch (Exception $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
    }
}

function create_tables($pdo) {
    try {
        // Users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            last_login TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Store settings table with UNIQUE constraint
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Create trigger for updated_at on users
        $pdo->exec("
            CREATE TRIGGER IF NOT EXISTS update_users_timestamp 
            AFTER UPDATE ON users
            BEGIN
                UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
            END;
        ");

        // Create trigger for updated_at on store_settings
        $pdo->exec("
            CREATE TRIGGER IF NOT EXISTS update_settings_timestamp 
            AFTER UPDATE ON store_settings
            BEGIN
                UPDATE store_settings SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
            END;
        ");

    } catch (PDOException $e) {
        throw new Exception("Erro ao criar tabelas: " . $e->getMessage());
    }
}

function get_connection($config) {
    try {
        static $connection = null;
        
        if ($connection === null) {
            // Validate required configuration
            $validation = ErrorHandler::validateInput($config, [
                'database' => ['required' => true, 'type' => 'string']
            ]);

            if (!$validation['isValid']) {
                throw new Exception('Configuração inválida: ' . json_encode($validation['errors']));
            }

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
    } catch (PDOException $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
    } catch (Exception $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
    }
}

/**
 * Execute a database query safely
 * @param PDO $pdo PDO connection
 * @param string $query SQL query
 * @param array $params Query parameters
 * @return array Result with success/error information
 */
function execute_query($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return ErrorHandler::formatSuccess($stmt);
    } catch (PDOException $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY);
    }
}

/**
 * Insert or update a record (UPSERT operation for SQLite)
 * @param PDO $pdo PDO connection
 * @param string $table Table name
 * @param array $data Data to insert/update
 * @param string $uniqueKey Column name for uniqueness check
 * @return array Result with success/error information
 */
function upsert($pdo, $table, $data, $uniqueKey) {
    try {
        // Check if record exists
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE $uniqueKey = :key");
        $stmt->execute(['key' => $data[$uniqueKey]]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update
            $sets = [];
            $params = [];
            foreach ($data as $key => $value) {
                $sets[] = "$key = :$key";
                $params[$key] = $value;
            }
            $params['id'] = $exists['id'];

            $query = "UPDATE $table SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        } else {
            // Insert
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_map(function($key) { return ":$key"; }, array_keys($data)));
            
            $query = "INSERT INTO $table ($columns) VALUES ($values)";
            $stmt = $pdo->prepare($query);
            $stmt->execute($data);
        }

        return ErrorHandler::formatSuccess(true);
    } catch (PDOException $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY);
    }
}