// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

jq(document).one("ready", function() {
	switchButton.init();
});

switchButton = {
	init : function() {
		jq(document).on("switch.on", ".uiSwitchButton", function(ev) {
			jqSwitch = jq(this);
			jqSwitch.addClass("on");
			jqSwitch.removeClass("loading");
			jqSwitch.trigger("status.modified");
		}),
		jq(document).on("switch.off", ".uiSwitchButton", function(ev) {
			jqSwitch = jq(this);
			jqSwitch.removeClass("on");
			jqSwitch.removeClass("loading");
			jqSwitch.trigger("status.modified");
		}),
		jq(document).on("click", ".uiSwitchButton", function(ev) {
			// Get Switch Object
			jqSwitch = jq(this);
			
			// Trigger Module Action
			if (jq.type(jqSwitch.data("sb")) != "undefined") {
				// Set Switch as loading
				jqSwitch.addClass("loading");
				
				// Make ajax call
				ModuleProtocol.triggerAction(ev, jqSwitch, "POST", "sb", null);
			}
		});
	},
	getStatus : function(jqSwitch) {
		return jqSwitch.hasClass("uiSwitchButton") && jqSwitch.hasClass("on");
	}
};