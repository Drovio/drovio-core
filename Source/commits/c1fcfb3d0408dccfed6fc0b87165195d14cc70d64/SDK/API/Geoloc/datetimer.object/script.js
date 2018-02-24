jq(document).one("ready", function() {
	// Init interval
	datetimer.init();
	
	// Set content.modified
	jq(document).on("content.modified", function() {
		datetimer.update(true);
	});
});


datetimer = {
	literals : new Array(),
	init : function() {
		// Get literals
		literal.load("sdk.resources.dates", function(result) {
			datetimer.literals = result;
		});
		
		// Set update timer for live timestamps, every minute
		var liveStampInterval = window.setInterval(function() {
			datetimer.update(false);
		}, 30000);
	},
	update : function(init) {
		// Get current time for all timestamps
		var current_time = Math.round((new Date).getTime());
		current_time = Math.round(current_time / 1000);
		
		// Get every timestamp and update value according to format
		jq(".timestamp.live").each(function() {
			// Get attributes
			var format = jq(this).data("format");
			var utime = jq(this).data("utime");
			
			// Get time difference
			var timeDiff = current_time - utime;
			
			// Set context
			var context = "";
			if (timeDiff < 3) {
				// It's just now
				context = "just now";
			}
			else if (timeDiff < 60) {
				// It's seconds
				if (init)
					context = timeDiff + " " + datetimer.literals["seconds_ago"];
				else
					context = datetimer.literals["few_seconds_ago"];
			}
			else if (timeDiff < 60*60) {
				// It's minutes
				var minutes = Math.floor(timeDiff / 60);
				if (minutes == 1)
					context = datetimer.literals["a_minute_ago"];
				else
					context = minutes + " " + datetimer.literals["minutes_ago"];
			}
			else if (timeDiff < 24*60*60) {
				// It's hours
				var hours = Math.floor(timeDiff / (60*60));
				if (hours == 1)
					context = datetimer.literals["an_hour_ago"];
				else
					context = hours + " " + datetimer.literals["hours_ago"];
			}
			else if (timeDiff < 7*24*60*60) {
				// Days in a week
				var uday = (new Date(utime*1000)).getDay();
				var cday = (new Date(current_time*1000)).getDay();
				var days = cday - uday;
				days = (days <= 0 ? 7 + cday - uday : days);
				if (days == 1)
					context = datetimer.literals["yesterday_at"] + " " + (new Date(utime*1000)).format("H:i");
				else
					context = days + " " + datetimer.literals["days_ago"];
			}
			else
				context = (new Date(utime*1000)).format(format);
			
			// Set context
			jq(this).html(context);
		});
	}
}