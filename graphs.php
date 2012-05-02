<?php
	header('Content-type: text/html; charset=utf-8');
	
	//подгружаем библиотеку функций
	require_once("funcs.php");

	//соединяемся с БД
	require_once("config.php");
	selectBD();
	
	//Получаем необходимые данные из БАЗЫ ################
	if (isAdmin())
	{
		//если выбрана группа
		if (isset($_GET["group_id"]))
		{
			$group_id = checkStr($_GET['group_id']);
			//если не существует группа с id_group
			if (!existGroupByID($group_id))
				printMsgAndDie("Группа данных с указанным group_id не существует");

			if (!(isset($_GET['from']) && (isDate($_GET['from']))))
				printMsgAndDie("Неверно указано начало интервала <b>from</b>");
			
			$begin = $_GET['from'];
			
			if (!(isset($_GET['to']) && (isDate($_GET['to']))))
				printMsgAndDie("Неверно указано конец интервала <b>to</b>");
				
			$end = $_GET['to'];
			$groupName = getGroupNameById($group_id);
			$fieldNames = getGroupFieldNames($groupName);
			$fieldNamesCount = count($fieldNames);
			//массив имен переданных полей
			$fieldNamesForBase = array();
			$baseStr = "";
			//количество переденных полей
			$found = 0;
			for ($i = 0; $i < $fieldNamesCount; $i++)
			{
				if (isset($_GET[$fieldNames[$i]]))
				{
					$fieldNamesForBase[$found++]=$fieldNames[$i];
					$baseStr.=" `$fieldNames[$i]`,";
				}
			}
			
			if ($found==0)
				printMsgAndDie("Не выбрано ни одного столбца для рисования графика");

			//удаляем последний символ - лишняя ","
			$baseStr = substr($baseStr, 0, -1);
			$query = "SELECT $baseStr FROM `group_$groupName` WHERE `day` >= '$begin' AND `day` <='$end' ORDER BY `day` ASC";
			$res = mysql_query($query) or printBDError("Ошибка при обращении к таблице group_$groupName");
			$maxPointsCount = mysql_num_rows($res);
			if ($maxPointsCount<1)
				printMsgAndDie("Нет данных за выбранный интервал");
			
			
			draw();
			
		}
		//если группа не выбрана
		else
			printMsgAndDie("Не выбрана группа данных");

	}
	else
	{
		printHTMLHead("Графики");
		if (isAuthorised())
		{
			$userName = $_COOKIE['sanLogin'];
			$str =<<<EOF
			Для работы с группой данных необходимо войти как <b>администратор</b>.
			Вы вошли как <b>$username</b>.
EOF;
		}
		else
		{
			$str =<<<EOF
			Для работы с группой данных необходимо войти как <b>администратор</b>.
			Пожалуйста, <a href="login.php">авторизуйтесь</a>. 
EOF;
			printError($str);
		}
		printHTMLFoot();
	}
	
	
	closeBDConnection();
	
function printMsgAndDie($msg)
{
	printHTMLHead("Графики");
	echo $msg;
	printHTMLFoot();
	exit();
}

function draw(){
	
	// Задаем изменяемые значения #######################################
	//размеры изображения
	$W = 800;
	$H = 500;
	
	//отступы
	$ML = 20;//слева
	$MB = 20;//внизу
	$M = 5;//верх, справа
	
	//толщина одного символа
	$LW = imagefontwidth(2);
	
	//максимальное количество точек на графике
	$count = 15;
	
	//максимальное значение на графике
	$max = 100;
	
	// Увеличим максимальное значение на 10% (для того, чтобы столбик
	// соответствующий максимальному значение не упирался в в границу
	// графика
	$max=intval($max+($max/10));
	
	// Количество подписей и горизонтальных линий
	// сетки по оси Y.
	$county=10;
	
	//толщина осей
	$AH = 4;
	
	// Работа с изображением ############################################
	//создаем изображение
	$img = imagecreate($W, $H);
	
	//задаем цвета
	//цвет фона
	$white  = imagecolorallocate($img, 255, 255, 255);
	$black = imagecolorallocate($img, 0, 0, 0);
	//светло-серый цвет для задней грани графика
	$lightgray = imagecolorallocate($img,231,231,231);
	//серый цвет для левой грани графика
	$gray = imagecolorallocate($img,212,212,212);
	//серый цвет темнее
	$gridColor = imagecolorallocate($img,180,180,180);
	//темно-серый цвет текста
	$textColor = imagecolorallocate($img,70,70,70);
	
	//цвета линий
	$lineColor[0] = imagecolorallocate($img,161,155,0);
	$lineColor[1] = imagecolorallocate($img,65,170,191);
	$lineColor[2] = imagecolorallocate($img,191,65,170);
	
	$text_width=0;
	// Вывод подписей по оси Y
	for ($i=1;$i<=$county;$i++)
	{
		$strl=strlen(($max/$county)*$i)*$LW;
		if ($strl>$text_width) $text_width=$strl;
	}

	// Подравняем левую границу с учетом ширины подписей по оси Y
	$ML+=$text_width;
	
	// Посчитаем реальные размеры графика (за вычетом подписей и
	// отступов)
	$RW=$W-$ML-$M;
	$RH=$H-$MB-$M;

	// Посчитаем координаты нуля
	$X0=$ML;
	$Y0=$H-$MB;
	
	$step=$RH/$county;
	
	// Вывод главной рамки графика
	imagefilledrectangle($img, $X0, $Y0-$RH, $X0+$RW, $Y0, $lightgray);
	imagerectangle($img, $X0, $Y0, $X0+$RW, $Y0-$RH, $gridColor);
	
	// Вывод сетки по оси Y
	for ($i=1;$i<=$county;$i++)
	{
		$y=$Y0-$step*$i;
		imageline($img,$X0,$y,$X0+$RW,$y,$gridColor);
		imageline($img,$X0,$y,$X0-($ML-$text_width)/4,$y,$textColor);
	}

	// Вывод сетки по оси X
	// Вывод изменяемой сетки
	for ($i=0;$i<$count;$i++)
	{
		imageline($img,$X0+$i*($RW/$count),$Y0,$X0+$i*($RW/$count),$Y0,$gridColor);
		imageline($img,$X0+$i*($RW/$count),$Y0,$X0+$i*($RW/$count),$Y0-$RH,$gridColor);
	}
	
	//вывод графиков
	
	
	
	
	// Уменьшение и пересчет координат
	$ML-=$text_width;

	// Вывод подписей по оси Y
	for ($i=1;$i<=$county;$i++)
	{
		$str=($max/$county)*$i;
		imagestring($img,2, $X0-strlen($str)*$LW-$ML/4-2,$Y0-$step*$i-
						   imagefontheight(2)/2,$str,$textColor);
	}

	// Вывод подписей по оси X
	$prev=100000;
	//$twidth=$LW*strlen($DATA["x"][0])+6;
	$i=$X0+$RW;

/*	while ($i>$X0)
	{
		if ($prev-$twidth>$i)
		{
			$drawx=$i-($RW/$count)/2;
			if ($drawx>$X0)
			{
		//		$str=$DATA["x"][round(($i-$X0)/($RW/$count))-1];
		$str="123";
				imageline($img,$drawx,$Y0,$i-($RW/$count)/2,$Y0+5,$textColor);
				imagestring($img,2, $drawx-(strlen($str)*$LW)/2, $Y0+7,$str,$textColor);
			}
			$prev=$i;
		}
		$i-=$RW/$count;
	}*/
	
	header("Content-Type: image/png");
	imagepng($img);
	imagedestroy($img);
}
?>
