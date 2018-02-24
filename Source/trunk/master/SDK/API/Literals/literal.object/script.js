literal = {
	literals : new Array(),
	get : function(scope, name) {
		// Check if the scope is set
		if (jq.type(this.literals[scope]) != "undefined") {
			if (jq.type(name) == "undefined")
				return this.literals[scope];
			else
				return this.literals[scope][name];
		}
			
		// Load literals and return FALSE
		this.load(scope);
		return false;
	},
	load : function(scope, callback) {
		// Else, get literals from server
		var urlParams = "scope="+encodeURIComponent(scope);
		ascop.asyncRequest("/ajax/literals/literals.php", "GET", urlParams, "json", null, function(result) {
			// Set literals and return value
			literal.literals[scope] = result;
			
			// Run callback (if any)
			if (typeof callback == 'function') {
				callback.call(literal, result);
			}
}, null, true, true);
	}
}