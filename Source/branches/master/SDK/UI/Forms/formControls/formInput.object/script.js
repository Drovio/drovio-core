// Create formInput object
formInput = {
	create: function(type, name, id, value, required) {
		// Check if input is radio or checkbox
		checked = false;
		if (type == "checkbox" && jq.type(value) == "boolean")
		{
			checked = (value === true);
			value = "";
		}
		
		// Create input item
		var input = formItem.create("input", name, id, value, "uiFormInput");
		
		// Add extra attributes
		input.attr("type", type);
		input.attr("checked", checked);
		input.attr("required", required);
		
		// Return item
		return input;
	}
}