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

require __DIR__.'/classes/JwtHandler.php';

require_once("config/database.php");
$db_connection = new Database();
$conn = $db_connection->getConnection();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if($_SERVER["REQUEST_METHOD"] != "POST") {
    $returnData = msg(0,404,'Page Not Found!');
} elseif(!isset($data->login) 
    || !isset($data->password)
    || empty(trim($data->login))
    || empty(trim($data->password))
    ) {

    $fields = ['fields' => ['login','password']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);
} else {
    $login = trim($data->login);
    $password = trim($data->password);

    echo $login;

    try{
        require_once('UserDao.php');

        $userDao = new UserDao();

        $user = $userDao->findByUsername($login);
        if (!$user) {
            $user = $userDao->findByEmail($login);
        }

        // IF THE USER IS FOUNDED BY EMAIL
        if($user) {
            $check_password = password_verify($password, $user->password);

            // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
            // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
            if($check_password) {

                $jwt = new JwtHandler();
                $token = $jwt->_jwt_encode_data(
                    'http://localhost/php_auth_api/',
                    array("user_id" => $user->id)
                );
                
                $returnData = [
                    'success' => 1,
                    'message' => 'You have successfully logged in.',
                    'token' => $token
                ];
            } else {
                $returnData = msg(0,422,'Invalid Password!');
            }

        } else {
            $returnData = msg(0,422,'Invalid Email Address!');
        }
    }
    catch(PDOException $e){
        $returnData = msg(0,500,$e->getMessage());
    }
}

echo json_encode($returnData);