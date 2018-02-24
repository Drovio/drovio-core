var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Reload page event
	jq(document).on("page.reload", function(ev) {
		location.reload(true);
	});
	// Redirect page event
	jq(document).on("page.redirect", function(ev, locationRef) {
		url.redirect(locationRef);
	});
	
	// Get content modified and trigger all static actions caused by RCPage
	jq(document).on("content.modified", function() {
		// Trigger all actionContainers
		jq(".actionContainer").each(function() {
			// Trigger action
			var action = jq(this).data("action");
			if (action == undefined)
				return true;

			// Trigger action to document
			jq(document).trigger(action.type, action.value);
			
			// Remove element
			jq(this).remove();
		});
	});
	
	
	// Set cookie consent notification options
	literal.load("sdk.UI.page", function() {
		// Get literals
		var cLiterals = literal.get("sdk.UI.page");
		
		// Set cookie consent notification options
		window.cookieconsent_options = {
			"message": cLiterals['cookiesconsent.message'],
			"dismiss": cLiterals['cookiesconsent.gotit'],
			"learnMore": cLiterals['cookiesconsent.more'],
			"link": "http://drov.io/help/Policies/Terms",
			"theme": "dark-bottom",
			"domain": "." + url.getDomain()
		};

		// Load script
		jq.getScript("//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js");
	});
	
	// Check for project update versions every 1 minute
	var updateDisposed = 0;
	jq(document.body).data("pv", jq(document.body).data("pv")).removeAttr("data-pv");
	var updateInterval = setInterval(function() {
		// Clear interval if user disposed the popup more than 2 times
		if (updateDisposed > 1)
			clearInterval(updateInterval);
		
		// Request for version update
		var currentVersions = jq(document.body).data("pv");
		JSONServerReport.request("/ajax/platform/updates.php", "GET", null, jq(document), function(response) {
			// Check response
			var update = false;
			for (var p in response['platform.versions'].payload)
				if (currentVersions[p] != response['platform.versions'].payload[p])
					update = true;
			
			// Show popup if it doesn't already exist
			if (update && jq(".platform-ntf-updates").length <= 1) {
				var jqSender = jq(".platform-ntf-container");
				jqSender.popup.withFade = true;
				jqSender.popup.type = "persistent";
				jqSender.popup.position = {"bottom":"20px","left":"20px","position":"fixed"};
				jqSender.popup(jq(".platform-ntf-container .platform-ntf-updates").clone(true));
			}
		}, null, null, false);
	}, 60000);
	
	// Platform updates notification listeners
	jq(document).on("click", ".platform-ntf-updates .close_button", function() {
		// Dispose popup
		jq(this).trigger("dispose");
		
		// Increase times of disposed
		updateDisposed++;
	});
	
	jq(document).on("click", ".platform-ntf-updates .refresh_button", function() {
		window.location.reload(true);
	});
});