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
	loadResources : function (report) {
		// Load BootLoader Resources
		for (key in report.head) {
			var header = report.head[key];
			if (header.header_type == "rsrc") {
				var hasCSS = typeof header.css != "undefined";
				var hasJS = typeof header.js != "undefined";
				var rsrcID = header.id;
				
				if (hasCSS) {
					var rsrcUrl = "/ajax/resources/explicit/css.php";
					BootLoader.loadResource(rsrcID, "css", rsrcUrl, header);
				}

				if (hasJS) {
					var rsrcUrl = "/ajax/resources/explicit/js.php";
					BootLoader.loadResource(rsrcID, "js", rsrcUrl, header);
				}
			}
		}
	},
	loadResource : function (rsrcID, type, rsrcUrl, header) {
		// Check if already loaded
		if (this.checkLoaded(rsrcID, type))
			return true;
		
		// Add Resource
		this.addResource(rsrcID, type);

		// Load CSS or JS Resource if not loaded
		switch (type) {
			case "css":
				this.loadCSSResource(rsrcID, rsrcUrl, header.attributes);
				break;
			case "js":
				this.loadJSResource(rsrcID, rsrcUrl, header.attributes);
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
	loadJSResource : function(rsrcID, rsrcUrl, attributes) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
		
		// Load Resource
		var jsUrl = url.resource(rsrcUrl+"?"+attrParams.join("&"));
		var flag = this.updateResource(rsrcID, "js", jsUrl);
		if (!flag)
			this.addResource(rsrcID, "js", jsUrl);
		BootLoader.loadJS(jsUrl);
	},
	loadCSSResource : function(rsrcID, rsrcUrl, attributes) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
			
		// Load Resource
		var cssUrl = url.resource(rsrcUrl+"?"+attrParams.join("&"));
		var flag = this.updateResource(rsrcID, "css", cssUrl);
		if (!flag)
			this.addResource(rsrcID, "css", cssUrl);
		BootLoader.loadCSS(cssUrl).data("id", rsrcID);
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
	loadJS : function(href) {
		ascop.getScript(href, function() {
			jq(document).trigger("ready");
			jq(document).trigger("content.modified");
		});
	},
	loadCSS : function(href) {
		return jq("<link rel='stylesheet' href='"+ascop.resolve(href)+"'>").appendTo(jq("head"));
	}
};