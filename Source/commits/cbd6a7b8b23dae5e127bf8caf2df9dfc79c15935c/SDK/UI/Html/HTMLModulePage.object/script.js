// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready", function() {
	HTMLModulePage.init();
});

HTMLModulePage = {
	init : function() {
		// Clear toolbar listener
		jq(document).on("pageTitle.update", function(ev, title) {
			HTMLModulePage.updateTitle(title);
		});
	},
	updateTitle : function(title) {
		jq("title").text(title);
	}
};