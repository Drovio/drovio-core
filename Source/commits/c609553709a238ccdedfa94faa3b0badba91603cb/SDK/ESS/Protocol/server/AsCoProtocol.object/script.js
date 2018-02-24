// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	ascop.init();
});

// Create Asychronous Communication Protocol Object
ascop = {
	counter : 0,
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
	},
	asyncRequest : function(ajaxUrl, method, data, dataType, sender, successCallback, completeCallback, silent, ajaxCredentials) {
		// Loading page
		this.counter++;
		if (silent === undefined || silent == false)
			jq('html').addClass('loading');

		// Send ascop variables
		AsCoPParams = data;
		AsCoPParams += "&__ASCOP[REQUEST_PATH]=" + url.getPathname();
		AsCoPParams += "&__ASCOP[REQUEST_SUB]=" + url.getSubdomain();
		
		if (jq.type(silent) === "undefined")
			silent = false;
		
		if (jq.type(ajaxCredentials) === "undefined")
			ajaxCredentials = false;
		
		// Resolve url
		ajaxUrl = url.resource(ajaxUrl);
		
		// Decide whether ajax tester is on
		if (ajaxTester.status()) {
			// Add parameter
			AsCoPParams += "&__AJAX[path]=" + ajaxUrl;
			
			// Set new url
			ajaxUrl = url.resource("/ajax/tester.php");
		}

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
				if (ascop.counter < 1)
					jq('html').removeClass('loading');
					
				// run completeCallback function, if any
				if (typeof completeCallback == 'function') {
					completeCallback.call(sender, jqXHR);
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				// run errorCallback function, if any
				if (typeof errorCallback == 'function') {
					errorCallback.call(sender, jqXHR);
				}
			},
			statusCode: {
				404: function() {
					console.log("There was an error in ajax call: page not found" );
				},
				500: function() {
					console.log("There was an error in ajax call: server error" );
				}
			},
			xhrFields: { 
				withCredentials: ajaxCredentials
			}
		});
		
		//jq(document).data("ascopQueue", request);

		return request;
	},
	getScript : function(script, callback) {
		// Resolve url
		script = url.resource(script);
		
		// Decide whether ajax tester is on
		if (ajaxTester.status()) {
			var testUrl = this.testUrl(script);
			
			// Set new url
			script = url.resource(testUrl);
		}
		
		// Get script
		jq.getScript(script, callback);
	},
	testUrl : function(tUrl) {
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

/*jq(document).on("keydown", function(ev){
	// On escape
	if (ev.which != 27)
		return;
	
	var request = jq(document).data("ascopQueue");
	if (request !== undefined)
		request.abort();
	// Bubble ajax abort event or trigger on document
});*/