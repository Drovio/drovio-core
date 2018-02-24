// Create formItem generator
DOM = {
	create: function(tag, context, id, itemClass) {
		// Create DOMElement object
		var domElement = jq("<"+tag+"/>").html(context);
		if (id != "")
			domElement.attr("id", id);
			
		if (itemClass != "")
			domElement.addClass(itemClass);
		
		return domElement;
	}
}