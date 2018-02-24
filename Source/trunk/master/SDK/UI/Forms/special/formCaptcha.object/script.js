// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();


// let the document load
jq(document).one("ready.extra",function() {
	// load helping scripts once
	// and register listeners
	
	// host
	var _host = "";
	/*
	// on content modified search any orphaned editors and initialize them
	jq(document).on("content.modified", function() {
		
		var captchas = jq(".captchaWrapper[data-fid]").each(function(){
			jq(this).data("fid", jq(this).attr("data-fid"));
		}).removeAttr("data-fid");
		
		captchas.each(function(){
			// Ajax			
		});
		
	});
	*/
	/*// New captcha on click
	jq(document).on("click", ".captchaWrapper", function() {
		$captcha = jq(this).find("img");
		$source = $captcha.prop("src");
		$captcha.removeProp();
		$captcha.prop("src", $source);
	});*/
});