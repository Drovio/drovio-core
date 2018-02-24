// Create Weblink builder
var jq = jQuery.noConflict();
weblink = {
	get: function(href, target, content, linkClass) {
		// Create item object
		var wlink = DOM.create("a", content, "", linkClass);
		
		// Add href and target
		wlink.attr("href", href);
		wlink.attr("target", target);
		
		// Return weblink
		return wlink;
	}
}