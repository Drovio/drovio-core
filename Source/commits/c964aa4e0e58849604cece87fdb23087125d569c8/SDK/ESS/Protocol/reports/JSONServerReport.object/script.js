var jq = jQuery.noConflict();

// Create HTML Server Report handler object
JSONServerReport = {
	request : function(serverUrl, method, requestData, jqSender, successCallback, errorCallback, completeCallback, loading) {
	
		// Set JSON report content parser
		var reportSuccessCallback = function(report) {
			// run successCallback function, if any
			if (typeof successCallback == 'function')
				successCallback.call(jqSender, report);
		}
			
		// Use new function
		return ServerReport.request(serverUrl, method, requestData, jqSender, reportSuccessCallback, errorCallback, completeCallback, loading);
	}
}