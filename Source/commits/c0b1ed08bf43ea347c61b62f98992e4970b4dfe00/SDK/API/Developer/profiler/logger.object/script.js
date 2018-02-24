// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	logger.init();
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
			logger.flush();
		});
	},
	loadLogs : function(report) {
		jq("#head", report).find("log").each(function(ev) {
			logger.logs.push(this);
			logger.flush();
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