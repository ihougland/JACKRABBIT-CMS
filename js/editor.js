

$(document).ready(function() {

/*
 d888b  db       .d88b.  d8888b.  .d8b.  db      
88' Y8b 88      .8P  Y8. 88  `8D d8' `8b 88      
88      88      88    88 88oooY' 88ooo88 88      
88  ooo 88      88    88 88~~~b. 88~~~88 88      
88. ~8~ 88booo. `8b  d8' 88   8D 88   88 88booo. 
 Y888P  Y88888P  `Y88P'  Y8888P' YP   YP Y88888P
 */

	// Ctl+S Save
	$(document).keydown(function(e) {
		if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
		{
			e.preventDefault();
			savePage();
			return false;
		}
		return true;
	}); 

	savePage = function (){
		//get the text, id, title, etc
		var pageText = $('#pageText').html();
		var page_id = $('#page_id').val();
		var pageTitle = $('#pageTitle').val();
		var metaTitle = $('#metaTitle').val();
		var metaDescription = $('#metaDescription').val();
		var externalURL = $('#externalURL').val();
		// post(file, data, callback, type); (only "file" is required)
		$.post(  
		"ajax_update.php", //The update file
		{ type: 'pageUpdate', id: page_id, title: pageTitle, meta_title: metaTitle, meta_description: metaDescription, text: pageText, external_url: externalURL },  // create an object will all values
		//function that is called when server returns a value.
		function(data){
			closeDropdowns();
			$('#save-warning').remove();
			$('#edited-date').html(data.last_updated);
			$('#page-title').html(data.page_title);
		}, 
		//How you want the data formated when it is returned from the server.
		"json"
		);
	}
	savePageUpload = function (){
		//get the text, id, title, etc
		$('#pageUploadForm').submit();
		return false;
	}

	// Delete Page
	deletePage = function (){
		var r = confirm("Whoa there, are you sure you want to delete this page forever?");
		if (r == true) 
		{
			var page_id = $('#page_id').val();
			//get list of all images & documents
			var files_array = new Array();
			$(".editor-text img").each(function() {
				files_array.push($(this).attr("src"));
			});
			$(".editor-text .doc-link").each(function() {
				files_array.push($(this).attr("href"));
			});
			
			$.post(  
			"ajax_update.php", //The update file
			{ type: 'pageDelete', id: page_id, files_array: files_array },  // create an object will all values
			//function that is called when server returns a value.
			function(data){
				if(data.disallow=="yes")
				{
					message("This page cannot be deleted.","error");
				}
				else
				{
					//redirect to main pages.php
					window.location.href= "pages.php";
				}
			}, 
			//How you want the data formated when it is returned from the server.
			"json"
			);
		}
		closeDropdowns();
	}

	$('.seo-input').change(function()
	{
		//get
		var page_id = $("#page_id").val();
		var fieldname = $(this).attr('name');
		var seovalue = $(this).val();

		$.post(  
			"ajax_update.php", //The update file
			{ type: 'seoData', page_id: page_id, fieldname: fieldname, value: seovalue },  // create an object will all values
			//function that is called when server returns a value.
			function(data){
				//message('Changes Saved!', 'success');
				if(fieldname=="title")
				{
					$('#page-title').html(seovalue);
				}
			}, 
			//How you want the data formated when it is returned from the server.
			"json"
		);
	});

	// Paste At Caret Function	
	function pasteHtmlAtCaret(html, selector) {
	var sel, range, parent, node = null;

	if (document.selection) {
		node = document.selection.createRange().parentElement();
	} else {
		var selection = window.getSelection();
		if (selection.rangeCount > 0) {
			node = selection.getRangeAt(0).startContainer;
			if (node !== $(node).closest(selector).get(0)) {
				node = node.parentNode;
			}
		}
	}
	
	

	if (node && $(node).closest(selector).length > 0 && window.getSelection) {
		sel = window.getSelection();
		if (sel.getRangeAt && sel.rangeCount) {
			range = sel.getRangeAt(0);
			range.deleteContents();
			var el = document.createElement("div");
			el.innerHTML = html;
			var frag = document.createDocumentFragment(),
				node, lastNode;
			while ((node = el.firstChild)) {
				lastNode = frag.appendChild(node);
			}
			range.insertNode(frag);

			if (lastNode) {
				range = range.cloneRange();
				range.setStartAfter(lastNode);
				range.collapse(true);
				sel.removeAllRanges();
				sel.addRange(range);
			}
		}
	} else if (document.selection && document.selection.type != "Control") {
		document.selection.createRange().pasteHTML(html);
	} else {
		message('Please click where you want to insert, and try again.','warn');
	}
}

	// Is within element? function
	function isSelectionInsideElement(tagName) {
		var sel, containerNode;
		tagName = tagName.toUpperCase();
		if (window.getSelection) {
			sel = window.getSelection();
			if (sel.rangeCount > 0) {
				containerNode = sel.getRangeAt(0).commonAncestorContainer;
			}
		} else if ( (sel = document.selection) && sel.type != "Control" ) {
			containerNode = sel.createRange().parentElement();
		}
		while (containerNode) {
			if (containerNode.nodeType == 1 && containerNode.tagName == tagName) {
				return true;
			}
			containerNode = containerNode.parentNode;
		}
		return false;
	}

// Highlight current buttons
$('.editor-text').on('keyup', function(){
	rangeMouseup();
});
				
$('.editor-text').on('mouseup', function(event){
	$('a').removeClass('active');
	var node = event.target;
	while(node.nodeName != 'DIV'){
		$('a[rel='+node.nodeName+']').addClass('active');
		node = node.parentNode;
	}
});


function rangeMouseup(){
	if (document.selection){
		$(document.selection.createRange().parentElement()).trigger('mouseup');
	}
	else if (window.getSelection){
		var range = window.getSelection().getRangeAt(0);
		$(range.commonAncestorContainer.parentNode).trigger('mouseup');
		$(range.commonAncestorContainer).trigger('mouseup');
	}
}

/*
d888888b d8b   db .d8888. d88888b d8888b. d888888b 
  `88'   888o  88 88'  YP 88'     88  `8D `~~88~~' 
   88    88V8o 88 `8bo.   88ooooo 88oobY'    88    
   88    88 V8o88   `Y8b. 88~~~~~ 88`8b      88    
  .88.   88  V888 db   8D 88.     88 `88.    88    
Y888888P VP   V8P `8888Y' Y88888P 88   YD    YP   
*/

	// Wrap Selection
	wrapElement = function (elem){
		var element = elem
			sel = window.getSelection(),
			isAlready = isSelectionInsideElement(elem);
		$('.editor-text').attr('rel',''+sel+'');
			
		if (isAlready) {
			removeSelectedElements("h1,h2,h3,h4,h5,h6,blockquote");
			document.execCommand('removeFormat', false, 'null');
		} else if (sel.rangeCount) {
			var selected = $('.editor-text').attr('rel'),
				finalCode = '<'+element+'>'+selected+'</'+element+'>',
				range = sel.getRangeAt(0);

			range.deleteContents();
			removeSelectedElements("h1,h2,h3,h4,h5,h6,blockquote");
			//range.insertNode(document.createTextNode(finalCode));
			pasteHtmlAtCaret(finalCode,'.editor-text');
			$('.editor-text').attr('rel','');
		}
	}

	// Fix Chrome Adding Spans
	checkForSpan = function (elem){
  this.$editor.on("DOMNodeInserted", $.proxy(function(e) {
	if (e.target.tagName == "SPAN" ) {
	  var helper = $("<b>helper</b>");

	  $(e.target).before(helper);

	  helper.after($(e.target).contents());
	  helper.remove();

	  $(e.target).remove();
	}

  }, this));
}

	// Insert embedded code
	createEmbed = function() {
		var code = prompt("Paste Embed Code", "");
		if (code) {
			var finalCode = "<div class='rwd-embed' style='max-width:100%;'><div class='rwd-aspect rwd-embed-16-9'></div>"+code+"<div class='rwd-embed-overlay'></div></div>";
			pasteHtmlAtCaret(finalCode,'.editor-text');
		}
		closeDropdowns();
	}

	// Insert Hyperlink on Existing Text
	createLink = function() {
		var url = prompt("Enter URL:", "http://");
		if (url)
		document.execCommand("createlink",false, url);
	}

	// Insert New Hyperlinks
	createnewLink = function() {
		var url = prompt("What link would you like?", "http://");
		var text = prompt("What text should be displayed to the user?", "");
		var combined = "<a href="+url+">"+text+"</a>";
		if (url && text) {
			pasteHtmlAtCaret(combined,'.editor-text');
		} else {
			alert('Please try again and enter both a URL and the text that should be displayed.');
		}
		closeDropdowns();
	}
	
	// Insert Form Token
	/*$(document).on('click', '.token', function(event) {
		event.preventDefault();
		var token = $(this).html();
		pasteHtmlAtCaret(token,'.editor-text');
		closeDropdowns();
	});*/

	// Insert Table
	$(document.body).on("click", ".cell-add", function(event) {
		event.preventDefault();
		// get # of rows & cols
		var cols = $(this).index() + 1,
			rows = $(this).parent().index() + 1;
		if (cols != 0 && rows != 0) {
			var table = '<table class="rwd-table" width="100%"><tbody>';
			for (var i = 1; i <= rows; i++) {
				table += '<tr>';
				for (var j = 1; j <= cols; j++) {
					table += '<td>&nbsp;</td>';
				}
				table += '</tr>';
			}
			table += '</tbody></table>';

			pasteHtmlAtCaret(table,'.editor-text');
		}

		closeDropdowns();
	});

	//Insert Document
	insertDocument = function (){
		closeDropdowns();
		var sel, range, parent, node = null;

		if (document.selection) {
			node = document.selection.createRange().parentElement();
		} else {
			var selection = window.getSelection();
			if (selection.rangeCount > 0) {
				node = selection.getRangeAt(0).startContainer;
				if (node !== $(node).closest('.editor-text').get(0)) {
					node = node.parentNode;
				}
			}
		}
		if (node && $(node).closest('.editor-text').length > 0 && window.getSelection) {
		var uploaded_tag = '';
		var formData = '';
		//open modal
		$("body").append('<div class="modal-small-wrap"><div class="modal-small"><h1>Upload a Document</h1><form enctype="multipart/form-data" id="insertForm"><input type="file" value="" name="image" class="form-field-text" id="imageToUpload"><label class="form-field-name">Document</label><input type="text" value="" name="description" class="form-field-text" id="Description"><label class="form-field-name">Document Title</label><input type="hidden" name="filetype" value="document" /><input type="submit" value="Upload" name="submit" class="form-field-submit"> <a href="#" class="small-modal-cancel">Cancel</a></form></div></div>');
		pasteHtmlAtCaret('<span id="cursor-placeholder"></span>','.editor-text');
		$('.modal-small-wrap').css('opacity');
		$('.modal-small-wrap').css('opacity','1');
		$('.modal-small').css('top');
		$('.modal-small').css('top','0');

		$("form#insertForm").submit(function(event){
			$('.modal-small .form-field-submit').hide();
			$('.modal-small').append('<h1><i class="fa fa-cog fa-spin"></i> Nice! Uploading Now...</h1>');
		  //disable the default form submission
		  event.preventDefault();
		 
		  //grab all form data  
		  formData = new FormData($( this )[0]);

		  var request = $.ajax({
			url: 'upload.php',
			type: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			dataType: 'json',
			
		  });
		  request.done(function(returndata){
			  //insert file into page
			  if(returndata.error_msg!='')
			  {
				//show error message
				message(returndata.error_msg, "error");
			  }
			  else
			  {
				//put together document link
				uploaded_tag = '<a href="'+returndata.new_file+'" target="_blank" title="'+returndata.file_description+'" class="doc-link">'+returndata.file_description+'</a>';
				
				//console.log(uploaded_tag);
				$('#cursor-placeholder').replaceWith(uploaded_tag);
				
				$('.modal-small-wrap').fadeOut(function(){
					$('.modal-small-wrap').remove();
				});

				//save page
				savePage();
			  }
		  });
		});
	} else {
			message('Please click where you want to insert, and try again.','warn');
		}
	}

	$(document).on('click','.addon-link', function(e){
		e.preventDefault();
		e.stopPropagation();
		var page_id = $("#page_id").val();
		var next_url = $(this).attr("href");
		var addon_id = $(this).attr("id");
		//alert(page_id+' '+addon_id+' '+next_url);
		
		// post(file, data, callback, type); (only "file" is required)
		$.post(  
		"ajax_update.php", //The update file
		{ type: 'addAddon', page_id: page_id, addon_id: addon_id },  // create an object will all values
		//function that is called when server returns a value.
		function(data){
			$('body').append('<div class="modal"><a href="#" class="modal-close"><i class="fa fa-chevron-left"></i> DONE</a></div>');
			$('.main-wrap').addClass('blurout');
			$('.modal').show().css('opacity');
			$('.modal').css('opacity','1');
			setTimeout(function(){ 
				$('.modal').append('<div class="modal-content"><iframe id="jr-iframe" src="'+next_url+'"></iframe></div>');
				$('#jr-iframe').load(function() {
					$('.modal-content').css({
						'transform':'scale(1)',
						'opacity':'1'
					});
					setTimeout(function(){ 
						$('.modal-close').fadeIn();
					}, 550);
				});
			}, 550);
		}, 
		//How you want the data formated when it is returned from the server.
		"json"
		);
	});

	$(document).on('click','.addon-delete', function(e){
		e.preventDefault();
		var r = confirm("Are you sure you want to delete this Add-on? All related images and documents will also be deleted.");
		if (r == true) 
		{
			var page_id = $("#page_id").val();
			var addon_id = $(this).attr("rel");
			//alert(page_id+' '+addon_id+' '+next_url);
			
			// post(file, data, callback, type); (only "file" is required)
			$.post(  
			"ajax_update.php", //The update file
			{ type: 'deleteAddon', page_id: page_id, addon_id: addon_id },  // create an object will all values
			//function that is called when server returns a value.
			function(data){
				window.location.href= "pages.php?page_id="+page_id;
			}, 
			//How you want the data formated when it is returned from the server.
			"json"
			);
		}
	});

/*
db      d888888b d8b   db db   dD .d8888. 
88        `88'   888o  88 88 ,8P' 88'  YP 
88         88    88V8o 88 88,8P   `8bo.   
88         88    88 V8o88 88`8b     `Y8b. 
88booo.   .88.   88  V888 88 `88. db   8D 
Y88888P Y888888P VP   V8P YP   YD `8888Y' 
*/

	// Show current link in panel
	$(document).on('click','.editor-text a', function(){
		$('.link-selected').removeClass('link-selected')
		$(this).addClass('link-selected');

		$('.panel:nth-child(2)').find('.panel-title-active').removeClass('panel-title-active');
		$('.panel:nth-child(2)').find('.panel-group').hide();
		$('.panel:nth-child(2)').find('.panel-title-tab:nth-child(5)').addClass('panel-title-active');
		$('.panel:nth-child(2)').find('.panel-group:nth-child(4)').show();
		$('.panel:nth-child(2)').find('.panel-group:nth-child(4)').css({
			'pointerEvents':'auto',
			'opacity':'1'
		});


		if ($(this).hasClass('doc-link')){
			var url = $('.link-selected').attr('href');
			var filename = url.substring(url.lastIndexOf('/')+1);

			$('#link-location').hide();
			$('.panel:nth-child(2)').find('.panel-group:nth-child(4) label').html('<i class="fa fa-link"></i>  '+filename+'');
		} else {
			var currentLink = $('.link-selected').attr('href');
			$('#link-location').show().val(currentLink);
			$('.panel:nth-child(2)').find('.panel-group:nth-child(4) label').html('<span></span><i class="fa fa-link"></i> Link Location');
		}

		
	});

	// Set New Link
	$('#link-location').on('input', function() {
		newLink = $('#link-location').attr('value');
		$('.link-selected').attr('href', newLink);
	});

	removeLink = function (event){
		var linkTxt = $('.link-selected').text();
		if($('.link-selected').hasClass('doc-link'))
		{
			var r = confirm("Are you sure you want to remove the document link?");
			if (r == true) {
				var filename = $('.link-selected').attr('href');
				$('.link-selected').replaceWith(linkTxt);
				setTimeout(function(){
					//delete file
					// post(file, data, callback, type); (only "file" is required)
					$.post(  
						"ajax_update.php", //The update file
						{ type: 'fileDelete', filename: filename },  // create an object will all values
						//function that is called when server returns a value.
						function(data){
							if(data.error_msg!='')
							{
								message(data.error_msg, "error");
							}
						}, 
						//How you want the data formated when it is returned from the server.
						"json"
					);
					savePage();
				}, 300);
			}
		}
		else
		{
			$('.link-selected').replaceWith(linkTxt);
		}
	}

	viewLink = function (){
		var linkUrl = $('.link-selected').attr('href');
		window.open(linkUrl, '_blank'); 
	}

/*
d88888b .88b  d88. d8888b. d88888b d8888b. 
88'     88'YbdP`88 88  `8D 88'     88  `8D 
88ooooo 88  88  88 88oooY' 88ooooo 88   88 
88~~~~~ 88  88  88 88~~~b. 88~~~~~ 88   88 
88.     88  88  88 88   8D 88.     88  .8D 
Y88888P YP  YP  YP Y8888P' Y88888P Y8888D' 
*/

// Click Image
	$(document).on('click', '.editor-text .rwd-embed-overlay', function(event) {
		if ($(this).hasClass("selectedEmbed")) {
			doneWithEmbed();
		} else {
			doneWithImage();
			$('.editor-text').attr('contenteditable',false);
			$('.selectedEmbed').removeClass('selectedEmbed');
			$('#selectedCell').removeAttr('id');
			$(this).addClass('selectedEmbed');
			$('.panel:nth-child(2)').find('.panel-title-active').removeClass('panel-title-active');
			$('.panel:nth-child(2)').find('.panel-group').hide();

			$('.panel:nth-child(2)').find('.panel-title-tab:nth-child(6)').addClass('panel-title-active');
			$('.panel:nth-child(2)').find('.panel-group:nth-child(5)').show().css({
				'pointerEvents':'auto',
				'opacity':'1'
			});

			// Set current aspect
			if ($(this).parent().find('.rwd-embed-4-3')[0]) {
				$('#aspect-4-3').attr('checked','check');
			} else if ($(this).parent().find('.rwd-embed-16-9')[0]) {
				$('#aspect-16-9').attr('checked','check');
			} else if ($(this).parent().find('.rwd-embed-21-9')[0]) {
				$('#aspect-21-9').attr('checked','check');
			}

			// Set current max size
			var embedWidth = $('.selectedEmbed').parents('.rwd-embed').css('maxWidth'),
				reg2 = /(\d\%$)/,
				percentTest = reg2.test(embedWidth);

			if (percentTest == true) {
				$('#embed-size').val(embedWidth);
			} else  {
				var adjustedWidth = parseInt(embedWidth);
				$('#embed-size').val(adjustedWidth);
			}
		}
		event.stopPropagation();
	});

	// Set New Embed Max Width
	$('#embed-size').on('input', function() {
		var newembedWidth = $('#embed-size').attr('value'),
			reg = /^\d+(\%$|\d*$)/,
			reg2 = /(\d\%$)/,
			charTest = reg.test(newembedWidth),
			percentTest = reg2.test(newembedWidth);

		// passes char test
		if (!newembedWidth){

		} else if(charTest == true) {
			if (percentTest == true) {
				$('.selectedEmbed').parent().css('maxWidth', newembedWidth);
			}
			else {
				$('.selectedEmbed').parent().css('maxWidth', newembedWidth + 'px');
			}
		// fails chartest
		} else {
			message('Please enter only numbers such as 300, or percents such as 50%', 'warning');
		}
	});

	// Set New Aspect
	$('#aspect-4-3-label').on('click', function() {
		$('.selectedEmbed').parents('.rwd-embed').find('.rwd-aspect').removeClass().addClass('rwd-embed-4-3').addClass('rwd-aspect');
	});
	$('#aspect-16-9-label').on('click', function() {
		$('.selectedEmbed').parents('.rwd-embed').find('.rwd-aspect').removeClass().addClass('rwd-embed-16-9').addClass('rwd-aspect');
	});
	$('#aspect-21-9-label').on('click', function() {
		$('.selectedEmbed').parents('.rwd-embed').find('.rwd-aspect').removeClass().addClass('rwd-embed-21-9').addClass('rwd-aspect');
	});

	// Delete Embed
	$('.delete-embed').on('click', function() {
		$('.selectedEmbed').parents('.rwd-embed').fadeOut(250, function(){
			$(this).remove();
		});
	});

	function doneWithEmbed() {
		$('.selectedEmbed').removeClass('selectedEmbed');
		$('.panel:nth-child(2)').find('.panel-group:nth-child(5)').css({
			'pointerEvents':'none',
			'opacity':'.2'
		});
		$('#embed-size').val('');
	}
	doneWithEmbed();

/*
d888888b .88b  d88.  .d8b.   d888b  d88888b .d8888. 
  `88'   88'YbdP`88 d8' `8b 88' Y8b 88'     88'  YP 
   88    88  88  88 88ooo88 88      88ooooo `8bo.   
   88    88  88  88 88~~~88 88  ooo 88~~~~~   `Y8b. 
  .88.   88  88  88 88   88 88. ~8~ 88.     db   8D 
Y888888P YP  YP  YP YP   YP  Y888P  Y88888P `8888Y' 
*/

	/* Prevent dragging 
		$('div').bind('dragover drop', function(event) {
			event.preventDefault();
			return false;
		});
	*/

	//Insert Image
	insertImage = function (){
		closeDropdowns();
		 var sel, range, parent, node = null;

		if (document.selection) {
			node = document.selection.createRange().parentElement();
		} else {
			var selection = window.getSelection();
			if (selection.rangeCount > 0) {
				node = selection.getRangeAt(0).startContainer;
				if (node !== $(node).closest('.editor-text').get(0)) {
					node = node.parentNode;
				}
			}
		}
		if (node && $(node).closest('.editor-text').length > 0 && window.getSelection) {
			var uploaded_tag = '';
			var formData = '';
			//open modal
			$("body").append('<div class="modal-small-wrap"><div class="modal-small"><h1>Upload an Image</h1><form enctype="multipart/form-data" id="insertForm"><input type="file" value="" name="image" class="form-field-text" id="imageToUpload"><label class="form-field-name">Photo</label><input type="text" value="" name="description" class="form-field-text" id="Description"><label class="form-field-name">Photo Description</label><input type="hidden" name="filetype" value="image" /><input type="submit" value="Upload" name="submit" class="form-field-submit"> <a href="#" class="small-modal-cancel">Cancel</a></form></div></div>');
			pasteHtmlAtCaret('<span id="cursor-placeholder"></span>','.editor-text');

			$('.modal-small-wrap').css('opacity');
			$('.modal-small-wrap').css('opacity','1');
			$('.modal-small').css('top');
			$('.modal-small').css('top','0');

			$("form#insertForm").submit(function(event){
				$('.modal-small .form-field-submit').fadeOut();
				$('.modal-small').append('<h1><i class="fa fa-cog fa-spin"></i> Nice! Uploading Now...</h1>');
			  //disable the default form submission
			  event.preventDefault();
			 
			  //grab all form data  
			  formData = new FormData($( this )[0]);

			  var request = $.ajax({
				url: 'upload.php',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				dataType: 'json',
				
			  });
			  request.done(function(returndata){
				  //insert image or file into page
				  if(returndata.error_msg!='')
				  {
					//show error message
					message(returndata.error_msg, "error");
				  }
				  else
				  {
					//put together image
					uploaded_tag = '<img src="'+returndata.new_file+'" class="float-normal" alt="'+returndata.file_description+'" />';
					
					//console.log(uploaded_tag);
					$('#cursor-placeholder').replaceWith(uploaded_tag);
					
					$('.modal-small-wrap').fadeOut(function(){
						$('.modal-small-wrap').remove();
					});
					//save page
					savePage();
				  }
			  });
			});
		} else {
			message('Please click where you want to insert, and try again.','warn');
		}
	}

	$(document).on('click', '.small-modal-cancel', function(event) {
		$('.modal-small-wrap').css('opacity','0');
		$('.modal-small').css('top','-100%');
		setTimeout(function(){
			$('.modal-small-wrap, #cursor-placeholder').remove();
		}, 500);
	});
	
	// Click Image
	$(document).on('click', '.editor-text img', function(event) {
		if ($(this).hasClass("selectedImg")) {
			doneWithImage();
		} else {
			doneWithEmbed();
			$('.selectedImg').removeClass('selectedImg');
			$('#selectedCell').removeAttr('id');
			$('.tool-highlight').removeClass('tool-highlight');
			event.preventDefault();
			$(this).addClass('selectedImg');
			$('.panel:nth-child(2)').find('.panel-title-active').removeClass('panel-title-active');
			$('.panel:nth-child(2)').find('.panel-group').hide();

			$('.panel:nth-child(2)').find('.panel-title-tab:nth-child(3)').addClass('panel-title-active');
			$('.panel:nth-child(2)').find('.panel-group:nth-child(2)').show();
			$('.panel:nth-child(2)').find('.panel-group:nth-child(2)').css({
				'pointerEvents':'auto',
				'opacity':'1'
			});
			// Update Alt if applicable
			if(this.alt) {
				var saveAlt = $(this).attr('alt');
				$('#photo-desc').val(saveAlt);
			} else {
				$('#photo-desc').val('');
			}

			// Update URL if applicable
			var elementType = $(this).parent().prop('tagName');
			if(elementType == 'A') {
				var saveUrl = $(this).parent().attr('href');
				$('#photo-url').val(saveUrl);
			} else {
				$('#photo-url').val('');
			}

			// Update Size if applicable
			if(this.width) {
				var saveWidth = $('.selectedImg').attr('width');
				$('#photo-width').val(saveWidth);
			} else {
				$('#photo-width').val('');
			}
			var naturalWidth = $('.selectedImg')[0].naturalWidth;
			$('#original-size').html('(originally '+naturalWidth+')');

			// Update selected float
			if($(this).hasClass('float-left')){
				$('.fl').addClass('tool-highlight');
			} else if ($(this).hasClass('float-right')){
				$('.fr').addClass('tool-highlight');
			} else if ($(this).hasClass('float-center')){
				$('.fc').addClass('tool-highlight');
			} else if ($(this).hasClass('float-normal')){
				$('.fn').addClass('tool-highlight');
			}
		}
		event.stopPropagation();
	});

	// So clicking these fields doesn't close the image edit session
	$(document).on('click', '#photo-desc, #photo-url, #photo-width, #link-location, .editor-text a, .radio-wrap, #embed-size', function(event) {
	   event.stopPropagation();
	});

	// Set New Image Alt
	$('#photo-desc').on('input', function() {
		newDesc = $('#photo-desc').attr('value');
		$('.selectedImg').attr('alt', newDesc);
	});

	// Set New Image URL
	$('#photo-url').on('input', function() {
		var newUrl = $('#photo-url').attr('value');
		var emptyCheck = $.trim($("#photo-url").val());
		var elementType = $('.selectedImg').parent().prop('tagName');
		if(emptyCheck.length<=0 && elementType == 'A') {
			$('.selectedImg').unwrap();
		} else if(elementType == 'A') {
			$('.selectedImg').parent().attr('href', newUrl);
		} else {
			$('.selectedImg').wrap('<a href="'+newUrl+'"></a>')
		}
	});

	// Set New Image Width
	$('#photo-width').on('input', function() {
		var naturalWidth = $('.selectedImg')[0].naturalWidth,
			newWidth = $('#photo-width').attr('value'),
			reg = /^\d+(\%$|\d*$)/,
			reg2 = /(\d\%$)/,
			charTest = reg.test(newWidth),
			percentTest = reg2.test(newWidth);

		// passes char test
		if (!newWidth){
		} else if(charTest == true) {
			$('.selectedImg').attr('width', newWidth);
		// fails chartest
		} else {
			message('Please enter only numbers such as 300, or percents such as 50%','error');
		}

		// Is it a px value that's larger than the original?
		if (percentTest == false && newWidth > naturalWidth) {
			$('#img-size-label span').html(' <i class="fa fa-warning" rel="New image size is larger than original. This may reduce image quality."></i>');
		} else {
			$('#img-size-label span').empty();
		}
	});

	function doneWithImage() {
		$("#image-toolbar").remove();
		$('.selectedImg').removeClass('selectedImg');
		$('.editor-text').attr('contenteditable', true).css('color', '');
		$(".editor-toolbar a").css({
			'pointer-events': 'auto',
			'opacity': '1'
		});
		$('.panel:nth-child(2)').find('.panel-group:nth-child(2)').css({
			'pointerEvents':'none',
			'opacity':'.2'
		});
		$('#photo-desc, #photo-url, #photo-width').val('');
		$('#img-size-label .fa-warning').remove();
		$('#original-size').empty();
	}
	doneWithImage();


	// Apply right
	$(document.body).on("click", ".fr", function(event) {
		$('.selectedImg').removeClass().addClass('float-right').removeAttr('id');
		doneWithImage();
		event.preventDefault();
	});

	// Apply left
	$(document.body).on("click", ".fl", function(event) {
		$('.selectedImg').removeClass().addClass('float-left').removeAttr('id');
		doneWithImage();
		event.preventDefault();
	});

	// Apply normal
	$(document.body).on("click", ".fn", function(event) {
		$('.selectedImg').removeClass().addClass('float-normal').removeAttr('id');
		doneWithImage();
		event.preventDefault();
	});

	// Apply center
	$(document.body).on("click", ".fc", function(event) {
		$('.selectedImg').removeClass().addClass('float-center').removeAttr('id');
		doneWithImage();
		event.preventDefault();
	});

	// Apply delete
	$(document.body).on("click", ".fd", function(event) {
		event.stopPropagation();
		var r = confirm("Are you sure you want to delete the image?");
		if (r == true) {
			$('.selectedImg').css('opacity','0');
			var filename = $('.selectedImg').attr('src');
			setTimeout(function(){
				$('.selectedImg').remove();
				//delete file
				// post(file, data, callback, type); (only "file" is required)
				$.post(  
					"ajax_update.php", //The update file
					{ type: 'fileDelete', filename: filename },  // create an object will all values
					//function that is called when server returns a value.
					function(data){
						if(data.error_msg!='')
						{
							message(data.error_msg, "error");
						}
					}, 
					//How you want the data formated when it is returned from the server.
					"json"
				);
				savePage();
				doneWithImage();
			}, 300);
		}
		
		
		event.preventDefault();
	});

	/*
	d888888b  .d8b.  d8888b. db      d88888b .d8888. 
	`~~88~~' d8' `8b 88  `8D 88      88'     88'  YP 
	   88    88ooo88 88oooY' 88      88ooooo `8bo.   
	   88    88~~~88 88~~~b. 88      88~~~~~   `Y8b. 
	   88    88   88 88   8D 88booo. 88.     db   8D 
	   YP    YP   YP Y8888P' Y88888P Y88888P `8888Y'
	*/
	// Hover Table Dropdown
	$(document).on("mouseenter", ".cell-add", function(event) {
		// How many rows/cols have you selected?
		var hoveredCols = $(this).index() + 1,
			hoveredRows = $(this).parent().index() + 1,
			//How many total?
			availRows = $('.row-add').length,
			availCols = $('.row-add:first .cell-add').length;

		$('.cell-highlight').removeClass('cell-highlight');
		// Update the text hint
		$('.table-dimension-result').html(hoveredRows + ' x ' + hoveredCols);
		// Add Highlighting to selected squares from top left to cursor
		$('.row-add:lt(' + hoveredRows + ') .cell-add:not(:nth-child(1n+' + (hoveredCols + 1) + '))').addClass('cell-highlight');
		// add rows as needed
		if (hoveredRows == availRows) {
			$('.row-add:first').clone().appendTo('.table-dimension');
			$('.row-add:last .cell-add').removeClass('cell-highlight');
		}
		if (hoveredCols == availCols) {
			$('.row-add').append('<a href="#" class="cell-add"></a>');
		}
	});
	// Mouse Out
	$(document).on("mouseleave", ".table-dimension", function(event) {
		// Remove old highlighting
		$('.cell-highlight').removeClass('cell-highlight');
		$('.table-dimension-result').html('0 x 0');
		var table = '';
		for (var i = 1; i <= 4; i++) {
			table += '<div class="row-add">';
			for (var j = 1; j <= 4; j++) {
				table += '<a href="#" class="cell-add"></a>';
			}
			table += '</div>';
		}
		$('.table-dimension').html(table);
	});
	

	// Click Cell
	$(document).on('click', '.editor-text td, .editor-text th', function (event) {
		$('#selectedCell').removeAttr('id');		
		doneWithImage();
		$(this).attr('id', 'selectedCell');
		$('.panel:nth-child(2)').find('.panel-group:nth-child(3)').css({
			'pointerEvents':'auto',
			'opacity':'1'
		});
		if($(this).parents('table').hasClass('hidden-table')){
			$('#invisible-table-mode').attr('checked','check');
		} else {
			$('#normal-table-mode').attr('checked','check');
		}
		event.stopPropagation();
	});

	// Invisible Style Mode
	$('#invisible-table-mode-label').on('click', function() {
		$('#selectedCell').parents('table').addClass('hidden-table');
	});
	$('#normal-table-mode-label').on('click', function() {
		$('#selectedCell').parents('table').removeClass('hidden-table');
	});


	// Add Header
	$(document.body).on("click", ".add-header", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var cols = $('#selectedTable tr:first-of-type td').length;

		if ($('#selectedTable thead').length ){
			alert("This table already has a heading.");
		} else {
			$('#selectedTable').prepend('<thead><tr id="selectedRow"></tr></thead>')
			for (var i = 0; i < cols; i++) {
				$('#selectedRow').append('<th>&nbsp;</th>');
			}
		}

		$('#selectedRow, #selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Delete Header
	$(document.body).on("click", ".delete-header", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var cols = $('#selectedTable tr:first-of-type td').length;

		$('#selectedTable thead').fadeOut(500, function() {
			$(this).remove();
		});

		$('#selectedRow, #selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Add Row Below
	$(document.body).on("click", ".add-row-below", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var whichOne = $('#selectedCell').parent().index(),
			cols = $('#selectedTable tr:first-of-type td').length;

		$('<tr id="selectedRow"></tr>').insertAfter("#selectedTable tbody tr:eq(" + whichOne + ")");
		for (var i = 0; i < cols; i++) {
			$('#selectedRow').append('<td>&nbsp;</td>');
		}
		$('#selectedRow, #selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Add Row Above
	$(document.body).on("click", ".add-row-above", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var whichOne = $('#selectedCell').parent().index(),
			cols = $('#selectedTable tr:first-of-type td').length;

		$('<tr id="selectedRow"></tr>').insertBefore("#selectedTable tbody tr:eq(" + whichOne + ")");
		for (var i = 0; i < cols; i++) {
			$('#selectedRow').append('<td>&nbsp;</td>');
		}
		$('#selectedRow, #selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Delete Row
	$(document.body).on("click", ".delete-row", function(event) {
		if ($('#selectedCell').parents('thead').length ){
			$('#selectedCell').parents('thead').fadeOut(500, function() {
				$(this).remove();
			});
		} else {
			$('#selectedCell').parents('tr').fadeOut(500, function() {
				$(this).remove();
			});
		}

		event.preventDefault();
	});

	// Add Column After
	$(document.body).on("click", ".add-column-after", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var whichOne = $('#selectedCell').index(),
			rows = $('#selectedTable tr').length;

		$('#selectedTable tr').each(function() {
			$('<td>&nbsp;</td>').insertAfter($(this).find('td:eq(' + whichOne + ')'));
			$('<th>&nbsp;</th>').insertAfter($(this).find('th:eq(' + whichOne + ')'));
		});

		$('#selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Add Column Before
	$(document.body).on("click", ".add-column-before", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var whichOne = $('#selectedCell').index(),
			rows = $('#selectedTable tr').length;

		$('#selectedTable tr').each(function() {
			$('<td>&nbsp;</td>').insertBefore($(this).find('td:eq(' + whichOne + ')'));
			$('<th>&nbsp;</th>').insertBefore($(this).find('th:eq(' + whichOne + ')'));
		});

		$('#selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Delete Col
	$(document.body).on("click", ".delete-column", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var whichOne = $('#selectedCell').index();
		$('#selectedTable tr').find("td:eq(" + whichOne + ")").fadeOut(500, function() {
			$(this).remove();
		});
		$('#selectedTable thead tr').find("th:eq(" + whichOne + ")").fadeOut(500, function() {
			$(this).remove();
		});
		$('#selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Delete Entire Table
	$(document.body).on("click", ".delete-table", function(event) {
		var r = confirm("Are you sure you want to delete the whole table");
		if (r == true) {
			$('#selectedCell').parents('table').fadeOut(500, function() {
				$(this).remove();
			});
		}
		event.preventDefault();
	});

/*
db    db d888888b d88888b db   d8b   db      .88b  d88.  .d88b.  d8888b. d88888b 
88    88   `88'   88'     88   I8I   88      88'YbdP`88 .8P  Y8. 88  `8D 88'     
Y8    8P    88    88ooooo 88   I8I   88      88  88  88 88    88 88   88 88ooooo 
`8b  d8'    88    88~~~~~ Y8   I8I   88      88  88  88 88    88 88   88 88~~~~~ 
 `8bd8'    .88.   88.     `8b d8'8b d8'      88  88  88 `8b  d8' 88  .8D 88.     
   YP    Y888888P Y88888P  `8b8' `8d8'       YP  YP  YP  `Y88P'  Y8888D' Y88888P 
*/
	// Normal Mode
	$(document.body).on("click", ".view-btns a:nth-child(1)", function(event) {
		if ($(this).hasClass('on')) {
			//chill
		} else {
			$('.on').removeClass();
			$(this).addClass('on');
			$('.CodeMirror').each(function(i, el) {
				el.CodeMirror.refresh();
				var text = el.CodeMirror.getValue();
				$('.html-mode').css({
					'transform': 'scale(.2)',
					'opacity': '0'
				});
				setTimeout(function() {
					$('.html-mode').hide();
					$('.editor-text').html(text).show().css('transform');
					$('.editor-text').css({
						'transform': 'scale(1)',
						'opacity': '1'
					});
					$('.html-mode').empty().html('<textarea name="htmlTextarea" id="htmlTextarea"></textarea>').hide();
				}, 250);
			});
		}
	});

	// HTML Mode 
	$(document.body).on("click", ".view-btns a:nth-child(2)", function(event) {

		// If already in html mode
		if ($(this).hasClass('on')) {
			// Chill
		} else {
			$('.on').removeClass();
			$(this).addClass('on');
			doneWithImage();
			doneWithEmbed();
			$('#selectedCell').removeAttr('id');
			var saveText = $('.editor-text').html(),
				formatText = $.htmlClean(saveText, {
					format:true,
					allowComments:true,
				});
			$('.editor-text').css({
				'transform': 'scale(.2)',
				'opacity': '0'
			});

			setTimeout(function() {
				$('.editor-text').hide();
				$('.html-mode textarea').html(formatText);

				var htmlEditor = CodeMirror(function(elt) {
					htmlTextarea.parentNode.replaceChild(elt, htmlTextarea);
				}, {
					value: htmlTextarea.value,
					lineNumbers: false,
					lineWrapping: true,
					viewportMargin: Infinity,
					theme: "syntax",
					indentWithTabs: true
				});
				$('.html-mode').css({
					'transform': 'scale(.2)',
					'opacity': '0'
				});
				$('.html-mode').show().css('opacity');
				$('.html-mode').css({
					'opacity': '1',
					'transform': 'scale(1)'
				});
			}, 250);
			setTimeout(function() {
				$('.CodeMirror').each(function(i, el) {
					el.CodeMirror.refresh();
				});
			}, 400);

		}



	});


/*
d88888b d8888b. d888888b d888888b d888888b d8b   db  d888b  
88'     88  `8D   `88'   `~~88~~'   `88'   888o  88 88' Y8b 
88ooooo 88   88    88       88       88    88V8o 88 88      
88~~~~~ 88   88    88       88       88    88 V8o88 88  ooo 
88.     88  .8D   .88.      88      .88.   88  V888 88. ~8~ 
Y88888P Y8888D' Y888888P    YP    Y888888P VP   V8P  Y888P  
*/

	// Reset crap when clicking anywhere else
	$(document).on('click', function (event) {
		$('#selectedCell').removeAttr('id');
		$('.panel:nth-child(2)').find('.panel-group:nth-child(3), .panel-group:nth-child(4), .panel-group:nth-child(5)').css({
			'pointerEvents':'none',
			'opacity':'.2'
		});
		
		$('#link-location').val('');
		$('.link-selected').removeClass('link-selected');
		doneWithImage();
		doneWithEmbed();
	});


	// Fix Copy & Paste formatting
	$('[contenteditable]').on('paste', function(e) {
		e.preventDefault();
		var text = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
		document.execCommand('insertText', false, text);
	});
	// Insert BR on enter
	$('div[contenteditable]').keydown(function(e) {
		if (e.keyCode === 13) {
			//document.execCommand('insertHTML', false, '<br>');
			pasteHtmlAtCaret('<br>','.editor-text');
			return false;
		}
	});


	/**** Remove Header Formatting ****/
	function nextNode(node) {
		if (node.hasChildNodes()) {
			return node.firstChild;
		} else {
			while (node && !node.nextSibling) {
				node = node.parentNode;
			}
			if (!node) {
				return null;
			}
			return node.nextSibling;
		}
	}

	function getRangeSelectedNodes(range, includePartiallySelectedContainers) {
		var node = range.startContainer;
		var endNode = range.endContainer;
		var rangeNodes = [];

		// Special case for a range that is contained within a single node
		if (node == endNode) {
			rangeNodes = [node];
		} else {
			// Iterate nodes until we hit the end container
			while (node && node != endNode) {
				rangeNodes.push(node = nextNode(node));
			}

			// Add partially selected nodes at the start of the range
			node = range.startContainer;
			while (node && node != range.commonAncestorContainer) {
				rangeNodes.unshift(node);
				node = node.parentNode;
			}
		}

		// Add ancestors of the range container, if required
		if (includePartiallySelectedContainers) {
			node = range.commonAncestorContainer;
			while (node) {
				rangeNodes.push(node);
				node = node.parentNode;
			}
		}

		return rangeNodes;
	}

	function getSelectedNodes() {
		var nodes = [];
		if (window.getSelection) {
			var sel = window.getSelection();
			for (var i = 0, len = sel.rangeCount; i < len; ++i) {
				nodes.push.apply(nodes, getRangeSelectedNodes(sel.getRangeAt(i), true));
			}
		}
		return nodes;
	}

	function replaceWithOwnChildren(el) {
		var parent = el.parentNode;
		while (el.hasChildNodes()) {
			parent.insertBefore(el.firstChild, el);
		}
		parent.removeChild(el);
	}

	function removeSelectedElements(tagNames) {
		var tagNamesArray = tagNames.toLowerCase().split(",");
		getSelectedNodes().forEach(function(node) {
			if (node.nodeType == 1 &&
				tagNamesArray.indexOf(node.tagName.toLowerCase()) > -1) {
				// Remove the node and replace it with its children
				replaceWithOwnChildren(node);
			}
		});
	}

	document.getElementById("removeHeadings").onclick = function() {
		removeSelectedElements("h1,h2,h3,h4,h5,h6,blockquote");
		document.execCommand('removeFormat', false, 'null');
		return false;
	};

	$(document.body).on("click", ".editor-text", function(event) {
		$('.editor-text span').contents().unwrap();
		if($('.editor-title #save-warning').length){

		}else {
		$('.editor-title').append('<span id="save-warning">- Not Saved</span>');
	}
		closeDropdowns();
	});


});
