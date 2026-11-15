<?php
session_start();

function load_env_file(): void
{
    $envFile = dirname(__DIR__) . '/.env';
    if (!is_file($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        if (($value[0] ?? '') === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}

load_env_file();

function get_db_config(): array
{
    return [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (string) (getenv('DB_PORT') ?: '3306'),
        'dbname' => getenv('DB_NAME') ?: 'portfolio',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ];
}

function db_connect(): PDO
{
    $config = get_db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );

    try {
        $pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]
        );

        return $pdo;
    } catch (Throwable $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new RuntimeException('Unable to connect to the database right now.');
    }
}
