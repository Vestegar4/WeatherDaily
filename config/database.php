<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $host = $_ENV['DB_HOST'] ?? '';
            $db_name = $_ENV['DB_NAME'] ?? '';
            $username = $_ENV['DB_USER'] ?? '';
            $password = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4";

            $this->conn = new PDO($dsn, $username, $password);
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $exception) {
            die("<h3>Database Connection Error:</h3> " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>