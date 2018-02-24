jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Init interval
	project.init();
});

project = {
	projects : new Array(),
	init : function() {
		// Get all developer projects and their information
		var serverUrl = "/ajax/dev/projects/getDevProjects.php";
		JSONServerReport.request(serverUrl, "GET", null, null, function(result) {
			// Get projects
			project.projects = result.projects.context;
		}, null, null, false, null);
	},
	getResourcesFolder : function(projectID) {
		if (jq.type(this.projects[projectID]) != "undefined")
			return this.projects[projectID].dev_resources;
	}
}