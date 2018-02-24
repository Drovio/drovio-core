// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// Let the document load
jq(document).one("ready.extra", function() {
	// Init moduleGroup name parser
	dvbDomain.init();
});

dvbDomain = {
	queryNames : new Array(),
	jqContainers : new Array(),
	init : function() {
		// Load group names
		ascop.asyncRequest("/ajax/resources/sdk/sql/queryNames.php", "GET", null, "json", this, function (response){
				// Set to local variable
				dvbDomain.queryNames = response;
			}
		);
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			dvbDomain.filter();
		});
	},
	addContainer : function(selector) {
		// Add the given container selector to the jqContainers
		if (this.jqContainers.indexOf(selector) < 0)
			this.jqContainers.push(selector);
	},
	filter : function() {
		// Search for each container
		for (var selector in dvbDomain.jqContainers) {
			// Get jqContainer
			var jqContainer = jq(dvbDomain.jqContainers[selector]);
			
			// Find group names
			for (var key in dvbDomain.queryNames) {
				jqContainer.find("span:contains('"+key+".sql')").each(function() {
					// Replace text
					var old_html = jq(this).html();
					var new_html = old_html.replace(key, dvbDomain.queryNames[key].replace(/ /g, "_"));
					jq(this).html(new_html);
				});
			}
		}
	}
}