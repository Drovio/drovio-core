jq = jQuery.noConflict();

url = {
	getVar : function(name) {
		return url.getUrlVar(window.location.href, name);
	},
	getUrlVar : function(url, name) {
		var vars = {};
		var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
			if (key in vars){
				if (typeof vars[key] == "string")
					vars[key] = [vars[key]];
				vars[key] = jq.merge(vars[key],[value]);
			}else
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
		var info = this.info();
		return info.sub;
	},
	getDomain : function() {
		var info = this.info();
		return info.domain;

	},
	info : function() {
		// Get init variables
		var fullHost = window.location.host;
		var parts = fullHost.split('.');
		
		// Set sub and domain
		var sub = "";
		var domain = "";
		
		if (parts[0] == "redback")
			sub = "www";
		else {
			sub = parts[0];
			parts = parts.splice(1);
		}
		
		domain = parts.join(".");
		
		var info = new Object();
		info['sub'] = sub;
		info['domain'] = domain;
		
		return info;
	},
	getPathname : function() {
		return encodeURIComponent(window.location.pathname);
	},
	resolve : function(sub, url) {
		// Check the subdomain
		var resolved_url = this.getDomain() + "/"+url;
		resolved_url = (sub == "www" ? "" : sub + ".") + resolved_url;
		return "http://" + resolved_url;
	},
	resource : function(url) {
		// Check if the url is already in http
		if (url.indexOf("http") < 0) 
			url = "http://" + this.getDomain() + "/" + url;
		
		// Return the new url
		return url;
	}
}