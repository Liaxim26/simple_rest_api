<?php

require_once('config/database.php');
require_once('user.php');

class UserDao {
    // соединение с БД и таблицей 'categories' 
    private $connection;
    private $table_name = "user";

    public function __construct() {
        $this->connection = (new Database())->getConnection();
    }

    function findById($id) {
        return $this->findByField('id', $id);
    }

    function findByEmail($email) {
        return $this->findByField('email', $email);
    }

    function findByUsername($username) {
        return $this->findByField('username', $username);
    }

    function persist($user) {
        $query = "INSERT INTO " . $this->table_name . " 
        (`full_name`, `email`, `username`, `password`, `phone_number`)
        VALUES (:fullName, :email, :username, :password, :phoneNumber)";
        $statement = $this->connection->prepare($query);
        $statement->bindValue(':fullName', htmlspecialchars(strip_tags($user->fullName)), PDO::PARAM_STR);
        $statement->bindValue(':email', $user->email, PDO::PARAM_STR);
        $statement->bindValue(':username', $user->username, PDO::PARAM_STR);
        $statement->bindValue(':password', password_hash($user->password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $statement->bindValue(':phoneNumber', $user->phoneNumber, PDO::PARAM_STR);

        $statement->execute();  
    }

    function update($user) {
        $query = "UPDATE " . $this->table_name . " 
        SET
            `full_name` = :fullName,
            `email` = :email,
            `username` = :username,
            `password` = :password,
            `phone_number` = :phoneNumber
        WHERE id = :id";
        $statement = $this->connection->prepare($query);
        $statement->bindValue(':id', $user->id, PDO::PARAM_INT);
        $statement->bindValue(':fullName', htmlspecialchars(strip_tags($user->fullName)), PDO::PARAM_STR);
        $statement->bindValue(':email', $user->email, PDO::PARAM_STR);
        $statement->bindValue(':username', $user->username, PDO::PARAM_STR);
        $statement->bindValue(':password', password_hash($user->password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $statement->bindValue(':phoneNumber', $user->phoneNumber, PDO::PARAM_STR);

        $statement->execute();  
    }

    private function findByField($fieldName, $fieldValue) {
       $query = "
            SELECT
                id, full_name, email, username, password, phone_number
            FROM
                " . $this->table_name . "
            WHERE 
                $fieldName = :fieldValue";

        // подготовка запроса 
        $statement = $this->connection->prepare($query);

        // привязка значений 
        $statement->bindParam(":fieldValue", $fieldValue);


        // выполняем запрос 
        $statement->execute();

        // получаем извлеченную строку 
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo "oh";
        // установим значения свойств объекта 
            $user = $this->convertToUser($row);
            return $user;        
        }

        return null;
    }

    private function convertToUser($row) {
        $user = new User();
        $user->id = $row['id'];
        $user->fullName = $row['full_name'];
        $user->email = $row['email'];
        $user->username = $row['username'];
        $user->password = $row['password'];
        $user->phoneNumber = $row['phone_number'];
        return $user;
    }
}
?>