var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	var manualModel = jq();
	var manualPrototypes = new Object();
	wasInitiated = false;
	
	// Load manual model
	getManualModel();
	jq(document).on("content.modified.classDocumentor", function() {
		if (!wasInitiated ||
			!jq(document).data("classDocEditorInit") ||
			!jq(document).data("classCommentsInit"))
			return;
		
		var documentorWrappers = jq(".init[data-wrapper='documentor']");
		documentorWrappers.each(function() {
			jq(this).removeClass("init").classDocumentor("init");
		});
	});
	
	if (jq.fn.classDocumentor) {
		return;
	}
	
	(function(jq){
	
		var methods = {
			init : function(){
				var jqDocWrapper = jq(".documentorContainer", this);
				var jqDocEditor = jqDocWrapper.find(".documentor");
				var jqDocModel = jqDocEditor.find("#classXMLModel").first();
				
				jqDocWrapper.data("manualModel", manualModel);
				var sourceModel = jq();
				jqDocWrapper.data("sourceModel", sourceModel);
				var documentationModel = jq();
				jqDocWrapper.data("documentationModel", documentationModel);
				var initialModel = jq();
				jqDocWrapper.data("initialModel", initialModel);
				
				if (jqDocEditor.length == 0)
					return;
			
				return this.each(function(){
					jqDocEditor.off(".documentation");
					
					jqDocEditor.on("updateDocumentationModel.documentation", function(){
						sourceModel = jqDocWrapper.data("sourceModel");
						// Check if previous documentation exists
						documentationModel = jq(jq.parseXML(jqDocModel.val())).children("manual");
						
						jqDocWrapper.data("initialModel", documentationModel);
						if (documentationModel.children().length == 0)
							documentationModel = sourceModel.clone();
						
						jqDocWrapper.data("documentationModel", documentationModel);
						jqDocEditor.trigger("update.documentation", true);
					});
					
					jqDocEditor.on("update.documentation", function(ev, reveal){
						
						sourceModel = jqDocWrapper.data("sourceModel");
						documentationModel = jqDocWrapper.data("documentationModel");
						
						// Get source model
						var smClone = sourceModel.clone();
						
						// Get documentation model
						var dmClone = documentationModel.clone();
						
						// Restore Model if necessary...
						dmClone = restoreModel(dmClone);

						// Sync models
						var finalModel = jq();
						// Skeleton from source
						finalModel = finalModel.add(smClone);
						
						var fmInfoChildren = finalModel.find("class > info").children();
						fmInfoChildren.each(function(){
							if (this.tagName != "extends" && this.tagName != "extends")
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
						
						finalModel.find("class").removeAttr("namespace");
						// Update version
						if (jqDocWrapper.data("checkVersion") === true){
							// Check differences between initialModel and finalModel
							// Call check function
							var v = checkVersion(jqDocWrapper.data("initialModel"), finalModel);
							
							// Update version in finalModel
							finalModel.find("class > info > version").text(v.version);
							finalModel.find("class > info > build").text(v.build);
							
							jqDocWrapper.data("checkVersion", false);
						}
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
						jqDocWrapper.data("documentationModel", documentationModel);
						
						if (reveal)
							jqDocEditor.trigger("reveal.documentation");
					});					
					
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
		
		
		// register plugin
		jq.fn.classDocumentor = function(method) {
			//calling method logic
			if (typeof(method) == "string") {
				return methods.init.apply(this, arguments);
			} else if (methods[ method ]) {
				return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
			} else if (jq.type(method) === 'object' || !method) {
				return methods.init.apply(this, arguments);
			} else {
				jq.error('Method ' +  method + ' does not exist in jQuery.classDocumentor');
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
			handleModelInfo,
			null,
			true,
			true
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
		
		jq(document).data("docManualPrototypes", manualPrototypes);
		
		wasInitiated = true;
		jq(document).trigger("content.modified.classDocumentor");
	}
	
	function checkVersion(oldModel, newModel){	
		// Get old version
		var version = jq.trim(oldModel.find("class > info > version").text());
		var build = jq.trim(oldModel.find("class > info > build").text());
		var v = { 
			"version" : "0.1",
			"build" : "1",
			"update" : function(updateMajor){
				var varr = this.version.split(".");
				var major = varr[0];
				var minor = varr[1];
				
				this.version = (updateMajor === true ? (++major)+".0" : major+"."+(++minor));
				this.build = "1";
			}
		};
		
		if (!version || !build)
			return v;
		
		v.version = version;
		v.build = build;
		
		// Check for API changes
		var oldPublic = oldModel.find("class > methods > scope[type='public'] > method");
		var newPublic = newModel.find("class > methods > scope[type='public'] > method");
		
		var update = compareMethods(oldPublic, newPublic);
		if (update){
			// If update = 'minor' the public API was updated in a backwards compatible way
			v.update((update === true ? true : false));
			return v;
		}
		
		// Check for Minor changes
		// Check if __construct exists in both
		// Check if there are public deprecated methods
		if (oldPublic.filter("[name='__construct']").length != newPublic.filter("[name='__construct']").length ||
				oldPublic.find("deprecated:not(:empty)").length != newPublic.find("deprecated:not(:empty)").length){
			v.update();
			return v;
		}
		
		// Check if there are changes in non-public methods
		var oldPrivate = oldModel.find("class > methods > scope[type='private'] > method");
		var newPrivate = newModel.find("class > methods > scope[type='private'] > method");
		var oldProtected = oldModel.find("class > methods > scope[type='protected'] > method");
		var newProtected = newModel.find("class > methods > scope[type='protected'] > method");
		if (compareMethods(oldPrivate, newPrivate) || 
				compareMethods(oldProtected, newProtected)){
			v.update();	
			return v;
		}
		
		// Update build
		v.build = ++build;
		
		return v;
	}
	
	function compareMethods(oldMethods, newMethods){
		if (oldMethods.filter("[name='__construct']").find("parameter").length != newMethods.filter("[name='__construct']").find("parameter").length)
			return true;
			
		if (oldMethods.not("[name='__construct']").length != newMethods.not("[name='__construct']").length)
			return true;
			
		var update = false;
		oldMethods.each(function(){
			var jqthis = jq(this);
			var name = jqthis.attr("name");
			var rtype = jqthis.attr("returntype");
			
			var m = newMethods.filter("[name='"+name+"'][returntype='"+rtype+"']");
			if (m.length == 0 && name != "__construct"){
				update = true;
				return false;
			}
			
			var oldParams = "";
			jqthis.find("parameter").each(function(){
				oldParams += jq(this).attr("type")+" ";
			});
			
			var newParams = "";
			m.find("parameter").each(function(){
				newParams += jq(this).attr("type")+" ";
			});
			
			if (newParams.indexOf(oldParams) != 0){
				update = true;
				return false;
			}
			if (newParams != oldParams){
				update = "minor";
				return false;
			}
		});
		
		return update;
			
	}
	
});