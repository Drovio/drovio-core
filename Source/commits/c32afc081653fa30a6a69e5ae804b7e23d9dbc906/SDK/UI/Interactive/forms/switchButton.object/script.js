var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	switchButton.init();
});

switchButton = {
	init : function() {
		jq(document).off("switch.on", ".uiSwitchButton");
		jq(document).on("switch.on", ".uiSwitchButton", function(ev) {
			// Get switch
			jqSwitch = jq(this);
			
			// Transition to on status
			jqSwitch.addClass("on");
			jqSwitch.removeClass("loading");
			
			// Set checkbox value for next submit
			jqSwitch.find(".swt_val").val(1);
			
			// Notify for content modified
			jqSwitch.trigger("status.modified");
		});
		
		jq(document).off("switch.off", ".uiSwitchButton");
		jq(document).on("switch.off", ".uiSwitchButton", function(ev) {
			// Get switch
			jqSwitch = jq(this);
			
			// Transition to on status
			jqSwitch.removeClass("on");
			jqSwitch.removeClass("loading");
			
			// Set checkbox value for next submit
			jqSwitch.find(".swt_val").val(0);
			
			// Notify for content modified
			jqSwitch.trigger("status.modified");
		});
		
		jq(document).off("click", ".uiSwitchButton");
		jq(document).on("click", ".uiSwitchButton", function(ev) {
			// Get Switch Object
			var jqSwitch = jq(this);
			
			// Initiate loading status
			jqSwitch.addClass("loading");
			
			// Submit form
			jqSwitch.trigger("submit");
		});
	},
	getStatus : function(jqSwitch) {
		return jqSwitch.hasClass("uiSwitchButton") && jqSwitch.hasClass("on");
	}
};