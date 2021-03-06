// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	// Initialize Module Protocol Event Listeners
	jq(document).on("content.modified", function() {
		ModuleProtocol.contentModified();
	});
	// Reload page event
	jq(document).on("page.reload", function(ev) {
		location.reload(true);
	});
	// Redirect page event
	jq(document).on("page.redirect", function(ev, locationRef) {
		url.redirect(locationRef);
	});
});

// Create Module Communication Protocol Object based on AsCoP
ModuleProtocol = {
	contentModified : function() {
	
		// Self-triggered AJAX Content (Load)
		jq("[data-ajx]").off("load");
		jq("[data-ajx]").on("load", function(ev) {
			// Stops the Bubbling
			ev.stopPropagation();
			
			// (Re-) Load content if empty
			if (jq(this).contents().length == 0)
				jq(this).trigger("reload");
		});
		
		// Self-triggered AJAX Content (Reload)
		jq("[data-ajx]").off("reload");
		jq("[data-ajx]").on("reload", function(ev) {
			// Stops the Bubbling
			ev.stopPropagation();
			
			// Load Content
			ModuleProtocol.triggerAction(ev, jq(this), "GET", "ajx");
		});
		
		// Triggerable GET Content
		jq(document).off('click action', "[data-gt]");
		jq(document).on('click action', "[data-gt]", function(ev, preventAction) {
			// Stops the Bubbling
			ev.stopPropagation();

			// Prevent Module Action
			if (preventAction)
				return;
			
			// Trigger loader
			var silent = false;
			ModuleProtocol.triggerAction(ev, jq(this), "GET", "gt", null, null, false);
		});
		
		// Triggerable POST Content (for compatibility reasons)
		jq(document).off('click action', "[data-action]");
		jq(document).on('click action', "[data-action]", function(ev, preventAction) {
			// Stops the Bubbling
			ev.stopPropagation();
			// Prevent Module Action
			if (preventAction)
				return;
				
			var formInputs = jq(this).closest("form").find("input[name!=''],select[name!=''],textarea[name!=''],button[type='submit']").serialize();
			
			// Trigger loader
			ModuleProtocol.triggerAction(ev, jq(this), "POST", "action", formInputs);
		});
		
		// Set attr as ajax object and remove from html
		jq("[data-attr]").each(function(){
			jq(this).data("attr", jq(this).data("attr")).removeAttr("data-attr");
		});
		
		// Load content at startup
		jq("[data-startup=1]").each(function(){
			jq(this).data("startup", jq(this).data("startup")).removeAttr("data-startup");
			jq(this).trigger("load");
		});
	},
	triggerAction : function (ev, jqSender, method, callType, extraParams, callback) {
		// Stops the Bubbling
		ev.stopPropagation();
		
		// Push Page State
		if (jqSender.prop("tagName") == "A") {
			// Check if key for new tab is clicked, then do nothing
			if (ev.ctrlKey || ev.metaKey)
				return;
			
			// Stops the Default Action (if any)
			ev.preventDefault();
				
			// Check if there is a form preventing unload
			if (jq.type(FormProtocol.preventUnload(".uiMainContainer")) != "undefined") {
				var ans = confirm("All unsaved data will be lost in this page. Navigate ?");
				if (!ans)
					return;
			}
			
			// Set state and load page
			var stateHref = jqSender.attr("href");
			state.push(stateHref, function() {
				// Clear the ribbon page navigation
				jq(document).trigger('ribbon.clear');
			});
		}
		
		var AJAXData = jqSender.data(callType);
		ModuleProtocol.ascopRequest(jqSender, AJAXData, method, callType, extraParams, callback, false);
	},
	// Load AJAX Module Content
	ascopRequest : function (jqSender, action, method, callType, extraParams, callback, silent) {
		
		// Initialize variables to send
		var ModuleParams = "";
	
		for (var act in action)
			ModuleParams += "&__REQUEST["+act.toUpperCase()+"]="+encodeURIComponent(action[act]);
		
		if (method.toUpperCase() == "GET") {
			// Get url variables
			var urlVars = url.getVar();
			for (var urlv in urlVars)
				ModuleParams += "&"+urlv+"="+encodeURIComponent(urlVars[urlv]);
		}
		
		// Get All Attributes from context
		for (var act in jqSender.data("attr"))
			ModuleParams += "&" + act + "=" + encodeURIComponent(jqSender.data("attr")[act]);
		
		// Add extra variables
		if (typeof extraParams != 'undefined') {
			ModuleParams += "&" + extraParams;
		}

		// Check callback
		if (typeof callback != 'function') {
			callback = function(report) { ModuleProtocol.handleReport(jqSender, report, callType); }
		}
		
		// Bootloader callback
		var bootLoaderCallback = function(report) { BootLoader.processReport(report, callback); }
		
		// AsCoP Request
		ascop.asyncRequest("/ajax/modules/load.php", method, ModuleParams, "html", jqSender, bootLoaderCallback, null, silent, true);
	},
	// Handles server reports
	handleReport : function (sender, report, callType) {
		// Foreach content in the report, do something
		jq(report).find('.content').each(function() {
			// Get inner contents
			var innerContent = jq(this).contents();
			
			// Get Result Type
			var reportType = jq(this).data('type');

			// Take action according to result type
			switch (reportType) {
				case "data":
					// If there is no content, trigger modification and exit
					if (innerContent.length == 0) {
						jq(document).trigger("content.modified", true);
						return false;
					}

					// Get Page Title from content
					if (jq("#page_title", jq(innerContent)).length == 1) {
						title = jq("#page_title", jq(innerContent)).remove().text();
						if (title != "")
							jq("title").text(title);
					}
					
					// Get Report Parameters
					var dataHolder;
					// If sender is loading at startup, set default holder as sender
					if (sender.data("startup") == true)
						dataHolder = sender;
					else if (sender.data(callType) != undefined)
						dataHolder = sender.data(callType).holder;
					var handleDataMethod = jq(this).data('attr').method;

					// If sender has no holder, get holder from context
					if (typeof(dataHolder) == "undefined" || dataHolder == "")
						dataHolder = jq(this).data('attr').holder;

					// If no holder is given anywhere, get sender
					if (typeof(dataHolder) == "undefined")
						dataHolder = sender;

					var jqHolder = jq(dataHolder, sender);
					if (jqHolder.length == 0)
						jqHolder = jq(dataHolder).first();

					// Remove old contents if replace
					if (handleDataMethod == "replace")
						jqHolder.contents().remove();
					
					// Append content to holder
					innerContent.appendTo(jqHolder);
						
					break;
				case "popup":
					jq(sender).popup(innerContent);
					break;
				case "action":
					// Get action data
					var action = jq(this).find("action").first();
					var actionType = action.data("attr").type;
					var actionValue = action.data("attr").value;
					
					// Trigger action
					sender.trigger(actionType, actionValue);
					break;
			}
		});
		
		// Check content arrived
		jq(document).trigger("content.modified");
		
		return true;
	}
}