<?php
namespace Bd;

use PDO;
use PDOException;

class Conexion
{
    private PDO $pdo;

    public function __construct()
    {
        $host = 'localhost';
        $database = 'redsocial';
        $user = 'root';
        $password = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$database;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $exception) {
            throw new PDOException('No se pudo conectar con la base de datos: ' . $exception->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
?>
