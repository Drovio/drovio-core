var jq=jQuery.noConflict();
jq(document).one("ready", function() {
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

// Create tabControl object
tabControl = tabber = {
	prototypes : new Object(),
	init : function() {
		// Fetch tab prototypes
		jq(document).on("content.modified", function(ev) {
			// Fetch tab prototype for each tabControl (given the tabber id)
			jq('.tabControl.editable').each(function() {
				// Get tabber
				jqTabber = jq(this);
				var tabber_id = jqTabber.attr("id");
				if (tabber.prototypes[tabber_id] != null)
					return true;
				
				// Init to prevent future executions
				tabber.prototypes[tabber_id] = new Object();
				
				// Fetch tab prototype callback
				fetchTabPrototype = function(data) {
					// Get payload and html content
					var payload = JSONServerReport.getReportPayload(data[0]);
					var tabContainer = HTMLServerReport.getPayloadContent(payload);
					
					// Add tabber prototype to list
					var tabberID = jq(tabContainer).attr("id");
					tabber.prototypes[tabberID] = tabContainer;
				}

				// Set async parameters
				var tabber_id = jqTabber.attr("id");
				var editable = jqTabber.hasClass("editable");
				var tabParameters = "tabber_id="+tabber_id+"&editable="+editable;
				JSONServerReport.request("/ajax/resources/sdk/tabControl/tabber.php", "GET", tabParameters, jqTabber, fetchTabPrototype, null, null, false, null);
			});
			
			// Reset tabControl state (selected tab)
			jq(".tabControl").not(".editable").not(function(){
				return jq(this).data("stateInitialized") === true;
			}).each(function(){
				// Get tabber id to load the state
				var id = jq(this).attr("id");
				
				// Reset state
				var state = new UIStateObject(id);
				var tabs = state.getState(true);
				if (typeof(tabs) == "undefined" || tabs == null)
					return;
				
				var selectedTab = jq(this).children(".tabs").find("ul > li").eq(tabs.selected);
				NavigatorProtocol.triggerClick(selectedTab);
				jq(this).data("stateInitialized", true);
			});
		});
		
		// Add a new tab
		jq(document).on("add.tab", '.tabControl', function(ev, editable, tab_id, tab_title, tab_context) {
			// Add a new tab
			tabber.addTab(jq(this), tab_id, tab_title, tab_context);
		});
		
		// Close tab
		jq(document).on("keydown", '.tabControl.editable', function(ev) {
			// Close tab on Ctrl/Meta + D
			if ((ev.ctrlKey || ev.metaKey) && ev.which == 68) {
				// Prevent Default Browser Action
				ev.preventDefault();
				
				// Get open tab and close it
				var jqTab = jq(this).find("li.tabMenuItem.editable.selected");
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
		
		// Tab changed
		jq(document).on('click', '.tabs .tabMenuItem', function(ev) {
			// Trigger tab changed
			jq(this).closest(".tabControl").trigger("tab_changed");
		});
		
		// Window unload listener
		jq(window).on("unload", function() {
			// Save state of each non-editable tab control
			jq(".tabControl").not(".editable").each(function() {
				jq(this).trigger("saveState.tabControl");
			});
		});
		
		// Save state trigger (only for internal use)
		jq(document).on("saveState.tabControl", ".tabControl:not(.editable)", function(ev){
			ev.stopPropagation();
			tabControl.saveState(jq(this));
		});
		
		// Set Modified Tab Content
		// Content inside tab was modified, trigger tab to be modified
		jq(document).on("text.modified tab.modified", ".tabPageContainer.editable", function(ev) {
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
		
		// Interval to resize tabControls if necessary
		setInterval(function() {tabControl.resizeAll()}, 5000);
	},
	resize : function(jqTabber) {
		// Resize tabs
		if (jqTabber.hasClass("full")) {
			var totalTabs = jqTabber.find('.tabs').first().find('.tabMenuItem').length;
			jqTabber.find('.tabMenuItem').css('width', 100/totalTabs+"%");
		} else {
			// Get objects
			var jqTabs = jqTabber.find('.tabs').first();
			
			// Resize tabs
			var initialWidth = 200;
			var tabsWidth = jqTabs.width();
			var tabItemMinWidth = 50;
			var tabItemsCount = jqTabs.find("li").length;
			var tabItemWidth = jqTabs.find("li").first().width() + 1;
			if ((tabItemsCount * initialWidth) > tabsWidth) {
				// Set to new width
				var newWidth = Math.floor(tabsWidth / tabItemsCount);
				if (tabItemWidth != newWidth && newWidth != 0)
					jqTabs.find("li").animate({width: newWidth});
			} else if (tabItemWidth != initialWidth)
				jqTabs.find("li").animate({width: initialWidth});
		}
		
	},
	resizeAll : function() {
		var jqTabbers = jq(".tabControl");
		jqTabbers.each(function() {
			tabber.resize(jq(this));
		});
	},
	getSelectedTab : function(jqTabber) {
		return jqTabber.find(".tabs .tabMenuItem.selected");
	},
	getSelectedPage : function(jqTabber) {
		return jqTabber.find(".pages .tabPageContainer:not(.noDisplay)");
	},
	closeTab : function(jqTab) {
		// Get controls
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
		var menuItem = jq(prototype).find('.tabMenuItem').first().contents();
		var tabPage = jq(prototype).find('.tabPage').first().contents();
		
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
					NavigatorProtocol.triggerClick(jq(this));
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
		NavigatorProtocol.triggerClick(menuItem);
		jq(document).trigger("content.modified");
	},
	saveState: function(jqTabber) {
		// Reset state initialized status
		jqTabber.data("stateInitialized", false);
		
		// Save state only for tabs with id
		var id = jqTabber.attr("id");
		if (jq.type(id) == "undefined" || id == null)
			return;
		
		// Save state of selected tabs
		var tabs = new Object();
		tabs.selected = jqTabber.children(".tabs").find("ul > .selected").index();
		var state = new UIStateObject(id);
		state.setState(tabs, true);
	}
}