var jq = jQuery.noConflict();

// Create Module Communication Protocol Object based on AsCoP
ServerReport = {
	request : function(serverUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading) {
		// Set ajax extra options
		ajaxOptions = new Object();
		ajaxOptions.dataType = "json";
		ajaxOptions.loading = loading;
		ajaxOptions.withCredentials = true;
		
		// Server report callback
		var reportSuccessCallback = function(report) {
			var status = ServerReport.handleReport(jqSender, report, successCallback);
			if (!status && typeof errorCallback == 'function') {
				errorCallback.call(jqSender);
			}
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

		// Check report integrity
		if (report.head == undefined)
			return false;
		
		// Load head resources
		BootLoader.loadResources(report.head, function() {
			// Parse report for content
			if (typeof parseContentCallback == 'function')
				parseContentCallback.call(sender, report.body);
			
			// Check content arrived
			jq(document).trigger("content.modified");
		});
		
		// Check content arrived again
		jq(document).trigger("content.modified");
		
		return true;
	}
}