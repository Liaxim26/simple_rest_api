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

	private function handleProducts() {
		require_once('ProductService.php');
 		$productDao = new ProductDao();
 		$productService = new ProductService($productDao);
 		$entityId = getOrNull($this->relativeUrlParts, 1);
 		if ($entityId) {
 			return $productService->findRequiredById($entityId);
 		}

 		$response = $productService->findPaged($data->page, $data->pageSize, $data->sortParameter, $data->filterParameters);
 		return $response;
	}

	private function handleUsers() {
		$userId = getOrNull($this->relativeUrlParts, 1);
 		if (!$userId) {
 			$this->notFound();
 		}
 			
		$this->authentificate($userId, $this->headers);


		$childEntityType = getOrNull($this->relativeUrlParts, 2);
		if ($childEntityType) {
			if ($childEntityType != "cart") {
				throw new RequestProcessingException(404, "Not found");
			}
			return $this->handleCard($userId);
		}
		
		$userDao = new UserDao();
		$user = $userDao->findById($userId);
		if ($this->method == 'GET') {
			$user->password = null;
			return $user;
		} else if ($this->method == 'PUT') {
			$user->fullName = $this->data->fullName ?: $user->fullName;
			$user->phoneNumber = $this->data->phoneNumber ?: $user->phoneNumber;
			$userDao->update($user);
			return;
		}
		
		$this->notFound();
 	}

 	private function handleCard($userId) {
		require_once ('cartItemDao.php');
		$cartItemDao = new CartItemDao();

 		$productId = getOrNull($this->relativeUrlParts, 3);
 		if ($productId) {
 			switch ($this->method) {
 				case 'PUT':
 					if (!$this->data->quantity) {
						echo "No correct data";
					}
					$cartItem = new CartItem();
					$cartItem->userId = $userId;
					$cartItem->productId = $productId;

					$cartItem->quantity = $this->data->quantity ?: 1;
					$cartItemDao->update($cartItem);
 					return;

 				case 'DELETE':
 					$cartItem->userId = $userId;
					$cartItem->productId = $productId;
					$cartItemDao->deleteFromCart($userId, $productId);
 					return;
 				
 				default:
 					$this->notFound();
 			}
		} else {
			switch ($this->method) {
				case 'GET':
					return $cartItemDao->findByUserId($userId);

				case 'POST':
					$cartItem = new CartItem();
					$cartItem->userId = $userId;
					$cartItem->productId = $this->data->productId;
					$cartItem->quantity = $this->data->quantity;
					$cartItemDao->addToCart($cartItem);
					return;
				
				default:
					$this->notFound();
			}
		}
	}

	private function notFound() {
		throw new RequestProcessingException(404, "Not Found");
	}

	private function authentificate($userId, $allHeaders) {
		
		$authChecker = new AuthChecker($allHeaders);
		$retrievedId = $authChecker->retrieveUserId();

		if (!$retrievedId) {
			throw new RequestProcessingException(402, "Bad token");
		}

		if ($retrievedId != $userId) {
			throw new RequestProcessingException(406, "Token provides data on other user");
		}
	}
}
?>