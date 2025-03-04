<?php

class Database {
    private $connection;

    public function __construct($config) {
        $this->connect($config);
    }

    private function connect($config) {
        $type = $config['type'];
        $plugin_path = __DIR__ . '/' . $type . '/connect.php';

        if (file_exists($plugin_path)) {
            require_once $plugin_path;
            $this->connection = get_connection($config['config']);
        } else {
            throw new Exception("Database plugin not found: " . $type);
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function testConnection($config) {
        $type = $config['type'];
        $plugin_path = __DIR__ . '/' . $type . '/connect.php';

        if (file_exists($plugin_path)) {
            require_once $plugin_path;
            return test_database_connection($config['config']);
        } else {
            throw new Exception("Database plugin not found: " . $type);
        }
    }
}