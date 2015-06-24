<?php
/**
 * @for Address Module Template
 * This file contains the hook logic required to create an effective address module view.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_EventBrite_Template')){
	class Tribe_Events_EventBrite_Template extends Tribe_Template_Factory {
		/**
		 * fire up the EventBrite Template
		 * @return void
		 * @author tim@imaginesimplicity.com
		 */
		public static function init(){
			// Start address template
			add_filter( 'tribe_events_eventbrite_before_the_tickets', array( __CLASS__, 'before_tickets' ), 1, 1 );

			// Address meta
			add_filter( 'tribe_events_eventbrite_the_tickets', array( __CLASS__, 'the_tickets' ), 1, 1 );

			// End address template
			add_filter( 'tribe_events_eventbrite_after_the_tickets', array( __CLASS__, 'after_tickets' ), 1, 2 );
		}
		/**
		 * before tickets injection
		 * @param  int $post_id
		 * @return string
		 * @author tim@imaginesimplicity.com
		 */
		public static function before_tickets( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_eventbrite_before_the_tickets');
		}

		/**
		 * Ticket html form.
		 *
		 * @return string
		 */
		public static function the_tickets() {
			$post_id  = get_the_ID();
			$api = EventbriteAPI::instance();
			$event_id = $api->get_event_id( $post_id );

			$iframe_url = ( is_ssl() ? 'https://' : 'http://' ) . 'www.eventbrite.com/tickets-external?eid=%s&amp;ref=etckt&v=2';
			$iframe_url = apply_filters( 'tribe_events_eb_iframe_url', sprintf( $iframe_url, $event_id ) );

			$html = '';

			if ( ! empty( $event_id ) && $api->is_live( $post_id ) && tribe_event_show_tickets( $post_id ) ) {
				$html = sprintf(
					'<div class="eventbrite-ticket-embed" style="width:100%%;text-align:left">
						<iframe id="eventbrite-tickets-%1$s" src="%2$s" style="height:200px;width:100%%;overflow:auto;"></iframe>
						<div style="font-family:Helvetica, Arial;font-size:10px;padding:5px 0 5px;margin:2px;width:100%%;text-align:left">
							<a target="_blank" href="http://www.eventbrite.com/features?ref=etckt" style="color:#ddd;text-decoration:none">Event registration</a>
							<span style="color:#ddd"> powered by </span>
							<a target="_blank" href="http://www.eventbrite.com?ref=etckt" style="color:#ddd;text-decoration:none">Eventbrite</a>
						</div>
					</div>', $event_id, $iframe_url );
			}

			/**
			 * @todo remove in 4.0
			 * @deprecated 3.9
			 */
			$html = apply_filters( 'tribe_template_factory_debug', $html, 'Tribe_Events_EventBrite_Template::the_tickets' );

			/**
			 * Allows Eventbrite iframe HTML to be modified.
			 *
			 * @param string $html
			 * @param string $event_id associated Eventbrite ID
			 * @param int    $post_id
			 */
			return apply_filters( 'tribe_events_eb_iframe_html', $html, $event_id, $post_id );
		}

		/**
		 * after ticket injection
		 * @param  int $post_id
		 * @return string
		 * @author tim@imaginesimplicity.com
		 */
		public static function after_tickets( $post_id ){
			$html = '';
			return apply_filters('tribe_template_factory_debug', $html, 'tribe_events_eventbrite_after_the_tickets');
		}
	}

	Tribe_Events_EventBrite_Template::init();
}
