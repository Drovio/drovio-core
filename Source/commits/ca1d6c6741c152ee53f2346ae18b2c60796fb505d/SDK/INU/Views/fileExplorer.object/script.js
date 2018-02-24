var jq = jQuery.noConflict();

jq(document).one("ready.extra", function(){
	
	// Check for the various File API support.
	if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
		console.error('The File API is not fully supported in this browser!');
		return false;
	}
	
	// On content modified, find uninitialized fileExplorers and initialize them
	jq(document).on("content.modified", function(){
		jq(".fileExplorer").filter(function(){
			return jq.type(jq(this).data("initiated")) == "undefined";
		}).data("initiated", true).each(function(){
			var jqthis = jq(this);
			jqthis.data("path", jqthis.data("path"));
			jqthis.removeAttr("data-path");
			jqthis.fileExplorer();
		});
		
		//jq(".fileExplorer").find("[data-file-type]");
	});
	
	// This wraps the fileExplorer extension
	(function(jq){
	
		// Method Logic for fileExplorer
		var methods = {
			init : function(){				
				return this.each(function(){
					fileExplorer.call(this);
				});		
			}
		}
		
		jq.fn.fileExplorer = function(/*method*/) {
			return methods.init.apply(this);
		};
		// ---------------------------
		
		// Check every quarter
		setInterval(keepAlive, 900000);
		
		// Initialization of fileExplorer (assign listeners etc...)
		function fileExplorer() {
		
			var fileExplorer = jq(this);
			var toolbar = fileExplorer.children(".toolbar");
			var pathbar = fileExplorer.children(".pathbar");
			var fileViewer = fileExplorer.children(".fileViewer");
			var folderTreeView = fileExplorer.children(".folderView");
			var messageArea = fileExplorer.children(".messageArea");
			var preventDelete = messageArea.children(".preventDeleteWrapper");
			var preferencesArea = fileExplorer.children(".preferencesArea").detach();
			var notificationsArea = fileExplorer.children(".notificationsArea");
			
			
			var fileUpload = toolbar.find("input[type='file']").first();
			
			fileViewer.on("dragenter", function(ev){
				ev.stopPropagation();
				ev.preventDefault();
				ev.originalEvent.dataTransfer.dropEffect = 'copy';
			});
			
			fileViewer.on("dragover", function(ev){
				ev.stopPropagation();
				ev.preventDefault();
				ev.originalEvent.dataTransfer.dropEffect = 'copy';
			});
			
			fileViewer.on("dragleave", function(ev){
				ev.stopPropagation();
				ev.preventDefault();
				ev.originalEvent.dataTransfer.dropEffect = 'none';
			});
					
			// Load files
			fileUpload.on("change", function(ev){
				var files = this.files;
				fileExplorer.data("local-files", files);
				filterFiles(files);
				uploadFiles.call(fileViewer, files, getSubPath.call(pathbar));
				//jq(this).val('');
			});
			
			// Drop files
			fileViewer.on("drop", function(ev){
				ev.stopPropagation();
				ev.preventDefault();
				var files = ev.originalEvent.dataTransfer.files;
				fileExplorer.data("local-files", files);
				filterFiles(files);
				uploadFiles.call(fileViewer, files, getSubPath.call(pathbar));
			});
			
			fileViewer.on("click", ".folderName:not(.loadingDirectory)", function(ev) {
				var previousRequest = fileViewer.data("contentsRequest");
				if (jq.type(previousRequest) != "undefined")
					previousRequest.abort();
				
				toolbar.trigger("init");
				fileViewer.find(".folderName").addClass("loadingDirectory");
				pathbar.append("<span class='pathRelation'>&gt;</span>").append("<span class='pathElement'>"+jq(this).text()+"</span>");
				pathbar.trigger("pathChanged");
				
				requestContents.call(fileViewer, getSubPath.call(pathbar));
			});
			
			pathbar.on("click", ".pathElement", function(ev) {
				var previousRequest = fileViewer.data("contentsRequest");
				if (jq.type(previousRequest) != "undefined")
					previousRequest.abort();
				
				toolbar.trigger("init");
				var jqthis = jq(this);
				var changed = jqthis.is(":last-child");
				jqthis.siblings().slice(jqthis.index()).remove();
				
				if (changed)
					jqthis.trigger("pathChanged");
				requestContents.call(fileViewer, getSubPath.call(pathbar));
			});
			
			pathbar.on("click", ".hiddenPath", function(ev) {
				// Popup with pathElements
				var hElements = jq(this).siblings(".pathElement").clone(true);
				hElements.removeClass("noDisplay");
				
				hElements.on("click", function(){
					var idx = jq(this).index();
					pathbar.find(".hiddenPath").remove();
					pathbar.children().removeClass("noDisplay");
					pathbar.children(".pathElement").eq(idx).trigger("click");
				});
				
				jq.fn.popup.binding = "on";
				jq.fn.popup.type = "obedient";
				jq.fn.popup.position = "bottom|left";
				jq(this).popup(jq("<div class='hiddenPathPopup'></div>").append(hElements), fileExplorer);
			});
			
			fileExplorer.on("pathChanged", function(){
				// Render path
				renderPath.call(jq(this).find(".pathbar").first().get(0), jq(this).find(".toolbar").first());
			});
			
			fileExplorer.on("listUpdated.uiDataGridList", function(ev, selected){
				if (selected == 0)
					toolbar.trigger("init");
				else
					toolbar.children("button").prop("disabled", false);
			});
			
			toolbar.on("init", function(){
				jq(this).children("[name='delete'], [name='download'], [name='copy'], [name='move'], [name='rename']").prop("disabled", true);
			});
			// Buttons
			// New folder 
			toolbar.on("click", "[name='newFolder']", function(ev) {
				if (fileViewer.find(".uiDataGridList").length == 0)
					return;
			
				var inputDiv = jq("<input placeholder='New Folder' />");
				
				inputDiv.one("focusout", function() {
					var folderName = jq.trim(jq(this).val());
					if (folderName == "") {
						var thisList = jq(this).closest(".uiDataGridList");
						
						thisList.trigger("removeRow", 0);
						var count = thisList.get(0).getRowCount();
						if (count == 0)
							fileViewer.find(".stateViewer").filter(function(){
								return jq(this).data("state") == "empty";
							}).removeClass("noDisplay").end().end()
								.find(".fileViewerWrapper").addClass("noDisplay");
						return;
					}
					
					var newElem = jq("<span></span>").text(folderName);
					var cont = jq("<div><div class='previewWrapper folderIcon'></div></div>");
					cont.append(newElem);
					
					var rowIdentifier = jq(this).closest(".uiDataGridList").trigger("replaceCell", [0, 0, cont]).get(0).assignRowIdentifier(0);
					
					// Create folder on server
					createFolder.call(fileViewer, getSubPath.call(pathbar), folderName, rowIdentifier);
				});
				
				inputDiv.on("keydown", function(ev){ 
					// Enter && Escape
					if (ev.which != 13 && ev.which != 27)
						return;
					// Escape
					if (ev.which == 27)
						jq(this).val("");
						
					jq(this).trigger("focusout");
				});
				
				var container = jq("<div><div class='previewWrapper folderIcon'></div></div>");
				container.append(inputDiv);
				
				fileViewer.find(".stateViewer").filter(function(){
					return jq(this).data("state") == "empty";
				}).addClass("noDisplay").end().end()
					.find(".fileViewerWrapper").removeClass("noDisplay")
					.find(".uiDataGridList").trigger("addRow", [container, "", "folder", ""]);
			});
			// Rename
			toolbar.on("click", "[name='rename']", function(ev) {
				var dtGridList = fileViewer.find(".uiDataGridList");
				if (dtGridList.length == 0)
					return;
			
				dtGridList.trigger("getSelectedRows", function(rows) {
					var firstRow = rows[0];
					
					var inputDiv = jq("<input placeholder='New Name' value='"+firstRow.name+"' />").data("oldValue", firstRow.name);
					
					inputDiv.one("focusout", function() {
						var newName = jq.trim(jq(this).val());
						var oldName = jq.trim(jq(this).data("oldValue"));
						var thisList = jq(this).closest(".uiDataGridList");
						var newElem;
						if (newName == "" || oldName == newName) {
							newElem = thisList.get(0).getRow(firstRow['__index']).data("oldRenameValue");
							thisList.trigger("replaceCell", [firstRow['__index'], 0, newElem]);
							return;
						}
						
						newElem = jq("<span></span>").text(newName);
						thisList.trigger("replaceCell", [firstRow['__index'], 0, newElem, function(oldVal, newVal){
							var icon = oldVal.find(".previewWrapper").add(oldVal.filter(".previewWrapper")).first();
							newVal.before(icon);
						}]);
						var rowIdentifier = thisList.get(0).assignRowIdentifier(firstRow['__index']);
						
						// Rename file on server
						renameFile.call(fileViewer, getSubPath.call(pathbar), oldName, newName, rowIdentifier);
					});
					
					inputDiv.on("keydown", function(ev){ 
						// Enter && Escape
						if (ev.which != 13 && ev.which != 27)
							return;
						var jqthis = jq(this);
						// Escape
						if (ev.which == 27)
							jqthis.val(jqthis.data("oldValue"));
							
						jqthis.trigger("focusout");
					});
					
					dtGridList.trigger("replaceCell", [firstRow['__index'], 0, inputDiv, function(oldVal, newVal){
						var icon = oldVal.find(".previewWrapper").add(oldVal.filter(".previewWrapper")).first().clone();
						dtGridList.get(0).getRow(firstRow['__index']).data("oldRenameValue", oldVal);
						newVal.before(icon);
						inputDiv.focus();
						inputDiv.select();
					}]);
				});
			});
			// Download
			toolbar.on("click", "[name='download']", function(ev) {
				var glist = fileViewer.find(".uiDataGridList");
				if (glist.length == 0)
					return;
					
				if (glist.get(0).getSelectedRowCount() == 0)
					return;
					
				glist.trigger("getSelectedRows", function(rows) {
					// Download files from server
					var fileNames = new Array();
					for (var r in rows)
						fileNames[r] = rows[r].name;
					
					downloadFiles.call(fileViewer, getSubPath.call(pathbar), fileNames);
				});
			});
			
			// Move / Copy
			toolbar.on("click", "[name='move'], [name='copy']", function(ev) {
				var glist = fileViewer.find(".uiDataGridList");
				if (glist.length == 0 || glist.get(0).getSelectedRowCount() == 0)
					return;
				
				var jqthis = jq(this);
				var name = jqthis.attr("name");
				var cp = (name == "copy" ? true : false);
				//Ajax to get fView
				folderTreeView.data("copy", cp);
				
				var content = "";				
				jq.fn.popup.type = "obedient";
				jq.fn.popup.withBackground = true;
				jq.fn.popup.position = {
					"top" : "center",
					"left" : "center",
					"position" : "absolute"
				};
				jqthis.popup(content, fileExplorer);
				
				getFolderView.call(folderTreeView, pathbar.children(".pathElement").first().text(), getSubPath.call(pathbar));
			});
			
			// Upload
			toolbar.on("click", "[name='upload']", function(ev) {
				fileUpload.trigger("click");
			});
			
			toolbar.on("click", ".prefIcon", function(ev) {
				var wrapper = preferencesArea.clone(true);
				var jqthis = jq(this);
				
				jq.fn.popup.type = "obedient toggle";
				jq.fn.popup.position = "bottom|right";
				jq.fn.popup.alignOffset = -10;
				jq.fn.popup.invertDock = "horizontal";
				jq(this).popup(wrapper, fileExplorer);
			});
			
			toolbar.on("click", ".viewIcon", function(ev) {
				if (jq(this).hasClass("gridIcon")) {
					jq(this).removeClass("gridIcon").addClass("thumbIcon");
					return;
				}
				
				jq(this).removeClass("thumbIcon").addClass("gridIcon");
			});
			
			fileExplorer.on("click", ".folderViewWrapper .treeView .treeItem", function(ev){
				var jqthis = jq(this);
				var folderView = jqthis.closest(".folderViewWrapper");
				if (jq(ev.target).closest(".treeItemContent").length == 0)
					return;
				
				folderView.find("[name='confirm']").prop("disabled", false);
				folderView.find(".treeView .treeItem.selected").not(this).removeClass("selected");
				jq(ev.target).closest(".treeView .treeItem").addClass("selected");
			});
			
			fileExplorer.on("click", ".folderViewWrapper [name='confirm']", function(ev){
				var glist = fileViewer.find(".uiDataGridList");
				if (glist.length == 0 || glist.get(0).getSelectedRowCount() == 0)
					return;
				
				var jqthis = jq(this);
				var folderView = jqthis.closest(".folderViewWrapper");
				glist.trigger("getSelectedRows", function(rows) {
					// Move files
					var fileNames = new Array();
					for (var r in rows)
						fileNames[r] = rows[r].name;
					
					var cp = folderTreeView.data("copy");
					var destSubPath = folderView.find(".selected").attr("subPath");
					
					jqthis.trigger("dispose.popup");
					moveFiles.call(fileViewer, getSubPath.call(pathbar), fileNames, destSubPath, cp);
				});
			});
			
			fileExplorer.on("click", ".folderViewWrapper [name='cancel']", function(){
				jq(this).trigger("dispose.popup");
			});
			
			// Delete
			toolbar.on("click", "[name='delete']", function(ev) {
				var glist = fileViewer.find(".uiDataGridList");
				if (glist.length == 0 || glist.get(0).getSelectedRowCount() == 0)
					return; 
				
				jq.fn.popup.type = "obedient";
				jq.fn.popup.withBackground = true;
				jq.fn.popup.position = jq.fn.popup.position = {
					"top" : "center",
					"left" : "center",
					"position" : "absolute"
				};
				jq(this).popup(preventDelete.clone(true), fileExplorer);
			});
			
			preventDelete.on("click", "[name='confirm']", function(ev) {
				var jqthis = jq(this);
				var glist = fileViewer.find(".uiDataGridList");
				if (glist.length == 0)
					return;
					
				if (glist.get(0).getSelectedRowCount() == 0)
					return;
				
				// Need verification popup here
				// The following will be its callback in case of "OK"				
				glist.trigger("removeSelectedRows", function(rows) {
					var thisList = jq(this);
					var count = thisList.get(0).getRowCount();
					
					if (count == 0)
						fileViewer.find(".stateViewer").filter(function(){
							return jq(this).data("state") == "empty";
						}).removeClass("noDisplay").end().end()
							.find(".fileViewerWrapper").addClass("noDisplay");
				
					// Delete file from server
					var fileNames = new Array();
					for (var row in rows) {
						fileNames[row] = rows[row].name;
					}
					
					jqthis.trigger("dispose.popup");
					dropFile.call(fileViewer, getSubPath.call(pathbar), fileNames, rows);
				});
			});
			
			preventDelete.on("click", "[name='cancel']", function(ev) {
				jq(this).trigger("dispose.popup");
			});
			
			preferencesArea.on("click", "#fe_hiddenFilesSwitch", function(){
				// hidden + state
				var jqthis = jq(this);
				var prefs = jqthis.closest(".preferencesArea");
				var pb = prefs.siblings(".pathbar");
				var fv = prefs.siblings(".fileViewer");
				//requestContents.call(fv, getSubPath.call(pb));
			});
			 
			fileViewer.on("click", "[href^='preview.php?']", function(ev){
				ev.preventDefault();
				
				var fileName = jq(this).text();
				previewFile.call(fileViewer, getSubPath.call(pathbar), fileName);
			});
		
			// Fetch needed icons
			jq(document).on("content.modified", function(){
				var activeIcons = iconsToFetch(jq(".fileExplorer"));
				if (activeIcons.length == 0)
					return;
	
				var subPath = getSubPath.call(pathbar);
				previewIcons(subPath, activeIcons);
			});
			
			fileExplorer.on("scroll wheel mousewheel", ".gridList .dataGridContentWrapper", function(){
				var activeIcons = iconsToFetch(jq(this).closest(".fileExplorer"));
				if (activeIcons.length == 0)
					return;
	
				var subPath = getSubPath.call(pathbar);
				previewIcons(subPath, activeIcons);
			});
		}
		
		function iconsToFetch(fe) {
			//setTimeout(function(){
			var gl = fe.find(".gridList");
			if (gl.length == 0)
				return jq();
			var glst = gl.offset().top;
			var border = glst + gl.height();
			//console.log(border);
			var activeicons = fe.find(".previewWrapper.initialize").filter(function(){
				//console.log(jq(this).offset().top);
				return jq(this).offset().top - 100 < border;
			}).removeClass("initialize");
			
			//console.log(activeicons);
			return activeicons;
			//}, 10);
		}
		
		function previewIcons(subPath, activeIcons){
			activeIcons.each(function(){
				var jqthis = jq(this);
				var data = jq("<input name='subPath' />").val(subPath+"/")
					.add(jq("<input name='fexId' />").val(jqthis.closest(".fileExplorer").attr("id")))
					.add(jq("<input name='fn' />").val(jqthis.parent().text()))
					.add(jq("<input name='mode' value='icon'>"))
					.serialize();
	
				//console.log(data);
				ascop.asyncRequest(
					"/ajax/resources/sdk/fileExplorer/previewFile.php", 
					"GET", 
					data, 
					"json",
					jqthis,
					function (response) {
						var jqwrapper = jq(response.htmlContent);
						
						jqwrapper.find("img").on("load", function(ev){
						    jq(this).addClass("reveal");
						});
						
						jqthis.replaceWith(jqwrapper);
					},
					null,
					true,
					true
				);
				
			});
		}
		
		// Called on pathbar
		function renderPath(tb) {
			var pb = jq(this);
			
			var children = pb.children();
			
			var last = pb.find(".pathElement").last();
			
			var tbLeft = jq(document).width() - tb.outerWidth();//tb.scrollLeft();
			//var tbRight = tbLeft + tb.outerWidth();
			
			var elemLeft = last.position().left;
			var elemRight = elemLeft + last.outerWidth();
			
			if (elemRight >= tbLeft) {
				// Collapse path
				var lastIndex = last.index();
				children.slice(2, lastIndex - 1).addClass("noDisplay");
				children.eq(1).after(jq("<span>...</span>").addClass("hiddenPath"));
			}
		}
		
		// Called on pathbar to get the subpath
		function getSubPath() {
			return jq(this).children().not(".hiddenPath").slice(2).text().replace(/\>/g, "::");
		}
		
		// Called on fileviewer
		function filterFiles(files) {
			// Filter unwanted types out ...
			// Print files
			/*console.group("files ->");
			for (var i = 0; i < files.length; i++) {
				console.log(files[i]);
			}
			console.groupEnd();*/
		}
		
		// Called on fileviewer
		function previewFile(subpath, fileName) {
			var data = jq("<input name='subPath' />").val(subpath+"/")
					.add(jq("<input name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")))
					.add(jq("<input name='fn' />").val(fileName))
					.serialize();
			var fviewer = jq(this);
			//console.log(data);
			ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/previewFile.php", 
				"GET", 
				data, 
				"json",
				fviewer,
				function (response) {
					showPreview.call(this, response);
				},
				null,
				false,
				true
			);
		}
		
		function showPreview(jsonResponse) {
			var jqelem = jq(jsonResponse.htmlContent);
			
			jq.fn.popup.binding = "on";
			jq.fn.popup.type = "obedient";
			jq.fn.popup.withFade = false;
			jq.fn.popup.withBackground = true;
			jq.fn.popup.position = {
				"top" : "user",
				"left" : "center",
				"position" : "absolute"
			};
			jq(document).popup(jqelem);
		}
		
		// Called on fileviewer
		function uploadFiles(files, subpath) {
			var fv = this;
			
			fv.find(".stateViewer").filter(function(){
					return jq(this).data("state") == "empty";
				}).addClass("noDisplay").end().end()
					.find(".fileViewerWrapper").removeClass("noDisplay");
					
			for (var f = 0; f < files.length; f++) {
				//if (files[f].size > 2000000)
				//	continue;
				
				var fd = new FormData();
				// Append files for post
				fd.append("fileInfo", files[f]);
				// Append subpath and identifier for post
				fd.append("subPath", subpath+"/");
				fd.append("fexId", jq(this).closest(".fileExplorer").attr("id"));
				
				var sameFileNameIndex = jq(this).find(".uiDataGridList").get(0).searchRows("name", new RegExp(files[f].name));
				
				var nameColumn = jq("<div class='uploadProgress'></div>");
				var progressBar = jq("<span class='progressBar'></span>");
				nameColumn.append(progressBar);
				nameColumn.append(jq("<div class='previewWrapper uploadingIcon uploadStatus'></div>"));
				nameColumn.append(jq("<span>"+files[f].name+"</span>"));
				var gList = jq(this).find(".uiDataGridList");
				
				var rowIdentifier = "";
				if (sameFileNameIndex.length > 0 && sameFileNameIndex[0] >= 0) {
					rowIdentifier = gList.get(0).assignRowIdentifier(sameFileNameIndex[0]);
					gList.trigger("replaceCell", [sameFileNameIndex, 0, nameColumn.get(0)]);
				} else {
					rowIdentifier = gList.trigger("addRow", [nameColumn.get(0), files[f].size+" B", files[f].type, files[f].lastModifiedDate]).get(0).assignRowIdentifier(0);
				}
				
				progressBar.data("assignedRow", rowIdentifier);
				var progress = progressBar;
				
				ascop.request(
					"/ajax/resources/sdk/fileExplorer/putFiles.php", 
					"POST", 
					fd,
					fv,
					function (resp, status, xhr) {
						uploadSuccess(resp, status, xhr);
					},
					{ cache: false, contentType: false, processData: false, withCredentials: true, 
						xhr: function(){
							myXhr = jq.ajaxSettings.xhr();
					        	if (myXhr.upload) {
								myXhr.upload.progressElement = progress;
								myXhr.upload.addEventListener('progress', monitorUploadProgress, false);
					        	}
					        	return myXhr;
						},
						error: function(jqXHR, textStatus, errorThrown){
							uploadError(jqXHR, textStatus, errorThrown);
						}
					}
				);
			}
		}
		
		function uploadError(jqXHR, textStatus, errorThrown){
			jqXHR.abort();
			var jqfv = jq(this);
			
			var completedBar = jqfv.find(".progressBar");
			
			// Remove row
			completedBar.addClass("invalid").each(function(){
				var jqthis = jq(this);
				var ri = jqthis.data("assignedRow");
				var row = jqthis.closest(".uiDataGridList").get(0).getRow(ri);
				jqthis.siblings(".uploadingIcon").removeClass("uploadingIcon uploadStatus").addClass("uploadError");
				
				var content = jqthis.closest(".fileViewer").siblings(".notificationsArea").children(":has(.failedConn)").clone();
				
				jq.fn.popup.binding = "on";
				jq.fn.popup.type = "obedient";
				jq.fn.popup.withFade = true;
				jq.fn.popup.position = {
					"right" : 0,
					"top" : 0,
					"bottom" : 0,
					"position" : "absolute"
				};
				jqthis.closest(".fileViewer").siblings(".toolbar").children("[name='upload']").popup(content, row);
				
				setTimeout(function(){
					// Row Identifier
					jqfv.find(".uiDataGridList").trigger("removeRow", ri);
				}, 3500);
			});
		}
		
		function uploadSuccess(resp, status, xhr) {
			var jqfv = jq(this);
						
			var completedBar = jqfv.find(".progressBar").filter(function(){
				return jq(this).data("uploadStatus") == "complete";
			});
			
			
			// Bad response
			if (jq.type(resp) == "undefined" || jq.type(resp) == "null" || resp.status !== true) {
				// Remove row
				completedBar.addClass("invalid").each(function(){
					var jqthis = jq(this);
					var ri = jqthis.data("assignedRow");
					var row = jqthis.closest(".uiDataGridList").get(0).getRow(ri);
					jqthis.siblings(".uploadingIcon").removeClass("uploadingIcon uploadStatus").addClass("uploadError");
					
					var notificationsArea = jqthis.closest(".fileViewer").siblings(".notificationsArea");
					var content = notificationsArea.children(":has(.unkFailedUpload)").clone();
					if (jq.type(resp.info) != "undefined" 
						&& jq.type(resp.upload) != "undefined")
						content = notificationsArea.children(":has(.failedUpload)").clone().find(".uploadedFile").text(resp.info.name).end().find(".uploadStatus").text(resp.upload.status).end();
					
					jq.fn.popup.binding = "on";
					jq.fn.popup.type = "obedient";
					jq.fn.popup.withFade = true;
					jq.fn.popup.position = {
						"right" : 0,
						"top" : 0,
						"bottom" : 0,
						"position" : "absolute"
					};
					jqthis.closest(".fileViewer").siblings(".toolbar").children("[name='upload']").popup(content, row);
					
					setTimeout(function(){
						// Row Identifier
						jqfv.find(".uiDataGridList").trigger("removeRow", ri);
					}, 3500);
				});
			}
			else {
				//console.log("good");
				// Remove completed progresses if response is good
				completedBar.each(function(){
					var jqthis = jq(this);
					var row = jqthis.closest(".dataGridRow");
					var ri = jqthis.data("assignedRow");
					jqthis.animate({
						'opacity' : 0
					}, 500, function(){
						var newElem = jq("<div class='previewWrapper uploadSuccess'></div><a href='preview.php?ri="+encodeURIComponent(jqfv.closest(".fileExplorer").attr("id"))+"&sp="+encodeURIComponent(resp.info.subpath)+"&fn="+encodeURIComponent(resp.info.name)+"' target='_blank'><span class='fileName'>"+resp.info.name+"</span></a>");
						var gList = jqfv.find(".uiDataGridList");
						var index = gList.get(0).identifyRow(ri);
						gList.trigger("replaceCell", [index, 0, newElem]);
					});
				});
			}
		}
		
		
		// Called on myXhr.upload
		function monitorUploadProgress(ev) {
			if (!ev.lengthComputable)
				return;

			//this.progressElement.attr("max", ev.total);
			//this.progressElement.val(ev.loaded);
			this.progressElement.animate({
				'width' : (ev.loaded/ev.total)*100+"%"
			}, 100);
			//this.progressElement.css("width", (ev.loaded/ev.total)*100+"%");
			if (ev.loaded >= ev.total)
				this.progressElement.data("uploadStatus", "complete");
		}
		
		// Called on fileviewer
		function requestContents(subpath) {	
			var data = jq("<input type='hidden' name='subPath' />").val(subpath+"/")
					.add(jq("<input type='hidden' name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")))
					.serialize();
			
			var fviewer = jq(this);
			var jqxhr = ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/getDirectoryDetails.php",
				"GET", 
				data, 
				"html",
				fviewer,
				showContents,
				function(xhr) {
					jq(this).find(".folderName.loadingDirectory").removeClass("loadingDirectory");
					fviewer.removeData("contentsRequest");
				},
				false,
				true
			);
			
			fviewer.data("contentsRequest", jqxhr);
		}
		
		// Called on fileviewer
		function showContents(response) {
			// Check if responce is OK
			var fviewer = jq(this);
			/*
			if (respose) {
				console.log("invalid path");
				fviewer.addClass("invalidPath");
			}else{
				fviewer.removeClass("invalidPath");
			}
			*/
			// Do your thing
			fviewer.html(response);
			
			var invalidViewer = fviewer.find(".stateViewer").filter(function(){
				return jq(this).data("state") == "invalid_root";
			});
			
			if (invalidViewer.length > 0) {
				var fExplorer = fviewer.closest(".fileExplorer");
				var tbar = fExplorer.children(".toolbar");
				fExplorer.data("toolbar", tbar.detach());
				fExplorer.children(".pathbar").css("width", "100%");
			} else {
				var fExplorer = fviewer.closest(".fileExplorer");
				var tbar = fExplorer.children(".toolbar");
				fExplorer.children(".pathbar").css("width", "");
				if (tbar.length == 0)
					fExplorer.prepend(fExplorer.data("toolbar"));
			}
			
			jq(document).trigger("content.modified");
		}
		
		// Called on fileviewer
		function createFolder(subpath, folderName, rowIdentifier) {	
			var data = jq("<input name='subPath' />").val(subpath+"/")
					.add(jq("<input name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")))
					.add(jq("<input name='fName' />").val(folderName))
					.serialize();
			var fviewer = jq(this);
			
			ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/createDirectory.php", 
				"POST", 
				data, 
				"json",
				fviewer,
				function (response) {
					folderCheck.call(this, response, folderName, rowIdentifier);
				},
				null,
				false,
				true
			);
		}
		
		// Called on fileviewer
		function folderCheck(response, folderName, rowIdentifier) {
			// Check if responce is OK
			// response is a json object... It's status property holds the status of the folder creation
			//console.log(response);
			//console.log("1");
			var fviewer = jq(this);
			var notificationsArea = fviewer.siblings(".notificationsArea");
			// Folder was not created!;
			var content;
			if (response.status !== true && jq.type(rowIdentifier) == "string"){
				content = notificationsArea.children(":has(.folderFail)").clone().find(".errorCode").text(response.status).end();
								
				jq.fn.popup.binding = "on";
				jq.fn.popup.type = "obedient";
				jq.fn.popup.position = {
					"top" : 0,
					"left" : "center",
					"position" : "absolute"
				};
				fviewer.siblings(".toolbar").children("[name='newFolder']").popup(content, fviewer.closest(".fileExplorer"));
				
				fviewer.find(".uiDataGridList").trigger("removeRow", rowIdentifier)
			}else {
				var newElem = jq("<div></div>");
				newElem.append(jq("<div class='previewWrapper folderIcon'></div>"));
				newElem.append(jq("<span class='folderName'></span>").text(folderName));
				var gList = fviewer.find(".uiDataGridList");
				var index = gList.get(0).identifyRow(rowIdentifier);
				gList.trigger("replaceCell", [index, 0, newElem]);
			}
				
			// Do your thing
		}
		
		// Called on fileviewer
		function renameFile(subpath, oldName, newName, rowIdentifier) {	
			var data = jq("<input name='subPath' />").val(subpath+"/")
					.add(jq("<input name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")))
					.add(jq("<input name='oName' />").val(oldName))
					.add(jq("<input name='nName' />").val(newName))
					.serialize();
			var fviewer = jq(this);
			
			ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/renameFile.php", 
				"POST", 
				data, 
				"json",
				fviewer,
				function (response) {
					renameCheck.call(this, response, oldName, rowIdentifier);
				},
				null,
				false,
				true
			);
		}
		
		// Called on fileviewer
		function renameCheck(response, oldName, rowIdentifier) {
			// Check if responce is OK
			// response is a json object... It's status property holds the status of the folder creation
			//console.dir(response);
			var fviewer = jq(this);
			var notificationsArea = fviewer.siblings(".notificationsArea");
			// Folder was not created!;
			var content;
			if (response.status !== true && jq.type(rowIdentifier) == "string") { 
				content = notificationsArea.children(":has(.renameFail)").clone().find(".errorCode").text(response.status).end();
								
				jq.fn.popup.binding = "on";
				jq.fn.popup.type = "obedient";
				jq.fn.popup.position = {
					"top" : 0,
					"left" : "center",
					"position" : "absolute"
				};
				fviewer.siblings(".toolbar").children("[name='rename']").popup(content, fviewer.closest(".fileExplorer"));
				
				var gList = fviewer.find(".uiDataGridList");
				var newElem = gList.get(0).getRow(rowIdentifier).data("oldRenameValue");
				var index = gList.get(0).identifyRow(rowIdentifier); 
				gList.trigger("replaceCell", [index, 0, newElem]);
			}else{
				var gList = fviewer.find(".uiDataGridList");
				var newElem = gList.get(0).getRow(rowIdentifier).data("oldRenameValue");
				var link = newElem.find("a").first();
				if (link.length != 0){
					var hr = link.attr("href");
					hr = hr.substring(0, hr.lastIndexOf("&fn=")+4)+encodeURIComponent(response.info.newName);
					link.attr("href", hr);
				}
				newElem.find("span").first().text(response.info.newName);
				var index = gList.get(0).identifyRow(rowIdentifier); 
				gList.trigger("replaceCell", [index, 0, newElem]);
			}
		}
		
		// Called on fileviewer
		function dropFile(subpath, fileNames, rows) {
			var elements = jq("<input name='subPath' />").val(subpath+"/")
					.add(jq("<input name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")));
			for (var i = 0; i < fileNames.length; i++)
				elements = elements.add(jq("<input name='fNames[]' />").val(fileNames[i]))
			
			var data = elements.serialize();
			var fviewer = jq(this);
			
			ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/dropFiles.php", 
				"POST", 
				data, 
				"json",
				fviewer,
				function (response) {
					dropCheck.call(this, response, rows);
				},
				null,
				false,
				true
			);
		}
		
		// Called on fileviewer
		function dropCheck(response, rows) {
			// Check if responce is OK
			// response is a json object... It's status property holds the status of the file drop
			//console.dir(response);
			//console.log(response.status);
			var statuses = response.status;
			var fviewer = jq(this);
			var notificationsArea = fviewer.siblings(".notificationsArea");
			var stateViewer = fviewer.find(".stateViewer").filter(function(){
				return jq(this).data("state") == "empty";
			});
			
			var icon = jq("<div class='previewWrapper brokenIcon'></div><span><span>");
			var content;
			if (jq.type(statuses) == "undefined" || jq.type(statuses) == "null")
				statuses = "";
			// Reappend rows if they were not deleted on server 
			if (jq.type(statuses) == "string") {
				// Notification
				content = notificationsArea.children(":has(.deleteFail)").clone().find(".errorCode").text(statuses).end();
								
				jq.fn.popup.binding = "on";
				jq.fn.popup.type = "obedient";
				jq.fn.popup.position = {
					"top" : 0,
					"left" : "center",
					"position" : "absolute"
				};
				fviewer.siblings(".toolbar").children("[name='delete']").popup(content, fviewer.closest(".fileExplorer"));
				// Reappend rows
				for (var row in rows) {
					fviewer.find(".uiDataGridList").trigger("addRow", [icon.clone().filter("span").text(rows[row].name).end(), rows[row].size, rows[row].type, rows[row].modified]);
					stateViewer.addClass("noDisplay");
					fviewer.find(".fileViewerWrapper").removeClass("noDisplay");
				}
				return;
			}
			
			var failed = [];
			for (var row in rows)
				if (statuses[row] !== true) {
					fviewer.find(".uiDataGridList").trigger("addRow", [icon.clone().filter("span").text(rows[row].name).end(), rows[row].size, rows[row].type, rows[row].modified]);
					stateViewer.addClass("noDisplay");
					fviewer.find(".fileViewerWrapper").removeClass("noDisplay");
					failed.push(rows[row].name);
				}
				
			// Notification for files / folders
			if (failed.length == 0)
				return;
				
			content = notificationsArea.children(":has(.partialDeleteFail)").clone().find(".fileList").text(failed.join(", ")).end();
								
			jq.fn.popup.binding = "on";
			jq.fn.popup.type = "obedient";
			jq.fn.popup.position = {
				"top" : 0,
				"left" : "center",
				"position" : "absolute"
			};
			fviewer.siblings(".toolbar").children("[name='delete']").popup(content, fviewer.closest(".fileExplorer"));
		}
		
		// Called on fileviewer
		function downloadFiles(subpath, fileNames) {
			var elements = jq("<input name='subPath' />").val(subpath+"/")
					.add(jq("<input name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")));
			for (var i = 0; i < fileNames.length; i++)
				elements = elements.add(jq("<input name='fNames[]' />").val(fileNames[i]));
				
			var data = elements.serialize();
			var fviewer = jq(this);
			var notificationsArea = fviewer.siblings(".notificationsArea");
			
			//window.open("/ajax/resources/sdk/fileExplorer/downloadFiles.php?"+data, "_blank");
			var dUrl = ajaxTester.resolve("/ajax/resources/sdk/fileExplorer/downloadFiles.php?"+data);
			dUrl = url.resource(dUrl);
			
			jq('#fediframe').attr('src', dUrl);
			
			content = notificationsArea.children(":has(.pendingDownload)").clone();
								
			jq.fn.popup.binding = "on";
			jq.fn.popup.type = "obedient";
			jq.fn.popup.withTimeout = true;
			jq.fn.popup.withFade = true;
			jq.fn.popup.position = {
				"top" : 0,
				"left" : "center",
				"position" : "absolute"
			};
			fviewer.siblings(".toolbar").children("[name='download']").popup(content, fviewer.closest(".fileExplorer"));
		}
		
		// Called on fileviewer
		/*function downloadCheck(response) {
			// Check if responce is OK
			// response is a json object... It's status property holds the status of the file drop
			console.log(response);
			
			//var _tmp = jq('<iframe />').attr('src', "http://redback.dyndns-at-home.com/"+response).hide().appendTo(jq(window));
	                //setTimeout(function(){
	                //    _tmp.remove();
	                //}, 5000);
			//console.log(response.status);
			// Do your thing
		}*/
		
		// Called on fileviewer
		function moveFiles(subpath, fileNames, destination, copy) {
			var elements = jq("<input name='subPath' />").val(subpath+"/")
					.add(jq("<input name='fexId' />").val(jq(this).closest(".fileExplorer").attr("id")));
					
			if (copy === true)
				elements = elements.add(jq("<input type='checkbox' name='copy' checked='checked' />"));
				
			for (var i = 0; i < fileNames.length; i++)
				elements = elements.add(jq("<input name='fNames[]' />").val(fileNames[i]));
			
			elements = elements.add(jq("<input name='fdest' />").val(destination));
			
			var data = elements.serialize();
			var fviewer = jq(this);
			
			ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/moveFiles.php", 
				"POST", 
				data, 
				"json",
				fviewer,
				moveCheck,
				null,
				false,
				true
			);
		}
		
		// Called on fileviewer
		function moveCheck(response) {
			// Check if responce is OK
			// response is a json object... It's status property holds the status of the file drop
			//console.dir(response);
			//console.log(response);
			var statuses = response.status;
			var fileViewer = jq(this);
			var notificationsArea = fileViewer.siblings(".notificationsArea");
			var content = notificationsArea.children(":has(.moveFail)").clone();
			var copy = fileViewer.siblings(".folderView").data("copy");
				
			jq.fn.popup.binding = "on";
			jq.fn.popup.type = "obedient";
			jq.fn.popup.withTimeout = false;
			jq.fn.popup.position = {
				"top" : 0,
				"left" : "center",
				"position" : "absolute"
			};
			
			if (jq.type(statuses) == "undefined" || jq.type(statuses) == "null") {
				fileViewer.siblings(".toolbar").children("[name='"+(copy ? "copy" : "move")+"']").popup(content, fileViewer.closest(".fileExplorer"));
				return;
			}
			
			if (jq.type(statuses) == "string") {
				fileViewer.siblings(".toolbar").children("[name='"+(copy ? "copy" : "move")+"']").popup(content.find(".errorCode").text(statuses).end(), fileViewer.closest(".fileExplorer"));
				return;
			}
			
			if (copy)
				return;
			
			var glist = fileViewer.find(".uiDataGridList");
			if (glist.length == 0 || glist.get(0).getSelectedRowCount() == 0)
				return;
			
			var failed = [];
			glist.trigger("getSelectedRows", function(rows) {
				for (var row in rows)
					if (statuses[rows[row].name] === true)
						glist.trigger("removeRow", rows[row]["__index"]);
					else
						failed.push(rows[row].name);
			});
			
			if (failed.length > 0)
				fileViewer.siblings(".toolbar").children("[name='"+(copy ? "copy" : "move")+"']").popup(content.find(".fileList").text("("+failed.join(", ")+")").end(), fileViewer.closest(".fileExplorer"));
			
			if (glist.get(0).getRowCount() > 0)
				return;
				
			fileViewer.find(".stateViewer").filter(function(){
				return jq(this).data("state") == "empty";
			}).removeClass("noDisplay").end().end()
				.find(".fileViewerWrapper").addClass("noDisplay");
		}
		
		function getFolderView(prettyName, subPath) {
			var fView = jq(this);
			
			var elements = jq("<input name='fexId' />").val(fView.closest(".fileExplorer").attr("id"));
			elements = elements.add(jq("<input name='pName' />").val(prettyName));
			elements = elements.add(jq("<input name='curSubP' />").val(subPath));
			
			var data = elements.serialize();
			
			ascop.asyncRequest(
				"/ajax/resources/sdk/fileExplorer/getFolderViewer.php", 
				"GET", 
				data, 
				"html",
				fView,
				positionFolderView,
				null,
				false,
				true
			);
		}
		
		// Called on fileviewer
		function positionFolderView(response) {
			// Check if responce is OK
			// Do your thing
			var folderView = jq(this);
			var copy = folderView.data("copy");
			var type = (copy ? "copy" : "move");
			var jqresponse = jq(response);
			
			jqresponse.find(".fepHeader."+type).css("display", "").siblings(".fepHeader").css("display", "none");
			
			var sender = folderView.siblings(".toolbar").find("[name='"+type+"']").first();
			
			sender.popup("set", jqresponse);
		}
		
		function keepAlive() {
			var fileExplorers = jq(".fileExplorer");
			
			fileExplorers.each(function(){
				var jqthis = jq(this);
				var info = jq("<input name='fexId' />").val(jqthis.attr("id"));
				var data = info.serialize();
				
				ascop.asyncRequest(
					"/ajax/resources/sdk/fileExplorer/keepAlive.php", 
					"GET", 
					data, 
					"json",
					jqthis,
					function (response) {
						//console.log(response);
					},
					null,
					true,
					true
				);
			});
		}
	
	})(jQuery);
});