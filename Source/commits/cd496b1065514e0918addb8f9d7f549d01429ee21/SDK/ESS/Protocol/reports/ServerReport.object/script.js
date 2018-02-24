var jq = jQuery.noConflict();

// Create Module Communication Protocol Object based on AsCoP
ServerReport = {
	request : function(serverUrl, jqSender, method, requestData, successCallback, errorCallback, completeCallback, loading) {
		// Set ajax extra options
		ajaxOptions = new Object();
		ajaxOptions.dataType = "json";
		ajaxOptions.loading = loading;
		ajaxOptions.withCredentials = true;
		
		// Server report callback
		var reportSuccessCallback = function(report) {
			ServerReport.handleReport(jqSender, report, successCallback);
		}
		
		// Set callbacks
		ajaxOptions.completeCallback = completeCallback;
		ajaxOptions.errorCallback = errorCallback;
			
		// Use new function
		return ascop.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, ajaxOptions);
	},
	// Handles server reports
	handleReport : function (sender, report, parseContentCallback) {
		// Show the report if debugger is active
		if (debuggr.status())
			console.log(report);

		// Load head resources
		BootLoader.loadResources(report, function() {
			// Parse report for content
			if (typeof parseContentCallback == 'function')
				parseContentCallback.call(sender, report);
			
			// Parse report for actions
			ServerReport.parseReportActions(sender, report);
		});
		
		// Check content arrived
		jq(document).trigger("content.modified");
		
		return true;
	},
	// Parse server report actions, trigger to document
	parseReportActions : function(sender, report) {
		// Load body context
		for (key in report.body) {
			var reportContent = report.body[key];
			var context = reportContent.context;
			var reportType = reportContent.type;
			
			// Filter only actions and trigger to document
			switch (reportType) {
				case "action":
					// Get action and trigger to document
					var action = context;
					if (jq.type(sender) != "undefined" && jq.contains(document.documentElement, sender.get(0)))
						sender.trigger(action.type, action.value);
					else
						jq(document).trigger(action.type, action.value);
					break;
			}
		}
	}
}