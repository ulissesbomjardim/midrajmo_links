<?php

declare(strict_types=1);

use App\Controllers\ApiController;
use App\Core\Database;
use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use App\Repositories\LinkRepository;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $length = strlen($prefix);
    if (strncmp($prefix, $class, $length) !== 0) {
        return;
    }

    $relativeClass = substr($class, $length);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

$pdo = Database::getConnection();

$controller = new ApiController(
    new CategoryRepository($pdo),
    new TagRepository($pdo),
    new LinkRepository($pdo)
);

$controller->dispatch($action, $method);
