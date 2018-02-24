// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	BootLoader.init();
});

// Create Asychronous Communication Protocol Object
BootLoader = {
	resources : new Array(),
	init : function() {
		// Set static resources
		jq("link[href][rel='stylesheet']").each(function() {
			jq(this).data("id", jq(this).data("id")).removeAttr("data-id");
			var resource = BootLoader.addResource(jq(this).data("id"), "css", jq(this).attr("href"));
			resource.scope = "static";
		});
		
		jq('script[src]').each(function() {
			jq(this).data("id", jq(this).data("id")).removeAttr("data-id");
			var resource = BootLoader.addResource(jq(this).data("id"), "js", jq(this).attr("src"));
			resource.scope = "static";
		});
	},
	loadResources : function (report, callback) {
		// Load BootLoader Resources
		for (key in report.head) {
			var header = report.head[key];
			if (header.header_type == "rsrc") {
				var hasCSS = typeof header.css != "undefined";
				var hasJS = typeof header.js != "undefined";
				var rsrcID = header.id;
				
				if (hasCSS) {
					var url = "";
					if (header.tester)
						url = ajaxTester.resolve("/ajax/resources/explicit/css.php");
					else
						url = "/Library/Resources/css/"+header.css+".css";
					
					BootLoader.loadResource(rsrcID, "css", url, header);
				}

				if (hasJS) {
					var url = "";
					if (header.tester)
						url = ajaxTester.resolve("/ajax/resources/explicit/js.php");
					else
						url = "/Library/Resources/js/"+header.js+".js";
					
					BootLoader.loadResource(rsrcID, "js", url, header, callback);
				} else if (typeof callback == 'function') {
					callback.call();
				}
			}
		}
	},
	loadResource : function (rsrcID, type, rsrcUrl, header, callback) {
		// Check if already loaded
		if (this.checkLoaded(rsrcID, type)) {
			if (typeof callback == 'function') {
				callback.call();
			}
			return true;
		}
		
		// Add Resource
		this.addResource(rsrcID, type);

		// Load CSS or JS Resource if not loaded
		switch (type) {
			case "css":
				this.loadCSSResource(rsrcID, rsrcUrl, header.attributes);
				break;
			case "js":
				this.loadJSResource(rsrcID, rsrcUrl, header.attributes, callback);
				break;
		}
	},
	reloadResource : function (rsrcID, type, rsrcUrl, attributes) {
		// Remove resource first
		this.removeResource(rsrcID, type);
		
		// Load CSS or JS Resource
		switch (type) {
			case "css":
				this.loadCSSResource(rsrcID, rsrcUrl, attributes);
				break;
			case "js":
				this.loadJSResource(rsrcID, rsrcUrl, attributes);
				break;
		}
		
		return true;
	},
	removeResource : function (rsrcID, type) {
		// Remove from array
		delete this.resources[rsrcID+"_"+type];
		
		// Remove resource from context
		var flag = false;
		switch (type) {
			case "css":
				flag = jq("link").each(function() {
					if (jq(this).data("id") == rsrcID) {
						jq(this).remove();
						return true;
					}
				});
				break;
			case "js":
				// Check for built in scripts
				flag = jq("script").each(function() {
					if (jq(this).data("id") == rsrcID) {
						jq(this).remove();
						return true;
					}
				});
				
				// Check for dynamic scripts
				break;
		}
		
		return flag;
	},
	loadJSResource : function(rsrcID, rsrcUrl, attributes, callback) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
		
		// Load Resource
		if (attrParams.length > 0)
			rsrcUrl = rsrcUrl+"?"+attrParams.join("&");
		var flag = this.updateResource(rsrcID, "js", rsrcUrl);
		if (!flag)
			this.addResource(rsrcID, "js", rsrcUrl);
		
		BootLoader.loadJS(rsrcUrl, callback);
	},
	loadCSSResource : function(rsrcID, rsrcUrl, attributes) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
			
		// Load Resource
		if (attrParams.length > 0)
			rsrcUrl = rsrcUrl+"?"+attrParams.join("&");
		var flag = this.updateResource(rsrcID, "css", rsrcUrl);
		if (!flag)
			this.addResource(rsrcID, "css", rsrcUrl);
		
		BootLoader.loadCSS(rsrcUrl).data("id", rsrcID);
	},
	checkLoaded : function (id, type) {
		return (jq.type(this.resources[id+"_"+type]) == "undefined" ? false : true);
	},
	addResource : function (id, type, url) {
		var object = new Object();
		object.id = id;
		object.type = type;
		object.url = url;
		this.resources[id+"_"+type] = object;
		
		return this.resources[id+"_"+type];
	},
	updateResource : function (id, type, url) {
		if (!this.checkLoaded(id, type))
			return false;

		this.resources[id+"_"+type].url = url;
		return true;
	},
	loadJS : function(href, callback) {
		ascop.getScript(href, function(ev) {
			jq(document).trigger("ready");
			jq(document).trigger("content.modified");
			
			// run successCallback function, if any
			if (typeof callback == 'function') {
				callback.call();
			}
		});
	},
	loadCSS : function(href, tester) {
		return jq("<link rel='stylesheet' href='"+href+"'>").appendTo(jq("head"));
	}
};