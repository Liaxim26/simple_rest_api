<?php
class Database {

    // укажите свои учетные данные базы данных 
    private $host = "localhost";
    private $db_name = "cloud_shop";
    private $username = "root";
    private $password = "";
    public $conn;

    // получаем соединение с БД 
    public function getConnection() {

        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>