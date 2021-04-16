<?php

require_once('RequestHandler.php');
require_once('ProductService.php');

/**
 * 
 */
class ProductController implements RequestHandler
{
	private $productService;
	
	function __construct($productService)
	{
		$this->productService = $productService;
	}

	function handleRequest($relativeUrlParts, $method, $data) {
 		$productId = getOrNull($relativeUrlParts, 1);
 		if ($productId) {
 			return $this->findSingleProduct($productId);
 		}

 		return $this->findProducts($data);
	}

	private function findSingleProduct($productId) {
		return $this->productService->findRequiredById($productId);
	}

	private function findProducts($data) {
		$page = $data->page ?: 1;
		$pageSize = $data->pageSize ?: 20;
		$sortParameters = $this->completeSortParameters($data->sortParameters);
		$filterParameters = $this->completeFilterParameters($data->filterParameters);

 		return $this->productService->findPaged($page, $pageSize, $sortParameters, $filterParameters);
	}

	private function completeSortParameters($sortParameters) {
		if (!$sortParameters) {
			$sortParameters = new stdClass();
		}

		if (!$sortParameters->field) {
			$sortParameters->field = 'id';
		}

		if (!$sortParameters->direction) {
			$sortParameters->direction = 'ASC';
		}

		return $sortParameters;
	}

	private function completeFilterParameters($filterParameters) {
		if (!$filterParameters) {
 			$filterParameters = new stdClass();
 		}

 		if (!$filterParameters->priceRange) {
 			$filterParameters->priceRange = new stdClass();
 		}

 		if (!$filterParameters->priceRange->min) {
 			$filterParameters->priceRange->min = 0;
 		}

 		if (!$filterParameters->priceRange->max) {
 			$filterParameters->priceRange->max = 1000000;
 		}

 		return $filterParameters;
	}
}
?>