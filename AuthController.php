<?php

require_once('RequestProcessingException.php');
require_once('RequestHandler.php');

/**
  * 
  */
 class AuthController implements RequestHandler
 {
 	protected $authChecker;
 	protected $userDao;
 	
 	function __construct($authChecker, $userDao)
 	{
 		$this->authChecker = $authChecker;
 		$this->userDao = $userDao;
 	}

 	function handleRequest($realtiveUrlParts, $method, $data) {
		if($method != "POST") {
			throw new RequestProcessingException(404, "Not found");
		}

		$login = $data->login;
		if (!$login || empty($login)) {
			throw new RequestProcessingException(302, "Missing required property login");
		}

		$password = $data->password;
		if (!$password || empty($password)) {
			throw new RequestProcessingException(302, "Missing required property password");
		}

		$login = trim($data->login);
		$password = trim($data->password);


		$user = $this->userDao->findByUsername($login);
		if (!$user) {
			$user = $this->userDao->findByEmail($login);
		}

		// IF THE USER IS FOUNDED BY EMAIL
		if($user) {
			$passwordMatch = password_verify($password, $user->password);

		// VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
		// IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
			if($passwordMatch) {
			    $jwt = new JwtHandler();
			    $token = $jwt->_jwt_encode_data(
			        'http://localhost/php_auth_api/',
			        array("user_id" => $user->id)
			    );

			    $response = ['token'=>$token];
			    
			    return $response;
			} else {
			    throw new RequestProcessingException(420, 'Password does not match');
			}

		} else {
			throw new RequestProcessingException(422, 'User not found');
		}
 	}
 } 
?>