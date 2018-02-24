var jq=jQuery.noConflict();
jq(document).one("ready.extra", function() {
	treeView.init();
});

treeView = {
	init: function() {
		// Save tree state on unload
		jq(window).on("unload", function(){
			treeView.saveState();
		});
		
		jq(document).on("saveState.treeView", ".treeView", function(ev){
			treeView.saveState(jq(this));
		});
		jq(document).on('toggleState', ".treeView .treeItem[expandable]", function(ev) {
			ev.stopPropagation();
			jq(this).children(".treePointer").trigger("click");
		});
		
		jq(document).on('openState', ".treeView .treeItem[expandable]", function(ev) {
			ev.stopPropagation();
			jq(this).addClass("open");
		});
		
		jq(document).on('closeState', ".treeView .treeItem[expandable]", function(ev) {
			ev.stopPropagation();
			jq(this).removeClass("open");
		});
		
		jq(document).on("mouseenter mouseleave", ".treeView .treeItem:not([expandable])",function(){
			jq(this).closest("[expandable='semi-expandable']").toggleClass("highlight");
		});
		
		jq(document).on('click', ".treeView .treePointer:not(:hidden), .treeView .treeItem[expandable='expandable'] > .treeItemContent", function(ev) {
			// Stops the Bubbling
			ev.stopPropagation();
			// Toggle Open
			jq(this).parent().toggleClass("open");
		});
		
		// Set actions when content is modified
		jq(document).on("content.modified", function() {
			// Initialize tree views
			treeView.initialize();
			
			// Set visibility
			treeView.filter();
			
			// Sort all tree views
			treeView.sort();
		});
	},
	initialize: function() {
		// Get all tree views not initialized
		jq(".treeView").not(function(){
			return jq(this).data("stateInitialized") === true;
		}).each(function(){
			var id = jq(this).attr("id");
			
			var state = new UIStateObject(id);
			var tree = state.getState(true);

			if (typeof(tree) == "undefined" || tree == null)
				return;
				
			for (var c in tree) 
				jq("#"+tree[c], jq(this)).addClass("open");
			
			jq(this).data("stateInitialized", true);
		});
	},
	saveState: function(treeView) {
		// Get given treeView or all
		var jqTreeView = treeView;
		if (jq.type(jqTreeView) == "undefined")
			jqTreeView = jq(".treeView");
		
		// Save stae
		jqTreeView.each(function() {
			jq(this).data("stateInitialized", false);
			var id = jq(this).attr("id");
			if (jq.type(id) == "undefined" || id == null)
				return;
			
			var tree = new Object();
			jq(this).find(".open").each(function(index){
				tree[index] = jq(this).attr("id");
			});
			
			var state = new UIStateObject(id);
			state.setState(tree, true);
		});
	},
	filter: function() {
		jq(".treeView .treeItem[expandable]").each(function(){
			var thisChildren = jq(this).children();
			
			if (thisChildren.filter(".treeViewList:empty")
				.add(thisChildren.filter(".subTreeView").children("ul:empty"))
				.length != 0)
				thisChildren.filter(".treePointer").css("visibility", "hidden");
			else
				thisChildren.filter(".treePointer").css("visibility", "");
		});
	},
	sort: function() {
		jq(jq(".treeView .treeViewList, .treeView .subTreeView > ul").get().reverse()).each(function(){
			var sortByAttr = "sort-value";
			var collection = new Array();
			
			var undefined = 0;
			jq(this).children().each(function(index){
				if (jq.type(jq(this).attr(sortByAttr)) == "undefined")
					undefined++;
				collection.push({"index": index, "value": jq(this).attr(sortByAttr)});
			});
	
			if (collection.length == undefined)
				return;
				
			collection.sort(function(a, b){
				return (a.value < b.value ? -1 : 1);
			});

			var clone = jq(this).clone(true, true).empty();
			for (i in collection) {
				jq(this).children().eq(collection[i].index).clone(true, true).appendTo(clone);
			}
			
			// not sure about this
			clone.find("[sort-value]").removeAttr("sort-value");

			jq(this).replaceWith(clone);
		});
	}
}