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
			// DISABLE FUNCTIONALITY UNTIL BUG ABOUT QUOTES FIXED
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






/*

var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// On content modified, find uninitialized html editors and initialize them
	jq(document).on("content.modified", function() {
		// Get valid html editors
		jq(".htmlEditor").filter(function(){
			return jq.type(jq(this).data("initiated")) == "undefined";
		}).data("initiated", true).each(function(){
			var jqthis = jq(this);
			jqthis.data("type", jqthis.data("type"));
			jqthis.removeAttr("data-type");
			jqthis.htmlEditor().removeClass("noDisplay");
		});
	});
	
	// Initialize first content modified for this document
	jq(document).trigger("content.modified");
});
	
// This wraps the fileExplorer extension
(function(jq){

	// Method Logic for fileExplorer
	var methods = {
		init : function(){
			return this.each(function(){
				htmlEditor.call(this);
			});		
		}
	}
	
	jq.fn.htmlEditor = function() {
		return methods.init.apply(this);
	};
	// ---------------------------
	
	// Initialization of htmlEditor (assign listeners etc...)
	function htmlEditor() {
	
		var htmlEditor = jq(this);
		var toolbar = htmlEditor.children(".htmlToolbars");
		var htmlWrapper = htmlEditor.children(".htmlWrapper");
		var footer = htmlEditor.children(".htmlFooter");
		var pathbar = footer.children(".htmlPath");
		var stylingOptions = htmlEditor.children(".stylingOptionsWrapper");
		var elementCatalog = stylingOptions.find(".elementCatalogue");
		var elementOptions = stylingOptions.find(".elementOptions");
	
		
		toolbar.on("click", ".htmlTool.toggleView", function(ev) {
			syncViews.call(htmlWrapper);
			toggleView.call(htmlWrapper);
		});
		
		// Toggle views in the preview area
		htmlEditor.find(".htmlTool.toggleView").on("click", function(ev){
			// Toggle selected class
			jq(this).toggleClass("activated");
			
			// Switch views
			if (jq(this).hasClass("activated")) {
				// Render code
				// Switch code view
			} else {
				// Render html
				// Switch html view
			}
			
			// If not activted (html view), refresh html
			var jqbar = jq(this).closest(".previewBar");
			var me = jqbar.siblings(".modelEditor").addClass("noDisplay");
			var pc = jqbar.siblings(".previewContainer").removeClass("noDisplay");
			jq(this).addClass("active").siblings(".active").removeClass("active");
			
			var coder = me.data("code-modified");
			me.data("code-modified", "false");
			
			if (coder != "true")
				return;
				
			// Get content from editor
			htmlCode.trigger("getCodeEditorContent", [true, function(content){
				var htmlc = content;
			
				saveTemplateInfo(templateParent.find(".propertiesView").first().not(".defaults").detach());
							
				// Update structure viewer
				structure.html(htmlc);
				structureViewer.trigger("initializeStructure.csseditor");
				
				// Update styles
				cssCode.trigger("getCodeEditorContent", [true, function(c){
					renderPreview(cssEditor, c, initialCollection);
				}]);
			}]);
				
		});
		
		stylingOptions.on("click", function(ev){
			if (jq(ev.target).not(jq(this)).length == 0) {
				jq(this).add(elementOptions).css("display", "");
				htmlWrapper.children(".htmlSection").find(".__highlight, .__select").removeClass("__highlight __select");
			}
		});
		
		toolbar.on("click", ".htmlTool[draggable]", function(ev){
			var text = jq(ev.target).text().toLowerCase();
			var type = "";
			var dataAttr = getDataAttributes(ev.target);
			for (var i = 0, l = dataAttr.length; i < l; i++) {
				type += "["+dataAttr[i]+"]";
			}
			
			var catalogEntry = jq("<div class='catalogEntry'><span class='entryTag'></span><span class='entryType'></span><span class='entryId'></span><span class='entryClass'></span></div>");
			elementCatalog.empty();
			var typeElements = htmlWrapper.children(".htmlSection").find(text+type);
			if (typeElements.length == 0) {
				stylingOptions.css("display", "");
				return;
			}
			
			typeElements.each(function(){
				catalogEntry.clone()
					.children(".entryTag").text(this.tagName.toLowerCase()).end()
					.children(".entryId").text(this.id).end()
					.children(".entryClass").text(this.className).end()
					.children(".entryType").text(type).end()
				.appendTo(elementCatalog);
			});
			
			stylingOptions.css("display", "block");
		});
		
		stylingOptions.on({
			"click": function(){
				var jqthis = jq(this);
				jqthis.addClass("selected").siblings(".selected").removeClass("selected");
				var index = jqthis.index();
				var text = jqthis.children(".entryTag").text();
				var type = jqthis.children(".entryType").text();
				htmlWrapper.children(".htmlSection").find(text+type).removeClass("__select").eq(index).addClass("__select");
				
				elementOptions.find(".idOption > input").val(jqthis.children(".entryId").text());
				var classes = "";
				jqthis.children(".entryClass").each(function(){
					classes += " "+jq(this).text();
				});
				elementOptions.find(".classOption > input").val(jq.trim(classes));
				
				elementOptions.css("display", "inline-block");
			},
			"mouseenter": function(){
				var jqthis = jq(this);
				var text = jqthis.children(".entryTag").text();
				var type = jqthis.children(".entryType").text();
				var index = jqthis.index();
				htmlWrapper.children(".htmlSection").find(text+type).eq(index).addClass("__highlight");
			},
			"mouseleave": function(){
				var jqthis = jq(this);
				var index = jqthis.index();
				var text = jqthis.children(".entryTag").text();
				var type = jqthis.children(".entryType").text();
				htmlWrapper.children(".htmlSection").find(text+type).eq(index).removeClass("__highlight");
			}
		}, ".catalogEntry");
		
		elementOptions.on("focusin", "input", function(){
			jq(this).data("initialValue", jq(this).val());
		});
		elementOptions.on({
			"keydown": function(){
				var jqthis = jq(this);
				setTimeout(function(){
					var i = jq.trim(jqthis.val());
					elementCatalog.find(".catalogEntry.selected > .entryId").text(i);
				}, 50);
			},
			"change": function(){
				var i = jq.trim(jq(this).val());
				var selected = elementCatalog.find(".catalogEntry.selected");
				var index = selected.index();
				var text = jqthis.children(".entryTag").text();
				var type = jqthis.children(".entryType").text();
				
				// If id is empty
				if ("" == i) {
					htmlWrapper.children(".htmlSection").find(text+type).eq(index).removeAttr("id");
					return;
				}
				
				var escapedId = i.replace(/(#|:|\.|\[|\])/g, "\\$1");
				// If id exists or id is not valid
				var validationPattern = /^[^ \t]*[a-zA-Z]+[^ \t]*$/gm;
				if (htmlWrapper.children(".htmlSection").find("#"+escapedId).length != 0
					|| (!validationPattern.test(i))) {
					var oldValue = jq(this).data("initialValue");
					jq(this).val(oldValue);
					selected.children(".entryId").text(oldValue);
					htmlWrapper.children(".htmlSection").find(text+type).eq(index).attr("id", oldValue);
					return;
				}
				
				htmlWrapper.children(".htmlSection").find(text+type).eq(index).attr("id", i);
			}
		}, ".idOption > input");
		elementOptions.on({
			"keydown": function(){
				var jqthis = jq(this);
				setTimeout(function(){
					var c = jq.trim(jqthis.val().replace(/[ \t]+/g," "));
					var cArray = c.split(" ");
					
					var selectedEntry = elementCatalog.find(".catalogEntry.selected");
					var entryClass = selectedEntry.children(".entryClass");
					entryClass = entryClass.slice(1).remove().end().eq(0);
					entryClass.text(cArray[0]);
					for (var i = 1; i < cArray.length; i++)
						entryClass.clone().text(cArray[i]).appendTo(selectedEntry);
				}, 50);
			},
			"change": function(){
				var c = jq.trim(jq(this).val().replace(/[ \t]+/g," "));
				var selected = elementCatalog.find(".catalogEntry.selected");
				var entryClasses = selected.children(".entryClass");
				var index = selected.index();
				var text = jqthis.children(".entryTag").text();
				var type = jqthis.children(".entryType").text();
				
				// If class is empty
				if ("" == c) {
					htmlWrapper.children(".htmlSection").find(text+type).eq(index).removeClass().addClass("__select");
					return;
				}
				
				// Filter invalids out
				var validationPattern = /^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/m;
				var cArray = c.split(" ");
				var validArray = new Array();
				for (var i = cArray.length-1; i >= 0; i--) {
					if (!validationPattern.test(cArray[i])) {
						entryClasses.eq(i).remove();
						continue;
					}
					validArray.unshift(cArray[i]);
				}
				
				var validClasses = validArray.join(" ");
				jq(this).val(validClasses);
				htmlWrapper.children(".htmlSection").find(text+type).eq(index).removeClass().addClass(validClasses+" __select");
			}
		}, ".classOption > input");
		
		toolbar.on("dragstart", ".htmlTool[draggable]", function(ev){
			var txt = jq(ev.target).text().toLowerCase();
			var dataAttr = getDataAttributes(ev.target);
			
			var data = {
				text : txt,
				attrs : dataAttr
			}
			data = JSON.stringify(data);
			
			ev.originalEvent.dataTransfer.setData("Text", data);
			jq(this).closest(".htmlEditor").data("dragData", data);
		});
	
		htmlWrapper.on({
			"dragover" : function(ev) {
				if (jq(this).attr("data-editable") !== undefined)
					return;
				ev.preventDefault();
				ev.stopPropagation();
			}, 
			"dragenter" : function(ev) {
				if (jq(this).attr("data-editable") !== undefined)
					return;
				ev.preventDefault();
				ev.stopPropagation();
				var data = jq.parseJSON(jq(this).closest(".htmlEditor").data("dragData"));
				var element = data.text;
				var jqtarget = jq(ev.target);
				jqtarget.addClass("dragover");
				updatePath.call(pathbar, ev.target, element);
			},
			"dragleave" : function(ev) {
				if (jq(this).attr("data-editable") !== undefined)
					return;
				ev.preventDefault();
				ev.stopPropagation();
				var jqtarget = jq(ev.target);
				jqtarget.removeClass("dragover");
				if (jq.trim(jqtarget.attr("class")) == "")
					jqtarget.removeAttr("class");
			},
			"drop" : function(ev) {
				if (jq(this).attr("data-editable") !== undefined)
					return;
				ev.preventDefault();
				ev.stopPropagation();
				var data = jq.parseJSON(ev.originalEvent.dataTransfer.getData("Text"));
				var type = data.text; 
				var jqtarget = jq(ev.target);
				var htmlSect = htmlWrapper.children(".htmlSection");
				var verticalOffset = ev.originalEvent.offsetY;
				var horizontalOffset = ev.originalEvent.offsetX;
				
				var elem = jqtarget;
				var elemTop = 0;
				var index = 0;
				var contents = jqtarget.contents();
				while (elemTop <= verticalOffset && index < contents.length) {
					elem = contents.eq(index);
					elemTop = elem.position().top + parseInt(elem.css("marginTop"));
					index++;
				}
				
				jqtarget.removeClass("dragover");
				var newElem = jq("<"+type+"></"+type+">");
				for (var i = 0, l = data.attrs.length; i < l; i++) {
					newElem.attr(data.attrs[i], "");
				}
				if (elem.is(jqtarget) || index == contents.length)
					jqtarget.append(newElem);
				else
					elem.before(newElem);
				
				
				
				if (jq.trim(jqtarget.attr("class")) == "")
					jqtarget.removeAttr("class");
				jq(this).closest(".htmlEditor").removeData("dragData");
			}
		}, ".htmlSection, .htmlSection *");
		
		// Bug in chrome that triggers twice or more if focus is moved to another window.
		htmlWrapper.on("focusin.htmleditor", ".htmlSection, .htmlSection *", function(ev) {
			var jqtarget = jq(ev.target);
			var focusControl = jqtarget.data("focusControl");
			//console.log(ev.target);
			//console.log(focusControl);
			if (focusControl)
				return;
			
			restoreActiveParagraph();
			
			jqtarget.data("focusControl", true);
		});
		htmlWrapper.on("focusout.htmleditor", ".htmlSection", function(ev) {
			var jqtarget = jq(ev.target);
			var focusControl = jqtarget.data("focusControl");
			if (!focusControl)
				return;	
				
			//jqtarget.find("p, br").filter(":empty").remove();
	
			jqtarget.data("focusControl", false);
		});
		
		htmlWrapper.on("keydown.htmleditor", ".htmlSection", function(ev) {
			if (isContentAlteringKey(ev.which)) {
				var jqthis = jq(this);
				restoreActiveParagraph();
				setTimeout(function(){
					jqthis.find("br").remove();
				}, 1);
			}
		});
		
		initializeViews.call(htmlWrapper);
	}
	
	function getDataAttributes(element) {
		var data = [];
		for (var i = 0, attrs = element.attributes, l = attrs.length; i<l; i++) {
			var name = attrs.item(i).nodeName;
			if (name.indexOf("data-") == 0)
				data.push(name);
		}
		return data;
	}
	
	function restoreActiveParagraph() {
		setTimeout(function(){
			var range = getSelectionRange();
			var parent = range.commonAncestorContainer;
			var paragraph = jq("<p></p>");
			
			if (parent.tagName != "DIV" && parent.nodeType != 3)
				return;

			if (parent.nodeType == 3) {
				if (parent.parentNode.tagName != "DIV")
					return;
					
				var off = range.startOffset;
				jq(parent).wrap(paragraph);
				
				var tmpRange = document.createRange();
				tmpRange.setStart(parent, off);
				tmpRange.setEnd(parent, off);
				
				var selection = getSelection();
				selection.removeAllRanges();
				selection.addRange(tmpRange);
				return;
			}
			
			var txtNode = document.createTextNode("");
			jq(parent).append(paragraph.append(txtNode));
			var tmpRange = document.createRange();
			tmpRange.selectNodeContents(txtNode);
			var selection = getSelection();
			selection.removeAllRanges();
			selection.addRange(tmpRange);
		}, 1);
	}
	
	// Called on htmlWrapper
	function initializeViews() {
		var htmlWrapper = jq(this);
		var code = htmlWrapper.children(".codeSection");
		var codeEditor = code.find(".codeEditor");
		var html = htmlWrapper.children(".htmlSection");
		
		// Sync Code to HTML
		codeEditor.trigger("getCodeEditorContent", [true, function(c) {
			c = collapseXml(c);
			html.html(c);
			html.contents().not(html.children()).wrap("<p></p>");
		}]);
	}
	
	// Called on htmlWrapper
	function syncViews() {
		var htmlWrapper = jq(this);
		var code = htmlWrapper.children(".codeSection");
		var codeEditor = code.find(".codeEditor");
		var html = htmlWrapper.children(".htmlSection");
		if (html.filter(":hidden").length == 0) {
			// Sync HTML to Code
			var htmlText = jq("<div>").text(html.html()).text();
			htmlText = expandXml(htmlText);
			htmlText = jq("<div />").text(htmlText).html();
			codeEditor.trigger("replaceContent", [htmlText]);
		} else {
			// Sync Code to HTML
			codeEditor.trigger("getCodeEditorContent", [true, function(c) {
				c = collapseXml(c);
				html.html(c);
				html.contents().not(html.children()).wrap("<p></p>");
			}]);				
		}
	}
	
	// Called on htmlWrapper
	function toggleView() {
		var htmlWrapper = jq(this);
		htmlWrapper.children(".codeSection:hidden, .htmlSection:hidden").css("display", "block")
			.siblings(".codeSection, .htmlSection").css("display", "none");
	}
	
	// Called on pathbar
	function updatePath(target, element) {
		var pathbar = jq(this);
		pathbar.empty();
		var htmlSection = jq(target).closest(".htmlSection");
		var parents = jq(target).parentsUntil(".htmlSection, .htmlWrapper");
		parents.each(function(){
			pathbar.append(jq("<div class='pathElement'>"+this.tagName+"</div>"));
		});
		if (jq(target).filter(".htmlSection").length == 0)
			pathbar.append(jq("<div class='pathElement'>"+target.tagName+"</div>"));
		pathbar.append(jq("<div class='pathElement'>"+element+"</div>"));
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
	
	function collapseXml(xml) {
		return xml.replace(/[\n\r]+^[\t]* /mg, '');
	}
	
	// Get the selection in the editor
	function getSelection() {
		var userSelection;
		if (window.getSelection) {
			userSelection = window.getSelection();
		} else if (document.selection) { 
			// should come last; Opera!
			userSelection = document.selection.createRange();
		}
		return userSelection;
	}

	// Store the selection as an array
	function getSelectionRange(){
		var s = getSelection();
		if (s.anchorNode == null)
			return null;
		
		// Pure clone
		//var selection = jQuery.extend(true, {}, s.getRangeAt(0).cloneRange());
		var selection = new Object();
		tmpRange = s.getRangeAt(0).cloneRange();
		selection.collapsed = tmpRange.collapsed;
		selection.commonAncestorContainer = tmpRange.commonAncestorContainer;
		selection.startContainer = tmpRange.startContainer;
		selection.startOffset = tmpRange.startOffset;
		selection.endContainer = tmpRange.endContainer;
		selection.endOffset = tmpRange.endOffset;
		var ofe = selection.endContainer.length - tmpRange.endOffset;
		selection.offsetFromEnd = (isNaN(ofe) ? 0 : ofe);
		return selection;
	}
	
	function isContentAlteringKey(keycode) {
		if ((57>=keycode && keycode>=48) //number
			|| (105>=keycode && keycode>=96) //numpad number
			|| (90>=keycode && keycode>=65) //letter
			|| (keycode == 8) || (keycode == 46) //backspace | delete  
			|| (keycode == 13) || (keycode == 32) //enter
			|| (111>=keycode && keycode>=106) //+-*./
			|| (192>=keycode && keycode>=186) //;=,-.`~
			|| (222>=keycode && keycode>=219) //'\][
			|| (keycode == 9) // tab
			|| (keycode == 226))
				return true;
		return false;
	}
})(jQuery);
*/