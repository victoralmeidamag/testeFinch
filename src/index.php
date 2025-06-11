<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/routes.php';
require_once __DIR__ . '/container.php';

use App\Adapters\Database\SqlServerConnection;
use App\Adapters\Http\Request;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$route = routes($path, $method);

if (empty($route)) {
    http_response_code(404);
    echo json_encode(['error' => 'Rota não encontrada']);
    exit;
}

[$controllerClass, $controllerMethod] = $route;


$pdo = (new SqlServerConnection())->getConnection();

$controllerFactory = dependencies($controllerClass);

if (!is_callable($controllerFactory)) {
    http_response_code(500);
    echo json_encode(['error' => 'Dependência não resolvida']);
    exit;
}

$controller = $controllerFactory($pdo);

$controller->$controllerMethod(new Request());
