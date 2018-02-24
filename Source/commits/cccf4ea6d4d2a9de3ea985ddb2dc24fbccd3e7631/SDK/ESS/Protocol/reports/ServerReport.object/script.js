var jq = jQuery.noConflict();

// Create Server Report Object for Redback Communication based on AsCoP
ServerReport = {
	request : function(serverUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading, options) {
		// Set ajax extra options
		var ajaxOptions = new Object();
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
		
		// Extend options
		ajaxOptions = jq.extend(ajaxOptions, options);
			
		// Use new function
		return ascop.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, ajaxOptions);
	},
	// Handles server reports
	handleReport : function (sender, report, parseContentCallback) {
		// Log the report
		logger.log(report);
		
		// Check if report is not null
		if (!report) {
			logger.log("The server report is empty. Aborting content parsing.");
			return false;
		}

		// Check report integrity
		if (report.head == undefined) {
			logger.log("Server Report doesn't contain a header element. Aborting content parsing.");
			return false;
		}
		
		// Load head resources
		BootLoader.loadResources(report.head, function() {
			// Parse report for content
			if (typeof parseContentCallback == 'function')
				parseContentCallback.call(sender, report.body);
		});
		
		return true;
	}
}