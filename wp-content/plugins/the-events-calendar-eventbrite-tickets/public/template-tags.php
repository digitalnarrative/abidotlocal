<?php


/**
 * Get ticket count for event
 *
 * @since 1.0
 * @author jgabois & Justin Endler
 * @param int $postId the event ID (optional if used in the loop)
 * @return int the number of tickets for an event
 */
function tribe_eb_get_ticket_count( $postId = null ) {
	$api = EventbriteAPI::instance();
	$postId = TribeEvents::postIdHelper( $postId );
	$event = $api->get_event( $post_id );
	$count = 0;
	if ( ! empty( $event->ticket_classes ) ){
		$count = count( $event->ticket_classes );
	}

	return apply_filters( 'tribe_eb_get_ticket_count', $retval );
}

/**
 * Returns the Eventbrite id for the post/event
 *
 * @since 1.0
 * @author jgabois & Justin Endler
 * @param int $postId the event ID (optional if used in the loop)
 * @return int event id, false if no event is associated with post
 */
function tribe_eb_get_id( $postId = null) {
	$api = EventbriteAPI::instance();
	return $api->get_event_id($postId);
}

/**
 * Determine if an event is live at Eventbrite
 *
 * @since 1.0
 * @author jgabois & Justin Endler
 * @param int $postId the event ID (optional if used in the loop)
 * @return bool true if live
 */
function tribe_eb_is_live_event( $postId = null) {
	$api = EventbriteAPI::instance();
	return $api->is_live($postId);
}

/**
 * Determine an event's Eventbrite status
 *
 * @since 1.0
 * @author jkudish
 * @param int $postId the event ID (optional if used in the loop)
 * @return string the event status
 */
function tribe_eb_event_status( $postId = null) {
	$api = EventbriteAPI::instance();
	return $api->get_event_status($postId);
}


/**
 * Outputs the Eventbrite ticket iFrame. The post in question must be registered with Eventbrite
 * and must have at least one ticket type associated with the event.
 *
 * @since 1.0
 * @author jkudish
 * @param int $postId the event ID (optional if used in the loop)
 * @return void
 */
function tribe_eb_event( $deprecated = null ) {
	echo Event_Tickets_PRO::print_ticket_form();
}

/**
 * Determine whether to show tickets
 *
 * @since 1.0
 * @author jgabois & Justin Endler
 * @param int $postId the event ID (optional if used in the loop)
 * @return bool
 */
function tribe_event_show_tickets( $postId = null ) {
	$postId = TribeEvents::postIdHelper( $postId );
	return apply_filters( 'tribe_event_show_tickets', ( get_post_meta($postId, '_EventShowTickets', true) == 'yes' ) );
}

/**
 * Display the Eventbrite attendee data for a specific event.
 *
 * @deprecated since 3.9.1
 * @todo       remove this function 2 releases after being deprecated
 *
 * @param string $id       Eventbrite event ID (not the ID of the local event post)
 * @param object $user     Eventbrite username
 * @param string $password corresponding password
 */
function tribe_eb_event_list_attendees( $id, $user, $password ) {
	_deprecated_function( __FUNCTION__, '3.9.1' );
}