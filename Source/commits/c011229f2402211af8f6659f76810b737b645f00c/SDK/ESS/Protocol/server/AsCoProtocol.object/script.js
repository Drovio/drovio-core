// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	ascop.init();
});

// Create Asychronous Communication Protocol Object
ascop = {
	counter : 0,
	loadingCounter : 0,
	init : function() {
		// Handles preventDefault action on every weblink with href="" or href="#" or has handler onclick
		jq(document).on("click", "a[href=''], a[href='#'], a[onclick]", function(ev) {
			ev.preventDefault();
		});
		
		// Noscript Handler
		if (url.getVar('_rb_noscript') || cookies.get("noscript") == "1")
		{
			var hrf = window.location.href;
			var new_hrf = url.removeVar(hrf, "_rb_noscript");
			cookies.set("noscript", "0", -1, "/");
			if (hrf != new_hrf)
				window.location = new_hrf;
			else
				location.reload();
		}
		
		// Cancel async requests and trigger cancel event
		jq(document).on("keydown", function(ev){
			// On escape key
			if (ev.which != 27)
				return;
			
			// Abort all requests in queue
			var requests = jq(document).data("ascopRequests");
			var keysToAbort = new Array();
			for (requestID in requests) {
				// Abort request
				requests[requestID].abort();
				
				// Add key to array to abort
				keysToAbort.push(requests[requestID].ascopID);
			}
			
			// Remove data from document
			jq(document).removeData("ascopRequests");
			
			// Bubble ajax cancel.ascop event or trigger on document
			jq(document).trigger("cancel.ascop", keysToAbort);
		});
	},
	asyncRequest : function(ajaxUrl, method, data, dataType, sender, successCallback, completeCallback, silent, ajaxCredentials) {
		// Loading page
		this.counter++;
		if (!silent)
			this.loadingCounter++;
		
		// Set silent
		if (jq.type(silent) === "undefined")
			silent = false;
		if (!silent)
			jq('html').addClass('loading');
			
		// Send ascop variables
		AsCoPParams = data;
		AsCoPParams += "&__ASCOP[REQUEST_PATH]=" + url.getPathname();
		AsCoPParams += "&__ASCOP[REQUEST_SUB]=" + url.getSubdomain();
		
		if (jq.type(ajaxCredentials) === "undefined")
			ajaxCredentials = false;
		
		// Resolve url
		ajaxUrl = url.resource(ajaxUrl);
		
		// Decide whether ajax tester is on
		if (ajaxTester.status()) {
			// Add parameter and set url
			AsCoPParams += "&__AJAX[path]=" + ajaxUrl;
			ajaxUrl = url.resource("/ajax/tester.php");
		}
		
		var ascopID = "ascopRequest_"+Math.random()+"_"+(new Date()).getTime();
		var request = jq.ajax({
			url: ajaxUrl,
			data: AsCoPParams,
			processData: false,
			type: method,
			context: sender,
			dataType: dataType,
			contentType: "application/x-www-form-urlencoded; charset=UTF-8",
			cache: true,
			crossDomain: true,
			success: function(result) {
				// run successCallback function, if any
				if (typeof successCallback == 'function') {
					successCallback.call(sender, result);
				}
			},
			complete: function(jqXHR, textStatus) {
				ascop.counter--;
				if (!silent)
					ascop.loadingCounter--;
				if (ascop.loadingCounter < 1)
					jq('html').removeClass('loading');
					
				// run completeCallback function, if any
				if (typeof completeCallback == 'function') {
					completeCallback.call(sender, jqXHR);
				}
				
				// Delete request from queue
				var requests = jq(document).data("ascopRequests");
				delete requests[jqXHR.ascopID];
			},
			error: function(jqXHR, textStatus, errorThrown){
				// run errorCallback function, if any
				if (typeof errorCallback == 'function') {
					errorCallback.call(sender, jqXHR);
				}
			},
			statusCode: {
				404: function() {
					// Report error
				},
				500: function() {
					// Report error
				}
			},
			xhrFields: { 
				withCredentials: ajaxCredentials,
				ascopID: ascopID
			}
		});
		
		// Set ascop id
		// Add request to queue
		var requests = jq(document).data("ascopRequests");
		if (jq.type(requests) == "undefined")
			requests = new Object();
		requests[ascopID] = request;
		
		// Set data
		jq(document).data("ascopRequests", requests);

		return request;
	},
	getScript : function(script, callback) {
		// Decide whether ajax tester is on
		script = this.resolve(script);
		
		// Resolve Url
		script = url.resource(script);
		
		// Get script
		jq.getScript(script, callback);
	},
	resolve : function(tUrl) {
		// If no ajax tester, return url unharmed
		if (!ajaxTester.status())
			return tUrl;
		
		// Get url
		var parts = tUrl.split('?');
		var testUrl = parts[0];
		
		// Get variables
		var urlVars = url.getUrlVar(tUrl);
		
		// Add testing parameter
		var requestParameters = "__AJAX[path]=" + testUrl;
		
		for (var name in urlVars)
			requestParameters += "&"+ name +"="+encodeURIComponent(urlVars[name]);
		
		return "/ajax/tester.php?"+requestParameters;
	}
}