<?php
session_start();
define('ROOT_PATH', __DIR__);

// Check if system is installed
if (!file_exists(ROOT_PATH . '/config/installed.php')) {
    header('Location: /install/');
    exit;
}

// Router implementation
$request = $_SERVER['REQUEST_URI'];
$base_path = dirname($_SERVER['SCRIPT_NAME']);
$route = str_replace($base_path, '', $request);

// Basic router
switch ($route) {
    case '/':
        require __DIR__ . '/pages/home.php';
        break;
    default:
        require __DIR__ . '/pages/404.php';
        break;
}