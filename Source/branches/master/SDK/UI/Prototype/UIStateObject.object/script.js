var jq = jQuery.noConflict();
function UIStateObject(identifier) {
	this.identifier = identifier;
}
UIStateObject.prototype.setState = function(state, session) {
	// Set local/session storage
	return dlocalStorage.set(this.identifier, state, session);
};

UIStateObject.prototype.getState = function(session) {
	// Get local/session storage
	return dlocalStorage.get(this.identifier, session);
};