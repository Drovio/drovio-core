// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

// let the document load
jq(document).one("ready.extra", function() {
	// all autocomplete masters
	var autocompleteMasterElements = "form [data-autocomplete]";
	var autocompleteForm = "form:has([data-autocomplete])";
	
	// autocomplete fields based on the value of a master field, by fetching values from the database
	jq(document).on({
		"change.autocomplete" : function(ev) {
		
			if (ev.target.tagName.toLowerCase() == "select") {
				jq(this).trigger("apply.autocomplete");
			}
		},
		
		"focusout.autocomplete" : function(ev, wasButtonClicked) {
			if (ev.target.tagName.toLowerCase() == "input" 
				&& (jq.type(wasButtonClicked) == "undefined") && (wasButtonClicked !== "true")) {
				jq(this).trigger("apply.autocomplete");
			}
		},
		
		"apply.autocomplete" : function(ev) {
			var jqthis = jq(this);
			var jqForm = jqthis.closest("form");
			/*var validationMode = jqForm.data("validation").mode;*/

			// if value is valid
			if (jq.trim(jqthis).length > 0) {
				// fields to be autofilled in case we have a hit
				var autocompleteSlavesToFill = jq();
				var fillIDs = jqthis.data("autocomplete").fill;
				if (jq.type(fillIDs) != 'undefined' && fillIDs.length > 0) {
					fillIDs = fillIDs.split("|");
					for(var i in fillIDs) {
						autocompleteSlavesToFill = autocompleteSlavesToFill.add("#"+fillIDs[i]);
					}
				}
				// fields to be hidden in case we have a hit
				var autocompleteSlavesToHide = jq();
				var hideIDs = jqthis.data("autocomplete").hide;
				if (jq.type(hideIDs) != 'undefined' && hideIDs.length > 0) {
					hideIDs = hideIDs.split("|");
					for(var i in hideIDs) {
						autocompleteSlavesToHide = autocompleteSlavesToHide.add("#"+hideIDs[i]);
					}
				}
				// fields to be hidden in case we have a hit
				var autocompleteSlavesToPopulate = jq();
				var populateIDs = jqthis.data("autocomplete").populate;
				if (jq.type(populateIDs) != 'undefined' && populateIDs.length > 0) {
					populateIDs = populateIDs.split("|");
					for(var i in populateIDs) {
						autocompleteSlavesToPopulate = autocompleteSlavesToPopulate.add("#"+populateIDs[i]);
					}
					autocompleteSlavesToPopulate.each(function() {
						jq(this).addClass("loading").empty();
						var siblingIcon = jq(this).siblings("#"+jq(this).attr("id")+"_icon.inputInfoIcon");
						if (siblingIcon.length > 0) {
							siblingIcon.addClass("loader");
						} else {
							jq(this).after("<div id='"+jq(this).attr("id")+"_icon' class='inputInfoIcon loader'></div>");
						}
					});
				}
				
				jqthis.data("autocomplete").activated = "false";
			
				// update field class
				jqthis.removeClass("loading").addClass("loading");
				var siblingIcon = jqthis.siblings("#"+jqthis.attr("id")+"_icon.inputInfoIcon");	
				if (siblingIcon.length > 0) {
					siblingIcon.removeClass("loader").addClass("loader");
				} else {
					siblingIcon = jq("<div id='"+jq(this).attr("id")+"_icon' class='inputInfoIcon loader'></div>");
					jqthis.after(siblingIcon);
				}
				
				
				successAutoComplete = function(xml) {
					var jqthis = jq(this);
					if (jqthis.data("autocomplete").type == "strict") {
						autocompleteSlavesToFill.add(autocompleteSlavesToPopulate).on({
							// if the value of the autofilled fields is changed {change | keydown} then 
							// background of fields and value of ssn are reset.
							"change.autoreset" : function() {
								if (jqthis.data("autocomplete").activated == "true") {
									autocompleteSlavesToFill.css("background", "");
									autocompleteSlavesToHide.each(function() {
										jq(this).attr("type", "").prop("disabled", false).parent().removeClass("noDisplay");
									});
									jqthis.val("").removeClass("invalid inputEmptyError").addClass("invalid inputEmptyError");
									/*if (validationMode=="verbose") {
										jqthis.siblings("#"+jqthis.attr("id")+"_icon.inputInfoIcon")
												.removeClass()
												.addClass("inputInfoIcon invalid");
									}*/
									jqthis.data("autocomplete").activated = "false";
								}
							},
							"keyup.autoreset" : function(ev) {
								if ((57 >= ev.which && ev.which >= 48) //number
									|| (105 >= ev.which && ev.which >= 96) //numpad number
									|| (90 >= ev.which && ev.which >= 65) //letter
									|| (ev.which == 8) || (ev.which == 46) //backspace | delete  
									|| (ev.which == 13) || (ev.which == 32) //enter
									|| (111 >= ev.which && ev.which >= 106) //+-*./
									|| (192 >= ev.which && ev.which >= 186) //;=,-.`~
									|| (222 >= ev.which && ev.which >= 219) //'\][
									|| (ev.which == 226)) { 
									jq(this).trigger("change.autoreset");
								}
							}
						});
					}
					
					// create jq object from returned text
					var jqdata = jq(xml);

					if (jqdata.find("master").children().length > 0) {
						// focus on next button
						//jq("#btn_next").parent().focus();
						
						// match
						// get response values
						
						autocompleteSlavesToFill.each(function() {
							var newValue = jqdata.find("master > "+jq(this).attr("id")).text();
							jq(this).val(newValue);
						});
						autocompleteSlavesToHide.each(function() {
							jq(this).attr("type", "hidden")
										.prop("disabled", true)
										.parent().addClass("noDisplay");
						});
						autocompleteSlavesToPopulate.each(function() {
							var jqSelect = jq(this);
							var newValues = jqdata.find("master > "+jqSelect.attr("id")).find("item");
							
							jqSelect.empty();
							newValues.each(function() {   
								jqSelect.append(
								 	jq("<option></option>")
								 		.attr("id", jq(this).attr("id"))
								 		.attr("value", jq(this).attr("value"))
								 		.text(jq(this).text())); 
							});
							jqSelect.removeClass("loading");
							siblingIcon.removeClass("info warning valid invalid");
							jqSelect.siblings("#"+jqSelect.attr("id")+"_icon.inputInfoIcon").removeClass().addClass("inputInfoIcon");
						});	

						var fieldsToTick = autocompleteSlavesToFill.filter(".required");
						fieldsToTick = fieldsToTick.add(autocompleteSlavesToFill.filter("[data-validators]"));

						autocompleteSlavesToFill.add(autocompleteSlavesToPopulate).each(function() {
							jq(this).removeClass("invalid");
						});
						// similar to .off("info.annotation", "this"); if existed!
						fieldsToTick.on("info.annotationPopup", function(evnt) {
							evnt.stopPropagation();
						});
						fieldsToTick.each(function() {
							jq(this).siblings("#"+jq(this).attr("id")+"_icon.inputInfoIcon").removeClass().addClass("inputInfoIcon valid");
						});
						
						//change filled elements background
						autocompleteSlavesToFill.css("background", "LemonChiffon");
					
						jqthis.data("autocomplete").activated = "true";
						
						// dispose notif ication popups {if any}
						jq.doTimeout("removeFNPPS", 40, function() {
							jq(".fnppContainer", jqForm).remove()
						});
						
					} else {
						// no match found!						
						jqthis.data("autocomplete").activated = "false";
						autocompleteSlavesToFill.css("background", "");
						autocompleteSlavesToHide.each(function() {
							jq(this).attr("type", "")
										.prop("disabled", false)
										.parent().removeClass("noDisplay");
						});				
					}
					// valid info icon
					jqthis.removeClass("loading");
					siblingIcon.removeClass("loader");
				}
				var data = "formID="+jqForm.attr("id")+"&"+
					"value="+jqthis.val()+"&"+
					"fill="+jqthis.data("autocomplete").fill+"&"+
					"hide="+jqthis.data("autocomplete").hide+"&"+
					"populate="+jqthis.data("autocomplete").populate;
				ascop.asyncRequest(
					jqthis.data("autocomplete").path, 
					"GET",
					data,
					"xml",
					jqthis,
					successAutoComplete,
					null,
					true,
					true
				);
			}
		}
	}, autocompleteMasterElements);
	
	// On form reset, reset autocomplete slaves that were acquired by ajax
	jq(document).on("reset", autocompleteForm, function(ev){
		var jqthis = jq(this);
		// This is needed to execute after the reset event
		setTimeout(function() {
			jq("[data-autocomplete]", jqthis).each(function(){
				// Check how this works for inputs
				jq(this).trigger("apply.autocomplete");
			});
		}, 1);
	});
});