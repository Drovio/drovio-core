var jq=jQuery.noConflict();

jq(document).one("ready", function() {
	jq(document).on('click', ".uiNotification .closeBtn", function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();
		
		jq(this).closest(".uiNotification").remove();
	});
	
	jq(document).on("content.modified", function(ev) {
		// For each timeout notification, set timeout fade out
		jq(document).find(".uiNotification.timeout").each(function() {
			// Get notification
			var jqthis = jq(this);
			
			// Remove class
			jqthis.removeClass("timeout");
			
			// Set fade out timeout
			setTimeout(function() {
				jqthis.fadeOut('slow', function() {
					jqthis.remove();
				});
			}, 2000);
		});
	});
});