var jq = jQuery.noConflict();
dlocalStorage = {
	set : function(st_name, st_value, st_session) {
		// Check if Storage is supported
		if (typeof(Storage) === "undefined")
			return false;

		// Don't keep state in session as default
		var inSession = st_session;
		if (typeof(inSession) === "undefined")
			inSession = false;

		// Check state and convert to JSON if possible
		if (jq.type(st_value) == "array" || typeof(st_value) == "object")
			try {
				st_value = JSON.stringify(st_value);
			} catch(err) {
				return false;
			}
		
		// If not string, try to convert to string
		if (typeof(st_value) != "string")
			try {
				st_value = String(st_value);
			} catch(err) {
				return false;
			}

		// Set local storage
		if (inSession)
			sessionStorage.setItem(st_name, st_value)
		else
			localStorage.setItem(st_name, st_value);

		return true;
	},
	get : function(st_name, st_session) {
		// Check if Storage is supported
		if (typeof(Storage) === "undefined")
			return false;

		// Don't get state from session as default
		var inSession = st_session;
		if (typeof(inSession) === "undefined")
			inSession = false;
		
		// Don't get state from session as default
		var st_value = null;
		if (inSession)
			st_value = sessionStorage.getItem(st_name);
		else
			st_value = localStorage.getItem(st_name);

		try {
			st_value = jQuery.parseJSON(st_value);
		} catch(err) {}

		// Return storage value
		return st_value;
	},
	remove : function(st_name, st_session) {
		// Check if Storage is supported
		if (typeof(Storage) === "undefined")
			return false;

		// Don't get state from session as default
		var inSession = st_session;
		if (typeof(inSession) === "undefined")
			inSession = false;
		
		// Don't get state from session as default
		var st_value = null;
		if (inSession)
			st_value = sessionStorage.removeItem(st_name);
		else
			st_value = localStorage.removeItem(st_name);
		
		return true;
	}
}