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

function isBlankOrNull($value) {
    return $value && empty(trim($value));
}

// INCLUDING DATABASE AND MAKING OBJECT
require_once("config/database.php");
$db_connection = new Database();
$conn = $db_connection->getConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = msg(0,404,'Page Not Found!');
} elseif(isBlankOrNull($data->email) 
    || isBlankOrNull($data->username) 
    || isBlankOrNull($data->password)
    ) {

    $fields = ['fields' => ['email', 'username','password']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);
} else {
    
    $fullName = trim($data->fullName ?: "Unnamed");
    $email = trim($data->email);
    $username = trim($data->username);
    $password = trim($data->password);
    $phoneNumber = trim($data->phoneNumber);


    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $returnData = msg(0,422,'Invalid Email Address!');
    } elseif(strlen($password) < 8) {
        $returnData = msg(0,422,'Your password must be at least 8 characters long!');
    } elseif(strlen($fullName) < 3) {
        $returnData = msg(0,422,'Your name must be at least 3 characters long!');
    } else {
        try{

            require_once('UserDao.php');
            $userDao = new UserDao();

            if($userDao->findByEmail($email)) {
                $returnData = msg(0,422, 'This E-mail already in use!');
            } elseif ($userDao->findByUsername($username)) {
                $returnData = msg(0,422, 'This username already in use!');
            } else {
                $user = new User();
                $user->fullName = $fullName;
                $user->email = $email;
                $user->username = $username;
                $user->password = $password;
                $user->phoneNumber = $phoneNumber;
                
                $userDao->persist($user);

                $returnData = msg(1,201,'You have successfully registered.');
            }
        }
        catch(PDOException $e){
            echo $e;
            $returnData = msg(0,500,$e->getMessage());
        }
    }
}

echo json_encode($returnData);