/* 
 * Redback JS Document
 *
 * Title: RedBack JS Library - tabControl
 * Description: --
 * Author: RedBack Developing Team
 * Version: --
 * DateCreated: 21/08/2012
 * DateRevised: --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// let the document load
jq(document).one("ready.extra", function() {

	// Set dimensions for each gridView
	jq(document).on("content.modified", function() {
		jq(".gridView").each(function() {
			var totalWidth = jq(this).width();
			if (jq.type(jq(this).data('dim')) != "undefined") {
				var wSize = jq(this).data('dim').w;
				var size = totalWidth / wSize;
				
				// Set Dimensions
				jq('.gridCell', this).width(size).height(size);
			}
		});
	})
	jq(document).trigger("content.modified");
});