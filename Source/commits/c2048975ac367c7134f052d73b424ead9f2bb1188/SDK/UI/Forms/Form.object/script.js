var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Initialize Form Factory/Builder
	Form.init();
});

// Create Form Factory/Builder
Form = {
	init: function() {
	},
	getInput: function(form, type, name, value, inputClass, autofocus, required) {
		// Create input
		var inputID = this.getInputID(form, name);
		var input = formInput.create(type, name, inputID, value, required);
		
		// Set extra attributes
		input.addClass(inputClass);
		input.attr("autofocus", autofocus);
		
		// Return input
		return input;
	},
	getLabel: function(form, text, forInputID, labelClass) {
		// Create label
		var label = formLabel.create(text, forInputID);
		
		// Set extra attributes
		label.addClass(labelClass);
		
		// Return label
		return label;
	},
	getButton: function(form, title, name, buttonClass) {
		// Create button
		var buttonID = this.getInputID(form, name);
		var button = formButton.create(title, "button", buttonID, name, false);
		
		// Set extra attributes
		button.addClass(buttonClass);
		
		// Return button
		return button;
	},
	getSubmitButton: function(title, id, name) {
		// Create button
		return formButton.create(title, "submit", id, name, true);
	},
	getResetButton: function(title, id) {
		// Create button
		return formButton.create(title, "reset", id, name);
	},
	getTextarea: function(form, name, value, areaClass, autofocus) {
		// Create textarea
		var textareaID = this.getInputID(form, name);
		var textarea = formItem.create("textarea", name, textareaID, value, itemClass);
		
		// Set extra attributes
		textarea.attr("autofocus", autofocus);
		
		// Return textarea
		return textarea;
	},
	getInputID: function(form, name) {
		// Get form id
		var formID = FormPrototype.getFormID(form);
		
		// Create input id
		var random = Math.ceil(Math.random() * Math.pow(10, 10));
		return "i"+formID+"_"+name+random;
	}
}