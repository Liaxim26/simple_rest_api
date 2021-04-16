<?php

function isNullOrEmpty($string) {
	return !$string || empty(trim($string));
}

function getOrNull($array, $index) {
	if (count($array) > $index) {
		return $array[$index];
	}

	return null;
}

?>