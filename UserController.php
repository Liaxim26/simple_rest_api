<?php

require_once('RequestProcessingException.php');
require_once('RequestHandler.php');

/**
 * 
 */
class UserController implements RequestHandler
{
	protected $userService;
	protected $authChecker;
	
	function __construct($userService, $authChecker)
	{
		$this->userService = $userService;
		$this->authChecker = $authChecker;
	}

	function handleRequest($relativeUrlParts, $method, $data) {
		$userId = getOrNull($relativeUrlParts, 1);
		if (!$userId) {
			throw new RequestProcessingException(404, "Not found");
		}
 			
		$this->authChecker->authorize($userId);

		$childEntityType = getOrNull($relativeUrlParts, 2);
		if ($childEntityType) {
			if ($childEntityType != "cart") {
				throw new RequestProcessingException(404, "Not found");
			}
			return $this->handleCard($userId, $relativeUrlParts, $method, $data);
		}
		
		if ($method == 'GET') {
			return $this->userService->findPublic($userId);
		} else if ($method == 'PUT') {
			$newFullName = $data->fullName;
			$newPhoneNumber = $data->phoneNumber;
			$this->userService->update($userId, $newFullName, $newPhoneNumber);
			return;
		}
	}

	private function handleCard($userId, $relativeUrlParts, $method, $data) {
		require_once ('cartItemDao.php');
		$cartItemDao = new CartItemDao();

 		$productId = getOrNull($relativeUrlParts, 3);
 		if ($productId) {
 			switch ($method) {
 				case 'PUT':
 					if (!$data->quantity) {
						echo "No correct data";
					}
					$cartItem = new CartItem();
					$cartItem->userId = $userId;
					$cartItem->productId = $productId;

					$cartItem->quantity = $data->quantity ?: 1;
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
			switch ($method) {
				case 'GET':
					return $cartItemDao->findByUserId($userId);

				case 'POST':
					$cartItem = new CartItem();
					$cartItem->userId = $userId;
					$cartItem->productId = $data->productId;
					$cartItem->quantity = $data->quantity;
					$cartItemDao->addToCart($cartItem);
					return;
				
				default:
					$this->notFound();
			}
		}
	}
}
?>