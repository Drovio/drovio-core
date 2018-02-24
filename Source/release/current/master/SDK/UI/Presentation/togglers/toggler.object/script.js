var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	toggler.init();
});

toggler = {
	init: function() {
		// Set toggler click action
		jq(document).on('click', '.toggler .togHeader', function(ev) {
			var jqToggler = jq(this).closest(".toggler");
			if (jqToggler.hasClass('open'))
				toggler.close(jqToggler);
			else
				toggler.open(jqToggler);
		});
		
		// Set toggler handlers
		jq(document).on("content.modified", function(ev) {
			// Open toggler
			jq(".toggler").off("open.toggler");
			jq(".toggler").on("open.toggler", function(ev) {
				// Stop bubbling
				ev.stopPropagation();
				
				// Open toggler
				toggler.open(this);
			});
			
			// Close toggler
			jq(".toggler").off("close.toggler");
			jq(".toggler").on("close.toggler", function(ev) {
				// Stop bubbling
				ev.stopPropagation();
				
				// Open toggler
				toggler.close(this);
			});
		});
		
		// Set head trigger (deprecated)
		jq(document).on('setHead.toggler', '.toggler', function(ev, content) {
			console.log("triggering event for setting toggler head is deprecated. Use toggler.setHead()");
			// Stop bubbling
			ev.stopPropagation();
			
			// Set toggler head
			toggler.setHead(this, content);
		});
		
		jq(document).on('appendToBody.toggler', '.toggler', function(ev, content) {
			console.log("triggering event for appending to toggler body is deprecated. Use toggler.appendBody()");
			// Stop bubbling
			ev.stopPropagation();
			
			// Append content to body
			toggler.appendBody(this, content);
		});
	},
	setHead: function(toggler, head) {
		if (head)
			jq(".headerContent", toggler).empty().append(head);
	},
	appendBody: function(toggler, body) {
		if (body)
			jq(".togBody", toggler).append(body);
	},
	open: function(toggler) {
		jq(toggler).addClass("open");
	},
	close: function(toggler) {
		jq(toggler).removeClass("open");
	}
}