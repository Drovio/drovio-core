var jq=jQuery.noConflict();
jq(document).one("ready.extra", function() {
/*	
	
	
	
*/
	/*
	*	Drop Down Menu
	*/
	jq(document).on('click', '.ddMenuRoot', function(ev) {
		ref = jq(this).attr('data-ref');
		
		var height = jq(this).parent().height();
		var menu = jq(this).siblings('#'+ref);
		menu.toggleClass('noDisplay');
		
		var position = jq(this).parent().offset(); 
		menu.css(position);
		var top = parseInt(menu.css('top'));
		menu.css('top', top + height  + 'px');
	});
	
	jq(document).on('hover', '.ddMenuRoot', function(ev) {
	});
	
	/*
	*	Listeners
	*/
	
	jq(document).on("documentor.sectionSave", ".documentor", function(){
		jq(document).on("content.modified", function(ev) {
			jq('.reportPool').children().each(function() {				
				// Get Vars				
				saveReport = jq(this);
				
				frmId = saveReport.attr('data-frmid');
				pos = saveReport.attr('data-pos');
								
				// Get documentor
				jqDocumentor = saveReport.closest(".documentor");

				// Delete Report
				saveReport.remove();
				
				presenter = jqDocumentor.find('#'+frmId).closest(".presenter");
				
				presenter.attr('data-pos', pos);
				
				editorWrapper = presenter.find('.editorWrapper');
				editorWrapper.addClass('noDisplay');
				editorContent = editorWrapper.find('.formBody [name="sectionBody"]').val();
				
				viewerWrapper = presenter.find('.viewerWrapper');
				viewerWrapper.removeClass('noDisplay');
				viewerWrapper.find('.viewer').html(editorContent);
				
			});
		});

	});
	
	jq(document).on("documentor.sectionDelete", ".documentor", function(){
		jq(document).on("content.modified", function(ev) {
			jq('.reportPool').children('.deleteReport').each(function() {				
				// Get Vars				
				deleteReport = jq(this);
				
				frmId = deleteReport.attr('data-frmid');
				pos = deleteReport.attr('data-pos');
				count = deleteReport.attr('data-count');
								
				// Get documentor
				jqDocumentor = deleteReport.closest(".documentor");

				// Delete Report
				deleteReport.remove();
				
				presenter = jqDocumentor.find('#'+frmId).closest(".presenter");				
				presenter.remove();
				
				console.log(parseInt(pos) + 1);
				console.log(parseInt(count) + 1);
				
				posInt = parseInt(pos) + 1
				cntInt = parseInt(count) + 1
				for(i = posInt; i <= cntInt; i++)
				{
					presenter = jqDocumentor.find('.presenter[data-pos="'+i+'"]');
					presenter.attr('data-pos', i-1);
				}
				
				
			});
		});

	});
	
	jq(document).on("content.modified", function(ev) {
		jq(document).off('click', '.uiFormButton[data-formDissmiss="save"]');
		jq(document).on('click', '.uiFormButton[data-formDissmiss="save"]', function(ev) {
			presenter = jq(this).closest('.presenter');
			
			pos = presenter.attr('data-pos');
			posInt = parseInt(pos);
			console.log(posInt);
			if(posInt > 0)
			{	
				// Edit Modode
				editorWrapper = presenter.find('.editorWrapper');
				editorWrapper.addClass('noDisplay');
				
				viewerWrapper = presenter.find('.viewerWrapper');
				viewerWrapper.removeClass('noDisplay');
			}
			else
			{
				// Add New Mode
				presenter.remove();
			}
			
		});
	});
	
	// 
	jq(document).on('click', 'div[data-clickAct]', function(ev) {
		var type = jq(this).attr('data-clickAct');
		jq(this).closest('#ddMenuGroup').addClass('noDisplay');		
		
		var documentor = jq(this).closest('.documentor');
		var fid = documentor.find('input[name="id"]').attr('id');
		
		data = "type="+type+"&fid="+fid;
		ascop.asyncRequest(
			"/ajax/resources/sdk/documentor/addNewSection.php", 
			"GET",
			data,
			"html",
			this,
			handleNewSection
		);
	});
	
	function handleNewSection(html) {
		console.log(html);
		var fid = jq(html).find('div[data-frmTarget]').attr('data-frmTarget');
		var documentor = jq('.documentor > input[id="'+ fid +'"]').parent();
		
		var jqxml = jq(html).find('.content'); 
		var key = documentor.find('input[name="key"]').clone();
		
		jqxml.find('form > .formBody').append(key);
		var editor = jqxml.html();		
		
		documentor.find('.dropPool').append(editor);
		console.log(documentor);
		
		
		//wasInitiated = true;
		//jq(document).trigger("content.modified.documentor");
	}
	
	jq(document).on('click', '.control[data-ctrlType="edit"]', function(ev) {
		presenter = jq(this).closest('.presenter');
		
		type = presenter.attr('data-type');
		pos = presenter.attr('data-pos');
		
		data = "type="+type+"&pos="+pos;
		
		ascop.asyncRequest(
			"/ajax/resources/sdk/documentor/getEditor.php", 
			"GET",
			data,
			"html",
			this,
			handleGetEditor
		);
	});
	
	function handleGetEditor(html) {
		var jqxml = jq(html).find('.content'); 
		
		appendPathKey(jqxml);
		
		pos = jqxml.find('.formBody input[name="pos"]').val();
		
		viewerWrapper = jq('.presenter[data-pos="'+pos+'"]').find('.viewerWrapper');
		viewerContent = viewerWrapper.find('.viewer').html();
		viewerWrapper.addClass('noDisplay');
				
		
		jqxml.find('.formBody [name="sectionBody"]').html(viewerContent);
		
		var editor = jqxml.html();
		
		editorWrapper = jq('.presenter[data-pos="'+pos+'"]').find('.editorWrapper')
		editorWrapper.html(editor);
		editorWrapper.removeClass('noDisplay');
		
	}
	
	function appendPathKey(formWrapper)
	{
		var key = jq('.documentor > input[name="key"]').clone();		
		formWrapper.find('form > .formBody').append(key);
		
		var id = jq('.documentor > input[name="id"]').clone();		
		formWrapper.find('form > .formBody').append(key);
	}
	
	jq(document).on('click', '.control[data-ctrlType="delete"]', function(ev) {
		presenter = jq(this).closest('.presenter');
		
		type = presenter.attr('data-type');
		pos = presenter.attr('data-pos');
		
		data = "type="+type+"&pos="+pos;
		
		ascop.asyncRequest(
			"/ajax/resources/sdk/documentor/deleteSection.php", 
			"GET",
			data,
			"html",
			this,
			handleDeleteSection
		);
	});
	
	function handleDeleteSection(html) {
		var jqxml = jq(html).find('.content'); 
		
		appendPathKey(jqxml);
		pos = jqxml.find('.formBody input[name="pos"]').val();
		itemTopBar = jq('.presenter[data-pos="'+pos+'"]').find('.itemTopBar');
		
		itemTopBar.find('.itemControlBar').addClass('noDisplay');
		itemTopBar.find('.prompt').html(jqxml.html());
		itemTopBar.find('.prompt').removeClass('noDisplay');
		
		// Add dissmiss event
		jq(jqxml).on('click', '.uiFormButton[data-formDissmiss="delete"]', function(ev) {
			console.log('OK');
			presenter = jq(this).closest('.presenter');
			
			presenter.find('.itemControlBar').removeClass('noDisplay');
			presenter.find('.prompt').addClass('noDisplay');
		});
	}
});