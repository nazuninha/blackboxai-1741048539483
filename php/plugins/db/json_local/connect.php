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
        $database_path = $db_dir . $database_name . '.json'; // Use .json extension

        // Initialize database file
        if (!file_exists($database_path)) {
            file_put_contents($database_path, json_encode([])); // Create an empty JSON file
        }

        // Return a message indicating success
        return "Conexão com o banco de dados JSON local estabelecida com sucesso.";
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function get_connection($config) {
    // This function can be used to return the JSON data as an array
    $database_name = str_replace(['..', '/', '\\'], '', $config['database']);
    $database_path = __DIR__ . '/../../../database/' . $database_name . '.json';

    if (file_exists($database_path)) {
        $data = json_decode(file_get_contents($database_path), true);
        return $data;
    }

    return null;
}