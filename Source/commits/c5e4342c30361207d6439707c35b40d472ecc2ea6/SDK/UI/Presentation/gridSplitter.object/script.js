// Write Your Javascript Code Here
/* 
 * Redback JavaScript Document
 *
 * Title: oneRightColumnAbsolute layout script
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1
 * DateCreated: 01/11/2012
 * DateRevised: --
 *
 */
 
// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

jq(document).one("ready.extra", function() {
	
	// host
	var _host = ""; 
	
	var vSliderClass = "vSlider";
	var hSliderClass = "hSlider";
	var preventSelectionClass = "preventSelection";
	
	var dimension = "width";
	
	var jqslider = jq();
	var jqside = jq();
	var jqmain = jq();
	var jqcontainer = jq();
	
	function getContext() {
		var splitter = new Object();
		
		splitter.bar = jq(this);
		splitter.side = splitter.bar.parent();
		var idx = splitter.side.index();
		
		splitter.main = splitter.side.parent().children().not(splitter.side).filter(function(){
			var i = jq(this).index();
			if (i == idx + 1){
				// Main is right / down, so bar should snap there
				splitter.bar.filter("."+vSliderClass).closest(splitter.side).addClass("rightSnap");
				splitter.bar.filter("."+hSliderClass).closest(splitter.side).addClass("bottomSnap");
				splitter.inverted = true;
			}else if(i == idx - 1){
				// Main is left / up, so bar should snap there
				splitter.bar.filter("."+vSliderClass).closest(splitter.side).addClass("leftSnap");
				splitter.bar.filter("."+hSliderClass).closest(splitter.side).addClass("topSnap");
				splitter.inverted = false;
			}
			return i == idx - 1 || i == idx + 1;
		}).first();
		splitter.expander = splitter.main.children(".sideExpander");
		
		splitter.container = splitter.side.parent();
		
		splitter.dimension = (splitter.bar.hasClass(vSliderClass) ? "width" : "height");
		
		return splitter;
	}
	
	jq(document).on("content.modified", function(){
		jq("."+vSliderClass+","+"."+hSliderClass).filter(function(index){
			return jq.type(jq(this).data("slider-initialized")) == "undefined";
		}).each(function(){
			var slider = getContext.call(this);
			jq(this).data("slider-elements", slider);
			jq(this).data("slider-initialized", true);
			slider.expander.data("side-title", slider.expander.attr("data-side-title"));
			slider.expander.removeAttr("data-side-title");
			
			slider.expander.data("exp-pos", slider.expander.attr("data-exp-pos"));
			slider.expander.removeAttr("data-exp-pos");

			// Init dimensions
			if(slider.side.attr('style'))
			{
				// Init dimensions if only side dimension has been set
				// (style attribute exists) during item building in php
				calculateNewDimensions(slider, 0, vSliderClass);
			}


			if (jq.type(slider.container.data("slider-close")) != "undefined") {
				slider.container.removeAttr("data-slider-close").removeData("slider-close");
				jq(this).trigger("dblclick.slider");
			}
			
		});
	})
	
	// Split view on double click
	jq(document).on("dblclick.slider", "."+vSliderClass+","+"."+hSliderClass, function(ev){
		var splitter = jq(this).data("slider-elements");
		var jqslider = splitter.bar;
		var jqside = splitter.side;
		var jqmain = splitter.main;
		var jqcontainer = splitter.container;
		var jqexpander = splitter.expander;
		var dimension = splitter.dimension;
		
		jqside.addClass("noDisplay").css("min-"+dimension, "").css(dimension,"0");
		jqmain.css(dimension, "100%").css("max-"+dimension, "");
		
		// Link main with expander
		var linkId = Math.floor((Math.random()*100));
		jqmain.data("link-id", linkId);
		
		// Find inner expanders
		var innerExpanders = jqmain.find(".sideExpander").not(".noDisplay");
		var innerParrents = innerExpanders.parent();
		innerExpanders = innerExpanders.detach();
		innerParrents.remove();
		
		var expanderClone = jqexpander.clone().removeClass("noDisplay")
			.one("click.expander", function(){
				var jqthis = jq(this);
				jqmain.css(dimension, "");
				jqside.removeClass("noDisplay").css("min-"+dimension, "").css(dimension,"");
				if (jqthis.siblings(".sideExpander").length == 0)
					jqthis.parent().remove();
				else
					jqthis.remove();
			});
		expanderClone.data("link-id", linkId);
		var title = jqexpander.data("side-title");
		title = (title === undefined ? "Expand" : title);
		expanderClone.append("<span>"+title+"</span>");
		var wrapper = jq("<div class='expandersWrapper'></div>");
		wrapper.on("mouseenter mouseleave", function(){
			jq(this).children(".sideExpander").not(":last-child").toggleClass("noDisplay");
		});
		
		var parentExpander = jqmain.parents(".sliderMain").children().not(".sliderMainContent")
			.find(".expandersWrapper > .sideExpander");
		var parent;
		if (parentExpander.length > 0) {
			parent = parentExpander.closest(".sliderMain");
			var p = parentExpander.parent();
			innerExpanders = innerExpanders.add(parentExpander.detach());
			p.remove();
		} else {
			parent = jqmain;
		}
		var sender = parent.siblings(".sliderSide");
		wrapper.append(innerExpanders.addClass("noDisplay"));
		wrapper.append(expanderClone);
		
		var position = jqexpander.data("exp-pos");
		
		switch (position ) { 
			case '3':
				// Right Top
				wrapper.css({
					"top":10,
					"right":26,
					"position":"absolute"
				});
				break;
			case '2':
				// Left Bottom
				wrapper.css({
					"bottom":10,
					"left":26,
					"position":"absolute"
				});
				break;
			default :
				// Right Bottom
				wrapper.css({
					"bottom":10,
					"right":26,
					"position":"absolute"
				});
		}
		parent.append(wrapper);
	});
	
	// Drag event
	jq(document).on("mousedown.slider", "."+vSliderClass+","+"."+hSliderClass, function(ev){
		// Get position of mouse on mouse down, initialize parameters, and register a listener for mouse move
		var splitter = jq(this).data("slider-elements");
		var jqslider = splitter.bar;
		var jqside = splitter.side;
		var jqmain = splitter.main;
		var jqcontainer = splitter.container;
		var dimension = splitter.dimension;
		var inverted = splitter.inverted;
		
		jqcontainer.addClass(preventSelectionClass);
		jq(document).off("selectstart.slider");
		jq(document).on("selectstart.slider", function(ev){ev.stopPropagation(); return false;});
		
		var prevOffset = (jqslider.hasClass(vSliderClass) ? ev.pageX : ev.pageY);
		var dock = (jqslider.hasClass(vSliderClass) ? jqcontainer.offset().left : jqcontainer.offset().top);

		// Calculate the distance in pixels that the mouse spanned during the last move
		// and adjust the dimension of the sibling elements.
            	jq(document).on("mousemove.slider", function(evnt){
			// Calculate distance spanned
			var offset = (jqslider.hasClass(vSliderClass) ? evnt.pageX : evnt.pageY);
			calculateNewDimensions(splitter, offset, vSliderClass);			
		});
	});
	
	// Remove the movement Event
	jq(document).on("mouseup.slider, click.slider", function(ev){
		jq(this).find("."+preventSelectionClass).removeClass(preventSelectionClass);
        	jq(document).off("mousemove.slider");
		jq(document).off("selectstart.slider");
	});
	
	// Special destroyed event
	/*jq.event.special.destroyed = {
		remove: function(o) {
			if (o.handler) {
				o.handler();
			}
		}
	}*/  
	
	function calculateNewDimensions(slider, offset, vSliderClass)
	{
	    var jqslider = slider.bar;
	    var jqside = slider.side;
	    var jqmain = slider.main;
	    var jqcontainer = slider.container;
	    var dimension = slider.dimension;
	    var inverted = slider.inverted;
	
		var dimensions = Object();
		dimensions = getMinMaxDimensions(slider);
	
	    var dock = (jqslider.hasClass(vSliderClass) ? jqcontainer.offset().left : jqcontainer.offset().top);
	
	    var containerDimension = parseFloat(jqcontainer.css(dimension));
	    if (isNaN(containerDimension) || containerDimension == 0)
	        return;
	
	    var mainNewDimensionRatio = (offset - dock)/containerDimension;
	    var sideNewDimensionRatio = (containerDimension - (offset - dock))/containerDimension;
	
	    if (inverted) {
	        var tmp = mainNewDimensionRatio;
	        mainNewDimensionRatio = sideNewDimensionRatio;
	        sideNewDimensionRatio = tmp;
	    }
	
	    if(mainNewDimensionRatio <= dimensions.minMainDimensionRatio){
	        mainNewDimensionRatio = dimensions.minMainDimensionRatio;
	        sideNewDimensionRatio = dimensions.maxSideDimension/100;
	    }
	    if(sideNewDimensionRatio <= dimensions.minSideDimensionRatio){
	        mainNewDimensionRatio = dimensions.maxMainDimension/100;
	        sideNewDimensionRatio = dimensions.minSideDimensionRatio;
	    }
	
	    jqside.css(dimension, Math.round(sideNewDimensionRatio*10000)/100+"%");
	    jqmain.css(dimension, Math.round(mainNewDimensionRatio*10000)/100+"%");
	}
	
	function getMinMaxDimensions(slider)
	{
		var jqside = slider.side;
		var jqmain = slider.main;
		var jqcontainer = slider.container;
		var dimension = slider.dimension;
	
		var minMainDimensionRatio = 0;
		var minSideDimensionRatio = 0;
		var containerDimension = parseFloat(jqcontainer.css(dimension));
		if (isNaN(containerDimension) || containerDimension == 0)
			return;
		
		
		var mmind = jqmain.css("min-"+dimension);
		var smind = jqside.css("min-"+dimension);
		
		// Ends with %
		if (mmind.indexOf("%", mmind.length - 1) !== -1) {
			// JQuery fix
			// Chrome
			minMainDimensionRatio = parseFloat(mmind)/100;
			minSideDimensionRatio = parseFloat(smind)/100;
		} else {
			// FF
			minMainDimensionRatio = parseFloat(mmind)/containerDimension;
			minSideDimensionRatio = parseFloat(smind)/containerDimension;
		}
		
		
		if (isNaN(minMainDimensionRatio))
			minMainDimensionRatio = 0;
		if (isNaN(minSideDimensionRatio))
			minSideDimensionRatio = 0;
		
		// Force max dimension limitation
		var maxMainDimension = Math.round((1-minSideDimensionRatio)*10000)/100;
		var maxSideDimension = Math.round((1-minMainDimensionRatio)*10000)/100;
		jqmain.css("max-"+dimension, maxMainDimension+"%");
		jqside.css("max-"+dimension, maxSideDimension+"%");
		
		// Snap threshold
		var snapThreshold = 0.00;
		minMainDimensionRatio = Math.round((minMainDimensionRatio + snapThreshold)*100)/100;
		minSideDimensionRatio = Math.round((minSideDimensionRatio + snapThreshold)*100)/100;
		
		var dimensions = Object();
		dimensions['minMainDimensionRatio'] = minMainDimensionRatio;
		dimensions['maxSideDimension'] = maxSideDimension;
		dimensions['minSideDimensionRatio'] = minSideDimensionRatio;
		dimensions['maxMainDimension'] = maxMainDimension;
		
		return dimensions;
	}
});