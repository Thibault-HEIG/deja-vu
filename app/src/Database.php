<?php
/**
 * Database class for managing the PDO connection.
 */

class Database {

    private static ?PDO $pdo = null;

    public static function getPdo(): PDO {
        if (self::$pdo === null) {
            $dbPort = getenv('DB_PORT') ?: '';
            $dbHost = getenv('DB_HOST') ?: '';
            $dbName = getenv('DB_NAME') ?: '';
            $dbUser = getenv('DB_USER') ?: '';
            $dbPassword = getenv('DB_PASSWORD') ?: '';

            $dsn = "mysql:host=" . $dbHost . ";port=" . $dbPort . ";dbname=" . $dbName . ";charset=utf8mb4";

            self::$pdo = new PDO($dsn, $dbUser, $dbPassword);
        }
        return self::$pdo;
    }
}
