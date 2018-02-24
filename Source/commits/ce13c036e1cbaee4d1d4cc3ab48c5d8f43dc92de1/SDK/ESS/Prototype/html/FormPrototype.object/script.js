// Create Form Prototype object
var jq = jQuery.noConflict();
FormPrototype = {
	append: function(form, element) {
		// Find form body and append element
		return jq(form).find(".formBody").append(element);
	},
	getFormID: function(form) {
		return jq(form).attr("id");
	}
}