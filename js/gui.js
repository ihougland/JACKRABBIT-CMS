

$(document).ready(function() {

	/* Add Header?
	var urlParam = function(name, w){
		w = w || window;
		var rx = new RegExp('[\&|\?]'+name+'=([^\&\#]+)'),
			val = w.location.search.match(rx);
		return !val ? '':val[1];
	}
	var app = urlParam('app'),
		appCookie = $.cookie('isApp');
	if (app == true){

	} else if (appCookie == true || )
		$('.main-wrap').prepend('<header><div class="head-title">JACKRABBIT<span>CMS</span></header>');
		$('.table').css('height','calc(100% - 100px)');
	}
	*/

	// Menu Bar Dropdowns
	$(document.body).on('mousedown', '.menu-bar>ul>li>a', function (event) {
		$('.menu-bar > ul > li > ul').slideUp(150);
		if ($(this).parent().find('ul').length ){
			if ($(this).parent().find('> ul').is(":hidden")) {
				$(this).parent().find('> ul').slideDown(150);
			}
		}
		event.preventDefault();
	});

	closeDropdowns = function() {
		$('.menu-bar > ul > li > ul').slideUp(150);
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


	// Draggable Pages https://github.com/ilikenwf/nestedSortable
	pagesSortable = function() {
		$('.draggable-parent').nestedSortable({
			handle: '.sort-drag',
			listType: 'ul',
			items: 'li',
			opacity: .3,
			tabSize: 2,
			maxLevels: 3,
			placeholder: 'placeholder',
			toleranceElement: '> div'
		});
	}
	pagesSortable();

	// Add A Page
	$('#addpage').click(function(event){
		$('<li class="page-add"><div><a href="#"><i class="fa fa-times page-add-cancel"></i><i class="fa fa-check page-add-confirm"></i><input type="text" placeholder="Add Page Name..." /></a></div></li>').appendTo('.pages>ul');
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
		$('.page-add').removeClass().addClass('draggable');
		$('.draggable-parent').nestedSortable('refresh');
		event.preventDefault();
	});

	// Connection Dropdown
	$('.con-result').click(function(){
		$('.con-panel').fadeToggle();
		$(this).toggleClass('con-result-active');
	});

	// Open in iFrame
	$(document.body).on('click', '.in-iframe', function (event) {
		event.stopPropagation();
		event.preventDefault();
		closeDropdowns();
		var getUrl = $(this).attr('href');
		$('body').append('<div class="modal"><a href="#" class="modal-close"><i class="fa fa-chevron-left"></i> DONE</a></div>');
		$('.main-wrap').addClass('blurout');
		$('.modal').show().css('opacity');
		$('.modal').css('opacity','1');
		setTimeout(function(){ 
			$('.modal').append('<div class="modal-content"><iframe id="jr-iframe" src="'+getUrl+'"></iframe></div>');
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
	});

	$(document.body).on('click', '.modal, .modal-close', function (event) {
		$('.modal-close').fadeOut();
		$('.modal').css('background-image','none');
		$('.modal-content').css({
			'transform':'',
			'opacity':'0'
		});
		setTimeout(function(){ 
			$('.modal').fadeOut(function(){
				$('.modal').remove();
			})
			$('.main-wrap').removeClass('blurout');
		}, 550);
	});

	// addon Content
	$(document.body).on('click', '.addon-btn', function (event) {
		$(this).toggleClass('addon-open');
		$('.addon-selector').toggleClass('addon-content-open');
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
		if ($(".editor-text").length){
		var copyText = $('.editor-text').text();
		$('#stripformat').val(copyText);
		var value = $('#stripformat').val();

		if (value.length == 0) {
			$('#wordCount').html('0');
			$('#totalChars').html('0');
			$('#charCount').html('0');
			$('#charCountNoSpace').html('0');
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
	}
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
			thisObj.parent().find('.warn').html("<i class='fa fa-warning' rel='Nothing entered.'></i> ");
			thisObj.parent().find('.seo-length').empty();
		}
	}

	// Tool Tips
	$(document.body).on('mouseover', '.fa-warning, .fa-question-circle', function (event) {
		var getText = $(this).attr('rel');
		$(this).parent().append('<div class="tooltip">'+getText+'</div>');
	});
	// Remove Tool Tips
	$(document.body).on('mouseout', '.fa-warning, .fa-question-circle', function (event) {
		$('.tooltip').remove();
	});
	
	// Input Label Styles
	$(document.body).on('focus', '.form-field-text, .form-field-textarea', function (event) {
		$(this).next('label').css('color','#8CAB28');
	});
	$(document.body).on('blur', '.form-field-text, .form-field-textarea', function (event) {
		$(this).next('label').css('color','');
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