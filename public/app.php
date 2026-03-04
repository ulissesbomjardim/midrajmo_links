<?php

declare(strict_types=1);

// Headers de segurança que permitem scripts inline (necessário para o HTML funcionar)
header("Content-Type: text/html; charset=utf-8");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
// CSP permissivo para permitir scripts inline
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'self';");

$html_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'run_app.htm';

if (!is_file($html_file)) {
    http_response_code(404);
    echo '<h1>404 - Arquivo não encontrado</h1>';
    exit;
}

readfile($html_file);
