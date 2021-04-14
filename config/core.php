<?php
// показывать сообщения об ошибках 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// URL домашней страницы 
$home_url="http://localhost/api/";

// страница указана в параметре URL, страница по умолчанию одна 
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// установка количества записей на странице 
$records_per_page = 5;

// расчёт для запроса предела записей 
$from_record_num = ($records_per_page * $page) - $records_per_page;

$Url = $_GET['url_param'];
$params = explode('/', $Url);
$method = $_SERVER['REQUEST_METHOD'];

$Module = $params[0];
$entityId = $params[2];



?>