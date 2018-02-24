jq = jQuery.noConflict();

jq(document).one("ready", function() {
	
	// Add containers for name resolving
	moduleGroup.addContainer(".uiHistoryManager");
	module.addContainer(".uiHistoryManager");
	sqlDomain.addContainer(".uiHistoryManager");

	// Search for all uiHistoryManager objects
	jq(document).on("content.modified", function() {
		jq(".uiHistoryManager").each(function() {
		
			// Check if commit container is empty or not
			if (jq(".cmContainer", this).contents().length == 0) {
				var jqSender = jq(this);
				
				// Set request data
				var hmid = jq(".cmContainer", this).attr("id");
				var hid = jq(this).closest(".uiHistoryManager").attr("id")
				
				// Request init page
				requestNextPage(jqSender, hmid, hid, 0);
			}
		});
	});
	
	
	jq(document).on("click", ".uiHistoryManager .navBtn.older.active", function() {
		// Set attributes
		var jqSender = jq(this).closest(".cmContainer");
		var hmid = jq(this).closest(".uiHistoryManager").find(".cmContainer").attr("id");
		var hid = jq(this).closest(".uiHistoryManager").attr("id");
		var nextPage = parseInt(jq(".clist").data("pagination").current) + 1;
		
		// Request next page
		requestNextPage(jqSender, hmid, hid, nextPage);
	});
	
	jq(document).on("click", ".uiHistoryManager .navBtn.newer.active", function() {
		// Set attributes
		var jqSender = jq(this).closest(".cmContainer");
		var hmid = jq(this).closest(".uiHistoryManager").find(".cmContainer").attr("id");
		var hid = jq(this).closest(".uiHistoryManager").attr("id");
		var nextPage = parseInt(jq(".clist").data("pagination").current) - 1;
		nextPage = (nextPage < 0 ? 0 : nextPage);
		
		// Request next page
		requestNextPage(jqSender, hmid, hid, nextPage);
	});
	
	var requestNextPage = function(jqSender, hmid, hid, nextPage) {
		// Set attributes
		var attrs = new Array();
		attrs['hmid'] = hmid;
		attrs['hid'] = hid;
		attrs['page'] = nextPage;
		
		// Add extra parameters
		var requestData = "";
		for (var name in attrs)
			requestData += "&"+name+"="+encodeURIComponent(attrs[name]);
		
		// Set ajax extra options
		ajaxOptions = new Object();
		ajaxOptions.loading = false;
		ajaxOptions.withCredentials = true;
		
		// Success callback
		var successCallback = function(report) {
			ModuleProtocol.handleReport(jqSender, report);
		}
			
		// Create request for commits
		ascop.request("/ajax/resources/sdk/vcs/history/clist.php", "GET", requestData, jqSender, successCallback, ajaxOptions);
	}
});