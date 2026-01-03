<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set CORS headers for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->post('/graphql', [App\Controller\GraphQL::class, 'handle']);
});

// Remove base path and query string from URI
$basePath = '/scandiweb';
$requestUri = $_SERVER['REQUEST_URI'];
 

// Remove base path
if (strpos($requestUri, $basePath) === 0) {         
    $requestUri = substr($requestUri, strlen($basePath)); // strip the base path from the beginning
} 

$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $requestUri
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Method Not Allowed',
            'allowed' => $routeInfo[1]
        ]);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        // Handle callable or class method
        if (is_callable($handler)) {
            echo $handler($vars);
        } elseif (is_array($handler)) {
            [$class, $method] = $handler;
            echo (new $class())->$method($vars);
        }
        break;
}
