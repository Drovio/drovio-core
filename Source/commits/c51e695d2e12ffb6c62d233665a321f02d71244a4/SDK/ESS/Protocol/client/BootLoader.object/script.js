// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	BootLoader.init();
});

// Create Asychronous Communication Protocol Object
BootLoader = {
	resources : new Array(),
	init : function() {
		// Init Boot Loader
	},
	processReport : function(report, callback) {
		// Show the report if debugger is active
		if (debuggr.status())
			console.log(report);
		
		// Get Page Title
		if (jq("#page_title").length == 1) {
			title = jq("#page_title").text();
			jq("title").text(title);
		}
		
		// Load Resources
		if (jq("#head", report).length > 0)
		{
			jq("#head", report).find("rsrc").each(function() {
				var pkgAttr = jq(this).data("attr");
				if (typeof pkgAttr != "undefined") {
					var clearedPkgAttr = pkgAttr;
					var hasCSS = typeof pkgAttr.css != "undefined";
					var hasJS = typeof pkgAttr.js != "undefined";
					var rsrcID = clearedPkgAttr.id;
					delete clearedPkgAttr.js;
					delete clearedPkgAttr.css;
					delete clearedPkgAttr.id;
					
					if (hasCSS)
						BootLoader.loadResource(rsrcID, "css",clearedPkgAttr);

					if (hasJS)
						BootLoader.loadResource(rsrcID, "js", clearedPkgAttr);
				}
			});
		}
		
		// Get Log
		Logger.loadLogs(report);
		
		// Execute Callback
		if (typeof callback == 'function') {
			callback.call(this, jq("<div>").append(jq(report).find("#body").first()).html());
		}
	},
	loadResource : function (rsrcID, type, attributes) {
		// Check if already loaded
		if (this.checkLoaded(type, rsrcID))
			return true;

		// Load CSS or JS Resource if not loaded
		switch (type) {
			case "css":
				this.loadCSSResource(attributes);
				break;
			case "js":
				this.loadJSResource(attributes);
				break;
		}
		
		// Set Package Loaded
		this.setLoaded(type, rsrcID);
	},
	loadJSResource : function(attributes) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
		
		// Load Resource
		var jsUrl = url.resource("/ajax/resources/explicit/js.php?"+attrParams.join("&"));
		BootLoader.loadJS(jsUrl);
	},
	loadCSSResource : function(attributes) {
		// Set Attributes
		var attrParams = new Array();
		for (var attr in attributes)
			attrParams.push(attr + "=" + encodeURIComponent(attributes[attr]));
			
		// Load Resource
		var cssUrl = url.resource("/ajax/resources/explicit/css.php?"+attrParams.join("&"));
		BootLoader.loadCSS(cssUrl);
	},
	checkLoaded : function (type, id) {
		for (var i=0; i<this.resources.length; i++)
			if (this.resources[i].type == type && this.resources[i].id == id)
				return true;

		return false;
	},
	setLoaded : function (type, id) {
		var object = new Object();
		object.type = type;
		object.id = id;
		this.resources[this.resources.length] = object;
	},
	loadJS : function(href) {
		jq.getScript(href, function() {
			jq(document).trigger("ready");
			jq(document).trigger("content.modified");
		});
	},
	loadCSS : function(href) {
		jq("<link rel='stylesheet' href='"+href+"'>").appendTo(jq("head"));
	}
};