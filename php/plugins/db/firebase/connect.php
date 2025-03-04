<?php

function test_database_connection($config) {
    // Firebase connection logic
    $firebase_url = $config['firebase_url'];
    $firebase_key = $config['firebase_key'];

    // Example of a simple connection test
    $response = file_get_contents($firebase_url . '/.json?auth=' . $firebase_key);
    
    if ($response === false) {
        throw new Exception("Erro ao conectar ao Firebase.");
    }

    return json_decode($response, true);
}

function get_connection($config) {
    // This function can be used to return Firebase data as an array
    $firebase_url = $config['firebase_url'];
    $firebase_key = $config['firebase_key'];

    $response = file_get_contents($firebase_url . '/.json?auth=' . $firebase_key);
    
    if ($response === false) {
        throw new Exception("Erro ao obter dados do Firebase.");
    }

    return json_decode($response, true);
}