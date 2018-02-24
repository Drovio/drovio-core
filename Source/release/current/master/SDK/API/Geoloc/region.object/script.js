region = {
	get : function() {
		// Get current region value
		return cookies.get("region");
	},
	set : function(region) {
		// Set the region cookie for one year
		return cookies.set("region", region, (365 * 24 * 60 * 60), "/");
	}
}