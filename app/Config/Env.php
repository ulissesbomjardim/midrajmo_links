<?php

namespace App\Config;

class Env
{
    public static function load($filePath)
    {
        $data = [];

        if (!is_file($filePath)) {
            return $data;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $data;
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
            $value = trim($value);
            $value = trim($value, "\"'");

            $data[$key] = $value;
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }

        return $data;
    }
}
