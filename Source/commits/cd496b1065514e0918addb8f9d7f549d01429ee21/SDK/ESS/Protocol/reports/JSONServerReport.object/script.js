var jq = jQuery.noConflict();

// Create HTML Server Report handler object
JSONServerReport = {
	request : function(serverUrl, jqSender, method, requestData, successCallback, errorCallback, completeCallback, loading) {
	
		// Set JSON report content parser
		var reportSuccessCallback = function(report) {
			// run successCallback function, if any
			if (typeof successCallback == 'function')
				successCallback.call(jqSender, report);
		}
			
		// Use new function
		ServerReport.request(serverUrl, jqSender, method, requestData, reportSuccessCallback, errorCallback, completeCallback, loading);
	}
}