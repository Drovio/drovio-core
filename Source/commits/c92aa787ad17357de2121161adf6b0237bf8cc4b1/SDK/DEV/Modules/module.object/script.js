// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// Let the document load
jq(document).one("ready.extra", function() {
	// Init module name parser
	module.init();
});

module = {
	moduleNames : new Array(),
	viewsNames : new Array(),
	jqContainers : new Array(),
	init : function() {
		if (url.getSubdomain() != "developer")
			return;
		// Load module names
		ascop.asyncRequest("/ajax/resources/sdk/modules/moduleNames.php", "GET", null, "json", this, function (response){
				// Set to local variable
				module.moduleNames = response;
			}, null, true, true
		);
		
		// Load views names
		ascop.asyncRequest("/ajax/resources/sdk/modules/viewsNames.php", "GET", null, "json", this, function (response){
				// Set to local variable
				module.viewsNames = response;
			}, null, true, true
		);
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			module.filter();
		});
	},
	addContainer : function(selector) {
		// Add the given container selector to the jqContainers
		if (this.jqContainers.indexOf(selector) < 0)
			this.jqContainers.push(selector);
	},
	filter : function() {
		// Search for each container
		for (var selector in module.jqContainers) {
			// Get jqContainer
			var jqContainer = jq(module.jqContainers[selector]);
			
			// Find module names
			for (var key in module.moduleNames) {
				jqContainer.find("span:contains('m"+key+".module')").each(function() {
					// Replace text
					var old_html = jq(this).html();
					var new_html = old_html.replace("m"+key+".module", module.moduleNames[key]);
					jq(this).html(new_html);
				});
			}
			
			// Find module view names
			for (var key in module.viewsNames) {
				jqContainer.find("span:contains('"+key+".view')").each(function() {
					// Replace text
					var old_html = jq(this).html();
					var new_html = old_html.replace(key, module.viewsNames[key]);
					jq(this).html(new_html);
				});
			}
		}
	}
}