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
		// Set silent
		ajaxOptions = new Object();
		ajaxOptions.dataType = dataType;
		ajaxOptions.silent = silent;
		ajaxOptions.withCredentials = ajaxCredentials;
		if (jq.type(ajaxCredentials) === "undefined")
			ajaxOptions.withCredentials = true;
		
		ajaxOptions.completeCallback = completeCallback;
			
		// Use new function
		return this.request(ajaxUrl, method, data, sender, successCallback, ajaxOptions);
	},
	getScript : function(script, callback) {
		// Resolve Url Resource
		script = url.resource(script);
		
		// Get script
		jq.getScript(script, callback);
	},
	request : function(ajaxUrl, method, data, sender, successCallback, ajaxOptions) {
		// Init object for default ajax options
		var options = {
			silent: false,
			withCredentials: false,
			cache: true,
			crossDomain: true,
			processData: false,
			contentType: "application/x-www-form-urlencoded; charset=UTF-8",
			dataType: "json",
			xhr: jq.ajaxSettings.xhr()
		}
		
		// Extend ajax options
		options = jq.extend(options, ajaxOptions);
		
		// Request counter
		this.counter++;
		
		// Set Loading page
		if (!options.silent) {
			this.loadingCounter++;
			jq('html').addClass('loading');
		}
		
		if (method == "POST") {
			// Create form data
			var ascopData = new FormData();
			
			// Load string data
			if (jq.type(data) == "string") {
				var parts = data.split("&");
				for (var p in parts) {
					var parts2 = parts[p].split("=");
					ascopData.append(parts2[0], parts2[1]);
				}
			} else {
				ascopData = data;
			}
			
			// Add ajax ascop variables
			ascopData.append("__ASCOP[REQUEST_PATH]", url.getPathname());
			ascopData.append("__ASCOP[REQUEST_SUB]", url.getSubdomain());
			
			data = ascopData;
		} else {
			// Add ajax ascop variables
			data += "&__ASCOP[REQUEST_PATH]=" + url.getPathname();
			data += "&__ASCOP[REQUEST_SUB]=" + url.getSubdomain();
		}
		
		// Resolve url
		ajaxUrl = url.resource(ajaxUrl);
		
		// Decide whether ajax tester is on
		if (ajaxTester.status()) {
			// Add parameter and set url
			if (method == "POST")
				data.append("__AJAX[path]", ajaxUrl);
			else
				data += "&__AJAX[path]=" + ajaxUrl;
			ajaxUrl = url.resource("/ajax/tester.php");
		}

		var ascopID = "ascopRequest_"+Math.random()+"_"+(new Date()).getTime();
		var request = jq.ajax({
			url: ajaxUrl,
			type: method,
			data: data,
			context: sender,
			dataType: options.dataType,
			contentType: options.contentType,
			processData: options.processData,
			cache: options.cache,
			crossDomain: options.crossDomain,
			success: function(response, status, xhr) {
				// run successCallback function, if any
				if (typeof successCallback == 'function') {
					successCallback.call(sender, response, status, xhr);
				}
			},
			complete: function(jqXHR, textStatus) {
				ascop.counter--;
				if (!options.silent)
					ascop.loadingCounter--;
				if (ascop.loadingCounter < 1)
					jq('html').removeClass('loading');
					
				// run completeCallback function, if any
				if (typeof options.completeCallback == 'function') {
					options.completeCallback.call(sender, jqXHR);
				}
				
				// Delete request from queue
				var requests = jq(document).data("ascopRequests");
				delete requests[jqXHR.ascopID];
			},
			error: function(jqXHR, textStatus, errorThrown){
				// run errorCallback function, if any
				if (typeof options.errorCallback == 'function') {
					options.errorCallback.call(sender, jqXHR);
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
				withCredentials: options.withCredentials,
				ascopID: ascopID
			},
			xhr: function() {
				// Check if xhr is function and execute it
				if (typeof options.xhr == 'function')
					return options.xhr.call(sender);
				
				// Otherwise just return the object
				return options.xhr
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
	}
}