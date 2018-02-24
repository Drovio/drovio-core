var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Close popup
	jq(document).on("click", ".registerDialog .header .close", function() {
		jq(this).trigger("dispose");
	});
	
	
	
	// MIXPANEL TRACKING
	// Social signup
	jq(document).on("click", ".registerDialog .btn_social", function() {
		// Track login dialog open
		mixpanel.track("signup_social");
	});
	
	// Successfull signup
	jq(document).on("submit", ".registerDialog form", function() {
		// Track login dialog open
		mixpanel.track("signup_form_submit");
	});
});