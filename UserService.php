<?php

require_once('utilities.php');

/**
 * 
 */
class UserService
{
	protected $userDao;

	function __construct($userDao)
	{
		$this->userDao = $userDao;
	}

	function findPublic($userId) {
		$user = $this->userDao->findById($userId);
		$user->password = null;
		return $user;
	}

	function update($userId, $newFullName, $newPhoneNumber) {
		$existingUser = $this->userDao->findById($userId);

		if ($newFullName && !empty($newFullName)) {
			$existingUser->fullName = $newFullName;
		}

		if ($newPhoneNumber && !empty($newPhoneNumber)) {
			$existingUser->phoneNumber = $newPhoneNumber;
		}

		$this->userDao->update($existingUser);
	}

	function register($newUser) {
		if(!filter_var($newUser->email, FILTER_VALIDATE_EMAIL)) {
		    throw new RequestProcessingException(422, 'Invalid Email Address!');
		}
		if(strlen($newUser->password) < 8) {
		    throw new RequestProcessingException(422, 'Your password must be at least 8 characters long!');
		}

	    if ($this->userDao->findByEmail($newUser->email)) {
	        throw new RequestProcessingException(422, 'This E-mail already in use!');
	    }
	    if ($this->userDao->findByUsername($newUser->username)) {
	        throw new RequestProcessingException(422, 'This username already in use!');
	    };

	    $newUser->password = password_hash($newUser->password, PASSWORD_DEFAULT);
	    
	    $this->userDao->persist($newUser);
	}
}
?>