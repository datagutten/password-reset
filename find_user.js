"use strict";
var xmlhttp = new XMLHttpRequest();

function mobile(username)
{
	xmlhttp.abort();
	var sms_checkbox=document.getElementById('sms');
	var displayname_text=document.getElementById('displayname_text');
	console.log(displayname_text);
	if(username.length>2)
	{
		xmlhttp.onreadystatechange = function() 
		{
			if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
			{
				var userinfo=JSON.parse(xmlhttp.responseText);

				if(userinfo.error.length>0)
				{
					if(sms_checkbox!==null)
					{
						sms_checkbox.setAttribute('disabled','disabled');
						document.getElementById('sms_text').textContent='';
					}

					document.getElementById('submit_password').setAttribute('disabled','disabled');
					document.getElementById('displayname').removeAttribute('value');
					displayname_text.textContent=userinfo.error;
				}
				else //User found
				{
					document.getElementById('dn').setAttribute('value',userinfo.dn);
					document.getElementById('displayname').setAttribute('value',userinfo.displayName);
					displayname_text.textContent=userinfo.displayName;
					document.getElementById('pwdlastset').textContent='Brukerens passord ble sist endret '+userinfo.pwdlastset;

					document.getElementById('submit_password').removeAttribute('disabled');
					if(sms_checkbox!==null)
					{
						if(userinfo.mobile.length===0) //No mobile, but user is valid
						{
							sms_checkbox.setAttribute('disabled','disabled');	
							document.getElementById('sms_text').textContent='Bruker '+ userinfo.displayName +' har ikke mobilnummer';
						}
						else //Valid user with mobile
						{
							sms_checkbox.removeAttribute('disabled');
							sms_checkbox.setAttribute('value',userinfo.mobile);
							document.getElementById('sms_text').textContent='Send passord for '+ userinfo.displayName +' på SMS til ' + userinfo.mobile;
						}
					}
				}
			}
		}
		xmlhttp.open("GET", "find_user.php?username=" + username, true);
		displayname_text.textContent='Henter informasjon om bruker, vennligst vent...';
		xmlhttp.send();
	}
	//document.getElementById('test').innerHTML=username.length;
}