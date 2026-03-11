<?php
declare(strict_types=1);

function appConfig(): array
{
    $base = require __DIR__ . '/config.php';
    $localFile = __DIR__ . '/config.local.php';
    if (file_exists($localFile)) {
        $local = require $localFile;
        if (is_array($local)) {
            $base = array_merge($base, $local);
        }
    }
    return $base;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $c = appConfig();
    $charset = 'utf8mb4';
    if (!empty($c['db_socket'])) {
        $dsn = sprintf(
            'mysql:unix_socket=%s;dbname=%s;charset=%s',
            $c['db_socket'],
            $c['db_name'],
            $charset
        );
    } else {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $c['db_host'],
            $c['db_port'],
            $c['db_name'],
            $charset
        );
    }

    $pdo = new PDO($dsn, (string)$c['db_user'], (string)$c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

