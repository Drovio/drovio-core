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
		var requestParameters = "__AJAX[path]=" + testUrl;
		
		for (var name in urlVars)
			requestParameters += "&"+ name +"="+encodeURIComponent(urlVars[name]);
		
		return "/ajax/tester.php?"+requestParameters;
	}
}