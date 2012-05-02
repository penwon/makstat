<?php
	//выводит форму для добавления новой группы
	function showAddGroupForm(){
		$script=$_SERVER['SCRIPT_NAME'];
		echo <<<EOF
		<script type="text/javascript" src="js/funcs.js"></script>
		<script type="text/javascript">
		  var fieldsCount=2;
			
			function updatePage(){
				if (ajax.readyState == 4) {
					if (ajax.status == 200) {
						var response = ajax.responseText;
						document.getElementById("result").innerHTML = response;
					}
				}
			}
			
    		function sendData() {
				var params = "submitted=true&";
				ajax=getAjax();
				if (ajax!=null)
				{
					var paramName = "groupName";
					var paramValue = document.getElementsByName(paramName)[0].value;
					if ((paramValue == null) || (paramValue == ""))
					{
							alert("Не введено имя группы!");
							return;
					}
					params += paramName + "=" + encodeURIComponent(paramValue) + "&";
					var paramName = "coltype";
					paramValue = getRadioGroupValue(document.getElementsByName(paramName));
					params += paramName + "=" + paramValue;
					for(var k=1; k<fieldsCount; k++)
					{
						paramName = "column"+k;
						paramValue = document.getElementsByName(paramName)[0].value;
						if ((paramValue == null) || (paramValue == ""))
						{
							alert("Все поля формы должны быть заполнены!");
							return;
						}
						params += "&" + paramName + "=" + encodeURIComponent(paramValue);
					}

				  //params=encodeURI(params);
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
		   
		  function addField(field){
			  if (fieldsCount<16){
				  var newField=document.createElement('input');
				  newField.type="text";
				  newField.size="15";
				  newField.value="Поле_"+fieldsCount;
				  newField.name="column"+fieldsCount;
				  newField.onfocus=function(){onFocused(this);}
				  newField.onblur=function(){blured(this);}
				  document.getElementById('fields').appendChild(document.createElement('br'));
				  document.getElementById('fields').appendChild(newField);
				  fieldsCount++;
			  }
		  }
		  
		  function delField(){
			  if (fieldsCount>2){
				document.getElementById('fields').removeChild(document.getElementById('fields').lastChild);
				document.getElementById('fields').removeChild(document.getElementById('fields').lastChild);
				fieldsCount--;
			  }
		  }
		</script>
		<div id="result" style=""></div>
		<form action="$script" method="POST" name="addgroup">
		  <table>
			<tr>
			 <td>Введите название группы:</td>
			 <td><input type="text" size="14" name="groupName" value="Название группы" onBlur="blured(this);" onFocus="onFocused(this);" ></td>
			</tr>
			<tr>
			  <td>Тип данных в группе:</td>
			  <td><input type="radio" name="coltype" value="integer">Целые числа<br>
			  <input type="radio" name="coltype" value="float" checked>Вещественные числа
			  </td>
			</tr>	
		  </table>
		  <div id="fields">
		    <b>Поля:</b><br/>
			<input type="text" size="15" value="Поле_1" name="column1" id="tess" onBlur="blured(this);" onFocus="onFocused(this);">
			<input type="button" onClick="addField(this);" value="Добавить поле">
			<input type="button" onClick="delField();" value="Удалить поле">
			<input type="hidden" name="submitted" value="true">
		  </div>
		  <input type="button" value="Создать группу" onClick="sendData();">
		</form>
EOF;
	}
	
	function checkFormAndCreateGroup(){
		$columns = array();
		$msg = "<font color=\"red\"><b>Ошибки ввода: </b></font></br>";
		$errStr = "";
		$i = -1;
		$fields ="";
		
		$groupName = checkStr($_POST['groupName']);
		if (empty($groupName))
			$errStr = "Название группы не должно быть пустым!<br/>";
		
		if (is_numeric($groupName))	
			$errStr = "Название группы не должно быть числом. Добавьте буквы!<br/>";
		
		//(isset($_POST['coltype']) && ($_POST['coltype']=="integer"))?$type="INT(10)":$type="DEC(10,2)";
		$type = "DEC(10,2)";	
		//заполняем массив columns, который будет содержать
		//имена полей группы данных
		while ($curField = each($_POST)){
			if (strpos($curField['key'], 'column')!==FALSE){
				$columns[++$i] = $curField['value'];	
			}
		}
		
		//проверяем поочередно имена полей
		for ($i = 0; $i <count($columns); $i++)
		{
			$columns[$i] = checkStr($columns[$i]);
			if (empty($columns[$i]) || $columns[$i]==""){
				$errStr.="Поле данных ". $i+1 ." пусто. <br/>";
			}
			else{
				if (is_numeric($columns[$i]))
					$errStr.="Поле данных ". $i+1 ." является числом. Добавьте буквы<br/>";
				$fields.=$columns[$i]." ".$type.",\n";
			}
		}
		
		if ($errStr!==""){
			echo $msg.$errStr;
			return;
		}
		//print_r($_POST);
		$msg = "<font color=\"red\"><b>Ошибки при создании группы: </b></font></br>";	
		($_POST['coltype']=="integer")?$type="integer":$type="float";
		//проверяем, есть ли уже группа с таким названием в БД
		$query = "SELECT * FROM groups WHERE name = '$groupName'";
		$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице groups");
		$r = mysql_query("SELECT 1 FROM `group_$groupName` WHERE 0");
		if ((mysql_num_rows($res)<1) && (!$r)){
			//добавляем группу в базу					
			$addgroup= <<<EOF
			INSERT INTO groups VALUES(
				NULL,
				'$groupName',
				'$type')
EOF;
			mysql_query($addgroup) or printBDError("Ошибка добавления новой группы");
			
			//создаем новую таблицу для группы
			$addgroup= <<<EOF
			CREATE TABLE group_$groupName (
				id_rec INT NOT NULL AUTO_INCREMENT,
				day DATE NOT NULL default '0000-00-00',
				author TINYTEXT NOT NULL,
				$fields
				PRIMARY KEY (id_rec)
			)TYPE=MyISAM, default charset = utf8;
EOF;
			if (!mysql_query($addgroup)){
				 //если не получилось создать таблицу для группы, 
				 //то удаляем упоминание о группе из таблицы 'groups'
				// echo 	$addgroup;		
				 mysql_query("DELETE FROM `groups` WHERE `groups`.`name` = '$groupName'");	
				 printBDError("Ошибка создания таблицы для новой группы");
			}
			closeBDConnection();
			echo "Группа <font color=\"blue\"><b>".$groupName."</b></font> <font color=\"green\"><b>успешно</b></font> создана! <br/>";
		}
		else{
			echo "Группа <font color=\"blue\"><b>".$groupName."</b></font> уже существует или существовала когда то ранее. Введите другое имя.";
		}
		
	}
	
	//подгружаем библиотеку функций
	require_once("funcs.php");
	header('Content-type: text/html; charset=utf-8');
		
	//соединяемся с БД
	require_once("config.php");
	selectBD();

	if (isAdmin() && (isset($_POST['submitted']))){
		checkFormAndCreateGroup();	
		return;
	}		

	if (isAdmin()){
		showMenu();
		printHTMLHead("Добавление группы данных");
		echo "<h3>Добавление группы данных</h3>";
		showAddGroupForm();
	}
	else{
		printHTMLHead("Добавление группы данных");
		if (isAuthorised()){
			$userName = $_COOKIE['sanLogin'];
			$str =<<<EOF
			Для создания группы данных необходимо войти как <b>администратор</b>.
			Вы вошли как <b>$username</b>.
EOF;
		}
		else{
			$str =<<<EOF
			Для создания группы данных необходимо войти как <b>администратор</b>.
			Пожалуйста, <a href="login.php">авторизуйтесь</a>. 
EOF;
			printError($str);
		}	
	}
	printHTMLFoot();
?>
