<?php
//библиотека функций

//функция для вывода заголовка и титла страницы
function printHTMLHead($title){
	echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
    <HEAD>
    <TITLE>$title</TITLE>
    </HEAD>
<BODY>
EOF;
}

function printHTMLFoot(){
	echo<<<EOF
</BODY>
</HTML>
EOF;
}

//функция, установливающая cookies, необходимые для авторизации
function setAllCookie($user, $pass){
	//количество дней, которые будут храниться куки
	$days = 1;
	$tmppos = strpos($_SERVER['PHP_SELF'],"/")+1;
	$path = substr($_SERVER['PHP_SELF'], 0, $tmppos);
	setcookie("sanLogin", $user, time() + 3600*24*$days, $path);
	setcookie("sanPass", $pass, time() + 3600*24*$days, $path);	
}

//функция, выполняющая удаление cookie,
function cleanAllCookie(){
	$tmppos = strpos($_SERVER['PHP_SELF'],"/")+1;
	$path = substr($_SERVER['PHP_SELF'], 0, $tmppos);
	
	//устанавливем время жизни куки равным 0
	setcookie("sanLogin", "", 0, $path);
	setcookie("sanPass", "", 0, $path);	
}

//функция для вывода ошибок
function printError($errStr){
	echo <<<EOF
	<font color="red">Ошибка:</font> $errStr<br/>
	<a href="#" onClick="history.back(-1);">Назад</a>
EOF;
}

//функция, выводящая сообщение о ошибке работе с сервером БД
//$errorStr - текст сообщения
function printBDError($errorStr){
	global $htmlEnd;
	echo "<font color=red><b>".$errorStr.":</b></font>\n".mysql_error().$htmlEnd;
	mysql_close();
	printHTMLFoot();
	exit();
}

//Возвращает id_user - если пользователь авторизован
//0 - если необходима авторизация
function isAuthorised(){
	if (isset($_COOKIE['sanLogin']))
	{			
		$userName = checkStr($_COOKIE['sanLogin']);
		if ($userName!=="")
		{
			//проверяем, есть ли пользователь с таким именем
			$query = "SELECT id_user FROM users WHERE name = '$userName'";
			$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице users");
			if (mysql_num_rows($res)<1)
				return 0;
			return mysql_result($res, 0);
		}
	}
	return 0;
}

//true - если администратор
//false - если простой пользователь
function isAdmin(){
	if (isAuthorised() && ($_COOKIE['sanLogin']=='admin'))
		return true;
	return false;
}

//функция удаляет из строки запрещенные символы (не буквы и цифры)
function checkStr($str){
	//урезаем строку до 32 символов
	$str = substr($str,0,32);
	$str = preg_replace ("/[^a-zA-ZА-Яа-я0-9\s]/u","",$str);
	//удаляем пробелы
	$str = preg_replace ("/[\s]/","",$str);
	return $str;
}

//функция проверяет, существует ли группа с указанным id_group в базе
function existGroupById($id_group)
{
	$id_group = checkStr($id_group);
	$query = "SELECT * FROM groups WHERE id_group = '$id_group'";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице groups");
	if (mysql_num_rows($res)<1)
		return 0;
	return 1;
}

//функция проверяет, есть ли пользователя с id_user права на добавление данных в группу id_group
//возвращает 0, если у пользователя нет прав на доступ к группе
//1 - если у пользователя есть права на добавление данных
function checkRules($id_user, $id_group)
{
	$id_user = checkStr($id_user);
	$id_group = checkStr($id_group);
	$query = "SELECT id_rule FROM rules WHERE id_user='$id_user' AND id_group='$id_group'";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице rules");
	if (mysql_num_rows($res)<1)
		return 0;
	return 1;
}

//Функция выводит форму для выбора группы из списка групп
function showSelectGroupForm()
{
	$script=$_SERVER['SCRIPT_NAME'];
	echo <<<EOF
	<script type="text/javascript">
		var ajax=null;
		function getAjax()
		{
			var xmlHttp = false;
			/*@cc_on @*/
			/*@if (@_jscript_version >= 5)
			try {
			  xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
			  try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			  } catch (e2) {
				xmlHttp = false;
			  }
			}
			@end @*/

			if (!xmlHttp && typeof XMLHttpRequest != 'undefined') {
			  xmlHttp = new XMLHttpRequest();
			}
			if (xmlHttp!=false)
				return xmlHttp;
			return null;
		}
		
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
		
		
		function sendGroupId(sel)
		{
			if (sel.value!=0)
			{
				ajax=getAjax();
				if (ajax!=null)
				{
					var groupId = sel.value;
					var url = "$script?group_id="+ escape(sel.value);
					ajax.open("GET", url, true);
					ajax.onreadystatechange = updatePage;
					ajax.send(null);
				}
			}
		}
		
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
	</script>
	<form>
		<select onClick="sendGroupId(this);">
			<option  selected value="0">Выберите группу</option>
EOF;
	$query="SELECT id_group, name FROM groups";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице groups");
	while ($row=mysql_fetch_row($res))
	{
		echo "\n<option value=\"$row[0]\">$row[1]</option>";
	}
	echo <<<EOF
		</select>
		<div id="result"></div>
	</form>
EOF;
}

//фукнция возвзращает имя группы по id
function getGroupNameById($group_id)
{
	$query = "SELECT name FROM groups WHERE id_group=$group_id";
	$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице groups");
	if (mysql_num_rows($res)>0)
		return mysql_result($res, 0);
}

//фукнция возвращает имена столбцов в группе массивом
//только столбцы - поля (все кроме id_rec и day)
function getGroupFieldNames($name)
{
	$res = mysql_query("SHOW COLUMNS FROM group_$name");
	if (!$res)
	{
		printBDError("Ошибка при обращении к таблице group_$name");
	}
	$countFields = 0;
	$fieldNames = array();
	if (mysql_num_rows($res) > 0) {
		while ($row = mysql_fetch_assoc($res)) {
			if (($row['Key']=="PRI") || ($row['Field']=="author") || ($row['Field']=="day"))
				continue;
			$fieldNames[$countFields] = $row['Field'];
			$countFields++;
		}
	}
	return $fieldNames;
}	
?>
