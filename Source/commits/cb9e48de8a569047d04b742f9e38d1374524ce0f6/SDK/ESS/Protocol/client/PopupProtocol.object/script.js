// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

// Initialize Popup Protocol
jq(document).one("ready", function() {
	// Initialize Popup Protocol Event Listeners
	jq(document).on("content.modified", function(ev) {
		PopupProtocol.init();
	});
});

// Create Asychronous Communication Protocol Object
PopupProtocol = {
	init : function() {
		// Initialize form submit
		jq(document).off('click action', "[data-pp]");
		jq(document).on('click action', "[data-pp]", function(ev, preventAction) {
			// Stops the Default Action (if any)
			ev.preventDefault();

			// Prevent Module Action
			if (preventAction)
				return;
			
			// Submit form
			PopupProtocol.loadPopup(ev, jq(this));
		});
	},
	loadPopup : function(ev, jqThis) {
		// Call Popup
		ModuleProtocol.triggerAction(ev, jqThis, "GET", "pp", "");
	}
}