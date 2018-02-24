var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Reload page event
	jq(document).on("page.reload", function(ev) {
		location.reload(true);
	});
	// Redirect page event
	jq(document).on("page.redirect", function(ev, locationRef) {
		url.redirect(locationRef);
	});
});