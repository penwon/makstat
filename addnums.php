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
			$groupName = getGroupNameByID(isset($_GET["group_id"]));
			//если у пользователя нет прав на работу с выбранной группой
			if (!(checkRules($user_id, $group_id) || ($userName=="admin")))
			{
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
				checkFormAndSaveNums($group_id);
				return;
			}
			//если указана дата для добавления
			if (isset($_POST["data"]))
			{
				$data = $_POST["data"];
				if (isDate($data))
				{
					//возвращаем известные данные полей группы за это число
					//printGroupNumsByDate($groupName, $data);
					showAddNumsForm($group_id, $data);
				}
			}
			else
			{
				//выводим форму для добавления данных в поля выбранной группы
				$data="2012-01-01";
				showAddNumsForm($group_id, $data);
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
function showAddNumsForm($group_id, $data)
{
	$groupName=getGroupNameById($group_id);
	$fieldNames=getGroupFieldNames($groupName);
	$countFields = count($fieldNames);
	$script=$_SERVER['SCRIPT_NAME'];
	echo <<<EOF
	<br/>Добавление данных в группу <font color="green"><b>"$groupName"</b></font><br/>
	<br/>
EOF;
	
	//выводим шабку таблицы с названием полей
	echo <<<EOF
	<table border=1px cellspacing=0 id="numsTable">
	  <tr>
EOF;
	echo "<th>Дата</th>";

	for ($i = 0; $i < $countFields; $i++)
		echo "<th id=\"columnName$i\">".$fieldNames[$i]."</th>";
	//выводим форму для ввода значений в поля
	echo "</tr><tr><form>";
	echo <<<EOF
	<td>Дата в формате ГГГГ-ММ-ДД: <input type="text" size="10" name="data" onclick="displayDatePicker('data', false, 'ymd', '-');" value="$data" onblur="sendDate($group_id);"></td>
EOF;
	if (existsNums($groupName, $data))
	{
		$query = "SELECT * FROM group_$groupName WHERE day='$data'";
		$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице group_$groupName");

		//остальные поля
		for ($i = 0; $i < $countFields; $i++)
		{
			$val = mysql_result($res, 0, $fieldNames[$i]);
			if (isFieldTypeInt($group_id))
				$val = (int)$val;
			echo <<<EOF
			<td><input type="text" size="10" id="$fieldNames[$i]" value = "$val"></td>
EOF;
		}	
	}
	else
	{
		//остальные поля
		for ($i = 0; $i < $countFields; $i++)
		{
			echo <<<EOF
			<td><input type="text" size="10" id="$fieldNames[$i]"></td>
EOF;
		}
	}
	echo <<<EOF
		</form>
	 </tr>
	</table>
	<input type="button" value="Сохранить" onClick="sendNums($group_id);">
EOF;
if (existsNums($groupName, $data))
	{
		//выводим кнопку для перезаписи
		echo  <<< EOF
		<div id="addingResult"><font color="red"><b>Внимание: </b></font>данные в группе<font color="green"><b>"$groupName"</b></font> за <b>$data</b> уже существуют! <br/>
		Если вы действительно хотите перезаписать данные, то нажмите на кнопку<br/>
		<input type="button" value="Перезаписать" onClick="sendNums($group_id, true);"></div>
EOF;
	}
	else
	{	
		echo <<< EOF
		<div id="addingResult"></div>
EOF;
	}

}

//функция проверяет отправленные данные и сохраняет их в базу
function checkFormAndSaveNums($group_id)
{
	$groupName = getGroupNameById($group_id);
	$fieldNames=getGroupFieldNames($groupName);	
	$data = $_POST['data'];
	if (existsNums($groupName, $_POST['data']) && !isset($_POST['update']))
	{
		//выводим кнопку для перезаписи
		echo  <<< EOF
		<div id="addingResult"><font color="red"><b>Внимание: </b></font>данные в группе<font color="green"><b>"$groupName"</b></font> за <b>$data</b> уже существуют! <br/>
		Если вы действительно хотите перезаписать данные, то нажмите на кнопку
		<input type="button" value="Перезаписать" onClick="sendNums($group_id, true);"></div>
EOF;
		return;
	}
	$countFields = count($fieldNames);
	$userName = $_COOKIE['sanLogin'];
	$script=$_SERVER['SCRIPT_NAME'];
	$errMsg = "<font color=\"red\"><b>Ошибка:</b></font>";
	$foundErrors = false;
	$names = "";
	$values = "";
	$set = "";
	
	if (isset($_POST['data']) && isDate($_POST['data']))
	{
		$names = "`author`, `day`";
		$values = "'$userName', '".$_POST['data']."'";
		$set = "`author`='$userName'";
	}
	else
	{
		echo $errMsg."Дата введена неправильно, либо не введена вообще! Проверьте правильность ввода.";
		return;
	}	
	$isFloat = !isFieldTypeInt($group_id);
	for ($k = 0; $k < $countFields; $k++)
	{
		$v = $fieldNames[$k];
		if (isset($_POST[$v]) && (!empty($_POST[$v])) && isNum($_POST[$v], $isFloat))
		{
			$names .=", `".$v."`";
			$values .= ", '".$_POST[$v]."'";	
			$set .= ", `".$v."`='".$_POST[$v]."'";
		}
		else
		{
			$foundErrors = true;
			$errMsg .= "Данные  ".$_POST[$v]." в поле <b>$v</b> введены неверно, либо не являются числом! Проверьте правильность ввода. <br/>";
		}		
	}
	if (!$isFloat)
		$errMsg .= "Числа должны быть целого типа! И не должны начинаться с 0!";
	else
		$errMsg .= "Числа должны быть введены в формате xxxx.yy, где x - цифра от 0 до 9, y - цифра от 0 до 9, необязательная часть";
	$errMsg .= "</br>";
			
	if ($foundErrors)
	{
		echo $errMsg;
		return;
	}	
	
	if (existsNums($groupName, $_POST['data']))
	{
		//обновляем данные
		$query = "UPDATE `group_$groupName` SET $set WHERE day='$data'";
		mysql_query($query) or printBDError("Ошибка при обновлении данных в таблице group_$groupName");
		echo <<<EOF
		Данные в группе <font color="green"><b>$groupName</b></font> за <b>$data</b> успешно обновлены!
EOF;
	}
	else
	{	
		//добавляем данные
		$query = "INSERT INTO `group_$groupName` ( $names )	VALUES ( $values )";
		//echo "ddd".$names."ddd";
		mysql_query($query) or printBDError("Ошибка при добавлении данных в таблицу group_$groupName");
		echo <<<EOF
		Данные в группу <font color="green"><b>$groupName</b></font> за <b>$data</b> успешно добавлены!
EOF;
	}
}

//проверяет существует ли уже в таблице запись за текущее число
//return - true , если записи есть, иначе false
function existsNums($groupName, $day)
{
	$query = "SELECT * FROM group_$groupName WHERE day='$day'";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице group_$groupName");
	if (mysql_num_rows($res)<1)
		return false;
	return true;
}

//возвращает значения полей группы за дату = $data
function printGroupNumsByDate($groupName, $data)
{
	$query = "SELECT * FROM group_$groupName WHERE day='$day'";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице group_$groupName");
}

//return true, если $num  - число
function isNum($num, $isFloat)
{
	if ($isFloat)
	{
		//вещественное, начинающееся и с 0
		return preg_match("/^[0-9]\d*([.]\d{1,2})?$/", $num);
	}
	else
	{
		//целое, начинающееся не с 0
		return preg_match("/^[1-9]\d*$/", $num);
	}
}

?>
