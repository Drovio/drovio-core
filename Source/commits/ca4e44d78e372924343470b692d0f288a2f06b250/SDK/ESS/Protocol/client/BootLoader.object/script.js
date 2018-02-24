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
	getResources : function (report) {
		// Load BootLoader Resources
		for (key in report.head) {
			var header = report.head[key];
			if (header.header_type == "rsrc") {
				var hasCSS = typeof header.css != "undefined";
				var hasJS = typeof header.js != "undefined";
				var rsrcID = header.id;
				
				if (hasCSS)
					BootLoader.loadResource(rsrcID, "css", header);

				if (hasJS)
					BootLoader.loadResource(rsrcID, "js", header);
			}
		}
	},
	loadResource : function (rsrcID, type, header) {
		// Check if already loaded
		if (this.checkLoaded(type, rsrcID))
			return true;

		// Load CSS or JS Resource if not loaded
		switch (type) {
			case "css":
				this.loadCSSResource(header.attributes);
				break;
			case "js":
				this.loadJSResource(header.attributes);
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