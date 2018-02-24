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
		return ACProtocol.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, ajaxOptions);
	},
	downloadRequest : function(downloadUrl, jqSender) {
		// Create HTMLFrame to download content
		var frameID = "dlfi" + Math.round(Math.random() * 10000000);
		var jqFrame = HTMLFrame.create(downloadUrl, "", frameID, "dl_frame", null);
		
		// Add frame to popup to document
		jqSender.popup.binding = "on";
		jqSender.popup.position = {
			"top" : -500,
			"left" : -500,
			"position" : "absolute"
		};
		
		// Create popup
		jqSender.popup(jqFrame);
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