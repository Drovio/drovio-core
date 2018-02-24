var jq=jQuery.noConflict();
jq(document).one("ready.extra", function() {
	
	// editor content was modified
	jq(document).on("text.modified", ".wideContainer", function(){
		// Trigger tab to be modified
		jq(this).trigger("tab.modified");

	});
	
	jq(document).on("content.saved", ".wideContainer", function(){
		// Trigger tab to be modified
		jq(this).trigger("tab.synced");

	});
	
	
	jq(document).on("content.modified", function(){
			// Sync and Get content from modules pool
			jq(".wide > .dropPool").children().each(function() {
				// Get wide
				jqWide = jq(this).closest(".wide");
				
				// Get Container
				var wideContainer = jq(this).detach().data("info", jq(this).data("info")).removeAttr("data-info");
				
				// Add Tab
				var tabControlID = jqWide.attr("id")+"_"+"tabber";
				jqWide.find("#"+tabControlID).first().trigger("add.tab", [true, wideContainer.data('info').id, wideContainer.data('info').title, wideContainer, false]);
			});
			
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
	jq(document).trigger("content.modified");

	// temporary key combinations
	jq(window).on({
		"keydown" : function(ev){
			if (ev.which == 83 & (ev.ctrlKey || ev.metaKey) && !ev.shiftKey) { //CTRL | CMD + s (save)
				jq(this).closest("form").trigger("submit");
				ev.preventDefault();
			}else if (ev.which == 190 & (ev.ctrlKey || ev.metaKey)){ //Test
				jq(document).trigger("toggleParsing");
				ev.preventDefault();
			}else if (ev.altKey && (ev.which == 187 || ev.which == 107)){ //ALT + '+' (Inc font size)
				jq(this).trigger("changeFontSize", "+=1");
				ev.preventDefault();
			}else if (ev.altKey && (ev.which == 189 || ev.which == 109)){ //ALT + '-' (Dec font size)
				jq(this).trigger("changeFontSize", "-=1");
				ev.preventDefault();
			}else if (ev.altKey && (ev.which == 48 || ev.which == 96)){ //ALT + '0' (Def font size)
				jq(this).trigger("changeFontSize", "");
				ev.preventDefault();
			}
		}
	}, ".codeEditor");
});