<?php
	header('Content-type: text/html; charset=utf-8');
	
	//подгружаем библиотеку функций
	require_once("funcs.php");

	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	if (isAdmin())
	{
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
			//если была нажата кнопка "показать"
			if (isset($_POST["show"]))
			{
				$month = date("m");
				$year = date("Y");
				$from = $year."-".$month."-1";
				if (isset($_POST["from"]) && isDate($_POST["from"]))
					$from = $_POST["from"];
				$month+=1;
				$to = $year."-".$month."-1";
				
				$to = new DateTime($from);
				$to->modify("+1 month -1 day");
				$to= $to->format("Y-m-d");
				
				if (isset($_POST["to"]) && isDate($_POST["to"]))
					$to = $_POST["to"];	
				$period = "";
				if (isset($_POST["period"]))
				{
					switch ($_POST["period"])
					{
						case "week": $period = "неделя"; break;
						case "month": $period = "месяц"; break;
						case "quarter": $period = "квартал"; break;
						case "year": $period = "год"; break;
						default:
							$period = "месяц";
					}
					
				}
					
				showReport($group_id, $from, $to, $period);
				return;
			}
			showReportNavMenu($group_id);
			
		}
		//если группа не выбрана
		else
		{
			printHTMLHead("Отчеты");
			echo <<< EOF
			<link rel="stylesheet" type="text/css" href="css/calendar.css"> 
			<script type="text/javascript" src="js/calendar.js"></script>
			<script type="text/javascript" src="js/reports.js"></script>
EOF;
			showSelectGroupForm();
			printHTMLFoot();
		}
	}
	else
	{
		printHTMLHead("Отчеты");
		if (isAuthorised())
		{
			$userName = $_COOKIE['sanLogin'];
			$str =<<<EOF
			Для создания группы данных необходимо войти как <b>администратор</b>.
			Вы вошли как <b>$username</b>.
EOF;
		}
		else
		{
			$str =<<<EOF
			Для создания группы данных необходимо войти как <b>администратор</b>.
			Пожалуйста, <a href="login.php">авторизуйтесь</a>. 
EOF;
			printError($str);
		}
		printHTMLFoot();
	}
	closeBDConnection();


function showReportNavMenu($group_id)
{
	$groupName = getGroupNameById($group_id);
	echo <<< EOF
	<h3>Отчеты по группе "$groupName"</h3>
	<style>
		a{
			text-decoration: underline;
			color: green;
			//font-weight: bold;
			padding: 0 7px;
		}
		a.selected{
			color: white;
			background-color: #555;
		}
	</style>
	<form>
		<i>Отчетный период:</i> с <input type="text" name="from" size="5"  onclick="displayDatePicker('from', false, 'ymd', '-');"/> 
		&nbsp;&nbsp;по <input type="text" name="to" size="5"  onclick="displayDatePicker('to', false, 'ymd', '-');"/><br/>
		<i>Количество дней в периоде:</i> <a href="#" id="week" onclick="selectPeriod('week');">неделя</a> 
										<a href="#" id="month" onclick="selectPeriod('month');" class="selected">месяц</a>
										<a href="#" id="quarter" onclick="selectPeriod('quarter');">квартал</a>
										<a href="#" id="year" onclick="selectPeriod('year');">год</a>
		</br><input type="button" value="Показать" onclick="sendRequest($group_id);"/>
	</form>
	<div id="reportResult"></div>
	
EOF;
	
}

function showReport($group_id, $from, $to, $period)
{
	if ($from>$to)
	{
		echo <<< EOF
		<font color="red"><b>Ошибка:</b> </font>Неверно задан интервал. Дата в поле <b>"с"</b> должна быть раньше даты в поле <b>"по"</b>
EOF;
		return;
	}
	echo "<h4>Данные в интервале с $from по $to. Период - $period.</h4>";
	$periodSum = array();
	$daysSumArr = array();
	$periodsCount = 0;
	$rowsCount = 0;
	$groupName = getGroupNameById($group_id);
	$fieldNames = getGroupFieldNames($groupName);
	$isIntFlag = isFieldTypeInt($group_id);
	$countFields = count($fieldNames);
	//echo $date->format('Y-m-d');
	$beginDate = new DateTime($from);
	$endDate = new DateTime($to);
	
	//определяем количество периодов
	switch($period)
	{
		case "год":
			$diff = $endDate->diff($beginDate);
			$periodsCount = $diff->y +1; 
			break;
		case "квартал":
			$diff = $endDate->diff($beginDate);
			$monthsCount = $diff->m; 
			$periodsCount = (int)($monthsCount/3 + 1);
			break;
		case "неделя":
			$diff = $endDate->diff($beginDate);
			$dayCount = $diff->d; 
			$periodsCount = (int)($dayCount/7 + 1);
			break;
		case "месяц":
		default:
			$diff = $endDate->diff($beginDate);
			$periodsCount = $diff->m +1; 
		//	break;
	}
	
	for ($k = 1; $k <= $periodsCount; $k++)
	{
		$begin = $beginDate->format('Y-m-d');
		switch($period)
		{
			case "год":
				$endDate = $beginDate->modify("+1 year -1 day");
				break;
			case "квартал":
				$endDate = $beginDate->modify("+3 months -1 day");
				break;
			case "неделя":
				$endDate = $beginDate->modify("+6 days");
				break;
			case "месяц":
			default:
				$endDate = $beginDate->modify("+1 month -1 day");
		}
		$end = $endDate->format('Y-m-d');
		if ($end>$to)
			$end=$to;
		echo "<h4><span style=\"padding:10px 5px; background-color: #555; color: white;\"><u>Период $k.</u> С $begin по $end</span></h4>";
		$query = "SELECT * FROM group_$groupName WHERE day >= '$begin' AND day <='$end' ORDER BY day ASC";
		$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице group_$groupName");
		if (mysql_num_rows($res)>0)
		{
			echo "<table cellspacing=\"0px\" style=\"text-align: left;\" border=\"1px\"><tr><th>Дата</th>";
			// выводим шапку
			for ($i = 0; $i < $countFields; $i++)
			{
				$periodSum[$fieldNames[$i]] = 0;
				echo "<th>".$fieldNames[$i]."</th>";
			}
			echo "<th>Сумма за день</th>";
			echo "</tr>";
			
			//выводим значения полей
			while ($row=mysql_fetch_assoc($res))
			{
				echo "<tr><td>".$row['day']."</td>";
				$daySum = 0;
				for ($i = 0; $i < $countFields; $i++)
				{	
					$val = $row[$fieldNames[$i]];
					if ($isIntFlag)
						$val = (int)$val;
					$periodSum[$fieldNames[$i]]+=$val;
					echo "<td>".$val."</td>";
					$daySum+=$val;
				}
				if ($isIntFlag)
					$daySum = (int)$daySum;
				$daysSumArr[$rowsCount++] = $daySum;
				echo "<td>$daySum</td>";
				echo "</tr>";
			}
			echo "<tr><td>с <b>$from</b> <br/>по <b>$to</td>";
			
			//считаем общие суммы 
			$allSum = 0;
			for ($i = 0; $i < $countFields; $i++)
			{
				echo "<td>".$periodSum[$fieldNames[$i]]."</td>";
				$allSum+=$periodSum[$fieldNames[$i]];
			}
			for ($i = 0; $i < $rowsCount; $i++)
			{
				$allSum+=$daysSumArr[$i];
			}
			//if ($isIntFlag)
			//	$allSum = (int)$allSum;
			echo "<td>Всего: $allSum</td>";

			echo "</tr>";
			echo "</table>";
		}
		else
			echo "Данных в заданном интервале нет...";
		$beginDate = $endDate->modify("+1 day");
	}	
}

?>