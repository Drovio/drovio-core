var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	ribbon.init();
});

ribbon = {
	currentNavItem : null,
	init : function() {
		// Navigation Item Handler
		jq(document).on('click', ".tlbNavItem[data-rbn-nav]", function(ev) {
			ribbon.setActiveItem(jq(this));
		});
		
		// Remove titles
		jq(document).on("content.modified", function() {
			// Set collection title as data and remove from attribute
			jq(".ribbon .collection").each(function(){
				jq(this).data("title", jq(this).data("title")).removeAttr("data-title");
			});
			
			// Check if ribbon is cloned and re-clone
			if (jq(".ribbon").length > 1) {
				// Get initial ribbon
				var jqToolbarRibbon = jq(".uiTlbRibbon .ribbon");
				
				// Get cloned ribbon
				var jqClonedRibbon = jq(".ribbon").not(".uiTlbRibbon .ribbon");
				
				// Replace clone with original
				jqClonedRibbon.replaceWith(jqToolbarRibbon.clone());
			}
		});
		
		// Trigger content.modified to support title update on demand
		jq(document).trigger("content.modified");
	},
	show : function() {
		// Show ribbon
		jq('.uiTlbRibbon').addClass('inline').animate({height: 'show'}, 'fast');
	},
	hide : function(instant) {
		// Remove inline from ribbon
		if (instant) {
			jq('.uiMainToolbar > .uiTlbRibbon').removeClass('inline').css('display', 'none');
		}
		else
			jq('.uiMainToolbar > .uiTlbRibbon').animate({height: 'hide'}, 'fast', function() {
				jq('.uiMainToolbar > .uiTlbRibbon').removeClass('inline');
			});
		
		// Unselect all navigation items from ribbon
		jq('.uiMainToolbar > .uiTlbRibbon .tlbNavItem').removeClass('selected');
	},
	getCollection : function(id) {
		return jq("#"+id, ".uiMainToolbar > .uiTlbRibbon .colGroup");
	},
	showCollection : function(id) {
		// Get Collection
		var collection = ribbon.getCollection(id).removeClass('noDisplay');
		
		// Check if collection exists
		if (collection.length == 0)
			return false;
		
		// Clear collections and get full width
		jq('.uiMainToolbar > .uiTlbRibbon').addClass('inline');
		jq('.collection', '.colGroup').addClass('noDisplay');
		
		// Show Collection
		collection.removeClass('noDisplay');
		
		// Set Collection width to all contents
		var total_width = 0;
		collection.children().each(function(){
			total_width += jq(this).outerWidth(true);
		});
		collection.css("min-width", total_width);
		
		// Show Collection contents (ajax contents)
		collection.trigger("load");
		
		// Set collection title
		var collectionTitle = collection.data("title");
		jq(".ribbon .statusbar").html(collectionTitle);
		if (collectionTitle == "" || collectionTitle == undefined)
			jq(".ribbon .statusbar").empty();
		
		return true;
	},
	showPopup : function(jqNavItem) {
		jqNavItem.popup.type = jqNavItem.data("rbnNav").type;
		jqNavItem.popup.position = 'bottom|center';
		jqNavItem.popup(jq('.ribbon', '.uiMainToolbar > .uiTlbRibbon').clone(true).find('.collection').css("min-width","0px").end());
	},
	setActiveItem : function(jqItem) {
		ribbon.currentNavItem = jqItem;
		var selected = jqItem.hasClass('selected');
		
		jq(".tlbNavItem[data-rbn-nav]").removeClass("selected");
		
		// Get Parameters
		var ref_id = jqItem.data("rbnNav").ref;
		if (ref_id == "" || ref_id == null)
			return;
			
		var ribbon_type = jqItem.data("rbnNav").ribbon;
		
		var exists = ribbon.showCollection(ref_id)
		if (!exists)
			return false;

		switch (ribbon_type){
			case "float":
				ribbon.showPopup(jqItem);
				ribbon.hide(true);
				break;
			case "inline":
				if (selected) {
					ribbon.hide();
				}
				else {
					ribbon.show();
					jqItem.addClass('selected');
				}
				break;
		}
	}
};