<?php
/**
 * HRnexa - Front Controller
 */

// Load Configuration and Database
require_once __DIR__ . '/app/Config/database.php';

// Load Core Files
require_once __DIR__ . '/app/Core/Router.php';

// Load Helpers
foreach (glob(__DIR__ . '/app/Helpers/*.php') as $helper) {
    require_once $helper;
}

// Initialize Router
$router = new Router();

// Dispatch the request
$uri = $_SERVER['REQUEST_URI'];

// Handle subfolder if necessary (optional - adjust based on your server setup)
// $uri = str_replace('/HRnexa', '', $uri); 

$router->dispatch($uri);
