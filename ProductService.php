<?php

require_once('productDao.php');
require_once('utilities.php');
require_once('RequestProcessingException.php');

/**
  * 
  */
 class ProductService
 {
 	private $dao;
 	
 	function __construct($productDao)
 	{
 		$this->$dao = $productDao;
 	}

 	function findRequiredById($id) {
 		$foundProduct = $this->$dao->findById($id);
 		if ($foundProduct) {
 			return $foundProduct;
 		}

 		throw new RequestProcessingException(300, "No product with id $id found");
 	}

 	function findPaged($page, $pageSize, $sortParameter, $filterParameters) {
 		if (!$page) {
 			$page = 1;
 		} else if ($page <= 0) {
 			throw new RequestProcessingException(305, "Validation failed for page parameter: value should be positive");
 		}

 		if (!pageSize) {
 			$pageSize = 20;
 		} else if ($page <= 0) {
 			throw new RequestProcessingException(305, "Validation failed for pageSize parameter: value should be positive");
 		}


 		if (!$sortParameter) {
 			$sortParameter = "id";
 		}

 		if (!$filterParameters) {
 			$filterParameters = new stdObject();
 		}

 		if (isNullOrEmpty($filterParameters->category)) {
 			throw new RequestProcessingException(307, "Missing required parameter category");
 		}

 		if (!$filterParameters->priceRange) {
 			$filterParameters->priceRange = new stdObject();
 		}

 		if (!$filterParameters->priceRange->min) {
 			$filterParameters->priceRange->min = 0;
 		}

 		if (!$filterParameters->priceRange->max) {
 			$filterParameters->priceRange->max = 1000000;
 		}

 		$totalNumber = $this->$dao->count($filterParameters);
 		$products = $this->$dao->findAll($page, $pageSize, $sortParameter, $filterParameters);

 		$result = (object) [
 			'totalNumber' => $totalNumber,
 			'products' => $products
 		];

 		return $result;
 	}

 } 
?>