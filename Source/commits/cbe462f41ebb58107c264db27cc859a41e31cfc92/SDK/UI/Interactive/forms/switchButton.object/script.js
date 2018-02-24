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
			var jqSwitch = jq(this);
			jqSwitch.addClass("loading");
			jqSwitch.trigger("submit");
		});
	},
	getStatus : function(jqSwitch) {
		return jqSwitch.hasClass("uiSwitchButton") && jqSwitch.hasClass("on");
	}
};