/* 
 * Redback JS Document
 *
 * Title : RedBack JS Library - Form Validator
 * Description : Checks for potential input errors on forms
 * Author : RedBack Developing Team 
 * Version : --
 * DateCreated : 06/02/2012
 * DateRevised : --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();

// let the document load
jq(document).one("ready123.extra", function() { 

	// host
	var _host = "";

//---- initialize constants ----------------------------------------------------------------------------
	// regular expressions used in the script
	var REGULAR_EXPRESSIONS = {
		// username proper size
		USERNAME_REGEXP_PROPERSIZE : /^.{6,20}$/g, 
		// username proper charset
		USERNAME_REGEXP_PROPERCHARSET : /^[a-z][a-z0-9_\.]*$/g, 
		// username regexp
		USERNAME_REGEXP : /^[a-z][a-z0-9_\.]{5,19}$/gi, 
		// password regexp
		PASSWORD_REGEXP : /^.*(?=.*\d)((?=.*[a-z])|(?=.*[A-Z]))((?=.*\W)|(?=.*\_)).*$/g, 
		// only digits regexp
		DIGITS_ONLY_REGEXP : /^\d+$/g, 
		// no digits regexp
		NO_DIGITS_REGEXP : /^[^0-9]$/g, 
		// email regexp
		EMAIL_REGEXP : /^[a-z0-9._%+-]+@(?:[a-z0-9-]+\.)+(?:[a-z]{2}|com|xxx|cat|org|tel|net|int|pro|edu|gov|mil|biz|coop|info|mobi|name|aero|arpa|asia|jobs|travel|museum)$/gi, 
		// alphanumeric regexp
		ALPHANUMERIC_REGEXP : /^[\d|(a-zA-Z)]+$/gi
	};

//---- initialize variables ----------------------------------------------------------------------------
	var validationForms = "form[data-validation]";
	// all elements except of type 'hidden' or 'checkbox'
	var allNameElements = validationForms+" input:not([type='hidden'], [type='checkbox'], [type='reset'], [type='submit']), form select:not([type='hidden']), form textarea:not([type='hidden'])";
	// all |required| elements except of type 'hidden' or 'checkbox'
	var requiredNameElements = allNameElements.replace(/:/g, ".required:");
	// all elements with |max character| validators
	var maxCharacterInputElements = validationForms+" [data-validators*='maxLen']";
	// all elements with |numeric| validators
	var numericInputElements = validationForms+" [data-validators*='numeric']";
	// all |required| elements with select type
	var requiredSelectElements = validationForms+" select.required:not([type='hidden'], [type='checkbox'])";
	// all |required| elements with input type
	var requiredInputElements = requiredSelectElements.replace("select", "input");
	// fields with constrains
	var limitedInputElements = validationForms+" [data-validators], "+requiredNameElements;
	// all elements with |size| validators
	var sizeInputElements = validationForms+" [data-validators*='size']";
	// all elements with |min| character validators
	var minCharacterInputElements = validationForms+" [data-validators*='minLen']";
	// all elements with |regexp| validators
	var regexpInputElements = validationForms+" [data-validators*='regexp']";
	// all elements with |validate| validators
	var validateInputElements = validationForms+" [data-validators*='validate']";
	// all elements with |pair| validators
	var pairInputElements = validationForms+" [data-validators*='pair']";
	// all elements with |exists| validators
	var existsInputElements = validationForms+" [data-validators*='exists']";
	// |password| field [not validation]
	var passwordInputElement = validationForms+" input[type='password']:not([data-validators*='validate'])";
	// policy aggreement checkbox
	var policyAgreementCheck = validationForms+" input[type='checkbox'][name='eulaAgreement']";
	// |submit| button
	var submitButtonElement = validationForms+" button[type='submit']:not(:disabled)";
	// elements that need to update their values on initialization
	var updateElementsOnInit = validationForms+" [data-on-init='flush']";
	// element that gets focused first
	var autofocusedElement = validationForms+" [autofocus]";
	// elements with data-fann-info
	var fannInfo = validationForms+" [data-fann-info]";
	
	// keeps the previously focused input
	var lastFocusedInput = jq(autofocusedElement).last();
	// forms
	
	// this is used to avoid a bug on chrome on Windows {double focus|blur trigger on window minimize}!
	var canITrigger = true;
	
//---- html update -------------------------------------------------------------------------------------
	
	jq(document).on("content.modified", function(){
		// icon holders : add divs next to the fields. Access them through inputInfoIcons
		jq(allNameElements).each(function() {
			var jqthis = jq(this);
			if (jqthis.siblings("#"+jqthis.attr("id")+"_icon.inputInfoIcon").length == 0) {
				jqthis.after("<div id='"+jqthis.attr("id")+"_icon' class='inputInfoIcon'></div>");
			}
		});
	
		// password meter : add password meter for password field. Access through passwordMeter
		var passMeterHolder = jq(passwordInputElement).siblings("label").first();
		if(passMeterHolder.children(".passMeter").length == 0){
			passMeterHolder.append("<div class='passMeter'><div class='passMeterBar'></div></div>");
		}
		
		jq(fannInfo).each(function(){
			var fi = jq(this).data("fannInfo");
			jq(this).removeAttr("data-fann-info").data(fi);
		})
	});
	jq(document).trigger("content.modified");
	
//---- listeners ---------------------------------------------------------------------------------------
	// prevent context menu from password fields
	jq(document).on("contextmenu", "form input[type='password']", function(cmenuEvent) {
        cmenuEvent.preventDefault();
    });

	// ALLFIELDS FOCOUSIN
	jq(document).on({
	
		// focusin : scroll page | update last focused icon | create new popup anchor | remove old popup
		"focusin.allName" : function() {
			// this 'if ' is needed cause of a chrome bug that causes double focus | blur events 
			// when window loses focus and then regains it!!! only needed on windows chrome, 
			// but works as is anyway. If bug is fixed, those 'if 's can be removed
			// {canITrigger} throughout the whole script.
			// no further comments according this issue for the rest of the script.
			var jqthis = jq(this);
			var jqthisForm = jqthis.closest("form");
			if (canITrigger) {
				// adjust scroll : 38 is the height of possible bars on top of page
				if (jqthis.offset().top < jq(window).scrollTop()+38) {
					jq(window).scrollTop(jqthis.offset().top - 38);
				}
				if (jqthisForm.data("validation").mode == "verbose") {
					// change lastFocusedInput icons
					iconClassUpdater(lastFocusedInput);
					// change popup id to separate from the one that is going to be created
					jq(".fnppContainer", jqthisForm).attr("id", "fnpp_old").addClass("fnpp_old");
					if (jqthis.attr("type") != "reset" && jqthis.attr("type") != "submit" && jqthis.attr("type") != "button" && jqthis.attr("type") != "checkbox") {
						// create new popup anchor in the row {will be populated when a new popup is created}
						jqthis.parent().append("<div id='"+jqthis.attr("id")+"_fnpp' class='fnppContainer' ></div>");	
					}
					if (jqthis.siblings(".fnppContainer.fnpp_old").length != 0) {
						// if  old popup exists as a sibling, emphasize new by removing old
						emphasizeFFNPPopup(jqthisForm);
					} else {
						//  else remove old
						removeFFNPOldPopup(jqthisForm);
					}
				}
			}
		}
		// this applies to all name elements plus buttons and checkboxes
	}, allNameElements.replace("form input:not([type='hidden'], [type='checkbox'], [type='reset'], [type='submit'])", "form input:not([type='hidden'])")+", form button:not([type='hidden'])");

	// info message for all name elements
	jq(document).on({
		"info.annotationPopup" : function(ev) {
			var jqthis = jq(this);
			if (jqthis.closest("form").data("validation").mode == "verbose") {
				// info params to create info messages
				var infoIDs = new Array();
				
				// change icon to infoInputInfoIcon
				jqthis.siblings("div#"+jqthis.attr("id")+"_icon.inputInfoIcon").removeClass().addClass("inputInfoIcon info");
				// push annotation of the element as header
				if (jq.type(jqthis.data("annotation")) != "undefined") {
					infoIDs.push("ann."+jqthis.data("annotation").code+" header");
				} else if (jq.type(jqthis.data("fannInfo")) != "undefined") {
					infoIDs.push(jqthis.data("fannInfo"));
				} else {
					return;
				}
				if (jqthis.hasClass("required")) {
					// push "required" annotation where needed
					infoIDs.push("ann.required list");
				}
				var validators = jqthis.data("validators");
				for(var v in validators) {
					switch(v)
					{
					case "maxLen" : 
					case "minLen" : 
					case "size" : 
						// push "maxLen" | "minLen" | "size" annotation where needed
						// along with the constrain of each validator
						// separated with a space 
						infoIDs.push("ann."+v+" "+validators[v]+" list");
						break;
					case "numeric" : 
						// push proper numeric annotation where needed
						if (validators[v]) {
							infoIDs.push("ann.DIGITS_ONLY_REGEXP list");
						} else {
							infoIDs.push("ann.NO_DIGITS_REGEXP list");
						}
						break;
					case "regexp" : 
						// push proper regexp annotation where needed
						if (validators[v] == "USERNAME_REGEXP") {
							infoIDs.push("ann."+validators[v]+"_PROPERSIZE list");
						}
						infoIDs.push("ann."+validators[v]+" list");
						infoIDs.push("ann."+validators[v]+"_HINT footer");
						break;
					//case "pair" : 
					case "validate" : 
						// push "validate" annotation where needed
						// along with the text of sibling label
						// separated with a space from the field annotation
						infoIDs.push("ann."+v+" list");
						//infoIDs.splice(0, 1, infoIDs[0]+" "+jq.trim(jq("#"+validators[v]).siblings(".uiFormLabel").text().replace(/(\*|\ :)/g, "")));
						break;
					default : 
					  // no such validator!!!
					}
				}

				// create the popup
				updateLabelNotificationViaAJAX({
					dataType : "fann", 
					dataID : infoIDs, 
					currentJQElement : jqthis, 
					locale : jqthis.closest("form").attr("locale")
				});	
				// if  element is a slave element
				if (jq(limitedInputElements).filter(jqthis).length>0) {
					// stop firing this event
					// similar to .off("info.annotation", "this"); if  existed!
					jqthis.on("info.annotationPopup", function(evnt) {
						evnt.stopPropagation();
					});
				}
			}
		}, 
		
		// focusin : populate info popup. Fields with validators run this once.
		"focusin.annotationPopup" : function(ev) {
			jq(ev.target).trigger("info.annotationPopup");
		}
		
	}, allNameElements);
	
	// VALIDATE FIELDS FOCUSOUT/IN
	jq(document).on({
		
		// focusout : check if  field's validate criterion is preserved, when focus is lost.
		// If not, update field's class accordingly.
		"focusout.validate" : function() {
			var jqthis = jq(this);
			// if  input element has a warning tag, update its class
			if (jqthis.hasClass("invalidInputWarning")) {
				elementClassUpdater(true, jqthis, "invalidInputWarning");
			} else if (!jqthis.hasClass("invalid")) {
			// if  no other error or warning has been found for the field, proceed with the |regexp| checking
				// update input class
				elementClassUpdater(jqthis.val() != jq("#"+jqthis.data("validators").validate).val(), jqthis, "inputValidateError");
			}
		}, 
		
		// focusin : reveal message for validate field being wrong
		"focusin.validate" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// validator's pair field
				var pair = jq("#"+jqthis.data("validators").validate);
				
				if (jqthis.hasClass("invalid inputValidateError")) {
					// filter only invalid validate fields
					jqthis.removeClass("invalid inputValidateError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						snifInputErrors(jqthis);
					}
				} else if (jqthis.hasClass("invalid invalidInputWarning")) {
					// filter only invalid warning fields
					jqthis.removeClass("invalid");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						updateLabelNotificationViaAJAX({
							dataType : "warning", 
							dataID : "wrn.1", 
							currentJQElement : jqthis, 
							locale : jqthis.closest("form").attr("locale")
						});
					}
				}
			}
		}, 
	
		// VALIDATE FIELDS KEYUP
		// keyup : check if  pair field has an acceptable value.
		// If not, place a warning icon for the field and prompt user.
		"keyup.validate" : function(ev) {
			// if  key changes field's value
			if ((57 >= ev.which && ev.which >= 48) //number
							 ||  (105 >= ev.which && ev.which >= 96) //numpad number
							 ||  (90 >= ev.which && ev.which >= 65) //letter
							 ||  (ev.which == 8) || (ev.which == 46) //backspace | delete  
							 ||  (ev.which == 13) || (ev.which == 32) //enter
							 ||  (111 >= ev.which && ev.which >= 106) //+-*./
							 ||  (192 >= ev.which && ev.which >= 186) //;=, -.`~
							 ||  (222 >= ev.which && ev.which >= 219) //'\][
							 ||  (ev.which == 226)) { 
				// validator's pair field
				var jqthis=jq(this);
				var pair = jq("#"+jqthis.data("validators").validate);
				if (pair.hasClass("invalid") || pair.val() == "") {
					// pair field has unacceptable value
					// erase this field's value
					jqthis.val("");
					var preventPP = jqthis.hasClass("invalidInputWarning");
					// change class | icon
					elementClassUpdater(true, jqthis, "invalidInputWarning");
					iconClassUpdater(jqthis);
					// prompt user only if  there is no other info popup in the row
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						if (!preventPP) {
							// rename popup
							jqthis.siblings(".fnppContainer").attr("id", "fnpp_old").addClass("fnpp_old");
							// create new popup anchor {will be populated when new popup is created}
							jqthis.parent().append("<div id='"+jqthis.attr("id")+"_fnpp'></div>");
							// create new popup
							updateLabelNotificationViaAJAX({
								dataType : "warning", 
								dataID : "wrn.1", 
								currentJQElement : jqthis, 
								locale : jqthis.closest("form").attr("locale")
							});
							if (jqthis.siblings(".fnppContainer.fnpp_old").length != 0) {
								// if  old popup exists as a sibling, emphasize new by removing old
								emphasizeFFNPPopup(jqthis.closest("form"));
							} else {
								//  else remove old
								removeFFNPOldPopup(jqthis.closest("form"));
							}
						}
					}
				}
			}
		}
		
	}, validateInputElements);
		
	// VALIDATESOURCE FIELDS KEYDOWN
	jq(document).on({
		
		// keydown : erase validate field's value and warning class | icon {if  any}.
		"keydown.pair" : function(ev) {
			var jqthis = jq(this);
			// if  key changes field's value
			if ((57 >= ev.which && ev.which >= 48) //number
							 ||  (105 >= ev.which && ev.which >= 96) //numpad number
							 ||  (90 >= ev.which && ev.which >= 65) //letter
							 ||  (ev.which == 8) || (ev.which == 46) //backspace | delete  
							 ||  (ev.which == 13) || (ev.which == 32) //enter
							 ||  (111 >= ev.which && ev.which >= 106) //+-*./
							 ||  (192 >= ev.which && ev.which >= 186) //;=, -.`~
							 ||  (222 >= ev.which && ev.which >= 219) //'\][
							 ||  (ev.which == 226)) { 
				// pair's validate field
				var validate = jq("#"+jqthis.data("validators").pair);
				// erase validate field's value and remove its warning class | icon
				validate.val("");
				elementClassUpdater(false, validate, "invalidInputWarning");
				if (jqthis.closest("form").data("validation").mode == "verbose") {
					validate.siblings("div#"+validate.attr("id")+"_icon.inputInfoIcon").removeClass().addClass("inputInfoIcon");
				}
			}
		}
		
	}, pairInputElements);
	
	// REQUIRED FIELDS FOCOUSIN/OUT
	jq(document).on({
		
		// focusout : check if  field is empty when focus is lost. If empty, update field's class accordingly
		"focusout.required" : function() {
			var jqthis = jq(this);
			// if  no other error has been found for the field, proceed with the |required| checking
			if (!jqthis.hasClass("invalid")) {
				// update input class
				elementClassUpdater(jq.trim(jqthis.val()).length == 0, jqthis, "inputEmptyError");
			}
		}, 
		
		// focusin : reveal message for required field being empty
		"focusin.required" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// filter only invalid inputEmptyError fields
				if (jqthis.hasClass("invalid inputEmptyError")) {
					jqthis.filter(".invalid.inputEmptyError").removeClass("invalid inputEmptyError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						snifInputErrors(jqthis);
					}
				}
			}
		}
		
	}, requiredNameElements);
		
	// NUMERIC FIELDS FOCOUSIN/OUT
	jq(document).on({
		
		// focusout : check if  field has only digits or does not have digits at all, when focus is lost.
		// If not, update field's class accordingly.
		"focusout.numeric" : function() {
			var jqthis = jq(this);
			// only digits when "true" | no digits when "false"
			var validRegExp = (jqthis.data("validators").numeric) ? REGULAR_EXPRESSIONS.DIGITS_ONLY_REGEXP : REGULAR_EXPRESSIONS.NO_DIGITS_REGEXP;
			// if  no other error has been found for the field, proceed with the |numeric| checking
			if (!jqthis.hasClass("invalid")) {
				// update input and icon class
				elementClassUpdater(!jqthis.val().match(validRegExp), jqthis, "inputNumericError");
			}
		}, 
		
		// focusin : reveal message for numeric field being wrong
		"focusin.numeric" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// filter only invalid inputNumericError fields
				if (jqthis.hasClass("invalid inputNumericError")) {
					jqthis.filter(".invalid.inputNumericError").removeClass("invalid inputNumericError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						// only digits when "true" | no digits when "false"
						if (jqthis.data("validators").numeric) {
							snifInputErrors(jqthis);
						} else {
							snifInputErrors(jqthis);
						}
					}
				}
			}
		}
		
	}, numericInputElements);
	
	// SIZE FIELDS FOCOUSIN/OUT
	jq(document).on({
		
		// focusout : check if  field's characters size dif fers from preferred, when focus is lost.
		// If so, update field's class accordingly.
		"focusout.size" : function() {
			var jqthis = jq(this);
			// if  no other error has been found for the field, proceed with the |size| checking
			if (!jqthis.hasClass("invalid")) {
				// update input and icon class
				elementClassUpdater(jq.trim(jqthis.val()).length != jqthis.data("validators").size, jqthis, "inputSizeError");
			}
		}, 
		
		// focusin : reveal message for size field being wrong {if  such a thing occurs}
		"focusin.size" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// filter only invalid inputSizeError fields
				if (jqthis.hasClass("invalid inputSizeError")) {
					jqthis.filter(".invalid.inputSizeError").removeClass("invalid inputSizeError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						snifInputErrors(jqthis);
					}
				}	
			}
		}
		
	}, sizeInputElements);
	
	// MAX CHARACTERS FIELDS FOCOUSIN/OUT
	jq(document).on({
		
		// focusout : check if  field's max character criterion is preserved, when focus is lost. 
		// If not, update field's class accordingly.
		"focusout.max" : function() {
			var jqthis = jq(this);
			// if  no other error has been found for the field, proceed with the |max| checking
			if (!jqthis.hasClass("invalid")) {
				// update input and icon class
				elementClassUpdater(jq.trim(jqthis.val()).length>jqthis.data("validators").maxLen, jqthis, "inputMaxLenError");
			}
		}, 
		
		// focusin : reveal message for max field being wrong {if  such a thing occurs}
		"focusin.max" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// filter only invalid inputMaxLenError fields
				if (jqthis.hasClass("invalid inputMaxLenError")) {
					jqthis.filter(".invalid.inputMaxLenError").removeClass("invalid inputMaxLenError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						snifInputErrors(jqthis);
					}
				}
			}
		}

	}, maxCharacterInputElements);

	// MIN CHARACTERS FIELDS FOCOUSIN/OUT
	jq(document).on({
		
		// focusout : check if  field's min character criterion is preserved, when focus is lost.
		// If not, update field's class accordingly.
		"focusout.min" : function() {
			var jqthis = jq(this);
			// if  no other error has been found for the field, proceed with the |min| checking
			if (!jqthis.hasClass("invalid")) {
				// update input and icon class
				elementClassUpdater(jq.trim(jqthis.val()).length<jqthis.data("validators").minLen, jqthis, "inputMinLenError");
			}
		}, 
		
		// focusin : reveal message for min field being wrong {if  such a thing occurs}
		"focusin.min" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// filter only invalid inputMinLenError fields
				if (jqthis.hasClass("invalid inputMinLenError")) {
					jqthis.filter(".invalid.inputMinLenError").removeClass("invalid inputMinLenError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						snifInputErrors(jqthis);
					}
				}	
			}
		}
		
	}, minCharacterInputElements);
	
	// REGEXP FIELDS FOCOUSIN/OUT
	jq(document).on({
		
		// focusout : check if  field's regexp criterion is preserved, when focus is lost.
		// If not, update field's class accordingly.
		"focusout.regexp" : function() {
			var jqthis = jq(this);
			// if  no other error has been found for the field, proceed with the |regexp| checking
			if (!jqthis.hasClass("invalid")) {
				// update input and icon class
				elementClassUpdater(!jqthis.val().match(REGULAR_EXPRESSIONS[jqthis.data("validators").regexp]), jqthis, "inputRegexpError");
			}
		}, 
		
		// focusin : reveal message for regexp field being wrong {if  such a thing occurs}
		"focusin.regexp" : function() {
			var jqthis = jq(this);
			if (canITrigger) {
				// filter only invalid inputRegexpError fields
				if (jqthis.hasClass("invalid inputRegexpError")) {
					jqthis.filter(".invalid.inputRegexpError").removeClass("invalid inputRegexpError");
					if (jqthis.closest("form").data("validation").mode == "verbose") {
						snifInputErrors(jqthis);	
					}
				}
			}
		}
		
	}, regexpInputElements);
	
	// PASSWORD STRENGTH
	jq(document).on({
		
		// keyup : calculate password's strength and display it.
		"keyup.password" : function() {
			var jqthis = jq(this);
			var passwordMeterBar = jqthis.siblings("label").find(".passMeterBar");
			// get password
			var password = jqthis.val();
			// get username
			var username = jq("#" + jqthis.data("validators").unpair).val();
			if (password == "") {
				// password field is empty
				// change background to white and length to zero
				passwordMeterBar.css("background", "").css("width", "0");
			} else if (username.match(RegExp.escaped(password))) {
				// password contains username
				// change background to red {length : 1px}
				passwordMeterBar.css("background", "rgba(255, 0, 0, 0.4)").css("width", "1px");
			} else if (!password.match(REGULAR_EXPRESSIONS.PASSWORD_REGEXP)) {
				// password doesn't meet minimum criteria
				// change background to red {length : 1px} <- in this case the pass is not valid
				// further info in regexp listener
				passwordMeterBar.css("background", "rgba(255, 0, 0, 0.4)").css("width", "1px");
			} else if (password.length<6) {
				// password length is smaller than preferred
				// change background to red {length : 1px} <- in this case the pass is not valid
				// further info in minLen listener
				passwordMeterBar.css("background", "rgba(255, 0, 0, 0.4)").css("width", "1px");
			} else {
				// evaluate password strength
				passwordEvaluator(password, passwordMeterBar);
			}
		}
		
	}, passwordInputElement);
	
	// annotation checkout for slaves
	jq(document).on("change.annotation", limitedInputElements, function(ev) {
		//console.log(jq(ev.target).siblings());return;
		var jqthis = jq(ev.target);
		if (jqthis.closest("form").data("validation").mode == "verbose") {
			if (jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children().hasClass("info")) {
				// update info list
				fnppInfoRequirementsChecker(jqthis, jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children(".info"));	
			} else if (jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children().hasClass("error")) {
				// create | update error list
				if (jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children(".error").find("ul").length == 0) {
					snifInputErrors(jqthis);
				}
				fnppErrorRequirementsChecker(jqthis, jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children(".error"));	
			}/* else if (!jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children().hasClass("warning")) {
				// create error list
				snifInputErrors(jqthis);
			}*/
		}
	});
	
	// annotation checkout for slaves
	jq(document).on("keyup.annotation", limitedInputElements, function(ev) {
		var jqthis = jq(ev.target)
		// if  key changes field's value
		if ((57 >= ev.which && ev.which >= 48) //number
				 ||  (105 >= ev.which && ev.which >= 96) //numpad number
				 ||  (90 >= ev.which && ev.which >= 65) //letter
				 ||  (ev.which == 8) || (ev.which == 46) //backspace | delete  
				 ||  (111 >= ev.which && ev.which >= 106) //+-*./
				 ||  (192 >= ev.which && ev.which >= 186) //;=, -.`~
				 ||  (222 >= ev.which && ev.which >= 219) //'\][
				 ||  (ev.which == 226)
				 ||  (ev.which == 32) //spacebar
				 ||  (jq.type(ev.which)  === "undefined")) { // this is used for selects
			if (jqthis.closest("form").data("validation").mode == "verbose") {
				jqthis.trigger("change.annotation");
			}
		}
	});
	
	// annotation checkout for selects
	jq(document).on("change.annotation", requiredSelectElements, function() {
		var jqthis = jq(this);
		if (jqthis.closest("form").data("validation").mode == "verbose") {
			if (jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children().hasClass("info")) {
				// update info list
				fnppInfoRequirementsChecker(jqthis, jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children(".info"));	
			} else if (jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children().hasClass("error")) {
				// update error list
				fnppErrorRequirementsChecker(jqthis, jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer").children(".error"));	
			}
		}
	});
	
	// checks if  the value exists in the database and acts according to the data
	jq(document).on({

		// focusout : check field existence
		"focusout.exists" : function(ev) {
			var jqthis = jq(this);
			// if  value is valid 
			if (!jqthis.hasClass("invalid") && jq.trim(jqthis.val()) != "") {
				// value that may have been autosuggested
				var autosuggestValue = jqthis.data("autosuggestValue");
				var ajaxval = (jq.type(autosuggestValue) == "undefined") ? jqthis.val() : autosuggestValue;
				
				// loading class
				jqthis.removeClass("loading").addClass("loading");
				var divSibling;
				if (jqthis.closest("form").data("validation").mode == "verbose") {
					divSibling = jqthis.siblings("div#"+jqthis.attr("id")+"_icon.inputInfoIcon");
					// loading class
					divSibling.removeClass().addClass("inputInfoIcon loader");		
				}
				// ajax query
				jq.ajax({
					// ask "url" to check if  the "data" already exists
					url : _host+jqthis.data("ajax-expath"), 
					data : { 
						// value to check
						"val" : ajaxval
					}, 
					dataType : "xml", 
					// on success change icons and classes
					success : function(xml) {
						// create jq object from ajax xml response
						var jqdata = jq(xml);
						var condition = jqthis.data("validators").exists;
						var fnppSibling = jqthis.siblings("#"+jqthis.attr("id")+"_fnpp.fnppContainer");
						// unique falls back to true
						condition = (condition == "unique") ? "true" : condition;
						// update class and icon
						if (!(jqdata.find("exists").text() == condition)) {
							// invalid
							if (jqthis.closest("form").data("validation").mode == "verbose") {
								// update icon
								divSibling.removeClass().addClass("inputInfoIcon invalid");
							}
							// update element
							jqthis.removeClass("invalid inputExistenceError uniqueInputWarning loading").addClass("invalid inputExistenceError");
							if (jqthis.closest("form").data("validation").mode == "verbose") {
								// dispose notification popup only if  it is an info popup
								if (fnppSibling.find(".info").length>0) {
									fnppSibling.animate({opacity : 0, top : "+=10"}, "middle", function() {
										jq(this).remove();
									});
								}
							}
						} else if (jqthis.data("validators").exists == "unique" 
								 &&  jq.type(autosuggestValue) == "undefined"
								 &&  jqdata.find("count").text() != "1") {
							// warning, value is correct but not unique
							if (jqthis.closest("form").data("validation").mode == "verbose") {
								// update icon
								divSibling.removeClass().addClass("inputInfoIcon warning");
							}
							// update element
							jqthis.removeClass("invalid inputExistenceError uniqueInputWarning loading").addClass("invalid uniqueInputWarning");
							if (jqthis.closest("form").data("validation").mode == "verbose") {
								// dispose notification popup only if  it is an info popup
								if (fnppSibling.find(".info").length>0) {
									fnppSibling.animate({opacity : 0, top : "+=10"}, "middle", function() {
										jq(this).remove();
									});
								}
							}
						} else {
							// valid
							if (jqthis.closest("form").data("validation").mode == "verbose") {
								// update icon
								divSibling.removeClass().addClass("inputInfoIcon valid");
							}
							// update element
							jqthis.removeClass("invalid inputExistenceError uniqueInputWarning loading");
							if (jqthis.closest("form").data("validation").mode == "verbose") {
								// dispose notification popup
								fnppSibling.animate({opacity : 0, top : "+=10"}, "middle", function() {
									jq(this).remove();
								});
							}
						}
					}
				})
				.error(function(data, ts) { 
					//runs if  match is found or any error occurs
					//alert("match!");
				});
			}
		}, 
		
		// focusin : update labels
		"focusin.exists" : function() {
			var jqthis = jq(this);
			jqthis.removeClass("invalid");
			if (jqthis.hasClass("inputExistenceError")) {
				if (jqthis.closest("form").data("validation").mode == "verbose") {
					// filter only invalid inputExistenceError fields
					// create error list
					snifInputErrors(jqthis);
				}
			} else if (jqthis.hasClass("uniqueInputWarning")) {
				// create warning
				if (jqthis.closest("form").data("validation").mode == "verbose") {
					updateLabelNotificationViaAJAX({
						dataType : "warning", 
						dataID : "wrn.5", 
						currentJQElement : jqthis, 
						locale : jqthis.closest("form").attr("locale")
					});
				}
			}
		}
	}, existsInputElements);
	
	// SUBMIT BUTTON CLICK
	//submitButtonElement.on({
	jq(document).on("click.validate", submitButtonElement, function(ev) {	
		//"click.validate" : function(ev) {
		// check if  required fields have acceptable values
			/*jq(this).trigger("action", true);
			return;*/
			// required input elements to check before submit
			var jqthis = jq(this);
			var jqForm = jqthis.closest("form");
			var policyAgreementCheckbox = jqForm.find(policyAgreementCheck);
			jqthis.prop("disabled", true).removeClass("disabled").addClass("disabled").children(".unlocked")
															.removeClass("unlocked").addClass("locked");
			policyAgreementCheckbox.prop("disabled", true);
			
			var checkTheseElementsBeforeSubmit = jq();
			var checkableElements = jq("[name]", jqForm).not("[type='hidden'], :checkbox");
			checkableElements.filter(".required").each(function() {
				if (jq.trim(jq(this).val()) == "") {
					checkTheseElementsBeforeSubmit = checkTheseElementsBeforeSubmit.add(jq(this));
				}
			});
			
			// all side menu links
			var allSideNavMenuLinks = jq("a", "div.uiSideNavMenu");

			// prevent info annotation from popping up
			// similar to .off("info.annotation", "this"); if  existed!
			jq(allNameElements).on("info.annotationPopup", function(evnt) {
				evnt.stopPropagation();
			});

			// trigger focus and blur on every input and select that are required
			jq.doTimeout("checkUserInput", 1, function() {
				checkTheseElementsBeforeSubmit.each(function() {
					jq(this).trigger("focusin");
					jq(this).trigger("focusout", "true");
				});
				
				jqthis.focus();
				jq.doTimeout("removeFNPPS", 1, function() {
					jq(".fnppContainer", jqForm).remove();
				
					// invalid fields
					var invalidFields = checkableElements.filter(".invalid");
					var loadingFields = checkableElements.filter(".loading");
					// Must add notification in case some fields are loading...
					if (invalidFields.length != 0) {
					// there are invalid fields
					
						// display proper "tab"
						var dataRef = invalidFields.first().closest("[data-targetgroupid='registerGroup']")
															.attr("id");
						allSideNavMenuLinks.filter(function(index) {
												return jq(this).attr("data-ref") == dataRef;
											}).trigger("click");
						
						invalidFields.on({
							"change.invalid" : function() {
								jqthis.prop("disabled", false).removeClass("disabled").children(".locked")
																.removeClass("locked").addClass("unlocked");
								invalidFields.off("change.invalid");
							}
						});
						
						policyAgreementCheckbox.prop("disabled", false);
					
						if (jqForm.data("validation").mode == "silent") {
							var invalidFieldHeaders = new Array();
							var invalidFieldErrorLists = new Array();
							invalidFields.each(function() {
								invalidFieldHeaders.push(jq.trim(jq(this).siblings("label").text().replace(/[\*|\:]/g, "")));
								invalidFieldErrorLists.push(snifInputErrors(jq(this)));
							});
		
							// ajax query to get notification
							jq.ajax({
								// ask "url" to acquire "data" as html
								url : _host+"/Library/Media/Tools/eBuilder/forms/validator/ajax/fnin.php", 
								data : { 
									// notification id
									headers : invalidFieldHeaders, 
									errorLists : invalidFieldErrorLists, 
									locale : jqForm.attr("locale")
								}, 
								// on completion
								success : function(msg) {
									//console.log(msg);
									var jqresponse = jq(msg).find(".content").children();
									
									jqForm.children(".form_report").html(jqresponse);
								}, 
								dataType : "html"
								})
								.error(function() { 
									//runs if  any error occurs
									//alert("!");
								});
						} else {
							// temp popup to check if  everything works correctly
							jqForm.redPopups("ajaxGlobalPopup", 
									{
										"sender" : jq(this), 
										"binding" : "on", 
										"type" : "obedient", 
										"withTimeout" : true, 
										"withFade" : true
									}, 
									//popup properties
									{
										sender : jq(this), 
										context : jq('<div class="uiGppHolder" id="fnpp" style="opacity : 1;"><a class="actionButton"></a><div class="uiPpArrow" style="left : 0px; top : 4px; "></div><div class="uiSysNtf error"><div class="uiGlbDataContainer">'+'<div class="dtaHead"><div class="ntfHeader">ERROR!!!</div></div>'+'<div id="msg_content" class="dtaBody">ERROR!!!</div>'+'<div class="dtaFooter"><div class="ntfFooter"><div class="sign"><span>Redback Notification Center • </span><a href="" target="" onclick="" tabindex="-1"><span id="lbl_help" class="uiMlgLit" data-lang="el_GR" data-type="Static" data-rsrc="sys_ntf_center">Βοήθεια</span></a></div></div></div>'+'</div></div></div>"'), 
										ppID : 'ppSSNPopup', 
										filePath : '/ajax/notifications/glpp.php', 
										position : 'user', 
										data : {
											popupID : "globalSSNPopup", 
											popupTitle : "Warning!"
										}
									}
							);
						}
						
						// focus on first
						//invalidFields.first().focus();

						// prevent submit
						return false;
					} else if (loadingFields.length == 0) {
						// what to do if loading occurs
					} else {
						jqthis.focus();
						jqterms = jqForm.find("[data-terms]");
						if (jq.type(jqterms) != "undefined" && jq.type(jqterms) != "null" 
							 &&  jqterms.data("terms").mode == "verbose") {
							jq.ajax({
								type : "GET", 
								url : _host+"/ajax/legal/terms.php", 
								dataType : "html", 
								success : function(cntxt) {
									// temp popup to check if  everything works correctly
									//console.log("success");
									//console.log(cntxt);
									jqForm.redPopups("ajaxGlobalPopup", 
										{
											"sender" : jq(this), 
											"binding" : "on", 
											"type" : "persistent", 
											"withBackground" : true, 
											"withFade" : true
										}, 
										//popup properties
										{
											sender : jq(this), 
											ppID : 'ppTerms', 
											context : jq(cntxt), 
											position : 'center', 
										}
									);
								}
							})
							.error(function() { 
								//runs if  no match is found or any error occurs
							});
						}
					}
				});
			});
			
			
		}
	);
	
	// check if  captcha is passed
	jq(document).on({
		change : function() {
			var jqthis = jq(this);
			var submitButton = jqthis.closest("form").find(submitButtonElement.replace("form ", ""));
			if (jqthis.prop("checked")) {
				if (jqthis.closest("form").find(".mCanvasHolder").find(".mc-valid").length>0) {
					submitButton.prop('disabled', false)
										.removeClass("disabled")
										.children(".locked").removeClass("locked").addClass("unlocked");
				}
			} else {
				submitButton.prop('disabled', true)
									.removeClass("disabled").addClass("disabled")
									.children(".unlocked").removeClass("unlocked").addClass("locked");
			}
		}
	}, policyAgreementCheck);
	
	jq(document).on({
		"focusin.allName" : function() {
			if (canITrigger) {
				//jq("textarea#test").val(jq("textarea#test").val()+jq(this).closest("form").find(".fnppContainer").length+" focusin");
				// change last focused
				canITrigger = !canITrigger;
				lastFocusedInput = jq(this);
			}
		}, 
		
		// focusout : against chrome bug
		"focusout.allName" : function() {
			if (!canITrigger) {
				//jq("textarea#test").val(jq("textarea#test").val()+jq(this).closest("form").find(".fnppContainer").length+" focusin");
				canITrigger = !canITrigger;
			}
		}
	}, allNameElements);
	
//---- functions ---------------------------------------------------------------------------------------	
	initializeFields();
	// initialize some values in the form
	function initializeFields() {
		jq(updateElementsOnInit).val("");
		jq(autofocusedElement).first().trigger("focusin");
		jq(validationForms).each(function() {
			jq(this).find(submitButtonElement.replace("form[data-validation] ", "").replace(":not(:disabled)", "")).attr("data-validator-control", "true");
		});
	}
	
	// removes previous popup
	function removeFFNPOldPopup(context) {
		// dispose notification popup
		jq(".fnppContainer.fnpp_old", context).animate({opacity : 0, top : "+=10"}, "middle", function() {
			jq(this).remove();
		});
	}
	
	// emphasizes popup (!)
	function emphasizeFFNPPopup(context) {
		// dispose notification popup with delay, to produce emphasize effect on new popup
		jq(".fnppContainer.fnpp_old", context).doTimeout("erasePopup", 400, function() {
			jq(".fnppContainer.fnpp_old", context).animate({opacity : 0}, "middle", function() {
				jq(this).remove();
			});
		}, false);
	}
	
	// default class updater
	function elementClassUpdater(condition, elementToCheck, errorClass) {
		if (condition) {
			// condition = invalid field according to this check
			elementToCheck.removeClass("invalid "+errorClass).addClass("invalid "+errorClass);
		} else {
			// !condition = valid field according to this check
			elementToCheck.removeClass("invalid "+errorClass);
		}
	}
	
	// default icon updater
	function iconClassUpdater(elementToCheck) {
		// info icon container
		var divToUpdate = elementToCheck.siblings("div#"+elementToCheck.attr("id")+"_icon.inputInfoIcon");
		if (elementToCheck.hasClass("invalidInputWarning") 
			 ||  elementToCheck.hasClass("uniqueInputWarning")) {
			divToUpdate.removeClass("warning invalid valid info").addClass("warning");
		} else if (elementToCheck.hasClass("invalid")) {
			divToUpdate.removeClass("warning invalid valid info").addClass("invalid");			
		}/* else if (elementToCheck.hasClass("loading")) {
			divToUpdate.removeClass("warning invalid valid info").addClass("loader");
		}*/ else {
			if (jq(limitedInputElements).filter(elementToCheck).length > 0) {
				divToUpdate.removeClass("warning invalid valid info").addClass("valid");
			} else {
				divToUpdate.removeClass("warning invalid valid info");
			}
		}
	}

	// pass meter
	function passwordEvaluator(password, jqBar) {
		var maxCharRepetition = function(str) {
			// function : calculate maximum repetition of a character in a string
			// returns : that number
			var lastchar = '';
			var charcount = 0;
			var tmpcount = 0;
			var i = 0;
			while(i<str.length) {
				if (lastchar == str.charAt(i)) {
					tmpcount++;
				} else {
					tmpcount = 0;
					lastchar = str.charAt(i);
				}
				if (tmpcount > charcount) {
					charcount = tmpcount;
				}
				i++;
			}
			
			return charcount = (charcount<6) ? charcount : charcount*4;
		}
		// function : evaluate password strength
		// result : change color and length to the meter bar

		// initialize password score | bar length
		var score = 0;
		var width = 0;

		// extra credits as length increases
		score = (password.length<10) ? score + (password.length * 5) : score + 50;
		// minus credits for character repetition
		score -= maxCharRepetition(password);
		if (password.match(/^.*\d.*\d.*\d.*$/g)) {
			// password contains at least three digits -> extra credits
			score += 5;
		}
		if (password.match(/^.*\W.*\W.*$/g)) {
			// password contains at least two special characters -> extra credits
			score += 5;
		}
		if (password.match(/^(.*[a-z].*[A-Z].*|.*[A-Z].*[a-z].*)$/g)) {
			// password contains both capitalized and uncapitalized characters -> extra credits
			score += 10;
		}
		if (password.match(/^(.*[a-zA-Z].*\d.*|.*\d.*[A-Za-z].*)$/g)) {
			// password contains both characters and digits -> extra credits
			score += 10; // that is default
		}
		if (password.match(/^(.*\W.*\d.*|.*\d.*\W.*)$/g)) {
			// password contains both special characters and digits -> extra credits
			score += 10; // that is default
		}
		if (password.match(/^(.*[a-zA-Z].*\W.*|.*\W.*[A-Za-z].*)$/g)) {
			// password contains both special characters and characters -> extra credits
			score += 10; // that is default
		}
		if (password.match(/^[a-zA-Z]$/g)) {
			// password contains only characters -> minus credits
			score -= 10; // that can't happen because of the password regexp, but anyway
		}
		if (password.match(/^[0-9]$/g)) {
			// password contains only digits -> minus credits
			score -= 10; // that can't happen because of the password regexp, but anyway
		}
		// minimum score without repetition is 60 {four letters, one digit, one special char, 6 length, no caps}
		// minimum score 0 | maximum score 100
		score = (score<0) ? 0 : score;
		score = (score>100) ? 100 : score;
		// color range 255 0 0 -> 255 255 0 -> 0 255 0 = 510
		// readjust width. readjust score to color range
		width = score * 1.02; // bar has width of 102px
		width = (width == 0) ? "1px" : Math.round(width)+"px"; // readjust minimum width to 1px
		score *= 5.1; // readjust score to color range
		var color = "rgba(255, 0, 0, 0.4)"; // min color : red
		if (Math.round(score)<=255) {
			// from red to yellow
			color = "rgba(255, " + Math.round(score) + ", 0, 0.4)";
		} else {
			// from yellow to green
			color = "rgba("+(510 - Math.round(score))+", 255, 0, 0.4)";
		}
		// update bar
		jqBar.css("background", color).css("width", width);
	}
	
	function fnppInfoRequirementsChecker(jqtarget, infoPopupSibling) {
		if (infoPopupSibling.length == 1) {
			infoPopupSibling.find("ul")
				.children("li").children("div").each(function() {
					switch(jq(this).attr("id")) {
						case "ann.required" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
							}
							break;
						case "ann.maxLen" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (jqtarget.val().length>jqtarget.data("validators").maxLen) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.minLen" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (jqtarget.val().length<jqtarget.data("validators").minLen) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.size" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (jqtarget.val().length != jqtarget.data("validators").size) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.DIGITS_ONLY_REGEXP" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.DIGITS_ONLY_REGEXP)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.NO_DIGITS_REGEXP" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.NO_DIGITS_REGEXP)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.USERNAME_REGEXP" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.USERNAME_REGEXP_PROPERCHARSET)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;	
						case "ann.USERNAME_REGEXP_PROPERSIZE" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.USERNAME_REGEXP_PROPERSIZE)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.PASSWORD_REGEXP" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.PASSWORD_REGEXP)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.ALPHANUMERIC_REGEXP" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.ALPHANUMERIC_REGEXP)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.EMAIL_REGEXP" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (!jqtarget.val().match(REGULAR_EXPRESSIONS.EMAIL_REGEXP)) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						case "ann.validate" : 
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppBullet");
							} else {
								if (jqtarget.val() != jq("#"+jqtarget.data("validators").validate).val()) {
									jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText");
								} else {
									jq(this).parent().removeClass()
												.addClass("fnppGreenBullet fnppGreenText");
								}
							}
							break;
						default : 
						  // no such validator!!!
					}
			});
		}
	}

	function snifInputErrors(jqtarget) {
		function pushNotification(options) {
			var settings = jq.extend({
				"array" : errorIDs, 
				"element2push" : "", 
				"header" : false, 
				"hidden" : false, 
				"list" : true, 
				"footer" : false
			}, options);
			
			if (settings['header']) {
				settings['element2push'] += " header";
			}
			if (settings['hidden']) {
				settings['element2push'] += " hidden";
				hiddens++;
			}
			if (settings['list']) {
				settings['element2push'] += " list";
			}
			if (settings['footer']) {
				settings['element2push'] += " footer";
			}
			settings['array'].push(settings['element2push']);
		}
		// error params to create error messages
		var errorIDs = new Array();
		var hstr = "";
		var hiddens = 0;
		var visible = 0;
		// push errors
		if (jqtarget.closest("form").data("validation").mode == "verbose") {
			pushNotification({"element2push" : "err.header", "header" : true, "list" : false});
			visible++;
		}
		if (jqtarget.hasClass("required")) {
			pushNotification({"element2push" : "err.required", "hidden" : jq.trim(jqtarget.val()) != ""});
		}
		var validators = jqtarget.data("validators");
		for(var v in validators) {
			switch(v) {
				case "maxLen" : 
					pushNotification({
						"element2push" : "err.maxLen"+" "+validators[v], 
						"hidden" : jqtarget.val().length<=validators[v]
					});
					break;
				case "minLen" : 
					pushNotification({
						"element2push" : "err.minLen"+" "+validators[v], 
						"hidden" : jqtarget.val().length >= validators[v]
					});
					break;
				case "size" : 
					pushNotification({
						"element2push" : "err.size"+" "+validators[v], 
						"hidden" : jqtarget.val().length == validators[v]
					});
					break;
				case "numeric" : 
					if (validators[v]) {
						pushNotification({
							"element2push" : "err.DIGITS_ONLY_REGEXP", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.DIGITS_ONLY_REGEXP)
						});
					} else if (!validators[v]) {
						pushNotification({
							"element2push" : "err.NO_DIGITS_REGEXP", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.NO_DIGITS_REGEXP)
						});
					}
					break;
				case "regexp" : 
					if (validators[v] == "USERNAME_REGEXP") {
						pushNotification({
							"element2push" : "err.USERNAME_REGEXP_PROPERSIZE", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.USERNAME_REGEXP_PROPERSIZE)
						});
						pushNotification({
							"element2push" : "err.USERNAME_REGEXP_PROPERCHARSET", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.USERNAME_REGEXP_PROPERCHARSET)
						});	
					} else if (validators[v] == "PASSWORD_REGEXP") {
						pushNotification({
							"element2push" : "err.PASSWORD_REGEXP", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.PASSWORD_REGEXP)
						});	
						if (jqtarget.closest("form").data("validation").mode == "verbose") {
							pushNotification({
								"element2push" : "err.PASSWORD_REGEXP_HINT", 
								"list" : false, 
								"footer" : true
							});
							visible++;
						}
					} else if (validators[v] == "ALPHANUMERIC_REGEXP") {
						pushNotification({
							"element2push" : "err.ALPHANUMERIC_REGEXP", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.ALPHANUMERIC_REGEXP)
						});	
						if (jqtarget.closest("form").data("validation").mode == "verbose") {
							pushNotification({
								"element2push" : "err.ALPHANUMERIC_REGEXP_HINT", 
								"list" : false, 
								"footer" : true
							});
							visible++;
						}
					} else if (validators[v] == "EMAIL_REGEXP") {
						pushNotification({
							"element2push" : "err.EMAIL_REGEXP", 
							"hidden" : jqtarget.val().match(REGULAR_EXPRESSIONS.EMAIL_REGEXP)
						});	
						if (jqtarget.closest("form").data("validation").mode == "verbose") {
							pushNotification({
								"element2push" : "err.EMAIL_REGEXP_HINT", 
								"list" : false, 
								"footer" : true
							});
							visible++;
						}
					}
					break;
				case "validate" : 
					pushNotification({
							"element2push" : "err.validate", 
							"hidden" : jqtarget.val() == jq("#"+jqtarget.data("validators").validate).val()
						});		
					break;
				case "exists" : 
					if (validators[v] == "false") {
						pushNotification({
							"element2push" : "err.exists", 
							"hidden" : !jqtarget.hasClass("inputExistenceError")
						});	
					} else {
						pushNotification({
							"element2push" : "err.absent", 
							"hidden" : !jqtarget.hasClass("inputExistenceError")
						});	
					}	
					break;
				default : 
				  // no such validator!!!
			}
		}	
		if (jqtarget.closest("form").data("validation").mode == "verbose") {
			if (errorIDs.length-hiddens>visible) {
				jqtarget.siblings("div#"+jqtarget.attr("id")+"_icon.inputInfoIcon").removeClass().addClass("inputInfoIcon invalid");
				if (jqtarget.siblings("#"+jqtarget.attr("id")+"_fnpp.fnppContainer > .error").length != 0) {
					// change popup id to separate from the one that is going to be created
					jq("div#fnpp", "form").attr("id", "fnpp_old").addClass("fnpp_old");
					// create new popup anchor in the row {will be populated when a new popup is created}
					jqtarget.parent().append("<div id='"+jqtarget.attr("id")+"fnpp'></div>");
					emphasizeFFNPPopup(jqtarget.closest("form"));
				}
				
				// create the popup
				updateLabelNotificationViaAJAX({
					dataType : "error", 
					dataID : errorIDs, 
					currentJQElement : jqtarget, 
					locale : jqtarget.closest("form").attr("locale")
				});	
			}
		} else if (jqtarget.closest("form").data("validation").mode == "silent") {
			return errorIDs;
		}
	}
	
	function fnppErrorRequirementsChecker(jqtarget, errorPopupHolder) {
		if (errorPopupHolder.length == 1) {
			errorPopupHolder.find("ul")
				.children("li").children("div").each(function() {
					switch(jq(this).attr("id")) {
						case "err.required" : 
							//mandatory
							if (jq.trim(jqtarget.val()) == "") {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.maxLen" : 
							if (jqtarget.val().length>jqtarget.data("validators").maxLen) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.minLen" : 
							if (jqtarget.val().length<jqtarget.data("validators").minLen) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.size" : 
							if (jqtarget.val().length != jqtarget.data("validators").size) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.DIGITS_ONLY_REGEXP" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.DIGITS_ONLY_REGEXP)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.NO_DIGITS_REGEXP" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.NO_DIGITS_REGEXP)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.USERNAME_REGEXP_PROPERCHARSET" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.USERNAME_REGEXP_PROPERCHARSET)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;	
						case "err.USERNAME_REGEXP_PROPERSIZE" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.USERNAME_REGEXP_PROPERSIZE)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.PASSWORD_REGEXP" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.PASSWORD_REGEXP)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.ALPHANUMERIC_REGEXP" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.ALPHANUMERIC_REGEXP)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;
						case "err.validate" : 
							if (jqtarget.val() != jq("#"+jqtarget.data("validators").validate).val()) {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							} else {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							}
							break;
						case "err.exists" : 
						case "err.absent" : 
							jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
							jqtarget.filter(".invalid.inputExistenceError").removeClass("invalid inputExistenceError");
							jq(this).parent().not(".noDisplay")
								.doTimeout("hideListElement", 750, function() {
									jq(this).animate({
										opacity : 0
									}, 400, function() {
										jq(this).addClass("noDisplay");
										jq(this).css("opacity", "");
									});
							});
							break;
						case "err.EMAIL_REGEXP" : 
							if (!jqtarget.val().match(REGULAR_EXPRESSIONS.EMAIL_REGEXP)) {
								jq(this).parent().removeClass()
												.addClass("fnppRedBullet fnppRedText")
												.removeClass("noDisplay")
												.doTimeout("hideListElement");
							} else {
								jq(this).parent().not(".noDisplay").removeClass()
												.addClass("fnppLineThroughBullet fnppLineThroughText");
								jq(this).parent().not(".noDisplay")
									.doTimeout("hideListElement", 750, function() {
										jq(this).animate({
											opacity : 0
										}, 400, function() {
											jq(this).addClass("noDisplay");
											jq(this).css("opacity", "");
										});
								});
							}
							break;	
						default : 
						  // no such validator!!!
					}
			});
			if (errorPopupHolder.find("ul").children("li.fnppRedBullet").not(".noDisplay").length == 0) {
				// change popup id to separate from the one that is going to be created in the future
				jqtarget.closest("form").find(".fnppContainer").attr("id", "fnpp_old").addClass("fnpp_old");
				// create new popup anchor in the row {will be populated when a new popup is created}
				jqtarget.parent().append("<div id='"+jqtarget.attr("id")+"_fnpp'></div>");
				// remove old
				removeFFNPOldPopup(jqtarget.closest("form"));
			}
		}
	}
	
	// label popup to notify during form filling
    function updateLabelNotificationViaAJAX(options) { 
    	// Create some defaults, extending them with any options that were provided
		var settings = jq.extend({
			// type of notification : info | error | warning
			"dataType" : "", 
			// notification ID
			"dataID" : "", 
			// sibling (popup points to this)
			"currentJQElement" : null, 
			// extend notification with extra data
			"notificationExtension" : "", 
			// orientation and alignment
			"position" : ["right", "top"], 
			// locale
			"locale" : "el_GR"
		}, options);
		
		// change popup's position
		/*var repositionPopUp = function(fnPopUp, pos) {
			// split values
			var posArr = pos.split("|");
			// orientation
			var metrics1 = posArr[0].split(" : ");
			// alignment
			var metrics2 = posArr[1].split(" : ");
			
			// set css
			fnPopUp.css(metrics1[0], parseInt(metrics1[1]));
			fnPopUp.css(metrics2[0], parseInt(metrics2[1]));
			
			var ppArrow = fnPopUp.find(".uiPpArrow");
			ppArrow.css(metrics1[0], -ppArrow.width());
			ppArrow.css(metrics2[0], +4);
			
			// check if  a popup already exists there
			if (settings['currentJQElement'].siblings(".fnppContainer.fnpp_old").length != 0) {
				// if  popup exists, emphasize it
				fnPopUp.css("top", "-=10");
				fnPopUp.animate({opacity : 1}, "middle");
			} else {
				//  else slide the new popup in
				fnPopUp.animate({opacity : 1, top : "-=10"}, "middle");
			}
			
		}*/
		
		return jq(this).each(function() {  
			var ajaxData;
			if (jq.type(settings['dataID']) == "string") {
				ajaxData = { 
					// folder
					type : settings['dataType'], 
					// notification id
					id : settings['dataID'], 
					// locale
					locale : settings['locale']
				}
			} else {
				var firstElem = settings['dataID'].shift();
				if (jq.type(firstElem) == "object") {
					ajaxData = { 
						// info header from form
						info : firstElem, 
						// folder
						type : settings['dataType'], 
						// notification id
						id : settings['dataID'], 
						// locale
						locale : settings['locale']
					}
				} else {
					settings['dataID'].unshift(firstElem);
					ajaxData = {
						// folder
						type : settings['dataType'], 
						// notification id
						id : settings['dataID'], 
						// locale
						locale : settings['locale']
					}
				}
			}
			
			//console.log(dataObject);
			// ajax query to get notification
			jq.ajax({
				// ask "url" to acquire "data" as html
				url : _host+"/ajax/apps/aux_content.php?app=1&fl=ajax::fnpp", 
				data : ajaxData, 
				// on completion
				success : function(cntxt) {
					// create jq object from returned text that holds the popup
					//console.log(popup);
					var jqcntxt = jq(cntxt);//.children(".uiSysNtf");
					jqcntxt.attr("id", settings['currentJQElement'].attr("id")+"_fnpp");
					//jqcntxt.addClass("fnppContainer");
					// icon container
					var iconSibling = settings['currentJQElement'].siblings("div#"+settings['currentJQElement'].attr("id")+"_icon.inputInfoIcon");
					// document height before the popup is appended
					//var documentHeightBeforeAppend = jq(document).height();
					// position new popup in proper row
					iconSibling.redPopups("ajaxGlobalPopup", 
						{
							"sender" : iconSibling, 
							"binding" : "on", 
							"type" : "persistent", 
							"withFade" : true
						}, 
						//popup properties
						{
							sender : iconSibling, 
							parent : iconSibling.parent(), 
							context : jqcntxt, 
							ppID : settings['currentJQElement'].attr("id")+"_fnpp", 
							position : 'right|top', 
							positionOffset : 10
						}
					);
					
					settings['currentJQElement'].siblings("div#"+settings['currentJQElement'].attr("id")+"_fnpp").replaceWith(jqcntxt.parent().addClass("fnppContainer"));

				}, 
				dataType : "html"
				})
				.error(function() { 
					//runs if  any error occurs
					//alert("!");
				});
		});
    }
	
	RegExp.escaped = function(str) {
		return (str+'').replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
	};
	
});