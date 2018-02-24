appcookies = {
	get : function(c_name) {
		// Get application relative cookie name
		c_name = this.getCookieName(c_name);
		
		// Get cookie value
		return cookies.get(c_name);
	},
	set : function(c_name, value, exdays, path) {
		// Get application relative cookie name
		c_name = this.getCookieName(c_name);
		
		// Get cookie value
		return cookies.set(c_name, value, exdays, path);
	},
	getCookieName : function(applicationID, c_name) {
		return "__APP" + applicationID + "_" + c_name;
	}
}