// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// let the document load
jq(document).one("ready.extra", function() {
	// Init tabber
	tabber.init();
	
	// Set listeners
	jq(document).on("content.modified", function() {
		// Resize full tabbers
		jqFullTabbers = jq(".tabControl.full").each(function() {
			tabber.resize(jq(this));
		});
	});
	
	// Trigger content.modified
	jq(document).trigger("content.modified");
});



tabber = {
	prototypes : new Object(),
	init : function() {
		// Fetch tab prototypes
		jq(document).on("content.modified", function(ev) {
			// Fetch tab prototype for each tabControl (given the tabber id)
			jq('.tabControl').each(function() {
				// Get tabber
				jqTabber = jq(this);
				var tabber_id = jqTabber.attr("id");
				if (tabber.prototypes[tabber_id] != null)
					return true;
				
				// Init to prevent future executions
				tabber.prototypes[tabber_id] = new Object();
				
				// Fetch tab prototype callback
				fetchTabPrototype = function(data) {
					var tabContainer = data.body[0].context;
					var tabberID = jq(tabContainer).attr("id");
					tabber.prototypes[tabberID] = tabContainer;
				}
				
				
				// Set async parameters
				var tabber_id = jqTabber.attr("id");
				var editable = jqTabber.hasClass("editable");
				var tabParameters = "tabber_id="+tabber_id+"&editable="+editable;
				ascop.asyncRequest("/ajax/resources/sdk/tabControl/tabber.php", "GET", tabParameters, "json", jqTabber, fetchTabPrototype, null, true, true);
			});
		});
		
		// Add a new tab
		jq(document).on("add.tab", '.tabControl', function(ev, editable, tab_id, tab_title, tab_context) {
			// Add a new tab
			tabber.addTab(jq(this), tab_id, tab_title, tab_context);
		});
		
		// Close tab
		jq(document).on("keydown", '.tabControl.editable', function(ev) {
			if ((ev.ctrlKey || ev.metaKey) && ev.which == 68)
			{
				// Prevent Default Browser Action
				ev.preventDefault();
				
				// Get tab and close
				var jqTab = jq(this).closest("li.tabMenuItem.editable");
				tabber.closeTab(jqTab);
			}
		});
		
		// Close button action
		jq(document).on('click', '.tabs .tabMenuItem.editable > .closeButton', function(ev) {
			// Stop bubbling
			ev.stopPropagation();
			
			// Get tab and close
			var jqTab = jq(this).closest("li.tabMenuItem.editable");
			tabber.closeTab(jqTab);
		});
		
		// Save non-editable tabControl state on unload
		jq(window).on("unload", function(){
			jq(".tabControl").not(".editable").trigger("saveState.tabControl");
		});
		jq(document).on("saveState.tabControl", ".tabControl:not(.editable)", function(ev){
			ev.stopPropagation();
			jq(this).data("stateInitialized", false);
			var id = jq(this).attr("id");
			if (jq.type(id) == "undefined" || id == null)
				return;
			
			var tabs = new Object();
			tabs.selected = jq(this).children(".tabs").find("ul > .selected").index();
			
			var state = new UIStateObject(id);
			state.setState(tabs, true);
		});
		jq(document).on("content.modified", function(){
		
			jq(".tabControl").not(".editable").not(function(){
				return jq(this).data("stateInitialized") === true;
			}).each(function(){
				var id = jq(this).attr("id");
				
				var state = new UIStateObject(id);
				var tabs = state.getState(true);
		
				if (typeof(tabs) == "undefined" || tabs == null)
					return;
				
				jq(this).children(".tabs").find("ul > li").eq(tabs.selected).trigger("click");
				
				jq(this).data("stateInitialized", true);
			});
		});
		
		// Set Modified Tab Content
		jq(document).on("tab.modified", ".tabPageContainer.editable", function(ev) {
			// Find reference ID
			var refID = jq(this).attr("id");
			
			// Find menu Item
			jq(this).closest(".tabControl").find(".tabMenuItem").each(function() {
				if (jq(this).data("staticNav") !== undefined && jq(this).data("staticNav").ref == refID) {
					// Set item edited
					jq(this).data("edited", true);
					
					// Find Text
					var itemText = jq(this).find(".tabMenuItemText");
					
					// Check if already exists
					itemText.find(".modifiedAnchor").remove();
					
					// Create Span
					var textModified = jq("<span />").addClass("modifiedAnchor").html("*");
					textModified.prependTo(itemText);
					
				}
			});
		});
		
		// Set Synced Tab Content
		jq(document).on("tab.synced", ".tabPageContainer.editable", function(ev) {
			// Find reference ID
			var refID = jq(this).attr("id");
			
			// Find menu Item
			jq(this).closest(".tabControl").find(".tabMenuItem").each(function() {
				if (jq(this).data("staticNav") !== undefined && jq(this).data("staticNav").ref == refID) {
					// Set item clear from editing
					jq(this).data("edited", false);
					
					// Find Text
					var itemText = jq(this).find(".tabMenuItemText");
					
					// Remove anchor
					itemText.find(".modifiedAnchor").remove();
					
				}
			});
		});
	},
	resize : function(jqTabber) {
		// Resize tabs
		if (jqTabber.hasClass("full")) {
			var totalTabs = jqTabber.find('.tabs').first().find('.tabMenuItem').length;
			jqTabber.find('.tabMenuItem').css('width', 100/totalTabs+"%");
		} else {
			// Get objects
			var jqTabs = jqTabber.find('.tabs').first();
			
			// Set tabs to original width
			jqTabs.find("li").css('width', '');
			
			// Resize tabs
			var tabsWidth = jqTabs.width();
			var tabItemMinWidth = 50;
			var tabItemsCount = jqTabs.find("li").length;
			var tabItemWidth = jqTabs.find("li").first().width();
			if ((tabItemsCount * tabItemWidth) > tabsWidth) {
				// Set width to zero to loose any scrolls
				//jqTabs.find("li").width(0);
				
				// Reset width tabs
				//setTimeout(function() {
					var newWidth = Math.floor(tabsWidth / tabItemsCount);
					jqTabs.find("li").width(newWidth);
				//}, 100);
			}
		}
		
	},
	resizeAll : function() {
		var jqTabbers = jq(".tabControl");
		jqTabbers.each(function() {
			tabber.resize(jq(this));
		});
	},
	closeTab : function(jqTab) {
		
		var jqTabber = jqTab.closest('.tabControl');
		var tabMenu = jqTab.closest('ul');
		
		// Check if the tab is edited
		if (jqTab.data("edited")) {
			var ans = confirm("All unsaved data will be lost in this tab. Close ?");
			if (!ans)
				return;
		}
	
		// Get Next Selected Item
		var nextItem;
		if (jqTab.hasClass('selected')) {
			// Get indices
			var index = tabMenu.children("li").index(jqTab);
			var total_tabs = tabMenu.children("li").length;
			
			// Get next item
			if (index < total_tabs - 1)
				nextItem = tabMenu.children('li').eq(index + 1);
			else if (index == total_tabs - 1 && total_tabs > 1)
				nextItem = tabMenu.children('li').eq(index - 1);
		} else {
			nextItem = tabMenu.children('li.selected');
		}
		
		// Remove page
		var ref_id = jqTab.data('staticNav').ref;
		jqTabber.find('.pages').children('#'+ref_id).remove();
		
		// Remove tab item
		jqTab.remove();
		
		// Trigger click to next item
		if (jq.type(nextItem) !== "undefined")
			nextItem.trigger('click');
		
		// Resize tabber
		tabber.resize(jqTabber);
	},
	addTab : function(jqTabber, tab_id, tab_title, tab_context) {
		// Form container and insert
		var tabberID = jqTabber.attr("id");
		var prototype = tabber.prototypes[tabberID].replace(/%tabID/g, tab_id);
		var menuItem = jq(prototype).find('.menuItem').contents();
		var tabPage = jq(prototype).find('.tabPage').contents();
		
		// Set header
		menuItem.find(".tabMenuItemText").text(tab_title);
		
		// Search for existing page with same id
		if (jqTabber.find('#'+tabPage.attr('id')+'.tabPageContainer').length > 0) {
		
			// Ask user to replace tab
			var replace = confirm("This tab is already loaded and any unsaved data will be lost. Would you like to replace contents ?");
			if (replace) {
				// Fill Tab Page and Replace Page Container
				tab_context.appendTo(tabPage);
				jqTabber.find('#'+tabPage.attr('id')+'.tabPageContainer').first().replaceWith(tabPage);
			}
			
			// Search for Menu Item and select the tab
			jqTabber.find('.tabs > ul').first().children().each(function() {
				if (jq(this).data('staticNav').ref == tabPage.attr('id')) {
					
					// If replace
					if (replace)
						jq(this).replaceWith(menuItem);
						
					// Focus Menu Item
					jq(this).trigger('click');
					return true;
				}
			});
		}
		else {
			// Append Menu Item
			menuItem.appendTo(jqTabber.find('.tabs').first().children('ul'));
			
			// Resize tabs
			tabber.resize(jqTabber);
			
			// Fill Tab Page
			tab_context.appendTo(tabPage);
			// Append Tab Page
			tabPage.appendTo(jqTabber.find('.pages').first());
		}
		
		// Set Menu Item Selected
		menuItem.trigger("click");
		jq(document).trigger("content.modified");
	}
}