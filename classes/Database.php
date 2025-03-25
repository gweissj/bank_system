<?php
class Database {
    private $servername = "localhost:3308";
    private $username = "root";
    private $password = "";
    private $dbname = "bank - kuzbank";
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Ошибка подключения: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>