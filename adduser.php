<?php
	//функция показывает на экране форму для добавления пользователя
	function showAddUserForm(){
		$script=$_SERVER['SCRIPT_NAME'];
		$login = (empty($_POST['name'])) ? 'Имя пользователя':$_POST['name'];
		$passw = (empty($_POST['pass'])) ? 'Пароль':$_POST['pass'];
		$passw2 = (empty($_POST['pass2'])) ? 'Пароль':$_POST['pass2'];
		echo <<<EOF
		<form action="$script" method="POST">
			<table>
				<tr><td>Логин:</td><td><input type="text" value="$login" name="name" size="10" onClick="this.value='';"></td></tr>
				<tr><td>Пароль:</td><td><input type="password" value="$passw" name="pass" size="10" onClick="this.value='';"></td></tr>
				<tr><td>Подтверждение:</td><td><input type="password" value="$passw2" name="pass2" size="10" onClick="this.value='';"></td><tr/>
				<tr><td colspan="2"><input type="submit" value="Добавить"></td></tr>
			</table>
		</form>		
EOF;
	}

	//подгружаем библиотеку функций
	require_once("funcs.php");
	header('Content-Type: text/html; charset=utf-8'); 
	
	printHTMLHead("Добавление пользователя");
	echo "<b>Добавление пользователя</b><br/>";
	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	if (isAdmin()){
		if (!empty($_POST['name']) && (!empty($_POST['pass'])) && (!empty($_POST['pass2'])) && ($_POST['name']!="Имя пользователя") && ($_POST['pass']!="Пароль")){
				
				//проверяем введенные данные
				$name = substr($_POST["name"],0,32);
				$name = htmlspecialchars(stripslashes($name));
				$pass = substr($_POST["pass"],0,32);
				$pass = htmlspecialchars(stripslashes($pass));
				$pass2 = substr($_POST["pass2"],0,32);
				$pass2 = htmlspecialchars(stripslashes($pass2));
				
				if ((!empty($name)) && (!empty($pass)) && (!empty($pass2)) && ($pass==$pass2)){					
					//проверяем, есть ли уже такой пользователь в БД
					$query = "SELECT * FROM users WHERE name = '$name'";
					$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице users");
					if (mysql_num_rows($res)<1){
						//добавляем пользователя в базу
						$pass=md5($pass);
						$adduser= <<<EOF
						INSERT INTO users VALUES(
							NULL,
							'$name',
							'$pass')
EOF;
						mysql_query($adduser) or printBDError("Ошибка добавления нового пользователя");
						closeBDConnection();
						echo "Пользователь <font color=\"blue\"><b>".$name."</b></font> с паролем <font color=\"blue\"><b>$pass2</b></font> <font color=\"green\"><b>успешно</b></font> добавлен! <br/>";
					}
					else{
						echo <<<EOF
						Пользователь <font color="blue"><b>$name</b></font> уже существует. Введите другое имя.
EOF;
						showAddUserForm();
					}
				}
				else{
					echo <<<EOF
					1)Имя пользователя и пароль не должны быть пустыми!<br/>
					2)Пароль и подтверждение пароля должны совпадать<br/>
EOF;
					showAddUserForm();
				}
		}
		else{
			echo <<<EOF
			Введите имя пользователя и пароль<br/>
EOF;
			showAddUserForm();
		}
	}
	else{
		if (isAuthorised()){
			$userName = $_COOKIE['sanLogin'];
			$str =<<<EOF
			Для добавления пользователя необходимо войти как <b>администратор</b>.
			Вы вошли как <b>$username</b>.
EOF;
		}
		else{
			$str =<<<EOF
			Для добавления пользователя необходимо войти как <b>администратор</b>.
			Пожалуйста, <a href="login.php">авторизуйтесь</a>. 
EOF;
			printError($str);
		}
		
	}
	printHTMLFoot();
?>
