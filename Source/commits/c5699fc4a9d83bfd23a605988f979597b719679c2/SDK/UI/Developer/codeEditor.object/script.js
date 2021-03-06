// jQuery part
// change annotation from "$" to "jq"

/*
var jq=jQuery.noConflict();

if (!jq.fn.codeEditor) {
console.log("2");
// register plugin
(function(jq){
	//plugin variables
	var globalRegexp = jq();
	var globalTokens = jq();
	var langRegexp = jq();
	var langTokens = jq();
	var jqlineWrapper = jq("<div class='code' />");
	var tab = document.createTextNode("\t");
	var popupTimer;
	
	//global + local
	var HELP_EXPRESSIONS = {
		scriptLineBreak : /<[ ]*(\/|RW_SLASH_TOKEN)[ ]*div[^>]*>/gm,
		lineBreak : /\n/g,
		tag : /<[^<>]*>/gm,
		notCodeTag : /(?!<(div|RW_DIV_TOKEN)[^<>]*class=("|&quot;|'|&apos;)?[^<>]*code[^<>]*("|&quot;|'|&apos;)?[^<>]*>)<[^<>]*>/g,
		codeTag : /<(div|RW_DIV_TOKEN)[^<>]*(class|RW_CLASS_TOKEN)=("|&quot;|'|&apos;)?[^<>]*(code|RW_CODE_TOKEN)[^<>]*("|&quot;|'|&apos;)?[^<>]*>/g,
		//codeLine : /<div[^<>]*class=("|')?[^<>]*code[^<>]*("|')?[^<>]*>[^\v]*?<\/div>(?=(<div[^<>]*class=("|')?[^<>]*code[^<>]*("|')?[^<>]*>|))/g,
		startTabs : /^\t*\/,
		br: /((&lt;|\<)[ ]*br[ ]*\/{0,1}(&gt;|\>)\n|(&lt;|\<)[ ]*br[ ]*\/{0,1}(&gt;|\>))/g
		//br:/(&lt;|<)\bbr\b(.(?!(>|&gt;)))*.(>|&gt;)((<|&lt;)\/(.(?!(>|&gt;)))*.(>|&gt;))?|(&lt;|<)[ ]*\bbr\b[ ]*(&gt;|>)/g
	};
	
	var HELP_CONSTANTS = {
		"CLASS" : "RW_CLASS_TOKEN",
		"SLASH" : "RW_SLASH_TOKEN",
		"DIV"	: "RW_DIV_TOKEN",
		"CODE"	: "RW_CODE_TOKEN"
	}
	
	var methods = {
		init : function(lang) {
			// instance variables
			var codeEditorHabitat = jq();
			//var codeEditorContainer = jq();
			var toolsArea = jq();
			var scriptArea = jq();
			var presentationArea = jq();
			var jqtxtArea = jq();
			var userSelectionRange = null;
			userSelectionArray = {
				"collapsed" : true,
				"commonAncestorContainer" : null,
				"startContainer" : null,
				"endContainer" : null,
				"startOffset" : 0,
				"endOffset" : 0,
				"offsetFromEnd" : 0
			}
			
			var documentModel = jq("<model />");

			//var statusbar = jq();
			var scriptWrapper = jq();
			var scriptLines = jq();
			var blockIndex = 0;
			var blockSize = 40;
			var syncTimer;
			var keyTimer = 0;
			var topDigits = 3;
			var mlRegexp = jq();

			var input = "";
			var textinput = "";
			var wasInitiated = false;
			var jqfragment = jq("<div />");
			var jqprompt = jq("<span class='prompt' />");
			var jqhighlight = jq("<span class='highlight'></span>");
			var currentWord = "";
			var filtering = true;
			var start = new Date().getTime();		
		
			return this.each(function(){
				//html
				//console.log("fetching");
				if(this.tagName.toUpperCase() != 'DIV'){
					jq.error('Container must(!) be a "div" element...');
					return;
				}

				// instance variables
				codeEditorHabitat = jq(this);
			
				toolsArea = jq(".toolsArea", codeEditorHabitat);
				scriptArea = jq(".scriptArea", codeEditorHabitat);
				presentationArea = jq(".presentationArea", codeEditorHabitat);
				jqtxtArea = jq(".contentArea", codeEditorHabitat);
				
				// Prevent area contents from being visible in console inspector
				jqtxtArea.detach();
				var txt = jqtxtArea.val();
				jqtxtArea.empty();
				jqtxtArea.val(txt);
				scriptArea.after(jqtxtArea);
				
				scriptWrapper = jq(".scriptWrapper", codeEditorHabitat);
				scriptLines = jq(".scriptLines", codeEditorHabitat);
				
				//scriptArea.after(jqtxtArea);
				
			//Embed functionality
				
				jq(document).off("toggleParsing.codeEditor")
				jq(document).on("toggleParsing.codeEditor",function(ev) {
					filtering = !filtering;
				});
				
				scriptWrapper.off("click.codeEditor");
				scriptWrapper.on("click.codeEditor",function(ev){
					if(jq(ev.target).is(jq(this)) && jq.type(userSelectionArray.startContainer) != "null" && jq.type(userSelectionArray.startContainer) != "undefined"){
						userSelectionRange = document.createRange();
						userSelectionRange
							.setStart(userSelectionArray.startContainer, 
										userSelectionArray.startOffset);
						userSelectionRange
							.setEnd(userSelectionArray.endContainer, 
										userSelectionArray.endOffset);
						var userSelection = getUserSelection();
						userSelection.removeAllRanges();
						userSelection.addRange(userSelectionRange);
					}
					scriptArea.trigger("focus");
				});

				presentationArea.off("click.codeEditor", ".presentationArea > .lineNumber");
				presentationArea.on("click.codeEditor", ".presentationArea > .lineNumber", function(ev){
					if (ev.ctrlKey)
						return;
				
					var jqthis = jq(this);
					var nodeToSelect = scriptArea.children(".code").removeClass("active").eq(jq(ev.target).index()/2);

					//select node
					userSelectionRange = document.createRange();
					userSelectionRange.selectNodeContents(nodeToSelect.get(0));
					var userSelection = getUserSelection();
					userSelection.removeAllRanges();
					userSelection.addRange(userSelectionRange);

					//declare active
					nodeToSelect.addClass("active");
				});
				
				scriptArea.off("init.content.codeEditor")
				scriptArea.on("init.content.codeEditor",function(ev){
					if (langRegexp.filter("[lang='"+lang+"']").length > 0
						&& langTokens.filter("[lang='"+lang+"']").length > 0
						&& !wasInitiated) {
						wasInitiated = true;
	
						var jqthis = jq(this);

						// Regexps that need special care for multiline.
						mlRegexp = globalRegexp.children("[name='filter']").children("regexp")
								.add(langRegexp.filter("[lang='"+lang+"']").children("[name='filter']").children("regexp"));
						mlRegexp = mlRegexp.filter("[identifier]");
						
						input = jqthis.html();
						// replace br's
						input = input.replace(HELP_EXPRESSIONS.br,"</div><div class='code'>");
						
						jqthis.html(input);
						
						jqthis.trigger("preprocess", true);
					}
				});
				
				scriptArea.off("focus.codeEditor").off("blur.codeEditor");
				scriptArea.on("focus.codeEditor", function(){
					if (jq(this).data("focusState") != 1) {
						jq(this).data("focusState", 1);
						jq(this).trigger("focusGained.codeEditor");
					}
				}).on("blur.codeEditor",function(){
					if (jq(this).data("focusState") != 0) {
						jq(this).data("focusState", 0);
						jq(this).trigger("focusLost.codeEditor");
					}
				});
				
				scriptArea.off("paste.manager.codeEditor");
				scriptArea.on("paste.manager.codeEditor",function(ev){
					start = new Date().getTime();
					
					var jqthis = jq(this);

					jqthis.doTimeout("pasteTimeout", 1, function() {
						// multiline unwrap
						jqthis.children(".code").children(".code").removeAttr("style").unwrap();
						// Remove style from tags inside the codes.
						jqthis.children(":not(.code)").each(function(){
							if (jq(this).children().length == 1 && jq(this).children().get(0).tagName == "BR") {
								jq(this).remove();
								return;
							}
							jqlineWrapper.clone().append(jq(this).text()).replaceAll(jq(this));
						});
						
						//pasted as text and needs reshaping
						var activeHTML = jqthis.children(".active").html();

						activeHTML = activeHTML.replace(HELP_EXPRESSIONS.br, "");
						activeHTML = activeHTML.replace(HELP_EXPRESSIONS.lineBreak,"</div><div class='code contentAltered'>");
						activeHTML = activeHTML.replace(HELP_EXPRESSIONS.notCodeTag, "");
						activeHTML = activeHTML.replace(HELP_EXPRESSIONS.codeTag, function(a,b,c,d) {
							return '</div>'+a;
						});
						
						activeHTML = "<div class='code contentAltered'>"+activeHTML+"</div>";
						activeHTML = activeHTML.replace(new RegExp("<div class='code'></div>","g"),"<div class='code'></div>");
						
						var newActive = jq(activeHTML);
						newActive.last().addClass("active");//.end().addClass("contentAltered");
						jqthis.children(".active").replaceWith(newActive);
						//}
						
						// highlight prompt line after paste
						newActive.last().addClass("highlight");//.scrollHere(0);
						setTimeout(function(){
							jqthis.children(".code").removeClass("highlight");
						}, 1000);

						//select active
						userSelectionRange = document.createRange();
						userSelectionArray.startContainer = jqthis.children(".contentAltered").eq(-1).contents().get(0);
						userSelectionArray.endContainer = jqthis.children(".contentAltered").eq(-1).contents().get(0);
						
//						var sContainer = jqthis.children(".active").eq(0).contents().get(0);
						userSelectionRange
							.setStart(userSelectionArray.endContainer, userSelectionArray.endContainer.length - userSelectionArray.offsetFromEnd);
						userSelectionRange
							.setEnd(userSelectionArray.endContainer, userSelectionArray.endContainer.length - userSelectionArray.offsetFromEnd);
						//userSelectionRange.selectNode(jqthis.children(".active").eq(0).contents().get(0));
						var userSelection = getUserSelection();
						userSelection.removeAllRanges();
						userSelection.addRange(userSelectionRange);
						userSelection.collapseToEnd();

						jqthis.trigger("preprocess");
					});
				});
				
				scriptArea.off("keydown.manager.codeEditor");
				scriptArea.on("keydown.manager.codeEditor",function(ev) {
					// if key changes content
					if ((57>=ev.which && ev.which>=48) //number
							|| (105>=ev.which && ev.which>=96) //numpad number
							|| (90>=ev.which && ev.which>=65) //letter
							|| (ev.which == 8) || (ev.which == 46) //backspace | delete  
							|| (ev.which == 13) || (ev.which == 32) //enter
							|| (111>=ev.which && ev.which>=106) //+-*./
							|| (192>=ev.which && ev.which>=186) //;=,-.`~
							|| (222>=ev.which && ev.which>=219) //'\][
							|| (ev.which == 9) // tab
							|| (ev.which == 226)) { 
						var jqthis = jq(this);

						//wrap orphans
						if (ev.which == 13) {
							//jqthis.find(".code").children("br").unwrap();
							//jqthis.children("br").wrap(jqlineWrapper.clone());
							//enter
							//jqthis.doTimeout("typeTimeout", true);
							jqthis.doTimeout("typeTimeout", 1, function() {							
								// fix for empty texts (!)
								jqthis.contents().filter(function() {
									return (this.nodeType == 3 && this.nodeValue == "");
								}).remove();
								// -----------------------
								
								var jqcontents = jqthis.contents();
								var jqdivs = jqthis.children("div");
								
								if (jqcontents.length != jqdivs.length) {
									jqcontents.not(jqdivs).wrapAll("<div />");
								}
								jqthis.children("div").not(".code").addClass("code");
								
								// prepend tabs
								jqthis.children("div").each(function() {
									if (jq(this).contents().length == 1 && jq(this).contents().is("br")) {
										var prevSibling = jqthis.children("div").eq(jq(this).index()-1);
										var startTabs = prevSibling.text().match(HELP_EXPRESSIONS.startTabs);
										jq(this).prepend(jq(document.createTextNode(startTabs)).clone().get(0));
										return false;
									}
								});
								
							});
						} else if (ev.which == 9 && !(ev.ctrlKey || ev.metaKey)) {
							//tab
							//append tab span
							if (jq.type(userSelectionArray.startContainer) != "null" && jq.type(userSelectionArray.startContainer) != "undefined") {
								if (jq(userSelectionArray.commonAncestorContainer).closest(".code").length != 0
									|| jq(this).children(".code").length == 0) {
									jq(this).children(".code").removeClass("selection");
									
									userSelectionRange = document.createRange();
									userSelectionRange
										.setStart(userSelectionArray.startContainer, 
													userSelectionArray.startOffset);
									userSelectionRange
										.setEnd(userSelectionArray.endContainer, 
													userSelectionArray.endOffset);
									// line tab
									var df = document.createDocumentFragment();
									df.appendChild(jq(tab).clone().get(0));
									userSelectionRange.deleteContents();
									userSelectionRange.insertNode(df);
									
									var userSelection = getUserSelection();
									userSelection.removeAllRanges();
									userSelection.addRange(userSelectionRange);
									userSelection.collapseToEnd();
								} else {
									// multiline tab
									
									userSelectionRange = document.createRange();
									userSelectionRange
										.setStart(userSelectionArray.startContainer, 
													userSelectionArray.startOffset);
									userSelectionRange
										.setEnd(userSelectionArray.endContainer, 
													userSelectionArray.endOffset);
									
									// get selection's boundary points
									var sCode = jq(userSelectionRange.startContainer.parentNode).closest(".code");
									//var sOff = userSelectionRange.startOffset;
									var eCode = jq(userSelectionRange.endContainer.parentNode).closest(".code");
									//var eOff = userSelectionRange.endOffset;
									
									if (sCode.length != 0 && eCode.length != 0) {
										jq(this).children(".code").removeClass("selection");
										jq(this).children(".code")
													.slice(sCode.index(), eCode.index()+1).addClass("selection");
										userSelectionRange.setStartBefore(sCode.get(0));
										userSelectionRange.setEndAfter(eCode.get(0));
									}
									
									if (!ev.shiftKey) {
										tabLines(jq(this));
									} else {
										tabLines(jq(this), "remove");
									}
									
									var userSelection = getUserSelection();
									userSelection.removeAllRanges();
									userSelection.addRange(userSelectionRange);
								}
							}
						} else if (ev.which == 86 && (ev.ctrlKey || ev.metaKey)) {
							//pastes are handled by other function, no need to keep on
							return;
						} else if ((ev.ctrlKey || ev.metaKey) && !(ev.which == 88)) {
							return;
						}
						// trigger after 50ms {gives time to the content to change after keydown}
						keyTimer = setTimeout(function() {
							keyTimer = 0;
							clearTimeout(syncTimer);
							//start sync
							jqthis.trigger("preprocess");
						}, 1);
						
						// prevent focuslost
						if (ev.which == 9) {
							ev.preventDefault();
						}
					}
				});
				
				scriptArea.off("preprocess.codeEditor");
				scriptArea.on("preprocess.codeEditor", function(ev, init) {
					scriptLines.empty();
					// Input must be in code divs here... Any other tags will be stripped.
					var jqthis = jq(this);
					var textinput = "";
					
					if (init !== true) {
						jqthis.trigger("text.modified");
					}
					textinput = jqthis.html();
					
					// Replace line breaks and strip tags.
					textinput = textinput.replace(HELP_EXPRESSIONS.scriptLineBreak,"\n");
					// ffox tweak
					textinput = textinput.replace(/<\bbr\b>(?![\n\r])/g,"\n");
					textinput = textinput.replace(HELP_EXPRESSIONS.tag,"");
					// This is the code for the textarea
					jqtxtArea.detach();
					jqtxtArea.val(textinput);
					jqthis.after(jqtxtArea);
					
					// Textarea contents have changed
					if (init !== true) {
						jqtxtArea.trigger("change");
					}

					// Don't bother with text that is guaranteed to be the same [normally...]
					blockIndex = 0;
					var firstAltered = jqthis.children(".code.contentAltered, .code.sync, .code.active").first().index();
					if (firstAltered > 0) {
						blockIndex = firstAltered;
					}
					
					if (presentationArea.children(".code").eq(blockIndex).length == 0)
						blockIndex = Math.floor(presentationArea.length / 2);
					
//					console.log("Start sync from line: "+blockIndex);
					jqthis.trigger("sync.codeEditor", [true, init]);
				})
				
				scriptArea.off("sync.codeEditor");
				scriptArea.on("sync.codeEditor", function(ev, first){
//console.log("Sync Start");
//console.log(new Date().getTime() - start);
					var jqthis = jq(this);
					// Get previous line's html from presentation.
					var guideLine = "";
					if (blockIndex > 0)
						guideLine = presentationArea.children(".code").eq(blockIndex-1).html();
					// Special strip guideLine [in order to know how to style the following lines]
					// Temp... need to coop with regexps that were given to me, for class names...
					guideLine = specialStrip(guideLine);
//					console.log(first);
					
					// Get block for stylizing
					var block = jqthis.children(".code").eq(blockIndex).removeClass("sync").end()
														.slice(blockIndex, blockIndex + blockSize);									
					
					block = block.clone();
					block.removeClass().addClass("code").removeAttr("style");
					
					var blockInput = guideLine + jq("<div />").html(block).html();

					blockInput = blockInput.replace(/<\bbr\b>(?!([\n\r]|<\/div>))/g,"</div><div class='code'>");
					blockInput = blockInput.replace(HELP_EXPRESSIONS.lineBreak,"</div><div class='code'>");
					blockInput = blockInput.replace(HELP_EXPRESSIONS.notCodeTag,"");
					blockInput = blockInput.replace(HELP_EXPRESSIONS.codeTag, function(a,b,c,d) {
						return '\n<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>'+a.replace(/\"|\'/g, "&quot;").replace(/\bclass\b/g, HELP_CONSTANTS.CLASS).replace(/\bcode\b/g, HELP_CONSTANTS.CODE).replace(/\//g, HELP_CONSTANTS.SLASH).replace(/\bdiv\b/g, HELP_CONSTANTS.DIV);
					});
					blockInput = blockInput.substring('\n<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>'.length)+'\n<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>';
//console.log("Normalized");
//console.log(new Date().getTime() - start);	
			
					if (filtering) {
						jqthis.trigger("filter", [blockInput, first]);
					} else {
						jqthis.trigger("present", [blockInput, first]);
					}
				});
				
				// Strips a line from special tags for formatting purposes.
				function specialStrip(line) {
					if (!line)
						return '<div class="code guide"></div>';
						
					for (var i = 0; i < mlRegexp.length ; i++){
						var jqthis = mlRegexp.eq(i);
						var tempRegexp = new RegExp("<span[^<>]*(class|RW_CLASS_TOKEN)=(\"|&quot;|'|&apos;)?[^<>]*"+jqthis.attr("class")+"[^<>]*(\"|&quot;|'|&apos;)?[^<>]*>((\"|&quot;|'|&apos;)?|)");
						line = line.replace(tempRegexp, jqthis.attr("identifier"));
					}

					return '<div class="code guide">'+line+'</div>';
				}
				
				scriptArea.off("filter.codeEditor");
				scriptArea.on("filter.codeEditor",function(ev, input, first) {
					var jqthis = jq(this);
					if (jq.trim(input) != "") {
						var middle = input;
						
						// Set styles
						middle = stylizeContent(middle, lang);
						middle = middle.replace(HELP_EXPRESSIONS.lineBreak, '');
						
						// Highlight
						middle = highlightTokens(middle, lang);
						middle = middle.replace(/&quot;/g, "\"").replace(HELP_EXPRESSIONS.tag, function(a,b,c,d) {
							return a.replace(new RegExp(HELP_CONSTANTS.CLASS, "g"), "class")
									.replace(new RegExp(HELP_CONSTANTS.SLASH, "g"), "/")
									.replace(new RegExp(HELP_CONSTANTS.CODE, "g"), "code")
									.replace(new RegExp(HELP_CONSTANTS.DIV, "g"), "div");
						});
						
						input = middle;
					}
					jqthis.trigger("present", [input, first]);
				});
				
				scriptArea.off("present.codeEditor");
				scriptArea.on("present.codeEditor",function(ev, output, first){
					var jqthis = jq(this);
					// present code

					var counter = blockIndex;
					// line numbering
					output = output.replace(HELP_EXPRESSIONS.codeTag, function(a) {
						return '<span class="lineNumber">'+(counter++)+'</span>'+a;
					});
					//console.log(output);
					var block = jq("<div />").html(output);
					block.children().slice(0, 2).remove();

					var presentationBlock = jq();
					// *2 to include 
					presentationBlock = presentationArea.children()
						.slice(blockIndex * 2, (blockIndex + blockSize) * 2);

					if (presentationBlock.length == 0)
						presentationArea.append(block);
					else {
						block.insertBefore(presentationBlock.first());
						presentationBlock.remove();
					}	

					// _____ If lines are too many, increase the line column width.
					var lines = blockIndex + blockSize - 1;
					var linesDigits = (lines == "undefined" || lines == 0) ? 1 : Math.ceil(Math.log(lines) / Math.LN10);
					var linesExtraDigits = (linesDigits <= topDigits ? 0 : linesDigits - topDigits);
					if (linesExtraDigits > 0) {
						scriptWrapper.css("padding-left", "+="+(linesExtraDigits*10));
						scriptLines.css("width", "+="+(linesExtraDigits*10));
						topDigits += linesExtraDigits;
					}
					if (jqthis.children().eq(blockIndex + blockSize).length != 0) {
						blockIndex += blockSize;
						if (first !== true && jq.type(syncTimer) == "undefined")
							return;
						
						jqthis.children().eq(blockIndex).addClass("sync");
						syncTimer = setTimeout(function(){
							jqthis.trigger("sync.codeEditor");
						}, 1);
					}else{
						var last = counter-2;//jqthis.children().last().index();
						presentationArea.children().slice((last+1)*2).remove();
					}
				});	
				
				jq(this).off("insert.codeEditor");
				jq(this).on("insert.codeEditor", function(ev, content){
					if(jq.type(content) == "undefined" || jq.type(content) == "null"){
						content = "";
					}
					
					insertInUserSelection(content, scriptArea, userSelectionArray);
					scriptArea.trigger("preprocess");
				});
				
				jq(this).on("replaceContent.codeEditor", function(ev, content, pattern, modifiers, appendOnNotFound, triggerTextModified){
					if(jq.type(content) == "undefined" || jq.type(content) == "null"){
						content = "";
					}
					
					if (jq.type(pattern) != "undefined") {
						var text = jqtxtArea.val();
						text = jq("<div>").html(text).text();
						var newtext = text.replace(new RegExp(pattern, modifiers), content);
						if ((newtext == text) && appendOnNotFound)
							newtext = text+content;

						content = jq("<div>").text(newtext).html();
					}
					content = jq.trim(content).replace(HELP_EXPRESSIONS.lineBreak,"</div><div class='code contentAltered'>");
					scriptArea.html("<div class='code contentAltered'>"+content+"</div>");
					
					scriptArea.trigger("preprocess", triggerTextModified);
				});
				
				// If criterion is a number, scroll to that line
				// else scroll to the first match of the (text || regexp) the criterion holds
				jq(this).on("scrollContent.codeEditor", function(ev, criterion){
					if (!isNaN(parseFloat(criterion)) && isFinite(criterion)) {
						// number
						var line = criterion;
						scriptWrapper.find(".lineNumber:contains("+line+")").first().scrollHere();
					} else if (criterion instanceof RegExp) {
						var text = jqtxtArea.val();
						text = jq("<div>").html(text).text();
						// RegExp
						var matches = text.match(criterion);
						if (jq.type(matches) == "null")
							return;
							
						var idx = text.indexOf(matches[0]);
						var substr = text.substring(0, idx);
						
						matches = substr.match(HELP_EXPRESSIONS.lineBreak);
						if (jq.type(matches) == "null")
							matches = new Array();
	
						var line = matches.length + 1;
						scriptWrapper.find(".lineNumber:contains("+line+")").first().scrollHere();
					} else if (jq.type(criterion) == "string") {
						var text = jqtxtArea.val();
						text = jq("<div>").html(text).text();
						// String
						var line = 0;
						if (criterion == "top") {
							line = 0;
						} else if (criterion == "bottom") {
							line = text.match(HELP_EXPRESSIONS.lineBreak).length;
						} else {
							var idx = text.indexOf(criterion);
							if (idx < 0)
								return;
								
							var substr = text.substring(0, idx);
							line = substr.match(HELP_EXPRESSIONS.lineBreak).length + 1;
						}
						scriptWrapper.find(".lineNumber:contains("+line+")").first().scrollHere();
					}
				});

				jq(this).on("getCodeEditorContent", function(ev, escaped, callback){
					var content = "";
					if (escaped === true)
						content = jq('<div/>').html(jqtxtArea.val()).text()
					else
						content = jqtxtArea.val();
					
					callback.call(this, content);
				});
				
				scriptArea.off(".selection.codeEditor")
							.off(".activePrompt.codeEditor")
							.off(".activeLine.codeEditor")
							.off(".activeWord.codeEditor")
							.off(".activePopup.codeEditor")
							;
				scriptArea.on("mouseup.selection.codeEditor", function(){
								userSelectionArray = storeUserSelection();
							})
							.on("mouseup.activeLine.codeEditor", function(ev){
								defineActiveLine(userSelectionArray, scriptArea);
							})
							.on("keydown.selection.codeEditor", function(ev){
								if(!(ev.which == 86 && (ev.ctrlKey || ev.metaKey)))
									setTimeout(function(){
										userSelectionArray = storeUserSelection();
									}, 1);
							})
							.on("keydown.activeLine.codeEditor", function(){
								setTimeout(function(){
									defineActiveLine(userSelectionArray, scriptArea);
								}, 1);
							});
			});
			//local functions
		}//, -> other methods
	};
//global functions
	
	// These regexp will be used for every type of editor
	// Get regexps
	function loadGlobalRegexp() {
		var data = new Object();
		data.p = "global";
		data.c = "regulars";
		ascop.asyncRequest(
			"/ajax/resources/sdk/codeEditor/parsers.php", "GET", jq.param(data), "xml", this, function(response) {
				var jqGlobalRegexp = jq(jq(response).children("regulars"));
				globalRegexp = jqGlobalRegexp.attr("lang", "global");
			}
		);
	}
	
	// These tokens will be searched in every type of editor
	// Get regexps
	function loadGlobalTokens() {
		var data = new Object();
		data.p = "global";
		data.c = "tokens";
		ascop.asyncRequest(
			"/ajax/resources/sdk/codeEditor/parsers.php",  "GET", jq.param(data), "xml", this, function(response) {
				var jqGlobalTokens = jq(jq(response).children("tokens"));
				globalTokens = jqGlobalTokens.clone().attr("lang", "global");
			}
		);
	}
	
	// load each parser's tokens and regexp
	function identifyParsers() {
		var data = new Object();
		data.c = "list";
		ascop.asyncRequest(
			"/ajax/resources/sdk/codeEditor/parsers.php", "GET", jq.param(data), "xml", this, function(response) {
				loadParsers(jq(response).children("parsers"));
			}
		);
	}
	
	function loadParsers(parsers){
		var jqParsers = jq(parsers);
		var jqparsersList = jqParsers.children();

		jqparsersList.each(function() {
			var lang = jq(this).attr("name");
			loadParserRegexp(lang);
			loadParserTokens(lang);
		});
	}
	
	function loadParserRegexp(language) {
		// Get Parser regexps
		var data = new Object();
		// parser
		data.p = language;
		// content
		data.c = "regulars";
		
		ascop.asyncRequest(
			"/ajax/resources/sdk/codeEditor/parsers.php", 
			"GET",
			jq.param(data),
			"xml",
			this,
			function(response){
			
				var jqLangRegexp = jq(jq(response).children("regulars"));
				langRegexp = langRegexp.add(jqLangRegexp.attr("lang", language));
				jq('.scriptArea').trigger('init.content');
				
			}
		);
	}
	
	function loadParserTokens(language) {
		// Get Parser regexps
		var data = new Object();
		// parser
		data.p = language;
		// content
		data.c = "tokens";
		
		ascop.asyncRequest(
			"/ajax/resources/sdk/codeEditor/parsers.php", 
			"GET",
			jq.param(data), 
			"xml",
			this,
			function(response){
			
				var jqLangTokens = jq(jq(response).children("tokens"));
				langTokens = langTokens.add(jqLangTokens.clone().attr("lang", language));
				jq('.scriptArea').trigger('init.content');
				
			}
		);
	}
	
	//init lines with preciding tabs
	function tabLines(parent, remove){
		if(remove == null){
			parent.children(".code.selection").each(function(){
				jq(this).prepend(jq(tab).clone());
			});
		}else{
			parent.children(".code.selection").each(function(){
				jq(this).html(jq(this).html().replace(/^\t/,""));
			});
		}
	}
	
	//remove elements inside the rootElement's children
	function cleanChildren(rootElement){
		rootElement.children("span").each(function(){
			jq(this).find("span").contents().unwrap();
		});
		
		return rootElement;
	}
	
	// enclose orphan text in spans and map lines between area (?)
	function stylizeText(str2chk){
		var tmpString = str2chk;
		// wrap text in spans
		var tmpDiv = jq("<div />");
		tmpDiv.html(tmpString);
		var tmpOut = "";
		var str = "";
		var	span = document.createElement('span');

		//clean children
		tmpDiv = cleanChildren(tmpDiv);
		
		//split on line break to map with original lines...
		//if map of lines is wanted
		tmpOut = tmpDiv.html();
		tmpOut = tmpOut.replace(HELP_EXPRESSIONS.lineBreak,"<span "+HELP_CONSTANTS.CLASS+"='lineBreak'>\n</span>");
		tmpDiv.html(tmpOut);
		
		tmpDiv.children().each(function(){
			var jqthis = jq(this);
			if(jqthis.children().length != 0){
				jqthis.contents().not(jqthis.children()).wrap(jq("<span "+HELP_CONSTANTS.CLASS+"='"+jqthis.attr("class")+"' />"));
				jqthis.contents().unwrap();
			}
		});

		//wrap is slow! ~0.5sec
		tmpDiv.contents().not(tmpDiv.children()).wrap(span);
		//
		
		var lineIndex = 0;
		tmpDiv.children().each(function(){
			jq(this).attr("data-original-line", lineIndex+1);
			if(jq(this).text() == "\n"){
				lineIndex++;
			}else if(jq(this).text() == ""){
				jq(this).remove();
			}
		}).not("[class]").addClass("text");
		//
		tmpOut = tmpDiv.html();

		return tmpOut;
	}
	
	// search and highlight tokens of language+global in a given string
	function highlightTokens(str2chk, language) {
		var tmpString = str2chk;
		// get only text, not children
		var tmpDiv = jq("<div />");
		tmpDiv.html(tmpString);
		var str = "";
		tmpDiv.children().contents().not(tmpDiv.children().children()).each(function() {
			str += jq(this).text()+" ";
		});
		// snif hits
		var tokensToCheck = globalTokens
			.add(langTokens.filter("[lang='"+language+"']"));
		var tokenRegexps = globalRegexp
			.add(langRegexp.filter("[lang='"+language+"']"));

		var tokens = tokensToCheck.find("token");
		var expression = "";
		for (var i=0; i < tokens.length; i++)
			expression += "|\\b"+tokens.eq(i).text()+"\\b";
		expression = "("+expression.substr(1)+")";
		if (expression == "()" ) 
			return tmpString;
		
		var cl = tokensToCheck.filter("[lang='"+language+"']").attr("class");
		tmpString = tmpString.replace(new RegExp(expression, "g"), function(a){
			return "<span "+HELP_CONSTANTS.CLASS+"='"+language+cl+"'>"+a+"</span>";
		});
		
		return tmpString;
	}
	
	//search and stylize the given string according to language+global regexp
	function stylizeContent(str2chk, language) {
		//stylize elements that have a top priority in stylizing (i.e. string, comment)
		function majorStylizer(str) {
			var first = +Infinity;
			var matchedChild = jq();
			// Get closest match
			instanceRegexp.filter("[priority*='major']").each(function() {
				var pos = str.search(new RegExp(jq(this).text(), jq(this).attr("modifiers")));
				if (pos > -1 && pos < first) {
					first = pos;
					matchedChild = jq(this);
				};
			})
			// Do something with that and redo with the substring, till there's no more text...
			if (first == +Infinity) {
				// No more matches | No more text
				tmpOut += str;
				tmpOut = tmpOut.substring(0, tmpOut.lastIndexOf('<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>'))+'<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>';
				return;
			} else if (matchedChild.is(codeSnif)) {
				// Match is a code tag... Get next substring
				var firstMatch = str.match(new RegExp(matchedChild.text(), matchedChild.attr("modifiers")));
				var endOffset = str.indexOf(firstMatch[0])+firstMatch[0].length;
				tmpOut += str.substring(0, endOffset);
				majorStylizer(str.substring(endOffset));
			} else {
				// Valid Match...
				var firstMatch = str.match(new RegExp(matchedChild.text(), matchedChild.attr("modifiers")));
				var fm = firstMatch[0].replace(/<(\/|RW_SLASH_TOKEN)(div|RW_DIV_TOKEN)>/g, '</span><'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>');
				fm = fm.replace(HELP_EXPRESSIONS.codeTag, function(a){
					return a+"<span "+HELP_CONSTANTS.CLASS+"='"+language+matchedChild.attr("class")+"'>";
				});
				var efm = RegExp.escaped(firstMatch[0]);
				str = str.replace(new RegExp(efm), function(a){
					return "<span "+HELP_CONSTANTS.CLASS+"='"+language+matchedChild.attr("class")+"'>"+fm+"</span>";
				});
				var endOffset = str.indexOf(fm+"</span>")+fm.length+"</span>".length;
				tmpOut += str.substring(0, endOffset);
				majorStylizer(str.substring(endOffset));
			}
		}
		
		//stylize elements that have minor priority in stylizing (i.e. var format, keywords etc)
		function minorStylizer(str){
			instanceRegexp.filter("[priority*='minor']").each(function(){
				var jqthis = jq(this);
				str = str.replace(new RegExp(jqthis.text(), jqthis.attr("modifiers")+"g"), function(matched, index, originalText){ 
					return "<span "+HELP_CONSTANTS.CLASS+"='"+language+jqthis.attr("class")+"'>"+matched+"</span>";
				})
			});
			
			tmpOut = str;		
		}
		
		var tmpOut = "";
		
		var instanceRegexp = globalRegexp.children("[name='filter']").children("regexp")
								.add(langRegexp.filter("[lang='"+language+"']").children("[name='filter']").children("regexp"));
		var codeSnif = jq("<regexp modifiers='' priority='major' />").text(HELP_EXPRESSIONS.codeTag.source);
		instanceRegexp = instanceRegexp.add(codeSnif);

		majorStylizer(str2chk);
		minorStylizer(tmpOut);

		return tmpOut;
	}

	// get the selection of a user in the editor
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

	// save the selection in a local variable
	function storeUserSelection(){
		if(getUserSelection().anchorNode != null){
			tmpRange = getUserSelection().getRangeAt(0).cloneRange();
			userSelectionArray.collapsed = tmpRange.collapsed;
			userSelectionArray.commonAncestorContainer = tmpRange.commonAncestorContainer;
			userSelectionArray.startContainer = tmpRange.startContainer;
			userSelectionArray.startOffset = tmpRange.startOffset;
			userSelectionArray.endContainer = tmpRange.endContainer;
			userSelectionArray.endOffset = tmpRange.endOffset;
			var ofe = userSelectionArray.endContainer.length - tmpRange.endOffset;
			userSelectionArray.offsetFromEnd = (isNaN(ofe) ? 0 : ofe);
			return userSelectionArray;
			//return getUserSelection().getRangeAt(0).cloneRange();
		}
		return null;
	}
	
	function insertInUserSelection(text, sriptArea, userSelectionArray) {
		if (jq.type(userSelectionArray.startContainer) != "null" && jq.type(userSelectionArray.startContainer) != "undefined") {
			userSelectionRange = document.createRange();
			// get selection's boundary points
			var sCont = userSelectionArray.startContainer;
			var sOff = userSelectionArray.startOffset;
			var eCont = userSelectionArray.endContainer;
			var eOff = userSelectionArray.endOffset;
			// expand selection
			userSelectionRange.setEndAfter(eCont.parentNode);
			userSelectionRange.setStartBefore(sCont.parentNode);
			// change content
			var df = userSelectionRange.extractContents();

			var preText = jq(df.firstChild).text().substring(0, sOff);
			var postText = jq(df.lastChild).text().substring(eOff);
			var fChild = jq(df.firstChild).clone();
			fChild.empty().text(preText+text+postText);
			
			jq(df).empty(0);

			df.appendChild(fChild.get(0));
			
			userSelectionRange.insertNode(df);
			// readjust selection
			var activeNode = jq(".code.active", scriptArea).get(0);
			userSelectionRange.setStart(activeNode.firstChild, sOff);
			userSelectionRange.setEnd(activeNode.firstChild, sOff+text.length);

			var userSelection = getUserSelection();
			userSelection.removeAllRanges();
			userSelection.addRange(userSelectionRange);
			tmpRange = userSelection.getRangeAt(0);
			userSelectionArray.collapsed = tmpRange.collapsed;
			userSelectionArray.commonAncestorContainer = tmpRange.commonAncestorContainer;
			userSelectionArray.endContainer = tmpRange.endContainer;
			userSelectionArray.endOffset = tmpRange.endOffset;
			userSelectionArray.offsetFromEnd = userSelectionArray.endContainer.length - tmpRange.endOffset;
			userSelectionArray.startContainer = tmpRange.startContainer;
			userSelectionArray.startOffset = tmpRange.startOffset;
			//userSelectionRange = userSelection.getRangeAt(0);
		}
	}
	
	// popup to be when a context menu/helping popup is needed
	function popUse(currentWord, prmpt, lang){
		clearTimeout(popupTimer);
		popupTimer = setTimeout(function(){
			var tokensToCheck = globalTokens.add(langTokens.filter("[lang='"+lang+"']"));
	
			var tokens = tokensToCheck.find("token");
			var expression = "";
			for (var i=0; i < tokens.length; i++)
				expression += "|\\b"+tokens.eq(i).text()+"\\b";
			expression = "("+expression.substr(1)+")";
			if (expression == "()" ) 
				return;
		}, 400);
	}
	
	//places a prompt element
	function placePrompt(userSelectionArray, jqprompt){
		if(jq.type(userSelectionArray) != "null" && jq.type(userSelectionArray) != "undefined"
			&& userSelectionArray.collapsed){
			var range = document.createRange();
			range.setStart(userSelectionArray.startContainer, userSelectionArray.startOffset);
			range.setEnd(userSelectionArray.endContainer, userSelectionArray.endOffset);
			range.insertNode(jqprompt.get(0));
			jqprompt.parent().get(0).normalize();
		}
	}
	
	// defines the line that is active
	function defineActiveLine(userSelectionArray, scriptArea){
		if(jq.type(userSelectionArray.startContainer) != "null" && jq.type(userSelectionArray.startContainer) != "undefined"){
			//remove actives
			if (scriptArea.children(".code.active").length == 0)
				scriptArea.children(".code.contentAltered").addClass("active");
			scriptArea.children(".code.contentAltered").removeClass("contentAltered");
			scriptArea.children(".code.active").addClass("contentAltered");
			scriptArea.removeClass("active");
			scriptArea.children(".code.active").removeClass("active");
			
			// new selection??
			//userSelectionArray = storeUserSelection();
			//console.log(userSelectionArray.endContainer);
			if ((jq(userSelectionArray.endContainer).text() != "" || jq(userSelectionArray.endContainer).not("div:not(.code):has(br)")) && scriptArea.find(jq(userSelectionArray.endContainer)).length > 0){
				if (jq(userSelectionArray.endContainer).closest("div").children(".code").length > 0)
					jq(userSelectionArray.endContainer).closest("div").children(".code").last().addClass("active");
				jq(userSelectionArray.endContainer).closest("div").addClass("active");
				jq(userSelectionArray.startContainer).closest("div").addClass("contentAltered");
			}
			else
				scriptArea.children(".code.contentAltered").eq(-1).addClass("active");
				
		}
	}
	
	// stores the word next to (left side) the cursor 
	function defineActiveWord(userSelectionArray){
		if(jq.type(userSelectionArray) != "null" && jq.type(userSelectionArray) != "undefined" && userSelectionArray.collapsed){
			var tmpRange = document.createRange();
			tmpRange.setStart(userSelectionArray.startContainer, 0);
			tmpRange.setEnd(userSelectionArray.endContainer, userSelectionArray.endOffset);
			
			var currentWord = "";
			// Get preceding text.
			var txt = tmpRange.toString();
			// Get preceding delimiters.
			var delims = txt.match(/\W/g);
			// Get active word.
			var lastSpacePos = (jq.type(delims) != "null" ? txt.lastIndexOf(delims[delims.length-1]) : -1);
			currentWord = txt.substring(lastSpacePos+1);
			if (lastSpacePos != -1 && currentWord == "")
				currentWord = delims[delims.length-1];

			return currentWord;
		}
		return "";
	}

	// register plugin
	jq.fn.codeEditor = function(method) {
		//calling method logic
		if (typeof(method) == "string") {
			return methods.init.apply(this, arguments);
		} else if (methods[ method ]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (jq.type(method) === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			jq.error('Method ' +  method + ' does not exist in jQuery.codeEditor');
		}
	};
	
	jq.fn.codeEditor.loadedResources = false;
	jq.fn.codeEditor.loadResources = function() {
		loadGlobalRegexp();
		loadGlobalTokens();
		identifyParsers();
		jq.fn.codeEditor.loadedResources = true;
	};

	RegExp.escaped = function(str) {
		return (str+'').replace(/([.?*+^$[\]\\() {}|-])/g,  "\\$1");
	};
	
})(jQuery);
}

// Let the document load
jq(document).one("ready.extra",function() {
	// on content modified search any orphaned editors and initialize them
	jq(document).on("content.modified", function() {
		var ces = jq(".codeEditor").filter("[data-type]");
		if (ces.length > 0 && jq.fn.codeEditor.loadedResources !== true)
			jq.fn.codeEditor.loadResources();
		
		ces.each(function() {
			var type = jq(this).data("type");
			jq(this).codeEditor(type)
				.find(".scriptArea").trigger("init.content.codeEditor").end()
				.removeClass("noDisplay").removeAttr("data-type");
		});
	});
	
	jq(document).trigger("content.modified");	
});*/