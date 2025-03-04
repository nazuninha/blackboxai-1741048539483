<?php

class ErrorHandler {
    // Error codes
    const ERROR_CONNECTION = 1001;
    const ERROR_QUERY = 1002;
    const ERROR_VALIDATION = 1003;
    const ERROR_AUTHENTICATION = 1004;
    const ERROR_PERMISSION = 1005;

    // Error messages (user-friendly)
    private static $errorMessages = [
        self::ERROR_CONNECTION => 'Não foi possível conectar ao banco de dados.',
        self::ERROR_QUERY => 'Ocorreu um erro ao processar sua solicitação.',
        self::ERROR_VALIDATION => 'Os dados fornecidos são inválidos.',
        self::ERROR_AUTHENTICATION => 'Erro de autenticação.',
        self::ERROR_PERMISSION => 'Você não tem permissão para realizar esta ação.'
    ];

    // Log levels
    const LOG_ERROR = 'ERROR';
    const LOG_WARNING = 'WARNING';
    const LOG_INFO = 'INFO';

    /**
     * Handle database error
     * @param Exception $e Original exception
     * @param int $errorCode Error code
     * @return array Formatted error response
     */
    public static function handleError($e, $errorCode) {
        // Log the actual error
        self::logError($e, $errorCode);

        // Return user-friendly message
        return [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => self::$errorMessages[$errorCode] ?? 'Ocorreu um erro inesperado.'
            ]
        ];
    }

    /**
     * Validate input data
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array [isValid, errors]
     */
    public static function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) && $rule['required']) {
                $errors[$field] = 'Campo obrigatório';
                continue;
            }

            if (isset($data[$field])) {
                $value = $data[$field];

                // Type validation
                if (isset($rule['type'])) {
                    switch ($rule['type']) {
                        case 'string':
                            if (!is_string($value)) {
                                $errors[$field] = 'Deve ser uma string';
                            }
                            break;
                        case 'int':
                            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                                $errors[$field] = 'Deve ser um número inteiro';
                            }
                            break;
                        case 'email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[$field] = 'E-mail inválido';
                            }
                            break;
                    }
                }

                // Length validation
                if (isset($rule['min']) && strlen($value) < $rule['min']) {
                    $errors[$field] = "Mínimo de {$rule['min']} caracteres";
                }
                if (isset($rule['max']) && strlen($value) > $rule['max']) {
                    $errors[$field] = "Máximo de {$rule['max']} caracteres";
                }

                // Pattern validation
                if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                    $errors[$field] = $rule['pattern_message'] ?? 'Formato inválido';
                }

                // Custom validation
                if (isset($rule['custom']) && is_callable($rule['custom'])) {
                    $result = $rule['custom']($value);
                    if ($result !== true) {
                        $errors[$field] = $result;
                    }
                }
            }
        }

        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Sanitize input data
     * @param mixed $input Input data
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        if (is_string($input)) {
            // Remove any HTML tags
            $input = strip_tags($input);
            
            // Convert special characters to HTML entities
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            
            // Remove any potential SQL injection attempts
            $input = str_replace(['--', ';'], '', $input);
            
            return $input;
        }
        
        return $input;
    }

    /**
     * Log error to file
     * @param Exception $e Exception
     * @param int $errorCode Error code
     * @return void
     */
    private static function logError($e, $errorCode) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $errorMessage = sprintf(
            "[%s] [%s] Error %d: %s\nStack trace:\n%s\n",
            $timestamp,
            self::LOG_ERROR,
            $errorCode,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        error_log($errorMessage, 3, $logFile);
    }

    /**
     * Format success response
     * @param mixed $data Response data
     * @return array Formatted success response
     */
    public static function formatSuccess($data) {
        return [
            'success' => true,
            'data' => $data
        ];
    }
}