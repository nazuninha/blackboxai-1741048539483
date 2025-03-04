<?php
session_start();

// Check if system is installed
if (!file_exists(__DIR__ . '/../config/installed.php')) {
    header('Location: /install');
    exit;
}

// Load configuration
$db_config = require __DIR__ . '/../config/database.php';
$theme_config = require __DIR__ . '/../plugins/themes/default/config.json';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php');
    exit;
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Load theme
$theme = $theme_config['name'] ?? 'default';
$theme_path = __DIR__ . '/../plugins/themes/' . $theme;

if (!is_dir($theme_path)) {
    die('Theme not found');
}

// Include the theme's layout
include $theme_path . '/layout.php';