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
                throw new Exception("Database configuration not initialized");
            }

            // Use provided type or get from config
            $type = $type ?? ($config['type'] ?? null);
            if (!$type) {
                throw new Exception("Database type not specified");
            }

            // Check if we already have an instance
            $instance_key = $type . '_' . md5(json_encode($config));
            if (isset(self::$instances[$instance_key])) {
                return self::$instances[$instance_key];
            }

            // Validate the database type
            $plugin_path = __DIR__ . '/../plugins/db/' . $type . '/connect.php';
            if (!file_exists($plugin_path)) {
                throw new Exception("Database type '$type' not supported");
            }

            // Load the database plugin
            require_once $plugin_path;

            // Get the connection
            $connection = get_connection($config);

            // Check if the connection returned an error
            if (is_array($connection) && isset($connection['success']) && !$connection['success']) {
                return $connection;
            }

            // Store the instance
            self::$instances[$instance_key] = $connection;

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
                throw new Exception("Query cannot be empty");
            }

            // Sanitize query parameters
            $params = ErrorHandler::sanitizeInput($params);

            // Check if the connection is valid
            if (!$connection) {
                throw new Exception("Invalid database connection");
            }

            // Execute the query based on connection type
            if ($connection instanceof PDO) {
                $stmt = $connection->prepare($query);
                $stmt->execute($params);
                return ErrorHandler::formatSuccess($stmt);
            } else if (method_exists($connection, 'query')) {
                return $connection->query($query, $params);
            } else {
                throw new Exception("Unsupported database connection type");
            }
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
                throw new Exception("Database type '$type' not supported");
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
        self::$instances = [];
    }
}