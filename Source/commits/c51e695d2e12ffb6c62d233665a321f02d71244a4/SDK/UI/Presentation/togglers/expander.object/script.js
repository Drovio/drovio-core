/* 
 * Redback JavaScript Document
 *
 * Title: Expander
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.0
 * DateCreated: 12/06/2013
 * DateRevised: --
 *
 */
 
// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready", function() {
	
	jq(document).on("click", ".expander > .switch", function(ev){
		var jqswitch = jq(this);
		var jqexpander = jqswitch.closest(".expander");
		
		jqexpander.toggleClass("expanded");
		
		if (jqexpander.hasClass("expanded"))
			jqswitch.text("Less")
		else
			jqswitch.text("More");	
	});
});