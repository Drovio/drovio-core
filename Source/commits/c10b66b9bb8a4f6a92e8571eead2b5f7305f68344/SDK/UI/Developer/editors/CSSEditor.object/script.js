var jq = jQuery.noConflict();
jq(document).one("ready", function(){
	// Load properties
	getCSSProperties();
	
	// Define selector :containsExactly
	jq.expr[":"].containsExactly = function(obj, index, meta, stack){
		return (obj.textContent || obj.innerText || jq(obj).text() || "").toLowerCase() == meta[3].toLowerCase();
	}
	// On content modified, find uninitialized cssEditors and initialize them
	jq(document).on("content.modified.csseditor", function(){
		if (!wasInitiated)
			return;
		
		if (jq(".codeEditor").filter("[data-type]").length != 0)
			return;
			
		jq(".cssEditor").filter(function(){
			return jq.type(jq(this).data("initiated")) == "undefined";
		}).data("initiated", true).find("[data-css-property]").each(function(){
			jq(this).data("css-property", jq(this).attr("data-css-property"));
			jq(this).data("default-value", jq(this).attr("data-default-value"));
		}).removeAttr("data-css-property").removeAttr("data-default-value").end().cssEditor();
		
	});
	
	// Initialize first content modified for this document
	jq(document).trigger("content.modified");
});

// Initialize objects
var pseudoWrapper = jq();
var units = new Object();
var prefixes = new Object();
var equalityOperators = new Array();
var propertiesInfo = new Object();
var wasInitiated = false;
	
// This wraps the cssEditor extension
(function(jq){
	// Method Logic for cssEditor
	var methods = {
		init : function(){			
			return this.each(function(){
				cssEditorInitialize.call(this);
			});		
		}
	}
	
	jq.fn.cssEditor = function() {
		return methods.init.apply(this);
	};
	// ---------------------------
	
	RegExp.escaped = function(str) {
		return (str+'').replace(/([.?*+^$[\]\\() {}|-])/g,  "\\$1");
	};
	
	// Initialization of cssEditor (assign listeners etc...)
	function cssEditorInitialize(){
		// Initialize variables
		var thisPseudoWrapper = pseudoWrapper.clone();
		var cssEditor = jq(this);
		var structureWrapper = cssEditor.find(".structureWrapper");
		var structureViewer = structureWrapper.children('.structureViewer');
		var previewWrapper = cssEditor.find(".previewWrapper");
		var modelEditor = cssEditor.find(".modelEditor");
		cssEditor.data("hasModel", modelEditor.length > 0);
		var cssContainer = cssEditor.find(".cssContainer");		
		// Initialize template
		var template = cssContainer.find("[data-properties-template]").data("properties-template", true).removeAttr("data-properties-template");
		var templateParent = template.parent();
		
		var templateCollection = new Object();
		var oldCollection = new Object();
		var initialCollection = new Object();
		
		var cssCode = cssContainer.find(".codeEditor");
		var htmlCode = modelEditor.find(".codeEditor");
		
		var structure = jq("<div>").html(cssEditor.find("[name='structure']").detach().val());
		var jqpath = structureWrapper.children(".path");
		if (!cssEditor.data("hasModel"))
			jqpath = cssContainer.find(".currentSelectorPath");
			
		template.detach();
		
		// Get info from xml
		//
		// -------------------
		
		// Initialize preview, in case there is already some css loaded
		cssEditor.on("initialize.csseditor", function(ev, cssCoder){
		// This needs timeout to let the code script load... we need to do something for this...
			// Initialize pool and preview if needed
			jq(cssCoder).trigger("getCodeEditorContent", [true, function(content){
				templateCollection = parseCssCode(content);
				initialCollection = jq.extend(true, {}, templateCollection);
				renderPreview(cssEditor, content, initialCollection);
			}]);
		});
		
		// Initialize Preview
		cssEditor.trigger("initialize.csseditor", cssCode.get(0));
		
		// Respective editor's content has changed
		cssContainer.on("text.modified.csseditor", function(){
			jq(this).data("code-modified", "true");
		});
		cssEditor.on("text.modified.csseditor", ".modelEditor", function(){
			jq(this).data("code-modified", "true");
		});
		var t1 = null;
		cssContainer.on("keydown.csseditor, focusin.csseditor", ".currentSelectorPath", function(ev) {
			var jqthis = jq(this);
			var txt = jq.trim(jqthis.text());
			
			if (ev.type == "keydown" && (!isContentAlteringKey(ev.which)))
				return;
			if (ev.type == "focusin" && txt == "")
				return;
			
			
			clearTimeout(t1);
			
			t1 = setTimeout(function(){
				txt = jq.trim(jqthis.text());
				if (txt == "")
					return;
					
				cssEditor.trigger("path.changed.csseditor");
				
				var selector = jq.trim(txt.replace(/[ ]{2,}/g," ").replace(/[ ]*([>+~])[ ]*/g, " $1 "));
				var pattern = new RegExp("^[\\t ]*"+RegExp.escaped(selector)+"[\\t \\r\\n]*\\{", "m");
				cssCode.trigger("scrollContent", pattern);
			}, 40);
		});
		
		cssCode.on("focusGained.codeEditor", function(){
			if (cssEditor.data("hasModel"))
				return;
			
			var tmplActive = templateParent.find(".propertiesView").detach();
			saveTemplateInfo(tmplActive.not(".defaults"));
		})
		.on("focusLost.codeEditor", function(){
			if (cssEditor.data("hasModel"))
				return;
				
			var coder = cssContainer.filter(function(){
				return jq(this).data("code-modified") == "true";
			}).find(cssCode);
			cssContainer.data("code-modified", "false");
			
			if (coder.length == 0)
				return;
			
			// Parse css
			coder.trigger("getCodeEditorContent", [true, function(content){
				templateCollection = parseCssCode(content);
			}]);
		});
		
		// Path has been changed
		cssEditor.on("path.changed.csseditor", function(ev){
			var elementpath = jqpath.text();
			elementpath = elementpath.replace(/[ ]{2,}/g," ").replace(/[ ]*([>+~])[ ]*/g, " $1 ");
			elementpath = jq.trim(elementpath);
			
			//console.log(elementpath);
			jqpath.data("atomic-path", elementpath);
			
			var path = "";
			jqpath.siblings(".pathSibling").filter(function(){
				return jq(this).data("atomic-path") != jqpath.data("atomic-path");
			}).add(jqpath).each(function(){
				var p = jq(this).data("atomic-path");
				p = p.replace(/[ ]{2,}/g," ");
				p = jq.trim(p);
			
				path += p;
			});
			path = path.replace(/[ ]*\,[ ]*/g,", ");
			
			//console.log(path);
			jqpath.data("path-value", path);
			
			var tmpl = templateParent.find(".propertiesView").not(".defaults").detach();
			saveTemplateInfo(tmpl);
			
			if (path == ""){
				templateParent.empty();
				return;
			}
			
			var t = loadTemplateInfo(jq.trim(path));
			templateParent.html(t);
		});
		
		// on change, focusout, reset??
		cssContainer.on("focusout.csseditor", ".propertiesView input, .propertiesView select", function(){
			jq(this).closest(".propertyWrapper").removeClass("focused");
		});
		
		cssContainer.on("focusin.csseditor", ".propertiesView input, .propertiesView select", function(){
			jq(this).closest(".propertyWrapper").addClass("focused");
			jq(this).data("previousCSSValue", jq(this).val());
		});
		// Selection Input
		cssContainer.on("change.csseditor", ".propertiesView .selectionInput > select", function(){
			jq(this).siblings("input").val(jq(this).val()).change();
		});
		cssContainer.on("change.csseditor", ".propertiesView .selectionInput > input", function(){
			jq(this).siblings("select").val("");
		});
		// Selection Input
		cssContainer.on("change.csseditor", ".propertiesView .timeInput > select, .propertiesView .lengthInput > select", function(){
			jq(this).siblings("input").change();
		});
		// Update Criterion
		cssContainer.on("change.csseditor", ".propertiesView .propertyWrapper input, .propertiesView .propertyWrapper > select", function(){	
			var val = jq.trim(jq(this).val());
			if (/*val != jq.trim(jq(this).data("default-value")) 
				&& */val != "")
				jq(this).removeClass("default");
			else
				jq(this).addClass("default");
	
			var pv = jq(this).closest(".propertiesView");
			var notDefaults = pv.find("input, select").not(".default").filter(function(){
				return jq.type(jq(this).data("css-property")) != "undefined";
			});
			
			if (notDefaults.length == 0)
				pv.addClass("defaults");
			else
				pv.removeClass("defaults");
				
			if (val != jq(this).data("previousCSSValue"))
				cssEditor.trigger("update.styles.csseditor", [true, function(){
					cssCode.trigger("getCodeEditorContent", [true, function(c){
						renderPreview(cssEditor, c, initialCollection);
					}]);
				}]);
		});
	
		// Update the css styles
		cssEditor.on("update.styles.csseditor", function(ev, updateEverything, callback){
			var txt = "";
			var holderObj = new Object();
			
			var tmpl = templateParent.find(".propertiesView");
			var selector = tmpl.data("selector-path");
			saveTemplateInfo(tmpl);
			
			if (updateEverything === true)
				holderObj = templateCollection
			else 
				holderObj[selector] = templateCollection[selector];
			
				
			for (var selector in holderObj) {
				txt += selector+" {\n";
				
				for (var prop in holderObj[selector]) {
					var value = holderObj[selector][prop]['value'];
					txt += "\t"+prop+":"+value+";\n";
					
					var support = holderObj[selector][prop]['support'];
					
					for (var browser in support)
						if (support[browser] == "true")
							txt += "\t"+prefixes[browser]+prop+":"+value+";\n";
				}
				
				txt += "}\n";
				
				var pattern = "^[\\t ]*"+RegExp.escaped(selector)+"[\\t \\r\\n]*\\{[^\\}]*?\\}[\\t ]*[\\n]{0,2}$";				
				var triggerChange = ( cssContainer.data("code-modified") == "true" ? true : false );
				
				if (updateEverything !== true)
					cssCode.trigger("replaceContent", [txt, pattern, "gm", true, true]);		
			}
			if (updateEverything === true)
				cssCode.trigger("replaceContent", [txt]);	
			
			if (jq.type(callback) == "function")
				callback.call(this);
		})
		
		function saveTemplateInfo(tmpl) {
			var blockCollection = new Object();
			var selector = tmpl.data("selector-path");
			if (jq.type(selector) == "undefined")
				return;
			
			if (jq.type(tmpl) == "undefined"
				|| jq.type(tmpl) == "null" 
				|| tmpl.hasClass("defaults")) {
					delete templateCollection[selector];
					return;
			}
			
			//blockCollection['__selector'] = selector;
			tmpl.find("input, select").not(".default").filter(function(){
				return jq.type(jq(this).data("css-property")) != "undefined";
			}).each(function(){
				var prop = jq.trim(jq(this).data("css-property"));
				blockCollection[prop] = jq.extend(true, {}, propertiesInfo[prop]);
				blockCollection[prop]['value'] = jq.trim(jq(this).val());
				templateCollection[selector] = blockCollection;
			});
		}
		
		function loadTemplateInfo(selector) {
			var tmpl = template.clone(true, true);
			tmpl.data("selector-path", selector);
			
			if (jq.type(templateCollection[selector]) == "undefined")
				return tmpl;
			
			var candidates = tmpl.find("input, select").filter(function(){
				return jq.type(jq(this).data("css-property")) != "undefined";
			});
			
			var blockCollection = jq.extend(true, {}, templateCollection[selector]);
			for (var prop in blockCollection) {
				var val = blockCollection[prop]['value'];
				var un = "";
				
				var elem = candidates.filter(function() {
					return jq(this).data("css-property") == prop.replace(/^\-[^-]*\-/, "");
				});
				
				var pairElem = elem.filter(function(){
					return jq(this).parent(".lengthInput, .timeInput").length != 0;
				});
				
				pairElem.each(function(){
					var type = jq(this).data("type");
					var regex = "("+units[type].join("|")+")$";
				
					val = val.replace(new RegExp(regex), function(m, u, a, b){
						un = u;
						return "";
					});
				});
			
				elem.removeClass("default").val(val);
				pairElem.siblings("select").val(un);
			}
			
			return tmpl.removeClass("defaults");
		}
		
		// Search / Store css value for a tag
		cssEditor.on("keyup.csseditor", ".cssContainer .cssCurrentSelector", function(ev){
			
		});
		
		
		structureWrapper.append(thisPseudoWrapper);
			
		// Initialize structure (here the structure could be stored in the model editor in php, which is better)
		structureViewer.on("initializeStructure.csseditor", function(ev){
			// Convert structure to viewable tree
			var jqstructure = jq(structure.clone());
			jqstructure.find("*").each(function(){
				var jqthis = jq(this);
				var wrapper = jq("<div></div>");
				var tagWrap = jq("<div class='elementTag' />").text(this.nodeName.toLowerCase());
				var classWrap = jq("<div class='elementClass' />");
				var idWrap = jq("<div class='elementId' />");
				var attrWrap = jq("<div class='elementAttr' />");
				/*var attrValueWrap = jq("<div class='elementAttrValue' />");*/
			        
				var attrList = new Object();
				for (var attr, i = 0, attrs = this.attributes; i < attrs.length; i++){
				    attr = attrs.item(i);
				    attrList[attr.nodeName] = attr.value;
				}
				// We'll handle id and class later, so remove those from the list
				delete attrList['id'];
				delete attrList['class'];
			
				for (var attr in attrList) {
					wrapper.prepend("'");
					// For each value
					/*var attrs = attrList[attr].match(/[^ ]+/g);
			        	for(var a in attrs) {
			        		jqthis.prepend(attrValueWrap.clone().text(attrs[a]));
				                if (a != attrs.length - 1) {
				                    jqthis.prepend(" ");
				                }
			        	}*/
					// For attribute
					wrapper.prepend(attrList[attr]);
					wrapper.prepend("='");
					wrapper.prepend(attrWrap.clone().text(attr));
					wrapper.prepend(" ")
				}
			
				if (typeof(jqthis.attr("class")) != "undefined") {
					wrapper.prepend("'");
					var classes = jqthis.attr("class").match(/[^ ]+/g);
			        	for( var c in classes ){
			        		wrapper.prepend(classWrap.clone().text(classes[c]));
				                if(c!= classes.length - 1){
				                    wrapper.prepend(" ");
				                }
			        	}
					wrapper.prepend("='");
					wrapper.prepend(attrWrap.clone().text("class"));
					wrapper.prepend(" ");
			        }
			
				if (typeof(jqthis.attr("id")) != "undefined") {
					wrapper.prepend("'");
					var id = jqthis.attr("id");
		        		wrapper.prepend(idWrap.clone().text(id));
					wrapper.prepend("='");
					wrapper.prepend(attrWrap.clone().text("id"));
					wrapper.prepend(" ");
			        }
			
			        wrapper.prepend(tagWrap.clone());
			        wrapper.prepend("<");
			
				// Append closing tag
				var contents = jqthis.contents();
				if (jqthis.contents().length != 0) {
					wrapper.append(">");
					wrapper.append(contents);
					wrapper.append("</");
				        wrapper.append(this.nodeName.toLowerCase()/*tag.clone()*/);
				        wrapper.append(">");
				} else {
					wrapper.append(" />");
				}
				
			        jqthis.removeAttr("class");
				jqthis.replaceWith(wrapper);
			});
			jqstructure.children().data("structure-root", "true");
			
			// Reveal structure tree
			jq(this).html(jqstructure.html());
			
			jq(".previewWrapper", cssEditor).html(structure.clone().children());
		});
		
		structureViewer.trigger("initializeStructure.csseditor");
		
		// Toggle views in CSS structure, while parsing new css from the editor, if any
		cssEditor.on("click.csseditor", ".cssContainer .cssTool.toggleCoder", function(ev){
			cssContainer.children(".cssCoder").filter(".selected").removeClass("selected")
								.siblings(".cssCoder").addClass("selected");
			var txt = jq(this).text();
			var toggleName = jq(this).data("toggle-name");
			jq(this).text(toggleName);
			jq(this).data("toggle-name", txt);
			
			var selector = templateParent.find(".propertiesView").first().data("selector-path");
			if (toggleName == "Form" && jq.type(selector) != "undefined") {
				var pattern = new RegExp("^[\\t ]*"+RegExp.escaped(selector)+"[\\t \\r\\n]*\\{", "m");
				cssCode.trigger("scrollContent", pattern);
			}
			
			var coder = cssContainer.filter(function(){
				return jq(this).data("code-modified") == "true";
			}).find(cssCode);
			cssContainer.data("code-modified", "false");
			
			if (coder.length != 0) {
				saveTemplateInfo(templateParent.find(".propertiesView").not(".defaults").detach());
				
				// Clear selection structure
				clearStructureSelection.apply(structureViewer);
				oldCollection = templateCollection;
				// Parse css
				coder.trigger("getCodeEditorContent", [true, function(content){
					templateCollection = parseCssCode(content);
					// Initialize preview
					renderPreview(cssEditor, content, initialCollection);
					cssContainer.children().not(".cssCoder").find(".cssTool.restore").removeClass("noDisplay");
					
					if (jq.type(selector) != "undefined")
						templateParent.html(loadTemplateInfo(selector));
				}]);
			}
		});
		
		// Restore previous collection
		cssEditor.on("click.csseditor", ".cssContainer .cssTool.restore", function(){
			/*templateCollection.append(templateParent.find(".propertiesView").not(".defaults").detach());
			clearStructureSelection.apply(structureViewer);
			
			var tmp = templateCollection;
			templateCollection = oldCollection;
			oldCollection = tmp;
			
			cssEditor.trigger("update.styles.csseditor", true);
			//renderPreview(previewWrapper, templateCollection.clone(true, true));
			
			var txt = jq(this).text();
			var toggleName = jq(this).data("toggle-name");
			jq(this).text(toggleName);
			jq(this).data("toggle-name", txt);*/
		});
		/*
		// Call appropriate function from the select in preview
		cssEditor.on("change.csseditor", ".previewBar .selectBrowser > select", function() {
			try {
				browserStyles[jq(this).val()].call(this, jq(this).val());
			} catch (err) {
			}
		});*/
		
		// Hide / Reveal pseudowrapper when someone clicks a path element
		jq(document).off("click.csseditor.pseudowrapper");
		jq(document).on("click.csseditor.pseudowrapper", function(ev){
			if (!jq(ev.target).is(jq(".cssEditor .structureWrapper > .path > .element")))
				jq(".pseudoWrapper").css("display", "");
		});
		cssEditor.on("click.csseditor", ".structureWrapper > .path > .element", function(){
			thisPseudoWrapper.css("top", jq(this).position().top + jq(this).height()).css("left", jq(this).position().left).css("display", "block").data("senderElement", jq(this));
		});
		
		// Edit element
		cssEditor.on("click.csseditor", ".structureWrapper > .path > .element > .edit", function(ev){
			var parent = jq(this).parent();
			var equality = "<span title='Cycle through equality operators' class='equality'>=</span>";
			var editableArea = "<span title='Edit' class='editableArea' contentEditable='true'>(name)</span>";
			jq(this).replaceWith(equality+"'"+editableArea+"'");
			
			parent.find(".equality").data(equalityOperators);
			
			var range = document.createRange();
			range.selectNodeContents(parent.find(".editableArea").get(0));
			
			var userSelection = getUserSelection();
			userSelection.removeAllRanges();
			userSelection.addRange(range);
		});
		
		// Click on an equality operator in the path
		cssEditor.on("click.csseditor", ".structureWrapper > .path .equality", function() {
			var val = jq(this).text();
			var newval = equalityOperators[(equalityOperators.indexOf(val)+1)%equalityOperators.length];
			jq(this).text(newval);
			cssEditor.trigger("path.changed.csseditor");
		});
		
		// Click on a pseudoclass / pseudoelement in the preudowrapper list
		cssEditor.on("click.csseditor", ".structureWrapper > .pseudoWrapper .pseudoClass, .structureWrapper > .pseudoWrapper .pseudoElement", function(){
			var span = jq("<span title='Remove' class='"+jq(this).attr("class")+"'>"+jq(this).text()+"</span>");
			jq(this).closest(".pseudoWrapper").data("senderElement").after(span);
			if (span.hasClass("configurable")){
				var content = span.html();
				content = content.replace(/\((.*)\)/, function(a, b){
					return "(<span title='Edit' class='editableArea' contentEditable='true'>"+b+"</span>)";
				});
				span.html(content);
				
				var range = document.createRange();
				range.selectNodeContents(span.children(".editableArea").first().get(0));
				/*range.setStart(span.children(".editableArea").first().contents().get(0), 1);
				range.setEnd(span.children(".editableArea").first().contents().get(0), span.children(".editableArea").first().text().length - 1);
	*/
				var userSelection = getUserSelection();
				userSelection.removeAllRanges();
				userSelection.addRange(range);
			}
	
			cssEditor.trigger("path.changed.csseditor");
		});
		
		// Click on a pseudoelement in the path
		cssEditor.on("click.csseditor", ".structureWrapper > .path > .pseudoElement, .structureWrapper > .path > .pseudoClass, .structureWrapper > .path > .relation", function(ev){
			jq(ev.target).filter(".pseudoElement, .pseudoClass, .relation").remove().each(function(){
				cssEditor.trigger("path.changed.csseditor");
			});					
		});
		
		// focus out of a pseudoelement's editable area in the path
		cssEditor.on("focusout.csseditor", ".structureWrapper > .path .editableArea", function(ev){
			cssEditor.trigger("path.changed.csseditor");
		});
		
		// Hover above a pseudoelement's editable area in the path
		cssEditor.on("mouseenter.csseditor", ".structureWrapper > .path > .pseudoClass > .editableArea",function(){
			jq(this).parent(".pseudoClass, .pseudoElement").css("background-color","transparent");
		})
		.on("mouseleave.csseditor", ".structureWrapper > .path > .pseudoClass > .editableArea", function(){
			jq(this).parent(".pseudoClass, .pseudoElement").css("background-color","");
		});
		
		// Handle hover class in structure viewer
		cssEditor.on('mouseenter.cssEditor', '.structureWrapper > .structureViewer *:not([class="elementClass"], [class="elementTag"], [class="elementId"], [class="elementAttr"])', function(ev){
			structureViewer.find('*').removeClass("hover");
			jq(this).addClass("hover");
		});
		cssEditor.on('mouseleave.cssEditor', '.structureWrapper > .structureViewer *', function(ev){
		        jq(this).removeClass("hover");
			jq(ev.relatedTarget).closest('.cssEditor .structureWrapper > .structureViewer *:not(.elementClass, .elementTag, .elementId, .elementAttr)').addClass("hover");
		});
	    	
		// Click a clickable element in the viewer
	    	cssEditor.on("click.csseditor", ".structureWrapper > .structureViewer .elementTag, .structureWrapper > .structureViewer .elementClass, .structureWrapper > .structureViewer .elementId, .structureWrapper > .structureViewer .elementAttr", function(){
	        	var jqthis = jq(this);
			structureViewer.addClass("activePath");
			jqthis.toggleClass("active");
			var pivot = jq();
	        	if (jqthis.siblings(".active").length == 0 & jqthis.parent().hasClass("activePath")){
				if (jqthis.not(".active").parent().siblings(".activePath").has(".active").length > 0)
					jqthis.not(".active").parent().find(".active").removeClass("active");
				pivot = structureViewer.find(".active").last().parent();
			}else{
				pivot = jqthis.parent().parent().children().children(".active").parent().last();
				pivot = pivot.find(".active").last().parent();
			}
			preparePath(pivot);
	
	        	setPath.call(structureWrapper);
			cssEditor.trigger("path.changed.csseditor");
			
			setMirror.call(structureViewer, jqpath.data("path-value"));
			
			structureViewer.removeClass("activePath");
	    	});
	
		// Temp... reset selection
		cssEditor.on("click.csseditor", ".structureWrapper > .structureViewer", function(ev){
			if (ev.ctrlKey)
				clearStructureSelection.apply(structureViewer);
		});
		/*
		cssEditor.on("click.csseditor", ".cssContainer #cssEditor_formView", function(ev){
			if (!ev.ctrlKey)
				return;
				
			//resetActiveTemplate();
			templateParent.find(".propertiesView").not(".defaults").find("input, select").not(".dafault").filter(function(){
				return (jq.type(jq(this).data("css-property")) != "undefined");
			}).val("").trigger("change");
				
		});*/
		
	
		// Functionality for adding multiple path selectors
		cssEditor.on("mouseenter.csseditor", ".structureWrapper > .path", function() {
			jq("<div title='Add multiple selectors' class='plus' />").appendTo(jq(this));
		});
		cssEditor.on("mouseleave.csseditor", ".structureWrapper > .path", function(){
			jq(this).children(".plus").remove();
		});
		cssEditor.on("click.csseditor", ".structureWrapper > .path > .plus", function(){
			var exists = jqpath.siblings(".pathSibling").filter(function(){
				return jq(this).data("atomic-path") == jqpath.data("atomic-path");
			}).length > 0;
			if (!exists) {
				jqpath.clone(true, true).attr("title", "Remove").removeClass("path").addClass("pathSibling").children(".plus").replaceWith(", ").end().find("[title]").removeAttr("title").end().insertBefore(jqpath);
				//jq("<span class='specificity'>"+calculateSpecificity(jqpath)+"</span>").insertBefore(jqpath);
			}
			cssEditor.trigger("path.changed.csseditor");
		});
		cssEditor.on("click.csseditor", ".structureWrapper > .pathSibling", function(){
			jq(this).remove();
			cssEditor.trigger("path.changed.csseditor");
		});
	
		// Toggle views in the preview area
		cssEditor.on("click.csseditor", ".previewBar .showHTMLPage", function(ev){
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
		
		// Toggle views in the preview area
		cssEditor.on("click.csseditor", ".previewBar .showHTMLEditor", function(ev){
			var jqbar = jq(this).closest(".previewBar");
			var me = jqbar.siblings(".modelEditor").removeClass("noDisplay");
			var pc = jqbar.siblings(".previewContainer").addClass("noDisplay");
			jq(this).addClass("active").siblings(".active").removeClass("active");
		});
		
		// Search / Store css value for a tag
		cssEditor.on("keyup.csseditor", ".cssContainer .searchCss", function(ev){
			var val = jq(this).val();
			
			if (jq.trim(val) == ""){
				jq(this).css("backgroundColor", "");
				return;
			}
			try {
				val = val.split(":");
				var property = val[0];
				var value = val[1].split(";")[0];
				var elem = templateParent.find(".propertiesView").find("input, select").filter(function(){
					var pr = jq(this).data("css-property");
					return (jq.type(pr) != "undefined" && pr == property );
				});
				jq(this).css("backgroundColor", "rgba("+255*(1-elem.length)+", "+255*elem.length+", "+0+", 0.4)");
				
				if (elem.length == 1 && ev.which == 13){
					var type = elem.data("type");
					if (type in units) {
						var regex = "("+units[type].join("|")+")$";
					 	value = value.replace(new RegExp(regex), function(m, u, a, b){
							elem.siblings("select").val(u);
							return "";
						});
					}
					
					elem.val(value).change();
					jq(this).val("");
					jq(this).css("backgroundColor", "");
				}
			}catch(err){
			}
		});
		/*
		// Splits for the preview
jq(document).on("click.csseditor", ".cssEditor .viewOptions .horizontalSplit, .cssEditor .viewOptions .verticalSplit", function(){
			var jqsplit = jq(this);
			//var splits = jqsplit.closest(".cssEditor").data("splits");
			
			var dimension = "width";
			if (jqsplit.hasClass("verticalSplit"))
				dimension = "height";
			
			var container = jqsplit.closest(".previewContainer");
			jqsplit.detach();
			var clone1 = container.clone(true, true);
								
			// Adjust dimension
			container.css(dimension, "50%");
			clone1.css(dimension, "50%");
			
			//jqsplit.closest(".cssEditor").data("splits", ++splits);
			container.after(clone1);
		});
		*/
	}
	
	// Calculate path element's css specificity
	function calculateSpecificity(pathObject) {
		var specificity = [0, 0, 0, 0];
		// specificity[0] is for inline styles, which are not supported atm...
		specificity[1] = pathObject.find("[data-css-role='id']").length;
		specificity[2] = pathObject.find("[data-css-role='class'], [data-css-role='attribute'], .pseudoClass").length;
		specificity[3] = pathObject.find("[data-css-role='element'], .pseudoElement").length;

		return specificity.join();
	}
	
	// Prepare Active path
	function preparePath(pivotElement){
		if (pivotElement.length != 1)
			return;
		
		var structureViewer = pivotElement.closest(".structureViewer");
		var pathElements = jq();

		do {
			pathElements = pathElements.add(pivotElement);

			var idx = pivotElement.index();
			var sidx = pivotElement.prevAll().has(".active").last().index();
			var olderSiblings = jq();
			if (sidx != -1)
				olderSiblings = pivotElement.siblings().slice(sidx, idx);

			if (olderSiblings.length > 0){
				pathElements = pathElements.add(olderSiblings);
				pivotElement = olderSiblings.first();
			}else{
				var closestActiveParent = pivotElement.parents(".activePath").children(".active").first().parent();
				if (closestActiveParent.length == 0)
					closestActiveParent = pivotElement.parents(".activePath").last();
				var parentElements = pivotElement.parentsUntil(closestActiveParent);
				pathElements = pathElements.add(parentElements);
				pivotElement = closestActiveParent;
			}
				
		} while (!pivotElement.is(structureViewer));

		structureViewer.find(".activePath").removeClass("activePath");
		pathElements.addClass("activePath");
		structureViewer.find(":not(.activePath) > .active").removeClass("active");
	}

	// Set path
        function setPath(){
		structureWrapper = jq(this);
		var path = structureWrapper.children('.path');
		path.empty();
        	structureWrapper.children(".structureViewer").find(".activePath").each(function(){
	                var children = jq(this).children(".active");
	                var parentChildren = jq(this).parent().children(".active");
					var prevSibling = children.parent().prev().children(".active");
					var idx = children.parent().index();
					var olderSibling = children.parent().siblings().slice(2, idx).children(".active");
	
					if(prevSibling.length > 0){
						path.append("<span class='relation'> + </span>");
					}else if(olderSibling.length > 0){
						path.append("<span class='relation'> ~ </span>");
					}else if(parentChildren.length > 0 & children.length > 0){
						path.append("<span class='relation'> > </span>");
					}
	
					if(children.length > 0){
						var tmp = "";
						children.each(function(){
							if (jq(this).hasClass('elementClass')) {
								tmp += "<span class='element' data-css-role='class'>."+jq(this).text()+"</span>";
							} else if (jq(this).hasClass('elementId')) {
								tmp += "<span class='element' data-css-role='id'>#"+jq(this).text()+"</span>";
							} else if (jq(this).hasClass('elementAttr')) {
								tmp += "<span class='element' data-css-role='attribute'>["+jq(this).text()+"<span title='Add equality rule' class='edit'></span>]</span>";
							} else {
								tmp += "<span class='element' data-css-role='element'>"+jq(this).text()+"</span>";
							}
						});
						path.append(tmp+" ");
					}
		});
        }

	// Set mirror elements (this needs rechecking... might not work properly as is...)
        function setMirror(vPath){
		/*structureViewer = jq(this);
		structureViewer.find(".mirror").removeClass("mirror");
		if (jq.trim(vPath) == "")
			return;

		vPath = vPath+" ";
		vPath = vPath.replace(/[\w]+/g, function(a){
				return "elementTag:containsExactly("+a+")";
			});

		vPath = vPath.replace(/\.elementTag/g, ".elementClass");
		vPath = vPath.replace(/elementTag/g, ".elementTag");

		vPath = vPath.replace(/[^ \+\~\>]+ (?=[\+\~])/g, function(a){
				return "div:has("+jq.trim(a)+") ";
			});
		vPath = vPath.replace(/[^ \+\~\>]+ (?![\+\~\>])/g, function(a){
				return "div "+a;
			});
		vPath = vPath.replace(/[^ \+\~\>]+ (?=[\>])/g, function(a){
				return "div > "+a;
			});
		vPath = vPath.replace(/\) \>/g, ") ~");
		vPath = vPath.replace(/\) d/g, ") ~ d");

		//vPath = vPath.replace(/\+/g, "+ div >");
		//vPath = vPath.replace(/~/g, "~ div >");
		//vPath = vPath.replace(/\&tilda\;/g, "~");
		vPath = vPath.replace(/[ ]*~[ ]*div[ ]*$/, "");
		//vPath = "div > "+vPath;
		structureViewer.find(vPath).not(".active").addClass("mirror");*/
	}
	
	// Clear structure selection
	function clearStructureSelection() {
		jq(this).find(".active").trigger("click");
	}
	
	// Update css styles in preview 
	/*function renderPreview(previews, collection) {
		var jqpreview = previews;
		
		collection.find(".propertiesView").each(function(){
			var notDefaults = jq(this).find("input, select").not(".default").filter(function(){
				return jq.type(jq(this).data("css-property")) != "undefined";
			});
			
			var elem = jq(jq(this).data("selector-path"), jqpreview);
			notDefaults.each(function(){
				elem.css(jq(this).data("css-property"), jq(this).val());
			});
		});
	}*/
	
	function renderPreview(cssEditor, code, initialCollection) {
		if (!cssEditor.data("hasModel"))
			return;
	
		// Get style identifier
		var cesi = cssEditor.attr("data-cesi");
		
		// Or assign new style identifier
		if (jq.trim(cesi) == "") {
			// Assign identifier
			var r = Math.round(Math.random()*10000);
			cesi = "ce_"+r;
			cssEditor.attr("data-cesi", cesi);
		}
		
		// Get all cssEditor styles from head
		var styles = jq(document.head).find("style").filter(function(){
			return jq(this).data("origin", "cssEditor");
		});
		
		// Remove styles that are not used anymore
		styles.filter(function(){
			var editor = jq(".cssEditor[data-cesi='"+jq(this).data("cesi")+"']", document.body);
			return editor.length == 0;
		}).remove();
		
		// Create current style content
		// ___ Overlap default values
		var restoreText = "/* Initial Defaults */\n";
		for (var selector in initialCollection) {
			var proper = selector.replace(/[^,]+/gm, function(mat){
				return " .cssEditor[data-cesi='"+cesi+"'] .previewContainer > .previewWrapper "+mat;
			});
			proper = jq.trim(proper.replace(/[ \t]+/g, " "));
			restoreText += proper+" {\n";
			for (prop in initialCollection[selector]) {
				var def = initialCollection[selector][prop]['default'];
				if (jq.type(def) != "undefined")
					restoreText += "\t"+prop+":"+def+";\n";
			}
			restoreText += "}\n";
		}
		restoreText += "/* Current Values */\n";
		
		// ___ Tweak code
		code = code.replace(/^.*[\n\r]*(?={)/gm, function(ma, o, s){
			var proper = ma.replace(/[^,]+/gm, function(mat){
				return " .cssEditor[data-cesi='"+cesi+"'] .previewContainer > .previewWrapper "+mat;
			});
			proper = jq.trim(proper.replace(/[ \t]+/g, " "));
			return proper+" ";
		});
		code = restoreText+code;
		
		// Get current style
		var styleElement = styles.filter(function(){
			return jq(this).data("cesi") == cesi;
		});
		if (styleElement.length != 0) {
			// Update style
			styleElement.empty().text(code);
			return;
		}
		
		// Create style
		styleElement = jq("<style type='text/css'></style>").data("origin", "cssEditor").data("cesi", cesi).text(code);
		jq(document.head).append(styleElement);
	}
	
	// Parse css code and return a template collection (template elements wrapped in a div)
	function parseCssCode(code) {
		var collection = new Object();
		
		var cssBlocks = code.match(/^([^\{\/]*)\{([^\}]*)\}/gm);
		for (var i in cssBlocks) {
			var selector = jq.trim(cssBlocks[i].match(/^.*[\n\r]*(?={)/m)[0]);
			
			// Selector for template
			blockCollection = new Object();
			//blockCollection['__selector'] = selector;
			cssBlocks[i].replace(/^([^\{\/]*)\{([^\}]*)\}/gm, function(match, p1, p2, offset, str){
				p2.replace(/([^\:\/]*)\:([^\;\/]*)\;/g, function(m, name, value, o, s){
					blockCollection[jq.trim(name)] = jq.extend(true, {}, propertiesInfo[jq.trim(name)]);
					blockCollection[jq.trim(name)]['value'] = jq.trim(value);
				});
			});
			
			if (jq.type(collection[selector]) == "undefined")
				collection[selector] = blockCollection
			else
				collection[selector] = jq.extend(true, collection[selector], blockCollection);
		}

		return collection;
	}
	
	// Browser Styles ->
	// Active browser view
	function restorePreviewStyles(){
		jq(document.head).children("link[rel='stylesheet']").filter(function(){
			return jq(this).data("origin") == "cssEditor";
		}).remove();
		jq(this).closest(".viewOptions").siblings(".previewWrapper").removeAttr("user-agent");
	}
	// Selected browser view
	function setPreviewStyles(browser) {
		restorePreviewStyles();
		
		var element;
		if (document.createStyleSheet){
			element = document.createStyleSheet('/ajax/resources/sdk/cssEditor/userAgentDefaults.php?userAgent='+browser, 0);
		}else{
			element = jq("<link rel='stylesheet' href='/ajax/resources/sdk/cssEditor/userAgentDefaults.php?userAgent="+browser+"' />").get(0);
			jq(document.head).children("link[rel='stylesheet'], style").first().before(element);
		}
		jq(this).closest(".viewOptions").siblings(".previewWrapper").attr("user-agent", browser);
		jq(element).data("origin", "cssEditor");
	}
	
	// Get the selection of a user in the path's pseudoelements editable field
	function getUserSelection() {
		var userSelection;
		if (window.getSelection) {
			userSelection = window.getSelection();
		}
		else if (document.selection) { // should come last; Opera!
			userSelection = document.selection.createRange();
		}
		return userSelection;
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

// Get css properties with ajax (temp solution)
function getCSSProperties() {
	ascop.asyncRequest(
		"/ajax/resources/sdk/cssEditor/properties.php", 
		"GET",
		null,
		"xml",
		this,
		handlePropertiesInfo,
		null,
		true,
		true
	);	
}

function handlePropertiesInfo(xml) {
	var jqxml = jq(xml);
	
	var pseudoClasses = jqxml.find("css > pseudoclasses > pseudoclass");
	var pseudoElements = jqxml.find("css > pseudoelements > pseudoelement");
	
	// Preudowrapper element that holds availiable pseudoclasses and pseudoelements
	pseudoWrapper = jq('<div class="pseudoWrapper"><div class="pseudo-classes" /><div class="pseudo-elements" /></div>');
	pseudoClasses.each(function(){
		jq("<div class='pseudoClass"+(jq(this).attr("configurable")=="true"? " configurable" :"")+"' />").text(jq(this).attr("value")).appendTo(pseudoWrapper.children(".pseudo-classes"));
	});
	pseudoElements.each(function(){
		jq("<div class='pseudoElement' />").text(jq(this).attr("value")).appendTo(pseudoWrapper.children(".pseudo-elements"));
	});
	
	var u = jqxml.find("units");
	var b = jqxml.find("css > browsers browser");
	var o = jqxml.find("css > equality operator");
	var i = jqxml.find("css > properties property");
	
	// Units
	u.children().each(function(){
		var type = new Array();
		jq(this).children().each(function(index){
			type[index] = RegExp.escaped(jq(this).attr("value"));
		});
		units[this.tagName] = type;
	});
	
	// Browsers
	b.each(function(){
		prefixes[jq(this).attr("name")] = jq(this).attr("prefix");
	});
	
	// Operators
	o.each(function(index){
		equalityOperators[index] = jq(this).attr("value");
	});
	
	// Properties Info
	i.each(function(){
		var jqprop = jq(this);
		var name = jqprop.attr("name");
		propertiesInfo[name] = new Object();
		propertiesInfo[name]['default'] = jqprop.attr("default-value");
		propertiesInfo[name]['support'] = jQuery.parseJSON( jqprop.attr("browser-support") );
		propertiesInfo[name]['type'] = jqprop.attr("type");
		propertiesInfo[name]['versions'] = jqprop.find("versions").text();
	});
	wasInitiated = true;
	jq(document).trigger("content.modified.csseditor");
}