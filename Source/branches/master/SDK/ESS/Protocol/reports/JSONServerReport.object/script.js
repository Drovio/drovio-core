var jq = jQuery.noConflict();

// Create HTML Server Report handler object
JSONServerReport = {
	request : function(serverUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading, options) {
		// Set JSON report content parser
		var reportSuccessCallback = function(report) {
			// run successCallback function, if any
			if (typeof successCallback == 'function')
				successCallback.call(jqSender, report);
			
			// Parse report for actions
			JSONServerReport.parseReportActions(jqSender, report);
		}
			
		// Use new function
		return ServerReport.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, errorCallback, completeCallback, loading, options);
	},
	// Parse server report actions, trigger to document
	parseReportActions : function(sender, report) {
		// Load body payload
		for (key in report) {
			var reportContent = report[key];
			var payload = reportContent.payload;
			var reportType = reportContent.type;
			
			// Filter only actions and trigger to document
			switch (reportType) {
				case "action":
					// Get action and trigger to document
					var action = payload;
					if (jq.type(sender) != "undefined" && jq.contains(document.documentElement, sender.get(0)))
						sender.trigger(action.type, action.value);
					else
						jq(document).trigger(action.type, action.value);
					break;
			}
		}
	},
	// Get the report payload
	getReportPayload : function(reportContent) {
		return reportContent.payload;
	}
}