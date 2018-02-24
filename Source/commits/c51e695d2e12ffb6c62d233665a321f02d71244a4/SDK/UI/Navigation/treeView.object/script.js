/* 
 * Redback JS Document
 *
 * Title: RedBack JS Library - treeView
 * Description: --
 * Author: RedBack Developing Team
 * Version: --
 * DateCreated: 04/10/2012
 * DateRevised: --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// let the document load
jq(document).one("ready.extra", function() {
	
	// Save tree state on unload
	jq(window).on("unload", function(){
		jq(".treeView").trigger("saveState.treeView");
	});
	
	jq(document).on("saveState.treeView", ".treeView", function(ev){
		ev.stopPropagation();
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
	
	jq(document).on("content.modified", function(){
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
	
	jq(document).on('click', ".treeView .treePointer:not(:hidden), .treeView .treeItem[expandable='expandable'] > .treeItemContent", function(ev) {
		// Stops the Bubbling
		ev.stopPropagation();
		// Toggle Open
		jq(this).parent().toggleClass("open");
	});
	
	jq(document).on("content.modified", function(){
		
		jq(".treeView .treeItem[expandable]").each(function(){
			var thisChildren = jq(this).children();
			
			if (thisChildren.filter(".treeViewList:empty")
				.add(thisChildren.filter(".subTreeView").children("ul:empty"))
				.length != 0)
				thisChildren.filter(".treePointer").css("visibility", "hidden");
			else
				thisChildren.filter(".treePointer").css("visibility", "");
		});
		
	})
	
	// sort treeview
	jq(document).on("content.modified", function(){
		jq(jq(".treeView .treeViewList, .treeView .subTreeView > ul").get().reverse()).each(function(){
			var sortByAttr = "sort-value";
			//var sortByOrder = jq(this).attr("sort-by").order;                    
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
				//if(jq.type(sortByOrder) == "undefined" || sortByOrder != "descending")
					return (a.value < b.value ? -1 : 1);
				//else
					//return (a.value > b.value ? -1 : 1);
			});

			var clone = jq(this).clone(true, true).empty();
			for(i in collection){
				jq(this).children().eq(collection[i].index).clone(true, true).appendTo(clone);
			}
			
			// not sure about this
			clone.find("[sort-value]").removeAttr("sort-value");

			jq(this).replaceWith(clone);
		});
	});
	
	jq(document).on("mouseenter mouseleave", ".treeView .treeItem:not([expandable])",function(){
		jq(this).closest("[expandable='semi-expandable']").toggleClass("highlight");
	});
	
});