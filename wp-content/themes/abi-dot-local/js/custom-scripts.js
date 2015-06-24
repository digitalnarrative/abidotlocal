//Custom scripts

jQuery( document ).ready( function () {

	jQuery( '#wpadminbar a.x' ).click( function () {
		jQuery( document ).trigger( 'menu-close.buddyboss' );
	} );

	// fix for group filter
	jQuery( ".activity-filter li" ).click( function ( event ) {
		var activity_filter_by = jQuery( this ).attr( 'data-value' );
		jQuery( "ul.activity-filter li" ).removeClass( "current" );
		jQuery( this ).addClass( "current" );
		jQuery( '#activity-filter-by' ).find( 'option[value="' + activity_filter_by + '"]' ).attr( "selected", true );
		jQuery( '#activity-filter-by' ).trigger( 'change' );
		event.preventDefault();
	} );

	jQuery( window ).load( function () {
		// For our add photo, progress and preview area we rely
		// on #what-new-content
		var whats_new_content = jQuery( '#whats-new-options' );

		// Add photo button + progress area
		var add_photo = jQuery( '#buddyboss-media-add-photo' );

		if ( jQuery( add_photo ).length && jQuery( whats_new_content ).length ) {
			jQuery( whats_new_content ).prepend( jQuery( add_photo ).html() );
		}

	} );


	jQuery( '#wpadminbar .dashboard li a,#wp-admin-bar-my-account-buddypress li a' ).click( function ( e ) {
		// if the link has children, when clicked it shows the children instead of going somewhere
		if ( jQuery( this ).closest( 'li' ).find( '.ab-sub-wrapper' ).length != 0 ) {
			e.preventDefault();
			jQuery( 'li#wp-admin-bar-my-account li > .ab-sub-wrapper, ul.dashboard .ab-sub-wrapper' ).css( 'display', 'none' );
			jQuery( this ).closest( 'li' ).find( '.ab-sub-wrapper' ).toggle();
		}
	} );

	jQuery( '#respond form textarea' ).on( 'focus', function () {
		jQuery( this ).animate( { height: 200 }, 200 );
	} );
	jQuery( '#respond form textarea' ).on( 'focusout', function () {
		if ( jQuery( this ).val() == '' ) {
			jQuery( this ).animate( { height: 40 }, 200 );
		}
	} );
	jQuery( '.wp-caption .wp-caption-text' ).each( function () {
		var image_width = jQuery( this ).closest( '.wp-caption' ).find( 'img' ).width() - 40;
		jQuery( this ).css( 'width', image_width + 'px' );
	} );
	//updating group role
	jQuery( '.abi-group-role-editor' ).on( 'change', function () {

		var user_id = jQuery( this ).parents( 'li' ).find( '.abi-meta-user-id' ).val();
		var group_id = jQuery( this ).parents( 'li' ).find( '.abi-meta-group-id' ).val();

		jQuery.post( ajaxurl, {
			action: 'abi_group_role_update',
			role: jQuery( this ).val(),
			user_id: user_id,
			group_id: group_id
		},
		function ( response ) {
			jQuery( '#buddypress #item-header' ).append( '<div id="message" class="bp-template-notice updated"><p>Role Updated</p></div>' );
			window.scrollTo( 0, 0 );
		} );

	} );

	/* New posts */
	jQuery( '#aw-custom-announcements-submit' ).on( 'click', function () {

		var last_date_recorded = 0,
				button = jQuery( this ),
				form = button.closest( 'form#custom-announcements-form' );

		/* Remove any errors */
		jQuery( 'div.error' ).remove();

		/* Default POST values */
		object = 'groups';
		item_id = jQuery( '#custom-announcements-post-in' ).val();
		content = jQuery( '#custom-announcements' ).val();
		firstrow = jQuery( '#buddypress ul.activity-list li' ).first();
		activity_row = firstrow;
		timestamp = null;

		// Checks if at least one activity exists
		if ( firstrow.length ) {

			if ( activity_row.hasClass( 'load-newest' ) ) {
				activity_row = firstrow.next();
			}

			timestamp = activity_row.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
		}

		if ( timestamp ) {
			last_date_recorded = timestamp[1];
		}

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = jQuery( '#custom-announcements-post-object' ).val();
		}

		jQuery.post( ajaxurl, {
			action: 'announcement_post_update',
			'cookie': bp_get_cookies(),
			'_wpnonce_post_update': jQuery( '#_wpnonce_post_update' ).val(),
			'content': content,
			'object': object,
			'item_id': item_id,
			'since': last_date_recorded,
			'_bp_as_nonce': jQuery( '#_bp_as_nonce' ).val() || ''
		},
		function ( response ) {

			location.reload();

		} );

		return false;
	} );


} );

// Members list filter
$( function () {

	var $active = $( "#members-order-by" ).val();

	if ( 'newest' === $active ) {
		$( '.select-newest' ).addClass( 'active' );
	}

	else if ( 'alphabetical' === $active ) {
		$( '.select-alphabet' ).addClass( 'active' );
	}

	$( '.select-alphabet' ).on( 'click', function ( e ) {
		e.preventDefault();
		var wantedValue = $( '#alphabet' ).attr( 'value' );
		$( '#members-order-by' ).val( wantedValue ).trigger( 'change' );
		$( '.select-newest' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
	} );

	$( '.select-newest' ).on( 'click', function ( e ) {
		e.preventDefault();
		var wantedValue = $( '#newest' ).attr( 'value' );
		$( '#members-order-by' ).val( wantedValue ).trigger( 'change' );
		$( '.select-alphabet' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
	} );

} );

// Load more
$( document ).ready( function () {
	size_li = $( "#members-list li" ).size();
	x = 30;
	$( '#members-list li:lt(' + x + ')' ).show();
	$( '#loadMore' ).click( function () {
		x = ( x + 12 <= size_li ) ? x + 12 : size_li;
		$( '#members-list li:lt(' + x + ')' ).show();
	} );
} );