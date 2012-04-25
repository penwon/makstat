function updateAfterSendNums()
{
	if (ajax.readyState == 4) 
	{
		if (ajax.status == 200) 
		{
			var response = ajax.responseText;
			document.getElementById("addingResult").innerHTML = response;
		}
	}
}

function sendNums(groupId, isRewriting) {
	var table=document.getElementById("numsTable");
	var params = "submitted=true&";
	ajax=getAjax();
	if (typeof(isRewriting)=='undefined') isRewriting=false;
	if (ajax!=null)
	{
		if (isRewriting)
		{
			//alert("rewrite");
			params+="update=true&";
		}
		var paramName = "data";
		var paramValue = document.getElementsByName(paramName)[0].value;
		params += paramName + "=" + paramValue;
		var fieldsCount = table.rows[0].cells.length;
		for (var k=1; k<fieldsCount; k++)
		{
				paramName = table.rows[0].cells[k].innerHTML;
				paramValue = document.getElementById(paramName).value;
				params += "&" + encodeURIComponent(paramName) + "=" + paramValue;
		}

	  //params=encodeURI(params);
	  //Открыть соединение с сервером
	  ajax.open("POST", "addnums.php?group_id="+groupId, true);
		ajax.setRequestHeader("Content-type", 
	  "application/x-www-form-urlencoded;");

		ajax.setRequestHeader("Content-length", params.length);
		ajax.setRequestHeader("Connection", "close"); 
	  //Установить функцию для сервера, которая выполнится после его ответа
	  ajax.onreadystatechange = updateAfterSendNums;
	  //Передать запрос
	  ajax.send(params);
	}
}	

function updateNumValues()
{
	if (ajax.readyState == 4) 
	{
		if (ajax.status == 200) 
		{
			var response = ajax.responseText;
			document.getElementById("addingResult").innerHTML = response;
		}
	}
}

function sendDate(groupId)
{
	ajax=getAjax();
	if (ajax!=null)
	{
		var paramName = "data";
		var paramValue = document.getElementsByName(paramName)[0].value;
		var params = paramName + "=" + paramValue;
		//Открыть соединение с сервером
		ajax.open("POST", "addnums.php?group_id="+groupId, true);
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
