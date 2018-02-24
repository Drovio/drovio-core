var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Initialize Form Prototype
	FormPrototype.init();
});

// Create Form Prototype object
FormPrototype = {
	init: function() {
	},
	getFormID: function(form) {
		return jq(form).attr("id");
	}
}