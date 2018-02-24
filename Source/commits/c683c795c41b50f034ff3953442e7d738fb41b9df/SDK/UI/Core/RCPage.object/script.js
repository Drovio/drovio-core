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
	
	// Get content modified and trigger all static actions caused by RCPage
	jq(document).on("content.modified", function() {
	
		// Trigger all actionContainers
		jq(".actionContainer").each(function() {
			// Trigger action
			var action = jq(this).data("action");
			if (action == undefined)
				return true;
			
			// Trigger action to document
			jq(document).trigger(action.type, action.value);
			
			// Remove element
			jq(this).remove();
		});
	});
	
	// Noscript Handler
	if (url.getVar('_rb_noscript') || cookies.get("noscript") == "1")
	{
		var hrf = window.location.href;
		var new_hrf = url.removeVar(hrf, "_rb_noscript");
		cookies.set("noscript", "0", -1000, "/");
		if (hrf != new_hrf)
			window.location = new_hrf;
		else
			location.reload();
	}
});