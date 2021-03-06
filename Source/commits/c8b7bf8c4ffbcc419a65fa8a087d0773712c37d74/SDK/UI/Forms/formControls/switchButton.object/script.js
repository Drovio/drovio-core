var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	switchButton.init();
});

switchButton = {
	init : function() {
		jq(document).off("click", ".uiSwitchButton");
		jq(document).on("click", ".uiSwitchButton", function(ev) {
			// Get Switch Object
			var jqSwitch = jq(this);
			
			// Toggle on-off class
			if (jqSwitch.hasClass("loading"))
				jqSwitch.toggleClass("on");
			
			// Initiate loading status
			jqSwitch.addClass("loading");
			
			// Set checkbutton
			if (jqSwitch.hasClass("on"))
				jqSwitch.find(".swt_chk").attr("checked", false);
			else
				jqSwitch.find(".swt_chk").attr("checked", true);
		});
	},
	getStatus : function(jqSwitch) {
		return jqSwitch.hasClass("uiSwitchButton") && jqSwitch.hasClass("on");
	},
	activate : function(jqSwitch) {
		// Transition to on status
		jqSwitch.addClass("on");
		jqSwitch.removeClass("loading");

		// Set checkbox value for next submit
		jqSwitch.find(".swt_chk").attr("checked", true);
	},
	deactivate : function(jqSwitch) {
		// Transition to on status
		jqSwitch.removeClass("on");
		jqSwitch.removeClass("loading");

		// Set checkbox value for next submit
		jqSwitch.find(".swt_chk").attr("checked", false);
	}
};