<?php

require_once('utilities.php');
require_once('UserDao.php');
require_once('php-login-registration-api-master/middlewares/AuthChecker.php');
require_once('AuthController.php');
require_once('ProductController.php');
require_once('RegisterController.php');
require_once('UserService.php');
require_once('UserController.php');
require_once('RequestProcessingException.php');

class RequestDispatcher {
	protected $relativeUrlParts;
	protected $headers;
	protected $data;
	protected $method;

	function __construct($relativeUrlParts, $headers, $data, $method) {
		$this->relativeUrlParts = $relativeUrlParts;
		$this->headers = $headers;
		$this->data = $data;
		$this->method = $method;
	}

	function dispatch() {
		$entityType = getOrNull($this->relativeUrlParts, 0);
		if (!$entityType) {
			$this->notFound();
		}

		switch ($entityType) {
		 	case 'products':
		 		$controller = new ProductController(new ProductService(new ProductDao()));
		 		break;

		 	case 'login':
		 		$userDao = new UserDao();
		 		$authChecker = new AuthChecker($userDao, $this->headers);
		 		$controller = new AuthController($authChecker, $userDao);
		 		break;

		 	case 'register':
		 		$userDao = new UserDao();
		 		$userService = new UserService($userDao);
		 		$controller = new RegisterController($userService);
		 		break;

		 	case 'users':
		 		$userDao = new UserDao();
		 		$authChecker = new AuthChecker($userDao, $this->headers);
		 		$userService = new UserService($userDao);
		 		$controller = new UserController($userService, $authChecker);
		 		break;
		 	
		 	default:
		 		$this->notFound();
	 	}

	 	return $controller->handleRequest($this->relativeUrlParts, $this->method, $this->data);
	}
}
?>