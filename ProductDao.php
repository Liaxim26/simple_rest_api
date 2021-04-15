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
       $query = "
            SELECT
                id, name, price, image, category
            FROM
                " . $this->table_name . "
            WHERE 
                id = :id";

        // подготовка запроса 
        $statement = $this->connection->prepare($query);

        // привязка значений 
        $statement->bindParam(":id", $id);


        // выполняем запрос 
        $statement->execute();

        // получаем извлеченную строку 
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        // установим значения свойств объекта 
        $products = $this->convertToProduct($row);

        return $products;    
    }

    function count($filterParameters) {

        $query = "SELECT
                count(*)
            FROM
                " . $this->table_name . "
            WHERE
                name like concat('%',:name,'%') and
                category = :category and
                price between :minPrice and :maxPrice     
            ";

        $statement = $this->connection->prepare($query);

        $statement->bindParam(":name", $filterParameters->name);
        $statement->bindParam(":category", $filterParameters->category);
        $statement->bindParam(":minPrice", $filterParameters->priceRange->min, PDO::PARAM_INT);
        $statement->bindParam(":maxPrice", $filterParameters->priceRange->max, PDO::PARAM_INT);

        $statement->execute();

        $number = $statement->fetchColumn();


        return $number;
    }

    function findAll($page, $pageSize, $sortParameters, $filterParameters) {

        $query = "SELECT
                id, name, category, price, image
            FROM
                " . $this->table_name . "
            WHERE
                name like concat('%',:name,'%') and
                category = :category and
                price between :minPrice and :maxPrice
            ORDER BY $sortParameters
            LIMIT :number
            OFFSET :start 
            ";

        $statement = $this->connection->prepare($query);

       
        $start = $pageSize * ($page-1);

        $statement->bindParam(":name", $filterParameters->name);
        $statement->bindParam(":category", $filterParameters->category);
        $statement->bindParam(":minPrice", $filterParameters->priceRange->min, PDO::PARAM_INT);
        $statement->bindParam(":maxPrice", $filterParameters->priceRange->max, PDO::PARAM_INT);
        $statement->bindParam(":start", $start, PDO::PARAM_INT); 
        $statement->bindParam(":number", $pageSize, PDO::PARAM_INT); 

        $statement->execute();

        $products = array();


        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $product = $this->convertToProduct($row);
            array_push($products, $product);

        }
        return $products;
    }

    private function convertToProduct($row) {
        $product = new Product();
        $product->id = $row['id'];
        $product->name = $row['name'];
        $product->category = $row['category'];
        $product->price = $row['price'];
        $product->image = $row['image'];
        return $product;
    }
}
?>