jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Init interval
	project.init();
});

project = {
	projects : new Array(),
	init : function() {
		// Get all developer projects and their information
		ascop.request("/ajax/dev/projects/getDevProjects.php","GET", null, null, function(result) {
			// Get projects
			projects = result;
		}, ajaxOptions);
	},
	getResourcesFolder : function(projectID) {
		if (jq.type(projects[projectID]) != "undefined")
			return projects[projectID].dev_resources;
	}
}