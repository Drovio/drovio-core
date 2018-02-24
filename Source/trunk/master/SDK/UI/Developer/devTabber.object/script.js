var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	// Editor content was saved, Trigger tab as synced
	jq(document).on("content.saved", ".wideContainer", function(){
		jq(this).trigger("tab.synced");
	});
	
	// Generic content modified listeners for this object
	jq(document).on("content.modified", function(){
			// Sync and Get content from modules pool
			jq(".wide > .dropPool").children().each(function() {
				// Get wide
				jqWide = jq(this).closest(".wide");
				
				// Get Container
				var wideContainer = jq(this).data("info", jq(this).data("info")).removeAttr("data-info").detach();
				
				// Add Tab
				var tabControlID = jqWide.attr("id")+"_tbc";
				var tab_id = wideContainer.data('info').id;
				var tab_title = wideContainer.data('info').title;
				var tab_context = wideContainer;
				tabber.addTab(jqWide.find("#"+tabControlID).first(), tab_id, tab_title, tab_context);
			});
			
			// Show notification results
			jq(".wide > .result").children().each(function() {
				// Get wide
				jqWide = jq(this).closest(".wide");
				
				// Show Popup
				jqWide.popup.withTimeout = true;
				jqWide.popup.withFade = true;
				jqWide.popup.position = {"top":"5px", "right":"5px"};
				jqWide.popup(jq(this), jqWide);
			});
	});
});