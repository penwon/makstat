<?php
	if isAdmin()
	{
		echo <<<EOF
		Вы вошли как администратор. И можете:
		<a href="adduser.php">Спеть песню</a>
		<a href="addgroup.php">Сводить хоровод</a>
		<a href="editrules.php">Станцевать</a>
		<a href=""></a>
		<a href=""></a>
EOF;
	}
	else
	{
		echo<<<EOF
EOF;
	}
?>
