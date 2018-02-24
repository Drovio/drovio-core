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
		var page_literals = literal.get("sdk.UI.page");
		
		// Check if it's guest, otherwise they accepted our terms
		if (jq(".uiMainContainer").hasClass("guest")) {
			// Set cookie consent notification options
			window.cookieconsent_options = {
				"message": page_literals['cookiesconsent.message'],
				"dismiss": page_literals['cookiesconsent.gotit'],
				"learnMore": page_literals['cookiesconsent.more'],
				"link": "http://drov.io/help/Policies/Terms",
				"theme": "dark-bottom",
				"domain": "." + url.getDomain()
			};

			// Load script
			jq.getScript("//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js");
		}
		
		// Check for project update versions every 1 minute
		var updateDisposed = 0;
		jq(document.body).data("pv", jq(document.body).data("pv")).removeAttr("data-pv");
		var updateInterval = setInterval(function() {
			// Clear interval if user disposed the popup more than 2 times
			if (updateDisposed > 2)
				return clearInterval(updateInterval);
			
			// Check if the page is embedded and stop checking for updates
			if (jq("body").hasClass("embedded"))
				return clearInterval(updateInterval);

			// Request for version update
			var currentVersions = jq(document.body).data("pv");
			JSONServerReport.request("/ajax/platform/updates.php", "GET", null, jq(document), function(response) {
				// Check response
				var update = false;
				for (var p in response['platform.versions'].payload)
					if (currentVersions[p] != response['platform.versions'].payload[p])
						update = true;

				// Show popup if it doesn't already exist
				if (update) {
					// Load page notification
					var actionCallback = function() {
						// Reload page
						window.location.reload(true);
					}
					
					var disposeCallback = function() {
						// Increase times of disposed
						updateDisposed++;
					}
					
					// Show notification
					pageNotification.show(jq(document), "platform-updates", page_literals['platform.updates'], page_literals['platform.updates.refresh'], actionCallback, disposeCallback);
				}
			}, null, null, false);
		}, 2 * 60 * 1000);
	});
	
	// Google analytics code
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-69525623-1', 'auto');
	ga('send', 'pageview');
	
	// Mixpanel code
	(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(".");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;"undefined"!==typeof d?c=b[d]=[]:d="mixpanel";c.people=c.people||[];c.toString=function(b){var a="mixpanel";"mixpanel"!==d&&(a+="."+d);b||(a+=" (stub)");return a};c.people.toString=function(){return c.toString(1)+".people (stub)"};i="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
for(g=0;g<i.length;g++)f(c,i[g]);b._i.push([a,e,d])};b.__SV=1.2;a=e.createElement("script");a.type="text/javascript";a.async=!0;a.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";f=e.getElementsByTagName("script")[0];f.parentNode.insertBefore(a,f)}})(document,window.mixpanel||[]);
mixpanel.init("997bde31931591470d632c70c9519a31");
	
	// Track core page initialization
	mixpanel.track("Core page builder initialized");
});