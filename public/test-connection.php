<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function parse_env_file($env_path)
{
    $config = [];

    if (!is_file($env_path)) {
        return $config;
    }

    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $config;
    }

    foreach ($lines as $rawLine) {
        $line = trim($rawLine);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
        } elseif (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
        } else {
            continue;
        }

        $key = trim($key);
        $value = trim(trim($value), "\"'");

        $config[$key] = $value;
    }

    return $config;
}

function test_mysql_connection($config)
{
    $required = ['hostname', 'port', 'username', 'password', 'database'];
    $missing = [];

    foreach ($required as $key) {
        if (empty($config[$key])) {
            $missing[] = $key;
        }
    }

    if (!empty($missing)) {
        return [
            'ok' => false,
            'message' => 'Campos faltando no .env: ' . implode(', ', $missing),
        ];
    }

    try {
        $connection = new PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['hostname'],
                $config['port'],
                $config['database']
            ),
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]
        );

        $stmt = $connection->query('SELECT VERSION()');
        $version = $stmt->fetchColumn();

        $connection = null;

        return [
            'ok' => true,
            'message' => 'Conexão com MySQL realizada com sucesso.',
            'server' => $version ?: 'desconhecido',
        ];
    } catch (Exception $exception) {
        return [
            'ok' => false,
            'message' => $exception->getMessage(),
        ];
    }
}

// Encontra o .env na raiz do projeto
$root = dirname(dirname(__FILE__));
$env_path = $root . DIRECTORY_SEPARATOR . '.env';

$config = parse_env_file($env_path);
$result = test_mysql_connection($config);

$status = $result['ok'] ? 200 : 500;
http_response_code($status);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
