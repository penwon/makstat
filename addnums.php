<?php
	header('Content-type: text/html; charset=utf-8');
	
	//подгружаем библиотеку функций
	require_once("funcs.php");

	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	$user_id = isAuthorised();
	//если пользователь не авторизован, то выводим сообщение об ошибке
	if (!$user_id)
	{
		printHTMLHead("Добавление данных в группу");
		$str =<<<EOF
		Для добавления данных в группу необходимо <a href="login.php">авторизоваться</a>!
EOF;
		printError($str);
		printHTMLFoot();
	}
	//если пользователь авторизован
	else
	{
		$userName = $_COOKIE['sanLogin'];
		//если выбрана группа
		if (isset($_GET["group_id"]))
		{
			$group_id = checkStr($_GET['group_id']);
			//если не существует группа с id_group
			if (!existGroupByID($group_id))
			{
				$str =<<<EOF
				Группа с выбранным id_group не существует
EOF;
				printError($str);
				return;
			}
			//если у пользователя нет прав на работу с выбранной группой
			if (!(checkRules($user_id, $group_id) || ($userName=="admin")))
			{
				$groupName = getGroupNameByID(isset($_GET["group_id"]));
				$str =<<<EOF
				 У пользователя <b>$userName</b> нет доступа для работы с группой <b>$groupName</b>! Обратитесь к администратору.
EOF;
				printError($str);
				return;
			}
			//если форма была отправлена
			if (isset($_POST['submitted']))
			{
				//проверяем форму и сохраняем введенные цифры
				checkFormAndSaveNums();
				return;
			}
			//если указана дата для добавления
			if (isset($_POST["date"]))
			{
				//возвращаем известные данные полей группы за это число
				printGroupNumsByDate($_POST["date"]);
			}
			else
			{
				//выводим форму для добавления данных в поля выбранной группы
				showAddNumsForm($group_id);
				printHTMLFoot();
			}		
		}
		else
		{
			//выводим форму для выбора группы, с которой работаем
			printHTMLHead("Добавление данных в группу");
			showSelectGroupForm();
			printHTMLFoot();
		}
	}
	closeBDConnection();
	
//функция отображает форму для ввода данных
function showAddNumsForm($group_id)
{
	$name=getGroupNameById($group_id);
	echo <<<EOF
	<script src="js/calendar.js"></script>
	<link rel="stylesheet" type="text/css" href="css/calendar.css"> 
	Добавление данных в группу <font color="green"><b>"$name"</b></font><br/>
	какая то форма для группы с id = $group_id
EOF;
	//выводим шабку таблицы с названием полей
	echo <<<EOF
	<table border=1px cellspacing=0>
	  <tr>
EOF;
	echo "<th>Дата</th>";
	
	$fieldNames=getGroupFieldNames($name);
	$countFields = count($fieldNames);
	for ($i = 0; $i < $countFields; $i++)
		echo "<th>".$fieldNames[$i]."</th>";
		
	//выводим форму для ввода значений в поля
	echo "</tr><tr><form>";
	//сначала поле для ввода даты
	echo <<<EOF
	<td>Дата в формате ГГГГ-ММ-ДД: <input type="text" size="10" name="date" onclick="displayDatePicker('date', false, 'ymd', '-');"></td>
EOF;
	
	//потом остальные поля
	for ($i = 0; $i < $countFields; $i++)
	{
		$val = isFieldTypeInt($group_id);
		echo <<<EOF
		<td><input type="text" size="10" id="$fieldNames[$i]" value = "$val"></td>
EOF;
	}
	
	echo <<<EOF
		</form>
	 </tr>
	</table>
	<input type="button" value="Сохранить">
EOF;
}

//функция проверяет отправленный данные и сохраняет их в базу
function checkFormAndSaveNums()
{
	
}
?>
