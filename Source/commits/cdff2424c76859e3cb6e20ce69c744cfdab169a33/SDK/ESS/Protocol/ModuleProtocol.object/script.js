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
		
		// Load content at startup
		jq("[data-mdl-startup]").each(function(){
			jq(this).data("startup", true).removeAttr("data-mdl-startup");
			jq(this).trigger("loadContent");
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
		if (jqSender.prop("tagName") == "A") {
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
	// Handles server reports
	handleReport : function (sender, report, callType) {
		console.log("ModuleProtocol handle report function is deprecated. Use ServerReport API.");
		// Show the report if debugger is active
		if (debuggr.status())
			console.log(report);

		// Load head resources
		BootLoader.loadResources(report, function() {
			// Parse report for content and actions
			ModuleProtocol.parseReport(sender, report, callType);
		});
		
		return true;
	},
	parseReport : function(sender, report, callType) {
		// Initialize actions to trigger later
		var actions = new Array();

		// Load body context
		for (key in report.body) {
			var reportContent = report.body[key];
			var context = reportContent.context;
			var reportType = reportContent.type;
			
			// Take action according to result type
			switch (reportType) {
				case "data":
					// If there is no content, trigger modification and exit
					if (jq(context).length == 0)
						continue;
					
					// Get Report Parameters
					var dataHolder = null;
					// If sender is loading at startup, set default holder as sender
					if (sender.data("startup") == true && dataHolder == null)
						dataHolder = sender;
					else if (sender.data(callType) != undefined)
						dataHolder = sender.data(callType).holder;
					// If sender has no holder, get holder from context
					if (jq.type(dataHolder) == "undefined" || dataHolder == null || dataHolder == "")
						dataHolder = reportContent.holder;

					// If no holder is given anywhere, get sender
					if (jq.type(dataHolder) == "undefined" || dataHolder == null || dataHolder == "")
						dataHolder = sender;

					var jqHolder = jq(dataHolder, sender);
					if (jqHolder.length == 0)
						jqHolder = jq(dataHolder).first();

					// Remove old contents if replace
					var handleDataMethod = reportContent.method;
					if (handleDataMethod == "replace")
						jqHolder.contents().remove();

					// Append content to holder
					jq(context).appendTo(jqHolder);
						
					break;
				case "popup":
					jq(sender).popup(jq(context));
					break;
				case "action":
					// Add action to stack
					actions.push(context);
					break;
			}
		}
		
		// Trigger actions
		for (key in actions) {
			// Get action and trigger to document
			var action = actions[key];
			if (jq.type(sender) != "undefined" && jq.contains(document.documentElement, sender.get(0)))
				sender.trigger(action.type, action.value);
			else
				jq(document).trigger(action.type, action.value);
		}
		
		// Check content arrived
		jq(document).trigger("content.modified");
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