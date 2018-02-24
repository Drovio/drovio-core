var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Remember me options
	jq(document).on("click", ".loginDialog .ricnt label", function() {
		// Set selected
		jq(".loginDialog .rocnt").removeClass("selected");
		jq(this).closest(".rocnt").addClass("selected");
		var id = jq(this).closest(".rocnt").attr("id");
		
		// Set notes selected
		jq(".rnotes .nt").removeClass("selected");
		jq(".nt."+id).addClass("selected");
	});
	
	jq(document).on("mouseenter", ".loginDialog .ricnt label", function() {
		// Get id
		var id = jq(this).closest(".rocnt").attr("id");
		
		// Set notes selected
		jq(".rnotes .nt").removeClass("selected");
		jq(".nt."+id).addClass("selected");
	});
	
	jq(document).on("mouseleave", ".loginDialog .ricnt label", function() {
		// Reset selected
		var rcnts = jq(".loginDialog .rocnt.selected");
		var id = rcnts.attr("id");
		
		// Set notes selected
		jq(".rnotes .nt").removeClass("selected");
		jq(".nt."+id).addClass("selected");
	});
	
	// Close popup
	jq(document).on("click", ".loginDialog .header .close", function() {
		jq(this).trigger("dispose");
	});
	
	
	
	
	
	// MIXPANEL TRACKING
	// Social login
	jq(document).on("click", ".loginDialog .btn_social", function() {
		// Track login dialog open
		mixpanel.track("login_social");
	});
	
	// Register dialog listener
	jq(document).on("click", ".loginDialog h4.register a", function() {
		// Track login dialog open
		mixpanel.track("login_dialog_to_register");
	});
	
	// Successfull login
	jq(document).on("submit", ".loginDialog form", function() {
		// Track login dialog open
		mixpanel.track("login_form_submit");
	});
});