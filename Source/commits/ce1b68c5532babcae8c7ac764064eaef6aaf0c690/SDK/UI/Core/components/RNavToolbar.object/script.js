// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready", function() {
	toolbar.init();
});

toolbar = {
	init : function() {
		// Actions on content.modified
		jq(document).on("content.modified", function() {
			// Load Toolbar Items
			toolbar.loadItems();
			/*
			// Refresh Toolbar css to fix the chrome float bug
			var cssFix = jq(".uiToolbarNav").data("cssFixed");
			if (cssFix == undefined || cssFix < 5) {
				jq(".tlbNav").each(function() {
					jqThis = jq(this);
					
					// Get class before
					var class_before = jqThis.attr("class");
					
					// Remove class
					jqThis.removeAttr("class");
					
					// Re-add class in timeout
					setTimeout(resetClass, 1, jqThis, class_before);
					function resetClass(jqItem, newClass) {
						jqItem.addClass(newClass);
					}
				});
				var count = jq(".uiToolbarNav").data("cssFixed");
				count = (count == undefined ? 1 : count + 1);
				jq(".uiToolbarNav").data("cssFixed", count)
			}*/
		});
		
		// Clear toolbar listener
		jq(document).on("clear.toolbar", function() {
			toolbar.clear();
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