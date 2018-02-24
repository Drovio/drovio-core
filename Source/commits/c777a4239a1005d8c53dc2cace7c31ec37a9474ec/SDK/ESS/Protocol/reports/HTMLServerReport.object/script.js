// Create HTML Server Report handler object, to handle html-specific server reports
var jq = jQuery.noConflict();
HTMLServerReport = {
	request : function(serverUrl, method, requestData, jqSender, errorCallback, completeCallback, loading, callType, options) {
		// Set HTML report content parser
		var reportSuccessCallback = function(report) {
			HTMLServerReport.parseReportContent(jqSender, report, callType);
		}
			
		// Use new function
		return JSONServerReport.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, errorCallback, completeCallback, loading, options);
	},
	// Parse server report actions, trigger to document
	parseReportContent : function(sender, report, callType) {
		// Get sender data
		var senderAttributes = sender.data(callType);
		var startup = sender.attr("data-startup") || sender.data("startup");
		startup = (startup == "" && jq.type(startup) != "undefined" ? true : startup);
		
		// Remove startup attribute
		if (startup)
			jq(sender).data("startup", true).removeAttr("data-startup");
		
		// Check if sender is in document, If not, reject report content
		if (startup && !jq.contains(document, jq(sender).get(0)))
		{
			logger.log("The sender of the report does no longer exist in the document.");
			return;
		}
		
		// Load body payload
		var contentModified = false;
		for (key in report) {
			var reportContent = report[key];
			var payload = reportContent.payload;
			var reportType = reportContent.type;
			
			// Take action according to result type
			switch (reportType) {
				case "data":
				case "html":
					// If there is no content, trigger modification and exit
					if (jq(payload).length == 0)
						continue;

					// Get Report Parameters
					var dataHolder = null;
					// If sender is loading at startup, set default holder as sender
					if (startup == true && dataHolder == null)
						dataHolder = sender;
					else if (senderAttributes != undefined)
						dataHolder = senderAttributes.holder;

					// If sender has no holder, get holder from payload
					if (jq.type(dataHolder) == "undefined" || dataHolder == null || dataHolder == "")
						dataHolder = payload.holder;

					// If no holder is given anywhere, get sender
					if (jq.type(dataHolder) == "undefined" || dataHolder == null || dataHolder == "")
						dataHolder = sender;

					var jqHolder = jq(dataHolder, sender).first();
					if (jqHolder.length == 0)
						jqHolder = jq(dataHolder).first();

					// Remove old contents if replace
					var handleDataMethod = payload.method;
					if (handleDataMethod == "replace")
						jqHolder.contents().remove();

					// Append content to holder
					jq(payload.content).appendTo(jqHolder);
					contentModified = true;
						
					break;
				case "popup":
					jq(sender).popup(jq(payload.content));
					contentModified = true;
					break;
			}
		}
		
		
		// Trigger content.modified if content actually modified
		if (contentModified)
			jq(document).trigger("content.modified");
	},
	// Get the report payload's content
	getPayloadContent : function(reportPayload) {
		return reportPayload.content;
	}
}