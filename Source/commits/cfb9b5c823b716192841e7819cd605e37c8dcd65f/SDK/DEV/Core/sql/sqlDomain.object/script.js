// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// Let the document load
jq(document).one("ready.extra", function() {
	// Init moduleGroup name parser
	sqlDomain.init();
});

sqlDomain = {
	queryNames : new Array(),
	jqContainers : new Array(),
	init : function() {
		// Load only in developer's domain
		if (url.getSubdomain() != "developer")
			return;
		
		// Initial load of sql names
		this.loadNames();
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			sqlDomain.filter();
		});
	},
	loadNames : function() {
		// Load group names
		JSONServerReport.request("/ajax/resources/sdk/sql/queryNames.php", "GET", null, this, function(response) {
			// Parse query names
			sqlDomain.queryNames = JSONServerReport.getReportPayload(response.qNames);
			
			// Refresh after 5min
			setTimeout(function() {
				sqlDomain.loadNames();
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
		for (var selector in this.jqContainers) {
			// Get jqContainer
			var jqContainer = jq(this.jqContainers[selector]);

			// Find group names
			for (var key in this.queryNames) {
				jqContainer.find("span:contains('"+key+".sql')").each(function() {
					// Replace text
					var old_html = jq(this).html();
					var new_html = old_html.replace(key, sqlDomain.queryNames[key].replace(/ /g, "_"));
					jq(this).html(new_html);
				});
			}
		}
	}
}