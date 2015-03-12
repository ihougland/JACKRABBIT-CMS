

$(document).ready(function() {

// Add Header?
var urlParam = function(name, w){
    w = w || window;
    var rx = new RegExp('[\&|\?]'+name+'=([^\&\#]+)'),
        val = w.location.search.match(rx);
    return !val ? '':val[1];
}
var app = urlParam('app');
if (app) {
	//$('body').append('result:no menu bar');
} else {
	$('body').prepend('result:add menu bar');
}

	// Connection Tabs 
	$('.connection-tabs .tab-content:first').show();
	$(document.body).on('click', '.connection-tabs .tab', function (event) {
		var getIndex = $(this).index();
		$('.connection-tabs .tab-content').hide();
		$('.tab-current').removeClass('tab-current');
		$(this).addClass('tab-current');
		$('.connection-tabs .tab-content:eq('+getIndex+')').show();
		event.preventDefault();
	});


	// Toggle Panels In Left Sidebar
	$(document.body).on('click', '.panel-toggle', function (event) {
		$(this).parents('.panel').find('.panel-contents').slideToggle(600, 'easeOutExpo');
		$(this).toggleClass('flipped');
		event.preventDefault();
	});

	// Toggle Panel Groups In Left Sidebar 
	$(document.body).on('click', '.panel-title-tab', function (event) {
		var itemNumber = ($(this).index())-1;
		$(this).parents('.panel').find('.panel-title-active').removeClass('panel-title-active');
		$(this).addClass('panel-title-active');
		$(this).parents('.panel').find('.panel-group').hide();
		$(this).parents('.panel').find('.panel-group:eq('+itemNumber+')').show();
	});

	// Toggle Entire Right Sidebar
	$(document.body).on('click', '.panel-toggler', function (event) {
		$('.sidebar-right-scroll, .sidebar-right').toggleClass('sidebar-right-closed');
		$(this).toggleClass('flipped');
	});

	// Toggle Titles In Right Sidebar
	$(document.body).on('click', '.sidebar-right-scroll>ul>li>a', function (event) {
		$(this).parent().find('>ul').slideToggle(600, 'easeOutExpo');
		$(this).find('i').toggleClass('flipped');
		event.preventDefault();
	});

	// Add A Page
	$('#addpage').click(function(event){
		$('<li class="page-add"><a href="#"><i class="fa fa-times page-add-cancel"></i><i class="fa fa-check page-add-confirm"></i><input type="text" placeholder="Add Page Name..." /></a></li>').appendTo('.pages>ul');
		$('.page-add').slideDown(300, 'easeOutExpo');
		event.preventDefault();
	});

	// Cancel Adding A Page
	$(document.body).on('click', '.page-add-cancel', function (event) {
		$(this).parent().slideUp(300, 'easeOutExpo', function(){
			$(this).remove();
		});
		event.preventDefault();
	});

	// Confirm Page Add
	$(document.body).on('click', '.page-add-confirm', function (event) {
		var pageName = $(this).parent().find('input').val();
		$(this).parent().empty().append("<i class='fa fa-reorder sort-drag'></i><i class='fa fa-file'></i> "+pageName+"");
		event.preventDefault();
	});

	// Draggable Pages
	$(".draggable-parent").sortable({
		handle: ".sort-drag",
		axis: "y",
		opacity:".5",
		toleranceElement: '> div',
	});

	// Connection Dropdown
	$('.con-result').click(function(){
		$('.con-panel').fadeToggle();
		$(this).toggleClass('con-result-active');
	});

	// Special Content
	$(document.body).on('click', '.special-btn', function (event) {
		$(this).toggleClass('special-open');
		$('.special-content').slideToggle();
		event.preventDefault();
	});

	// Toggle Spell Check Function
	$(document.body).on('click', '#spellcheckToggle', function (event) {
		if($(this).hasClass('on')){
			$(this).removeClass();
			$('.editor-text').attr('spellcheck','false');
			var editorText = $('.editor-text').html();
			$('.editor-text').html('').html(editorText);
		} else {
			$(this).addClass('on');
			$('.editor-text').attr('spellcheck','true');
			var editorText = $('.editor-text').html();
			$('.editor-text').html('').html(editorText);
		}
	});

	// Word Counter
	counter = function() {
		var copyText = $('.editor-text').text();
		$('#stripformat').val(copyText);
		var value = $('#stripformat').val();

		if (value.length == 0) {
			$('#wordCount').html(0);
			$('#totalChars').html(0);
			$('#charCount').html(0);
			$('#charCountNoSpace').html(0);
			return;
		}

		var regex = /\s+/gi;
		var wordCount = value.trim().replace(regex, ' ').split(' ').length;
		var totalChars = value.length;
		var charCount = value.trim().length;
		var charCountNoSpace = value.replace(regex, '').length;
		var readTime = Math.round(((wordCount / 270))*2)/2;

		$('#read-time').html(readTime+" min(s)");
		$('#words').html(wordCount);		
		$('#characters').html(charCountNoSpace);
	};
	counter();

	// SEO Length Warning on page load
	$(document).on('input', '.seo-input', function (event) {
		seolength( $(this) );
	});
	// SEO Length Warning on change
	$('.seo-input').each(function() {
		seolength( $(this) );
	});
	// SEO Length Warning function
	function seolength(thisObj) {
		var max = thisObj.attr('maxLength'),
			current = thisObj.val(),
			current = current.length;
		if (current > 0) {
			thisObj.parent().find('.seo-length').html("("+current+"/"+max+")");
			thisObj.parent().find('.warn').empty();
		} else {
			thisObj.parent().find('.warn').html("<i class='fa fa-warning' rel='Please enter text.'></i> ");
		}
	}

	$(document.body).on('mouseover', '.panel .fa-warning', function (event) {
		var getText = $(this).attr('rel');
		$(this).parent().append('<div class="tooltip">'+getText+'</div>');
	});
	$(document.body).on('mouseout', '.panel .fa-warning', function (event) {
		$('.tooltip').remove();
	});
	

	$('body').on('focus', '[contenteditable]', function() {
	    var $this = $(this);
	    $this.data('before', $this.html());
	    return $this;
	}).on('blur keyup paste input', '[contenteditable]', function() {
	    var $this = $(this);
	    if ($this.data('before') !== $this.html()) {
	        $this.data('before', $this.html());
	        counter();
	    }
	    return $this;
	});

});