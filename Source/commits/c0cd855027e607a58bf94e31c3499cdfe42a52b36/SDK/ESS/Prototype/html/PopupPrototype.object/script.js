/* 
 * Redback JS Document
 *
 * Title: RedBack JS Library - jquery.popup | popup library
 * Description: handles all popups
 * Author: RedBack Developing Team
 * Version: --
 * DateCreated: 13/03/2012
 * DateRevised: --
 *
 */
// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// host
var _host = "";

(function(jq) {
	
	// Method Logic for popups
	var methods = {
		init : function(settings, extra, callback){		
			return this.each(function(){
				popup.call(this, settings, extra, callback);
			});		
		},
		exists : function(){
			return !(jq(this).data("popup") === undefined);
		},
		set : function(newContent){		
			return this.each(function(index){
				if (methods.exists.call(jq(this)) !== true)
					return;
					
				var sender = jq(this);
				var popup = sender.data("popup");
				popup.find(".popupContent").html(newContent.eq(index));
				scoutPosition(popup);
				popup.trigger("content.modified");
			});		
		}
	}
	
	jq(document).on("wheel mousewheel DOMMouseScroll scroll", ".popupOverlay", function(ev){
		ev.stopPropagation();
	});
	
	// Attach popup to jQuery functions
	jq.fn.popup = function(content, parent, callback) {
		var sender = jq(this); 
		
		if (content == "exists")
			return methods.exists.call(sender);
			
		if (content == "set")
			return methods.set.call(sender, jq(parent));
			
		settings = acquirePopupSetting(content);
		extra = acquirePopupExtra(content, parent);
		
		// Inner plugin data
		// ___ sender
		extra.sender = sender;
		extra.invertedHorizontalDock = (extra.invertDock == "horizontal" || extra.invertDock == "both");
		extra.invertedVerticalDock = (extra.invertDock == "vertical" || extra.invertDock == "both");
		// ___ sender offset
		var senderOffset = (extra['parent'].is(jq(document.body)) ? sender.offset() : 
			{
				"top" : sender.offset().top - jq(extra['parent']).offset().top,
				"left" : sender.offset().left - jq(extra['parent']).offset().left
			}
		);
		extra.senderOffset = senderOffset;
		extra.calculatedPosition = extra.position;
		
		
		// Toggle popup logic
		var togglePopup = jq();
		jq(".uiPopup").each(function() {
			var jqthis = jq(this);
			if (jqthis.data("popup-extra").sender.is(sender)
				&& settings["type"].match(/toggle/gi)) {
				togglePopup = togglePopup.add(jqthis);
			}
		});
		// If toggle popups are found, then they are disposed and no new popups are created.
		if (togglePopup.length > 0) {
			togglePopup.trigger("dispose");
			return;
		}
		
		// Popup Disposal logic
		popupGCollector(sender, extra.parent);
		
		
		
		return methods.init.call(sender, settings, extra, callback);
	};
	// ---------------------------
	
	// Settings from js
	jq.fn.popup.binding;
	jq.fn.popup.type;
	jq.fn.popup.withTimeout;
	jq.fn.popup.withBackground;
	jq.fn.popup.withFade;
	// Extra settings from js
	jq.fn.popup.id;
	jq.fn.popup.position;
	jq.fn.popup.distanceOffset;
	jq.fn.popup.alignOffset;
	jq.fn.popup.invertDock;
	
	// ------------------------ Managerial Functions ------------------------
	
	// Get settings for popup
	function acquirePopupSetting(content) {
		// Get element's settings
		var settings = {};
		var settingsElem = jq(content).find("[data-popup-settings]").add(jq(content).filter("[data-popup-settings]")).eq(0);
		var settingsInfo = settingsElem.data("popup-settings");
		if (settingsInfo !== undefined)
			settings = {
				"binding" : settingsInfo.binding,
				"type" : settingsInfo.type,
				"withTimeout" : settingsInfo.timeout,
				"withBackground" : settingsInfo.background,
				"withFade" : settingsInfo.fade
			};
			
		// Get js settings
		var jsSettings = {
			"binding" : jq.fn.popup.binding,
			"type" : jq.fn.popup.type,
			"withTimeout" : jq.fn.popup.withTimeout,
			"withBackground" : jq.fn.popup.withBackground,
			"withFade" : jq.fn.popup.withFade
		};
		
		// Extend js with element's [js prevails]
		settings = jq.extend(settings, jsSettings);
		// Initialize empty settings with defaults
		settings = jq.extend({
			"binding" : "on",
			"type" : "obedient",
			"withTimeout" : false,
			"withBackground" : false,
			"withFade" : false
		}, settings);
		settingsElem.removeAttr("data-popup-settings");
		
		// Reset settings for next popup
		delete jq.fn.popup.binding;
		delete jq.fn.popup.type;
		delete jq.fn.popup.withTimeout;
		delete jq.fn.popup.withBackground;
		delete jq.fn.popup.withFade;
		
		return settings;
	}
	
	// Get extra for popup
	function acquirePopupExtra(content, parent) {
		// Get element's requested position info
		var extra = {};
		var extraElem = jq(content).find("[data-popup-extra]").add(jq(content).filter("[data-popup-extra]")).eq(0);
		var cnt = jq(".popupContent", content);
		
		var extraInfo = extraElem.data("popup-extra");
		if (extraInfo !== undefined)
			extra = {
				"id" : extraInfo.id,
				"position" : extraInfo.position,
				"parent" : (extraInfo.parentid !== undefined ? jq("#"+extraInfo.parentid) : undefined),
				"distanceOffset" : extraInfo.distanceOffset,
				"alignOffset" : extraInfo.alignOffset,
				"invertDock" : extraInfo.invertDock
			};
			
		// Get js extra
		var jsExtra = {
			"id" : jq.fn.popup.id,
			"position" : jq.fn.popup.position,
			"parent" : (parent !== undefined ? jq(parent) : undefined),
			"distanceOffset" : jq.fn.popup.distanceOffset,
			"alignOffset" : jq.fn.popup.alignOffset,
			"invertDock" : jq.fn.popup.invertDock
		};
		
		// Extend js with element's [js prevails]
		extra = jq.extend(extra, jsExtra);
		// Initialize empty data with defaults
		extra = jq.extend({
			"id" : "pp_"+Math.floor(Math.random()*100000),
			"content" : (cnt.length == 0 ? jq("<div class='popupContent'></div>").html(content) : cnt),
			"position" : "user",
			"parent" : jq(document.body),
			"distanceOffset" : 0,
			"alignOffset" : 0,
			"invertDock" : "none"
		}, extra);
		extraElem.removeAttr("data-popup-extra");
		
		// Reset settings for next popup
		delete jq.fn.popup.id;
		delete jq.fn.popup.position;
		delete jq.fn.popup.distanceOffset;
		delete jq.fn.popup.alignOffset;
		delete jq.fn.popup.invertDock;
		
		return extra;
	}
	
	// Garbage collector for popups. Disposes old popups.
	function popupGCollector(sender, parent) {
		// Remove any visible popups that don't contain the sender
		jq('.uiPopup').not(".fnppContainer")
			.not(":has(.fnppContainer, .fasppContainer)")
			.each(function() {
			
			if (/*jq(this).has(sender.get(0)).length == 0 || */jq(this).data("popup-extra").parent.is(parent)) {
				disposePopup(jq(this));
			}
		}); 
	}
	
	// Initialization of popup (assign listeners etc...)
	function popup(settings, extra, callback) {
		var sender = jq(this);
		
		// Popup
		var systemPopup = jq("<div class='uiPopup elevated' />"); 
		systemPopup.data("popup-settings", settings);
		systemPopup.data("popup-extra", extra);
		//Append arrow, content and add id
		systemPopup.append(jq("<div class='ppArrowBack'></div>"));
		systemPopup.append(extra['content']);
		systemPopup.append(jq("<div class='ppArrowFront'></div>"));
		systemPopup.attr("id", extra['id']);
		
		// If a background is requested, append it in the body [preparation step].
		if (settings['withBackground']/* && extra['parent'].children(".popupOverlay").length == 0*/){
			extra['parent'].append("<div class='popupOverlay' />");
			//extra['parent'].addClass("popupFixedParent");
		}
		
		// Attach functionality to popup
		createPopup.call(systemPopup);
		scoutPosition(systemPopup);
		
		if (settings['withBackground'])
			systemPopup.detach().appendTo(extra['parent'].children(".popupOverlay"));
		
		// Dispose on click out, if binding type is obedient
		// This needs to be inside a timeout, to avoid being called on the spot by a bubbling click that may have invoked the popup.
		if ((!settings.withBackground) && settings.type.match(/obedient/gi)) {
			extra.parentContainer = (extra.parent.is(jq(document.body)) ? jq(document):extra.parent);
			extra.parentContainer.off("click.popup"); 
			extra.parentContainer.on("click.popup", function(ev) {
				// If click out from popup && sender
				var jqthis = (jq(this).is(jq(document)) ? jq(document.body):jq(this));
				var popup = jqthis.find(".uiPopup").not(".elevated").filter(function(){
					var settings = jq(this).data("popup-settings");
					return settings && settings.type && (settings.type != "persistent");
				});
				if (popup.length == 0)
					return;
				
				//var sndr = popup.data("popup-extra").sender;
				if (jq(ev.target).closest(popup).length == 0 /*
					&& jq(ev.target).closest(sndr).length == 0*/){
					popup.trigger("dispose");
				}
			});
		}
		
		// This Elevated technique prevents recently created popup from being garbaged after a click bubble.
		setTimeout(function(){
			jq(".uiPopup.elevated").removeClass("elevated");
		}, 200);
		
		if (settings.withBackground) {
			systemPopup.addClass('wbg');
			extra.parent.off("click", ".popupOverlay");
			extra.parent.one("click", ".popupOverlay", function(ev){ 
			//	ev.stopPropagation();
				jq(this).children(".uiPopup").filter(function(){
					var settings = jq(this).data("popup-settings");
					return settings && settings.type && (settings.type != "persistent");
				}).trigger("dispose");
			});
		}
		
		// Link sender with the popup
		sender.data("popup", systemPopup);
		
		// When sender is destroyed, so does the popup
		// Check "jq.event.special.destroyed" event at the end of the file for details.
		sender.off("destroyed.popup");
		sender.on("destroyed.popup", function(){
			clearTimeout(systemPopup.data("disposalTimer"));
			systemPopup.trigger("dispose");
		});
		
		sender.off("dispose.popup");
		sender.one("dispose.popup", function(ev){
			ev.stopPropagation();
			var popup = jq(this).data("popup");
			if (popup === undefined)
				return;
			clearTimeout(popup.data("disposalTimer"));
			popup.trigger("dispose");
		});
		
		systemPopup.data("popup-extra", extra);
		
		jq(document).off("keydown.popup");
		jq(document).on("keydown.popup", function(ev){
			if (ev.which != 27)
				return;
				 
			jq(".uiPopup")/*.filter(function(){
				var settings = jq(this).data("popup-settings");
				return settings && settings.type && (settings.type != "persistent");
			})*/.trigger("dispose");
		});
		
		// Run callback function, if any, with popup element as 'this'
		if (typeof callback == 'function') {
			callback.call(systemPopup.get(0));
		}
	}
	
	// ------------------------ Creation and Removal Functions ------------------------
	
	// Popup functionality. Callback, if function, will be executed after the completion of the creation
	function createPopup() {
		var systemPopup = jq(this);
		var settings = systemPopup.data("popup-settings");
		
		// Initialize position
		systemPopup.css({
			"position" : "fixed",
			"top" : "",
			"left" : "",
			"bottom" : "",
			"right" : ""
		});
		
		// Disposal function
		systemPopup.on("dispose.popup", function(ev) {
			jq(this).trigger("cancel");
			disposePopup(jq(this));	
		});
		
		// dispose on closeButton click -- reevaluate
		systemPopup.on("click.close.popup", ".actionButton", function() {
			if (jq(this).closest(".uiNotification").hasClass("uiPopup"))
				disposePopupHandler(systemPopup);
		});
		
		// check if popup needs to be timed out
		if (settings.withTimeout) {
			timeoutPopup.call(systemPopup.get(0));
			// Timeout reset
			systemPopup.on({
				"mouseenter.popup": function() {
					systemPopup.find(".actionButton").empty();
					systemPopup.removeClass("timeout");
					clearTimeout(systemPopup.data("disposalTimer"));
				},
				// Reevaluate svg logic
				"mouseleave.popup":function() {
					timeoutPopup.call(systemPopup.get(0));
				}
			});
		}
		
		systemPopup.appendTo(systemPopup.data("popup-extra").parent);
	}
	
	// Dispose handler for the popup
	function disposePopupHandler(systemPopup) {
		systemPopup.trigger("dispose");
	} 
	
	// Timout handler for the popup
	function disposePopupWithTimoutHandler(systemPopup) {
		systemPopup.data("popup-settings").withFade = true;
		disposePopupHandler(systemPopup);
	}
	
	// Add timeout functionality to popup -- reevaluate
	function timeoutPopup() {
		var systemPopup = jq(this);
		systemPopup.addClass("timeout");
		systemPopup.data("disposalTimer", setTimeout(function(){disposePopupWithTimoutHandler(systemPopup)}, 3000));
	}
	 
	// Disposes popup background, if any.
	function disposePopupBackground(systemPopup) {
		var settings = systemPopup.data("popup-settings");
		var extra = systemPopup.data("popup-extra");
		// Check if fade should be used
		if (settings.withFade) {
			// Fade background out and then remove it
			jq(".popupOverlay", extra.parent).fadeOut("slow", function() {
				jq(this).remove();
			});
		} else {
			// Remove background
			jq(".popupOverlay", extra.parent).css("display", "").remove();
		}
		extra.parent.removeClass("popupFixedParent");
	}
	
	// Disposes popup
	function disposePopup(systemPopup) {
		var settings = systemPopup.data("popup-settings");
		var extra = systemPopup.data("popup-extra");
		var sender = systemPopup.data("popup-extra").sender;
		
		// Clear timer
		clearTimeout(systemPopup.data("disposalTimer"));
		// Unlink events
		systemPopup.off(".popup");
		sender.removeData("popup");
		// Checks if the popup should not be reinvoked in the future
		if (settings.binding == "one")
			sender.off(".popup");
		
		// Remove background
		if (settings.withBackground)
			disposePopupBackground(systemPopup);
			
		// Remove popup -- Remove or detach?
		if (settings.withFade) {
			// Fades popup out and then removes it
			systemPopup.fadeOut("slow", function() {
				jq(this).remove();
			});
		} else {
			// Removes popup
			systemPopup.remove();
		}
		
		// Unlink events
		sender.off(".popup");
		extra.parent.off(".popup");
		jq(document.body).off(".popup");
		jq(document).off(".popup");
		jq(window).off(".popup");
	}
	
	// Special destroyed event
	jq.event.special.destroyed = {
		remove: function(o) {
			if (o.handler) {
				o.handler();
			}
		}
	}
	
	// Reveals the background of the popup, if asked
	function revealPopupBackground(systemPopup) {
		var settings = systemPopup.data("popup-settings");
		var extra = systemPopup.data("popup-extra");
		// Checks fade
		if (settings.withFade) {
			jq(".popupOverlay", extra.parent).fadeIn("slow");
		} else {
			jq(".popupOverlay", extra.parent).css("display", "block");
		}
	}
	
	// Reveals popup
	function revealPopup(systemPopup) {
		var settings = systemPopup.data("popup-settings");
		var extra = systemPopup.data("popup-extra");
		// Checks fade
		if (settings.withFade) {
			systemPopup.fadeIn("slow");
		} else {
			systemPopup.css("display", "block");
		}
		
		// Reveals background, if needed
		if (settings.withBackground)
			revealPopupBackground(systemPopup);
		
		// Position Arrow, if needed
		var ppArrowBack = systemPopup.children(".ppArrowBack");
		if (ppArrowBack.length == 0)
			return;
		
		var ppArrowFront = systemPopup.children(".ppArrowFront");
		var ppArrowHalf = 8;//5+1
		var paddingHalf = 2;
		var sender = extra.sender;
		
		var arrOffset;
		var inPopup;
		var arrDock;
		if (extra.pointer.alignment == "center"
			|| ((extra.pointer.alignment == "left" || extra.pointer.alignment == "right")
				&& sender.outerWidth() > systemPopup.outerWidth())
			|| ((extra.pointer.alignment == "top" || extra.pointer.alignment == "bottom")
				&& sender.outerHeight() > systemPopup.outerHeight())) {
			systemPopup.addClass(extra.pointer.orientation+"center"+"Pointer");
		} else if (extra.pointer.alignment == "left" || extra.pointer.alignment == "right"){
			arrOffset = sender.offset().left - systemPopup.offset().left - paddingHalf
					+ sender.outerWidth()/2 - ppArrowHalf;
			arrDock = "left";
			inPopup = (arrOffset >= 0 && arrOffset <= systemPopup.width());
		} else { 
			arrOffset = sender.offset().top - systemPopup.offset().top - paddingHalf
					+ sender.outerHeight()/2 - ppArrowHalf;
			arrDock = "top";
			inPopup = (arrOffset >= 0 && arrOffset <= systemPopup.height());
		}
		
		if (arrOffset !== undefined && inPopup) {
			systemPopup.addClass(extra.pointer.orientation+"Pointer");
			ppArrowBack.css(arrDock, arrOffset);
			ppArrowFront.css(arrDock, arrOffset+1);
		}
	}
	
	// ------------------------ Positioning Functions ------------------------
	
	// Position popup accordingly
	function scoutPosition(systemPopup) {
		var extra = systemPopup.data("popup-extra");
		
		// Relative to parent
		if (jq.type(extra.position) == "object") {
			relativeToParent(systemPopup);
			return;
		}
		
		// Check if str has alignment as well 
		var posArr = extra.position.split("|");
		
		// Relative to window [top, bottom, left, right, center, user, 0-9]
		if (posArr.length == 1) {
			relativeToWindow(systemPopup);
		} else {
			relativeToSender(systemPopup);
		}
	}
	
	function relativeToParent(systemPopup) {
		var extra = systemPopup.data("popup-extra");
	
		systemPopup.css({"top" : "", "bottom" : "", "left" : "", "right" : "", "position" : "absolute", "margin-left":"", "margin-right":"", "margin-top":"", "margin-bottom":""});
		systemPopup.data("popup-extra").calculatedPosition = jq.extend({
			"top" : "",
			"bottom" : "",
			"left" : "",
			"right" : "",
			"position" : "absolute"
		}, extra.position);
		
		repositionPopup(systemPopup);
	}
	
	function relativeToWindow(systemPopup) {
		var extra = systemPopup.data("popup-extra");
		var settings = systemPopup.data("popup-settings");
		var screenMap = [ "left:0%|bottom:0%", "bottom", "right:0%|bottom:0%", "left", "center", "right", "left:0%|top:0%", "top", "right:0%|top:0%" ];
		// Remove anchors
		var ppPos = "fixed";
		if (settings.withBackground)
			ppPos = "absolute";
		systemPopup.css({"top" : "", "bottom" : "", "left" : "", "right" : "", "position" : ppPos, "margin-left":"", "margin-right":"", "margin-top":"", "margin-bottom":""});
		
		extra.calculatedPosition = jq.trim(extra['position']);
		var posInt = parseInt(extra.calculatedPosition);
		if (extra.calculatedPosition == "user")
			extra.calculatedPosition = "top:15%|center";
		else if (posInt > 0 && posInt < 10)
			extra.calculatedPosition = screenMap[posInt - 1]
		else if (extra.calculatedPosition != "top" && extra.calculatedPosition != "bottom" && extra.calculatedPosition != "left" && extra.calculatedPosition != "right")
			extra.calculatedPosition = "center";
		
		systemPopup.data("popup-extra").calculatedPosition = extra.calculatedPosition;
		repositionPopup(systemPopup);
	}
	
	function relativeToSender(systemPopup) {
		// Remove anchors
		systemPopup.css({"top" : "", "bottom" : "", "left" : "", "right" : "", "position" : "absolute", "margin-left":"", "margin-right":"", "margin-top":"", "margin-bottom":""});
		
		// Add Border
		systemPopup.addClass("wbrdr");
		
		var position = systemPopup.data("popup-extra").position;
		
		var posArr = position.split("|");
		posArr[0] = jq.trim(posArr[0]);
		systemPopup.data("popup-extra").pointer = {};
		
		if (posArr[0] == "top" || posArr[0] == "bottom")
			orientatePopupVertically(systemPopup, position, 0);
		else if (posArr[0] == "left" || posArr[0] == "right")
			orientatePopupHorizontally(systemPopup, position, 0);
		else {
			// invalid -> switch to center orientation relative to the window
			systemPopup.data("popup-extra").calculatedPosition = "center";
			repositionPopup(systemPopup);
		}	
	}
	
	// Reposition popup 
	function repositionPopup(systemPopup)
	{
		// Relative to window or parent
		// Request data for centering
		// Popup height
		var h = systemPopup.height();
		// Popup width
		var w = systemPopup.width();
		
		// ___ window width
		var windowWidth = jq(window).width();  
		// ___ window height
		var windowHeight = jq(window).height();
		
		var extra = systemPopup.data("popup-extra");
		
		systemPopup.children(".ppArrowBack, .ppArrowFront").remove();
		
		// Relative to window or parent [depends on position settings]
		if (jq.type(extra.calculatedPosition) == "object") {
			for (var i in extra.calculatedPosition) {
				if (extra.calculatedPosition[i] == "center" 
					&& (i == "top" || i == "bottom")) {
					extra.calculatedPosition[i] = "50%";
					systemPopup.css("margin-"+i, "-"+h/2+"px");
				}else if (extra.calculatedPosition[i] == "center" 
					&& (i == "left" || i == "right")) {
					extra.calculatedPosition[i] = "50%";
					systemPopup.css("margin-"+i, "-"+w/2+"px");
				}
			}
			systemPopup.css(extra.calculatedPosition);
			revealPopup(systemPopup);
			return;
		}
		
		var posArr = extra.calculatedPosition.split("|");
		
		if (posArr.length == 1) {						
			// calculate top
			var t = (jq.trim(extra.calculatedPosition) == "top") ? 0 : "50%";
			// calculate left
			var l = (jq.trim(extra.calculatedPosition) == "left") ? 0 : "50%";
			// calculate bottom
			var b = (jq.trim(extra.calculatedPosition) == "bottom") ? (t="",b=0) : "";
			// calculate right
			var r = (jq.trim(extra.calculatedPosition) == "right") ? (l="",r=0) : "";
			
			if (t == "50%")
				systemPopup.css("margin-top", "-"+h/2+"px");
			if (l == "50%")
				systemPopup.css("margin-left", "-"+w/2+"px");
			
			// place popup accordingly
			systemPopup.css({ 
				"top" : t,  
				"left" : l,
				"bottom" : b,
				"right" : r
			}); 
		} else {
			// vertical
			var metrics1 = posArr[0].split(":");
			// horizontal
			var metrics2 = posArr[1].split(":");
			
			if (jq.trim(metrics1[0]) == "center") {
				// calculate top
				var t = "50%";
				systemPopup.css("margin-top", "-"+h/2+"px");
				// place popup accordingly
				metrics1[0] = "top";
				metrics1[1] = t;
			}
			if (jq.trim(metrics2[0]) == "center") {
				// calculate left
				var l = "50%"; 
				systemPopup.css("margin-left", "-"+w/2+"px");
				// place popup accordingly
				metrics2[0] = "left";
				metrics2[1] = l;
			}
			
			// place popup accordingly
			systemPopup.css(metrics1[0], metrics1[1]).css(metrics2[0], metrics2[1]);
		}
		
		// reveal the popup
		revealPopup(systemPopup);
	}
	
	// Reposition popup next to sender
	function stickToSender(systemPopup) {
		// popup height
		var h = systemPopup.height();
		// popup width
		var w = systemPopup.width();
		
		var extra = systemPopup.data("popup-extra");
		// Sender
		var sender = extra.sender;
		var senderOffset = extra.senderOffset;
		
		var posArr = extra.calculatedPosition.split("|");
		// Orientation
		var metrics1 = posArr[0].split(":");
		// Alignment
		var metrics2 = posArr[1].split(":");
		
		// Position popup
		systemPopup.css({  
			"position": "absolute"
		}); 
		
		systemPopup.css(metrics1[0], parseInt(metrics1[1]));
		systemPopup.css(metrics2[0], parseInt(metrics2[1]));
		
		// Reveal the popup
		revealPopup(systemPopup);
	}
	
	// Vertical orientation of popup. Tries should be initially set to 0
	// Search for available position inside the parent and window, starting from the provided position.
	function orientatePopupVertically(systemPopup, position, tries) {
		var extra = systemPopup.data("popup-extra");
		var posArr = position.split("|");
		// Popup's onscreen distance from sender in px
		var offDist = extra['distanceOffset']+10;
		// Reference point
		var pivotPoint;
		// Sender
		var sender = extra.sender;
		// Sender's offset in parent
		var senderOffset = extra['senderOffset'];
		
		var dock = (extra.invertedVerticalDock === true ? "bottom" : "top");
		// Switching map
		var switchMap = {
			"top" : "bottom",
			"bottom" : "top"
		};
		posArr[0] = jq.trim(posArr[0]);
		// Check given orientation
		systemPopup.data("popup-extra").pointer.orientation = switchMap[posArr[0]];
		if (posArr[0] == "top") {
			// Above sender 
			// Pivot point depends on the docking of the sender
			if (extra.invertedVerticalDock === true)
				pivotPoint = extra['parent'].height() - senderOffset.top + offDist;
			else
				pivotPoint = senderOffset.top - offDist - systemPopup.outerHeight();
		} else {
			// Below sender
			// Pivot point depends on the docking of the sender
			if (extra.invertedVerticalDock === true)
				pivotPoint = extra['parent'].height() - senderOffset.top - sender.outerHeight() - systemPopup.outerHeight() - offDist;
			else
				pivotPoint = senderOffset.top + sender.outerHeight() + offDist;
		}
		
		// Check if popup is out of parent bounds
		var inParent = isInsideVerticalElementBounds(systemPopup, extra['parent'].offset().top + pivotPoint, extra['parent']) || extra['parent'].is(jq(document.body));
		// Check if popup is out of window bounds
		var inViewport = isInsideVerticalWindowBounds(systemPopup, extra['parent'].offset().top + pivotPoint);
		
		if ((inParent && inViewport) || tries > 1) {
			// Set top orientation
			systemPopup.data("popup-extra").calculatedPosition = dock+':' + (pivotPoint) + '|';
			// Sets Horizontal alignment with the sender
			alignHorizontally(systemPopup, posArr[1], 0);
		} else {
			// Change to bottom orientation
			orientatePopupVertically(systemPopup, switchMap[posArr[0]]+"|"+posArr[1], ++tries);
		}
	}
	
	// Horizontal orientation of popup. Tries should be initially set to 0
	// Search for available position inside the parent and window, starting from the provided position.
	function orientatePopupHorizontally(systemPopup, position, tries) {
		var extra = systemPopup.data("popup-extra");
		var posArr = position.split("|");
		// Popup's onscreen distance from sender in px
		var offDist = extra['distanceOffset']+10;
		// Reference point
		var pivotPoint;
		// Sender
		var sender = extra.sender;
		// Sender's offset in parent
		var senderOffset = extra['senderOffset'];
		
		var dock = (extra.invertedHorizontalDock === true ? "right" : "left");
		// Switching map
		var switchMap = {
			"left" : "right",
			"right" : "left"
		};
		posArr[0] = jq.trim(posArr[0]);
		systemPopup.data("popup-extra").pointer.orientation = switchMap[posArr[0]];
		// Check given orientation
		if (posArr[0] == "left") { 
			// Left side of sender
			// Pivot point depends on the docking of the sender
			if (extra.invertedHorizontalDock === true)
				pivotPoint = extra['parent'].width() - senderOffset.left + offDist;
			else
				pivotPoint = senderOffset.left - offDist - systemPopup.outerWidth();
		} else {
			// Right  
			// Pivot point depends on the docking of the sender
			if (extra.invertedHorizontalDock === true)
				pivotPoint = extra['parent'].width() - senderOffset.left - sender.outerWidth() - systemPopup.outerWidth() - offDist;
			else
				pivotPoint = senderOffset.left + sender.outerWidth() + offDist;
		}
		
		// Check if popup is out of parent bounds
		var inParent = isInsideHorizontalElementBounds(systemPopup, extra['parent'].offset().left + pivotPoint, extra['parent']) || extra['parent'].is(jq(document.body));
		
		// Check if popup is out of window bounds
		var inViewport = isInsideHorizontalWindowBounds(systemPopup, extra['parent'].offset().left + pivotPoint);
		
		if ((inParent && inViewport) || tries > 1) {
			// Set left orientation
			systemPopup.data("popup-extra").calculatedPosition = dock + ':' + (pivotPoint) + '|';
			// Sets Vertical alignment with the sender
			alignVertically(systemPopup, posArr[1], 0);
		} else {
			// Change to right orientation
			orientatePopupHorizontally(systemPopup, switchMap[posArr[0]]+"|"+posArr[1], ++tries);
		}
	}
	
	function isInsideVerticalElementBounds(systemPopup, pivotPoint, element) {
		var elemOffset = element.offset().top;
		if (elemOffset > pivotPoint 
			|| (elemOffset + element.height() < pivotPoint + systemPopup.outerHeight()))
			return false;
		return true;
	}
	
	function isInsideVerticalWindowBounds(systemPopup, pivotPoint) {
		var windowOffset = jq(document).scrollTop();
		if (windowOffset > pivotPoint 
			|| (windowOffset + jq(window).height() < pivotPoint + systemPopup.height()))
			return false;
		return true;
	}
	
	function isInsideHorizontalElementBounds(systemPopup, pivotPoint, element) {
		var elemOffset = element.offset().left;
		if (elemOffset > pivotPoint 
			|| (elemOffset + element.width() < pivotPoint + systemPopup.outerWidth()))
			return false;
		return true;
	}
	
	function isInsideHorizontalWindowBounds(systemPopup, pivotPoint) {
		var windowOffset = jq(document).scrollLeft();
		if (windowOffset > pivotPoint 
			|| (windowOffset + jq(window).width() < pivotPoint + systemPopup.width()))
			return false;
		return true;
	}
	
	// Aligns popup with sender Horizontally. Tries should initially be set to 0
	function alignHorizontally(systemPopup, position, tries) {
		var extra = systemPopup.data("popup-extra");
		// Sender's offset in parent
		var senderOffset = extra['senderOffset'];
		// Onscreen alignment distance
		var aliDist = extra['alignOffset'];
		// Point of reference
		var pivotPoint;
		// Sender
		var sender = extra.sender;
		// Amount that popup exceeds window
		var elemDiff;
		
		var dock = (extra.invertedHorizontalDock === true ? "right" : "left");
		// Switching map
		var switchMap = {
			"left" : "right",
			"right" : "left",
			"center" : "right"
		};
		
		systemPopup.data("popup-extra").pointer.alignment = jq.trim(position);
		var borderSize = 1;
		
		switch(jq.trim(position)) {
			case "left":
				// Left alignment
				// Pivot point depends on the docking of the sender
				if (extra.invertedHorizontalDock === true)
					pivotPoint = extra['parent'].width() - senderOffset.left - systemPopup.outerWidth() + borderSize;
				else		
					pivotPoint = senderOffset.left - borderSize;
				break;
			case "right":
				// Right alignment
				// Pivot point depends on the docking of the sender
				if (extra.invertedHorizontalDock === true)
					pivotPoint = extra['parent'].width() - senderOffset.left - sender.outerWidth() - borderSize;
				else
					pivotPoint = senderOffset.left + sender.outerWidth() - systemPopup.outerWidth() + borderSize;
				break;
			case "center":
				// Center alignment
				// Pivot point depends on the docking of the sender
				if (extra.invertedHorizontalDock === true)
					pivotPoint = extra['parent'].width() - senderOffset.left - sender.outerWidth() - ((systemPopup.outerWidth()-sender.outerWidth())/2);
				else
					pivotPoint = senderOffset.left - ((systemPopup.outerWidth()-sender.outerWidth())/2);
				break;
			default:
				// invalid -> switch to center orientation
				systemPopup.data("popup-extra").calculatedPosition = "center";
				repositionPopup(systemPopup);
				return;
		}
		
		// Check if popup is out of parent bounds
		var inParent = isInsideHorizontalElementBounds(systemPopup, extra['parent'].offset().left + pivotPoint, extra['parent']) || extra['parent'].is(jq(document.body));
		// Check if popup is out of window bounds
		var inViewport = isInsideHorizontalWindowBounds(systemPopup, extra['parent'].offset().left + pivotPoint);
		
		// Set alignment or try another
		if ((inParent && inViewport) || tries > 1) {
			systemPopup.data("popup-extra").calculatedPosition += dock + ":" + (pivotPoint+aliDist);
			stickToSender(systemPopup);
		} else
		  	alignHorizontally(systemPopup, switchMap[position], ++tries);
	}
	// Aligns popup with sender Vertically. Tries should initially be set to 0
	function alignVertically(systemPopup, position, tries) {
		var extra = systemPopup.data("popup-extra");
		// Sender's offset in parent
		var senderOffset = extra['senderOffset'];
		// Onscreen alignment distance
		var aliDist = extra['alignOffset'];
		// Point of reference
		var pivotPoint;
		// Sender
		var sender = extra.sender;
		// Amount that popup exceeds window
		var elemDiff;
		
		var dock = (extra.invertedVerticalDock === true ? "bottom" : "top");
		// Switching map
		var switchMap = {
			"top" : "bottom",
			"bottom" : "top",
			"center" : "bottom"
		};
		
		systemPopup.data("popup-extra").pointer.alignment = jq.trim(position);
		var borderSize = 1;
		switch(jq.trim(position)) {
			case "top":
				// Teft alignment
				// Pivot point depends on the docking of the sender
				if (extra.invertedVerticalDock === true)
					pivotPoint = extra['parent'].height() - senderOffset.top  - systemPopup.outerHeight() + borderSize;
				else	
					pivotPoint = senderOffset.top - borderSize;
				break;
			case "bottom":
				// Bottom alignment
				// Pivot point depends on the docking of the sender
				if (extra.invertedVerticalDock === true)
					pivotPoint = extra['parent'].height() - senderOffset.top - sender.outerHeight() - borderSize;
				else
					pivotPoint = senderOffset.top + sender.outerHeight() - systemPopup.outerHeight() + borderSize;
				break;
			case "center":
				// Center alignment
				// Pivot point depends on the docking of the sender
				if (extra.invertedVerticalDock === true)
					pivotPoint = extra['parent'].height() - senderOffset.top - sender.outerHeight() - ((systemPopup.outerHeight()-sender.outerHeight())/2);
				else
					pivotPoint = senderOffset.top - ((systemPopup.outerHeight()-sender.outerHeight())/2);
				break;
			default:
				// invalid -> switch to center orientation
				systemPopup.data("popup-extra").calculatedPosition = "center";
				repositionPopup(systemPopup);
				return;
		}
		// Check if popup is out of parent bounds
		var inParent = isInsideVerticalElementBounds(systemPopup, extra['parent'].offset().top + pivotPoint, extra['parent']) || extra['parent'].is(jq(document.body));
		// Check if popup is out of window bounds
		var inViewport = isInsideVerticalWindowBounds(systemPopup, extra['parent'].offset().top + pivotPoint);
		
		// Set alignment or try another
		if ((inParent && inViewport) || tries > 1) {
			systemPopup.data("popup-extra").calculatedPosition += dock + ":" + (pivotPoint+aliDist);
			stickToSender(systemPopup);
		} else
		  	alignVertically(systemPopup, switchMap[position], ++tries);
	}
	
})(jQuery);