var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	ModuleProtocol.init();
});

// Create Module Communication Protocol Object based on AsCoP
ModuleProtocol = {
	init: function() {
		// Self-triggered AJAX Content (Load)
		jq(document).on("loadContent", "[data-mdl-ajx]", function(ev, loading) {
			// Stops the Bubbling
			ev.stopPropagation();
			
			// (Re-) Load content if empty
			if (jq(this).contents().length == 0)
				jq(this).trigger("reload", loading);
		});
		// Self-triggered AJAX Content (Reload)
		jq(document).on("reload", "[data-mdl-ajx]", function(ev, loading) {
			// Stops the Bubbling
			ev.stopPropagation();
			
			// Load Content
			ModuleProtocol.trigger(ev, jq(this), "GET", "mdl-ajx", null, null, null, null, loading);
		});
		
		// Triggerable GET Content
		jq(document).on('click action', "*[data-mdl-gt]", function(ev, preventAction) {
			// Prevent Module Action
			if (preventAction)
				return;
			
			// Trigger loader
			ModuleProtocol.trigger(ev, jq(this), "GET", "mdl-gt", null, null, null, null);
		});
		
		// Download content action listener
		jq(document).on('click action', "*[data-mdl-dl]", function(ev, preventAction) {
			// Prevent Module Action
			if (preventAction)
				return;
			
			// Trigger download request
			ModuleProtocol.downloadRequest(jq(this), null, "mdl-dl");
		});
		
		// Initialize Module Protocol Event Listeners
		jq(document).on("content.modified", function() {
			ModuleProtocol.contentModified();
		});
	},
	contentModified : function() {
		// Self-triggered AJAX Content (Load)
		jq("[data-mdl-ajx]").off("load");
		jq("[data-mdl-ajx]").on("load", function(ev, loading) {
			// Stops the Bubbling
			ev.stopPropagation();
			jq(this).trigger("loadContent");
		});
		
		// Set attr as ajax object and remove from html
		jq("[data-attr]").each(function(){
			jq(this).data("attr", jq(this).data("attr")).removeAttr("data-attr");
		});
		
	},
	// Trigger user action before calling module
	triggerAction : function (ev, jqSender, method, callType, extraParams, callback, silent) {
		// Call the new trigger function
		return this.trigger(ev, jqSender, method, callType, extraParams, callback, null, null, !silent);
	},
	// Trigger user action and request module
	trigger : function(ev, jqSender, method, callType, extraParams, successCallback, errorCallback, completeCallback, loading) {
		// Push Page State
		if (jqSender.prop("tagName") == "A" && jq.type(jqSender.attr("href")) != "undefined" && jqSender.attr("href") != "") {
			// Check if key for new tab is clicked (windows and mac), then do nothing
			if (ev.ctrlKey || ev.metaKey)
				return;
			
			// Stops the Default Action (if any)
			ev.preventDefault();
				
			// Check if there is a form preventing unload
			if (jq.type(FormProtocol.preventUnload(".uiMainContainer")) != "undefined") {
				var ans = confirm("All unsaved data will be lost in this page. Navigate ?");
				if (!ans)
					return;
			}
			
			// Set state and load page
			var stateHref = jqSender.attr("href");
			state.push(stateHref);
			
			// Set loading as true
			loading = true;
		}
		
		// Set ModuleProtocol request
		ModuleProtocol.request(jqSender, method, extraParams, successCallback, errorCallback, completeCallback, loading, callType);
	},
	// Request module
	request : function(jqSender, method, extraParams, successCallback, errorCallback, completeCallback, loading, callType) {
		// Initialize variables to send
		var ModuleData = jqSender.data(callType);
		var attrData = jqSender.data("attr");
		var requestData = this.getModuleRequestData(ModuleData, "GET", attrData, extraParams);
		
		// Set page loading indicator
		if (ModuleData.loading)
			loading = true;
		var requestUrl = "/ajax/modules/load.php";
		if (jq.type(successCallback) == "undefined" || jq.type(successCallback) == "null") {
			// Start HTMLServerReport request
			HTMLServerReport.request(requestUrl, method, requestData, jqSender, errorCallback, completeCallback, loading, callType);
		}
		else {
			// Start HTMLServerReport request
			JSONServerReport.request(requestUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading);
		}
	},
	// Download request
	downloadRequest: function(jqSender, extraParams, callType) {
		// Initialize variables to send
		var ModuleData = jqSender.data(callType);
		var attrData = jqSender.data("attr");
		var requestData = this.getModuleRequestData(ModuleData, "GET", attrData, extraParams);
		
		// Set download url and trigger download request
		var downloadUrl = url.resource("/ajax/modules/load.php") + "?" + requestData;
		downloadUrl = ajaxTester.resolve(downloadUrl);
		ServerReport.downloadRequest(downloadUrl, jqSender);
	},
	getModuleRequestData : function(SenderData, method, ModuleAttr, extraParams) {
		// Initialize variables to send
		var requestData = "";
		
		// Get data from sender action
		for (var act in SenderData)
			requestData += "&__REQUEST["+act.toUpperCase()+"]="+encodeURIComponent(SenderData[act]);
		
		// If method is GET, transfer url variables
		if (method.toUpperCase() == "GET") {
			// Get url variables
			var urlVars = url.getVar();
			for (var urlv in urlVars)
				requestData += "&"+urlv+"="+encodeURIComponent(urlVars[urlv]);
		}
		
		// Get All Attributes from context
		for (var act in ModuleAttr)
			requestData += "&" + act + "=" + encodeURIComponent(ModuleAttr[act]);
		
		// Add extra variables
		if (typeof extraParams != 'undefined')
			requestData += "&" + extraParams;
		
		return requestData;
	}
}