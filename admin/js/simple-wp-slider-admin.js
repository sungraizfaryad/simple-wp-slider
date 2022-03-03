(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$('body').on('click', '.wpss-duplicator-field', function (e){
		e.preventDefault();
		let $divClone = $(this).prev('div.wpss-image-upload-wrapper').find('div:last-child').clone(true);
		$divClone.find('input.wpsa-url').val('');
		$divClone.find('input').attr('data-dashlane-rid', Math.floor((Math.random() * 1000000) + 10000));
		$divClone.appendTo($(this).prev('div.wpss-image-upload-wrapper'));
	});

	$('body').on('click', '.wpss-remove-duplicator-field', function (e){
		e.preventDefault();
		if($(this).closest('td').find('.wpss-image-upload-wrapper div').length > 1){
			$(this).closest('div').remove();
		}else{
			alert("You can't remove one item.");
		}
	});

	$('.wpss-image-upload-wrapper').sortable({
		axis: "y",
		handle: ".wpss-move-duplicator-field",
	});
	$('.wpss-image-upload-wrapper').disableSelection();
})( jQuery );
