var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	toolbarMenu.init();
});

toolbarMenu = {
	init: function() {
		// Create all listeners
		jq(document).on("click", ".tlbMenuHeader", function() {
			// Get menu container and create popup
			var menu = jq(this).find(".toolbarItemMenuContainer");
			jq(this).popup.type = "obedient toggle";
			jq(this).popup.position = 'bottom|center';
			jq(this).popup(menu.clone(true));
		});
	}
}