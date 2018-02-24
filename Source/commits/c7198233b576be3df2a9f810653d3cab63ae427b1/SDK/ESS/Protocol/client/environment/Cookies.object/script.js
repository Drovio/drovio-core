cookies = {
	get : function(c_name) {
		var i,x,y;
		var ARRcookies = document.cookie.split(";");
		for (i=0; i<ARRcookies.length; i++)
		{
			x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x = x.replace(/^\s+|\s+$/g,"");
			if (x == c_name)
				return unescape(y);
		}
		
		return null;
	},
	set : function(c_name, value, exdays, path) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value = escape(value) + "; path="+path+";" + ((exdays==null) ? "" : "expires="+exdate.toUTCString());
		document.cookie = c_name + "=" + c_value;
	}
}