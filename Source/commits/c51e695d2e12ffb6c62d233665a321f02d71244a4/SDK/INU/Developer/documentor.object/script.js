/* 
 * Redback JavaScript Document
 *
 * Title: ClassDocumentor
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1
 * DateCreated: 21/01/2013
 * DateRevised: --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready.extra", function() {
	
	var manualModel = jq();
	var manualPrototypes = new Object();
	wasInitiated = false;
	getManualModel();
	
	jq(document).off("content.modified.documentor");
	jq(document).on("content.modified.documentor", function() {
		if (!wasInitiated)
			return;
		
		var documentorWrappers = jq("[data-wrapper='documentor']");
		
		documentorWrappers.each(function() {
			jq(this).removeAttr("data-wrapper").documentor("init");
		});
	});
	
	if (jq.fn.documentor) {
		return;
	}

	(function(jq){
	
		var methods = {
			init : function(){
			
				var types = jq("<span replacement='string'>string</span><span replacement='integer'>integer|int</span><span replacement='float'>float|double|real</span><span replacement='array'>array</span><span replacement='object'>object</span><span replacement='void'>void</span><span replacement='boolean'>boolean|bool|true|false</span><span replacement='resource'>resource</span><span replacement='NULL'>null</span><span replacement='callback'>callback|function</span><span replacement='mixed'>mixed</span><span replacement='number'>number</span>");
			
				var jqDocWrapper = jq(this);
				var jqLinkedEditor = jqDocWrapper.find(".codeContainer").children().first();
				var jqDocModel = jqDocWrapper.find(".documentorContainer").find(".documentor").find("[name='classXMLModel']").first();
				var jqDocEditor = jqDocWrapper.find(".documentorContainer").find(".documentor");
				var docuTool = jqDocEditor.prev();
				
				var jqExpanderObject = { "expander" : jq() };
				var jqStaticExpanderObject = { "expander" : jq() };
				jq(document).trigger("produce.expander", [jqExpanderObject]);
				jq(document).trigger("produce.expander", [jqStaticExpanderObject, true]);
				
				// need to check this
				var jqExpanderUnit = jqExpanderObject.expander.clone(true, true);
				var jqStaticExpanderUnit = jqStaticExpanderObject.expander.clone(true, true);
				// ------------------
				/*jqExpanderUnit = jqDocWrapper.find(".documentorContainer").children(".expander").detach().first();
				jqStaticExpanderUnit = jqExpanderUnit.clone();
				jqStaticExpanderUnit.addClass("static");*/
				
				var nestedModel = jq();
				var sourceModel = jq();
				var documentationModel = jq();
				
				if (jqLinkedEditor.length == 0 || jqDocEditor.length == 0)
					return;
			
				return this.each(function(){
					
					jq(document).off("click", ".documentor .deprecatedTile [type='checkbox']");
					jq(document).on("click", ".documentor .deprecatedTile [type='checkbox']", function(){
					    jq(this).closest(".deprecatedTile").parent().toggleClass("deprecated");
					});					
/*					
					jq(document).off("testp.parser");
					jq(document).on("testp.parser", function(ev, evnt){
						if (evnt == "all"){
							jqDocEditor.trigger("updateSourceModel.documentation");
							jqDocEditor.trigger("updateDocumentationModel.documentation");
							jqDocEditor.trigger("update.documentation");
							jqDocEditor.trigger("reveal.documentation");
						}else if(evnt == "source"){
							jqDocEditor.trigger("updateSourceModel.documentation");
						}else{
							jqDocEditor.trigger("update.documentation");
							jqDocEditor.trigger("reveal.documentation");
						}
					}); 
*/
					
					jqDocEditor.off("click", ".objectsWrapper");
					jqDocEditor.on("click", ".objectsWrapper", function(){
						var txt = "";
						jq(this).find(".objectBox").each(function(){
							txt += jq(this).text()+" ";
						});
						txt = jq.trim(txt);
						jq(this).addClass("noDisplay");
						jq(this).siblings().filter(function(){
							return jq.type(jq(this).data("multipleObjects")) != "undefined";
						}).val(txt).removeClass("noDisplay").focus().select();
						jq(this).remove();
					});
					
					jqDocEditor.off("focusout", "[name]:not([type='checkbox'])");
					jqDocEditor.on("focusout", "[name]:not([type='checkbox'])", function(){
						if (jq.type(jq(this).data("multipleObjects")) != "undefined"
							&& jq(this).next(".objectsWrapper").length == 0) {
							var val = jq.trim(jq(this).val().replace(/[, \t]+/g," "));
							if (val == "") {
								return;
							}
							
							var objects = val.split(" ");
							var newContainer = jq("<div title='Exceptions Thrown' class='objectsWrapper'></div>");
							for (var i = 0; i < objects.length; i++) {
								newContainer.append(jq("<div class='objectBox'>"+objects[i]+"</div>"));
							}
							jq(this).addClass("noDisplay").after(newContainer);
						}
					});
					
					jqDocEditor.off("change", "[name]:not([type='checkbox'])");
					jqDocEditor.on("change", "[name]:not([type='checkbox'])", function(){
						var modelSelector = jq(this).data("docElemSelector");
						var jqthis = jq(this);
						var val = jq.trim(jq(this).val());
						
						var newval = types.filter(function() {
							return new RegExp("\\b("+jq(this).text()+")\\b", "i").test(jqthis.filter(".constantType, .propertyType, .methodReturnType, .argumentType").val());
						}).first().attr("replacement");
						val = (jq.type(newval) == "undefined" ? val : newval );
						jqthis.val(val);
						
						if (jq.type(jq(this).data("multipleObjects")) != "undefined") {
							if (val == "") {
								documentationModel.find(modelSelector).empty();
								jq(this).trigger("update.documentation");
								return;
							}	
							val = val.replace(/[, \t]+/g," ");
							console.log(val);
							var objects = val.split(" ");
							documentationModel.find(modelSelector).empty();
							for (var i = 0; i < objects.length; i++) {
								documentationModel.find(modelSelector).append(jq("<"+jq(this).data("multipleObjects")+"/>").text(objects[i]));
							}
						} else if (jq.type(jq(this).data("docElemSelectorAttr")) == "undefined") {
							documentationModel.find(modelSelector).text(jq("<div>").text(val).html());
						} else {
							documentationModel.find(modelSelector).attr(jq(this).data("docElemSelectorAttr"), val);
						}
						jq(this).trigger("update.documentation");
					});
					 
					jqDocEditor.off("change", "[type='checkbox']");
					jqDocEditor.on("change", "[type='checkbox']", function(){
						var modelSelector = jq(this).data("docElemSelector");
						var name = jq(this).attr("name");
						var represent = jq(this).parent().parent().find("[name='"+name+"']").not(this).first();
						var val = (jq(this).attr("checked") == "checked" ? represent.val() : "");

						if (jq.type(jq(this).data("docElemSelectorAttr")) == "undefined")
							documentationModel.find(modelSelector).text(jq("<div>").text(val).html())
						else
							documentationModel.find(modelSelector).attr(jq(this).data("docElemSelectorAttr"), val);
						jq(this).trigger("update.documentation");
					});
					
					jqDocEditor.off("click", ".expanderList > .className");
					jqDocEditor.on("click", ".expanderList > .className", function(){
						var stack = jq(this).closest(".expanderList").data("stack");
						if (jq.type(stack) != "array" || stack.length == 0)
							return;
						
						if (stack.length == 1)
							jq(this).children(".backArrow").remove();

						var head = stack.pop();
						jq(head).trigger("click");
					});
					
					jqDocEditor.off("expanded.staticExpander", ".expanderList");
					jqDocEditor.on("expanded.staticExpander", ".expanderList", function(ev){
						var stack = jq(this).data("stack");
						if (jq.type(stack) == "undefined")
							stack = new Array();
						
						if (stack.length == 0) {
							var backArrow = jq("<span class='backArrow'>&laquo;</span>");
							jq(this).children(".className").prepend(backArrow);
						}
						
						stack.push(ev.target);
						jq(this).data("stack", stack);
					});
					
					jqDocEditor.parents(".documentorContainer").off("click", ".docuTool");
					jqDocEditor.parents(".documentorContainer").on("click", ".docuTool", function(){
						jq(this).text("Update");
						jqDocEditor.trigger("updateSourceModel.documentation");
					});
					
					jqDocEditor.off(".documentation");
					
					jqDocEditor.on("updateSourceModel.documentation", function(){
						jqLinkedEditor.trigger("getCodeEditorContent", [false, function(content){
							var source = content;
							// Decode source - source is ready to be parsed after this line...
							source = jq("<span>").html(source).text();
							
							// Parse file
							sourceModel = parseFile(source);
							sourceModel = jq("<manual>").html(sourceModel);
							
							if (documentationModel.length == 0)
								jqDocEditor.trigger("updateDocumentationModel.documentation")
							else
								jqDocEditor.trigger("update.documentation", true);
						}]);
					});
					
					jqDocEditor.on("updateDocumentationModel.documentation", function(){
						// Check if previous documentation exists
						documentationModel = jq(jq.parseXML(jqDocModel.val())).children("manual");
						
						if (documentationModel.children().length == 0)
							documentationModel = sourceModel.clone();
						
						jqDocEditor.trigger("update.documentation", true);
					});
					
					jqDocEditor.on("update.documentation", function(ev, reveal){
						// Get source model
						var smClone = sourceModel.clone();
						
						// Keep skeleton
						/*smClone.find("properties prop").empty();
						smClone.find("constants const").empty();
						smClone.find("methods method").empty();*/
						
						// Get documentation model
						var dmClone = documentationModel.clone();
						
						// Restore Model if necessary...
						dmClone = restoreModel(dmClone);
/*
						console.log("source");
						console.log(smClone.clone().get(0));
						console.log("doc");
						console.log(dmClone.clone().get(0));
*/					

						// Sync models
						var finalModel = jq();
						// Skeleton from source
						finalModel = finalModel.add(smClone);
						var fmInfoChildren = finalModel.find("class > info").children();
						fmInfoChildren.each(function(){
							jq(this).text(dmClone.find("class > info > "+this.tagName.toLowerCase()).text());
						});
						fmInfoChildren.filter("throws").empty().append(dmClone.find("class > info > throws").children());
					
						// Sync same
						var elements = new Array();
						var dmElements = new Array();
						var elementGroups = ["properties", "constants", "methods"];
						var elementTags = ["prop", "const", "method"];
						
						for (var i = 0; i < elementGroups.length; i++) {
							// ___ Properties / Constants / Methods
							dmElements[i] = dmClone.find("class > "+elementGroups[i]+" "+elementTags[i]);
							elements[i] = finalModel.find("class > "+elementGroups[i]);
							elements[i].find(elementTags[i]).each(function(){
								var tmp = dmElements[i].filter("[name='"+jq(this).attr("name")+"']");
								if (tmp.length > 0) {
									var jqthis = jq(this);
									jqthis.children("arguments, parameters").children("arg, parameter").each(function(){
										var jqarg = jq(this);
										tmp.find("arguments arg, parameters parameter").filter("[name='"+jqarg.attr("name")+"']").each(function(){
											var a = jq(this);
											jqarg.empty();
											jqarg.append(a.children());
											var attributes = a.prop("attributes");
											jq.each(attributes, function() {
												jqarg.attr(this.name, this.value);
											});
										});
									});
									
									jqthis.children().not("arguments, parameters").remove();
									jqthis.prepend(tmp.children("description"));
									jqthis.append(tmp.children().not("description, arguments, parameters"));

//									jqthis.html(tmp.children());
									var attributes = tmp.prop("attributes");
									jq.each(attributes, function() {
										jqthis.attr(this.name, this.value);
									});
									tmp.remove();
								}else{
									jq(this).addClass("newElement");
								}
							});
						}
						
						
						
						for (var i = 0; i < elementGroups.length; i++) {
							// Get New
							// ___ Properties / Constants / Methods 
							elements[i].find(elementTags[i]+".newElement").each(function(index){
								var jqelem = jq(this);
								var chld = dmClone.find("class > "+elementGroups[i]+" "+elementTags[i]).eq(index);
								if (chld.children().length > 0){
									jqelem.empty();
									jqelem.append(chld.children());
									var att = chld.prop("attributes");
									jq.each(att, function() {
										if (this.name != "name")
											jqelem.attr(this.name, this.value);
									});
								}
									
								jqelem.removeAttr("class");
								chld.remove();
							});
						}
						
						// Strip previously deprecated elements
						dmClone.find("discontinued").remove();
						smClone.find("discontinued").remove();
/*	
						console.log("We send this");
						console.log(finalModel.clone().get(0));
*/						
						jqDocModel.val(finalModel.html());
						
						// Dropped
						// ___ Constants
						dmClone.find("class > constants const").append(jq("<discontinued>true</discontinued>")).appendTo(elements[1]);
						
						elements.splice(1, 1);
						elementGroups.splice(1, 1);
						elementTags.splice(1, 1);
						
						for (var i = 0; i < elements.length; i++) {
							// ___ Properties / Methods
							dmClone.find("class > "+elementGroups[i]).find(elementTags[i]).append(jq("<discontinued>true</discontinued>"));
							dmClone.find("class > "+elementGroups[i]).find("scope[type='public'] > "+elementTags[i]).appendTo(elements[i].children("scope[type='public']"));
							dmClone.find("class > "+elementGroups[i]).find("scope[type='protected'] > "+elementTags[i]).appendTo(elements[i].children("scope[type='protected']"));
							dmClone.find("class > "+elementGroups[i]).find("scope[type='private'] > "+elementTags[i]).appendTo(elements[i].children("scope[type='private']"));
						}
						
						documentationModel = finalModel;
/*						
						console.log("We see this");
						console.log(documentationModel.clone().get(0));
*/						
						//jqDocModel.val(documentationModel.html());
						
						if (reveal)
							jqDocEditor.trigger("reveal.documentation");
					});
					
					jqDocEditor.on("reveal.documentation", function(){
						// Reveal final model
						var txt = jq("<div>").append(documentationModel).html();
				/*		
						// This code needs to go to the expander somehow :S
						jqExpanderUnit.add(jqStaticExpanderUnit).off("appendToHead.expander");
						jqExpanderUnit.add(jqStaticExpanderUnit).on("appendToHead.expander", function(ev, content){
							jq(this).children(".expanderHead").append(content);
							jq(this).children(".expanderHead").append("<span class='expanderCounter'></span>");
						});
						jqExpanderUnit.add(jqStaticExpanderUnit).off("appendToBody.expander");
						jqExpanderUnit.on("appendToBody.expander", function(ev, content, c){
							jq(this).children(".expanderBodyWrapper").children().first().append(content);
							jq(this).children(".expanderHead").find(".expanderCounter").text("["+jq(content).children().length+"]");
							if (jq(content).children().not(".expander").length == 0)
								jq(this).addClass("emptyExpander");
						});
						jqStaticExpanderUnit.on("appendToBody.expander", function(ev, content, c){
							jq(this).children(".expanderBodyWrapper").children().first().append(content);
							var specialLength = jq(content).find(".constantTileWrapper, .propertyTileWrapper, .methodTileWrapper").length;
							var count = (specialLength == 0 ? "" : "["+specialLength+"]");
							jq(this).children(".expanderHead").find(".expanderCounter").text(count);
							
							if (specialLength == 0 && jq(content).children().not(".expander").length == 0)
								jq(this).addClass("emptyExpander");
							
						});*/
						// ------------------------------------------------
						
						// Content with nested structure
						jqDocEditor.empty();
						jqDocEditor.append(jqDocModel);
						nestedModel = jq(txt);

						var expanderList = jq("<div class='expanderList'></div>");
						jqDocEditor.append(expanderList);
						
						// __ Class name
						var className = nestedModel.children("class").attr("name");
						expanderList.append("<div class='className'><span>"+className+"</span></div>");
						
						var classElements = jq("<div class='classElements'></div>");
						expanderList.append(classElements);

						// ________ Class info tile
						var classExpander = jqStaticExpanderUnit.clone(true, true);
						var isDeprecated = nestedModel.find("class > info > deprecated:not(:empty)").length != 0;
						var classInfo = jq("<div>").addClass("infoTileWrapper"+(isDeprecated ? " deprecated" : ""));
						
						// Class name
						classInfo.append("<span class='className'>"+className+"</span>");
						
						/*
						// Class namespace
						var classNamespace = nestedModel.find("class").attr("namespace");
						classInfo.append("<div class='classNamespace'><label>namespace: </label><span>"+classNamespace+"</span></div>");
						*/
						
						// Class Abstract
						var classAbstract = nestedModel.find("class").attr("abstract");
						if (classAbstract == "true")
							classInfo.append("<span class='classAbstract'>ABSTRACT</span>");
						
						classInfo.append("<div class='deprecatedOverlay'></div>");
						
						var topRightInfo = jq("<div>").addClass("topRightInfo");
						classInfo.append(topRightInfo);
												
						// Version
						var classVersion = jq.trim(nestedModel.find("class > info > version").text());
						topRightInfo.append("<div class='classVersion'><label>v. </label><span>"+(classVersion == "" ? "&empty;" : classVersion )+"</span></div>");
						
						// Date Created 
						var classCreatedOn = jq.trim(nestedModel.find("class > info > datecreated").text());
						// If numeric
						if (!isNaN(parseFloat(classCreatedOn)) && isFinite(classCreatedOn)) {
							// PHP time is in seconds, js Date needs ms
							classCreatedOn = new Date(classCreatedOn * 1000);
							classCreatedOn = classCreatedOn.format('F j, Y, G:i (T)');
						}
						topRightInfo.append("<div class='classCreated'><label></label><span>"+(classCreatedOn == "" ? "&empty;" : classCreatedOn )+"</span></div>");
						
						// Date Revised
						var classRevisedOn = jq.trim(nestedModel.find("class > info > daterevised").text());
						// If numeric
						if (!isNaN(parseFloat(classRevisedOn)) && isFinite(classRevisedOn)) {
							// PHP time is in seconds, js Date needs ms
							classRevisedOn = new Date(classRevisedOn * 1000);
							classRevisedOn = classRevisedOn.format('F j, Y, G:i (T)');
						}
						topRightInfo.append("<div class='classRevised'><label>rev. </label><span>"+(classRevisedOn == "" ? "&empty;" : classRevisedOn )+"</span></div>");
						
						// Extends / Implements {/ Uses}
						var classRelationsWrapper = jq("<div>").addClass("classRelations");
						classInfo.append(classRelationsWrapper);
						var classExtendsWrapper = jq("<div>").addClass("classExtends");
						classRelationsWrapper.append(classExtendsWrapper);
						var classImplementsWrapper = jq("<div>").addClass("classImplements");
						classRelationsWrapper.append(classImplementsWrapper);
						/*var classUsesWrapper = jq("<div>").addClass("classUses");
						classRelationsWrapper.append(classUsesWrapper);*/
						
						var relations = [ classExtendsWrapper, classImplementsWrapper /*, classUsesWrapper*/ ];
						var relationsTags = [ "extends", "implements" /*, "uses"*/ ];
						
						for (var i = 0; i < relations.length; i++) {
							// __ Extends / Implements
							var classRelations = nestedModel.find("class > info > "+relationsTags[i]).children();
							if (classRelations.length > 0)
								relations[i].append("<label>"+relationsTags[i]+": </label>");
							classRelations.each(function(){
								relations[i].append("<span>"+jq(this).text()+"</span>");
							});
						}
						
						
						// Title / Description / Deprecated
						var classDescInfo = jq("<div>").addClass("classDescription");
						classInfo.append(classDescInfo);
						
						var classTitle = nestedModel.find("class > info > title").text();
						var inp = jq("<input type='text' title='Title' name='class[info][title]' placeholder='Title' />").val(decodeHtml(classTitle));
						classDescInfo.append(inp);
						
						var classDescription = nestedModel.find("class > info > description").text();
						var classExeptions = nestedModel.find("class > info > throws").children("exception");
						
						classDescInfo.append("<textarea title='Description' name='class[info][description]' placeholder='Description'>"+classDescription+"</textarea><input type='text' title='Exceptions' class='classThrows' placeholder='Exceptions Thrown' />");
						classDescInfo.find(".classThrows").attr("name", "class[info][throws]").data("multipleObjects", "exception");
						if (classExeptions.length != 0) {
							var newContainer = jq("<div class='objectsWrapper'></div>");
							classExeptions.each(function(){ 
								newContainer.append(jq("<div class='objectBox'>"+jq(this).text()+"</div>"));
							});
							classDescInfo.find(".classThrows").addClass("noDisplay").after(newContainer);
							
						}
						
						var classDeprecated = nestedModel.find("class > info > deprecated").text();
						inp = jq("<input type='text' name='class[info][deprecated]' placeholder='Description' class='deprecatedDescription' />").val(decodeHtml(classDeprecated));
						var dt = jq("<div class='deprecatedTile'><div class='deprecatedLabel'><label>Deprecated</label><input type='checkbox' name='class[info][deprecated]' value='deprecated' "+(isDeprecated ? "checked='checked'" : "")+" /></div></div>");
						dt.append(inp);
						classInfo.append(dt);
						
						classExpander.trigger("appendToHead", jq("<span>Info</span>"));
						classExpander.trigger("appendToBody", [ jq("<div>").append(classInfo), false ]);
						classElements.append(classExpander);
						
						
						// __ Constants info
						var constantsInfoExpander = jqStaticExpanderUnit.clone(true, true);
						var constantsCollection = jq("<div>");
						
						var classConstants = nestedModel.find("class > constants");
						var constantsExpander = jqStaticExpanderUnit.clone(true, true);
						classConstants.children().each(function(){
							var isDiscontinued = jq(this).children("discontinued:contains(true)").length != 0;
							var constantsInfo = jq("<div>").addClass("constantTileWrapper"+(isDiscontinued ? " discontinued":""));
							constantsInfo.append((isDiscontinued ? "<div class='discontinuedOverlay'></div>" : "" )+"<span class='constantName'>"+jq(this).attr("name")+"</span>");
							inp = jq("<input type='text' title='Type' class='constantType' placeholder='Type' />").attr("name", "class[constants]["+jq(this).attr("name")+"][type]").val(decodeHtml(jq(this).attr("type")));
							constantsInfo.append(inp);
							inp = jq("<input type='text' title='Description' class='constantDescription' placeholder='Description' />").attr("name", "class[constants]["+jq(this).attr("name")+"][description]").val(decodeHtml(jq(this).children("description").text()));
							constantsInfo.append(inp);
							constantsInfo.append((isDiscontinued ? "<div class='discontinuedTile'><label>Discontinued</label></div>" : "" ));
							constantsCollection.append(constantsInfo);
						});
						constantsExpander.trigger("appendToHead", jq("<span>Constants</span>"));
						constantsExpander.trigger("appendToBody", [ constantsCollection, ".constantTileWrapper" ]);
						classElements.append(constantsExpander);
						
						
						var scopeTags = [ "Public", "Protected", "Private" ];
						
						// __ Properties info
						var propertiesInfoExpander = jqStaticExpanderUnit.clone(true, true);
						var propertiesInfoWrapper = jq("<div>");
						
						var classProperties = nestedModel.find("class > properties");
						//console.log(classProperties.get(0));
						for (var i = 0; i < scopeTags.length; i++) {
							var propertiesExpander= jqExpanderUnit.clone(true, true);
							var propertiesCollection = jq("<div>");
							classProperties.children("scope[type='"+scopeTags[i].toLowerCase()+"']").children().each(function(){
								var isDiscontinued = jq(this).children("discontinued:contains(true)").length != 0;
								var propertiesInfo = jq("<div>").addClass("propertyTileWrapper"+(isDiscontinued ? " discontinued":""));
								propertiesInfo.append((isDiscontinued ? "<div class='discontinuedOverlay'></div>" : "" )+"<span class='propertyName'>"+jq(this).attr("name")+"</span>"+(jq(this).attr("static") == "true" ? "<span class='propertyStatic'>STATIC</span>" : ""));
								inp = jq("<input type='text' title='Type' class='propertyType' placeholder='Type' />").attr("name", "class[properties]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][type]").val(decodeHtml(jq(this).attr("type")));
								propertiesInfo.append(inp);
								inp = jq("<input type='text' title='Description' class='propertyDescription' placeholder='Description' />").attr("name", "class[properties]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][description]").val(decodeHtml(jq(this).children("description").text()));
								
								propertiesInfo.append(inp);
								propertiesInfo.append((isDiscontinued ? "<div class='discontinuedTile'><label>Discontinued</label></div>" : "" ));
								propertiesCollection.append(propertiesInfo);
							});
							propertiesExpander.trigger("appendToHead", jq("<span>"+scopeTags[i]+"</span>"));
							propertiesExpander.trigger("appendToBody", [ propertiesCollection, ".propertyTileWrapper" ]);
							propertiesInfoWrapper.append(propertiesExpander);
						}
						propertiesInfoExpander.trigger("appendToHead", jq("<span>Properties</span>"));
						propertiesInfoExpander.trigger("appendToBody", [ propertiesInfoWrapper, ".propertyTileWrapper" ]);
						classElements.append(propertiesInfoExpander);
						
						
						
						// __ Methods info
						var methodsInfoExpander = jqStaticExpanderUnit.clone(true, true);
						var methodsInfoWrapper = jq("<div>");
						
						var classMethods = nestedModel.find("class > methods");
						for (var i = 0; i < scopeTags.length; i++) {
							var methodsExpander = jqExpanderUnit.clone(true, true);
							var methodsCollection = jq("<div>");
							classMethods.children("scope[type='"+scopeTags[i].toLowerCase()+"']").children().each(function(){
								var isMethodDeprecated = jq(this).children("deprecated:not(:empty)").length != 0;
								var isDiscontinued = jq(this).children("discontinued:contains(true)").length != 0;
								var jqmethod = jq(this);
								var args = jqmethod.find("arguments > arg, parameters > parameter");
								var methodExeptions = jqmethod.find("throws > exception");
								var methodsInfo = jq("<div>").addClass("methodTileWrapper"+(isDiscontinued ? " discontinued":"")+(isMethodDeprecated ? " deprecated":""));
								methodsInfo.append("<div class='"+(isDiscontinued ? "discontinuedOverlay" : "deprecatedOverlay" )+"'></div><div class='methodName'><input type='text' title='Return Type' class='methodReturnType' placeholder='Return Type' /><span>"+jqmethod.attr("name")+"</span></div>"+(jq(this).attr("static") == "true" ? "<span class='methodStatic'>STATIC</span>" : "")+(jq(this).attr("abstract") == "true" ? "<span class='methodAbstract'>ABSTRACT</span>" : "")+"<textarea title='Description' class='methodDescription' placeholder='Description'>"+jqmethod.children("description").text()+"</textarea>"+"<textarea title='Return Value Description' class='methodReturnDescription' placeholder='Return Value Description'>"+jqmethod.children("returndescription").text()+"</textarea>"+"<input type='text' title='Exceptions' class='methodThrows' placeholder='Exceptions Thrown' />"+(args.length > 0 ? "<label class='argumentsHeader'>Parameters</label>" : "" ));
								methodsInfo.find(".methodReturnType").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][returntype]").val(decodeHtml(jq(this).attr("returntype"))).end()
									.find(".methodDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][description]").end()
									.find(".methodReturnDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][returndescription]").end()
									.find(".methodThrows").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][throws]").data("multipleObjects", "exception");
									
								if (methodExeptions.length != 0) {
									var newContainer = jq("<div class='objectsWrapper'></div>");
									methodExeptions.each(function(){ 
										newContainer.append(jq("<div class='objectBox'>"+jq(this).text()+"</div>"));
									});
									methodsInfo.find(".methodThrows").addClass("noDisplay").after(newContainer);	
								}

								var argumentsCollection = jq("<div>");
								args.each(function(){
									var argumentsInfo = jq("<div>").addClass("argumentTileWrapper");
									argumentsInfo.append("<span class='argumentName'>"+jq(this).attr("name")+"</span><input type='text' title='Type' class='argumentType' placeholder='Type' /><span class='argumentDefault'>"+jq(this).attr("defaultvalue")+"</span><textarea title='Description' class='argumentDescription' placeholder='Description'>"+jq(this).children("description").text()+"</textarea>");
									argumentsInfo.find(".argumentType").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][parameters]["+jq(this).attr("name")+"][type]").val(decodeHtml(jq(this).attr("type"))).end()
										.find(".argumentDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][parameters]["+jq(this).attr("name")+"][description]");
									argumentsCollection.append(argumentsInfo);
								});
								methodsInfo.append(argumentsCollection);
								methodsInfo.append("<div class='"+(isDiscontinued ? "discontinuedTile" : "deprecatedTile" )+"'>"+(isMethodDeprecated ? "<div class='deprecatedLabel'>" : "" )+"<label>"+(isDiscontinued ? "Discontinued" : "Deprecated" )+"</label><input type='checkbox' value='deprecated' "+(isMethodDeprecated ? "checked='checked'":"")+" />"+(isMethodDeprecated ? "</div>" : "" )+"<input type='text' placeholder='Description' title='Reason for Deprecation' class='deprecatedDescription' /></div>");
								methodsInfo.find("[value='deprecated']").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][deprecated]").end()
									.find(".deprecatedDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][deprecated]").val(decodeHtml(jq(this).children("deprecated").html()));
								methodsCollection.append(methodsInfo);
							});
							methodsExpander.trigger("appendToHead", jq("<span>"+scopeTags[i]+"</span>"));
							methodsExpander.trigger("appendToBody", [ methodsCollection, ".methodTileWrapper" ]);
							methodsInfoWrapper.append(methodsExpander);
						}
						methodsInfoExpander.trigger("appendToHead", jq("<span>Methods</span>"));
						methodsInfoExpander.trigger("appendToBody", [ methodsInfoWrapper, ".methodTileWrapper" ]);
						classElements.append(methodsInfoExpander);
						
						// This should be handled by the expander :S
						jqDocEditor.find(".emptyExpander").remove();
						
						// Better way????????????
						jqDocEditor.find("[name]").not("[name='classXMLModel']").each(function(){
							var jqt = jq(this);
							var sel = jqt.attr("name")
									.replace(/\[/g, " > ")
									.replace(/\]/g, "")
									.replace(/(methods|properties) \> (public|protected|private) \> ([\w]*) \> ([^ ]*)/, function(m, m1, m2, m3, m4, o, s){
										if (m4 == "returntype" || m4 == "type"){
											jqt.data("docElemSelectorAttr", m4);
											return m1+" > "+m2+" > "+m3;
										}else{
											return m1+" > "+m2+" > "+m3+" > "+m4;
										}
									})
									.replace(/constants \> ([\w]*) \> ([^ ]*)/, function(m, m1, m2, o, s){
										if (m2 == "returntype" || m2 == "type"){
											jqt.data("docElemSelectorAttr", m2);
											return "constants > "+m1;
										}else{
											return "constants > "+m1+" > "+m2;
										}
									})
									.replace(/(properties|methods) \> (public|protected|private) \> ([\w]*)/, function(m, m1, m2, m3, o, s){
										return m1+" > scope[type='"+m2+"'] > [name='"+m3+"']";
									})
									.replace(/constants \> ([\w]*)/, function(m, m1, o, s){
										return "constants > [name='"+m1+"']";
									})
									.replace(/methods \> (([^\>]*\>){2} parameters) \> ([^ ]*) \> ([\w]*)/, function(m, m1, m2, m3, m4, o, s){
										if (m4 == "type"){
											jqt.data("docElemSelectorAttr", m4);
											return "methods > "+m1+" > [name='"+m3+"']";
										}else{
											return "methods > "+m1+" > [name='"+m3+"'] > "+m4;
										}
									});
							jqt.data("docElemSelector", sel);
						});
					});
					//jqDocEditor.trigger("updateSourceModel.documentation");					
					
				});
			}
		}
		
		function restoreModel(model) {
		
			// Class info
			manualPrototypes.info.children().each(function(){
				var tag = jq(this).get(0).tagName;
				model.find("class > info").filter(function(){
					return jq(this).children(tag).length == 0
				}).append(jq("<"+tag+" />"));
				
			});
			
			// Constants
			manualPrototypes.constant.children().each(function(){
				var tag = jq(this).get(0).tagName;
				model.find("class > constants const").filter(function(){
					return jq(this).children(tag).length == 0
				}).append(jq("<"+tag+" />"));
			});
			
			// Properties
			manualPrototypes.property.children().each(function(){
				var tag = jq(this).get(0).tagName;
				model.find("class > properties prop").filter(function(){
					return jq(this).children(tag).length == 0
				}).append(jq("<"+tag+" />"));
			});
			
			// Methods
			manualPrototypes.method.children().each(function(){
				var tag = jq(this).get(0).tagName;
				model.find("class > methods method").filter(function(){
					return jq(this).children(tag).length == 0
				}).append(jq("<"+tag+" />"));
			});
			
			// Parameters
			manualPrototypes.parameter.children().each(function(){
				var tag = jq(this).get(0).tagName;
				model.find("class > methods parameters parameter").filter(function(){
					return jq(this).children(tag).length == 0
				}).append(jq("<"+tag+" />"));
			});
			
			return model;
		}
		
		function escapeHtml(unsafe) {
		    return unsafe
		         .replace(/&/g, "&amp;")
		         .replace(/</g, "&lt;")
		         .replace(/>/g, "&gt;")
		         .replace(/"/g, "&quot;")
		         .replace(/'/g, "&#039;");
		}
		
		function decodeHtml(text) {
		    return jq("<div>").html(text).text();
		}
		
		function parseFile(content) {
			var start = new Date().getTime();
			var m = manualModel.clone();
			var c = content;
			
			
			// dotall: . = [\s\S]
			// line break: (\r\n|\n|\r)
	
			// replace php tags with "" [global]
			var stripRegexp = /^([ \t]*<\?php[ \t]*[\r\n]*)|^([ \t]*<\?[ \t]*[\r\n]*)|([\r\n]*[ \t]*\?>[ \t]*)$/g;
			c = c.replace(stripRegexp, "");
			
			// get first class [global, dotall] (not sure. needs more specific regex?)
			var c = c.match(/[\s\S]*?\bclass\b[\s\S]*?\{[\s\S]*\}/g);
			c = (jq.type(c) == "null" ? "" : c[0]);
			
			// get name, extends, implements [global, dotall]
			var cl = c.match(/^[\t ]*(abstract)?[\t ]*\bclass\b[ \t]*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[\w, (\r\n|\n|\r)]*(?=\{[\s\S]*\})/gm);
			cl = (jq.type(cl) == "null" ? "" : cl[0]);
			className = cl.match(/\bclass\b[ \t]+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/);
			className = (jq.type(className) == "null" ? "" : className[0]);
			className = className.replace(/\bclass\b[ \t]+/, "");
			m.attr("name", className);
			
			// Abstract
			classAbstract = cl.match(/^[\t ]*abstract[\t ]*\bclass\b/m);
			classAbstract = (jq.type(classAbstract) == "null" ? "" : "true");
			m.attr("abstract", classAbstract);
			
			// get namespace [global, multiline]
			var namespace = c.match(/^[ \t]*\bnamespace\b[ \w\\\t]+/gm);
			namespace = (jq.type(namespace) == "null" ? "" : namespace[0]);
			namespace = namespace.replace(/[ \t]*\bnamespace\b[ \t]*/g, "");
			m.attr("namespace", namespace);
			
			// get preclass stuff [global, dotall] [this takes its sweet time...]
			var preclass = c.match(/[\s\S]+(?=class[ \t]*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[\w, (\r\n|\n|\r)]*?(?=\{[\s\S]*\}))/g);
			preclass = (jq.type(preclass) == "null" ? "" : preclass[0]);
			
			// get description line [i,m]
			//var classDescription = preclass.match(/(^[\t ]*\/\/.*[\r\n]{1,2})+|(^[\t ]*(\/\*)(?:(?!(\*\/))[^\\]|\\[\w\W])*(\*\/))/gim);
			//classDescription = (jq.type(classDescription) == "null" ? "" : classDescription[classDescription.length-1]);
			//classDescription = classDescription.replace(/^[\t ]*\/\/[\t ]*|^[\t ]*[\/]?\*[\t ]*/gm, "");
			//m.find("info > description").text(classDescription);
			
			// get implements
			var impl = cl.match(/\bimplements\b[ \t]+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*([ \t]*,[ \t]*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*/);
			impl = (jq.type(impl) == "null" ? "" : impl[0]);
			
			// get uses [i,m]
			var uses = preclass.match(/^[ \t]*\buse\b[ \\\w]*/gim);
			if (jq.type(uses) != "null"){
				for (var i=0; i < uses.length; i++){
					var use = jq.trim(uses[i]);
					var last = use.match(/\b[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\b$/);
					use = use.replace(/\buse\b[ \t]*/, "").replace(/[ \t]*\bas\b.*/i, "");
					if (impl.indexOf(last) == -1){
						// class
						m.find("info > uses").append("<object>"+use+"</object>")
					}else{
						// interface
						m.find("info > uses").append("<object>"+use+"</object>");
					}
				}
			}
			
			// get extend
			ext = cl.match(/\bextends\b[ \t]+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/);
			ext = (jq.type(ext) == "null" ? "" : ext[0]);
			if (ext != "") {
				ext = ext.replace(/\bextends\b[ \t]+/, "");
				ext = preclass.match(new RegExp("[ \\t]*use.*"+ext+";", "g"));
				ext = (jq.type(ext) != "null" && ext.length == 1 ? ext[0] : "");
				ext = ext.replace(/[ \t]*\buse\b[ \t]*/, "").replace(/([ \t]*\bas\b.*)?;/i, "");		
				m.find("info > extends").append("<object>"+ext+"</object>");
			}

			// implements
			if (impl != ""){
				impl = impl.replace(/\bimplements\b[ \t]+/, "");
				impl = impl.split(",");
				for (var i=0; i < impl.length; i++){
					var iface = jq.trim(impl[i]);
					iface = preclass.match(new RegExp("[ \\t]*use.*"+iface+";", "g"));
					iface = (jq.type(iface) != "null" && iface.length == 1 ? iface[0] : "");
					iface = iface.replace(/[ \t]*\buse\b[ \t]*/, "").replace(/([ \t]*\bas\b.*)?;/i, "");
					m.find("info > implements").append("<object>"+iface+"</object>");
				}
			}
			
			var postclass = c.replace(preclass, "");
			// get properties [m]
			var classProperties = postclass.match(/([ \t]*\/\/.*[\n\r]*|[ \t]*(\/\*([\w\W](?!(\*\/)))*(.\*\/)?)[ \t]*[\r\n]*)?^[ \t]*(public|private|protected)[ \t]*(static)?[ \t]*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/gm)
			if (jq.type(classProperties) != "null"){
				for (var i=0; i < classProperties.length; i++){
					// name
					var prop = classProperties[i].replace(/(^[\t ]*\/\/.*[\r\n]{1,2})+|(^[\t ]*(\/\*)(?:(?!(\*\/))[^\\]|\\[\w\W])*(\*\/)[\r\n]*)/, "").match(/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/);	
					var prop = prop[0].substr(1);
					var static = classProperties[i].indexOf("static") != -1;
					// description
					//var desc = classProperties[i].match(/(^[\t ]*\/\/.*[\r\n]{1,2})+|(^[\t ]*(\/\*)(?:(?!(\*\/))[^\\]|\\[\w\W])*(\*\/)[\r\n]*)/);
					//desc = (jq.type(desc) == "null" ? "" : desc[0]);
					//desc = jq.trim(desc.replace(/.*\/\//, ""));
					// scope
					var scope = classProperties[i].replace(/(^[\t ]*\/\/.*[\r\n]{1,2})+|(^[\t ]*(\/\*)(?:(?!(\*\/))[^\\]|\\[\w\W])*(\*\/)[\r\n]*)/, "").match(/(\bpublic\b|\bprivate\b|\bprotected\b)/);
					scope = (jq.type(scope) == "null" ? "public" : jq.trim(scope[0]));
					
					var p = manualPrototypes.property.clone();
					if (static)
						p.attr("static", "true");
					//p.children("description").text(desc);
					p.attr("name", prop);
					m.find("properties > scope[type='"+scope+"']").append(p);
				}
			}
		
			// get constants
			var classConstants = postclass.match(/([ \t]*\/\/.*[\n\r]*|[ \t]*(\/\*([\w\W](?!(\*\/)))*(.\*\/)?)[ \t]*[\r\n]*)?^[ \t\w]*\bconst\b.*(?=;)/gm);
			if (jq.type(classConstants) != "null"){
				for (var i=0; i < classConstants.length; i++){
					// name
					var cons = classConstants[i].match(/\bconst\b[ \t]+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/);
					cons = cons[0];
					var cons = cons.match(/\b[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\b$/);
					cons = cons[0];
					
					var c = manualPrototypes.constant.clone();
					c.attr("name", cons);
					m.children("constants").append(c);
					// description
					//var desc = classConstants[i].match(/(^[\t ]*\/\/.*[\r\n]{1,2})+|(^[\t ]*(\/\*)(?:(?!(\*\/))[^\\]|\\[\w\W])*(\*\/)[\r\n]*)?/);
					//desc = (jq.type(desc) == "null" ? "" : desc[0]);
					//desc = jq.trim(desc.replace(/.*\/\//, ""));
					//c.children("description").text(desc);
					// value
					/*var val = classConstants[i].match(new RegExp("\\bconst\\b[ \\t]+"+cons+"[ \\t]*=.*"));
					val = val[0];
					val = jq.trim(val.replace(new RegExp("\\bconst\\b[ \\t]"+cons+"[ \\t]="), ""));
					m.children("constants").find("[name='"+cons+"']").children("value").text(val);*/
				}
			}
			
			// get methods
			var classMethods = postclass.match(/^([ \t]*\/\/.*[\n\r]*|[ \t]*(\/\*([\w\W](?!(\*\/)))*(.\*\/))[ \t]*[\r\n]*)?[\r\n\t ]*[\t ]*(final)?[\t ]*(abstract)?[\t ]*(public|private|protected)[\t ]*(static)?[\t ]*\bfunction\b.*\(.*\)/gm);
			if (jq.type(classMethods) != "null"){
				for (var i=0; i < classMethods.length; i++){
					// name
					var methodSignature = classMethods[i].replace(/(([ \t]*\/\/.*[\r\n]*)*|\/\*.*([ \n\r\t]*\*.*)*)(\r\n|\n|\r)/, "");
					var methodName = methodSignature.match(/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[ \t]*\(/);
					methodName = jq.trim(methodName[0].substr(0, methodName[0].length-1));
					/*if (methodName == "__construct")
						continue;*/
					// static
					var static = methodSignature.indexOf("static") != -1;
					// abstract
					var abstract = methodSignature.match(/^.*?abstract.*?function/m);
					abstract = (jq.type(abstract) != "null" ? true : false);
					
					// deprecated
					var depr = classMethods[i].match(/^[ \t\/]*(\*)?[ \t]*\bdeprecated\b.*/im);
					// description
					//var methodDescription = classMethods[i].match(/(([ \t]*\/\/.*[\r\n]*)*(\r\n|\n|\r)|\bDescription\b[ \t]*:[ \t]*.*)/g);
					//methodDescription = (jq.type(methodDescription) == "null" ? "" : jq.trim(methodDescription.join("")));
					//methodDescription = jq.trim(methodDescription.replace(/^[ \t]*\/\/|^\bDescription\b[ \t]*:[ \t]*/gm, ""));
					// scope
					var methodScope = methodSignature.match(/\bpublic\b|\bprivate\b|\bprotected\b/);
					methodScope = (jq.type(methodScope) == "null" ? "public" : methodScope[0]);
					// return value
					var methodReturn = classMethods[i].match(/Returns[ \t]*:.*/i);
					methodReturn = (jq.type(methodReturn) == "null" ? "void" : methodReturn[0]);
					methodReturn = jq.trim(methodReturn.replace(/^\bReturns\b[ \t]*:[ \t]*/gm, ""));
					var parentElement = m.find("methods > scope[type='"+methodScope+"']");
					
					var meth = manualPrototypes.method.clone();
					if (static)
						meth.attr("static", "true");
					if (abstract)
						meth.attr("abstract", "true");
					//meth.children("description").text(methodDescription);
					meth.attr("name", methodName);
					meth.attr("returntype", methodReturn);
					
					parentElement.append(meth);
					
					// arguments
					var arguments = methodSignature.match(/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/g);
					if (jq.type(arguments) != "null"){
						for (var j=0; j < arguments.length; j++){
							// name
							var arg = "";
							var argName = arguments[j];
							//var argDefault = methodSignature.match(new RegExp("\\b"+argName+"\\b[\t ]*\=([^\,\)])"));
							//argName = argName[0].substr(1);
							 
							var par = manualPrototypes.parameter.clone();
							par.attr("name", escapeHtml(argName));
							meth.children("parameters").append(par);
							
							// desc
							//var argDesc = arg.match(/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[ \t]*\-.*/);
							//argDesc = (jq.type(argDesc) == "null" ? "" : argDesc[0]);
							//argDesc = jq.trim(argDesc.replace(/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[ \t]*\-[ \t]*/, ""));
							//par.children("description").text(argDesc);
							
							// type
							var argType = arg.match(/type[ \t]*:.*/i);
							argType = (jq.type(argType) == "null" ? "" : argType[0]);
							argType = jq.trim(argType.replace(/type[ \t]*:[ \t]*/, ""));
							par.attr("type", argType);
							
							// default value
							var argValue = arg.match(/value[ \t]*:.*/i);
							argValue = (jq.type(argValue) == "null" ? "" : argValue[0]);
							argValue = jq.trim(argValue.replace(/value[ \t]*:[ \t]*/, ""));
							par.attr("defaultvalue", argValue);
						}
					}
				}
			}
	
/*			while (m.find(":empty").length > 0)
				m.find(":empty").remove();
*/
			return m;
		}
		
		// register plugin
		jq.fn.documentor = function(method) {
			//calling method logic
			if (typeof(method) == "string") {
				return methods.init.apply(this, arguments);
			} else if (methods[ method ]) {
				return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if (jq.type(method) === 'object' || !method) {
				return methods.init.apply(this, arguments);
			} else {
				jq.error('Method ' +  method + ' does not exist in jQuery.documentor');
			}
		};
	
	})(jQuery);
	
	// Get css properties with ajax (temp solution)
	function getManualModel() {
		ascop.asyncRequest(
			"/ajax/resources/sdk/documentor/model.php", 
			"GET",
			null,
			"xml",
			this,
			handleModelInfo
		);
	}
	
	function handleModelInfo(xml) {
		var jqxml = jq(xml);
		var jqproto = jqxml.find("class > prototypes").remove();
		
		manualModel = jqxml.children("class");
		
		manualPrototypes.info = manualModel.children("info").clone();
		manualPrototypes.constant = jqproto.children("const");
		manualPrototypes.property = jqproto.children("prop");
		manualPrototypes.method = jqproto.children("method");
		manualPrototypes.parameter = jqproto.children("parameter");
		
		wasInitiated = true;
		jq(document).trigger("content.modified.documentor");
	}
	
});