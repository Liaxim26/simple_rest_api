<?php

require_once('RequestDispatcher.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$url_array = explode('/', $_SERVER['REQUEST_URI']);
array_shift($url_array);
array_shift($url_array);

$method = $_SERVER['REQUEST_METHOD'];

$allHeaders = getallheaders();

$data = json_decode(file_get_contents("php://input"));

try {
	$requestDispatcher = new RequestDispatcher($url_array, $allHeaders, $data, $method);
	$result = $requestDispatcher->dispatch();
	http_response_code(200);
	echo json_encode($result);
} catch (RequestProcessingException $e) {
	$response = ['message' => $e->getMessage()];
	http_response_code($e->getHttpCode());
	echo json_encode($response);
}

?>