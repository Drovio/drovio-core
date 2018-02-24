var jq=jQuery.noConflict();

jq(document).one("ready.extra", function() {
	jq(document).data("classCommentsInit", true);
	jq(document).trigger("content.modified.classDocumentor");
		
	jq(document).on("updateSourceModel.documentation", ".documentorContainer .documentor", function(){
		var jqDocEditor = jq(this);
		var jqDocWrapper = jqDocEditor.closest(".documentorContainer");
		var jqLinkedEditor = jqDocWrapper.closest("[data-wrapper='documentor']").find(".codeContainer").children().first();
				
		jqLinkedEditor.trigger("getCodeEditorContent", [false, function(content){
			
			var documentationModel = jqDocWrapper.data("documentationModel");
			var source = content;
			
			// Decode source - source is ready to be parsed after this line...
			source = jq("<span>").html(source).text();
		
			// Parse file
			var manualModel = jqDocWrapper.data("manualModel");
			var sourceModel = parseFile(source, manualModel);
			sourceModel = jq("<manual>").html(sourceModel);
			jqDocWrapper.data("sourceModel", sourceModel);
			
			if (documentationModel.length == 0)
				jqDocEditor.trigger("updateDocumentationModel.documentation")
			else
				jqDocEditor.trigger("update.documentation", true);
		}]);
	});
	
	function parseFile(content, manualModel) {
	
		var manualPrototypes = jq(document).data("docManualPrototypes");
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
		//var namespace = c.match(/^[ \t]*\bnamespace\b[ \w\\\t]+/gm);
		//namespace = (jq.type(namespace) == "null" ? "" : namespace[0]);
		//namespace = namespace.replace(/[ \t]*\bnamespace\b[ \t]*/g, "");
		//m.attr("namespace", namespace);*/
		
	
		// get preclass stuff [global, dotall] [this takes its sweet time...]
		var preclass = c.match(/[\s\S]+(?=class[ \t]*[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*[\w, (\r\n|\n|\r)]*?(?=\{[\s\S]*\}))/);
		
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
		var classMethods = postclass.match(/^([ \t]*\/\/.*[\n\r]*|[ \t]*(\/\*([\w\W](?!(\*\/)))*(.\*\/))[ \t]*[\r\n]*)?[\r\n\t ]*[\t ]*(final)?[\t ]*(abstract)?[\t ]*(public|private|protected)?[\t ]*(static)?[\t ]*\b[fF]unction\b.*\(.*\)/gm);
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
				var abstract = methodSignature.match(/^.*?abstract.*?[fF]unction/m);
				abstract = (jq.type(abstract) != "null" ? true : false);
				
				// deprecated
				var depr = classMethods[i].match(/^[ \t\/]*(\*)?[ \t]*\bdeprecated\b.*/im);
				// description
				//var methodDescription = classMethods[i].match(/(([ \t]*\/\/.*[\r\n]*)*(\r\n|\n|\r)|\bDescription\b[ \t]*:[ \t]*.*)/g);
				//methodDescription = (jq.type(methodDescription) == "null" ? "" : jq.trim(methodDescription.join("")));
				//methodDescription = jq.trim(methodDescription.replace(/^[ \t]*\/\/|^\bDescription\b[ \t]*:[ \t]*/gm, ""));
				// scope
				var methodScope = methodSignature.match(/\bpublic\b|\bprivate\b|\bprotected\b/);
				methodScope = (!methodScope ? "public" : methodScope[0]);
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
	
	function escapeHtml(unsafe) {
	    return unsafe
	         .replace(/&/g, "&amp;")
	         .replace(/</g, "&lt;")
	         .replace(/>/g, "&gt;")
	         .replace(/"/g, "&quot;")
	         .replace(/'/g, "&#039;");
	}
});