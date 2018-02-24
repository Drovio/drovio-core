var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	ApplicationProtocol.init();
});

// Create Module Communication Protocol Object based on AsCoP
ApplicationProtocol = {
	init: function() {
		// Self-triggered AJAX Content (Load)
		jq(document).on("loadContent", "[data-app-ajx]", function(ev, loading, extraParams) {
			// Stops the Bubbling
			ev.stopPropagation();
			
			// (Re-) Load content if empty
			if (jq(this).contents().length == 0)
				jq(this).trigger("reload", loading, extraParams);
		});
		// Self-triggered AJAX Content (Reload)
		jq(document).on("reload", "[data-app-ajx]", function(ev, loading, extraParams) {
			// Stops the Bubbling
			ev.stopPropagation();
			
			// Load Content
			ApplicationProtocol.request(jq(this), "GET", extraParams, null, null, null, loading, "app-ajx");
		});
		
		// Triggerable GET Content
		jq(document).on('click action', "*[data-app-gt]", function(ev, preventAction) {
			// Prevent Module Action
			if (preventAction)
				return;
			
			// Trigger loader
			ApplicationProtocol.request(jq(this), "GET", null, null, null, null, null, "app-gt");
		});
		
		// Download content action listener
		jq(document).on('click action', "*[data-app-dl]", function(ev, preventAction) {
			// Prevent Module Action
			if (preventAction)
				return;
			
			// Trigger download request
			ApplicationProtocol.downloadRequest(jq(this), null, "app-dl");
		});
		
		// Initialize Module Protocol Event Listeners
		jq(document).on("content.modified", function() {
			ApplicationProtocol.contentModified();
		});
	},
	contentModified : function() {
		// Self-triggered AJAX Content (Load)
		jq("[data-app-ajx]").off("load");
		jq("[data-app-ajx]").on("load", function(ev, loading, extraParams) {
			// Stops the Bubbling
			ev.stopPropagation();
			jq(this).trigger("loadContent", loading, extraParams);
		});
		
		// Set attr as ajax object and remove from html
		jq("[data-attr]").each(function(){
			jq(this).data("attr", jq(this).data("attr")).removeAttr("data-attr");
		});
		
	},
	// Request module
	request : function(jqSender, method, extraParams, successCallback, errorCallback, completeCallback, loading, callType) {
		// Initialize variables to send
		var AppData = jqSender.data(callType);
		var attrData = jqSender.data("attr");
		var requestData = this.getApplicationRequestData(AppData, "GET", attrData, extraParams);
		
		// Set page loading indicator
		if (AppData.loading)
			loading = true;
		
		// Set application script to call
		var requestUrl = "/ajax/apps/load.php";
		var subdomain = url.getSubdomain();
		if (subdomain == "developers")
			requestUrl = "/ajax/apps/tester.php";
		
		if (jq.type(successCallback) == "undefined" || jq.type(successCallback) == "null") {
			// Start HTMLServerReport request
			HTMLServerReport.request(requestUrl, method, requestData, jqSender, errorCallback, completeCallback, loading, callType);
		}
		else {
			// Start JSONServerReport request
			JSONServerReport.request(requestUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading);
		}
	},
	// Download request
	downloadRequest: function(jqSender, extraParams, callType) {
		// Initialize variables to send
		var ModuleData = jqSender.data(callType);
		var attrData = jqSender.data("attr");
		var requestData = this.getApplicationRequestData(ModuleData, "GET", attrData, extraParams);
		
		// Set download url and trigger download request
		// Set application script to call
		var subdomain = url.getSubdomain();
		var downloadUrl = (subdomain == "developers" ? "/ajax/apps/tester.php" : "/ajax/apps/load.php");
		downloadUrl += "?" + requestData;
		downloadUrl = ajaxTester.resolve(downloadUrl);
		downloadUrl = url.resource(decodeURIComponent(downloadUrl));
		ServerReport.downloadRequest(downloadUrl, jqSender);
	},
	getApplicationRequestData : function(SenderData, method, Attributes, extraParams) {
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
		for (var act in Attributes)
			requestData += "&" + act + "=" + encodeURIComponent(Attributes[act]);
		
		// Finish request data if extra params are empty
		if (typeof extraParams == 'undefined')
			return requestData;
			
		// Add extra variables
		if (typeof extraParams === 'string')
			requestData += "&" + extraParams;
		else
			for (var exp in extraParams)
				requestData += "&" + exp + "=" + encodeURIComponent(extraParams[exp]);

		return requestData;
	}
}