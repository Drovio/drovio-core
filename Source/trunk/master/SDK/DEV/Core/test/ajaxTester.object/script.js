// ajaxTester
ajaxTester = {
	status : function() {
		return (cookies.get("ajxTester") == "1");
	},
	resolve : function(tUrl) {
		// Check if ajax tester is true
		if (!this.status())
			return tUrl;
		
		// Get url
		var parts = tUrl.split('?');
		var testUrl = parts[0];
		
		// Get variables
		var urlVars = url.getUrlVar(tUrl);
		
		// Add testing parameter
		var requestParameters = this.getParameterName() + "=" + testUrl;
		
		for (var name in urlVars)
			if (typeof urlVars[name] == "string")
				requestParameters += "&"+ name +"="+encodeURIComponent(urlVars[name]);
			else
				for (var i in urlVars[name]) {
					requestParameters += "&"+ name +"="+encodeURIComponent(urlVars[name][i]);
				}
		
		return "/ajax/tester.php?"+requestParameters;
	},
	getParameterName: function() {
		return "__AJAX[path]";
	}
}