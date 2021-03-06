var jq = jQuery.noConflict();
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
			
			// Submit form
			FormProtocol.submit(ev, jq(this));
		});
		
		// Prevent Reload or Redirect on Form.edit
		jq(window).on('beforeunload', function() {
			return FormProtocol.preventUnload();
		});

		// Set form prevent unload on edit all data-pu forms
		jq(document).on('change', 'input, textarea, select', function() {
			if (jq.type(jq(this).closest('form').data("pu")) != "undefined")
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
		jq(document).on('freset.form', 'form', function(ev, full) {
			// Reset form (full or password-only)
			if (full == 1 || full == undefined)
				jq(this).trigger('reset');
			else
				jq(this).find("input[type=password]").val("");
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
		var formsPreventingUnload = jqForms.filter(function() {
				return jq.type(jq(this).data("preventUnload")) != "undefined";
			});
		if (formsPreventingUnload.length > 0)
		{
			var pu = "You are editing multiple forms right now. Do you want to leave without finishing?"
			if (formsPreventingUnload.length == 1)
			{
				var fpu = formsPreventingUnload.first().data("pu");
				if (fpu != "1" && fpu != "true")
					pu = fpu;
				else
					pu = "You are editing a form at the moment. Do you want to leave without finishing?"
			}
			
			return pu;
		}
	},
	submit : function(ev, jqForm, callback) {
		// Check if form is already posting
		if (jqForm.data("posting") == true)
			return false;
		
		// Initialize posting
		jqForm.data("posting", true);
			
		// Clear form report
		jqForm.find(".formReport").empty();
		
		// Form Parameters
		var formData = "";
		if (jqForm.attr('enctype') == "multipart/form-data") {
			// Initialize form data
			formData = new FormData();
			
			// Get form data
			var fdArray = jqForm.serializeArray();
			for (index in fdArray)
				formData.append(fdArray[index].name, fdArray[index].value);
			
			// Get files (if any)
			jqForm.find("input[type='file']").each(function() {
				if (jq.type(this.files[0]) != "undefined")
					formData.append(jq(this).attr("name"), this.files[0]);
			});
		}
		else
			formData = jqForm.serialize();
		
		// Disable all inputs
		jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", true).addClass("disabled");
		
		// Set Complete callback Handler function
		var completeCallback = function(ev) {
			// Enable inputs again
			jqForm.find("input[name!=''],select[name!=''],textarea[name!=''],button").prop("disabled", false).removeClass("disabled");
			
			// Set posting status false
			jqForm.data("posting", false);
			
			// Execute custom callback (if any)
			if (typeof callback == 'function') {
				callback.call(this, ev);
			}
		};
		
		// Trigger request
		if (jqForm.data("form-action") != undefined) {
			console.log("Module's form-action attribute is deprecated. Use new formFactory interface for engaging.");
			// Submit form to Module
			ModuleProtocol.trigger(ev, jqForm, "POST", "formAction", formData, null, null, completeCallback, true);
		} else {
			// Check if form is for upload
			var options = new Object();
			if (jqForm.attr('enctype') == "multipart/form-data") {
				options.cache = false;
				options.contentType = false;
			}
			
			// Start HTMLServerReport request
			var formAction = jqForm.attr("action");
			HTMLServerReport.request(formAction, "POST", formData, jqForm, null, completeCallback, true, null, options);
		}
	},
	reset : function(ev, jqForm) {
		// Reset form
		jqForm.trigger('reset');
	}
}