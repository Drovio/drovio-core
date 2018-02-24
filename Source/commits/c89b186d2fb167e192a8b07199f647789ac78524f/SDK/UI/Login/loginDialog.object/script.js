jq = jQuery.noConflict();
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
	
	jq(document).on("mouseenter", ".loginDialog .ricnt label", function() {;
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
});