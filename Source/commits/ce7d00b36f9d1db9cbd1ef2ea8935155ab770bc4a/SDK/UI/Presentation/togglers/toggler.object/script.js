// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready", function() {
	
	// Set toggler click action
	jq(document).on('click', '.toggler .togHeader', function(ev) {
		if (jq(this).closest(".toggler").hasClass('open'))
			jq(this).trigger("close.toggler");
		else
			jq(this).trigger("open.toggler");
	});
	
	// Set toggler handlers
	jq(document).on("content.modified", function(ev) {
		// Open toggler
		jq(".toggler").off("open.toggler");
		jq(".toggler").on("open.toggler", function(ev) {
			jq(this).addClass("open");
			ev.stopPropagation();
		});
		
		// Close toggler
		jq(".toggler").off("close.toggler");
		jq(".toggler").on("close.toggler", function(ev) {
			jq(this).removeClass("open");
			ev.stopPropagation();
		});
	});
	
	jq(document).on('setHead.toggler', '.toggler', function(ev, content) {
		ev.stopPropagation();
		if (!content)
			return;
			
		jq(".headerContent", this).empty().append(content);
	});
	
	jq(document).on('appendToBody.toggler', '.toggler', function(ev, content) {
		ev.stopPropagation();
		if (!content)
			return;
		
		jq(".togBody", this).append(content);
	});
});