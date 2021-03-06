// jQuery part
// change annotation from "$" to "jq"
var jq = jQuery.noConflict();
var statePushed = false;

jq(document).one("ready", function() {
	state.init();
});

state = {
	init : function() {
		jq(window).off('popstate');
		jq(window).on('popstate', function(ev) {
			// If it's a reload event and not a push state, ignore this trigger
			if (!statePushed)
				return;

			var path_name = window.location.pathname;
			var found = false;
			var reg_exp = path_name+"$";
			var match_reg_exp = new RegExp(reg_exp);
			
			// Find weblink with the same location href
			jq("a").each(function() {
				if (jq.type(jq(this).attr('href')) != "undefined" && jq(this).attr('href').match(match_reg_exp)) {
					found = true;
					jq(this).click();
					return false;
				}
			});

			// If weblink not found, reload the page
			if (!found)
				location.reload(true);
		});
	},
	push : function(state, callback) {
			
		// If it's the same state from trigger, do nothing
		// Else
		if (window.location.pathname != state && (state != "" && state != "#" && jq.type(state) != "undefined")) {
			window.history.pushState(Date.now(), "Title", state);
			statePushed = true;
			console.log("state pushed");
			// Trigger callback
			if (typeof callback == 'function') {
				callback();
				// or callback.apply(this);
			}
		}
	}
}