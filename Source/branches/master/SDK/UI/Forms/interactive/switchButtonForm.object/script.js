var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	switchButtonForm.init();
});

switchButtonForm = {
	init : function() {
		jq(document).off("switch.on", ".uiSwitchButtonForm");
		jq(document).on("switch.on", ".uiSwitchButtonForm", function(ev) {
			// Activate switch
			jqSwitch = jq(this).find(".sbf");
			switchButton.activate(jqSwitch);
			
			// Notify for content modified
			jqSwitch.trigger("status.modified");
		});
		
		jq(document).off("switch.off", ".uiSwitchButtonForm");
		jq(document).on("switch.off", ".uiSwitchButtonForm", function(ev) {
			// Deactivate switch
			jqSwitch = jq(this).find(".sbf");
			switchButton.deactivate(jqSwitch);
			
			// Notify for content modified
			jqSwitch.trigger("status.modified");
		});
		
		jq(document).off("click", ".uiSwitchButtonForm .sbf");
		jq(document).on("click", ".uiSwitchButtonForm .sbf", function(ev) {
			// Get Switch Object
			var jqSwitch = jq(this);
			
			// Submit form
			setTimeout(function() {
				jqSwitch.trigger("submit");
			}, 10);
		});
	},
	getStatus : function(jqSwitchForm) {
		// Get switch
		var jqSwitch = jqSwitchForm.find(".sbf");
		
		// Return switch status
		return switchButton.getStatus(jqSwitch);
	}
};