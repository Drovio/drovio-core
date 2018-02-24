// Create iFrame generator
HTMLFrame = {
	create: function(src, name, id, frameClass, sandbox) {
		// Create item object
		var frameItem = DOM.create("iframe", "", id, frameClass);
		
		// Add source, name and sandbox
		frameItem.attr("name", name);
		frameItem.attr("src", src);
		frameItem.attr("sandbox", sandbox);
		
		// Return item
		return frameItem;
	}
}