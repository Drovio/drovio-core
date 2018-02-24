/* 
 * Redback JavaScript Document
 *
 * Title: RedBack URL Manager
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.0
 * DateCreated: 06/11/2012
 * DateRevised: --
 *
 */
 
url = {
	getVar : function(name) {
		return url.getUrlVar(window.location.href, name);
	},
	getUrlVar : function(url, name) {
		var vars = {};
		var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			vars[key] = value;
		});
		
		if (typeof(name) != "undefined")
			return vars[name];
		
		return vars;
	},
	removeVar : function(hrf, vrb) {
		// If URL has no variables, return it
		if (hrf.indexOf("?") == -1)
			return hrf;
		
		// Split variables from URI
		var hr_splitted = hrf.split("?");
		var prefix = hr_splitted[0];
		var vrbles_sec = "?" + hr_splitted[1];
		
		// Remove variable using patterns
		var var_patt = new RegExp(vrb+"=(?=[^&]*)[^&]*[&*]|[&*]"+vrb+"=(?=[^&]*)[^&]*|^\\?"+vrb+"=(?=[^&]*)[^&]*$", "i");
		vrbles_sec = vrbles_sec.replace(var_patt, "");
		var result = prefix + vrbles_sec;
		
		return result;
	},
	redirect : function(url) {
		if (false) {// if site is not trusted, prompt user
			
		}
		else {// If site is trusted
			window.location = url;
		}
	},
	getSubdomain : function() {
		var fullHost = window.location.host;
		var parts = fullHost.split('.');
		var sub = "";
		var domain = "";
		var type = "";

		if (parts.length == 3)
		{
			sub = parts[0];
			domain = parts[1];
			type = parts[2];
			
			if (domain == "redback")
				return sub;
			else
				return "";
		}
		else if (parts.length == 2)
		{
			domain = parts[0];
			type = parts[1];
			if (domain == "redback" && (type == "gr" || type == "com"))
				return "www";
		}
		
		return "";
	},
	getDomain : function() {
		var fullHost = window.location.host;
		var parts = fullHost.split('.');

		var sub = "";
		var domain = "";
		var type = "";

		if (parts.length == 3)
		{
			sub = parts[0];
			domain = parts[1];
			type = parts[2];
		}
		else if (parts.length == 2)
		{
			domain = parts[0];
			type = parts[1];
		}
		
		if (domain == "redback" && (type == "gr" || type == "com"))
			return domain+"."+type;
		
		return fullHost;

	},
	getPathname : function() {
		return encodeURIComponent(window.location.pathname);
	},
	resolve : function(url) {
		// Check the subdomain
		var subdomain = this.getSubdomain();		
		if (subdomain != "" && url.indexOf("http") < 0)
			url = "http://"+this.getDomain()+"/"+url;
		
		// Return the new url
		return url;
	},
	resource : function(url) {
		// Check the subdomain
		var subdomain = this.getSubdomain();		
		if (subdomain != "" && url.indexOf("http") < 0) 
			url = "http://"+this.getDomain()+"/"+url;
		
		// Return the new url
		return url;
	}
}