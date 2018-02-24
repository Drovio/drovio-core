// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	jq(document).on("click", ".uiVCSControl .footer .closeBtn", function(ev) {
		// Dispose Popup
		jq(this).trigger("dispose");
		
		// Remove control
		jq(this).closest(".uiVCSControl").remove();
	});
});