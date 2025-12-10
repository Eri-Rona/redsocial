<?php
class Database {
    private $host = "fdb1034.awardspace.net";
    private $db_name = "4698085_erimich";
    private $username = "4698085_erimich";
    private $password = "3hermanaserika"; // <-- ¡PON AQUÍ TU CONTRASEÑA DE AWARDSPACE!
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
