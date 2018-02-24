// Create formLabel object
formLabel = {
	create: function(context, forInputID) {
		// Create label item
		var label = formItem.create("label", "", "", "", "uiFormLabel").html(context);
		
		// Add extra attributes
		if (forInputID != "")
			label.attr("for", forInputID);
		
		// Return item
		return label;
	}
}