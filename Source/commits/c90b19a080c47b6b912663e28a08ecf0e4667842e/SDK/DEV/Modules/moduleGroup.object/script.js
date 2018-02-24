// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// Let the document load
jq(document).one("ready.extra", function() {
	// Init moduleGroup name parser
	moduleGroup.init();
});

moduleGroup = {
	groupNames : new Array(),
	jqContainers : new Array(),
	init : function() {
		// Load only in developer's domain
		if (url.getSubdomain() != "developer" && url.getSubdomain() != "admin")
			return;
		
		// Load and refresh names every 3 minute
		moduleGroup.loadNames();
		setInterval(function() {
			moduleGroup.loadNames();
		}, 3 * 60 * 1000);
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			moduleGroup.filter();
		});
	},
	loadNames : function() {
		// Load group names
		ascop.asyncRequest("/ajax/resources/sdk/modules/moduleGroups.php", "GET", null, "json", this, function (response){
				// Set to local variable
				moduleGroup.groupNames = response;
			}, null, true, true
		);
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