$(document).ready(function() {

	$(document.body).on('click', '.sidebar-right-scroll>ul>li>a', function (event) {
		$(this).parent().find('>ul').slideToggle(600, 'easeOutExpo');
		$(this).find('i').toggleClass('flipped');
		event.preventDefault();
	});

	$(document.body).on('click', '.panel-title a', function (event) {
		$(this).parents('.panel').find('.panel-contents').slideToggle(600, 'easeOutExpo');
		$(this).find('i').toggleClass('flipped');
		event.preventDefault();
	});

	$('#addpage').click(function(event){
		$('<li class="page-add"><a href="#"><i class="fa fa-times page-add-cancel"></i><i class="fa fa-check page-add-confirm"></i><input type="text" placeholder="Add Page Name..." /></a></li>').appendTo('.pages>ul');
		$('.page-add').slideDown(300, 'easeOutExpo');
		event.preventDefault();
	});

	$(document.body).on('click', '.page-add-cancel', function (event) {
		$(this).parent().slideUp(300, 'easeOutExpo', function(){
			$(this).remove();
		});
		event.preventDefault();
	});

	$(document.body).on('click', '.page-add-confirm', function (event) {
		var pageName = $(this).parent().find('input').val();
		$(this).parent().empty().append("<i class='fa fa-reorder sort-drag'></i><i class='fa fa-file'></i> "+pageName+"");
		event.preventDefault();
	});

	$('.con-result').click(function(){
		$('.con-panel').fadeToggle();
		$(this).toggleClass('con-result-active');
	});

	$(".draggable-parent").sortable({
		handle: ".sort-drag",
		axis: "y",
		opacity:".5",
		toleranceElement: '> div',
	});

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