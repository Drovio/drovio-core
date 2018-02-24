var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Initialize dataGridList
	dataGridList.init();
});

dataGridList = {
	init: function() {
		// Sort on lick
		jq(document).on("click", ".gridList > .dataGridHeader > .dataGridCell", function(){
			var gridList = jq(this).closest(".uiDataGridList");
			dataGridList.sort(gridList, jq(this));
		})
		
		// Check on click
		jq(document).on("click", ".uiDataGridList .dataGridRow", function(ev){
			if (ev.metaKey || ev.ctrlKey || ev.altKey)
				jq(this).closest(".dataGridRow").find(".dataGridCheck input[type='checkbox']").trigger("click");
		});
		
		// Update checks
		jq(document).on("click", ".uiDataGridList .dataGridHeader > .dataGridCheck [type='checkbox']", function() {
			var checked = jq(this).prop("checked");
			var glist = jq(this).closest(".uiDataGridList");
			var rows = glist.find(".dataGridRow");
			var checks = rows.find(".dataGridCheck [type='checkbox']");
			
			checks.prop("checked", checked);
			if (checked)
				rows.addClass("selected");
			else
				rows.removeClass("selected");
			 
			glist.removeData("shift-pivot");
			glist.removeData("lastIndex");
			
			jq(this).trigger("listUpdated.uiDataGridList", rows.filter(".selected").length);
		});
		
		// Update all checked rows
		jq(document).on("change", ".uiDataGridList .dataGridRow > .dataGridCheck [type='checkbox']", function(ev){
			var thisCheck = jq(this);
			var glist = jq(this).closest(".uiDataGridList");
			
			var rows = glist.find(".dataGridRow");
			var checks = rows.find(".dataGridCheck [type='checkbox']");
			var checkAll = jq(".dataGridHeader > .dataGridCheck [type='checkbox']", glist);
			
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
			
			var checkedLength = rows.filter(".selected").length;
			checkAll.prop("checked", false);
			if (checkedLength)
				checkAll.prop("checked", true);
			
			jq(this).trigger("listUpdated.uiDataGridList", checkedLength);
		});
		
		// On form reset
		jq(document).on("reset", "form:has(.uiDataGridList)", function(ev){
			dataGridList.reset(jq(this));
		});
		
		// Re-initialize on content.modified
		jq(document).on("content.modified", function(){
			dataGridList.initialize();
		})
		
		
		// @DEPRECATED
		jq(document).on("addRow", ".uiDataGridList", function(ev, contents){
			dataGridList.addRow(jq(this), contents);
		});
		
		// @DEPRECATED
		jq(document).on("replaceCell", ".uiDataGridList", function(ev, row, column, replacement, callback) {
			// Replace cell
			var oldValue = dataGridList.replaceCell(jq(this), row, column, replacement);
			
			// Callback
			if (jq.type(callback) == "function")
				callback.call(this, oldValue, replacement);
		});
		
		// @DEPRECATED
		jq(document).on("removeSelectedRows", ".uiDataGridList", function(ev, callback) {
			// Remove rows
			var rows = dataGridList.removeSelectedRows(jq(this));
			
			// Callback
			if (jq.type(callback) == "function")
				callback.call(this, rows);
		});
		
		// @DEPRECATED
		jq(document).on("removeRow", ".uiDataGridList", function(ev, identifier) {
			dataGridList.removeRow(jq(this), identifier);
		});
		
		
		// @DEPRECATED
		jq(document).on("getSelectedRows", ".uiDataGridList", function(ev, callback) {
			// Get rows
			var rows = dataGridList.getSelectedRows(jq(this));
			
			// Callback
			if (jq.type(callback) == "function")
				callback.call(this, rows);
		});
	},
	initialize: function() {
		// Get init objects and remove initialize class
		var gLists = jq(".uiDataGridList.initialize").removeClass("initialize");

		gLists.find(".gridList .dataGridRow > .dataGridCheck :checked")
			.closest(".dataGridRow").addClass("selected").closest(".uiDataGridList").find(".dataGridHeader > .dataGridCheck [type='checkbox']").prop("checked", true);
		
		gLists.each(function(){
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
					row = jq(this).find(".gridList .dataGridRow").eq(ri);
				} else {
					row = jq(this).find(".gridList .dataGridRow").filter(function(){
						return jq(this).data("identifier") == ri;
					});
				}
				return row;
			};


		});
	},
	addRow: function(gridList, contents) {
		// Get data grid list
		var jqGridList = jq(gridList);
		
		// Get content and transform if necessary
		var length = 0;
		if (jq.type(contents) == "undefined" || jq.type(contents) == "null" ) {
			length = jqGridList.find(".dataGridHeader > .dataGridCell").length;
			contents = new Array();
			for (var i = 0; i < length; i++)
				contents[i] = "<span class='dataGridTextWrapper' style='max-width:100%;width:100%;box-sizing:border-box;'></span>";
		} else {
			length = contents.length;
			for (var i = 0; i < length; i++)
				if (jq.type(contents[i]) == "string")
					contents[i] = "<span class='dataGridTextWrapper' style='max-width:100%;width:100%;box-sizing:border-box;'>"+jq("<div />").text(contents[i]).html()+"</span>";
		}
		
		var row = jq("<li />").addClass("dataGridRow");
		var ratio = 100;
		if (jqGridList.closest(".uiDataGridList").hasClass("checkable")) {
			ratio = 100 - 8;
			var input = jq("<input />").attr("type", "checkbox").attr("name", "files[]");
			jq("<div />").addClass("dataGridCheck").append(input).prependTo(row);
		}
		
		var cell = jq("<div />").addClass("dataGridCell");
		var width = ratio/length;
		var columnRatios = jqGridList.find(".gridList").data("column-ratios");
		for (var i = 0; i < length; i++) {
			if (jq.type(columnRatios) == "object")
				width = columnRatios[i];
			cell.clone().css("width", width+"%").append(contents[i]).appendTo(row);
		}
		
		jqGridList.find(".dataGridContentWrapper").append(row);
		
		// Focus on first 'name' element of the row
		row.find(".dataGridCell input, .dataGridCell select, .dataGridCell textarea").first().trigger("focus");
	},
	removeRow: function(gridList, identifier) {
		var jqGridList = jq(gridList);
		
		if (jq.type(identifier) == "number") {
			jqGridList.find(".dataGridContentWrapper .dataGridRow").eq(identifier).remove();
		}
		else if (jq.type(identifier) == "string") {
			jqGridList.find(".dataGridContentWrapper .dataGridRow").filter(function(){
				return jq(this).data("identifier") == identifier;
			}).remove();
		}
	},
	replaceCell: function(gridList, row, column, replacement) {
		var jqGridList = jq(gridList);
		
		if (jq.type(replacement) == "string"
			|| (jq.type(replacement.tagName) != "undefined" && replacement.tagName == "SPAN"))
			replacement = "<span class='dataGridTextWrapper' style='max-width:100%;width:100%;box-sizing:border-box;'>"+jq("<div>").text(replacement).html()+"</span>"
		var cell = jqGridList.find(".dataGridRow").eq(row).children(".dataGridCell").eq(column);
		var oldValue = cell.contents().clone(true);
		cell.html(replacement);
		
		return oldValue;
	},
	getSelectedRows: function(gridList) {
		var jqGridList = jq(gridList);
		
		var rows = new Object();
		var selected = jqGridList.find(".dataGridContentWrapper .dataGridRow.selected");
		
		if (selected.length == 0)
			return;
		
		var identifiers = new Array();
		jqGridList.find(".dataGridHeader > .dataGridCell").each(function(index) {
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
		return rows;
	},
	removeSelectedRows: function(gridList) {
		var jqGridList = jq(gridList);
		
		var rows = new Object();
		var removed = jqGridList.find(".dataGridContentWrapper .dataGridRow.selected").remove();
		
		if (removed.length == 0)
			return;
		
		removed.each(function(idx){
			rows[idx] = new Object();
			jq(this).find(".dataGridCell").each(function(index){
				var identifier = jqGridList.find(".dataGridHeader > .dataGridCell").eq(index).data("column-name");
				rows[idx][identifier] = jq(this).text();
			});
		});
		
		return rows;
	},
	sort: function(gridList, headerCell) {
		var jqCell = jq(headerCell);
		
		// Set class
		jq(".gridList > .dataGridHeader > .dataGridCell")
			.not(jqCell).removeClass("selected ascending").end()
			.filter(jqCell).addClass("selected").toggleClass("ascending");
		
		// Sort
		var jqthiscol = jqCell.closest(".gridList").find(".dataGridRow").children(":nth-child("+(jqCell.index()+1)+")");
		var sortByAscOrder = jqCell.hasClass("ascending");
		
		// Create collection to sort
		var collection = new Array();
		jqthiscol.each(function(index){
			collection.push({"index": index, "value": jq(this).text()});
		});
		
		if (collection.filter(function(c){return (!c.value && c.value != 0)}).length != 0)
			return;

		collection.sort(function(a, b){
			a.value = (!isNaN(parseFloat(a.value)) && isFinite(a.value) ? parseFloat(a.value) : a.value );
			b.value = (!isNaN(parseFloat(b.value)) && isFinite(b.value) ? parseFloat(b.value) : b.value );
			if (sortByAscOrder)
				return (a.value < b.value ? -1 : 1);
			else
				return (a.value > b.value ? -1 : 1);
		});

		var jqthisrow = jqCell.closest(".uiDataGridList").find(".dataGridRow");
		var collectionClone = jqthisrow.clone(true, true);
		for (i in collection) {
			jqthisrow.eq(i).replaceWith(collectionClone.eq(collection[i].index));
		}
	},
	reset: function(gridList) {
		var jqGridList = jq(gridList);
		setTimeout(function() {
			jq(".uiDataGridList .dataGridRow > .dataGridCheck [type='checkbox']", jqGridList)
				.filter(":checked").closest(".dataGridRow").addClass("selected").end().end()
				.not(":checked").closest(".dataGridRow.selected").removeClass("selected");
		}, 1);
	},
	clear: function(gridList) {
		// Get jQuery object (if not)
		var jqGridList = jq(gridList);
		
		// Remove all grid list rows
		jqGridList.find(".dataGridContentWrapper .dataGridRow").remove();
	}
}