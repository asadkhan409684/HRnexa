<?php
/**
 * Router - Core routing engine for HRnexa
 */
class Router {
    private $webRoutes;
    private $apiRoutes;

    public function __construct() {
        // Load defined routes
        $this->webRoutes = require_once __DIR__ . '/../../routes/web.php';
        $this->apiRoutes = require_once __DIR__ . '/../../routes/api.php';
    }

    /**
     * Dispatches the request to the appropriate controller action
     * @param string $uri The requested URI
     */
    public function dispatch($uri) {
        // Remove query strings
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Handle subfolders dynamically
        // Get the base directory from the current script's directory
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        
        // If the path starts with the base path, remove it
        if ($basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Ensure path starts with /
        if (empty($path)) {
            $path = '/';
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        // Combine all routes for matching
        $allRoutes = array_merge($this->webRoutes, $this->apiRoutes);

        if (array_key_exists($path, $allRoutes)) {
            $route = $allRoutes[$path];
            $controllerName = $route['controller'];
            $action = $route['action'];

            // Include the controller file
            $controllerFile = __DIR__ . "/../Controllers/$controllerName.php";
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                // Get DB connection (assuming global $conn from database.php)
                global $conn;
                
                $controller = new $controllerName($conn);
                
                if (method_exists($controller, $action)) {
                    // Execute action
                    $controller->$action();
                } else {
                    $this->handleError(500, "Action $action not found in $controllerName");
                }
            } else {
                $this->handleError(500, "Controller $controllerName not found");
            }
        } else {
            // Check if it's an API request or Web request for error display
            if (strpos($path, '/api/') === 0) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'API Route not found']);
            } else {
                $this->handleError(404);
            }
        }
    }

    /**
     * Handles displaying error pages
     * @param int $code HTTP response code
     * @param string $message Optional internal message
     */
    private function handleError($code, $message = '') {
        http_response_code($code);
        
        $errorPage = __DIR__ . "/../../views/errors/$code.php";
        if (file_exists($errorPage)) {
            require_once $errorPage;
        } else {
            echo "Error $code: " . ($message ?: "Something went wrong.");
        }
        exit();
    }
}
