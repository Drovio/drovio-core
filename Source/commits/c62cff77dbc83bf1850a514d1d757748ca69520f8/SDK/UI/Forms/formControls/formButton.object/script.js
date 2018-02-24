// Create formButton object
formButton = {
	create: function(title, type, id, name, positive) {
		// Create button item
		var button = formItem.create("button", name, id, "", "uiFormButton").html(title);
		
		// Add extra attributes
		button.attr("type", type);
		if (positive)
			button.addClass("positive");
		
		// Return item
		return button;
	}
}