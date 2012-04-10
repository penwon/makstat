<?php
	//подгружаем библиотеку функций
	require_once("funcs.php");
	header('Content-Type: text/html; charset=utf-8'); 
	
	printHTMLHead("Главная страница");
	
	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	if (isAuthorised()){
		$user = $_COOKIE['sanLogin'];
		echo <<<EOF
		Вы авторизованы как <b>$user</b> и можете работать с системой.<br/>
		<a href="exit.php">Выход</a>
EOF;
	}
	else{
		echo <<<EOF
		Перед началом работы необходимо <a href="login.php">авторизоваться</a>.<br/>
		Через 5 секунд Вы будете перенаправлены автоматически.
EOF;
		header("Refresh: 5; URL=login.php");
	}
	printHTMLFoot();
?>
