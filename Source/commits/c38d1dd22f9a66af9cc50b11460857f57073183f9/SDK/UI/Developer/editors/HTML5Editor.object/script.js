var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	HTML5Editor.init();
});

HTML5Editor = {
	init: function() {
		console.log("HTML 5 Editor initialized!");
	}
}