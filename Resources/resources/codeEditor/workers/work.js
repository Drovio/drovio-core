
// Change annotation from "$" to "jq"
//var jq=jQuery.noConflict();

// Worker variables
var regexp;
var tokens;
var forbiddenTokens;

// Global + local
var HELP_EXPRESSIONS;
var HELP_CONSTANTS;

// Worker functions
this.addEventListener("message", function(ev){
	// Data received
	var data = ev.data;
	postMessage(data);
	/*
	// Initialize variables
	var blockInput = data.blockInput;
	var blockIndex = data.blockIndex;
	var filtering = data.filtering;
	var lang = data.lang;
	HELP_CONSTANTS = data.HELP_CONSTANTS;
	HELP_EXPRESSIONS = data.HELP_EXPRESSIONS;
	regexp = data.regexp;
	tokens = data.tokens;
	forbiddenTokens = data.forbiddenTokens;

	
	// Execute work
	blockInput = prefilter(blockInput);
	
	if (filtering) {
		blockInput = filter(blockInput, lang);
	}
	present(blockInput, blockIndex);*/
}, false);

this.addEventListener("error", function(ev){
	console.log("Worker error!");
	console.log(ev);
	this.close();
}, false);



function prefilter(blockInput){
	blockInput = blockInput.replace(HELP_EXPRESSIONS.lineBreak,"</div><div class='code'>");
	blockInput = blockInput.replace(HELP_EXPRESSIONS.notCodeTag,"");
	blockInput = blockInput.replace(HELP_EXPRESSIONS.codeTag, function(a,b,c,d) {
		return '\n<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>'+a.replace(/\"|\'/g, "&quot;").replace(/\bclass\b/g, HELP_CONSTANTS.CLASS).replace(/\bcode\b/g, HELP_CONSTANTS.CODE).replace(/\//g, HELP_CONSTANTS.SLASH).replace(/\bdiv\b/g, HELP_CONSTANTS.DIV);
	});
	blockInput = blockInput.substring('\n<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>'.length)+'\n<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>';
	
	return blockInput;
};
			
function filter(input, lang) {
	var middle = input;
	middle = forbidTokens(input, lang);
	// stylize strings, comments, etc

	middle = stylizeContent(middle, lang);
	middle = middle.replace(HELP_EXPRESSIONS.lineBreak, '');
	// stylize constants and stuff that need to be read from outer source

	middle = highlightTokens(middle, lang);
	// stylize rest of the script
	middle = middle.replace(/&quot;/g, "\"").replace(HELP_EXPRESSIONS.tag, function(a,b,c,d) {
		return a.replace(new RegExp(HELP_CONSTANTS.CLASS, "g"), "class")
				.replace(new RegExp(HELP_CONSTANTS.SLASH, "g"), "/")
				.replace(new RegExp(HELP_CONSTANTS.CODE, "g"), "code")
				.replace(new RegExp(HELP_CONSTANTS.DIV, "g"), "div");
	});
	
	input = middle;
	
	return input;
};
			
function present(output, blockIndex){
	// Present code
	var counter = blockIndex;
	// line numbering
	output = output.replace(HELP_EXPRESSIONS.codeTag, function(a) {
		return '<span class="lineNumber">'+(counter++)+'</span>'+a;
	});

	// Post output
	postMessage(output);
};	

// Help functions
function forbidTokens(str2chk, language) {
	var tmpString = str2chk;
	
	var replaceRegexp = "";
	
	for (var l in forbiddenTokens) {
		for (var t in forbiddenTokens[l]['tokens']) {
			replaceRegexp += ".*"+forbiddenTokens[l]['tokens'][t]+".*;|";
		}
	}

	replaceRegexp = replaceRegexp.substring(0,replaceRegexp.length-1);

	if (replaceRegexp.trim() != "")
		tmpString = tmpString.replace(new RegExp(replaceRegexp, "ig"), function(matched, index, originalText) {
			return "\/*"+matched+"*\/";
		})
	
	return tmpString;
}

// search and highlight tokens of language+global in a given string
function highlightTokens(str2chk, language) {
	var tmpString = str2chk;
	
	// snif hits
	var tokensToCheck = tokens;
	var tokenRegexps = regexp;

	var expression = "";
	
	for (var l in tokensToCheck) {
		for (var to in tokensToCheck[l]['tokens']) {
			expression += "|\\b"+tokensToCheck[l]['tokens'][to]+"\\b";
		}
	}
	
	expression = "("+expression.substr(1)+")";
	if (expression == "()" ) 
		return tmpString;
	postMessage(tokensToCheck);
	postMessage(language);
	postMessage(tokensToCheck[language]);
	var cl = tokensToCheck[language]['class'];
	
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
		var matchedChild = new Object();
		// Get closest match
		for (var l in instanceRegexp['major'])
			for (var g in instanceRegexp['major'][l])
				for (var name in instanceRegexp['major'][l][g]) {
					var pos = str.search(instanceRegexp['major'][l][g][name]['regexp'], instanceRegexp['major'][l][g][name]['modifiers']);
					if (pos > -1 && pos < first) {
						first = pos;
						matchedChild = instanceRegexp['major'][l][g][name];
					};
				}
		// Do something with that and redo with the substring, till there's no more text...
		if (first == +Infinity) {
			// No more matches | No more text
			tmpOut += str;
			tmpOut = tmpOut.substring(0, tmpOut.lastIndexOf('<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>'))+'<'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>';
			return;
		} else if (matchedChild['name'] == "__codeSnif") {
			// Match is a code tag... Get next substring
			var firstMatch = str.match(new RegExp(matchedChild["regexp"], matchedChild["modifiers"]));
			var endOffset = str.indexOf(firstMatch[0])+firstMatch[0].length;
			tmpOut += str.substring(0, endOffset);
			majorStylizer(str.substring(endOffset));
		} else {
			// Valid Match...
			var firstMatch = str.match(new RegExp(matchedChild["regexp"], matchedChild["modifiers"]));
			var fm = firstMatch[0].replace(/<(\/|RW_SLASH_TOKEN)(div|RW_DIV_TOKEN)>/g, '</span><'+HELP_CONSTANTS.SLASH+HELP_CONSTANTS.DIV+'>');
			fm = fm.replace(HELP_EXPRESSIONS.codeTag, function(a){
				return a+"<span "+HELP_CONSTANTS.CLASS+"='"+language+matchedChild["class"]+"'>";
			});
			var efm = RegExp.escaped(firstMatch[0]);
			str = str.replace(new RegExp(efm), function(a){
				return "<span "+HELP_CONSTANTS.CLASS+"='"+language+matchedChild["class"]+"'>"+fm+"</span>";
			});
			var endOffset = str.indexOf(fm+"</span>")+fm.length+"</span>".length;
			tmpOut += str.substring(0, endOffset);
			majorStylizer(str.substring(endOffset));
		}
	}
	
	//stylize elements that have minor priority in stylizing (i.e. var format, keywords etc)
	function minorStylizer(str){
		for (var l in instanceRegexp['minor'])
			for (var g in instanceRegexp['minor'][l])
				for (var name in instanceRegexp['minor'][l][g]) {
					str = str.replace(new RegExp(instanceRegexp['minor'][l][g]["regexp"], instanceRegexp['minor'][l][g]["modifiers"]+"g"), function(matched, index, originalText){ 
						return "<span "+HELP_CONSTANTS.CLASS+"='"+language+instanceRegexp['minor'][l][g]["class"]+"'>"+matched+"</span>";
					})
				}
		
		tmpOut = str;		
	}
	
	var tmpOut = "";
	
	var instanceRegexp = regexp;
	instanceRegexp['major']['global']['filter']['__codeSnif'] = new Object();
	instanceRegexp['major']['global']['filter']['__codeSnif']['name'] = "__codeSnif";
	instanceRegexp['major']['global']['filter']['__codeSnif']['modifiers'] = "";
	instanceRegexp['major']['global']['filter']['__codeSnif']['priority'] = "major";
	instanceRegexp['major']['global']['filter']['__codeSnif']['regexp'] = HELP_EXPRESSIONS.codeTag.source;

	majorStylizer(str2chk);
	minorStylizer(tmpOut);

	return tmpOut;
}

RegExp.escaped = function(str) {
	return (str+'').replace(/([.?*+^$[\]\\() {}|-])/g,  "\\$1");
};