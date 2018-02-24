moduleLiteral = {
	get : function(moduleID, name) {
		// Check if the scope is set
		var scope = "mdl." + moduleID;
		return literal.get(scope, name);
	},
	load : function(moduleID, callback) {
		// Translate scope and load literals
		var scope = "mdl." + moduleID;
		literal.load(scope, callback);
	}
}