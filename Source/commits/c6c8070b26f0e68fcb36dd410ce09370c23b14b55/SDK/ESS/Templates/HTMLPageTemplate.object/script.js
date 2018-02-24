var jq = jQuery.noConflict();
jq(document).one("ready", function() {
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