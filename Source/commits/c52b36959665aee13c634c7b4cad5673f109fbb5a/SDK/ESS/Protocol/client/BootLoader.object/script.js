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
			jq(this).data("static", jq(this).data("static")).removeAttr("data-static");
			var resource = BootLoader.addResource(jq(this).data("id"), "css", jq(this).attr("href"), new Array(), jq(this).data("static"));
		});
		
		jq('script[src]').each(function() {
			var resourceID = jq(this).data("id");
			var static = resourceID.indexOf("static:") >= 0;
			resourceID = resourceID.replace("static:", "");
			jq(this).data("id", resourceID).removeAttr("data-id");
			jq(this).data("static", static);
			var resource = BootLoader.addResource(jq(this).data("id"), "js", jq(this).attr("src"), new Array(), jq(this).data("static"));
		});
	},
	loadResources : function (report, callback) {
		// Load BootLoader Resources
		for (key in report.head) {
			var header = report.head[key];
			if (header.type == "rsrc") {
				var hasCSS = typeof header.css != "undefined";
				var hasJS = typeof header.js != "undefined";
				var rsrcID = header.id;
				
				if (hasCSS) {
					var url = "";
					var static = true;
					if (header.tester) {
						url = "/ajax/resources/explicit/css.php";
						static = false;
					}
					else
						url = "/Library/Resources/css/"+header.css+".css";
					BootLoader.loadResource(rsrcID, "css", url, header, null, static);
				}

				if (hasJS) {
					var url = "";
					var static = true;
					if (header.tester) {
						url = "/ajax/resources/explicit/js.php";
						static = false;
					}
					else
						url = "/Library/Resources/js/"+header.js+".js";
					
					BootLoader.loadResource(rsrcID, "js", url, header, callback, static);
				} else if (typeof callback == 'function') {
					callback.call();
				}
			}
		}
	},
	loadResource : function (rsrcID, type, rsrcUrl, header, callback, static) {
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
				this.loadCSSResource(rsrcID, rsrcUrl, header.attributes, static);
				break;
			case "js":
				this.loadJSResource(rsrcID, rsrcUrl, header.attributes, callback, static);
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
	loadJSResource : function(rsrcID, rsrcUrl, attributes, callback, static) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
		
		// Load Resource
		if (attrParams.length > 0)
			rsrcUrl = rsrcUrl+"?"+attrParams.join("&");
		if (jq.type(static) != "undefined" && static == false)
			rsrcUrl = ajaxTester.resolve(rsrcUrl);
		var flag = this.updateResource(rsrcID, "js", rsrcUrl, attributes, static);
		if (!flag)
			this.addResource(rsrcID, "js", rsrcUrl, attributes, static);
		
		BootLoader.loadJS(rsrcUrl, callback);
	},
	loadCSSResource : function(rsrcID, rsrcUrl, attributes, static) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
			
		// Load Resource
		if (attrParams.length > 0)
			rsrcUrl = rsrcUrl+"?"+attrParams.join("&");
		if (jq.type(static) != "undefined" && static == false)
			rsrcUrl = ajaxTester.resolve(rsrcUrl);
		var flag = this.updateResource(rsrcID, "css", rsrcUrl, attributes, static);
		if (!flag)
			this.addResource(rsrcID, "css", rsrcUrl, attributes, static);

		rsrcUrl = url.resource(rsrcUrl);
		BootLoader.loadCSS(rsrcUrl).data("id", rsrcID);
	},
	checkLoaded : function (id, type) {
		return (jq.type(this.resources[id+"_"+type]) == "undefined" ? false : true);
	},
	addResource : function (id, type, url, attributes, static) {
		var object = new Object();
		object.id = id;
		object.type = type;
		object.url = url;
		object.attributes = attributes;
		object.static = static;
		this.resources[id+"_"+type] = object;
		
		return this.resources[id+"_"+type];
	},
	updateResource : function (id, type, url, attributes, static) {
		if (!this.checkLoaded(id, type))
			return false;

		this.resources[id+"_"+type].url = url;
		this.resources[id+"_"+type].attributes = attributes;
		this.resources[id+"_"+type].static = static;
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