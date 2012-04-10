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
			//если существует группа с id_group
			if (existGroupByID($group_id))
			{
				//если у пользователя есть права на работу с выбранной группой
				if (checkRules($user_id, $group_id) || ($userName=="admin"))
				{
					//если форма была отправлена
					if (isset($_POST['submitted']))
					{
						//проверяем форму и сохраняем введенные цифры
						checkFormAndSaveNums();
					}
					//если форма не была отправлена
					else
					{
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
				}
				//если у пользователя нет прав на работу с выбранной группой
				else
				{
					//$userName = $_COOKIE['sanLogin'];
					$groupName = getGroupNameByID(isset($_GET["group_id"]));
					$str =<<<EOF
					 У пользователя <b>$userName</b> нет доступа для работы с группой <b>$groupName</b>! Обратитесь к администратору.
EOF;
					printError($str);
				}
			}
			else
			{
				$str =<<<EOF
				Группа с выбранным id_group не существует
EOF;
				printError($str);
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
?>
