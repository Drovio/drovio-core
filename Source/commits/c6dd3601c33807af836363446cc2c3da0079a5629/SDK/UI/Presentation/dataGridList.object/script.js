/* 
 * Redback JS Document
 *
 * Title: RedBack JS Library - tabControl
 * Description: --
 * Author: RedBack Developing Team
 * Version: --
 * DateCreated: 21/08/2012
 * DateRevised: --
 *
 */

// jQuery part
// change annotation from "$" to "jq"
var jq=jQuery.noConflict();

// let the document load
jq(document).one("ready.extra", function() {

	jq(document).on("content.modified", function(){
	
		var gLists = jq(".dataGridWrapper.initialize");
		gLists.removeClass("initialize");
	
		gLists.find(".dataGridList .dataGridRow > .dataGridCheck :checked")
			.closest(".dataGridRow").addClass("selected");
		
		gLists.find(".dataGridList").each(function(){
			var jqthis = jq(this);
			jqthis.data("column-ratios", jqthis.data("column-ratios"));
			jqthis.removeAttr("data-column-ratios");
			
			this.getRowCount = function(){
				return jq(this).find(".dataGridContentWrapper .dataGridRow").length;
			};
			this.getSelectedRowCount = function(){
				return jq(this).find(".dataGridContentWrapper .dataGridRow.selected").length;
			};
			this.assignRowIdentifier = function(identifier){
				var rand = "grow_"+Math.floor(Math.random()*10000000000);
				var row;
				if (jq.type(identifier) == "number") {
					row = jq(this).find(".dataGridContentWrapper .dataGridRow").eq(identifier);
				} else {
					row = jqthis.find(identifier).first().closest(".dataGridRow");
				}
				row.data("identifier", rand);
				return rand;
			};
			this.identifyRow = function(identifier){
				return jq(this).find(".dataGridContentWrapper .dataGridRow").filter(function(){
					return jq(this).data("identifier") == identifier;
				}).index();
			};
			
			this.searchRows = function(column, regexp) {
				var jqthis = jq(this);
				var rows = jqthis.find(".dataGridContentWrapper .dataGridRow");
				
				if (rows.length == 0)
					return -1;
				
				var indexes = new Array();
				var index = column;
				var headerCells = jqthis.find(".dataGridHeader > .dataGridCell");
				if (jq.type(column) == "string") {
					var c = headerCells.filter(function() {
						return jq(this).data("column-name") == column;
					});
					index = headerCells.index(c);
				}
				
				if (jq.type(index) != "number" || index < 0)
					return -1;
				
				rows.filter(function() {
					return regexp.test(jq(this).find(".dataGridCell").eq(index).text());
				}).each(function(i){
					indexes[i] = jq(this).index();
				});
				
				return indexes;
			};
			
			this.getRow = function(ri) {
				var row;
				if (jq.type(ri) == "number") {
					row = jq(this).find(".dataGridContentWrapper .dataGridRow").eq(ri);
				} else {
					row = jq(this).find(".dataGridContentWrapper .dataGridRow").filter(function(){
						return jq(this).data("identifier") == ri;
					});
				}
				return row;
			};


		});
	})

	jq(document).on("click", ".dataGridList > .dataGridHeader > .dataGridCell", function(){
		jq(".dataGridList > .dataGridHeader > .dataGridCell")
			.not(jq(this)).removeClass("selected ascending").end()
			.filter(jq(this)).addClass("selected").toggleClass("ascending");
	})
	
	// sort on click
	jq(document).on("click", ".dataGridList > .dataGridHeader > .dataGridCell", function(){
		jqthiscol = jq(this).closest(".dataGridList")
							.find(".dataGridRow")
							.children(":nth-child("+(jq(this).index()+1)+")");
		
		var sortByAscOrder = jq(this).hasClass("ascending");
		
		var collection = new Array();

		jqthiscol.each(function(index){
			collection.push({"index": index, "value": jq(this).text()});
		});
		
		if (collection.filter(function(c){return (!c.value && c.value != 0)}).length != 0)
			return;
		
		collection.sort(function(a, b){
			a.value = (!isNaN(parseFloat(a.value)) && isFinite(a.value) ? parseFloat(a.value) : a.value );
			b.value = (!isNaN(parseFloat(b.value)) && isFinite(b.value) ? parseFloat(b.value) : b.value );
			if(sortByAscOrder)
				return (a.value < b.value ? -1 : 1);
			else
				return (a.value > b.value ? -1 : 1);
		});
		
		var jqthisrow = jq(this).closest(".dataGridList").find(".dataGridRow");
		var collectionClone = jqthisrow.clone(true, true);
		for(i in collection){
			jqthisrow.eq(i).replaceWith(collectionClone.eq(collection[i].index));
		}
	});
	
	// Check on click
/*	jq(document).on("click", ".dataGridList .dataGridRow > .dataGridCell", function(ev){
		if ((ev.ctrlKey || ev.metaKey) && jq(ev.target).attr("type") != "checkbox")
			jq(this).closest(".dataGridRow").find(".dataGridCheck input[type='checkbox']").trigger("click");
	});
*/	
	// Click on a row selection checkbox
	jq(document).on("click", ".dataGridList .dataGridRow > .dataGridCheck [type='checkbox']", function(ev){
		var thisCheck = jq(this);
		var glist = thisCheck.closest(".dataGridList");
		var rows = glist.find(".dataGridRow");
		var checks = rows.find(".dataGridCheck [type='checkbox']");
		
		if (!ev.ctrlKey && (!ev.shiftKey)) {
			//thisRow.toggleClass("selected");
			var idx = thisCheck.closest(".dataGridRow").index();
			glist.data("shift-pivot", idx);
			glist.data("lastIndex", idx);
		} else if (ev.shiftKey) {
			var thisIndex = thisCheck.closest(".dataGridRow").index();
			var pivotIndex = glist.data("shift-pivot");
			var lastIndex = glist.data("lastIndex");
			if (jq.type(pivotIndex) == "undefined") {
				pivotIndex = thisIndex;
				glist.data("shift-pivot", thisIndex);
			}
			if (lastIndex !== undefined)
				rows.slice(Math.min(lastIndex, thisIndex), Math.max(lastIndex, thisIndex)+1).find(".dataGridCheck [type='checkbox']").prop("checked", false);
			rows.slice(Math.min(pivotIndex, thisIndex), Math.max(pivotIndex, thisIndex)+1).find(".dataGridCheck [type='checkbox']").prop("checked", true);
			glist.data("lastIndex", thisIndex);
		} else {
			var idx = thisCheck.closest(".dataGridRow").index();
			checks.prop("checked", false);
			thisCheck.prop("checked", true);
			glist.data("shift-pivot", idx);
			glist.data("lastIndex", idx);
		}
		
		checks.filter(":checked").closest(".dataGridRow").addClass("selected").end().end()
			.not(":checked").closest(".dataGridRow").removeClass("selected");
		
		jq(this).trigger("listUpdated.dataGridList", rows.filter(".selected").length);
	});
	
	// On form reset
	jq(document).on("reset", "form:has(.dataGridList)", function(ev){
		var jqthis = jq(this);
		setTimeout(function() {
			jq(".dataGridList .dataGridRow > .dataGridCheck [type='checkbox']", jqthis)
				.filter(":checked").closest(".dataGridRow").addClass("selected").end().end()
				.not(":checked").closest(".dataGridRow.selected").removeClass("selected");
		}, 1);
	});
	
	jq(document).on("addRow", ".dataGridList", function(ev, contents){
		var contents = Array.prototype.slice.call(arguments, 1);
		
		var length = 0;
		if (jq.type(contents) == "undefined" || jq.type(contents) == "null" ) {
			length = jq(this).find(".dataGridHeader > .dataGridCell").length;
			contents = new Array();
			for (var i = 0; i < length; i++)
				contents[i] = "<span class='dataGridTextWrapper' style='max-width:100%;width:100%;box-sizing:border-box;'></span>";
		} else {
			length = contents.length;
			for (var i = 0; i < length; i++)
				if (jq.type(contents[i]) == "string")
					contents[i] = "<span class='dataGridTextWrapper' style='max-width:100%;width:100%;box-sizing:border-box;'>"+jq("<div>").text(contents[i]).html()+"</span>";
		}
		
		var row = jq("<li class='dataGridRow'></li>");
		var ratio = 100;
		if (jq(this).closest(".dataGridWrapper").hasClass("checkable")) {
			ratio = 100 - 8;
			jq("<div class='dataGridCheck'><input type='checkbox' name='files[]' /><\/div>").prependTo(row);
		}
		
		var cell = jq("<div class='dataGridCell'><\/div>");
		var width = ratio/length;
		var columnRatios = jq(this).data("column-ratios");
		for (var i = 0; i < length; i++) {
			if (jq.type(columnRatios) == "object")
				width = columnRatios[i];
			cell.clone().css("width", width+"%").append(contents[i]).appendTo(row);
		}
		
		jq(this).find(".dataGridContentWrapper").prepend(row);
		
		// Focus on first 'name' element of the row
		row.find(".dataGridCell input, .dataGridCell select, .dataGridCell textarea").first().trigger("focus");
	});
	
	jq(document).on("replaceCell", ".dataGridList", function(ev, row, column, replacement, callback) {
		if (jq.type(replacement) == "string"
			|| (jq.type(replacement.tagName) != "undefined" && replacement.tagName == "SPAN"))
			replacement = "<span class='dataGridTextWrapper' style='max-width:100%;width:100%;box-sizing:border-box;'>"+jq("<div>").text(replacement).html()+"</span>"
		var cell = jq(this).find(".dataGridRow").eq(row).children(".dataGridCell").eq(column);
		var oldValue = cell.contents().clone(true);
		cell.html(replacement);
		
		if (jq.type(callback) == "function")
			callback.call(this, oldValue, replacement);
	});
	
	jq(document).on("removeSelectedRows", ".dataGridList", function(ev, callback) {
		var rows = new Object();
		var jqthis = jq(this);
		var removed = jqthis.find(".dataGridContentWrapper .dataGridRow.selected").remove();
		
		if (removed.length == 0)
			return;
		
		removed.each(function(idx){
			rows[idx] = new Object();
			jq(this).find(".dataGridCell").each(function(index){
				var identifier = jqthis.find(".dataGridHeader > .dataGridCell").eq(index).data("column-name");
				rows[idx][identifier] = jq(this).text();
			});
		});
		
		if (jq.type(callback) == "function")
			callback.call(this, rows);
	});
	
	jq(document).on("removeRow", ".dataGridList", function(ev, identifier) {
		if (jq.type(identifier) == "number") {
			jq(this).find(".dataGridContentWrapper .dataGridRow").eq(identifier).remove();
		}
		else if (jq.type(identifier) == "string") {
			jq(this).find(".dataGridContentWrapper .dataGridRow").filter(function(){
				return jq(this).data("identifier") == identifier;
			}).remove();
		}
	});
	
	jq(document).on("getSelectedRows", ".dataGridList", function(ev, callback) {
		var rows = new Object();
		var jqthis = jq(this);
		var selected = jqthis.find(".dataGridContentWrapper .dataGridRow.selected");
		
		if (selected.length == 0)
			return;
		
		var identifiers = new Array();
		jqthis.find(".dataGridHeader > .dataGridCell").each(function(index) {
			identifiers[index] = jq(this).data("column-name");
		});
		
		selected.each(function(idx) {	
			rows[idx] = new Object();
			jq(this).find(".dataGridCell").each(function(index){
				rows[idx][identifiers[index]] = jq(this).text();
			});
			rows[idx]['__index'] = jq(this).index();
		});
		
		selected = selected.clone();
		if (jq.type(callback) == "function")
			callback.call(this, rows);
	});
	
	
	/*
	jq(document).on("deactivateRow", ".dataGridList", function(ev, index) {
		jq(this).find(".dataGridContentWrapper .dataGridRow").eq(index).addClass("inactive");
	});
	
	jq(document).on("activateRow", ".dataGridList", function(ev, index) {
		jq(this).find(".dataGridContentWrapper .dataGridRow").eq(index).removeClass("inactive");
	});
	*/
	
});