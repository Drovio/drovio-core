// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// let the document load
jq(document).one("ready.extra", function() {
	
	jq(document).on("content.modified", function() {
		jq('.tabControl.full').each(function() {
			var jq_tabber = jq(this);
			
			// Resize tabs
			var totalTabs = jq_tabber.find('.tabs').find('[data-static-nav]').length;
			jq_tabber.find('.tabs').find('[data-static-nav]').css('width', 100/totalTabs+"%");
		});
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
	
	jq(document).on('click', '.tabs .tabMenuItem.editable > .closeButton', function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();
		
		var jq_tabControl = jq(this).closest('.tabControl');
		var jq_item = jq(this).closest(".tabMenuItem");
		var jq_menu = jq(this).closest('ul');
		
		// Check if the tab is edited
		if (jq_item.data("edited")) {
			var ans = confirm("All unsaved data will be lost in this tab. Close ?");
			if (!ans)
				return;
		}

		// Get Next Selected Item
		var next_item;
		if (jq_item.hasClass('selected')) {
		
			total_tabs = jq_menu.children("li").length;
			
			index = jq_menu.children("li").index(jq_item);
			
			if (index < total_tabs - 1)
				next_item = jq_menu.children('li').eq(index+1);
			else if (index == total_tabs - 1 && total_tabs > 1)
				next_item = jq_menu.children('li').eq(index-1);
		} else {
			next_item = jq_menu.children('li.selected');
		}
		
		// Remove page
		ref_id = jq_item.data('staticNav').ref;
		jq_tabControl.find('.pages').children('#'+ref_id).remove();
		
		// Remove tab
		jq_item.remove();
		
		// Trigger click to next item
		if (typeof(next_item) != "undefined")
			next_item.trigger('click');
		else
			jq_menu.parent().children('.pointer').attr('style', '');
	});
	
	// Close tab
	jq(document).on("keydown", '.tabControl.editable', function(ev) {
		if ((ev.ctrlKey || ev.metaKey) && ev.which == 68)
		{
			// Prevent Default Browser Action
			ev.preventDefault();
			
			// Get Closest tab page container
			var closeBtn = jq(this).find(".tabMenuItem.editable.selected").find(".closeButton").first();
			closeBtn.trigger("click");
		}
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
				textModified.appendTo(itemText);
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
	
	// Add a new tab
	jq(document).on("add.tab", '.tabControl', function(ev, editable, tab_id, tab_title, tab_context, tab_replace) {
		
		// Get Sender
		var jqsender = jq(this);
		jqsender.data("context", tab_context);
		var tabber_id = jqsender.attr("id");
		
		var f_pvar = "o_id=Presentation.tabControl&o_path=tabber&o_type=content";
		f_pvar += "&editable="+editable+"&holder_id="+tabber_id+"&tab_id="+tab_id+"&header="+tab_title;
		jq.ajax({
			url: "/ajax/resources/sdk/tabControl/tabber.php",
			data: f_pvar,
			type: "GET",
			context: jqsender,
			dataType: "html",
			success: function(data) {
				// Insert Menu Item in List
				var menuItem = jq(data).find('.menuItem').contents();
				var tabPage = jq(data).find('.tabPage').contents();

				// Search for existing page with same id
				if (jqsender.find('#'+tabPage.attr('id')+'.tabPageContainer').length > 0) {
					
					// If tab is going to be replaced
					if (tab_replace) {
						// Fill Tab Page
						jqsender.data("context").appendTo(tabPage);
						// Replace Page Container
						jqsender.find('#'+tabPage.attr('id')+'.tabPageContainer').first().replaceWith(tabPage);
						
						
					}
					
					// Search for Menu Item
					jqsender.find('.tabs > ul').first().children().each(function() {
						if (jq(this).data('staticNav').ref == tabPage.attr('id')) {
							
							// If replace
							if (tab_replace)
								jq(this).replaceWith(menuItem);
								
							// Focus Menu Item
							jq(this).trigger('click');
							return true;
						}
					});
				}
				else {
					// Append Menu Item
					menuItem.appendTo(jqsender.find('.tabs').first().children('ul'));
					
					// Re-arrange tabs
					var tabs = jqsender.find('.tabs').first();
					var tabsWidth = tabs.width() - 10;
					var tabItemMinWidth = 50;
					var tabItemsLength = tabs.find("li").length + 1;
					var tabItemWidth = tabs.find("li").first().width();
					if (tabItemsLength * tabItemWidth > tabsWidth) {
						// Resize tabs
						var newWidth = Math.floor(tabsWidth / tabItemsLength);
						tabs.find("li").width(newWidth);
						tabItemWidth = tabs.find("li").first().width();
						
						// Check min
						if (tabItemWidth < tabItemMinWidth) {
							// Resize tabs to min width
							tabs.find("li").width(tabItemMinWidth - 50);
						}
						else {
							// Create menu on the side
						}
					}
					
					// Fill Tab Page
					jqsender.data("context").appendTo(tabPage);
					// Append Tab Page
					tabPage.appendTo(jqsender.find('.pages').first());
				}
				
				// Set Menu Item Selected
				menuItem.trigger("click");
				jq(document).trigger("content.modified");
			},
			complete: function(){
			}
		});
	});
	
	jq(document).trigger("content.modified");
});