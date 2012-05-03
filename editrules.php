<?php
	header('Content-type: text/html; charset=utf-8');
	
	//подгружаем библиотеку функций
	require_once("funcs.php");

	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	if (isAdmin())
	{
		//если выбран пользователь
		if (isset($_GET['user_id']))
		{
			if (!isNum($_GET['user_id'], false))
				return;
			//проверяем существует ли выбранный пользователь
			//если нет, то выходим
			$query = "SELECT name FROM users WHERE id_user={$_GET['user_id']}";
			$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице users");
			if (mysql_num_rows($res)<1)
				return;
			if (isset($_POST['save']))
				saveRules($_GET['user_id']);
			else
				showGroupsCheckBoxes($_GET['user_id']);
			
		}
		//если пользователь не выбран
		else
		{
			printHTMLHead("Редактирование прав доступа");
			showMenu();
			echo "<h3>Редактирование прав доступа</h3>";
			echo <<< EOF
			<link rel="stylesheet" type="text/css" href="css/calendar.css"> 
			<script type="text/javascript" src="js/calendar.js"></script>
			<script type="text/javascript" src="js/reports.js"></script>
EOF;
			showSelectUserForm();
			printHTMLFoot();
		}

	}
	else
	{
		printHTMLHead("Редактирование прав доступа");
		if (isAuthorised())
		{
			$userName = $_COOKIE['sanLogin'];
			$str =<<<EOF
			Для редактирования прав доступа необходимо войти как <b>администратор</b>.
			Вы вошли как <b>$username</b>.
EOF;
		}
		else
		{
			$str =<<<EOF
			Для редактирования прав доступа необходимо войти как <b>администратор</b>.
			Пожалуйста, <a href="login.php">авторизуйтесь</a>. 
EOF;
			printError($str);
		}
		printHTMLFoot();
	}
	closeBDConnection();


//Функция выводит форму для редактирования прав
function showSelectUserForm()
{
	$script=$_SERVER['SCRIPT_NAME'];
	echo <<<EOF
	<script type="text/javascript" src="js/funcs.js"></script>
	<script type="text/javascript" src="js/editrules.js"></script>
	<script type="text/javascript">		
		function updatePage()
		{
			if (ajax.readyState == 4) 
			{
				if (ajax.status == 200) 
				{
					var response = ajax.responseText;
					document.getElementById("result").innerHTML = response;
				}
			}
		}
		
		
		function sendUserId(sel)
		{
			if (sel.value!=0)
			{
				ajax=getAjax();
				if (ajax!=null)
				{
					var groupId = sel.value;
					var url = "$script?user_id="+ escape(sel.value);
					ajax.open("GET", url, true);
					ajax.onreadystatechange = updatePage;
					ajax.send(null);
				}
			}
		}
	</script>
	<form>
		<select onChange="sendUserId(this);">
			<option  selected value="0">Выберите пользователя</option>
EOF;
	$query="SELECT id_user, name FROM users";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице users");
	while ($row=mysql_fetch_row($res))
	{
		if ($row[1]=="admin")
			continue;
		echo "\n<option value=\"$row[0]\">$row[1]</option>";
	}
	echo <<<EOF
		</select>
		<div id="result"></div>
	</form>
EOF;
}

function showGroupsCheckBoxes($user_id)
{
	$query="SELECT id_group, name FROM groups";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице groups");
	echo <<<EOF
	<style>
		#rulesTable .check{
			text-align: center;
		}
		#rulesTable{
			//border: 10px;
		}
	</style>
	<br><form name="formName"><table border="1px" cellspacing="0" cellpadding="5" id="rulesTable">
	<tr><th>Группа данных</th><th>Права на запись</th></tr>
EOF;
	while ($row = mysql_fetch_row($res))
	{
		$checked = checkRules($user_id, $row[0])?"checked":"";
		echo <<<EOF
		<tr><td>$row[1]</td><td class="check"><input type="checkbox" name="fieldRules" value="$row[0]" $checked></td></tr>
EOF;
	}
	echo <<<EOF
	</table></form>
	<input type="button" value="Сохранить" onClick="sendRules($user_id);">
	<div id="saveResult"></div>
EOF;
	
}


//сохраняет права пользователя на доступ к группам данных
function saveRules($user_id)
{
	$changeFlag = false;
	foreach ($_POST as $key => $value)
	{
		if (isNum($key, false) && existGroupById($key))
		{
			if (($value=="true") && !checkRules($user_id, $key))
			{
				//добавляем права
				$query = "INSERT INTO `rules`(`id_rule`, `id_user`, `id_group`) VALUES (0,$user_id, $key)";
				mysql_query($query) or printBDError("Ошибка при добавлении данных в таблицу rules");
				$changeFlag = true;
			}
			if (($value=="false") && checkRules($user_id, $key))
			{
				//удаляем права
				$query = "DELETE FROM `rules` WHERE `id_user`= $user_id AND `id_group` = $key";
				mysql_query($query) or printBDError("Ошибка при удалении данных из таблицы rules");
				$changeFlag = true;
			}
		}
	}
	if ($changeFlag)
		echo "Права для пользователя успешно изменены!";
}
?>
