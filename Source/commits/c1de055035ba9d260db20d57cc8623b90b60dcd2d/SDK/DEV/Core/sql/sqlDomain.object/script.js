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
		if (url.getSubdomain() != "developer")
			return;
		// Load group names
		ascop.asyncRequest("/ajax/resources/sdk/sql/queryNames.php", "GET", null, "json", this, function (response){
				// Set to local variable
				sqlDomain.queryNames = response;
			}, null, true, true
		);
		
		// Set content.modified event listener
		jq(document).on("content.modified", function() {
			// Filter text
			sqlDomain.filter();
		});
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