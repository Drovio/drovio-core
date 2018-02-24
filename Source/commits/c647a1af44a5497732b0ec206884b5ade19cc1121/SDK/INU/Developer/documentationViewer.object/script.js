jq(document).on("scroll wheel mousewheel", ".sectionWrapper", function(){
		var jqthis = jq(this);
		setTimeout(function(){
			if (jqthis.scrollTop() > 38) {
				jqthis.addClass("dock");
			}else{
				jqthis.removeClass("dock");
			}
		}, 1);
});