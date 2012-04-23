<?php
	//функция показывает на экране форму для добавления пользователя
	function showAddUserForm(){
		$script=$_SERVER['SCRIPT_NAME'];
		$login = (empty($_POST['name'])) ? 'Имя пользователя':$_POST['name'];
		$passw = (empty($_POST['pass'])) ? 'Пароль':$_POST['pass'];
		$passw2 = (empty($_POST['pass2'])) ? 'Пароль':$_POST['pass2'];
		echo <<<EOF
		<script type="text/javascript" src="js/funcs.js"></script>
		<script type="text/javascript">			
			function updatePage(){
				if (ajax.readyState == 4) {
					if (ajax.status == 200) {
						var response = ajax.responseText;
						document.getElementById("result").innerHTML = response;
					}
				}
			}
			
			function sendData(){
				var params = "submitted=true&";
				ajax=getAjax();
				if (ajax!=null)
				{
					var paramName = "name";
					var userName = document.getElementsByName(paramName)[0].value;
					if (userName=="Имя пользователя")
					{
						alert("Необходимо ввести имя пользователя!");
						return;
					}
					params+=paramName + "=" + encodeURIComponent(userName) + "&";
					paramName = "pass";
					var pass = document.getElementsByName(paramName)[0].value;
					params+=paramName + "=" + encodeURIComponent(pass) + "&";
					paramName = "pass2"
					var pass2 = document.getElementsByName(paramName)[0].value;
					params+=paramName + "=" + encodeURIComponent(pass2);
					if (pass!=pass2)
					{
						alert("Пароль и подтверждение пароля не совпадают. Проверьте правильность ввода!");
						return;
					}
					if (pass== "Пароль")
					{
						alert("Необходимо ввести пароль!");
						return;
					}
					//Открыть соединение с сервером
					ajax.open("POST", "$script", true);
					ajax.setRequestHeader("Content-type", 
					"application/x-www-form-urlencoded;");
					ajax.setRequestHeader("Content-length", params.length);
					ajax.setRequestHeader("Connection", "close"); 
					//Установить функцию для сервера, которая выполнится после его ответа
					ajax.onreadystatechange = updatePage;
	
					//Передать запрос
					ajax.send(params);
				}
			}
		</script>
		<div id="result" style=""></div>
		<form action="$script" method="POST">
			<table>
				<tr><td>Логин:</td><td><input type="text" value="$login" name="name" size="10" onBlur="blured(this);" onFocus="onFocused(this);"></td></tr>
				<tr><td>Пароль:</td><td><input type="password" value="$passw" name="pass" size="10" onBlur="blured(this);" onFocus="onFocused(this);"></td></tr>
				<tr><td>Подтверждение:</td><td><input type="password" value="$passw2" name="pass2" size="10" onBlur="blured(this);" onFocus="onFocused(this);"></td><tr/>
				<tr><td colspan="2"><input type="button" value="Добавить" onClick="sendData();"></td></tr>
			</table>
		</form>		
EOF;
	}

	//подгружаем библиотеку функций
	require_once("funcs.php");
	header('Content-Type: text/html; charset=utf-8'); 
	
	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	if (isAdmin()){
		if (isset($_POST['submitted'])){
				//проверяем введенные данные
				$name = checkStr($_POST["name"]);
				$pass = checkStr($_POST["pass"]);
				$pass2 = checkStr($_POST["pass2"]);
				
				if ((!empty($name)) && (!empty($pass)) && ($pass==$pass2)){					
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
					}
				}
				else{
					echo <<<EOF
					1)Имя пользователя и пароль не должны быть пустыми!<br/>
					2)Имя пользователя и пароль должны состоять только из букв и цифр<br/>
					3)Пароль и подтверждение пароля должны совпадать<br/>
EOF;
				}
		}
		else{
			printHTMLHead("Добавление пользователя");
			echo "<b>Добавление пользователя</b><br/>";
			showAddUserForm();
			printHTMLFoot();
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
?>
