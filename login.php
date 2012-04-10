<?php
//скрипт авторизации

function showLoginForm(){
	//выводим форму авторизации
	echo<<<EOF
	<form action="login.php?enter=1" method="POST">
		Логин:&nbsp;&nbsp;<input type="text" value="Имя пользователя" name="name" size="10" onClick="this.value='';"><br/>
		Пароль:<input type="password" value="Пароль" name="pass" size="10" onClick="this.value='';"><br/>
		<input type="submit" value="Вход">
	</form>
EOF;
}

//подгружаем библиотеку функций
require_once("funcs.php");
header('Content-Type: text/html; charset=utf-8'); 
printHTMLHead("Страница авторизации");
if (isAuthorised()){
	$user=$_COOKIE['sanLogin'];
	echo <<<EOF
	Вы уже авторизованы как <b>$user</b>.<br/>
    Для авторизации под другим именем нужно <a href="exit.php">выйти</a> из системы.
EOF;
	printHTMLFoot();
	exit();
}
//если данные для авторизации были введены,
//то ищем пользователя в таблице БД и авторизуемся
if (isset($_GET['enter']) && ($_GET['enter']=="1")){
	
	//проверка введенных данных
	$name = substr($_POST["name"],0,32);
	$name = htmlspecialchars(stripslashes($name));
	$pass = substr($_POST["pass"],0,32);
	$pass = htmlspecialchars(stripslashes($pass));
	if ((!empty($name)) && (!empty($pass))){
		//соединяемся с БД
		require_once("config.php");
		selectBD();
		$query = "SELECT * FROM users WHERE name = '$name'";
		$res = mysql_query($query);
		if ($res){
			$auth = mysql_fetch_array($res);
			if($auth['passw'] != md5($pass)){
				printError("Ошибка идентификации. Попробуйте еще раз!");
				showLoginForm();
			}
			else{
				setAllCookie($auth['name'], $auth['passw']);
				echo<<<EOF
				Вы успешно авторизовались как <b>$name</b>.<br/>
				Для перехода на главную страницу нажмите <a href="index.php">сюда</a>.<br/>
				Через 5 секунд Вы будете перенаправлены автоматически.
EOF;
				header("Refresh: 5; URL=index.php");
			}
			closeBDConnection();
		}
		else{
			printBDError("Ошибка при обращении к таблице пользователей");
		}
	}
	else{
		printError("Имя пользователя или пароль не должны быть пустыми. Проверьте вводимые данные.");
		showLoginForm();
	}
}
else{
	showLoginForm();
}
printHTMLFoot();
?>
