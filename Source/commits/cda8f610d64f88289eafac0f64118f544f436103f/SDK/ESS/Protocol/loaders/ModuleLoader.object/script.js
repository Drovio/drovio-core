// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

// Create Module Loader Object Handler
ModuleLoader = {
	load : function(jqSender, moduleID, viewName, holder, extraParams, successCallback, errorCallback, completeCallback, loading) {
		// Create module attributes
		var ModuleData = new Array();
		ModuleData['ID'] = moduleID;
		ModuleData['ACTION'] = viewName;
		ModuleData['HOLDER'] = holder;
		
		// Get Module's Protocol request data
		var requestData = ModuleProtocol.getModuleRequestData(ModuleData, "GET", null, extraParams);

		// Create async request as GET
		ModuleProtocol.request(jqSender, "GET", requestData, successCallback, errorCallback, completeCallback, loading);
	}
}