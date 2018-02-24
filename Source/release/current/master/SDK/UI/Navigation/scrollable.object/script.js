/* 
 * Redback JavaScript Document
 *
 * Title: RedBack Scrollable Area Manager
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.0
 * DateCreated: 14/11/2012
 * DateRevised: --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready", function() {

	/*jq(document).on('content.modified', function(ev) {
		var dragElements = jq(".draggableList").children().not("[draggable='true']");
		if (dragElements.length == 0) 
			return;
		
		dragElements.attr("draggable", "true");
		
		dragElements.off("dragstart");
		dragElements.on("dragstart", function(ev){
			jq(this).parent().data("activeDrag", this);
			jq(this).css("opacity", "0.4");
			
			ev.originalEvent.dataTransfer.effectAllowed = 'move';
			ev.originalEvent.dataTransfer.setData('text/html', jq("<div>").append(jq(this).clone(true, true)).html());
		});
		dragElements.off("dragenter");
		dragElements.on("dragenter", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			console.log("enter");
			var sourceElem = jq(this).parent().data("activeDrag");
			console.log(jq(this).parent().get(0) == jq(sourceElem).parent().get(0));
			if (jq(this).parent().get(0) == jq(sourceElem).parent().get(0)
				&& this != sourceElem)
				jq(this).css("box-shadow", "inset 0px 0px 2px black");
		});
		dragElements.off("dragleave");
		dragElements.on("dragleave", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			console.log("leave");
			jq(this).css("box-shadow", "");
			
		});
		dragElements.off("dragover");
		dragElements.on("dragover", function(ev){
			ev.preventDefault(); // Necessary. Allows us to drop.
			ev.stopPropagation();
			var sourceElem = jq(this).parent().data("activeDrag");
			ev.originalEvent.dataTransfer.dropEffect = 'none';
			if (jq(this).parent().get(0) == jq(sourceElem).parent().get(0)
				&& this != sourceElem)
				ev.originalEvent.dataTransfer.dropEffect = 'move';
			
			return false;
		});
		dragElements.off("dragend");
		dragElements.on("dragend", function(ev){
			dragElements.css("box-shadow", "");
			dragElements.css("opacity", "");
			console.log("end");
		});
		dragElements.off("drop");
		dragElements.on("drop", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			console.log("drop");
			var sourceElem = jq(this).parent().data("activeDrag");
			if (jq(this).parent().get(0) == jq(sourceElem).parent().get(0)
				&& this != sourceElem) {
				//var draggedElem = ev.originalEvent.dataTransfer.getData("text/html");
				if (jq(this).index() > jq(sourceElem).index())
					jq(this).after(sourceElem)
				else
					jq(this).before(sourceElem);
			}
			
			jq(this).parent().removeData("activeDrag");
			dragElements.css("box-shadow", "");
			dragElements.css("opacity", "");
			
			return false;
		});
	});*/

	jq(document).on({
		"mousemove.scrollArea": function(ev){
			var jqthis = jq(this);
			//split area into 3 parts
			var topPart = jqthis.offset().top + jqthis.height()/3;
			var bottomPart = jqthis.offset().top + 2*(jqthis.height()/3);
			var h = jqthis.height();
			var sh = jqthis.get(0).scrollHeight;
			var interval = 4;
			
			// mouse above top part
			if(ev.pageY<topPart && jqthis.data("scrollArea")===true){
				jqthis.doTimeout('scrollListUp',interval,function(){
					var acc = (topPart-ev.pageY)/4;
					var speed = 1+acc;
					if(this.scrollTop()==0){
						return false;
					}else{
						
						// do something loopy
						this.scrollTop(this.scrollTop()-speed);
						return true;
					}
				}, false);
			//mouse above bottom part
			}else if(ev.pageY>bottomPart && jqthis.data("scrollArea")===true){
				jqthis.doTimeout('scrollListDown',interval,function(){
					var acc = (ev.pageY-bottomPart)/4;
					var speed = 1+acc;
					if(this.scrollTop()+h>=sh){
						return false;
					}else{
						// do something loopy
						this.scrollTop(this.scrollTop()+speed);
						return true;
					}
				}, false);
			//cancel timeouts + init scrollable area
			}else{
				if(jqthis.data("scrollArea")!==true && topPart<ev.pageY && ev.pageY<bottomPart)
					jqthis.data("scrollArea",true);
				jqthis.doTimeout('scrollListUp');
				jqthis.doTimeout('scrollListDown');
			}
		},
		
		"mouseleave.scrollArea": function(){
			//cancel timeouts
			var jqthis = jq(this);
			jqthis.doTimeout('scrollListUp');
			jqthis.doTimeout('scrollListDown');
		}
	}, ".uiScrollableArea");
	
	// Scrollable elements that prevents scroll bubbling.
	jq(document).on('wheel mousewheel DOMMouseScroll', '.soloScrollable', function(ev) {
		var delta = ev.originalEvent.wheelDelta || -ev.originalEvent.detail;
		this.scrollTop += ( delta < 0 ? 1 : -1 ) * 90;
		ev.preventDefault();
	});

	
	//________________________________________ scrollHere Function Extension of jQuery ________________________________________//
	// args: speed (number|string) - of the animation, focus (boolean) - after animation
	(function( jq ){
		
		var methods = {
			"animate" : function( scrollThis, speed ) { 
				if (jq.type(speed) == "undefined") 
					speed = 400;
					
				return this.each(function(){
					// Calculate pixels to move, in order for this to reach the top of scrollThis
					var top = scrollThis.scrollTop() + jq(this).offset().top - scrollThis.offset().top;
					// Offset to move from top
					var offset = 0.25*scrollThis.height();
				
					scrollThis.animate({
						scrollTop:top-offset
					},speed)
				});
			},
	
			"scroll" : function( scrollThis ){
				return this.each(function(){
					// Calculate pixels to move, in order for this to reach the top of scrollThis
					var top = scrollThis.scrollTop() + jq(this).offset().top - scrollThis.offset().top;
					// Offset to move from top
					var offset = 0.25*scrollThis.height();
					
					scrollThis.animate({
						scrollTop:top - offset
					},"fast")
				});
			},
	
			"focus" : function( scrollThis, f ){
				return this.each(function(){
					// Calculate pixels to move, in order for this to reach the top of scrollThis
					var top = scrollThis.scrollTop() + jq(this).offset().top - scrollThis.offset().top;
					// Offset to move from top
					var offset = 0.25*scrollThis.height();
				
					scrollThis.animate({
						scrollTop:top-offset
					},"fast", function(){
						if (f) jq(this).focus();
					});
				});
			},
	
			"anifocus" : function( scrollThis, o, t ){
				return this.each(function(){
					var speed = 400;
					var f = false;
					var jqthis = jq(this);
					if(typeof(o)=="boolean") f=o;
					if(typeof(o)=="string" || typeof(o)=="number") speed=o;
					if(typeof(t)=="boolean") f=t;
					if(typeof(t)=="string" || typeof(t)=="number") speed=t;
					
					// Calculate pixels to move, in order for this to reach the top of scrollThis
					var top = scrollThis.scrollTop() + jq(this).offset().top - scrollThis.offset().top;
					// Offset to move from top
					var offset = 0.25*scrollThis.height();
					
					scrollThis.animate({
						scrollTop:top-offset
					},speed, function(){
						if (f) jqthis.focus();
					})
				});
			}
		};
	
		jq.fn.scrollHere = function( method ) {
			// Closest scrolling element
			var collection = jq(this).add(jq(this).parents());
			var scrollThis = jq(collection.get().reverse()).filter(function(){
				return jq(this).height() != this.scrollHeight;
			}).first();
			
			var scrollThis = (scrollThis.length == 0 ? jq(window) : scrollThis );
			
			// Method calling logic
			if ( jq.type(method) == "undefined" || jq.type(method) == "null" ) {
				return methods[ "scroll" ].call( this, scrollThis );
			} else if ( arguments.length == 1 && typeof(method) == "boolean" ) {
				return methods[ "focus" ].apply( this, [scrollThis].concat(Array.prototype.slice.call( arguments, 0 )));
			} else if ( arguments.length == 1 && (typeof(method) == "string" || typeof(method) == "number" )){
				return methods[ "animate" ].apply( this, [scrollThis].concat(Array.prototype.slice.call( arguments, 0 )));
			} else if ( arguments.length == 2){
				return methods[ "anifocus" ].apply( this, [scrollThis].concat(Array.prototype.slice.call( arguments, 0 )));
			} else {
				jq.error( 'Wrong use of method jQuery.scrollTo...' );
			}    
	  
		};
	})( jQuery );
	
});