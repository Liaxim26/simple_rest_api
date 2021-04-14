<?php

require_once('config/database.php');
require_once('product.php');

class ProductDAO {
    // соединение с БД и таблицей 'categories' 
    private $connection;
    private $table_name = "product";

    public function __construct() {
        $this->connection = (new Database())->getConnection();
    }

    function findById($id) {
       $query = "SELECT
                id, name, category, price, image
            FROM
                " . $this->table_name . "
            WHERE id == :id";

        // подготовка запроса 
        $statement = $this->connection->prepare($query);

        // привязка значений 
        $statement->bindParam(":id", $id);

        // выполняем запрос 
        $statement->execute();

        // получаем извлеченную строку 
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        // установим значения свойств объекта 
        $product = convertToProduct($row);

        return $product;
    }

    function findAll($page, $pageSize, $sortParameters, $filterParameters) {

        $query = "SELECT
                id, name, category, price, image
            FROM
                " . $this->table_name . "
            WHERE
                name like '%:name%' and
                category = :category and
                price between :minPrice and :maxPrice
            ORDER BY :sortParameter
            ";

        $statement = $this->connection->prepare($query);

        $statement->bindParam(":name", $filterParameters->name);
        $statement->bindParam(":category", $filterParameters->category);
        $statement->bindParam(":minPrice", $filterParameters->priceRange->min);
        $statement->bindParam(":maxPrice", $filterParameters->priceRange->max);

        $statement->execute();

        $products = array();


        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $product = convertToProduct($row);
            array_push($products_arr, $product);
        }
    }

    private function convertToProduct($row) {

    }
}
?>