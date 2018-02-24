// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

// Create Module Loader Object Handler
ModuleLoader = {
	load : function(jqSender, moduleID, viewName, holder, extraParams, callback, silent) {
	
		// Create module attributes
		var moduleAttrs = new Array();
		moduleAttrs['ID'] = moduleID;
		moduleAttrs['ACTION'] = viewName;
		moduleAttrs['HOLDER'] = holder;
		
		// Create async request as GET
		ModuleLoader.ascopRequest(jqSender, moduleAttrs, "GET", null, extraParams, callback, silent);
	}
}