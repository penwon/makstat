<?php
global $dbcnx;
global $db_name;
$db_host="localhost";
$db_user="root";
$db_pass="qwertyasd123";
$db_name="test";//имя создаваемой базы данных
$defaultpass = "admpwd"; //пароль администратора по умолчанию

$dbcnx = mysql_connect($db_host, $db_user, $db_pass);
mysql_query("SET NAMES 'utf8'") or printBDError("Ошибка при установлении кодировки БД");
if (!$dbcnx){
	printBDError("Ошибка соединения с сервером БД");
}

//функция, закрывающая соединение
function closeBDConnection(){
	global $dbcnx;
	mysql_close($dbcnx) or printBDError("Невозможно закрыть соединение с БД");
}

//функция, указывающая серверу, с какой БД идет работа
function selectBD(){
	global $dbcnx;
	global $db_name;
	if (!mysql_select_db($db_name, $dbcnx)){
		printBDError("База данных недоступна");
	}
}

?>
