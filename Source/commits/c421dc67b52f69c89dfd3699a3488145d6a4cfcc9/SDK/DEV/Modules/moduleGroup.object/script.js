// Let the document load
var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	// Init moduleGroup name parser
	moduleGroup.init();
});

moduleGroup = {
	groupNames : new Array(),
	jqContainers : new Array(),
	init : function() {		
		// Initial load of module group names
		moduleGroup.loadNames();
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			moduleGroup.filter();
		});
	},
	loadNames : function() {
		// Load group names
		JSONServerReport.request("/ajax/modules/devcontent/moduleGroups.php", "GET", null, this, function(response) {
			// Parse module groups
			moduleGroup.groupNames = response.mGroups.context;
			
			// Refresh after 5min
			setTimeout(function() {
				moduleGroup.loadNames();
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
		for (var selector in moduleGroup.jqContainers) {
			// Get jqContainer
			var jqContainer = jq(moduleGroup.jqContainers[selector]);
			
			// Find group names
			for (var key in moduleGroup.groupNames) {
				jqContainer.find("span:contains('g"+key+".mGroup')").each(function() {
					// Replace text
					var old_html = jq(this).html();
					var new_html = old_html.replace("g"+key+".mGroup", moduleGroup.groupNames[key]);
					jq(this).html(new_html);
				});
			}
		}
	}
}