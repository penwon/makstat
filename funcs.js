var text="";		  
var ajax=null;

function getAjax(){
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

function blured(textField){
  if (textField.value==""){
	textField.value=text;
   }
}

function onFocused(textField){
	text=textField.value; 
	textField.value='';
}
