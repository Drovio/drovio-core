var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	// Update page title
	jq(document).on("title.update", function(ev, title) {
		jq("title").text(title);
	});
});