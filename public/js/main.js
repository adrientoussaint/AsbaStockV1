$.noConflict();
var editor;
jQuery(document).ready(function($) {

	"use strict";

	[].slice.call( document.querySelectorAll( 'select.cs-select' ) ).forEach( function(el) {
		new SelectFx(el);
	} );

	jQuery('.selectpicker').selectpicker;


	$('#menuToggle').on('click', function(event) {
		$('body').toggleClass('open');
	});

	$('.search-trigger').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		$('.search-trigger').parent('.header-left').addClass('open');
	});

	$('.search-close').on('click', function(event) {
		event.preventDefault();
		event.stopPropagation();
		$('.search-trigger').parent('.header-left').removeClass('open');
	});
	$.fn.editable.defaults.mode = 'inline';
	$.fn.editableform.buttons  = "<button type='submit' class='editable-submit btn btn-primary'><i class='fa fa-check'></i></button><button type='button' class='btn btn-danger editable-cancel'><i class='fa fa-times'></i></button>";
	$('.editable').editable({
		success: function(response, newValue){
			$.toast({
				heading: 'Information',
				text: response,
				icon: 'info',
				loader: true,        // Change it to false to disable loader
				loaderBg: '#9EC600'  // To change the background
			})
		}
	});
	

	// $('.user-area> a').on('click', function(event) {
	// 	event.preventDefault();
	// 	event.stopPropagation();
	// 	$('.user-menu').parent().removeClass('open');
	// 	$('.user-menu').parent().toggleClass('open');
	// });


});