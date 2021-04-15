<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

// INCLUDING DATABASE AND MAKING OBJECT
require_once("config/database.php");
$db_connection = new Database();
$conn = $db_connection->getConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0,404,'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif(!isset($data->fullName) 
    || !isset($data->email) 
    || !isset($data->username) 
    || !isset($data->password)
    || !isset($data->phoneNumber) 
    || empty(trim($data->fullname))
    || empty(trim($data->email))
    || empty(trim($data->userName))
    || empty(trim($data->password))
    || empty(trim($data->phoneNumber))
    ):

    $fields = ['fields' => ['fullName','email', 'username','password', 'phoneNumber']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $fullName = trim($data->fullName);
    $email = trim($data->email);
    $username = trim($data->username);
    $password = trim($data->password);
    $phoneNumber = trim($data->phoneNumber);


    if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0,422,'Invalid Email Address!');
    
    elseif(strlen($password) < 8):
        $returnData = msg(0,422,'Your password must be at least 8 characters long!');

    elseif(strlen($fullName) < 3):
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');

    else:
        try{

            $check_email = "SELECT `email` FROM `users` WHERE `email`=:email";
            $check_email_stmt = $conn->prepare($check_email);
            $check_email_stmt->bindValue(':email', $email,PDO::PARAM_STR);
            $check_email_stmt->execute();

            if($check_email_stmt->rowCount()):
                $returnData = msg(0,422, 'This E-mail already in use!');
            
            else:
                $insert_query = "INSERT INTO `user`(`full_name`,`email`,`username`, `password`, `phone_number`) VALUES(:full_name,:email,:username,:password,:phone_number)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':full_name', htmlspecialchars(strip_tags($full_name)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email,PDO::PARAM_STR);
                $insert_stmt->bindValue(':username', $username,PDO::PARAM_STR);
                $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT),PDO::PARAM_STR);
                $insert_stmt->bindValue(':phone_number', $phone_number,PDO::PARAM_STR);

                $insert_stmt->execute();

                $returnData = msg(1,201,'You have successfully registered.');

            endif;

        }
        catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
    endif;
    
endif;

echo json_encode($returnData);