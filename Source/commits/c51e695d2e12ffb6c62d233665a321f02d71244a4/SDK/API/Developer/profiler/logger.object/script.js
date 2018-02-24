// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	Logger.init();
});

// Create Logger Object
Logger = {
	logs : new Array(),
	status : function() {
		return (cookies.get("lggr") == "");
	},
	init : function() {
		// Init Boot Loader
		jq(document).on("content.modified", function(ev) {
			Logger.flush();
		});
	},
	loadLogs : function(report) {
		jq("#head", report).find("log").each(function(ev) {
			Logger.logs.push(this);
			Logger.flush();
		});
	},
	flush : function() {
		// Get Logger Data Container
		var logData = jq(document).find("#logData");
		if (logData.length > 0 && Logger.logs.length > 0) {
			jq.each(Logger.logs, function(index, value) {
				logData.append(jq(value).find("div").first());
			});
			
			// Clear logs
			Logger.logs = new Array();
		}
	},
	log : function(content) {
		if (this.status())
			console.log(content);
	},
	dir : function(content) {
		if (this.status())
			console.dir(content);
	}
};