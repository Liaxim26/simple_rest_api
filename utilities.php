<?php

function isNullOrEmpty($string) {
	return !$string || empty(trim($string));
}

function getOrNull($array, $index) {
	if (isset($array[$index])) {
		return $array[$index];
	}

	return null;
}

function notEmptyOr($string, $defaultValue) {
	if ($string && !empty($string)) {
		return $string;
	}

	return $defaultValue;
}

?>