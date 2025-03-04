<?php
require_once __DIR__ . '/error_handler.php';

class Config {
    private static $instance = null;
    private $config = [];
    private $config_path;

    private function __construct() {
        $this->config_path = __DIR__ . '/../config/';
        $this->loadConfigurations();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load all configuration files
     */
    private function loadConfigurations() {
        try {
            if (!is_dir($this->config_path)) {
                mkdir($this->config_path, 0755, true);
            }

            // Load database configuration
            $db_config = $this->loadFile('database.php');
            if ($db_config) {
                $this->config['database'] = $db_config;
            }

            // Load theme configuration
            $theme_config = $this->loadFile('theme.php');
            if ($theme_config) {
                $this->config['theme'] = $theme_config;
            }

            // Load other configurations as needed
        } catch (Exception $e) {
            ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
        }
    }

    /**
     * Load a specific configuration file
     * @param string $filename Configuration file name
     * @return mixed Configuration data or null if file doesn't exist
     */
    private function loadFile($filename) {
        $file_path = $this->config_path . $filename;
        if (file_exists($file_path)) {
            return require $file_path;
        }
        return null;
    }

    /**
     * Get configuration value
     * @param string $key Configuration key (use dot notation for nested values)
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Configuration value
     */
    public function get($key, $default = null) {
        try {
            $keys = explode('.', $key);
            $value = $this->config;

            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }

            return $value;
        } catch (Exception $e) {
            ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
            return $default;
        }
    }

    /**
     * Set configuration value
     * @param string $key Configuration key (use dot notation for nested values)
     * @param mixed $value Configuration value
     * @return bool Success status
     */
    public function set($key, $value) {
        try {
            // Validate and sanitize input
            $validation = ErrorHandler::validateInput(
                ['key' => $key, 'value' => $value],
                ['key' => ['required' => true, 'type' => 'string']]
            );

            if (!$validation['isValid']) {
                throw new Exception('Invalid configuration key');
            }

            $keys = explode('.', $key);
            $config = &$this->config;

            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $config[$k] = $value;
                } else {
                    if (!isset($config[$k]) || !is_array($config[$k])) {
                        $config[$k] = [];
                    }
                    $config = &$config[$k];
                }
            }

            // Save configuration to file if it's a top-level key
            if (count($keys) === 1) {
                $this->saveConfiguration($keys[0]);
            }

            return true;
        } catch (Exception $e) {
            ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
            return false;
        }
    }

    /**
     * Save configuration to file
     * @param string $key Configuration key (top-level only)
     * @return bool Success status
     */
    private function saveConfiguration($key) {
        try {
            if (!isset($this->config[$key])) {
                throw new Exception("Configuration key '$key' not found");
            }

            $file_path = $this->config_path . $key . '.php';
            $content = "<?php\nreturn " . var_export($this->config[$key], true) . ";\n";

            // Create a temporary file first
            $temp_file = $file_path . '.tmp';
            if (file_put_contents($temp_file, $content) === false) {
                throw new Exception("Failed to write configuration file");
            }

            // Rename temporary file to actual file
            if (!rename($temp_file, $file_path)) {
                unlink($temp_file);
                throw new Exception("Failed to save configuration file");
            }

            return true;
        } catch (Exception $e) {
            ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
            return false;
        }
    }

    /**
     * Check if configuration exists
     * @param string $key Configuration key
     * @return bool Whether configuration exists
     */
    public function has($key) {
        try {
            $keys = explode('.', $key);
            $value = $this->config;

            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return false;
                }
                $value = $value[$k];
            }

            return true;
        } catch (Exception $e) {
            ErrorHandler::handleError($e, ErrorHandler::ERROR_VALIDATION);
            return false;
        }
    }
}