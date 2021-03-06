/* 
 * Redback JavaScript Document
 *
 * Title: RedBack Form Submit Protocol
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.00
 * DateCreated: 01/03/2013
 * DateRevised: --
 *
 */
 
// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

// Initialize Form Protocol
jq(document).one("ready", function() {
	// Initialize Module Protocol Event Listeners
	jq(document).on("content.modified", function(ev) {
		FormProtocol.init();
	});
});

// Create Asychronous Communication Protocol Object
FormProtocol = {
	init : function() {
		// Initialize form submit
		jq(document).off('submit', 'form[data-async]');
		jq(document).on('submit', 'form[data-async]', function(ev) {
			// Stops the Default Action (if any)
			ev.preventDefault();

			// Clear form report
			jq(this).find(".formReport").empty();
			
			// Submit form
			FormProtocol.submit(ev, jq(this));
		});
		
		// Prevent Reload or Redirect on Form.edit
		jq(window).off('beforeunload');
		jq(window).on('beforeunload', function() {
			return FormProtocol.preventUnload();
		});

		// Set form prevent unload on edit all data-pu forms
		jq(document).off('change', 'input, textarea, select');
		jq(document).on('change', 'input, textarea, select', function() {
			if (jq(this).closest('form').data("pu") == 1)
				jq(this).closest('form').data("preventUnload", true);
		});
		
		// Remove data-pu attr from forms
		jq("form[data-pu]").each(function(){
			jq(this).data("pu", jq(this).data("pu")).removeAttr("data-pu");
		});
		
		// Get form event listeners
		jq(document).off('submited.form', 'form');
		jq(document).on('submited.form', 'form', function() {
			jq(this).removeData("preventUnload");
		});
		
		// Get form event listeners
		jq(document).off('reset.form', 'form');
		jq(document).on('reset.form', 'form', function() {
			FormProtocol.reset(jq(this));
		});
	},
	preventUnload : function(holder) {
		// Get forms inside holder (if defined), otherwise get all forms
		var jqForms = null;
		if (jq.type(holder) != "undefined")
			jqForms = jq(holder).find("form");
		else
			jqForms = jq("form");
			
		// Check for forms that prevent unload
		if (jqForms.filter(function() { return jq.type(jq(this).data("preventUnload")) != "undefined";}).length > 0)
			return "All unsaved data will be lost.";
	},
	submit : function(ev, jqForm) {
		// Form Parameters
		var formInputs = jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button[type='submit']").serialize();
		
		// Disable all inputs
		jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", true).addClass("disabled");
		
		// Set Handler function
		var handlerFunction = function(report) {
			// Handle form content
			ModuleProtocol.handleReport(jqForm, report, "formAction");
			
			// Enable inputs again
			jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", false).removeClass("disabled");
		};
		
		if (jqForm.data("form-action") != undefined) {
			// Submit form to Module
			ModuleProtocol.triggerAction(ev, jqForm, "POST", "formAction", formInputs, handlerFunction);
		} else {
			// Bootloader callback
			var bootLoaderCallback = function(report) { BootLoader.processReport(report, handlerFunction); }
			
			// Submit form to action
			var formAction = jqForm.attr("action");
			ascop.asyncRequest(formAction, "POST", formInputs, "json", jqForm, bootLoaderCallback, null, false);
		}
	},
	reset : function(ev, jqForm) {
		// Reset form
		jqForm.trigger('reset');
	}
}