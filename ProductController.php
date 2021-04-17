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
		$page = (int) $data->page ?: 1;
		$pageSize = (int) $data->pageSize ?: 20;
		$sortParameters = $this->createSortParameters($data->sortField, $data->sortDirection);
		$filterParameters = $this->createFilterParameters($data->name, $data->category, $data->minPrice, $data->maxPrice);
 		return $this->productService->findPaged($page, $pageSize, $sortParameters, $filterParameters);
	}

	private function createSortParameters($field, $direction) {
		$sortParameters = new stdClass();

		$sortParameters->field = $field ?: 'id';
		$sortParameters->direction = $direction ?: 'ASC';

		return $sortParameters;
	}

	private function createFilterParameters($name, $category, $minPrice, $maxPrice) {
		$filterParameters = new stdClass();

		$filterParameters->name = $name ?: "";
		$filterParameters->category = $category;

 		$filterParameters->priceRange = new stdClass();
 		$filterParameters->priceRange->min = $minPrice ?: 0;
 		$filterParameters->priceRange->max = $maxPrice ?: 1000000;

 		return $filterParameters;
	}
}
?>