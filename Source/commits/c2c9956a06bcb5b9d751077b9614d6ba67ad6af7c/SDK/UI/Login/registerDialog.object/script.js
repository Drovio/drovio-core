var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Close popup
	jq(document).on("click", ".registerDialog .header .close", function() {
		jq(this).trigger("dispose");
	});
});