<?php

require_once('utilities.php');
require_once('RequestHandler.php');
require_once('RequestProcessingException.php');

class RegisterController implements RequestHandler {
	private $userService;

	function __construct($userService) {
		$this->userService = $userService;
	}

	function handleRequest($relativeUrlPart, $method, $data) {
		if ($method != 'POST') {
			throw new RequestProcessingException(404, "Not found.");
		}

		if(isNullOrEmpty($data->email)) {
			throw new RequestProcessingException(406, "Missing required field email");
		}
		if(isNullOrEmpty($data->username)) {
			throw new RequestProcessingException(406, "Missing required field username");
		}
		if(isNullOrEmpty($data->password)) {
			throw new RequestProcessingException(406, "Missing required field password");
		}

		$newUser = new User();
	    $newUser->fullName = trim($data->fullName);
	    $newUser->email = trim($data->email);
	    $newUser->username = trim($data->username);
	    $newUser->password = trim($data->password);
	    $newUser->phoneNumber = trim($data->phoneNumber);

	    $this->userService->register($newUser);
	}
}
?>