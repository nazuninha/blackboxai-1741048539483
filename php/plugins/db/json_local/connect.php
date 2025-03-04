<?php
require_once __DIR__ . '/../../../core/error_handler.php';

class JsonDatabase {
    private $data;
    private $file_path;
    private static $instance = null;
    private $is_locked = false;
    private $lock_file;

    private function __construct($file_path) {
        $this->file_path = $file_path;
        $this->lock_file = $file_path . '.lock';
        $this->load();
    }

    public static function getInstance($config) {
        if (self::$instance === null) {
            $db_dir = __DIR__ . '/../../../database/';
            if (!is_dir($db_dir)) {
                mkdir($db_dir, 0755, true);
            }

            $database = str_replace(['..', '/', '\\'], '', $config['database']);
            $file_path = $db_dir . $database . '.json';

            self::$instance = new self($file_path);
        }
        return self::$instance;
    }

    private function acquireLock() {
        $start_time = microtime(true);
        $timeout = 10; // 10 seconds timeout

        while (true) {
            if (!file_exists($this->lock_file)) {
                file_put_contents($this->lock_file, getmypid());
                $this->is_locked = true;
                return true;
            }

            if (microtime(true) - $start_time > $timeout) {
                throw new Exception("Timeout waiting for database lock");
            }

            usleep(100000); // Wait 100ms before trying again
        }
    }

    private function releaseLock() {
        if ($this->is_locked && file_exists($this->lock_file)) {
            unlink($this->lock_file);
            $this->is_locked = false;
        }
    }

    private function load() {
        if (!file_exists($this->file_path)) {
            $this->data = $this->getInitialStructure();
            $this->save();
        } else {
            $content = file_get_contents($this->file_path);
            $this->data = json_decode($content, true);
            if ($this->data === null) {
                throw new Exception("Invalid JSON data in database file");
            }
        }
    }

    private function save() {
        $this->acquireLock();
        try {
            file_put_contents($this->file_path, json_encode($this->data, JSON_PRETTY_PRINT));
        } finally {
            $this->releaseLock();
        }
    }

    private function getInitialStructure() {
        return [
            'users' => [],
            'store_settings' => [],
            'metadata' => [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'version' => '1.0'
            ]
        ];
    }

    public function query($table, $conditions = []) {
        if (!isset($this->data[$table])) {
            throw new Exception("Table '$table' not found");
        }

        $result = array_filter($this->data[$table], function($row) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($row[$key]) || $row[$key] !== $value) {
                    return false;
                }
            }
            return true;
        });

        return array_values($result);
    }

    public function insert($table, $data) {
        if (!isset($this->data[$table])) {
            throw new Exception("Table '$table' not found");
        }

        // Validate data
        $validation = ErrorHandler::validateInput($data, $this->getTableValidationRules($table));
        if (!$validation['isValid']) {
            throw new Exception('Invalid data: ' . json_encode($validation['errors']));
        }

        // Sanitize data
        $data = ErrorHandler::sanitizeInput($data);

        // Add metadata
        $data['id'] = $this->getNextId($table);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->data[$table][] = $data;
        $this->save();

        return $data;
    }

    public function update($table, $id, $data) {
        if (!isset($this->data[$table])) {
            throw new Exception("Table '$table' not found");
        }

        // Validate data
        $validation = ErrorHandler::validateInput($data, $this->getTableValidationRules($table));
        if (!$validation['isValid']) {
            throw new Exception('Invalid data: ' . json_encode($validation['errors']));
        }

        // Sanitize data
        $data = ErrorHandler::sanitizeInput($data);

        foreach ($this->data[$table] as &$row) {
            if ($row['id'] === $id) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $row = array_merge($row, $data);
                $this->save();
                return $row;
            }
        }

        throw new Exception("Record not found");
    }

    private function getNextId($table) {
        $max_id = 0;
        foreach ($this->data[$table] as $row) {
            if ($row['id'] > $max_id) {
                $max_id = $row['id'];
            }
        }
        return $max_id + 1;
    }

    private function getTableValidationRules($table) {
        switch ($table) {
            case 'users':
                return [
                    'name' => ['required' => true, 'type' => 'string', 'min' => 2, 'max' => 255],
                    'email' => ['required' => true, 'type' => 'email'],
                    'password' => ['required' => true, 'type' => 'string', 'min' => 8]
                ];
            case 'store_settings':
                return [
                    'setting_key' => ['required' => true, 'type' => 'string'],
                    'setting_value' => ['required' => true]
                ];
            default:
                return [];
        }
    }
}

function test_database_connection($config) {
    try {
        // Validate required configuration
        $validation = ErrorHandler::validateInput($config, [
            'database' => ['required' => true, 'type' => 'string']
        ]);

        if (!$validation['isValid']) {
            throw new Exception('Configuração inválida: ' . json_encode($validation['errors']));
        }

        $db = JsonDatabase::getInstance($config);
        return ErrorHandler::formatSuccess($db);
    } catch (Exception $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
    }
}

function get_connection($config) {
    try {
        return JsonDatabase::getInstance($config);
    } catch (Exception $e) {
        return ErrorHandler::handleError($e, ErrorHandler::ERROR_CONNECTION);
    }
}