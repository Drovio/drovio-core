var jq=jQuery.noConflict();

jq(document).one("ready", function() {

	// Init logger (Get checked filters and filter logs)
	jq(document).on("content.modified", function(ev) {
		jqLogger = jq(document).find("#logViewer");
		jqLogger.find(".filterCheck").each(function() {
			// Get Filter Category
			var cat = jq(this).data("cat");
			// Filter logs
			if (jq(this).is(":checked"))
				jqLogger.find(".logItem[data-cat='"+cat+"']").removeClass("noDisplay");
			else
				jqLogger.find(".logItem[data-cat='"+cat+"']").addClass("noDisplay");
		});
	});
	
	// Check filters
	jq(document).on('change', ".filterCheck", function(ev) {
		// Trigger Content.modified
		jq(document).trigger("content.modified");
	});
	
	// Clear log
	jq(document).on('click', "#logViewer .clear", function(ev) {
		// Get Logger
		jqLogger = jq(document).find("#logViewer");

		// Clear all logs
		jqLogger.find("#logData").empty();
	});
	
	jq(document).on('click', ".logItem .logHeader", function(ev) {
		// Get Log Item
		var logItem = jq(this).closest(".logItem");

		// Expand
		if (logItem.hasClass("open"))
			logItem.removeClass("open");
		else
			logItem.addClass("open");
	});
	
	// Update logger priority
	jq(document).on('change', ".loggerPriority", function(ev) {
		// Submit form
		jq(this).closest("form").trigger("submit");
	});
	
	// Trigger Content.modified
	jq(document).trigger("content.modified");
});