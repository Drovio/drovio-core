// Create formItem generator
formItem = {
	create: function(tag, name, id, value, itemClass) {
		// Create item object
		var item = DOM.create(tag, "", id, itemClass);
		
		// Add name and value
		item.attr("name", name);
		item.attr("value", value);
		
		// Return item
		return item;
	}
}