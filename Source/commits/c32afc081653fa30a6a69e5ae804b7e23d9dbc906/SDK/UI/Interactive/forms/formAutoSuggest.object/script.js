var jq=jQuery.noConflict();
jq(document).one("ready", function() {
	
	// all elements that have autosuggestion
	var autosuggestInputElements = "form [data-ajax-as]";
	var asContainer = null;
	var asGroup = null; 
	var asListItem = null;
	var asPopup = null;
	var jqpopup = jq();
	var cntxt = jq();
	
	// autosuggests values from the database
	jq(document).on({
		"keyup.autosuggest" : function(ev) {
			var jqthis = jq(this);
			if (((57 >= ev.which && ev.which >= 48) //number
						|| (105 >= ev.which && ev.which >= 96) //numpad number
						|| (90 >= ev.which && ev.which >= 65) //letter
						|| (ev.which == 8) || (ev.which == 46) //backspace | delete  
						|| (111 >= ev.which && ev.which >= 106) //+-*./
						|| (192 >= ev.which && ev.which >= 186) //;=, -.`~
						|| (222 >= ev.which && ev.which >= 219) //'\][
						|| (ev.which == 226)) && jq.type(jqthis.data("doTimeouttyping")) == "undefined") {
				jqthis.trigger("fetch.autosuggest");
			}
		},
		
		"fetch.autosuggest" : function() {
			var jqthis = jq(this);
			var autosuggestFormParent = jqthis.closest("form");
			var autosuggestPopup = jq(document.body).children("#"+jqthis.attr("id")+"_faspp");
			jqthis.siblings("input:hidden").attr("value",  "");
			
			// define the parent of the autosuggest
			var parent = jq(document.body);
			if(jqthis.data("type-as") == "inline"){
				parent = jqthis.parent();
			}

			// if autosuggest area already exists
			if (autosuggestPopup.length > 0) {
				// reveal it
				autosuggestPopup.removeClass("noDisplay");
				// previous input
				var prevInput = autosuggestPopup.find(".dtaBody > .pivot");
				if (jqthis.val().match(new RegExp(RegExp.escaped(prevInput.text()), "i")) != null
					&& jqthis.val().match(new RegExp(RegExp.escaped(prevInput.text()), "i")) != "") {
					// if value extends previous one
					// hide elements
					// filter content
					
					var asGroups = autosuggestPopup.find(".fasppGroup");
					var visibleGroups = asGroups.not(".noDisplay").length;
					asGroups.each(function() {
						var asElements = jq(this).find(".suggestListItem");
						var visibleListElements = asElements.not(".noDisplay").length;
						var hits = 0;
						asElements.each(function() {
							// if no match,  hide
							if (!jq(this).attr("filter").match(new RegExp(RegExp.escaped(jqthis.val()), "i"))) {
								jq(this).remove();
							} else {
								if (jqthis.val().match(new RegExp(RegExp.escaped("^"+jq(this).attr("filter")+"$"), "i"))) {
									jqthis.siblings("input:hidden").attr("value",  jq(this).data("item-id"));
									hits++;
								}
							}
						});
						if (hits != 1) {
							jqthis.siblings("input:hidden").attr("value",  "");
						}
						
						asElements = jq(this).find(".suggestListItem");
						
						if (asElements.length == 0 && asGroups.length > 1) {
							jq(this).remove();
						} else {
							asElements.slice(0, visibleListElements).removeClass("noDisplay");
							jq(this).find(".more")
								.find(".moreTitle > .query").text(" "+jqthis.val()).end()
								.find(".moreSubtitle")
									.find(".topResultsNum").text(" "+asElements.not(".noDisplay").length+" ").end()
									.find(".totalResultsNum").text(" "+asElements.length+" ");
						}
					});
					
					asGroups.slice(0, visibleGroups).removeClass("noDisplay");
					
					// update previous input value
					prevInput.text(jqthis.val());
				} else if (jqthis.val().length > 2) {
					// if input has 3 chars or more
					// load autosuggest
					// here ajax
					autosuggestPopupViaAJAX(//popup properties
						{
							sender: jqthis,  
							parent: parent,
							ppID: jqthis.attr("id")+'_faspp',  
							filepath: jqthis.data("ajax-as"), 
							data: {
								"value" : jqthis.val(), 
								"locale" : autosuggestFormParent.attr("locale")
							},  
							position: 'bottom|left', 
							timeout: 500
						}
					);
				} else {
					//remove autosuggest
					autosuggestPopup.trigger("dispose");
					jqpopup = jq();
				}		
			} else {
				// create area
				if (jqthis.val().length > 2) {
					// load autosuggest
					// here ajax
					autosuggestPopupViaAJAX(//popup properties
						{
							sender: jqthis,  
							parent: parent,
							ppID: jqthis.attr("id")+'_faspp',  
							filepath: jqthis.data("ajax-as"), 
							data: {
								"value" : jqthis.val(), 
								"locale":autosuggestFormParent.attr("locale")
							},  
							position: 'bottom|left'
						}
					);
				}
			}
				
		}, 
		
		"focusin.autosuggest" : function(ev,  wasButtonClicked) {
			var jqthis = jq(this);
			jqthis.removeClass("invalid");
			if (jqthis.val().length > 2 && jq.type(jqthis.data("doTimeouttyping")) == "undefined"
				&& (jq.type(wasButtonClicked)=="undefined") && (wasButtonClicked !== "true")) {
				jqthis.trigger("fetch.autosuggest");
			}
		}
			
	},  autosuggestInputElements);	
	
	function autosuggestPopupViaAJAX(options) { 
    	// Create some defaults,  extending them with any options that were provided
		var settings = jq.extend({
			"sender":jq(document), 
			"parent":jq(document.body),
			// popup ID
			"ppID":"", 
			// path to get content from
			"filepath":null, 
			// data to send to the server
			"data":"", 
			// orientation and alignment
			"position":"bottom|left", 
			// timeout for refresh
			"timeout":150
		}, options);
		
		var createPopup = function() {
			if (jq.type(settings['sender'].data("doTimeouttyping")) == "undefined") {
				cntxt = asContainer.clone().attr("id", settings['ppID']);
				cntxt.find(".dtaBody > .loading").removeClass("noDisplay");
				cntxt.css("width",  settings['sender'].innerWidth());
				
				if (jq.type(settings['sender'].data("redPopup")) == "undefined") {
					settings['sender'].redPopups("ajaxGlobalPopup", 
						{
							"sender": settings['sender'], 
							"binding":"on", 
							"type":"obedient", 
							"withFade": true
						}, 
						//popup properties
						{
							sender: settings['sender'],  
							context: cntxt, 
							parent: settings['parent'],
							ppID: settings['ppID'],  
							position: 'bottom|center'
						}
					);
	
					jqpopup = cntxt.parent();
				} else {
					settings['sender'].data("redPopup").setContext(cntxt);
				}
			}
			
			settings['sender'].doTimeout("typing",  settings['timeout'],  function() {	
				if (settings['sender'].val().length > 2) {			
					currentSettings = settings;
					currentSettings['data'].value = settings['sender'].val();
					currentSettings['data'].locale = settings['data'].locale;
					//console.log(currentSettings['data']);
					// ajax query to get content
					jq.ajax({
						url: _host+currentSettings['filepath'], 
						data: currentSettings['data'], 
						dataType: "xml", 
						// on success
						success: function(contentXML) {
							//console.log(contentXML);
							var asItemHeight = 56;//px
							var groupMinHeight = 133;//px
							var extraElementsOffset = 3;
							var extraGroupsOffset = 3;
							var orientation = "top";//below
							// determine orientation of as
							var xmlGroups = jq(contentXML).find("group:not(:empty)");
							var availableSpaceBelowSender = jq(window).scrollTop()+jq(window).height()
															-(currentSettings['sender'].offset().top+currentSettings['sender'].outerHeight());
							var availableSpaceAboveSender = currentSettings['sender'].offset().top-jq(window).scrollTop();
							var groupsBelow = availableSpaceBelowSender/groupMinHeight;
							var groupsAbove = availableSpaceAboveSender/groupMinHeight;
							
							var extraItemsAbove = (availableSpaceAboveSender-groupMinHeight*xmlGroups.length)/asItemHeight; 
							var extraItemsBelow = (availableSpaceBelowSender-groupMinHeight*xmlGroups.length)/asItemHeight; 
							
							var visibleGroups = 0;
							var extraElements = 0;
							
							if (xmlGroups.length <= groupsAbove && xmlGroups.length <= groupsBelow) {
								//groups fit in both areas. pick area based on the element number that can be revealed
								if (extraItemsAbove - extraItemsBelow  >=  extraElementsOffset) {
									// pick above
									orientation = "bottom";
									visibleGroups = xmlGroups.length;
									extraElements = extraItemsAbove;
								} else {
									// pick below
									orientation = "top";
									visibleGroups = xmlGroups.length;
									extraElements = extraItemsBelow;
								}
							} else if (xmlGroups.length <= groupsAbove) {
								// groups fit above
								// pick above
								orientation = "bottom";
								visibleGroups = xmlGroups.length;
								extraElements = extraItemsAbove;
							} else if (xmlGroups.length <= groupsBelow) {
								// groups fit below
								// pick below
								orientation = "top";
								visibleGroups = xmlGroups.length;
								extraElements = extraItemsBelow;
							} else {
								// groups are too many,  hide some based on the biggest area and reveal them
								if (groupsAbove - groupsBelow >= extraGroupsOffset) {
									// pick above
									orientation = "bottom";
									visibleGroups = groupsAbove;
									extraElements = extraItemsAbove;
								} else {
									// pick below
									orientation = "top";
									visibleGroups = groupsBelow;
									extraElements = extraItemsBelow;
								}
							}
							
							if (jq.type(jqpopup.data("orientation")) != "undefined" && jq.type(jqpopup.data("orientation")) != "null" 
								&& orientation != jqpopup.data("orientation")) {
								// change popup's orientation
								currentSettings['sender'].redPopups("ajaxGlobalPopup", 
									{
										"sender": currentSettings['sender'], 
										"binding":"on", 
										"type":"obedient", 
										"withFade": true
									}, 
									//popup properties
									{
										sender: currentSettings['sender'],  
										context: cntxt,
										parent: currentSettings['parent'], 
										ppID: currentSettings['ppID'],  
										position: jqpopup.data("orientation")+'|center', 
										positionOffset:0
									}
								);
								jqpopup = cntxt.parent();
							}
							
							// create jq object from returned text that holds the popup
							// clone autosuggest container

							// for each xml group
							jq(contentXML).find("group").each(function(index) {
								// clone group container
								var asGroupClone = asGroup.clone();
								
								// for each item in group
								jq(this).find("item").each(function(idx) {
									// clone item container
									// fill item container with xml item info
									var imgSrc = jq(this).find("image").text();
									var imgTag = "";
									if (imgSrc.length > 0) {
										imgTag = "<img src='"+imgSrc+"' />";
									}
									var asListItemClone = asListItem.clone()
										.children(".listItemImage").append(imgTag).end()
										.children(".listItemTitle").append(jq(this).find("title").contents()).end()
										.children(".listItemSubtitle").append(jq(this).find("subtitle").contents()).end()
										.children(".listItemNotes").append(jq(this).find("notes").contents()).end()
										.children(".listItemTag").append(jq(this).find("tag").contents()).end()
										.attr("data-item-id",  jq(this).attr("id"))
										.attr("filter",  jq(this).attr("filter"));

									// append item container to group container
									asGroupClone.find(".more").before(asListItemClone);
								});
								// fill group info and append group container to autosuggest container
								asGroupClone.attr("data-group-id",  jq(this).attr("id"))
									.children(".groupTitle").append(jq(this).attr("title")).end()
									.find(".more")
										.find(".moreTitle > .query").text(" "+currentSettings['sender'].val()).end()
									.end()
								.removeClass("noDisplay").addClass("noDisplay")
								.appendTo(jqpopup.find(".dtaBody"));
							});
							
							jqpopup.find(".dtaBody > .loading").addClass("noDisplay");
							
							var asGroups = jqpopup.find(".fasppGroup");
							asGroups.each(function() {
								var jqthis = jq(this);
								var asListElements = jqthis.find(".suggestListItem");
								if (asListElements.length > 0) {
									var moreElement = jqthis.find(".more");
									var liElements = asListElements.add(moreElement);
									var gTitle = jqthis.find(".groupTitle");
									
									if (asListElements.length != 0) {
										var hits = 0;
										asListElements.each(function() {
											if (currentSettings['sender'].val().match(new RegExp(RegExp.escaped("^"+jq(this).attr("filter")+"$"), "i"))) {
												currentSettings['sender'].siblings("input:hidden").attr("value",  jq(this).data("item-id"));
												hits++;
											}
										});
										if (hits != 1) {
											currentSettings['sender'].siblings("input:hidden").attr("value",  "");
										}
									}
									
									asListElements.off(".asItem").on({
										
										"mousedown.asItem" : function() {
											currentSettings['sender'].val(jq(this).attr("filter"));
											currentSettings['sender'].siblings("input:hidden").attr("value",  jq(this).data("item-id"));
											jqpopup.removeClass("noDisplay").addClass("noDisplay");
										}, 
										
										"mouseenter.asItem" : function() {
											currentSettings['sender'].data("autosuggestValue",  jq(this).attr("filter"));
										}, 
										
										"mouseleave.asItem" : function() {
											currentSettings['sender'].removeData("autosuggestValue");
										}
										
									});
									
									moreElement.off("moreItem").on({
										"mousedown.moreItem" : function() {
											var morePopupClone = asPopup.clone();
											var groupClone = jqthis.clone(false).find(".more").remove().end();
											var asListElementClones = groupClone.find(".suggestListItem");
											var moreElementClone = groupClone.find(".more");
											var liElementClones = asListElementClones.add(moreElementClone);
											var gTitleClone = groupClone.find(".groupTitle");
											
											if (asListElementClones.length != 0) {
												var hits = 0;
												asListElementClones.each(function() {
													if (currentSettings['sender'].val().match(new RegExp(RegExp.escaped("^"+jq(this).attr("filter")+"$"), "i"))) {
														currentSettings['sender'].siblings("input:hidden").attr("value",  jq(this).data("item-id"));
														hits++;
													}
												});
												if (hits != 1) {
													currentSettings['sender'].siblings("input:hidden").attr("value",  "");
												}
											}
											
											asListElementClones.off(".asPopupItem").on({
												
												"click.asPopupItem" : function() {
													asListElementClones.removeClass("selected");
													jq(this).addClass("selected");
												}
												
											});
											
											jq(document).off(".asPopupNavigation").on({
												"keydown.asPopupNavigation" : function(ev) {
													var asItems = morePopupClone.find(".fasppGroup li");
													var activeItem = asItems.index(asItems.filter(".selected"));
													if (ev.which == 40) {
														// down arrow
														asItems.removeClass("selected");
														asItems.eq((++activeItem)%asItems.length).addClass("selected");
														ev.preventDefault();
													} else if (ev.which == 38) {
														//up arrow
														asItems.removeClass("selected");
														activeItem = (activeItem==-1)?0:activeItem;
														asItems.eq((--activeItem)%asItems.length).addClass("selected");
														ev.preventDefault();
													}
												}
											});
											
											groupClone.find(".suggestionList").removeClass("uiScrollableArea").addClass("uiScrollableArea");
											
											asListElementClones.removeClass("noDisplay");
											morePopupClone.find(".dtaBody").append(groupClone);

											currentSettings['sender'].data("redPopup").trigger("dispose");
											jq.doTimeout("autosuggestPopup", 1, function() {
												currentSettings['sender'].redPopups("ajaxGlobalPopup", 
													{
														"sender": currentSettings['sender'], 
														"binding":"on", 
														"type":"persistent", 
														"withFade": true
													}, 
													//popup properties
													{
														sender: currentSettings['sender'],  
														context: morePopupClone, 
														ppID: currentSettings['ppID'],  
														position: 'center'
													}
												);
												
												morePopupClone.parent().focus().off(".ok").on({
												
													"popup.ok" : function() {
														var selectedItem = jq(this).find(".dtaBody .selected");
														if (selectedItem.length == 0) {
															return;
														} else if (selectedItem.length > 1) {
															selectedItem = selectedItem.first();
														} else {
															currentSettings['sender'].val(selectedItem.attr("filter"));
															currentSettings['sender'].siblings("input:hidden").attr("value",  selectedItem.data("item-id"));
															currentSettings['sender'].data("autosuggestValue",  selectedItem.attr("filter"));
															currentSettings['sender'].trigger("focusin.autosuggest",  "true");
															currentSettings['sender'].trigger("focusout");
														}
													}
													
												});
												
											});
										}
									});
									
									gTitle.off(".groupTitle").on({
										"mouseenter.groupTitle" : function() {
											asListElements.eq(0).trigger("mouseenter.asItem");
										}, 
										
										"mouseleave.groupTitle" : function() {
											asListElements.eq(0).trigger("mouseleave.asItem");
										}, 
										
										"mousedown.groupTitle" : function() {
											asListElements.eq(0).trigger("mousedown.asItem");
										}
									});
								} else {
									jqthis.find(".groupTitle").css("cursor",  "default");
								}
								
							});

							var jqVisibleGroups = asGroups.slice(0, visibleGroups);
							jq(jqVisibleGroups.get().reverse()).each(function(idx,  elm) {
								var flooredExtra = Math.floor(extraElements);
								var visibleGroupExtra = Math.floor(flooredExtra/(jqVisibleGroups.length-idx));
								var totalElements = jq(this).find(".suggestListItem");
								totalElements.slice(visibleGroupExtra+1).removeClass("noDisplay").addClass("noDisplay");
								jq(this).find(".moreSubtitle")
											.find(".topResultsNum").text(" "+(totalElements.not(".noDisplay").length)+" ").end()
											.find(".totalResultsNum").text(" "+totalElements.length+" ");
							});
							
							if (jqVisibleGroups.length > 0) {
								jqVisibleGroups.removeClass("noDisplay");
							} else {
								var hasItems = asGroups.first().find(".suggestListItem").length == 0 ? 0 : 1;
								asGroups.first().find(".moreSubtitle")
											.find(".topResultsNum").text(" "+(hasItems)+" ").end()
											.find(".totalResultsNum").text(" "+asGroups.first().find(".suggestListItem").length+" ").end()
											.end()
											.removeClass("noDisplay")
											.find(".suggestListItem").slice(hasItems).addClass("noDisplay");
										
							}
							
							var liElems = jqpopup.find(".fasppGroup li");						
							liElems.on({
								"mouseenter.asItem" : function() {
									liElems.removeClass("hover");
									jq(this).addClass("hover");
								}
							});
							
							currentSettings['sender'].off(".asNavigation").on({
								"keydown.asNavigation" : function(ev) {
									var asItems = jqpopup.find(".fasppGroup li:not(.noDisplay)");
									var activeItem = asItems.index(asItems.filter(".hover"));
									if (ev.which == 40) {
										// down arrow
										asItems.removeClass("hover");
										if (activeItem < asItems.length) {
											asItems.eq(++activeItem).addClass("hover");
										}
										ev.preventDefault();
									} else if (ev.which == 38) {
										//up arrow
										asItems.removeClass("hover");
										if (activeItem == -1) {
											asItems.eq(-1).addClass("hover");
										} else if (activeItem != 0) {
											asItems.eq(--activeItem).addClass("hover");
										}
										ev.preventDefault();
									} else if (ev.which == 9) {
										//tab
										asItems.eq(activeItem).trigger("mouseenter.asItem").trigger("mousedown.asItem");
									} else if (ev.which == 13 && asItems.eq(activeItem).not(asItems.filter(".more")).length != 0) {
										//if enter is pressed on "li"
										asItems.eq(activeItem).trigger("mouseenter.asItem").trigger("mousedown.asItem");
									} else if (ev.which == 13 && activeItem == asItems.index(asItems.filter(".more"))) {
										//if enter is pressed on ".more"
										ev.preventDefault();
									}
								}, 
								
								"keyup.asNavigation" : function(ev) {
									var asItems = jqpopup.find(".fasppGroup li:not(.noDisplay)");
									var activeItem = asItems.index(asItems.filter(".hover"));

									if (ev.which == 13 && activeItem >= 0) {
										//enter
										asItems.eq(activeItem).trigger("mousedown.moreItem");
										ev.preventDefault();
									}
								}, 
							});
							
							jqpopup.find(".dtaBody > .pivot").text(currentSettings['sender'].val());
							
							currentSettings['sender'].off("autosuggest").on({
								
								"keydown.autosuggest" : function(ev) {
									if (ev.which == 9) {//tab
										jqpopup.removeClass("noDisplay").addClass("noDisplay");
									}
								},
								
								"focusout.autosuggest" : function(ev) {
									jqpopup.trigger("dispose");
								}
			
							});
				
						}
					})
					.error()
					.complete();
				} else {
					jqpopup.trigger("dispose");
					jqpopup = jq();
				}
			});
		}      
		
		createPopup();
		
    }
	
	RegExp.escaped = function(str) {
		return (str+'').replace(/([.?*+^$[\]\\() {}|-])/g,  "\\$1");
	};
});