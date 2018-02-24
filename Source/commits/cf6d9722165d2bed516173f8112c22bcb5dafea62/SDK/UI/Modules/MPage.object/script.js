var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Set page title
	jq(document).on("pageTitle.update", function(ev, title) {
		jq("head title").html(title);
	});
});