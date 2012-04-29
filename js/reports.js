var period = "month";
var selectedPeriodId = "month";

function selectPeriod(selectedPeriodId)
{
	document.getElementById(period).className = "";
	document.getElementById(selectedPeriodId).className = "selected";
	period = selectedPeriodId;
}

function showReportResult()
{
	if (ajax.readyState == 4) 
	{
		if (ajax.status == 200) 
		{
			var response = ajax.responseText;
			document.getElementById("reportResult").innerHTML = response;
		}
	}
}

function sendRequest(groupId)
{
	var params = "show=true&";
	ajax=getAjax();
	if (ajax!=null)
	{
		var paramName = "from";
		var paramValue = document.getElementsByName(paramName)[0].value;
		params += paramName + "=" + paramValue + "&";
		paramName = "to";
		paramValue = document.getElementsByName(paramName)[0].value;
		params += paramName + "=" + paramValue;
		params += "&period=" + period;
	  //params=encodeURI(params);
	  //Открыть соединение с сервером
	  ajax.open("POST", "reports.php?group_id="+groupId, true);
		ajax.setRequestHeader("Content-type", 
	  "application/x-www-form-urlencoded;");

		ajax.setRequestHeader("Content-length", params.length);
		ajax.setRequestHeader("Connection", "close"); 
	  //Установить функцию для сервера, которая выполнится после его ответа
	  ajax.onreadystatechange = showReportResult;
	  //Передать запрос
	  ajax.send(params);
	}
}
