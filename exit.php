<?php
	//подгружаем библиотеку функций
	require_once("funcs.php");
	header('Content-Type: text/html; charset=utf-8'); 
	
	printHTMLHead("Выход из системы");
	cleanAllCookie();
	echo <<<EOF
	Выход из системы успешно произведен!<br/>
	Для перехода на главную страницу нажмите <a href="index.php">сюда</a>
EOF;
	printHTMLFoot();
?>
