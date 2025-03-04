<?php
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/database_factory.php';

class Auth {
    private static $instance = null;
    private $db;
    private $session_duration = 7200; // 2 hours
    private $max_login_attempts = 5;
    private $lockout_duration = 900; // 15 minutes

    private function __construct() {
        try {
            // Load database configuration
            $db_config = require __DIR__ . '/../config/database.php';
            
            // Get database connection
            $this->db = DatabaseFactory::getConnection($db_config['type'], $db_config['config']);
            
            if (!$this->db instanceof PDO) {
                throw new Exception("Falha ao conectar ao banco de dados");
            }
            
            $this->initializeSession();
        } catch (Exception $e) {
            error_log("Auth initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize secure session
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
        }

        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $this->regenerateSession();
        } else if (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            $this->regenerateSession();
        }
    }

    /**
     * Regenerate session safely
     */
    private function regenerateSession() {
        // Update regeneration time
        $_SESSION['last_regeneration'] = time();
        
        // Regenerate session ID
        session_regenerate_id(true);
    }

    /**
     * Attempt user login
     * @param string $email User email
     * @param string $password User password
     * @return array Login result
     */
    public function login($email, $password) {
        try {
            // Validate input
            $validation = ErrorHandler::validateInput(
                ['email' => $email, 'password' => $password],
                [
                    'email' => ['required' => true, 'type' => 'email'],
                    'password' => ['required' => true, 'type' => 'string', 'min' => 8]
                ]
            );

            if (!$validation['isValid']) {
                throw new Exception('Dados de login inválidos');
            }

            // Check for too many login attempts
            if ($this->isIpLocked($_SERVER['REMOTE_ADDR'])) {
                throw new Exception('Muitas tentativas de login. Tente novamente mais tarde.');
            }

            // Get user from database
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $this->recordFailedLogin($_SERVER['REMOTE_ADDR']);
                throw new Exception('E-mail ou senha inválidos');
            }

            // Clear failed login attempts
            $this->clearFailedLogins($_SERVER['REMOTE_ADDR']);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['is_admin'] = true; // Since this is admin panel

            // Update last login
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $stmt->execute(['id' => $user['id']]);

            return ErrorHandler::formatSuccess([
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]);
        } catch (Exception $e) {
            return ErrorHandler::handleError($e, ErrorHandler::ERROR_AUTHENTICATION);
        }
    }

    /**
     * Check if user is authenticated
     * @return bool Authentication status
     */
    public function isAuthenticated() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }

        // Check session expiration
        if (time() - $_SESSION['last_activity'] > $this->session_duration) {
            $this->logout();
            return false;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * Log out user
     * @return bool Logout success
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Destroy the session
        session_destroy();

        return true;
    }

    /**
     * Record failed login attempt
     * @param string $ip IP address
     */
    private function recordFailedLogin($ip) {
        $key = 'failed_logins_' . $ip;
        $attempts = isset($_SESSION[$key]) ? $_SESSION[$key] : [];
        $attempts[] = time();
        
        // Keep only recent attempts
        $attempts = array_filter($attempts, function($time) {
            return $time > (time() - $this->lockout_duration);
        });

        $_SESSION[$key] = $attempts;
    }

    /**
     * Check if IP is locked out
     * @param string $ip IP address
     * @return bool Lock status
     */
    private function isIpLocked($ip) {
        $key = 'failed_logins_' . $ip;
        if (!isset($_SESSION[$key])) {
            return false;
        }

        $attempts = array_filter($_SESSION[$key], function($time) {
            return $time > (time() - $this->lockout_duration);
        });

        return count($attempts) >= $this->max_login_attempts;
    }

    /**
     * Clear failed login attempts
     * @param string $ip IP address
     */
    private function clearFailedLogins($ip) {
        $key = 'failed_logins_' . $ip;
        unset($_SESSION[$key]);
    }

    /**
     * Get current user data
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT id, name, email, last_login FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }
}