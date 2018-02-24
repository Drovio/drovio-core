// Code mirror initialization flag
var codeMirrorScriptLoaded = false;

// Load object
var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	
	// Load code mirror script
	jq.getScript("http://cdn.drov.io/libs/codeMirror/codemirror.js", function() {
		// Load code mirror addons
		jq.getScript("http://cdn.drov.io/libs/codeMirror/addons/edit/matchbrackets.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/addons/comment/continuecomment.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/addons/comment/comment.js");
		
		// Load code mirror scripts
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/php/php.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/htmlmixed/htmlmixed.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/xml/xml.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/javascript/javascript.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/css/css.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/sql/sql.js");
		jq.getScript("http://cdn.drov.io/libs/codeMirror/modes/clike/clike.js");
		
		// Set code mirror loaded flag
		codeMirrorScriptLoaded = true;
		
		// Initialize first content modified for this document
		jq(document).trigger("content.modified");
	});
	
	// On content modified search for un-initialized code mirrors and initialize them
	jq(document).on("content.modified", function() {
		// Check if code mirror script is loaded
		if (!codeMirrorScriptLoaded)
			return false;
			
		// Search for un-iniitalized code mirrors
		jq(".code-mirror-holder.initialize").each(function() {
			// Remove initialize class
			jq(this).removeClass("initialize");
			var cmID = jq(this).attr("id");
			
			// Map supported modes
			var modeMap = new Array();
			modeMap.php = "text/x-php";
			modeMap.css = "text/x-scss";
			modeMap.js = "text/javascript";
			modeMap.html = "text/html";
			modeMap.xml = "text/html";
			modeMap.sql = "text/x-sql";
			modeMap.plain = "";
			
			// Set code editor mode
			var cmType = jq(this).data("cmtype");
			var cmMode = modeMap[cmType];
			
			// Get read-only mode
			var cmReadOnly = jq(this).data("cmro");
			var cmReadOnlyMode = false;
			if (cmReadOnly)
				cmReadOnlyMode = true;
			
			// Initialize code mirror
			var textarea = document.getElementById(cmID + "-cm-textarea");
			var codeMirrorEditor = CodeMirror.fromTextArea(textarea, {
				lineNumbers: true,
				lineWrapping: true,
				indentUnit: 8,
				indentWithTabs: true,
				tabMode: "shift",
				tabSize: 8,
				autofocus: true,
				mode: cmMode,
				readOnly: cmReadOnlyMode
			});
			
			// Add listeners and events
			codeMirrorEditor.on("change", function(CodeMirror) {
				// Create custom change listener for compatibility
				jq(CodeMirror.display.wrapper.parentElement).trigger("text.modified");
				
				// Refresh code mirror
				CodeMirror.refresh();
				
				// Save code mirror to text area
				CodeMirror.save();
			});
			
			// Code Mirror focus
			codeMirrorEditor.on("focus", function(CodeMirror) {
				// Refresh code mirror
				CodeMirror.refresh();
			});
		});
		
	});
	
	// Initialize first content modified for this document
	jq(document).trigger("content.modified");
});