<?php
header('Content-Type: text/html; charset=utf-8'); 
//подгружаем библиотеку функций
require_once("funcs.php");

printHTMLHead("Скрипт установки");

//соединяемся с БД
require_once("config.php");

//если установка была выполнена успешно, то просто выводим сообщение
if (isset($_GET['done']) && $_GET['done']=='1'){
	echo <<<EOF
	<font color="green" size="5"><b>Установка выполнена успешно!!!</b></font><br />
	Не забудьте <b>переименовать файл <font color="red">"setup.php"!</font></b><br />
	Теперь можно приступить к <a href="index.php">работе</a>.<br/>
	Логин администраторa: <b>admin</b></br>
	Пароль по-умолчанию: <b>$defaultpass</b>
EOF;
	printHTMLFoot();
	exit();
	closeBDConnection();
}

echo <<<EOF
Создаем базу данных...<br/>
EOF;
$db="CREATE DATABASE `".$db_name."`;"; //создаем БД
mysql_query($db) or printBDError("Невозможно создать БД");
echo "База данных <font color=\"green\"><b>успешно</b></font> создана! <br/>";
selectBD();

//Создаем таблицу users
$table =<<<EOF
  CREATE TABLE users (
    id_user INT NOT NULL AUTO_INCREMENT,
    name TINYTEXT NOT NULL,
    passw TINYTEXT NOT NULL,
    PRIMARY KEY (id_user)
) TYPE=MyISAM, default charset = utf8;
EOF;
mysql_query($table) or printBDError("Невозможно создать таблицу 'users'");
echo "Таблица 'users' <font color=\"green\"><b>успешно</b></font> создана! <br/>";

//создаем таблицу groups
$table =<<<EOF
  CREATE TABLE groups (
    id_group INT NOT NULL AUTO_INCREMENT,
    name TINYTEXT NOT NULL,
    col_type enum('integer', 'float') NOT NULL default 'float',
    PRIMARY KEY (id_group)
  )TYPE=MyISAM, default charset = utf8;
EOF;
mysql_query($table) or printBDError("Невозможно создать таблицу 'groups'");
echo "Таблица 'groups' <font color=\"green\"><b>успешно</b></font> создана! <br/>";

//создаем таблицу rules
$table =<<<EOF
  CREATE TABLE rules (
    id_rule INT NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_group INT NOT NULL,
    PRIMARY KEY (id_rule)
  )TYPE=MyISAM, default charset = utf8;
EOF;
mysql_query($table) or printBDError("Невозможно создать таблицу 'rules'");
echo "Таблица 'rules' <font color=\"green\"><b>успешно</b></font> создана! <br/>";

$pass=md5($defaultpass);
$adduser= <<<EOF
INSERT INTO users VALUES(
	NULL,
	'admin',
	'$pass')
EOF;
mysql_query($adduser) or printBDError("Ошибка добавления учетной записи администратора");
echo "Учетная запись администратора <font color=\"green\"><b>успешно</b></font> создана! <br/>";

//Редирект на страницу-оповещение об успешной установке
header("Refresh: 5; URL=setup.php?done=1");
echo "Через 5 секунд Вы будете перенаправлены на другую страницу...";
printHTMLFoot();
closeBDConnection();
?>
