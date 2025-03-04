<?php
require_once __DIR__ . '/error_handler.php';

class DatabaseFactory {
    private static $instances = [];
    private static $config = null;

    /**
     * Initialize the database factory with configuration
     * @param array $config Database configuration
     * @return void
     */
    public static function initialize($config) {
        self::$config = $config;
    }

    /**
     * Get a database connection
     * @param string $type Database type (optional, uses config if not provided)
     * @param array $config Database configuration (optional, uses stored config if not provided)
     * @return mixed Database connection or error response
     */
    public static function getConnection($type = null, $config = null) {
        try {
            // Use provided config or fall back to stored config
            $config = $config ?? self::$config;
            if (!$config) {
                throw new Exception("Configuração do banco de dados não inicializada");
            }

            // Use provided type or get from config
            $type = $type ?? ($config['type'] ?? null);
            if (!$type) {
                throw new Exception("Tipo de banco de dados não especificado");
            }

            // Check if we already have an instance
            $instance_key = $type . '_' . md5(json_encode($config));
            if (isset(self::$instances[$instance_key])) {
                return self::$instances[$instance_key];
            }

            // Validate the database type
            $plugin_path = __DIR__ . '/../plugins/db/' . $type . '/connect.php';
            if (!file_exists($plugin_path)) {
                throw new Exception("Tipo de banco de dados '$type' não suportado");
            }

            // Load the database plugin
            require_once $plugin_path;

            // Get the connection
            $connection = get_connection($config);

            // Store the instance if it's a PDO object
            if ($connection instanceof PDO) {
                self::$instances[$instance_key] = $connection;
            }

            return $connection;
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
        }
    }

    /**
     * Execute a database query safely
     * @param mixed $connection Database connection
     * @param string $query Query string
     * @param array $params Query parameters
     * @return array Result with success/error information
     */
    public static function executeQuery($connection, $query, $params = []) {
        try {
            // Validate query
            if (empty($query)) {
                throw new Exception("Query não pode estar vazia");
            }

            // Check if the connection is valid
            if (!$connection instanceof PDO) {
                if (is_array($connection) && isset($connection['error'])) {
                    // If connection is an error response, return it
                    return $connection;
                }
                throw new Exception("Conexão com banco de dados inválida");
            }

            // Execute the query
            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            return ErrorHandler::formatSuccess($stmt);
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_QUERY);
        }
    }

    /**
     * Test database connection
     * @param string $type Database type
     * @param array $config Database configuration
     * @return array Result with success/error information
     */
    public static function testConnection($type, $config) {
        try {
            // Validate the database type
            $plugin_path = __DIR__ . '/../plugins/db/' . $type . '/connect.php';
            if (!file_exists($plugin_path)) {
                throw new Exception("Tipo de banco de dados '$type' não suportado");
            }

            // Load the database plugin
            require_once $plugin_path;

            // Test the connection
            return test_database_connection($config);
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
        }
    }

    /**
     * Close all database connections
     * @return void
     */
    public static function closeAll() {
        foreach (self::$instances as $key => $instance) {
            if ($instance instanceof PDO) {
                $instance = null;
            }
        }
        self::$instances = [];
    }

    /**
     * Get database error info
     * @param mixed $connection Database connection
     * @return array Error information
     */
    public static function getErrorInfo($connection) {
        if ($connection instanceof PDO) {
            return $connection->errorInfo();
        }
        return ['Unknown error'];
    }
}