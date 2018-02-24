var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Init logger
	logger.init();

	// Init visual logger (Get checked filters and filter logs)
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


// Create Logger Object
logger = {
	logs : new Array(),
	status : function() {
		return (cookies.get("lggr") == "");
	},
	init : function() {
		// Init Boot Loader
		jq(document).on("content.modified", function(ev) {
			logger.loadLogs();
			logger.flush();
		});
	},
	loadLogs : function() {
		jq(".logWrapper").each(function(ev) {
			logger.logs.push(this);
			logger.flush();
			jq(this).remove();
		});
	},
	flush : function() {
		// Get Logger Data Container
		var logData = jq(document).find("#logData");
		if (logData.length > 0 && logger.logs.length > 0) {
			jq.each(logger.logs, function(index, value) {
				logData.append(jq(value).find("div").first());
			});
			
			// Clear logs
			logger.logs = new Array();
		}
	},
	log : function(content) {
		if (this.status() || debuggr.status())
			console.log(content);
	},
	dir : function(content) {
		if (this.status() || debuggr.status())
			console.dir(content);
	}
};