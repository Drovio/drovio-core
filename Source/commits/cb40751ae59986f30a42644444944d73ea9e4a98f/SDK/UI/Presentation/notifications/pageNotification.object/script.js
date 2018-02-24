var jq=jQuery.noConflict();

// Create pageNotification object
pageNotification = {
	show : function(sender, ntf_id, title, actionTitle, actionCallback, disposeCallback, timeout) {
		// Check if there is already a notification with the same id
		if (jq("#" + ntf_id).length > 0)
			return;
		
		// Create notfication
		ntf_id = (ntf_id == undefined ? "pg_ntf_".Math.round(Math.random() * 1000000) : ntf_id);
		var notification = DOM.create("div", "", ntf_id, "page-notification");
		
		// Close button
		var close_button = DOM.create("div", "", "", "close_button");
		notification.append(close_button);
		
		// Add close button callback
		close_button.on("click", function() {
			// Close poup
			jq(this).trigger("dispose");
			
			// Call callback function
			if (typeof disposeCallback == 'function')
				disposeCallback.call(this);
		});
		
		// Title
		notification.append(DOM.create("div", title, "", "title"));
		
		// Action button
		if (actionTitle != undefined || actionTitle != null) {
			// Add action
			var ntf_action = DOM.create("div", actionTitle, "", "action_button");
			notification.append(ntf_action);
			
			// Add action callback
			ntf_action.on("click", function() {
				// Call callback function
				if (typeof actionCallback == 'function')
					actionCallback.call(this);
			});
		}
		
		// Get notification count to see for position
		var ntf_count = jq(".page-notification").length;
		var pos_bottom = 20 + (55 + 20) * ntf_count;
		
		// Show popup
		var jqSender = jq(sender);
		jqSender.popup.withFade = true;
		jqSender.popup.withTimeout = (timeout == undefined ? false : timeout);
		jqSender.popup.type = "persistent";
		jqSender.popup.position = {"bottom":pos_bottom+"px","left":"20px","position":"fixed"};
		jqSender.popup(notification);
	}
}