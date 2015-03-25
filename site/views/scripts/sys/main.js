dumpProps = function(obj, parent) {
   // Go through all the properties of the passed-in object
   for (var i in obj) {
      // if a parent (2nd parameter) was passed in, then use that to
      // build the message. Message includes i (the object's property name)
      // then the object's property value on a new line
      if (parent) { var msg = parent + "." + i + "\n" + obj[i]; } else { var msg = i + "\n" + obj[i]; }
      // Display the message. If the user clicks "OK", then continue. If they
      // click "CANCEL" then quit this level of recursion
      if (!confirm(msg)) { return; }
      // If this property (i) is an object, then recursively process the object
      if (typeof obj[i] == "object") {
         if (parent) { dumpProps(obj[i], parent + "." + i); } else { dumpProps(obj[i], i); }
      }
   }
}

disableField = function(originalRequest) {
	$(originalRequest.request.container.success).disabled = true;
}
enableField = function(originalRequest) {
	$(originalRequest.request.container.success).disabled = false;
}

enableCountry = function(mxdValue,strID) {
	if(mxdValue == '1' || mxdValue.toUpperCase() == 'BRASIL' || mxdValue.toUpperCase() == 'BRAZIL' || mxdValue.toUpperCase() == 'BR') {
		$(strID).disabled = false;
	} else {
		$(strID).disabled = true;
		$(strID).selectedIndex = 0;
		
		if($('tb_municipio_bn_id') !== undefined) {
			$('tb_municipio_bn_id').disabled = true;
			$('tb_municipio_bn_id').selectedIndex = 0;
		}
	}
}

clearField = function(objField,strText) {
	if(objField.value == strText) { objField.value = ''; } else if(objField.value == '') { objField.value = strText; }
}

onlyNumber = function(event) {
    if (event.keyCode) {
    	if ((event.keyCode < 48 || event.keyCode >= 58) && event.keyCode != 8 && event.keyCode != 9 && event.keyCode != 13 && event.keyCode != 27 && event.keyCode != 32 && event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40 && event.keyCode != 46) {
            return false;
        } else {
            return true;
        }
    } else {
		if ((event.which < 44 || event.which > 58) && event.which != 8 && event.which != 9 && event.which != 13 && event.which != 27 && event.which != 32 && event.which != 37 && event.which != 38 && event.which != 39 && event.which != 40 && event.which != 46) {
            return false;
        } else {
            return true;
        }
    }
}

valiDate = function(strForm,strPrefix,boolMandatory) {
	var intDay 		= parseInt(eval('document.'+strForm+'.'+strPrefix+'Day.value'));
	intDay			= (isNaN(intDay) ? 0 : intDay);
	if(intDay > 31)		return false;
	
	var intMonth 		= parseInt(eval('document.'+strForm+'.'+strPrefix+'Month.value'));
	intMonth		= (isNaN(intMonth) ? 0 : intMonth);
	if(intMonth > 12)	return false;
	
	var intYear 		= parseInt(eval('document.'+strForm+'.'+strPrefix+'Year.value'));
	intYear			= (isNaN(intYear) ? 0 : intYear);
	
	if(boolMandatory && (!intDay || !intMonth || !intYear)) return false;
	
	// Checks 31st of months with 30 days
	switch(intMonth) {
		case 4:
		case 6:
		case 9:
		case 11:
			if(intDay == 31) 	return false;
		break;
		
		case 2:
			if((intYear % 4) == 0) {
				if(intDay > 29)		return false;
			} else {
				if(intDay > 28)		return false;
			}
		break;
	}
	return true;
}

cityFilter = function(formID,intStateID,strFieldCity,strCombo,intType,intFilter,intWidth) {
	if(isNaN(intStateID)) {
		intFormLength	= $(formID).elements.length;
		arrIDField		= new Array();
		
		for(i = 0; i < intFormLength; i++) {
			if($(formID).elements[i].id.indexOf(intStateID) === 0 && $(formID).elements[i].checked == true) arrIDField.push($(formID).elements[i].value);
		}
	} else {
		arrIDField		= new Array(intStateID);
	}
	
	return new Ajax.Updater(strCombo,PATH+'../include-sistema/sys/cityFilter.php',{ method: 'post', parameters: 'name='+strFieldCity+'&type='+intType+'&userFilter='+intFilter+'&state='+arrIDField.toString()+'&width='+intWidth, onLoading: loadingIcon, onError: loadingIconHide });
}

loadingIconHide = function(originalRequest) {
	$(originalRequest.request.container.success).innerHTML = '';
}

showAndHide = function(strValue,intValue,strDisplay) {
	if(strDisplay == undefined) strDisplay = (navigator.appName.indexOf('Microsoft') >= 0 ? 'block' : 'table-row');
	
	if(intValue !== undefined) {
		switch(intValue) {
			case 1:
			case '1':
			case true:
				$(strValue).style.display = strDisplay;
			break;
			
			default:
				$(strValue).className = $(strValue).className.replace('hidden','');
				$(strValue).style.display = 'none';
			break;
		}
	} else {
		if($(strValue).style.display != strDisplay) {
			$(strValue).style.display = strDisplay;
		} else {
			$(strValue).className = $(strValue).className.replace('hidden','');
			$(strValue).style.display = 'none';
		}
	}
}

setPhoneSyntax = function(object,boolSeparateFields) {
	var mxdValue = object.value;
	if(boolSeparateFields) {
		switch (mxdValue.length) {
			case 4:
				mxdValue += '-';
			break;
		}
	} else {
		switch (mxdValue.length) {
			case 1:
				mxdValue = '(' + mxdValue;
			break;
			case 3:
				mxdValue += ')';
			break;  
			case 8:
				mxdValue += '-';
			break;
		}
	}
	
	object.value = mxdValue;
	
	return true;
}

setCNPJSyntax = function(objDOM, keyPress){
	if(onlyNumber(keyPress)) {
		var key = keyPress.keyCode;
		
		var vr = new String(objDOM.value);
		vr = vr.replace(".", "");
		vr = vr.replace("/", "");
		vr = vr.replace("-", "");
		
		tam = vr.length + 1;
		
		if (key != 14 && key != 8) {
			if (tam == 3)
				objDOM.value = vr.substr(0, 2) + '.';
			if (tam == 6)
				objDOM.value = vr.substr(0, 2) + '.' + vr.substr(2, 5) + '.';
			if (tam == 10)
				objDOM.value = vr.substr(0, 2) + '.' + vr.substr(2, 3) + '.' + vr.substr(6, 3) + '/';
			if (tam == 15)
				objDOM.value = vr.substr(0, 2) + '.' + vr.substr(2, 3) + '.' + vr.substr(6, 3) + '/' + vr.substr(9, 4) + '-' + vr.substr(13, 2);
		}
		
		return true;
	} else { return false; }
}