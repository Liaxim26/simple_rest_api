<?php
	// Set default HTTP response of 'Not Found'
	$response['status'] = 404;
	$response['data'] = NULL;

	$url_array = explode('/', $_SERVER['REQUEST_URI']);

	echo $_SERVER['REQUEST_URI'];

	echo json_encode(explode('/', $_SERVER['REQUEST_URI']));

	array_shift($url_array); // remove first value as it's empty
	// remove 2nd and 3rd array, because it's directory
	array_shift($url_array); // 2nd = 'NativeREST'
	//array_shift($url_array); // 3rd = 'api'

	// get the action (resource, collection)
	$entityType = $url_array[0];
	// get the method
	$method = $_SERVER['REQUEST_METHOD'];

	echo $action;

	$data = json_decode(file_get_contents("php://input"));

	switch ($entityType) {
	 	case 'products':
	 		require_once('productDao.php');
	 		$productDao = new ProductDao();
	 		if (count($url_array) > 1 && $url_array[1]) {
	 			$entityId = $url_array[1];
	 			echo "Product with id $entityId";
	 		} else {
	 			echo 'Products';
	 			$page = $data->page ?: 1;
	 			$pageSize = $data->pageSize ?: 20;
	 			$sortParamters = $data->sortParamters;
	 			$filterParameters = $data->filterParameters;
	 			echo json_encode($filterParameters);

	 		}
	 		break;

	 	case 'auth':
	 		echo "Auth";
	 		break;

	 	case 'register':
	 		echo "Registeration";
	 		break;

	 	case 'users':
	 		echo "Users";
	 		break;
	 	
	 	default:
	 		break;
	 }
 ?>