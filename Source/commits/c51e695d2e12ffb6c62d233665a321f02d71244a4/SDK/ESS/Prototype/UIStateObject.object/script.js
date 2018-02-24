/* 
 * Redback JavaScript Document
 *
 * Title: RedBack State Object Prototype
 * Description: --
 * Author: RedBack Developing Team
 * Version: 1.00
 * DateCreated: 07/06/2013
 * DateRevised: --
 *
 */
 
// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();
var bootHost = "";

function UIStateObject( identifier ) {
	this.identifier = identifier;
}

UIStateObject.prototype.setState = function( state, session ) {
	// Check if Storage is supported
	if( typeof(Storage) === "undefined") {
		return false;
	}

	// Don't keep state in session as default
	session = true;
	if ( typeof(session) === "undefined")
		session = false;
	
	// Check state and convert to JSON if possible
	if (jq.type(state) == "array" || typeof(state) == "object")
		try {
			state = JSON.stringify(state);
		} catch(err) {
			return false;
		}
	
	if (typeof(state) != "string")
		try {
			state = String(state);
		} catch(err) {
			return false;
		}
	
	if (session)
		sessionStorage.setItem(this.identifier, state)
	else
		localStorage.setItem(this.identifier, state);
	
	return true;
};

UIStateObject.prototype.getState = function( session ) {
	// Check if Storage is supported
	if( typeof(Storage) === "undefined") {
		return false;
	}
	
	// Don't get state from session as default
	var state = "";
	if ( typeof(session) === "undefined")
		state = localStorage.getItem(this.identifier);
	else
		state = sessionStorage.getItem(this.identifier);
	
	try {
		state = jQuery.parseJSON(state);
	} catch(err) {
	}
	
	return state;
};