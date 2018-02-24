var jq=jQuery.noConflict();

jq(document).one("ready.extra", function() {
	jq(document).data("classDocEditorInit", true);
	jq(document).trigger("content.modified.classDocumentor");
	
	var types = jq("<span replacement='string'>str|string</span><span replacement='integer'>integer|int</span><span replacement='float'>float|double|real</span><span replacement='array'>array</span><span replacement='object'>object</span><span replacement='void'>void</span><span replacement='boolean'>boolean|bool|true|false</span><span replacement='resource'>resource</span><span replacement='NULL'>null</span><span replacement='callback'>callback|function</span><span replacement='mixed'>mixed</span><span replacement='number'>number</span>");
	
	jq(document).on("click", ".documentor .docToggleWrap", function(ev){
		ev.stopPropagation();
		jq(this).closest(".documentor").find(".docToggleWrap.elevated").removeClass("elevated");
		jq(this).addClass("elevated");
	});
	
	jq(document).on("click", ".documentor .deprecatedTile label,.documentor .deprecatedTile [type='checkbox']", function(ev){
		var jqthis = jq(this).closest(".deprecatedTile");
		var chk = jqthis.find("[type='checkbox']");
		var checked = chk.prop("checked");
		jq(ev.target).not("[type='checkbox']").each(function(){
			chk.prop("checked", !checked);
		});
		
		jqthis.parent().toggleClass("deprecated");
	});
	
	jq(document).on("click", ".documentorContainer .documentor .objectsWrapper", function(){
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
	
	jq(document).on("focusout", ".documentorContainer .documentor [name]:not([type='checkbox'])", function(){
		if (jq.type(jq(this).data("multipleObjects")) == "undefined"
			|| jq(this).next(".objectsWrapper").length != 0) 
			return;
			
		var val = jq.trim(jq(this).val().replace(/[, \t]+/g," "));
		if (val == "")
			return;
		
		var objects = val.split(" ");
		var newContainer = jq("<div title='Exceptions Thrown' class='objectsWrapper'></div>");
		for (var i = 0; i < objects.length; i++) {
			newContainer.append(jq("<div class='objectBox'>"+objects[i]+"</div>"));
		}
		jq(this).addClass("noDisplay").after(newContainer);
	});
	
	jq(document).on("change", ".documentorContainer .documentor [name]:not([type='checkbox'])", function(){
		var jqDocWrapper = jq(this).closest(".documentorContainer");
		var documentationModel = jqDocWrapper.data("documentationModel");
		
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
		jqDocWrapper.data("documentationModel", documentationModel);
		jq(this).trigger("update.documentation");
	});
	
	jq(document).on("change", ".documentorContainer .documentor [type='checkbox']", function(){
		var jqDocWrapper = jq(this).closest(".documentorContainer");
		var documentationModel = jqDocWrapper.data("documentationModel");
		
		var modelSelector = jq(this).data("docElemSelector");
		var name = jq(this).attr("name");
		var represent = jq(this).parent().parent().find("[name='"+name+"']").not(this).first();
		var val = (jq(this).attr("checked") == "checked" ? represent.val() : "");
		
		if (jq.type(jq(this).data("docElemSelectorAttr")) == "undefined")
			documentationModel.find(modelSelector).text(jq("<div>").text(val).html())
		else
			documentationModel.find(modelSelector).attr(jq(this).data("docElemSelectorAttr"), val);
			
		jqDocWrapper.data("documentationModel", documentationModel);
		jq(this).trigger("update.documentation");
	});
	
	jq(document).on("click", ".documentorContainer .docuTool", function(){
		jq(this).closest(".documentorContainer").data("checkVersion", true);
		jq(this).siblings(".documentor").trigger("updateSourceModel.documentation");
	});
	
	jq(document).on("reveal.documentation", ".documentorContainer .documentor", function(){
	
		var jqDocEditor = jq(this);
		var jqDocWrapper = jqDocEditor.closest(".documentorContainer");
		var jqDocModel = jqDocEditor.find("#classXMLModel").first();
		var togglerTmpl = jqDocWrapper.find(".cld_pool .togglerTemplate").children();
		var jqExpanderUnit = togglerTmpl.addClass("docToggleWrap").clone(true, true);
		var infoTmpl = jqDocWrapper.find(".cld_pool .infoTemplate").children();
		var constantTmpl = jqDocWrapper.find(".cld_pool .constantTemplate").children();
		var propertyTmpl = jqDocWrapper.find(".cld_pool .propertyTemplate").children();
		var methodTmpl = jqDocWrapper.find(".cld_pool .methodTemplate").children();
		
		var documentationModel = jqDocWrapper.data("documentationModel");
		
		// Reveal final model
		var txt = jq("<div>").append(documentationModel).html();

		// ------------------------------------------------
		
		// Content with nested structure
		jqDocEditor.empty();
		jqDocEditor.append(jqDocModel);
		var nestedModel = jq(txt);

		var expanderList = jq("<div class='expanderList'></div>");
		jqDocEditor.append(expanderList);
		
		
		// __ Class name
		var className = nestedModel.children("class").attr("name");
		var classElements = jq("<div class='classElements'></div>");
		expanderList.append(classElements);

		// ________ Class info tile
		var classExpander = jqExpanderUnit.clone(true, true);
		var isDeprecated = nestedModel.find("class > info > deprecated:not(:empty)").length != 0;
		var classInfo = infoTmpl.clone();
		if (isDeprecated)
			classInfo.addClass("deprecated");
		
		// Class name
		classInfo.find(".className").text(className);
		
		// Class Abstract
		var classAbstract = nestedModel.find("class").attr("abstract");
		if (classAbstract == "true")
			classInfo.find(".classAbstract").text("ABSTRACT");
		
		var topRightInfo = classInfo.find(".topRightInfo");
								
		// Version
		var classVersion = jq.trim(nestedModel.find("class > info > version").text());
		classVersion += "-"+jq.trim(nestedModel.find("class > info > build").text());
		
		// Date Created 
		var classCreatedOn = jq.trim(nestedModel.find("class > info > datecreated").text());
		// If numeric
		if (!isNaN(parseFloat(classCreatedOn)) && isFinite(classCreatedOn)) {
			// PHP time is in seconds, js Date needs ms
			classCreatedOn = new Date(classCreatedOn * 1000);
			classCreatedOn = classCreatedOn.format('F j, Y, G:i (T)');
		}
		topRightInfo.find(".classCreated").append("<span>"+(classCreatedOn == "" ? "&empty;" : classCreatedOn )+"</span>");
		
		// Date Revised
		var classRevisedOn = jq.trim(nestedModel.find("class > info > daterevised").text());
		// If numeric
		if (!isNaN(parseFloat(classRevisedOn)) && isFinite(classRevisedOn)) {
			// PHP time is in seconds, js Date needs ms
			classRevisedOn = new Date(classRevisedOn * 1000);
			classRevisedOn = classRevisedOn.format('F j, Y, G:i (T)');
		}
		topRightInfo.find(".classRevised").append("<label>rev. </label><span>"+(classRevisedOn == "" ? "&empty;" : classRevisedOn )+"</span>");
		
		// Extends / Implements {/ Uses}
		var classRelationsWrapper = classInfo.find(".classRelations");
		var classExtendsWrapper = classRelationsWrapper.find(".classExtends");
		var classImplementsWrapper = classRelationsWrapper.find(".classImplements");
		//var classUsesWrapper = classRelationsWrapper.find(".classUses");
	
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
		if (classExtendsWrapper.filter(":empty").length != 0 &&
				classImplementsWrapper.filter(":empty").length != 0)
			classRelationsWrapper.css("padding", 0);
		
		
		// Title / Description / Deprecated
		var classDescInfo = classInfo.find(".classDescription");
		
		var classTitle = nestedModel.find("class > info > title").text();
		classDescInfo.find("input[title='Title']").val(decodeHtml(classTitle)).attr("name", "class[info][title]");
		
		var classDescription = nestedModel.find("class > info > description").text();
		var classExeptions = nestedModel.find("class > info > throws").children("exception");
		
		classDescInfo.find("textarea[title='Description']").attr("name", "class[info][description]").val(classDescription);
		classDescInfo.find(".classThrows").attr("name", "class[info][throws]").data("multipleObjects", "exception");
		if (classExeptions.length != 0) {
			var newContainer = jq("<div class='objectsWrapper'></div>");
			classExeptions.each(function(){ 
				newContainer.append(jq("<div class='objectBox'>"+jq(this).text()+"</div>"));
			});
			classDescInfo.find(".classThrows").addClass("noDisplay").after(newContainer);	
		}
		
		var classDeprecated = nestedModel.find("class > info > deprecated").text();
		var dt = classInfo.find(".deprecatedTile");
		var dt_chk = dt.find("[type='checkbox']").attr("name", "class[info][deprecated]").prop("checked", isDeprecated);
		inp = dt.find(".deprecatedDescription").val(decodeHtml(classDeprecated)).attr("name", "class[info][deprecated]");
		
		classElements.append(classExpander);
		classExpander.trigger("setHead.toggler", [jq("<span>"+className+"</span><span class='expanderCounter'>v. "+(classVersion == "" || classVersion == "-" ? "0.1-1" : classVersion)+"</span>")]);
		classExpander.trigger("appendToBody.toggler", [classInfo]);
		classExpander.addClass("elevated").trigger("open.toggler");
		
		
		// __ Constants info
		var constantsCollection = jq("<div class='constantsList'>");
		
		var classConstants = nestedModel.find("class > constants");
		var constantsExpander = jqExpanderUnit.clone(true, true);
		classConstants.children().each(function(){
			var isDiscontinued = jq(this).children("discontinued:contains(true)").length != 0;
			var constantsInfo = constantTmpl.clone();
			if (isDiscontinued)
				constantsInfo.addClass("discontinued");
			
			constantsInfo.not(".discontinued").find(".discontinuedOverlay").remove();
			constantsInfo.find(".constantName").text(jq(this).attr("name"));
			constantsInfo.find(".constantType").attr("name", "class[constants]["+jq(this).attr("name")+"][type]").val(decodeHtml(jq(this).attr("type")));
			constantsInfo.find(".constantDescription").attr("name", "class[constants]["+jq(this).attr("name")+"][description]").val(decodeHtml(jq(this).children("description").text()));
			constantsInfo.not(".discontinued").find(".discontinuedTile").remove();
			
			constantsCollection.append(constantsInfo);
		});
		constantsCollection.not(":empty").each(function(){
			var emptyVital = constantsCollection.find("input[class$='Type'],textarea[class$='Description'][class!='methodReturnDescription']").filter(function(){
				var val = jq.trim(jq(this).val());
				return !val;
			}).closest("[class$='TileWrapper'][class!='argumentTileWrapper']");
			var missingCounter = (emptyVital.length == 0 ? "" : "<span class='missingCounter' title='Undocumented items'>" + emptyVital.length + "</span>" );
			
			classElements.append(constantsExpander);
			//var emptyFields = constantsExpander.find("input");
			constantsExpander.trigger("setHead.toggler", [jq("<span>Constants</span><span class='expanderCounter' title='Total items'>"+constantsCollection.find(".constantTileWrapper").length+"</span>"+missingCounter)]);
			constantsExpander.trigger("appendToBody.toggler", [constantsCollection]);
		});
		
		
		
		
		var scopeTags = [ "Public", "Protected", "Private" ];
		
		// __ Properties info
		var propertiesInfoExpander = jqExpanderUnit.clone(true, true);
		var propertiesInfoWrapper = jq("<div class='propertiesList'></div>");
		
		var classProperties = nestedModel.find("class > properties");
		
		classElements.append(propertiesInfoExpander);
		propertiesInfoExpander.trigger("setHead.toggler", [jq("<span>Properties</span><span class='expanderCounter' title='Total items'></span><span class='missingCounter' title='Undocumented items'></span>")]);
		propertiesInfoExpander.trigger("appendToBody.toggler", [propertiesInfoWrapper]);
		var missingSum = 0;
		for (var i = 0; i < scopeTags.length; i++) {
			var propertiesExpander= jqExpanderUnit.clone(true, true);
			var propertiesCollection = jq("<div>");
			classProperties.children("scope[type='"+scopeTags[i].toLowerCase()+"']").children().each(function(){
				var isDiscontinued = jq(this).children("discontinued:contains(true)").length != 0;
				var propertiesInfo = propertyTmpl.clone();
				if (isDiscontinued)
					propertiesInfo.addClass("discontinued");
				
				propertiesInfo.not(".discontinued").find(".discontinuedOverlay").remove();
				propertiesInfo.find(".propertyName").text(jq(this).attr("name"));
				if (jq(this).attr("static") != "true")
					propertiesInfo.find(".propertyStatic").remove();
				
				propertiesInfo.find(".propertyType").attr("name", "class[properties]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][type]").val(decodeHtml(jq(this).attr("type")));
				propertiesInfo.find(".propertyDescription").attr("name", "class[properties]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][description]").val(decodeHtml(jq(this).children("description").text()));
				
				propertiesInfo.not(".discontinued").find(".discontinuedTile").remove();
				propertiesCollection.append(propertiesInfo);
			});
			propertiesCollection.not(":empty").each(function(){ 
				var emptyVital = propertiesCollection.find("input[class$='Type'],textarea[class$='Description'][class!='methodReturnDescription']").filter(function(){
					var val = jq.trim(jq(this).val());
					return !val;
				}).closest("[class$='TileWrapper'][class!='argumentTileWrapper']");
				var missingCounter = (emptyVital.length == 0 ? "" : "<span class='missingCounter' title='Undocumented items'>" + emptyVital.length + "</span>" );
				missingSum += emptyVital.length;
				
				propertiesInfoWrapper.append(propertiesExpander);
				propertiesExpander.trigger("setHead.toggler", [jq("<span>"+scopeTags[i]+"</span><span class='expanderCounter' title='Total items'>"+propertiesCollection.find(".propertyTileWrapper").length+"</span>"+missingCounter)]);
				propertiesExpander.trigger("appendToBody.toggler", [propertiesCollection]);
			});
		}
		if (propertiesInfoExpander.find(".propertiesList:empty").length != 0)
			propertiesInfoExpander.remove();
		else {
			var sum = propertiesInfoExpander.find(".propertyTileWrapper").length;
			propertiesInfoExpander.find(".expanderCounter").first().text(sum);
			var mc = propertiesInfoExpander.find(".missingCounter").first();
			mc.text(missingSum);
			if (missingSum == 0)
				mc.remove();
		}
		
		
		
		// __ Methods info
		var methodsInfoExpander = jqExpanderUnit.clone(true, true);
		var methodsInfoWrapper = jq("<div class='methodsList'></div>");
		
		var classMethods = nestedModel.find("class > methods");
		classElements.append(methodsInfoExpander);
		methodsInfoExpander.trigger("setHead.toggler", [jq("<span>Methods</span><span class='expanderCounter' title='Total items'></span><span class='missingCounter' title='Undocumented items'></span>")]);
		methodsInfoExpander.trigger("appendToBody.toggler", [methodsInfoWrapper]);
		missingSum = 0;
		for (var i = 0; i < scopeTags.length; i++) {
			var methodsExpander = jqExpanderUnit.clone(true, true);
			var methodsCollection = jq("<div>");
			classMethods.children("scope[type='"+scopeTags[i].toLowerCase()+"']").children().each(function(){
				var isMethodDeprecated = jq(this).children("deprecated:not(:empty)").length != 0;
				var isDiscontinued = jq(this).children("discontinued:contains(true)").length != 0;
				var jqmethod = jq(this);
				var args = jqmethod.find("arguments > arg, parameters > parameter");
				var methodExeptions = jqmethod.find("throws > exception");
				
				var methodsInfo = methodTmpl.clone();
				if (isDiscontinued)
					methodsInfo.addClass("discontinued");
				else if (isMethodDeprecated)
					methodsInfo.addClass("deprecated");
				
				methodsInfo.not(".discontinued").find(".discontinuedOverlay").remove();
				methodsInfo.find(".methodName .methodReturnType").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][returntype]").val(decodeHtml(jq(this).attr("returntype")));
				methodsInfo.find(".methodName span").text(jqmethod.attr("name"));
				
				if (jqmethod.attr("static") != "true")
					methodsInfo.find(".methodStatic").remove();
					
				if (jqmethod.attr("abstract") != "true")
					methodsInfo.find(".methodAbstract").remove();
				
				methodsInfo.find(".methodDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][description]").val(jqmethod.children("description").text());
				
				methodsInfo.find(".methodReturnDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][returndescription]").val(jqmethod.children("returndescription").text());
				
				methodsInfo.find(".methodThrows").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][throws]").data("multipleObjects", "exception");
				
				if (methodExeptions.length != 0) {
					var newContainer = jq("<div class='objectsWrapper'></div>");
					methodExeptions.each(function(){ 
						newContainer.append(jq("<div class='objectBox'>"+jq(this).text()+"</div>"));
					});
					methodsInfo.find(".methodThrows").addClass("noDisplay").after(newContainer);	
				}

				var argumentTmpl = methodsInfo.find(".argumentTileWrapper").detach();
				var argumentsCollection = methodsInfo.find(".argumentList");
				if (args.length == 0)
					methodsInfo.find("[class*='argument']").remove();

				args.each(function(){
					var argumentsInfo = argumentTmpl.clone();
					argumentsInfo.find(".argumentName").text(jq(this).attr("name"));
					argumentsInfo.find(".argumentType").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][parameters]["+jq(this).attr("name")+"][type]").val(decodeHtml(jq(this).attr("type")));
					argumentsInfo.find(".argumentDefault").text(jq(this).attr("defaultvalue"));
					argumentsInfo.find(".argumentDescription").val(jq(this).children("description").text()).attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jqmethod.attr("name")+"][parameters]["+jq(this).attr("name")+"][description]");
					
					argumentsCollection.append(argumentsInfo);
				});
				
				methodsInfo.not(".discontinued").find(".discontinuedTile").remove();
				methodsInfo.find(".deprecatedTile [type='checkbox']").prop("checked", isMethodDeprecated).attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][deprecated]");
				methodsInfo.find(".deprecatedTile .deprecatedDescription").attr("name", "class[methods]["+scopeTags[i].toLowerCase()+"]["+jq(this).attr("name")+"][deprecated]").val(decodeHtml(jq(this).children("deprecated").html()));
				
				methodsCollection.append(methodsInfo);
			});
			methodsCollection.not(":empty").each(function(){
				var emptyVital = methodsCollection.find("input[class$='Type'],textarea[class$='Description'][class!='methodReturnDescription']").filter(function(){
					var val = jq.trim(jq(this).val());
					return !val;
				}).closest("[class$='TileWrapper'][class!='argumentTileWrapper']");
				var missingCounter = (emptyVital.length == 0 ? "" : "<span class='missingCounter' title='Undocumented items'>" + emptyVital.length + "</span>" );
				missingSum += emptyVital.length;
				
				methodsInfoWrapper.append(methodsExpander);
				methodsExpander.trigger("setHead.toggler", [jq("<span>"+scopeTags[i]+"</span><span class='expanderCounter' title='Total items'>"+methodsCollection.find(".methodTileWrapper").length+"</span>"+missingCounter)]);
				methodsExpander.trigger("appendToBody.toggler", [methodsCollection]);
			});
		}
		if (methodsInfoExpander.find(".methodsList:empty").length != 0)
			methodsInfoExpander.remove();
		else {
			var sum = methodsInfoExpander.find(".methodTileWrapper").length;
			methodsInfoExpander.find(".expanderCounter").first().text(sum);
			var mc = methodsInfoExpander.find(".missingCounter").first();
			mc.text(missingSum);
			if (missingSum == 0)
				mc.remove();
		}
		
		// This should be handled by the expander :S
		jqDocEditor.find(".emptyExpander").remove();
	
		
		// Better way????????????
		jqDocEditor.find("[name]").not("#classXMLModel").each(function(){
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
	
	function decodeHtml(text) {
	    return jq("<div>").html(text).text();
	}
});