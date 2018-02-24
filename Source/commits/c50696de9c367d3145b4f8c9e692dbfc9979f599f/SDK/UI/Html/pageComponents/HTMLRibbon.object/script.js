/* 
 * Redback JavaScript Document
 *
 * Title: RedBack Ribbon Manager
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
	ribbon.init();
});

ribbon = {
	currentNavItem : null,
	init : function() {
		// Navigation Item Handler
		jq(document).on('click', ".tlbNavItem[data-rbn-nav]", function(ev) {
			ribbon.setActiveItem(jq(this));
		});
		
		// Toggle ribbon (inline - float)
		jq(document).on('click', ".tools > .pin", function(ev) {
			ribbon.toggle(jq(this));
		});
		
		// Actions on content.modified
		jq(document).on("content.modified", function() {
			// Reset Context of the popup
			if (ribbon.currentNavItem != null && ribbon.currentNavItem.popup("exists"))
			{
				//ribbon.currentNavItem.popup("set", jq('.ribbon', '.uiMainToolbar > .uiTlbRibbon').clone(true).find('.collection').css("min-width","0px").end());
			}
		});
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
	showCollection : function(id, pinnable) {
		// Get Collection
		var collection = ribbon.getCollection(id).removeClass('noDisplay');
		
		// Check if collection exists
		if (collection.length == 0)
			return false;
		
		// Clear collections and get full width
		jq('.uiMainToolbar > .uiTlbRibbon').addClass('inline');
		jq('.collection','.colGroup').addClass('noDisplay');
		
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
		
		// Set Pinnable Collection
		if (pinnable)
			jq('.tools > .pin').removeClass("noDisplay");
		else
			jq('.tools > .pin').addClass("noDisplay");
		
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
		var pinnable = jqItem.data("rbnNav").pinnable;
		
		var exists = ribbon.showCollection(ref_id, pinnable == true)
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
	},
	toggle : function(jqPin) {
		if (jqPin.closest('.uiMainToolbar > .uiTlbRibbon').length > 0) {
			var jq_navItem_selected = jq('.tlbNavItem.selected');
			this.hide();
			jq_navItem_selected.data("rbnNav").ribbon = "float";
			jq_navItem_selected.trigger("click");
		} else {
			ribbon.currentNavItem.trigger("click");
			ribbon.currentNavItem.data("rbnNav").ribbon = "inline";
			ribbon.currentNavItem.trigger("click");
		}
	}
};