// Let the document load
var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	// Init module name parser
	module.init();
});

module = {
	moduleNames : new Array(),
	viewsNames : new Array(),
	queriesNames : new Array(),
	jqContainers : new Array(),
	init : function() {
		// Initial load of module names
		this.loadNames();
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			module.filter();
		});
	},
	loadNames : function() {
		// Load module names
		JSONServerReport.request("/ajax/modules/devcontent/moduleNames.php", "GET", null, this, function(response) {
			// Parse module names
			module.moduleNames = JSONServerReport.getReportPayload(response.mNames);
			module.viewsNames = JSONServerReport.getReportPayload(response.mViews);
			module.queriesNames = JSONServerReport.getReportPayload(response.mQueries);
			
			// Refresh after 5min
			setTimeout(function() {
				module.loadNames();
			}, 5 * 60 * 1000);
		}, null, null, false, null);
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
			
			// Find module query names
			for (var key in module.queriesNames) {
				jqContainer.find("span:contains('"+key+".query')").each(function() {
					// Replace text
					var old_html = jq(this).html();
					var new_html = old_html.replace(key, module.queriesNames[key]);
					jq(this).html(new_html);
				});
			}
		}
	}
}