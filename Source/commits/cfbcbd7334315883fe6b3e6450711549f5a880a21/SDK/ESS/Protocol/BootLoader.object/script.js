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
			var rsrcStatic = jq(this).data("static");
			rsrcStatic = (jq.type(rsrcStatic) != "undefined" && rsrcStatic == "" ? true : false);
			jq(this).data("static", rsrcStatic).removeAttr("data-static");
			jq(this).data("category", jq(this).data("category")).removeAttr("data-category");
			var attributes = new Array();
			attributes.category = jq(this).data("category");
			var resource = BootLoader.addResource(jq(this).data("id"), "css", jq(this).attr("href"), attributes, jq(this).data("static"));
		});
		
		jq('script[src]').each(function() {
			var resourceID = jq(this).data("id");
			if (resourceID == undefined)
				return true;
			var rsrcStatic = resourceID.indexOf("static:") >= 0;
			resourceID = resourceID.replace("static:", "");
			jq(this).data("id", resourceID).removeAttr("data-id");
			jq(this).data("static", rsrcStatic);
			jq(this).data("category", jq(this).data("category")).removeAttr("data-category");
			var attributes = new Array();
			attributes.category = jq(this).data("category");
			var resource = BootLoader.addResource(jq(this).data("id"), "js", jq(this).attr("src"), attributes, rsrcStatic);
		});
	},
	loadResources : function (report, callback) {
		// Load BootLoader Resources
		var callbackCalled = false;
		for (var rsrcID in report['bt_rsrc']) {
			var btResource = report['bt_rsrc'][rsrcID];
			var hasCSS = (jq.type(btResource.css) != "undefined");
			var hasJS = (jq.type(btResource.js) != "undefined");
			if (hasCSS) {
				var cssUrl = btResource.css;
				var static = true;
				if (btResource.tester)
					static = false;
				BootLoader.loadResource(rsrcID, "css", cssUrl, btResource.attributes, null, static);
			}

			if (hasJS) {
				var jsUrl = btResource.js;
				var static = true;
				if (btResource.tester)
					static = false;

				// Check if callback is already called
				if (callbackCalled)
					callback = null;
				BootLoader.loadResource(rsrcID, "js", jsUrl, btResource.attributes, callback, static);
				callbackCalled = true;
			}
		}
		
		// Callback execution in case of empty header
		if (!callbackCalled && typeof callback == 'function')
			callback.call();
	},
	loadResource : function (rsrcID, type, rsrcUrl, attributes, callback, static) {
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
				this.loadCSSResource(rsrcID, rsrcUrl, attributes, static);
				break;
			case "js":
				this.loadJSResource(rsrcID, rsrcUrl, attributes, callback, static);
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
		// Update resource
		var flag = this.updateResource(rsrcID, "js", rsrcUrl, attributes, static);
		if (!flag)
			this.addResource(rsrcID, "js", rsrcUrl, attributes, static);
		
		BootLoader.loadJS(rsrcUrl, callback);
	},
	loadCSSResource : function(rsrcID, rsrcUrl, attributes, static) {
		// Update resource
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
		object.static = (static == undefined ? false : static);
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