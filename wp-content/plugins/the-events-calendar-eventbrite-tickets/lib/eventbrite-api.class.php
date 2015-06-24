<?php
/**
 * EventBrite API
 */

class EventbriteAPI {

	protected static $instance;

	public $key;
	public $secret;
	public $token;
	public $user_id;

	public static $date_format = 'Y-m-d\TH:i:s\Z';

	public static $expire = 900;

	/**
	 * inforce singleton factory method
	 *
	 * @since 4.0
	 * @author bordoni
	 * @return Tribe__Events__Tickets__Eventbrite__API
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}

	public function __construct(){
		$this->key = apply_filters( 'tribe-eventbrite-key', TribeEvents::instance()->getOption( 'eventbrite-app_key', null ) );
		$this->secret = apply_filters( 'tribe-eventbrite-secret', TribeEvents::instance()->getOption( 'eventbrite-client_secret', null ) );
		$this->token = apply_filters( 'tribe-eventbrite-token', TribeEvents::instance()->getOption( 'eventbrite-token', null ) );
		$this->user_id = apply_filters( 'tribe-eventbrite-user_id', TribeEvents::instance()->getOption( 'eventbrite-user_id', null ) );
	}

	public function is_ready() {
		if ( empty( $this->key ) || ! $this->key  ){
			return false;
		}

		if ( empty( $this->secret ) || ! $this->secret ){
			return false;
		}

		if ( empty( $this->token ) || ! $this->token  ){
			return false;
		}

		if ( empty( $this->user_id ) || ! $this->user_id  ){
			return false;
		}

		return true;
	}

	/**
	 * Get the API base URL
	 *
	 * @uses   apply_filters
	 * @since 4.0
	 *
	 * @param  string $endpoint Append an Endpoint to make it easier to create URL
	 * @return string           Return the Concat of Base URL and Endpoint
	 */
	public function get_base_url( $endpoint = '', $token = null ){
		if ( ! $this->is_ready() ){
			$this->key = apply_filters( 'tribe-eventbrite-key', TribeEvents::instance()->getOption( 'eventbrite-app_key', null ) );
			$this->secret = apply_filters( 'tribe-eventbrite-secret', TribeEvents::instance()->getOption( 'eventbrite-client_secret', null ) );
			$this->token = apply_filters( 'tribe-eventbrite-token', TribeEvents::instance()->getOption( 'eventbrite-token', null ) );
			$this->user_id = apply_filters( 'tribe-eventbrite-user_id', TribeEvents::instance()->getOption( 'eventbrite-user_id', null ) );
		}

		$baseurl = apply_filters( 'tribe-eventbrite-base_api_url', 'https://www.eventbriteapi.com/v3/' );
		if ( is_null( $token ) ){
			$token = $this->token;
		}
		return add_query_arg( array( 'token' => $token ), $baseurl . $endpoint );
	}

	/**
	 * Get the base URL
	 *
	 * @link   http://developer.eventbrite.com/docs/auth/
	 * @uses   apply_filters
	 * @uses   wp_parse_args
	 * @uses   add_query_arg
	 * @since 4.0
	 *
	 * @param  array  $args     The URL encoded arguments
	 * @param  string $endpoint Endpoint used
	 * @return string           Return the Concat the Base URL for the auth
	 */
	public function get_auth_url( $args = array(), $endpoint = 'authorize' ){
		$baseurl = apply_filters( 'tribe-eventbrite-auth_url', 'https://www.eventbrite.com/oauth/' );
		$url = $baseurl . $endpoint;

		$defaults = array();
		$args = wp_parse_args( $args, $defaults );

		return add_query_arg( $args, $url );
	}

	public function request( $url, $args = array() ){
		if ( 'get' === $args ){
			$response = wp_remote_get( $url );
		} else {
			$response = wp_remote_post( $url, array( 'timeout' => 15, 'body' => $args ) );
		}

		if ( ! is_wp_error( $response ) ){
			return (object) json_decode( $response['body'] );
		} else {
			return $response;
		}
	}

	public function authorize( $code = null ){
		if ( is_null( $code ) ){
			$args = array(
				'response_type' => 'code',
				'client_id' => TribeEvents::instance()->getOption( 'eventbrite-app_key' ),
			);
			exit( wp_redirect( $this->get_auth_url( $args ) ) );
		} else {
			$code = esc_attr( wp_kses( $code, array() ) );
			TribeEvents::instance()->setOption( 'eventbrite-auth_code', $code );

			$args = array(
				'code' => $code,
				'client_secret' => $this->secret,
				'client_id' => $this->key,
				'grant_type' => 'authorization_code',
			);

			$answer = $this->request( $this->get_auth_url( array(), 'token' ), $args );
			if ( ! empty( $answer->error ) ){
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ){
					// Make this error translatable later on
					return new WP_Error( 'tribe-eventbrite-authorize_fail', $answer->error_description );
				}
				return false;
			} elseif ( ! empty( $answer->access_token ) ){
				TribeEvents::instance()->setOption( 'eventbrite-token', $answer->access_token );
				$response = $this->request( $this->get_base_url( 'users/me' ), 'get' );

				if ( ! is_wp_error( $response ) && isset( $response->id ) ) {
					TribeEvents::instance()->setOption( 'eventbrite-user_id', $response->id );
					return true;
				}
			}

			return false;
		}
	}

	/**
	 * sends the organizer data to Eventbrite API
	 *
	 * @since  4.0
	 * @author bordoni
	 * @param  int|WP_Post $event The event ID or an WP_Post Object
	 * @return mixed the response string or an error on failure
	 */
	public function sync_organizer( $post ){
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		$args = array(
			'organizer.name' => '',
			'organizer.description.html' => '',
			// 'organizer.logo.id' => null,
		);

		if ( tribe_is_organizer( $post->ID ) ){
			$organizer = $post;
		} else {
			$organizer = get_post( get_post_meta( $post->ID, '_EventOrganizerID', true ) );
		}

		if ( $organizer ) {
			$eb_organizer_id = get_post_meta( $organizer->ID, '_OrganizerEventBriteID', true );
			$name            = get_the_title( $organizer->ID );
			$content         = apply_filters( 'the_content', $organizer->post_content );
			$email           = tribe_get_organizer_email( $post->ID );
			$website         = tribe_get_organizer_link( $post->ID, false, false );
			$phone           = tribe_get_organizer_phone( $post->ID );
		} else {
			// if there's no organizer associated to this event, let's make the author the organizer
			if ( ! empty( $post->post_author ) ) {
				$user = new WP_User( $post->post_author );
				if ( $user->exists() ) {
					$eb_organizer_id = get_user_meta( $user->ID, '_OrganizerEventBriteID', true );
					$name            = $user->display_name;
					$email           = $user->user_email;
					$website         = $user->user_url;
					$content         = $user->description;
				}
			}
		}

		if ( empty( $name ) || ! $name ) {
			$name = apply_filters( 'tribe-eventbrite-no_organizer_name', __( 'Unnamed Organizer', 'tribe-eventbrite' ) );
		}

		// if no stored EB Organizer ID, let's search it just to be safe
		if ( ! isset( $eb_organizer_id ) || ! $eb_organizer_id ) {
			$organizers = $this->request( $this->get_base_url( 'users/' . $this->user_id . '/organizers' ), 'get' );
			if ( ! empty( $organizers ) && isset( $organizers->organizers ) ) {
				$organizer_names = wp_list_pluck( $organizers->organizers, 'name' );
				$organizer_index = array_search( $name, $organizer_names );
				if ( false !== $organizer_index ) {
					$eb_organizer_id = $organizers->organizers[ $organizer_index ]->id;
				}
			}
		}

		$mode = ( ! empty( $eb_organizer_id ) ? 'update' : 'create' );

		$args['organizer.name'] = $name;

		if ( ! empty( $email ) ){
			$args['organizer.description.html'] .= '<p>' . esc_attr__( 'Email:', 'events-eventbrite' ) . $email . '</p>' . "\r\n";
		}

		if ( ! empty( $website ) ){
			$args['organizer.description.html'] .= '<p>' . esc_attr__( 'Website:', 'events-eventbrite' ) . $website . '</p>' . "\r\n";
		}

		if ( ! empty( $phone ) ){
			$args['organizer.description.html'] .= '<p>' . esc_attr__( 'Phone:', 'events-eventbrite' ) . $phone . '</p>' . "\r\n";
		}

		if ( ! empty( $content ) ){
			$args['organizer.description.html'] .= $content;
		}

		if ( 'create' === $mode ) {
			$response = $this->request( $this->get_base_url( 'organizers' ), $args );
		} else {
			$response = $this->request( $this->get_base_url( 'organizers/' . $eb_organizer_id ), $args );
			if ( ! empty( $response->error ) && 'ARGUMENTS_ERROR' === $response->error ){
				if ( $organizer ){
					delete_post_meta( $organizer->ID, '_OrganizerEventBriteID', $eb_organizer_id );
				} else {
					delete_user_meta( $user->ID, '_OrganizerEventBriteID', $eb_organizer_id );
				}
				$eb_organizer_id = null;
				$response = $this->request( $this->get_base_url( 'organizers' ), $args );
			}
		}

		$eb_organizer_id = ( isset( $response->id ) ? $response->id : $eb_organizer_id );

		if ( empty( $user ) ) {
			update_post_meta( $organizer->ID, '_OrganizerEventBriteID', $eb_organizer_id );
			return $eb_organizer_id;
		} else {
			// This might create problems, revision this - @bordoni
			update_user_meta( $user->ID, '_OrganizerEventBriteID', $eb_organizer_id );
			return $eb_organizer_id;
		}

		return false;
	}

	public function sync_venue( $post ){
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		if ( tribe_is_venue( $post->ID ) ){
			$venue = $post;
		} elseif ( tribe_is_event( $post->ID ) ) {
			$venue_id = get_post_meta( $post->ID, '_EventVenueID', true );
			if ( $venue_id ){
				$venue = get_post( $venue_id );
			}
		}

		if ( ! isset( $venue ) ){
			return false;
		}

		$eb_venue_id = get_post_meta( $venue->ID, '_VenueEventBriteID', true );
		$args = array(
			'venue.name' => get_the_title( $venue->ID ),
			'venue.address.address_1' => get_post_meta( $venue->ID, '_VenueAddress', true ),
			'venue.address.city' => get_post_meta( $venue->ID, '_VenueCity', true ),
			'venue.address.region' => tribe_get_region( $venue->ID ),
			'venue.address.postal_code' => get_post_meta( $venue->ID, '_VenueZip', true ),
			'venue.address.latitude' => get_post_meta( $venue->ID, '_VenueLat', true ),
			'venue.address.longitude' => get_post_meta( $venue->ID, '_VenueLng', true ),
		);

		$country = $this->get_country_code( $venue->ID );
		if ( $country ){
			$args['venue.address.country'] = $country;
			// $args['venue.address.country_name'] = $country;

			if ( 'US' !== $args['venue.address.country'] ){
				unset( $args['venue.address.region'] );
			}
		}

		if ( empty( $args['venue.name'] ) || ! $args['venue.name'] ) {
			$args['venue.name'] = apply_filters( 'tribe-eventbrite-no_venue_name', __( 'Unnamed Venue', 'tribe-eventbrite' ) );
		}

		if ( empty( $args['venue.address.latitude'] ) ){
			unset( $args['venue.address.latitude'] );
		}

		if ( empty( $args['venue.address.longitude'] ) ){
			unset( $args['venue.address.longitude'] );
		}

		// if no stored EB Organizer ID, let's search it just to be safe
		if ( ! isset( $eb_venue_id ) || ! $eb_venue_id ) {
			$venues = $this->request( $this->get_base_url( 'users/' . $this->user_id . '/venues' ), 'get' );
			if ( ! empty( $venues ) && isset( $venues->venues ) ) {
				$venue_names = wp_list_pluck( $venues->venues, 'name' );
				$venue_index = array_search( $args['venue.name'], $venue_names );
				if ( false !== $venue_index ) {
					$eb_venue_id = $venues->venues[ $venue_index ]->id;
				}
			}
		}

		$mode = ( ! empty( $eb_venue_id ) ? 'update' : 'create' );

		if ( 'create' === $mode ) {
			$response = $this->request( $this->get_base_url( 'venues' ), $args );
		} else {
			$response = $this->request( $this->get_base_url( 'venues/' . $eb_venue_id ), $args );
			if ( ! empty( $response->error ) && 'ARGUMENTS_ERROR' === $response->error ){
				delete_post_meta( $venue->ID, '_VenueEventBriteID', $eb_venue_id );
				$eb_venue_id = null;
				$response = $this->request( $this->get_base_url( 'venues' ), $args );
			}
		}

		if ( is_wp_error( $response ) || ! isset( $response->id ) || ! is_numeric( $response->id )  ){
			return false;
		}

		update_post_meta( $post->ID, '_VenueEventBriteID', $response->id );
		update_post_meta( $venue->ID, '_VenueEventBriteID', $response->id );

		return $response->id;
	}

	public function sync_ticket( $post, $args = array() ){
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		if ( ! tribe_is_event( $post->ID ) ){
			return false;
		}

		$eventbrite_id = get_post_meta( $post->ID, '_EventBriteId', true );

		if ( ! is_numeric( $eventbrite_id ) ){
			return false;
		}

		$args = (object) wp_parse_args( $args, array(
			'name' => '',
			'description' => '',
			'start' => '',
			'end' => '',
			'cost' => 0,
			'currency' => 'USD',
			'qty' => 0,
			'type' => 0,
			'include_fee' => false,
		) );

		// Prepare and Sanitize
		if ( ! empty( $args->name ) ){
			$args->name = wp_kses( $args->name, array() );
		}

		if ( isset( $args->start ) ){
			$args->start = intval( $args->start );
		}

		if ( isset( $args->end ) ){
			$args->end = intval( $args->end );
		}

		if ( isset( $args->cost ) ){
			$args->cost = absint( $args->cost );
		}

		if ( isset( $args->currency ) ){
			$args->currency = wp_strip_all_tags( $args->currency, true );
		}

		if ( isset( $args->qty ) ){
			$args->qty = absint( $args->qty );
		}

		if ( isset( $args->type ) ){
			$args->type = absint( $args->type );
		}

		if ( isset( $args->include_fee ) ){
			$args->include_fee = (bool) $args->include_fee;
		}

		$params = array(
			'ticket_class.name' => $args->name,
			'ticket_class.description' => $args->description,
			'ticket_class.quantity_total' => $args->qty,

			'ticket_class.sales_start' => date( self::$date_format, $args->start ),
			// 'ticket_class.sales_start_after' => null,

			// 'ticket_class.minimum_quantity' => null,
			// 'ticket_class.maximum_quantity' => null,

			'ticket_class.auto_hide' => date( self::$date_format, $args->end ),
			// 'ticket_class.auto_hide_before' => null,
			// 'ticket_class.auth_hide_after' => null,
		);

		if ( empty( $args->description ) ) {
			$params['ticket_class.hide_description'] = true;
		}

		if ( 0 === $args->type && ! empty( $args->cost ) && 0 != $args->cost ){
			$params['ticket_class.cost.currency'] = $args->currency;
			$params['ticket_class.cost.value'] = $args->cost;
		} elseif ( 1 === $args->type ) {
			$params['ticket_class.donation'] = true;
		} elseif ( 2 === $args->type ) {
			$params['ticket_class.free'] = true;
		}

		if ( $args->include_fee ){
			$params['ticket_class.include_fee'] = true;
		} else {
			$params['ticket_class.split_fee'] = true;
		}

		$response = $this->request( $this->get_base_url( 'events/' . $eventbrite_id . '/ticket_classes' ), $params );

		if ( ! empty( $response->error ) ){
			switch ( $response->error ) {
				case 'COST_GREATER_THAN_FEE':
					$message_error = esc_attr__( 'When absorbing fees, the price must be greater than the fee, status of the Event is "Draft"', 'tribe-eventbrite' );
					break;

				default:
					$message_error = sprintf( __( 'There was an error when syncing to EventBrite, %s and provide the following information on the Thread: %s', 'tribe-eventbrite' ), sprintf( '<a href="https://theeventscalendar.com/support/forums/forum/events/eventbrite-tickets/" target="_blank">%s</a>', __( 'contact our Support', 'tribe-eventbrite' ) ), sprintf( '<br /><code>%s</code>', str_replace( array( "\r\n", "\r", "\n", "\t" ), '', esc_attr( print_r( $response, true ) ) ) ) );
					break;
			}
			Event_Tickets_PRO::instance()->throw_notice( $post, $message_error );
			return false;
		} else {
			return true;
		}
	}

	public function sync_event( $post, $params = array() ){
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		if ( wp_is_post_revision( $post->ID ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ){
				Event_Tickets_PRO::instance()->throw_notice( $post, __( 'This Event is a revision and cannot sync to Eventbrite.', 'tribe-eventbrite' ) );
			}
			return false;
		} else {
			delete_post_meta( $post->ID, TribeEvents::EVENTSERROROPT );
		}

		$eventbrite_id = get_post_meta( $post->ID, '_EventBriteId', true );

		$start = get_post_meta( $post->ID, '_EventStartDate', true );
		$end = get_post_meta( $post->ID, '_EventEndDate', true );

		// Timezone handling
		$tz_conversion_needed = (bool) get_post_meta( $post->ID, '_EventbriteTZAdjust', true );
		$timezone = (string) get_post_meta( $post->ID, '_EventbriteTZ', true );

		if ( empty( $timezone ) ) {
			$timezone = $this->local_timezone();
		}

		$timezone = apply_filters( 'tribe_eb_event_timezone', $timezone );
		do_action( 'log', 'Timezone to EB', 'tribe-eventbrite', $timezone );

		$params = wp_parse_args( $params, array(
			'tickets' => array(),
			'status' => 'draft',
			'show_tickets' => 'yes',
		) );

		// Prepare/Sanitize $params
		$params['status'] = esc_attr( strtolower( $params['status'] ) );

		if ( ! in_array( $params['show_tickets'], array( 'yes', 'no' ) ) ){
			$params['show_tickets'] = 'yes';
		}

		// Any meta that is fully on our side updates here
		update_post_meta( $post->ID, '_EventShowTickets', $params['show_tickets'] );

		$args = array(
			'event.name.html' => get_the_title( $post ),
			'event.description.html' => apply_filters( 'the_content', $post->post_content ),

			'event.start.utc' => date( self::$date_format, strtotime( $start ) ),
			'event.start.timezone' => $timezone,

			'event.end.utc' => date( self::$date_format, strtotime( $end ) ),
			'event.end.timezone' => $timezone,

			'event.currency' => 'USD',

			'event.online_event' => 0,
			'event.listed' => 1,
			'event.shareable' => 0,
			'event.invite_only' => 0,
			// 'event.password' => null,
			// 'event.capacity' => null,
			'event.show_remaining' => 1,

			// 'event.logo.id' => null,

			// 'event.category_id' => null,
			// 'event.subcategory_id' => null,
			// 'event.format_id' => null,

			'event.organizer_id' => $this->sync_organizer( $post ),
			'event.venue_id' => $this->sync_venue( $post ),
		);

		$mode = ( ! empty( $eventbrite_id ) ? 'update' : 'create' );

		if ( empty( $args['event.name.html'] ) ){
			Event_Tickets_PRO::instance()->throw_notice( $post, __( 'This Event requires a Title to sync to Eventbrite.', 'tribe-eventbrite' ) );
			return false;
		}

		if ( ! isset( $args['event.venue_id'] ) || false === $args['event.venue_id'] ){
			if ( 'create' === $mode ){
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ){
					Event_Tickets_PRO::instance()->throw_notice( $post, __( 'This Event Requires a Venue to sync to Eventbrite', 'tribe-eventbrite' ) );
				}
				return false;
			}
			unset( $args['event.venue_id'] );
		}

		if ( ! isset( $args['event.organizer_id'] ) || false === $args['event.organizer_id'] ){
			if ( 'create' === $mode ){
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ){
					Event_Tickets_PRO::instance()->throw_notice( $post, __( 'This Event Requires a Organizer to sync to Eventbrite', 'tribe-eventbrite' ) );
				}
				return false;
			}
			unset( $args['event.organizer_id'] );
		}

		if ( 'create' === $mode ) {
			$response = $this->request( $this->get_base_url( 'events' ), $args );
		} else {
			$response = $this->request( $this->get_base_url( 'events/' . $eventbrite_id ), $args );

			// For some reason the EB API gives this error if the event comes from V1 of the API
			if ( ! empty( $response->error ) && 'INTERNAL_ERROR' === $response->error && $eventbrite_id ){
				$response->id = $eventbrite_id;
			}

			if ( ! empty( $response->error ) && 'ARGUMENTS_ERROR' === $response->error ){
				delete_post_meta( $post->ID, '_EventBriteId', $eventbrite_id );
				$eventbrite_id = null;
				$response = $this->request( $this->get_base_url( 'events' ), $args );
			}
		}

		if ( ! empty( $response->error ) && 'NOT_AUTHORIZED' === $response->error ){
			Event_Tickets_PRO::instance()->throw_notice( $post, $response->error_description );
			return false;
		}

		// If the response ID is not valid just leave
		if ( empty( $response->id ) || ! is_numeric( $response->id ) ){
			return false;
		}

		// Save the Response ID
		update_post_meta( $post->ID, '_EventBriteId', $response->id, true );

		if ( ! empty( $params['tickets'] ) ){
			foreach ( (array) $params['tickets'] as $ticket ) {
				$this->sync_ticket( $post->ID, $ticket );
			}
		}

		if ( 'live' === $params['status'] ){
			$status_response = $this->request( $this->get_base_url( 'events/' . $response->id . '/publish' ), array() );
		} else {
			$status_response = $this->request( $this->get_base_url( 'events/' . $response->id . '/unpublish' ), array() );
		}

		return $response->id;
	}


	/**
	 * Retreive Information Methods
	 */

	public function get_event( $post = null, $eb = false ){
		if ( true !== $eb ){
			if ( is_null( $post ) ){
				$post = get_the_ID();
			}

			$post = get_post( $post );
			if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
				return false;
			}

			$eventbrite_id = absint( get_post_meta( $post->ID, '_EventBriteId', true ) );
		} else {
			$eventbrite_id = absint( $post );
		}

		if ( ! is_numeric( $eventbrite_id ) ) {
			return false;
		}

		$url = $this->get_base_url( 'events/' . $eventbrite_id );

		$response = wp_cache_get( 'event-' . $eventbrite_id, 'tribe-eventbrite' );
		if ( false === $response ) {
			$response = $this->request( $url, 'get' );
			if ( ! is_wp_error( $response ) && ! empty( $response ) && empty( $response->error ) ){
				wp_cache_set( 'event-' . $eventbrite_id, $response, 'tribe-eventbrite', self::$expire );
			}
		}

		if ( is_wp_error( $response ) ){
			return false;
		}

		if ( ! empty( $response->error ) ){
			return false;
		}

		return $response;
	}

	/**
	 * get Eventbrite ID from event ID
	 *
	 * @since 1.0
	 * @author bordoni
	 * @param int|bool $postId the ID of the current event / defaults to false
	 * @return mixed false on failure / the Eventbrite ID on success
	 */
	public function get_event_id( $post = null ) {
		if ( is_null( $post ) ){
			$post = get_the_ID();
		}
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		return get_post_meta( $post->ID, '_EventBriteId', true );;
	}

	/**
	 * determines an event's status
	 *
	 * @since 1.0
	 * @author bordoni
	 * @param int|bool $postId the ID of the current event / defaults to false
	 * @return string the status of the event
	 */
	public function get_event_status( $post = null ) {
		if ( is_null( $post ) ){
			$post = get_the_ID();
		}
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		$event = $this->get_event( $post );
		if ( ! $event ){
			return false;
		}

		return $event->status;
	}

	/**
	 * is the event live?
	 *
	 * @since 1.0
	 * @author bordoni
	 * @param int|bool $postId the ID of the current event / defaults to false
	 * @return bool
	 */
	public function is_live( $post = null ) {
		if ( is_null( $post ) ){
			$post = get_the_ID();
		}
		$post = get_post( $post );
		if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
			return false;
		}

		$event = $this->get_event( $post );

		if ( ! $event ){
			return false;
		}

		if ( empty( $event->ticket_classes ) || 'live' !== $event->status ){
			return false;
		}

		if ( strtotime( get_post_meta( $post->ID, '_EventEndDate', true ) ) < strtotime( 'now' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * get a country code from an event id
	 *
	 * @since 1.0
	 * @author bordoni
	 * @param  int $event_id the event id
	 * @return string $code the country code
	 */
	public function get_country_code( $event_id = null ) {
		$country = tribe_get_country( $event_id );

		if ( empty( $country ) ){
			return;
		}

		$countries = TribeEventsViewHelpers::constructCountries();
		$code = array_search( $country, $countries );
		return $code;
	}

	/**
	 * Get the ticket costs from Eventbrite
	 *
	 * @param $postId the TEC event to get the Eventbrite ticket costs from
	 * @param $only_if_live Only return the cost if the event is live
	 * @param $range Return only the Lowest and High prices
	 *
	 * @return string|array $prices the price or range of prices of the Eventbrite tickets
	 */
	public function get_cost( $post, $only_if_live = true, $range = true ) {
		$event = $this->get_event( $post );

		// we don't have an associated eventbrite event
		if ( ! $event ) {
			return false;
		}

		// the event isn't live, we shouldn't use the cost from EB
		if ( 'live' !== $event->status && $only_if_live ) {
			return false;
		}

		// no tickets attached to the eventbrite event
		if ( empty( $event->ticket_classes ) ) {
			return false;
		}

		$tickets = $event->ticket_classes;

		$cost = '';
		$is_donation = true;
		$is_free = true;
		$prices = array();

		foreach ( $tickets as $key => $ticket ) {
			if ( ! $ticket->free ) {
				$is_free = false;
			} else {
				$prices[0] = esc_attr__( 'Free', 'events-eventbrite' );
			}

			if ( ! $ticket->donation ) {
				$is_donation = false;
			}

			if ( ! empty( $ticket->cost ) ){
				$prices[ $ticket->cost->value ] = $ticket->cost->display;
			}
		}

		if ( $is_donation ){
			return esc_attr__( 'Donation', 'events-eventbrite' );
		}

		if ( $is_free ){
			return esc_attr__( 'Free', 'events-eventbrite' );
		}

		// There's more than one ticket, grab the lowest and highest price
		if ( count( $prices ) > 1 && true === $range ) {
			$_prices_key = array_keys( $prices );
			$min = min( $_prices_key );
			$max = max( $_prices_key );

			$_prices[ $min ] = $prices[ $min ];
			$_prices[ $max ] = $prices[ $max ];
			$prices = $_prices;
		}

		if ( 1 === count( $prices ) ) {
			$prices = reset( $prices );
		}

		return $prices;
	}

	/**
	 * see if an ECP event is already linked to this event
	 *
	 * @since 1.0
	 * @author jgabois & Justin Endler
	 * @param  int $ebEventId the Eventbrite event ID
	 * @return bool
	 */
	public function is_event_imported( $event ) {
		if ( ! is_numeric( $event ) ){
			return false;
		}

		$events = new WP_Query( array(
			'meta_key' => '_EventBriteId',
			'meta_value' => $event,
			'post_type' => TribeEvents::POSTTYPE,
		) );

		// Utilize the first match
		if ( $events->have_posts() ) {
			$events->next_post();
			return $events->post;
		}

		return false;
	}

	/**
	 * Check if an Eventbrite venue has already been imported, returing the venue ID
	 * if it has or boolean false if not.
	 *
	 * In strict mode - which is turned off by default - we require that the Eventbrite
	 * ID for the venue matches. Strict mode can be enabled with:
	 *
	 *     add_filter( 'tribe_eb_strict_venue_imports', '__return_true' );
	 *
	 * On the other hand, when strict mode is off if we fail to locate a venue with the
	 * precise Evenbrite ID we are looking for we go on to try and detect a venue with a
	 * matching name, initial address line, city, country and zip and use the first available
	 * match. This helps to avoid issues with what to all intents and purposes look like
	 * duplicate venues being pulled in.
	 *
	 * @since 1.0
	 * @param array $venue_details
	 * @param $details
	 * @return mixed false|int
	 */
	public function is_venue_imported( $venue, $details = array() ) {
		if ( ! is_numeric( $venue ) ){
			return false;
		}

		// Check to see if the very same venue has already been imported
		$venues = new WP_Query( array(
			'meta_key' => '_VenueEventBriteID',
			'meta_value' => $venue,
			'post_type' => TribeEvents::VENUE_POST_TYPE,
		) );

		// Utilize the first match
		if ( $venues->have_posts() ) {
			$venues->next_post();
			return $venues->post;
		}

		if ( empty( $details ) ){
			return false;
		}

		// Check to see if what is essentially the same venue - but perhaps doesn't share the
		// same Eventbrite ID - is already in existence (this can help to prevent what are
		// effectively duplicate venues from building up in some circumstances)
		$venues = new WP_Query( array(
			'post_type' => TribeEvents::VENUE_POST_TYPE,
			'meta_query' => array(
				array(
					'key' => '_VenueVenue',
					'value' => $details->name,
				),
				array(
					'key' => '_VenueAddress',
					'value' => $details->address->address_1,
				),
				array(
					'key' => '_VenueCity',
					'value' => $details->address->city,
				),
				array(
					'key' => '_VenueCountry',
					'value' => $details->address->country,
				),
				array(
					'key' => '_VenueZip',
					'value' => $details->address->postal_code,
				),
		) ) );

		// Utilize the first match
		if ( $venues->have_posts() ) {
			$venues->next_post();
			return $venues->post;
		}

		return false;
	}

	/**
	 * see if an Eventbrite organizer exists in ECP
	 *
	 * @since 1.0
	 * @author jgabois & Justin Endler
	 * @param  int $ebOrganizerId the Eventbrite organizer ID
	 * @return mixed false on failure / the organizer ID on success
	 */
	public function is_organizer_imported( $organizer ) {
		if ( ! is_numeric( $organizer ) ){
			return false;
		}

		$organizers = new WP_Query( array(
			'meta_key' => '_OrganizerEventBriteID',
			'meta_value' => $organizer,
			'post_type' => TribeEvents::ORGANIZER_POST_TYPE,
		) );

		if ( $organizers->have_posts() ) {
			$organizers->next_post();
			return $organizers->post;
		}

		return false;
	}

	/**
	 * Attempts to convert the provided time (expected to be a Unix timestamp) from local
	 * time to the specified timezone. The returned value is in Y-m-d H:i:s format.
	 *
	 * If the conversion cannot be made, the unadjusted source time is returned.
	 *
	 * @param  string $timezone
	 * @param  int    $datetime
	 *
	 * @return string
	 */
	public function timezone_convert( $timezone, $datetime ) {
		try {
			$time = new DateTime( date_i18n( self::$date_format, $datetime ), new DateTimeZone( $this->local_timezone() ) );
			$time = $time->setTimezone( new DateTimeZone( $timezone ) );
			return $time->format( self::$date_format );
		}
		catch ( Exception $e ) {
			return date( self::$date_format, strtotime( $datetime ) );
		}
	}

	/**
	 * Tries to return the local timezone as a timezone string format, even if it is
	 * configured as an offset.
	 *
	 * @return mixed|string|void
	 */
	public function local_timezone() {
		if ( '' != get_option( 'timezone_string', '' ) ) {
			$timezone = get_option( 'timezone_string' );
		} elseif ( false !== get_option( 'gmt_offset' ) ) {
			$current_offset = get_option( 'gmt_offset' );

			// try to get timezone from gmt_offset, respecting daylight savings
			$timezone = timezone_name_from_abbr( null, $current_offset * 3600, true );

			// if that didn't work, maybe they don't have daylight savings
			if ( false === $timezone ) {
				$timezone = timezone_name_from_abbr( null, $current_offset * 3600, false );
			}

			// and if THAT didn't work, round the gmt_offset down and then try to get the timezone respecting daylight savings
			if ( false === $timezone ) {
				$timezone = timezone_name_from_abbr( null, (int) $current_offset * 3600, true );
			}

			// lastly if that didn't work, round the gmt_offset down and maybe that TZ doesn't do daylight savings
			if ( false === $timezone ) {
				$timezone = timezone_name_from_abbr( null, (int) $current_offset * 3600, false );
			}

			// if all else fails, use UTC
			if ( false === $timezone ) {
				$timezone = 'UTC';
			}
		} else {
			$timezone = 'UTC';
		}

		return $timezone;
	}

	/**
	 * Replace the wp_texturize stuff
	 *
	 * @param  string $string string to be untexturized
	 * @return string         Returns the untexturized
	 */
	private static function string_prepare( $string, $html = array() ) {
		return wp_specialchars_decode( str_replace(
			array(
				_x( '&#8220;', 'opening curly double quote' ),
				_x( '&#8221;', 'closing curly double quote' ),
				_x( '&#8217;', 'apostrophe' ),
				_x( '&#8242;', 'prime' ),
				_x( '&#8243;', 'double prime' ),
				_x( '&#8216;', 'opening curly single quote' ),
				_x( '&#8217;', 'closing curly single quote' ),
				_x( '&#8211;', 'en dash' ),
				_x( '&#8212;', 'em dash' ),
			),
			array(
				'"',
				'"',
				'\'',
				'\'',
				'"',
				'\'',
				'\'',
				'-',
				'-',
			),
			$string
		) );
	}
}