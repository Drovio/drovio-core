/* 
 * Redback JavaScript Document
 *
 * Title: Accordion Manager
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.0
 * DateCreated: 14/11/2012
 * DateRevised: --
 *
 */
 
// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();


jq(document).one("ready", function() {
	
	jq(document).on('click', '.sliceHeader', function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();
		// Show or load the content
		
		if (jq(this).hasClass('open'))
			jq(this).trigger("collapse");
		else {
			if (jq(this).find('.reportContainer').first().contents().length == 0)
				jq(this).trigger("load");
			else
				jq(this).trigger("show");
		}
	});
});