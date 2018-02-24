var jq = jQuery.noConflict();
jq(document).one("ready", function(){
	// On content modified, find uninitialized html5Editors and initialize them
	jq(document).on("content.modified", function(){
		// Initialize un-initialized HTML5Editors
		jq(".html5Editor").filter(function(){
			return jq.type(jq(this).data("initialized")) == "undefined";
		}).data("initialized", true).html5Editor();
	});
	
	// Initialize first content modified for this document
	jq(document).trigger("content.modified");
});
	
// This wraps the html5Editor extension
(function(jq){
	// Method Logic for HTML5Editor
	var methods = {
		init : function() {
			return this.each(function(){
				HTML5EditorInitialize.call(this);
			});		
		}
	}
	
	// Give the extension a name
	jq.fn.html5Editor = function() {
		return methods.init.apply(this);
	};
	
	// Initialization of HTML5Editor (assign listeners etc...)
	function HTML5EditorInitialize() {
		// Store objects
		var HTML5Editor = jq(this);
		var previewPanel = HTML5Editor.find(".previewPanel");
		var htmlCodePanel = HTML5Editor.find(".htmlCodePanel");
		var cmEditor = HTML5Editor.find(".htmlCodePanel .html5editor_cm");
		
		// Keep last state
		var lastCodeState = jq("<div>").text(previewPanel.html()).text();
		
		// Toggle preview
		HTML5Editor.on("click", ".htmlTool", function() {
			// Check if preview is enabled
			var enablePreview = HTML5Editor.data("preview-enabled");
			if (!enablePreview)
				return;
			
			// Sync content from code to preview
			syncViews.call(HTML5Editor);
			
			// Switch view
			previewPanel.toggleClass("noDisplay");
			htmlCodePanel.toggleClass("noDisplay");
		});
		
		// Get changes when the preview changes
		previewPanel.on("keyup", function() {
			// Sync content from preview to code
			syncViews.call(HTML5Editor);
		});
	
		function syncViews() {
			if (previewPanel.hasClass("noDisplay")) {
				// Sync code from codeEditor to preview
				var CodeMirrorInstance = cmEditor.data("CodeMirrorInstance");
				var htmlCode = CodeMirrorInstance.getDoc().getValue();
				// Check if code changed
				if (lastCodeState != htmlCode) {
					lastCodeState = htmlCode;
					previewPanel.html(htmlCode);
				}
			} else if (htmlCodePanel.hasClass("noDisplay")) {
				// Sync code from preview to codeEditor
				var htmlText = jq("<div>").text(previewPanel.html()).text();
				var CodeMirrorInstance = cmEditor.data("CodeMirrorInstance");
				
				// Check if code changed
				if (lastCodeState != htmlText) {
					// Set last code state
					lastCodeState = htmlText;
					
					// Set code mirror value
					CodeMirrorInstance.getDoc().setValue(htmlText);
				}
				
				// Refresh code mirror
				setTimeout(function() {
					CodeMirrorInstance.refresh();
				}, 10);
			}
		}
		
		function collapseXml(xml) {
			return xml.replace(/[\n\r]+^[\t]*/mg, '');
		}
		
		function expandXml(xml) {
			var formatted = '';
			var reg = /(>)(<)(\/*)/g;
			xml = xml.replace(reg, '$1\n$2$3');
			var pad = 0;
			jq.each(xml.split('\n'), function(index, node) {
				var indent = 0;
				if (node.match( /.+<\/\w[^>]*>$/ )) {
					indent = 0;
				} else if (node.match( /^<\/\w/ )) {
					if (pad != 0) {
						pad -= 1;
					}
				} else if (node.match( /^<\w[^>]*[^\/]>.*$/ )) {
					indent = 1;
				} else {
					indent = 0;
				}
				var padding = '';
				for (var i = 0; i < pad; i++) {
					padding += '\t';
				}
				formatted += padding + node + '\n';
				pad += indent;
			});
			return formatted;
		}
	}
})(jQuery);