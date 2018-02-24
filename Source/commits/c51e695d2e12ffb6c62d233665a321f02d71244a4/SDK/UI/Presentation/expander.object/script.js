/* 
 * Redback JavaScript Document
 *
 * Title: Expander Manager
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.0
 * DateCreated: 29/01/2013
 * DateRevised: --
 *
 */
 
// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();


jq(document).one("ready", function() {
	
	jq(document).on("click", ".expander:not(.static) > .expanderHead", function(ev){
		var jqthis = jq(this);
		var bodyWrapper = jqthis.next();		
		var expanded = (bodyWrapper.hasClass("expanded") ? false : true);
		var tout = (typeof jqthis.data("tout") === "undefined" ? -1 : jqthis.data("tout"));
		clearTimeout(tout);
		
		var bd = bodyWrapper.children().first();
		//bodyWrapper.get(0).style.height = (!expanded ? 0 : bodyWrapper.get(0).style.height = bd.get(0).clientHeight + "px");

		if (expanded) {
			bodyWrapper.get(0).style.height = bd.get(0).clientHeight + "px";
			bodyWrapper.toggleClass("expanded");
			var tt = setTimeout(function(){
				bodyWrapper.css("-webkit-transition", "none");
				bodyWrapper.css("height", "auto");
			}, 400);
		}else{
			bodyWrapper.css("height", "");
			bodyWrapper.css("-webkit-transition", "");
			bodyWrapper.toggleClass("expanded");
		}
		
	});
	 
	jq(document).on("click", ".expander.static > .expanderHead", function(ev){
	    var parent = jq(this).parent();
	    var th = jq(this);
	    var check = parent.filter(".expanded").length;
	    
	    if (check != 0){
	        parent.toggleClass("expanded"); 
	        setTimeout(function(){
	            parent.siblings().not("textarea:hidden").slideToggle("fast");
	            parent.siblings().promise().done(function(){
	                if (check == 0)
	                    parent.toggleClass("expanded");
	           });
	        }, 400)
		jq(this).trigger("collapsed.staticExpander");
	    } else {
	        parent.siblings().not("textarea:hidden").slideToggle("fast");
	        parent.siblings().promise().done(function(){
	            if (check == 0)
	                parent.toggleClass("expanded");
	        });
		jq(this).trigger("expanded.staticExpander");
	    }
	
	    // Initialize top according to css
	    jq(this).css("top", "");
	});
	
	// expanderObject is a javascript Object with key "expander"
	// It will hold a jQuery expander after the execution of the event
	jq(document).on("produce.expander", function(ev, expanderObject, static){
		expanderObject.expander = jq("<div>").addClass("expander"+(static === true ? " static" : ""));
		var head = jq("<div>").addClass("expanderHead");
		expanderObject.expander.append(head);
		var body = jq("<div>").addClass("expanderBodyWrapper").append("<div>");
		expanderObject.expander.append(body);
		
		expanderObject.expander.on("appendToHead.expander", function(ev, content){
			jq(this).children(".expanderHead").append(content);
			jq(this).children(".expanderHead").append("<span class='expanderCounter'></span>");
		});
		
		expanderObject.expander.on("appendToBody.expander", function(ev, content, count){
			jq(this).children(".expanderBodyWrapper").children().first().append(content);
			var childrenLength = jq(content).children().length;
			var specialLength = null;
			
			if (jq.type(count) == "undefined" || jq.type(count) == "null") {
				jq(this).children(".expanderHead").find(".expanderCounter").text("["+childrenLength+"]");
			}else if (count !== false){
				specialLength = jq(content).find(count).length;
				var c = (specialLength == 0 ? "" : "["+specialLength+"]");
				jq(this).children(".expanderHead").find(".expanderCounter").text(c);
				jq(this).addClass((specialLength == 0 ? "emptyExpander" : ""));
			}
			
			if (childrenLength == 0 || specialLength == 0){
				jq(this).addClass("emptyExpander");
			}
		});
	});
/*
	jq(document).on("mouseenter", ".expander.expanded > .expanderBodyWrapper > div > div > :first-child", function(ev){
		jq(this).closest(".expander.expanded").children(".expanderHead").css("top", "0px");
	});
	
	jq(document).on("mouseleave", ".expander.expanded > .expanderBodyWrapper > div > div > :first-child", function(ev){
		var head = jq(this).closest(".expander.expanded").children(".expanderHead");
		if (!jq(ev.relatedTarget).is(head))
			jq(this).closest(".expander.expanded").children(".expanderHead").css("top", "-15px");
	});
*/	
	/*
	jq(document).on("mouseleave", ".expander.expanded", function(ev){
		jq(this).children(".expanderHead").css("top", "0px");
	});
	*/
});