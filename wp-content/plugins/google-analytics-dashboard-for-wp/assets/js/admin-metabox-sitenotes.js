jQuery(document).ready(function() {

	if (jQuery('#exactmetrics-metabox-site-notes').length === 0) {
		return;
	}

	jQuery('#exactmetrics-metabox-site-notes input[name="_exactmetrics_sitenote_active"]').change(function () {
		let checked = jQuery(this).is(':checked');
		if ( checked ) {
			jQuery('textarea[name="_exactmetrics_sitenote_note"]').text( sprintf( 'Published: %s', jQuery('#title').val() ) );

			jQuery('#site-notes-active-container').slideDown('slow');
		} else {
			jQuery('#site-notes-active-container').slideUp('slow');
		}
	});

	jQuery('#title').change(function(){
		let current_title = jQuery(this).val();

		jQuery('textarea[name="_exactmetrics_sitenote_note"]').text( sprintf( 'Published: %s', current_title ) );
	})
});
