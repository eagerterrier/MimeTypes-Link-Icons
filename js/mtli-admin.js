/* Javascript functions for plugin: MimeTypes Link Icons */
jQuery(document).ready(function() {

	var fieldSetImages = jQuery('#images');
	var checkboxes = fieldSetImages.find(':checkbox');

	fieldSetImages.before(i18n_mtli.togglebox).after(i18n_mtli.togglebox);

//	var togglers = jQuery('.check-images');
//console.log( 'found '+togglers.length+' togglers' );
//console.log( 'found '+checkboxes.length+' checkboxes' );
	/**
	 * Attach the event handlers
	 */
	jQuery('.check-all').click( function(event) {
//console.log( 'trying to fire check-all event' );
		event.preventDefault();
		checkboxes.attr({
			'checked': 'checked'
		});
		return false;
	});

	jQuery('.uncheck-all').click( function(event) {
//console.log( 'trying to fire *un*check-all event' );
		event.preventDefault();
		checkboxes.removeAttr('checked');
		return false;
	});

});