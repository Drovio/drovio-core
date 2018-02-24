cookies = {
	get : function(c_name) {
		var i, b, c;
		var ARRcookies = document.cookie.split(";");
		for (i=0; i<ARRcookies.length; i++)
		{
			b = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			c = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			b = b.replace(/^\s+|\s+$/g,"");
			var flag = (b == c_name);
			if (flag)
				return unescape(c);
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