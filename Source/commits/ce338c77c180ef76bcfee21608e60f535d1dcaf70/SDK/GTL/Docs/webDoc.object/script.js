var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Click on images and open them in a new tab
	// This feature will preview the image in the same page later on
	jq(document).on("click", ".wDoc .image img", function() {
		// Get image source
		var imageSrc = jq(this).attr("src");
		
		// Open into a new tab
		window.open(imageSrc, "_blank");
	});
	
	// Toggle contents
	jq(document).on("click", ".wDoc .toc .toggleButton", function() {
		// Toggle content list
		jq(this).closest(".toc").toggleClass("closed");
	});
});