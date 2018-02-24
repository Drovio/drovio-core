locale = {
	get : function() {
		// Get current locale
		return cookies.get("lc");
	},
	set : function(locale) {
		// Set the locale cookie for one year
		return cookies.set("lc", locale, (365 * 24 * 60 * 60), "/") {
	}
}