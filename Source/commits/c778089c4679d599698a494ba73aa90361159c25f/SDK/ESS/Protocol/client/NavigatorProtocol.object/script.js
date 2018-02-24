// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {

	jq(document).on("content.modified", function() {
		jq('[data-static-nav].selected').trigger("click", true);
	});
	
	jq(document).on('click', '[data-static-nav]', function(ev) {
		// Get item
		var navItem = jq(this);

		//var selected = navItem.hasClass('selected');
		var selected = navItem.hasClass('selected');

		// Get the target Group
		var thisTargetGroup = navItem.data('staticNav').targetgroup;
		var thisTargetContainer = navItem.data('staticNav').targetcontainer;
		var thisNavGroup = navItem.data('staticNav').navgroup;
		
		// Clear all selected from navigation with the same targetgroup
		jq("[data-static-nav]").each(function(){
			if (typeof(jq(this).data('staticNav').navgroup) != "undefined" && jq(this).data('staticNav').navgroup == thisNavGroup)
				//jq(this).removeClass('selected');
				jq(this).removeClass('selected');
		}); 
		
		// Set this item as selected
		if (!(selected && navItem.data("staticNav").display == "toggle"))
			//navItem.addClass('selected');
			navItem.addClass('selected');
			
		
		if (navItem.data("staticNav").display == "none" || navItem.data("staticNav").display == "toggle") {
			// Clear All content from target container from the same group if "clearAll"
			jq("[data-targetgroupid='"+thisTargetGroup+"']", "#"+thisTargetContainer).not(".noDisplay").addClass("noDisplay");
		}
		else if (navItem.data("staticNav").display == "all") {
			// Display All content from target container from the same group if "displayAll"
			jq("[data-targetgroupid ='"+thisTargetGroup+"']", "#"+thisTargetContainer).removeClass("noDisplay");
		} 
	
	
		// Set Display to dataRef
		var thisTarget = navItem.data('staticNav').ref;
		if (!(selected && navItem.data("staticNav").display == "toggle"))
			jq("#"+thisTarget, "#"+thisTargetContainer).removeClass("noDisplay");
				
	});
});