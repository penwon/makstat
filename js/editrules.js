function sendRules(user_id)
{
	var table=document.getElementById("numsTable");
	var params = "save=true&";
	ajax=getAjax();
	if (ajax!=null)
	{
		for (i = 0; i < document.getElementsByName("fieldRules").length; i++)
		{
			checkbox = document.getElementsByName("fieldRules")[i];
			params += "&" + checkbox.value +"="+checkbox.checked;
		}

	  //params=encodeURI(params);
	  //Открыть соединение с сервером
	  ajax.open("POST", "editrules.php?user_id="+user_id, true);
		ajax.setRequestHeader("Content-type", 
	  "application/x-www-form-urlencoded;");

		ajax.setRequestHeader("Content-length", params.length);
		ajax.setRequestHeader("Connection", "close"); 
	  //Установить функцию для сервера, которая выполнится после его ответа
	  ajax.onreadystatechange = saveResult;
	  //Передать запрос
	  ajax.send(params);
	}
}

function saveResult()
{
	if (ajax.readyState == 4) 
	{
		if (ajax.status == 200) 
		{
			var response = ajax.responseText;
			document.getElementById("saveResult").innerHTML = response;
		}
	}
}
