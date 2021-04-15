<?php
session_start();
	// Set default HTTP response of 'Not Found'
	$response['status'] = 404;
	$response['data'] = NULL;

	$url_array = explode('/', $_SERVER['REQUEST_URI']);

	//echo $_SERVER['REQUEST_URI'];

	//echo json_encode(explode('/', $_SERVER['REQUEST_URI']));

	array_shift($url_array); // remove first value as it's empty
	// remove 2nd and 3rd array, because it's directory
	array_shift($url_array); // 2nd = 'NativeREST'
	//array_shift($url_array); // 3rd = 'api'

	// get the action (resource, collection)
	$entityType = $url_array[0];
	// get the method
	$method = $_SERVER['REQUEST_METHOD'];

	//echo $action;

	$allHeaders = getallheaders();

	$data = json_decode(file_get_contents("php://input"));

	require_once('config/database.php');
	$database = new DataBase();
	$conncetion = $database->getConnection();

	switch ($entityType) {

	 	case 'products':
	 		require_once('productDao.php');
	 		$productDao = new ProductDao();
	 		if (count($url_array) > 1 && $url_array[1]) {
	 			$entityId = $url_array[1];
	 			//echo "Product with id $entityId";
	 			//print_r($productDao->findById($entityId));

	 			//print_r($productDao->echook($entityId));
	 		} else {
	 			//echo 'Products';
	 			$page = $data->page ?: 1;
	 			$pageSize = $data->pageSize ?: 20;
	 			$sortParameters = $data->sortParameters;
	 			$filterParameters = $data->filterParameters;
	 			//echo json_encode($data);
	 			//echo json_encode($filterParameters);
	 			//print_r($productDao->count($filterParameters));
	 			//print_r($productDao->findAll($page, $pageSize, $sortParameters, $filterParameters));

	 			$response = array();
	 			$totalNamber = $productDao->count($filterParameters);
	 			$allFoundProduct = $productDao->findAll($page, $pageSize, $sortParameters, $filterParameters);
	 			$response['totalNamber'] = $totalNamber;
	 			$response['products'] = $allFoundProduct;

	 			print_r(json_encode($response));			
	 		}
	 		break;

	 	case 'login':
	 		require_once("php-login-registration-api-master/login.php");
	 		break;

	 	case 'register':
	 		require_once("php-login-registration-api-master/register.php");
	 		break;

	 	case 'users':
	 		if (count($url_array) > 1 && $url_array[1]) {
	 			require_once('php-login-registration-api-master/middlewares/AuthChecker.php');
	 			$entityId = $url_array[1];
	 			$authChecker = new AuthChecker($allHeaders);
	 			$retrievedId = $authChecker->retrieveUserId();

	 			if (!$retrievedId) {
	 				echo "Tocken is not valid anymore";
	 				break;
	 			}

 				if ($retrievedId != $entityId) {
 					echo "SOSIBIBU";
 					break;
 				}
	 					
				$userDao = new UserDao();
	 			$user = $userDao->findById($entityId);
	 			if (count($url_array) > 2 && $url_array[2]) {
	 				$childEntityType = $url_array[2];
	 				if ($childEntityType != "cart") {
	 					break;
	 				}
	 				require_once ('cartItemDao.php');
	 				$cartItemDao = new cartItemDao();
	 				if ($method == 'GET') {
	 					print_r($cartItemDao->findByUserId($user->id));
	 				} else if ($method == 'POST'){
	 					$cartItem = new CartItem();
	 					$cartItem->userId = $data->userId;
	 					$cartItem->productId = $data->productId;
	 					$cartItem->quantity = $data->quantity;

	 					print_r($cartItemDao->addToCart($cartItem));
	 				} else if (count($url_array) > 3 && $url_array[3]) {
			 				if($method == 'PUT') {
			 					if (!$data->quantity) {
			 						echo "No correct data";
			 					}
			 					$productId = $url_array[3];
			 					$cartItem = new CartItem();
			 					$cartItem->userId = $user->id;
			 					$cartItem->productId = $productId;

			 					$cartItem->quantity = $data->quantity ?: 1;
								$cartItemDao->update($cartItem);	 					
	 						} else if($method == 'DELETE') {
			 					$productId = $url_array[3];
			 					$cartItem->userId = $user->id;
			 					$cartItem->productId = $productId;

								$cartItemDao->deleteFromCart($userId, $productId);	 	
			 				}
		 			} 

	 			} else {
	 				if ($method == 'GET') {	
		 				$user->password = null;
		 				echo json_encode($user);
		 			} else if ($method == 'PUT') {
		 				$user->fullName = $data->fullName ?: $user->fullName;
		 				$user->phoneNumber = $data->phoneNumber ?: $user->phoneNumber;
		 				$userDao->update($user);
		 			}
	 			
	 			}
		 	}
		 	break;
	 	
	 	default:
	 		break;
	}
 ?>