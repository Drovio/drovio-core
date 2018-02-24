var jq = jQuery.noConflict();

// Create HTML Server Report handler object
HTMLServerReport = {
	request : function(serverUrl, method, requestData, jqSender, errorCallback, completeCallback, loading, callType) {
		// Set HTML report content parser
		var reportSuccessCallback = function(report) {
			HTMLServerReport.parseReportContent(jqSender, report, callType);
		}
			
		// Use new function
		return JSONServerReport.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, errorCallback, completeCallback, loading);
	},
	// Parse server report actions, trigger to document
	parseReportContent : function(sender, report, callType) {
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
			}
		}
	}
}