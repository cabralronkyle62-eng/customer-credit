<?php

declare(strict_types=1);


final class Database
{
    private string $host;
    private string $port;
    private string $databaseName;
    private string $username;
    private string $password;

    public function __construct()
    {
        $localConfigPath = __DIR__ . '/database.local.php';
        $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
        $passwordFromEnvironment = getenv('DB_PASS');

        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->port = getenv('DB_PORT') ?: '3306';
        $this->databaseName = getenv('DB_NAME') ?: 'customer_credit_monitoring';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = $passwordFromEnvironment !== false
            ? $passwordFromEnvironment
            : (string) ($localConfig['password'] ?? '');
    }

    public function connect(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->databaseName
        );

        try {
            return new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('Database connection error: ' . $exception->getMessage());

           throw new RuntimeException(
    'Database error: ' . $exception->getMessage()

            );
        }
    }
}
