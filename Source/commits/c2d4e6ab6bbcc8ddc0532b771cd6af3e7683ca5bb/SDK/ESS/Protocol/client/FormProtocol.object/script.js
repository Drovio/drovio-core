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
		jq(document).off('fsubmited.form', 'form');
		jq(document).on('fsubmited.form', 'form', function(ev) {
			jq(this).removeData("preventUnload");
		});
		
		// Get form event listeners
		jq(document).off('freset.form', 'form');
		jq(document).on('freset.form', 'form', function(ev) {
			jq(this).trigger('reset');
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
		var formInputs = jqForm.serialize();
		
		// Disable all inputs
		jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", true).addClass("disabled");
		
		// Set Complete callback Handler function
		var completeCallback = function() {
			// Enable inputs again
			jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", false).removeClass("disabled");
		};
		
		// Trigger request
		if (jqForm.data("form-action") != undefined) {
			// Submit form to Module
			ModuleProtocol.trigger(ev, jqForm, "POST", "formAction", formInputs, null, null, completeCallback, true);
		} else {
			// Set Success Callback Custom Handler function
			var successCallback = function(report) {
				// Handle form content
				HTMLServerReport.parseReportContent(jqForm, report, "formAction");
			};
			
			// Start HTMLServerReport request
			var formAction = jqForm.attr("action");
			HTMLServerReport.request(formAction, "POST", formInputs, jqForm, null, completeCallback, true, null);
		}
	},
	reset : function(ev, jqForm) {
		// Reset form
		jqForm.trigger('reset');
	}
}