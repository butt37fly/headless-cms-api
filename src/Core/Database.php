<?php

namespace App\Core;

use PDO;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (!isset($_ENV['DB_HOST']) || !isset($_ENV['DB_NAME']) || !isset($_ENV['DB_USER']) || !isset($_ENV['DB_PASS'])) {
            error_log("Alguna/s variable/s de entorno no existen.");
            throw new RuntimeException("Error de conexión en la base de datos");
        }

        if (self::$connection === null) {

            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            try {
                self::$connection = new PDO(
                    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    $options
                );

                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
                throw new RuntimeException("Error de conexión en la base de datos.");
            }
        }

        return self::$connection;
    }

    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}
