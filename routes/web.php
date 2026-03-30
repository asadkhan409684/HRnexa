<?php
/**
 * Web Routes - Handles page loads and standard form submissions
 * Format: 'url' => ['controller' => 'ControllerName', 'action' => 'methodName']
 */

return [
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/login' => ['controller' => 'AuthController', 'action' => 'showLoginForm'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    '/dashboard' => ['controller' => 'EmployeeController', 'action' => 'dashboard'],
    '/profile' => ['controller' => 'EmployeeController', 'action' => 'profile'],
];