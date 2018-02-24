// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready", function() {
	
	jq(document).on('content.modified', function(ev) {
		jq(".accordion").each(function(){
			jq(this).find(".toggler.open").slice(1).trigger("close")
		});
	});
	
	jq(document).on('open', '.accordion > .toggler', function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();

		// Get closest accordion
		jq(this).closest(".accordion").find(".toggler").not(this).trigger("close");
	});
});