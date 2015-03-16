// Create Hyperlinks
function createLink(){
	var url = prompt("Enter URL:", "http://");
	if (url)
	document.execCommand("createlink",false, url);
}

var createnewLink;

// Add embedded stuff
function createEmbed(){
	var code = prompt("Paste Embed Code", "");
	if (code) {
		var finalCode = "<div class='responsive-iframe-container'>"+code+"</div>";
		document.execCommand('insertHTML', false, finalCode);
	}
}

$(document).ready(function() {

	// Paste At Caret Funciton
	function pasteHtmlAtCaret(html, selector) {
		var sel, range, parent, node = null;
		
		if (document.selection) {
			node = document.selection.createRange().parentElement();
		} else {
			var selection = window.getSelection();
			if (selection.rangeCount > 0)
				node = selection.getRangeAt(0).startContainer.parentNode;
		}
		
		if ( node && $(node).is(selector) && window.getSelection) {
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
		} else {
			alert('Please click where you want to insert and try again');
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

// Create New Hyperlinks
createnewLink = function() {
	var url = prompt("What link would you like?", "http://");
	var text = prompt("What text should be displayed to the user?", "");
	var combined = "<a href="+url+">"+text+"</a>";
	if (url && text) {
		pasteHtmlAtCaret(combined,'.editor-text');
	} else {
		alert('Please try again and enter both a URL and the text that should be displayed.');
	}
}

	//Editor Nav Dropdowns
	$('.editor-drop').hover(
		function() {
			$(this).find('ul').slideDown(100);
		},
		function() {
			$(this).find('ul').slideUp(100);
		}
	);

	// Fix Copy & Paste formatting
	$('[contenteditable]').on('paste', function(e) {
		e.preventDefault();
		var text = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
		document.execCommand('insertText', false, text);
	});

	// Add Token
	$(document).on('click', '.token', function(event) {
		event.preventDefault();
		var token = $(this).html();
		document.execCommand('insertText', false, token);
	});

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

		var currentLink = $('.link-selected').attr('href');
		$('#link-location').val(currentLink);
	});

	// Set New Link
	$('#link-location').on('input', function() {
		newLink = $('#link-location').attr('value');
		$('.link-selected').attr('href', newLink);
	});

	
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

	// Click Image
	$(document).on('click', '.editor-text img', function(event) {
		if ($(this).hasClass("selectedImg")) {
			doneWithImage();
		} else {
			$('.selectedImg').removeClass('selectedImg');
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
	$(document).on('click', '#photo-desc, #photo-url, #photo-width, #link-location, .editor-text a', function(event) {
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
			alert('Please enter only numbers such as 300, or percents such as 50%');
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
		$('.selectedImg').css('opacity','0');
		setTimeout(function(){
			$('.selectedImg').remove();
			doneWithImage();
		}, 300);
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
	// Insert Table
	$(document.body).on("click", ".cell-add", function(event) {
		event.preventDefault();
		// get # of rows & cols
		var cols = $(this).index() + 1,
			rows = $(this).parent().index() + 1;
		if (cols != 0 && rows != 0) {
			var table = '<table class="rwd-table" width="100%">';
			for (var i = 1; i <= rows; i++) {
				table += '<tr>';
				for (var j = 1; j <= cols; j++) {
					table += '<td>&nbsp;</td>';
				}
				table += '</tr>';
			}
			table += '</table>';

			pasteHtmlAtCaret(table,'.editor-text');
		}
	});

	// Click Cell
	$(document).on('click', '.editor-text td, .editor-text th', function (event) {
		$('#selectedCell').removeAttr('id');
		$(this).attr('id', 'selectedCell');
		$('.panel:nth-child(2)').find('.panel-group:nth-child(3)').css({
			'pointerEvents':'auto',
			'opacity':'1'
		});
		event.stopPropagation();
	});

	$(document).on('click', function (event) {
		$('#selectedCell').removeAttr('id');
		$('.panel:nth-child(2)').find('.panel-group:nth-child(3), .panel-group:nth-child(4)').css({
			'pointerEvents':'none',
			'opacity':'.2'
		});
		$('#link-location').val('');
		$('.link-selected').removeClass('link-selected');
		doneWithImage();
	});



	// Add Header
	$(document.body).on("click", ".add-header", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var cols = $('#selectedTable tr:first-of-type td').length;

		$('#selectedTable').prepend('<thead><tr id="selectedRow"></tr></thead>')
		for (var i = 0; i < cols; i++) {
			$('#selectedRow').append('<th>&nbsp;</th>');
		}

		$('#selectedRow, #selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Add Row Below
	$(document.body).on("click", ".add-row-below", function(event) {
		$('#selectedCell').parents('table').attr('id', 'selectedTable');
		var whichOne = $('#selectedCell').parent().index(),
			cols = $('#selectedTable tr:first-of-type td').length;

		$('<tr id="selectedRow"></tr>').insertAfter("#selectedTable tr:eq(" + whichOne + ")");
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

		$('<tr id="selectedRow"></tr>').insertBefore("#selectedTable tr:eq(" + whichOne + ")");
		for (var i = 0; i < cols; i++) {
			$('#selectedRow').append('<td>&nbsp;</td>');
		}
		$('#selectedRow, #selected, #selectedTable').removeAttr('id');
		event.preventDefault();
	});

	// Delete Row
	$(document.body).on("click", ".delete-row", function(event) {
		$('#selectedCell').parents('tr').fadeOut(500, function() {
			$(this).remove();
		});
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


	/*** HTML MODE ***/
	$(document.body).on("click", "#htmlMode", function(event) {

		// If already in html mode
		if ($(this).hasClass('on')) {
			$(this).removeClass('on');
			$('.CodeMirror').each(function(i, el) {
				el.CodeMirror.refresh();
				var text = el.CodeMirror.getValue();
				//alert(text);
				$('.CodeMirror').fadeOut(250);
				setTimeout(function() {
					$('.editor-text').html(text).show().css('transform');
					$('.editor-text').css({
						'transform': 'scale(1)',
						'opacity': '1'
					});
					$('.html-mode').empty().html('<textarea name="htmlTextarea" id="htmlTextarea"></textarea>').hide();
				}, 250);
			});
			
			
		} else {
			$(this).addClass('on');
			var saveText = $('.editor-text').html(),
				formatText = $.htmlClean(saveText, {format:true});
			$('.editor-text').css({
				'transform': 'scale(.8)',
				'opacity': '0'
			});

			setTimeout(function() {
				$('.editor-text').hide();
				$('.html-mode textarea').html(formatText);

				var htmlEditor = CodeMirror(function(elt) {
					htmlTextarea.parentNode.replaceChild(elt, htmlTextarea);
				}, {
					value: htmlTextarea.value,
					lineNumbers: true,
					lineWrapping: true,
					viewportMargin: Infinity,
					theme: "syntax",
					indentWithTabs: true
				});
				$('.html-mode').fadeIn();
				$('.CodeMirror').each(function(i, el) {
					el.CodeMirror.refresh();
				});
			}, 250);

		}



	});

	/**** Insert BR on enter ****/
	$('div[contenteditable]').keydown(function(e) {
		if (e.keyCode === 13) {
			document.execCommand('insertHTML', false, '<br>');
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
});
