// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	dialogFrame.init();
});

// Create Window Frame Prototype Object
dialogFrame = {
	init : function() {
		// Handles preventDefault action on every weblink with href="" or href="#" or has handler onclick
		jq(document).on("click", ".dialogFrame #dlgCancel", function(ev) {
			// Dispose Popup
			jq(this).trigger("dispose");
		});
	}
}