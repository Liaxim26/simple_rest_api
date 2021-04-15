<?php

require_once('config/database.php');
require_once('cartItem.php');

class CartItemDAO {
    // соединение с БД и таблицей 'categories' 
    private $connection;
    private $table_name = "card_item";

    public function __construct() {
        $this->connection = (new Database())->getConnection();
    }

    function findByUserId($id) {
        $query = "
            SELECT
                user_id, product_id, quantity
            FROM
                " . $this->table_name . "
            WHERE 
                user_id = :id";

        // подготовка запроса 
        $statement = $this->connection->prepare($query);

        // привязка значений 
        $statement->bindParam(":id", $id);

        // выполняем запрос 
        $statement->execute();

        $cartItems = array();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $cartItem = $this->convertToCartItem($row);
            array_push($cartItems, $cartItem);
        }

        return $cartItems;    
    }

    function checkCartItem($userId, $productId) {
        $query = "SELECT
                count(*)
            FROM
                " . $this->table_name . "
            WHERE
                user_id = :userId and
                product_id = :productId      
            ";
        $statement = $this->connection->prepare($query);

        $statement->bindParam(":userId", $userId, PDO::PARAM_INT);
        $statement->bindParam(":productId", $productId, PDO::PARAM_INT);

        $statement->execute();

        $number = $statement->fetchColumn();


        return $number != 0;
    }

    function addToCart($cartItem) {

        if ($this->checkCartItem($cartItem->userId, $cartItem->productId)){
            echo "This product is alredy preset in the cart!";
            return;
        }

        $query = "INSERT INTO " . $this->table_name . " 
        (`user_id`, `product_id`, `quantity`)
        VALUES (:userId, :productId, :quantity)";
        
        $statement = $this->connection->prepare($query);
        $statement->bindValue(':userId', $cartItem->userId, PDO::PARAM_INT);
        $statement->bindValue(':productId', $cartItem->productId, PDO::PARAM_INT);
        $statement->bindValue(':quantity', $cartItem->quantity, PDO::PARAM_INT);

        $statement->execute();      
    }

    function update($cartItem) {
        $query = "UPDATE " . $this->table_name . " 
        SET
            `quantity` = :quantity
        WHERE user_id = :userId 
        AND product_id = :productId";

        $statement = $this->connection->prepare($query);
        $statement->bindValue(':quantity', $cartItem->quantity, PDO::PARAM_INT);
        $statement->bindValue(':userId', $cartItem->userId, PDO::PARAM_INT);
        $statement->bindValue(':productId', $cartItem->productId, PDO::PARAM_INT);

        $statement->execute();  
    }

    function deleteFromCart($userId, $productId) {
         if ($this->checkCartItem($userId, $productId)){
            $query = "DELETE FROM " . $this->table_name . " 
            WHERE user_id = :userId 
            AND product_id = :productId";
            $statement = $this->connection->prepare($query);
            $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
            $statement->bindValue(':productId', $productId, PDO::PARAM_INT);
            $statement->execute();     
        }    
    }
/*
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
*/
    private function convertToCartItem($row) {
        $cartItem = new CartItem();
        $cartItem->userId = $row['user_id'];
        $cartItem->productId = $row['product_id'];
        $cartItem->quantity = $row['quantity'];

        return $cartItem;
    }
}
?>