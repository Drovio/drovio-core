var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	RNavToolbar.init();
});

RNavToolbar = toolbar = {
	init : function() {
		// Actions on content.modified
		jq(document).on("content.modified", function() {
			// Load Toolbar Items
			RNavToolbar.loadItems();
		});
		
		// Clear toolbar listener
		jq(document).on("clear.toolbar", function() {
			RNavToolbar.clear();
		});
		
		jq(document).on("click", ".tlbNavItem.drovio.platform .navMenuHeader", function(ev) {
			// Stop normal behavior
			ev.preventDefault();
			ev.stopPropagation();
			
			// Get team selector contents
			var teamSelector = jq("#platformNav").html();
			
			// Show popup
			var jqPPParent = jq(document);
			jqPPParent.popup.withFade = true; 
			jqPPParent.popup.withBackground = true; 
			jqPPParent.popup(jq(teamSelector));
			jqPPParent.popup.position = "center";
		});
	},
	loadItems : function() {
		// Load Toolbar Controllers
		jq(".nav_item").each(function(ev) {
			var jq_this = jq(this).detach();
			
			// Insert each item in ribbon in correct position
			jq_this.find('.tlbNavItem').each(function() {
				var jqItem = jq(this);
				
				// Escape if there is already an item with the same ref
				var exists = false;
				jq('.tlbNavItem', jq('.tlbNav.leftNav')).each(function() {
					if (jqItem.attr('id') == jq(this).attr('id') || (jqItem.data('rbNav') && jqItem.data('rbnNav').ref == jq(this).data('rbnNav').ref))
						exists = true;
				});
				if (exists)
					return true;

				// Insert item in page navigation
				jqItem.appendTo(jq('#pageNav'));
			});
			
			// Insert each collection into collection group
			jq_this.find('.collection').each(function() {
				var cr_col_id = jq(this).attr('id');
				
				// If there is already a collection with the same id, remove the old
				jq('#'+cr_col_id+'.collection').remove();
					
				// Insert Collection
				jq(this).appendTo(jq('.colGroup', '.uiTlbRibbon'));
			});
		});
		
		// Commit each instruction
		jq('.instruct').each(function() {
			// Clear ribbon
			if (jq(this).data('type') == "ribbon.clear") {
				jq(document).trigger('ribbon.clear');
				jq(this).remove();
			}
		});
	}, 
	clear : function() {
		// Clear page navigation items
		jq("#pageNav").empty();
	}
};