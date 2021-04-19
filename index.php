<?php

require_once('RequestDispatcher.php');

//header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");
/*header("Access-Control-Request-Headers: Content-Type");
header("Access-Control-Request-Method: OPTIONS");*/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Authorization");


$url_array = explode('/', explode("?", $_SERVER['REQUEST_URI'])[0]);
array_shift($url_array);
array_shift($url_array);

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
	
} else {

	$allHeaders = getallheaders();
	if ($method == 'GET') {
		$data = (object) $_GET;
	} else {
		$data = json_decode(file_get_contents("php://input"));
	}

	try {
		$requestDispatcher = new RequestDispatcher($url_array, $allHeaders, $data, $method);
		$result = $requestDispatcher->dispatch();
		http_response_code(200);
		echo json_encode($result);
	} catch (RequestProcessingException $e) {
		$response = ['message' => $e->getMessage()];
		http_response_code($e->getHttpCode());
		echo json_encode($response);
	} catch (Exception $e) {
		$response = ['message' => $e->getMessage()];
		http_response_code(501);
		echo json_encode($response);
	}
}
?>