// Create simpleForm interface
var jq = jQuery.noConflict();
simpleForm = {
	insertRow: function(form, title, input, required, notes) {
		// Build a form row
		var formRow = this.buildRow(form, title, input, required, notes);
		
		// Append row to form body
		FormPrototype.append(form, formRow);
	},
	buildRow: function(form, title, input, required, notes) {
		// Get row
		var formRow = this.getRow();
		
		// Build label
		var inputID = jq(input).attr("id");
		var label = this.getSimpleLabel(form, title, inputID);
		// Set label as required
		if (jq.type(required) != "undefined" && required == true) {
			var reqElement = jq("<span />").html("*").addClass("required");
			label.append(reqElement);
		}
		
		// Append label to row
		formRow.append(label);
		
		// Append input to row
		formRow.append(input);
		
		// Create notes (if not empty)
		if (jq.type(notes) != "undefined") {
			var notesElement = this.getNotes(notes);
			formRow.append(notesElement);
		}
		
		// Return row
		return formRow;
	},
	getRow: function() {
		// Build a form row container
		return jq("<div />").addClass("simpleFormRow");
	},
	getSimpleLabel: function(form, title, forInputID) {
		// Build a simple form label
		return Form.getLabel(form, title, forInputID, "simpleFormLabel");
	},
	getNotes: function(notes) {
		// Build a form row container
		return jq("<div />").html(notes).addClass("simpleFormNotes");
	}
}