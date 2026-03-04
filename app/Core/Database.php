<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $connection;

    public static function getConnection()
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $privateConfigPath = getenv('APP_PRIVATE_CONFIG');
        $projectConfigPath = dirname(__DIR__) . '/Config/config.php';
        $defaultConfigPath = dirname(__DIR__) . '/Config/config.default.php';

        if ($privateConfigPath && is_file($privateConfigPath)) {
            $config = require $privateConfigPath;
        } elseif (is_file($projectConfigPath)) {
            $config = require $projectConfigPath;
        } else {
            $config = require $defaultConfigPath;
        }

        $db = $config['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['name'],
            $db['charset']
        );

        try {
            self::$connection = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('Falha ao conectar com o banco: ' . $exception->getMessage(), (int) $exception->getCode());
        }

        return self::$connection;
    }
}
