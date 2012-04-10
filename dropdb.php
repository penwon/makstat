<?php

//подгружаем библиотеку функций
require_once("funcs.php");
header('Content-Type: text/html; charset=utf-8'); 
//соединяемся с БД
require_once("config.php");

$sql= "DROP DATABASE `".$db_name."`;";
mysql_query($sql) or printBDError("Невозможно удалить БД");
?>
