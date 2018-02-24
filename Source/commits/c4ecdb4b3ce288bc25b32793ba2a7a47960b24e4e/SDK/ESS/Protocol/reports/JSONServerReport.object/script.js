var jq = jQuery.noConflict();

// Create HTML Server Report handler object
JSONServerReport = {
	request : function(serverUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading) {
		// Set JSON report content parser
		var reportSuccessCallback = function(report) {
			// run successCallback function, if any
			if (typeof successCallback == 'function')
				successCallback.call(jqSender, report);
			
			// Parse report for actions
			JSONServerReport.parseReportActions(jqSender, report);
		}
			
		// Use new function
		return ServerReport.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, errorCallback, completeCallback, loading);
	},
	// Parse server report actions, trigger to document
	parseReportActions : function(sender, report) {
		// Load body context
		for (key in report) {
			var reportContent = report[key];
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