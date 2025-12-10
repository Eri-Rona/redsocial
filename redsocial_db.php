<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;

    public function __construct() {
        if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
            // Configuración LOCAL (XAMPP)
            $this->host = "localhost";
            $this->db_name = "redsocial";
            $this->username = "root";
            $this->password = "";
        } else {
            // Configuración PRODUCCIÓN (AwardSpace)
            $this->host = "fdb1034.awardspace.net";
            $this->db_name = "4698085_erimich";
            $this->username = "4698085_erimich";
            $this->password = "3hermanaserika"; 
        }
    }
    public $conn;
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
