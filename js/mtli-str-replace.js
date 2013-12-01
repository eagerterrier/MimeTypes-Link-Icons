/* Javascript functions for plugin: MimeTypes Link Icons */
/*
 @todo look into issue: http://wordpress.org/support/topic/problem-with-images-13?replies=5
 Let us know how you get on. We can always use a jquery wildcard selector instead of classname in a later version of the plugin, so
 jQuery(this).parents('.myexcludedclassname').length)
 becomes
 jQuery(this).parents('[class^=wp-image-]').length /* matches all images on my theme
 */
/* Using alternative to jQuery(document).ready(function() as WP3.2.1/jQuery1.6.1 does not seem to like it
 */
(function(){

	if(i18n_mtli.enable_async == 1){

        if(i18n_mtli.enable_async_debug && typeof console == "undefined") {
            window.console = {
                log: function () {}
            };
        }

		var contentObj = jQuery('#content');
        if (contentObj.length == 0) {
            contentObj = jQuery('.status-publish:first').parent();
        }
        
		var contentLinks = contentObj.find('a');
		if(i18n_mtli.enable_async_debug) { console.log( '# of links found: '+contentLinks.length ); }

		if(i18n_mtli.oldwp == 1){
			i18n_mtli.mime_array = i18n_mtli.mime_array.split(',');
		}
		if(i18n_mtli.enable_async_debug) { console.log( '# of mime types: '+i18n_mtli.mime_array.length ); }

		jQuery.each(i18n_mtli.mime_array, function( i, elm ) {
		if(i18n_mtli.enable_async_debug) { console.log( 'Evaluating mime_type '+i+' : '+elm ); }
//			content_obj.find('[href*=".'+elm+'"]').addClass('mtli_attachment mtli_'+elm); // works, but case-sensitive and .ai also matches .air

			var reg = new RegExp('^([^"#]+\\.'+elm+')(#[^" ]+$|$)','i');
			
			var filtered = contentLinks.filter(function(){
				var thishref = jQuery(this).attr('href');
				if(typeof(thishref)==='undefined') { return false; }
				return ( jQuery(this).attr('href').search(reg) >= 0 );
			}).addClass('mtli_attachment mtli_'+elm);

			if(i18n_mtli.enable_async_debug) { console.log( 'Evaluating mime_type '+i+' : '+elm+"\t\t\t"+'filtered links: '+filtered.length ); }
		});
	}


	if(i18n_mtli.hidethings == 1){
		if(i18n_mtli.enable_async_debug) { console.log( 'nr of elements found : '+jQuery(i18n_mtli.avoid_selector).length ); }
		jQuery(i18n_mtli.avoid_selector).each(function(){
			if(i18n_mtli.enable_async_debug) { console.log( 'testing elm '+index+' : '+elm ); }
			// If the current element has the mtli_attachment class, remove it
			if( jQuery(this).hasClass('mtli_attachment') ) {
				if(i18n_mtli.enable_async_debug) { console.log( "\t"+'element has class mtli_attachment' ); }
				jQuery(this).removeClass('mtli_attachment').css('background-image','none');
				if( jQuery(this).attr('rel') != undefined && jQuery(this).attr('rel').indexOf('mtli_filesize') >= 0 ) {
					if(i18n_mtli.enable_async_debug) { console.log( "\t\t"+'rel mtli_filesize found' ); }
					jQuery(this).removeAttr('rel');
				}
			}
			// If the current element has descendants with the mtli_attachment class, remove it
			if(i18n_mtli.enable_async_debug) { console.log( "\t"+'# of jQuery(this).find(.mtli_attachment) : '+jQuery(this).find('.mtli_attachment').length ); }
			if(i18n_mtli.enable_async_debug) { console.log( "\t"+'# of jQuery(this).find([rel^="mtli_filesize"]) : '+jQuery(this).find('[rel^="mtli_filesize"]').length ); }
			jQuery(this).find('.mtli_attachment').removeClass('mtli_attachment').css('background-image','none');
			jQuery(this).find('[rel^="mtli_filesize"]').removeAttr('rel');
		});
	}

})(jQuery);
