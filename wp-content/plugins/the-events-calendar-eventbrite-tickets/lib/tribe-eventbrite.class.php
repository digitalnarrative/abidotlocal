<?php

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }


if ( !class_exists( 'Event_Tickets_PRO' ) ) {

	/**
	 * Event_Tickets_PRO main class
	 *
	 * @package Event_Tickets_PRO
	 * @since  1.0
	 * @author Modern Tribe Inc.
	 */
	class Event_Tickets_PRO {

		/**************************************************************
		 * EventBrite Configuration
		 **************************************************************/
		const REQUIRED_TEC_VERSION = '3.9.3';

		protected static $instance;
		public static $errors;
		public static $eventBritePrivacy = 0;
		public static $eventBriteTimezone;
		public static $eventBriteTransport; // https if supported, otherwise http
		public static $pluginVersion = '3.9.5';
		protected $cache_expiration = HOUR_IN_SECONDS; // defaults to 1 hour, use $this->get_cache_expiration() to apply filters
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginSlug;

		public static $metaTags = array(
			'_EventBriteId',			// ID in Eventbrite of this event
			'_EventBriteTicketName',
			'_EventBriteTicketDescription',
			'_EventBriteTicketStartDate',
			'_EventBriteTicketStartHours',
			'_EventBriteTicketStartMinutes',
			'_EventBriteTicketStartMeridian',
			'_EventBriteTicketEndDate',
			'_EventBriteTicketEndHours',
			'_EventBriteTicketEndMinutes',
			'_EventBriteTicketEndMeridian',
			'_EventBriteIsDonation',
			'_EventBriteTicketQuantity',
			'_EventBriteIncludeFee',
			'_EventBriteStatus',
			'_EventBriteEventCost',
			'_EventRegister',
			'_EventShowTickets',
		);


		/**
		 * inforce singleton factory method
		 *
		 * @since 1.0
		 * @author jkudish
		 * @return Tribe__Events__Tickets__Eventbrite__Main
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}

		/**
		 * checks whether the The Events Calendar 2.0 or higher is active
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return bool
		 */
		public static function is_core_active() {
			return defined( 'TribeEvents::VERSION' ) && version_compare( TribeEvents::VERSION, '2.0', '>=' );
		}

		/**
		 * class constructer
		 * init necessary functions
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 */
		function __construct() {

			// set internal variables
			$this->pluginPath = apply_filters( 'tribe_eb_pluginpath', trailingslashit( dirname( dirname( __FILE__ ) ) ) );
			$this->pluginDir = apply_filters( 'tribe_eb_plugindir', trailingslashit( basename( $this->pluginPath ) ) );
			$this->pluginFile = apply_filters( 'tribe_eb_pluginfile', $this->pluginDir . 'tribe-eventbrite.php' );
			$this->pluginUrl = apply_filters( 'tribe_eb_pluginurl', plugins_url() . '/' . $this->pluginDir );
			$this->pluginSlug = 'tribe-eventbrite';

			// bootstrap plugin
			self::load_domain();
			add_action( 'plugins_loaded', array( $this, 'add_actions' ) );
			add_action( 'plugins_loaded', array( $this, 'add_filters' ) );
		}

		/**
		 * echo admin error if/when TEC is not active
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function notice_missing_core() {
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __( 'The Events Calendar', 'tribe-events-community' );
			echo '<div class="error"><p>' . sprintf( __( 'To begin using The Events Calendar: Eventbrite Tickets, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-community' ), $url, $title ) . '</p></div>';
		}

		/**
		 * Add Eventbrite Tickets to the list of add-ons to check required version.
		 *
		 * @author PaulHughes01
		 * @since 1.0.1
		 * @return array $plugins the existing plugins
		 * @return array the pluggins
		 */
		public function init_addon( $plugins ) {
			$plugins['TribeEB'] = array(
				'plugin_name' => 'The Events Calendar: Eventbrite Tickets',
				'required_version' => self::REQUIRED_TEC_VERSION,
				'current_version' => self::$pluginVersion,
				'plugin_dir_file' => basename( dirname( __FILE__ ) ) . '/tribe-eventbrite.php'
			);

			return $plugins;
		}

		/**
		 * run all WordPress action hooks
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function add_actions() {
			add_filter( 'tribe_tec_addons', array( $this, 'init_addon' ) );

			if ( ! class_exists( 'TribeEvents' ) ) {
				add_action( 'admin_notices', array( $this, 'notice_missing_core' ) );
			} elseif ( $this->is_core_active() ) {
				add_action( 'admin_notices', array( $this, 'notice_missing_token') );
				add_action( 'admin_notices', array( $this, 'notice_edit_event') );

				add_action( 'admin_init', array( $this, 'prepopulate') );
				add_action( 'admin_enqueue_scripts' , array( $this, 'load_assets' ) );
				add_action( 'plugin_action_links_' . trailingslashit( $this->pluginDir ) . 'tribe-eventbrite.php', array( $this, 'addLinksToPluginActions' ) );
				add_action( 'template_redirect', array( $this, 'authorize_redirect' ) );
				add_action( 'parse_request', array( $this, 'maybe_regenerate_rewrite_rules' ) );
				add_action( 'tribe_settings_validate_before_checks', array( $this, 'authorize_get_permission_redirect' ) );
				add_action( 'tribe_settings_do_tabs', array( $this, 'add_eventbrite_tab' ) );

				if ( EventbriteAPI::instance()->is_ready() ) {

					add_action( 'wp_before_admin_bar_render', array( $this, 'addEventbriteToolbarItems' ), 20 );
					add_action( 'admin_menu', array( $this, 'add_option_page' ) );

					add_action( 'tribe_events_update_meta', array( $this, 'action_sync_event' ), 20 );
					add_action( 'tribe_events_event_clear', array( $this, 'clear_details' ) );
					add_action( 'tribe_events_cost_table', array( $this, 'add_metabox'), 1 );
					add_action( 'tribe_eventbrite_before_integration_header', array( $this, 'addEventbriteLogo' ) );
					add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'print_ticket_form' ), 9 );
				}
			}
		}

		/**
		 * run all WordPress filter hooks
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function add_filters() {
			add_filter( 'tribe_help_tab_forums_url', array( $this, '_link_support_forum' ), 100 );

			// get all pricing items for tickets on the cost field
			add_filter( 'tribe_get_cost', array( $this, 'filter_get_cost' ), 20, 3 );
			add_filter( 'tribe_events_admin_show_cost_field', '__return_false' );
			add_filter( 'tribe_events_template_paths', array( $this, 'add_eventbrite_template_paths' ) );

			if ( ! class_exists( 'TribeEvents' ) ) {

			} elseif ( $this->is_core_active() ) {
				add_filter( 'rewrite_rules_array', array( &$this, 'rewrite_rules_array' ) );
				add_filter( 'query_vars', array( &$this, 'query_vars' ) );
			}
		}

		/**
		 * Pre-populates an event with Eventbrite info, shows an error on failure.
		 */
		public function prepopulate() {
			// Sanity checks
			if ( ! current_user_can( 'publish_tribe_events' ) || empty( $_GET['import_eventbrite'] ) || ! wp_verify_nonce( $_GET['import_eventbrite'], 'import_eventbrite' ) || empty( $_GET['eventbrite_id'] ) ) {
				return;
			}

			// Attempt to import the event then take the user to the event editor
			try {
				$event_id = $this->import_existing_events();
				wp_safe_redirect( admin_url( 'post.php?post=' . $event_id . '&action=edit' ) );
			}
			// Or, on failure, keep them on the importer screen and trigger an appropriate error message
			catch ( TribeEventsPostException $e ) {
				wp_safe_redirect( admin_url( 'edit.php?post_type=tribe_events&page=import-eventbrite-events&error=' . urlencode( $e->getMessage() ) ) );
			}

			// As we're redirecting, kill execution here
			exit();
		}

		public function maybe_regenerate_rewrite_rules(){
			$rules = $this->rewrite_rules_array();
			$diff = array_diff( $rules , $GLOBALS['wp_rewrite']->rules );
			$key_diff = array_diff_assoc( $rules , $GLOBALS['wp_rewrite']->rules );

			if ( empty( $diff ) && empty( $key_diff ) ){
				return;
			}

			flush_rewrite_rules();
		}

		public function query_vars( $vars ){
			array_push( $vars, 'tribe_oauth' );
			return $vars;
		}

		public function rewrite_rules_array( $rules = array() ){
			$rule = array(
				'tribe-oauth/eventbrite/?' => '?index.php?tribe_oauth=eventbrite',
			);
			return $rule + $rules;
		}

		public function authorize_redirect(){
			if ( ! current_user_can( 'manage_options' ) || 'eventbrite' !== get_query_var( 'tribe_oauth' ) || empty( $_GET['code'] ) ){
				if ( 'eventbrite' === get_query_var( 'tribe_oauth' ) && empty( $_GET['code'] ) ){
					$url = home_url();
				} else {
					return;
				}
			} else {
				// After API Tab we need to change this to the right redirect
				$url = add_query_arg( array(
					'post_type' => TribeEvents::POSTTYPE,
					'page' => 'tribe-events-calendar',
					'tab' => 'eventbrite',
					'code' => wp_kses( $_GET['code'], array() ),
					'oauth' => get_query_var( 'tribe_oauth' ),
				), admin_url( '/edit.php' ) );
			}
			exit( wp_safe_redirect( $url ) );
		}

		public function authorize_get_permission_redirect(){
			$code = null;
			$instance = new TribeSettingsTab( 'eventbrite', 'eventbrite' );
			$instance->errors = array();

			// check permissions
			if ( ! current_user_can( 'manage_options' ) ) {
				$instance->errors[]    = __( "You don't have permission to do that.", 'tribe-events-calendar' );
				$instance->major_error = true;
			}

			if ( isset( $_POST['tribe-eventbrite-authorize'] ) && isset( $_POST['current-settings-tab'] ) ) {
				// check the nonce
				if ( ! wp_verify_nonce( $_POST['tribe-save-settings'], 'saving' ) ) {
					$instance->errors[]    = __( 'The request was sent insecurely.', 'tribe-events-calendar' );
					$instance->major_error = true;
				}

				// check that the request originated from the current tab
				if ( 'eventbrite' !== $_POST['current-settings-tab'] ) {
					$instance->errors[]    = __( "The request wasn't sent from this tab.", 'tribe-events-calendar' );
					$instance->major_error = true;
				}

				// bail if we have errors
				if ( count( $instance->errors ) ) {
					remove_action( 'shutdown', array( $instance, 'deleteOptions' ) );
					add_option( 'tribe_settings_errors', $instance->errors );
					add_option( 'tribe_settings_major_error', $instance->major_error );
					exit( wp_redirect( $instance->url ) );
				}
			} elseif ( ! empty( $_GET['oauth'] ) && 'eventbrite' === $_GET['oauth'] && ! empty( $_GET['code'] ) ) {
				$code = $_GET['code'];
			} else {
				return;
			}

			$api = EventbriteAPI::instance();

			$api->authorize( $code );

			$url = add_query_arg( array(
				'post_type' => TribeEvents::POSTTYPE,
				'page' => 'tribe-events-calendar',
				'tab' => 'eventbrite',
				'success' => 1,
			), admin_url( 'edit.php' ) );

			exit( wp_redirect( esc_url_raw( $url ) ) );
		}

		/**
		 * Apply filters and return the eventbrite cache expiration
		 *
		 * @return int number of seconds until the cache should expire
		 *
		 */
		protected function get_cache_expiration() {
			return apply_filters( 'tribe_events_eb_cache_expiration', $this->cache_expiration );
		}

		/**
		 * load plugin text domain
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function load_domain() {
			$langpath = trailingslashit( basename( dirname( EVENTBRITE_PLUGIN_FILE ) ) ) . 'lang/';

			load_plugin_textdomain( 'tribe-eventbrite', false, $langpath );
		}

		/**
		 * enqueue scripts & styles in the admin
		 *
		 * @since  1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function load_assets() {
			wp_enqueue_style( 'tribe-eventbrite-admin', $this->pluginUrl . 'resources/eb-tec-admin.css', array(), apply_filters( 'tribe_eventbrite_css_version', self::$pluginVersion ) );
		}

		public function throw_notice( $event, $message, $sent = array() ){
			if ( is_numeric( $event ) ){
				$event = get_post( $event );
			}

			if ( ! is_a( $event, 'WP_Post' ) ){
				return false;
			}

			if ( ! tribe_is_event( $event->ID ) ){
				return false;
			}

			if ( ! empty( $sent ) ){
				update_post_meta( $event->ID, 'tribe-eventbrite-saved-data', $sent );
			}

			$message = get_post_meta( $event->ID, TribeEvents::EVENTSERROROPT, true ) . '<br />' . $message;

			// The errors (flushed on page reload)
			return update_post_meta( $event->ID, TribeEvents::EVENTSERROROPT, $message );
		}

		/**
		 * Updates the Eventbrite information in WordPress and makes the
		 * API calls to EventBrite to update the listing on their side
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @link http://www.eventbrite.com/api/doc/
		 * @param int $postId the ID of the event being edited
		 * @uses $_POST
		 * @return void
		 */
		public function action_sync_event( $event ) {
			$event = get_post( $event );
			if ( ! is_object( $event ) || ! is_a( $event, 'WP_Post' ) ){
				return;
			}

			if ( ! tribe_is_event( $event->ID ) ){
				return;
			}

			// Clean if Register Event is not Yes
			if ( empty( $_POST['EventRegister'] ) || 'yes' !== $_POST['EventRegister'] ){
				self::clear_details( $event );
				return;
			}

			$eventbrite_id = get_post_meta( $event->ID, '_EventBriteId', true );

			$args = array(
				'status' => ( ! empty( $_POST['EventBriteStatus'] ) ? $_POST['EventBriteStatus'] : 'draft' ),
				'show_tickets' => ( ! empty( $_POST['EventShowTickets'] ) ? $_POST['EventShowTickets'] : 'yes' )
			);

			$api = EventbriteAPI::instance();

			if ( wp_is_post_revision( $event->ID ) ){
				return $this->throw_notice( $event, __( 'This Event is a revision and cannot sync to Eventbrite.', 'tribe-eventbrite' ), $_POST );
			}

			if ( ! is_numeric( $eventbrite_id ) ){
				$event_title = get_the_title( $event );
				if ( empty( $event_title ) ){
					return $this->throw_notice( $event, __( 'This Event requires a Title to sync to Eventbrite.', 'tribe-eventbrite' ), $_POST );
				}

				$venue = get_post_meta( $event->ID, '_EventVenueID', true );
				if ( is_numeric( $venue ) ){
					$venue = get_post( $venue );
				}

				if ( ! is_a( $venue, 'WP_Post' ) || ! tribe_is_venue( $venue->ID ) ){
					return $this->throw_notice( $event, __( 'This Event Requires a Venue to sync to Eventbrite', 'tribe-eventbrite' ), $_POST );
				} else {
					$venue->metas = array(
						'title' => array(
							'value' => get_the_title( $venue->ID ),
							'message' => __( 'The Venue is missing the Title', 'tribe-eventbrite' ),
						),
						'address' => array(
							'value' => get_post_meta( $venue->ID, '_VenueAddress', true ),
							'message' => __( 'No Address for this Venue', 'tribe-eventbrite' ),
						),
						'city' => array(
							'value' => get_post_meta( $venue->ID, '_VenueCity', true ),
							'message' => __( 'This Venue is missing the City', 'tribe-eventbrite' ),
						),
					);

					$throw_notice = false;
					foreach ( $venue->metas as $name => $meta ) {
						if ( ! empty( $meta['value'] ) ){
							continue;
						} else {
							$throw_notice = true;
						}
						$this->throw_notice( $event, $meta['message'], $_POST );
					}

					if ( $throw_notice ){
						return false;
					}
				}

				$organizer = get_post_meta( $event->ID, '_EventOrganizerID', true );
				if ( is_numeric( $organizer ) ){
					$organizer = get_post( $organizer );
				}

				if ( ! is_a( $organizer, 'WP_Post' ) || ! tribe_is_organizer( $organizer->ID ) ){
					return $this->throw_notice( $event, __( 'This Event Requires a Organizer to sync to Eventbrite', 'tribe-eventbrite' ), $_POST );
				}

				// make sure all required fields are present
				$required_fields = array(
					'EventBriteTicketName' => __( 'Ticket Name', 'tribe-eventbrite' ),
					'EventBriteTicketStartDate' => __( 'Date to Start Ticket Sales', 'tribe-eventbrite' ),
					'EventBriteTicketEndDate' => __( 'Date to End Ticket Sales', 'tribe-eventbrite' ),
					'EventBriteIsDonation' => __( 'Ticket Type', 'tribe-eventbrite' ),
					'EventBriteEventCost' => __( 'Ticket Cost', 'tribe-eventbrite' ),
					'EventBriteTicketQuantity' => __( 'Ticket Quantity', 'tribe-eventbrite' ),
					'EventBriteIncludeFee' => __( 'Ticket - Include Fee in Price', 'tribe-eventbrite' ),
				);

				$missing_fields = array();
				$sent_fields = array();

				foreach ( $required_fields as $key => $label ) {
					if ( ! isset( $_POST[ $key ] ) || '' === $_POST[ $key ] || is_null( $_POST[ $key ] ) ) {
						$missing_fields[ $key ] = $label;
					}
				}

				// if all fields are missing, assume the fields weren't meant to be filled out
				if ( count( $missing_fields ) != count( $required_fields ) ) {
					// if ticket type is set to Donation or Free, allow cost to be set to null
					if ( isset( $_POST['EventBriteIsDonation'] ) && 0 != $_POST['EventBriteIsDonation'] ) {
						if ( isset( $missing_fields['EventBriteEventCost'] ) ){
							unset( $missing_fields['EventBriteEventCost'] );
						}
					} elseif ( isset( $_POST['EventBriteEventCost'] ) && ! is_numeric( $_POST['EventBriteEventCost'] ) ) {
						$missing_fields['EventBriteEventCost'] = __( 'Ticket Cost (must be numeric)', 'tribe-eventbrite' );
					}

					// if ticket type is set to free, fee inclusion to be set to null
					if ( isset( $_POST['EventBriteIsDonation'] ) && 2 === $_POST['EventBriteIsDonation'] ) {
						if ( isset( $missing_fields['EventBriteIncludeFee'] ) ){
							unset( $missing_fields['EventBriteIncludeFee'] );
						}
					}

					if ( ! empty( $missing_fields ) ) {
						$message = __( 'All required fields must be filled out correctly in order for this event to be associated to Eventbrite. The following required fields were not filled out (or not correctly):', 'tribe-eventbrite' ) . '<br>';
						foreach ( $missing_fields as $key => $name ) {
							$message .= '<span class="admin-indent missing-field-' . esc_attr( $key ) . '">' . esc_attr( $name ) . '</span><br>';
						}
						$message .= '<strong>' . __( 'Please fill in the missing fields and try saving again.', 'tribe-eventbrite' ) . '</strong><br />';
					}
				}

				// check the dates of the ticket
				if ( isset( $_POST['EventBriteTicketStartDate'] ) ) {
					$message = '';
					$date_errors = array();
					$event_start_date = strtotime( get_post_meta( $event->ID, '_EventStartDate', true ) );
					$event_end_date = strtotime( get_post_meta( $event->ID, '_EventEndDate', true ) );
					$ticket_start = $_POST['EventBriteTicketStartDate'] . ' ' . $_POST['EventBriteTicketStartHours'] . ':' . $_POST['EventBriteTicketStartMinutes'];
					$ticket_start .= ( isset( $_POST['EventBriteTicketStartMeridian'] ) ) ? $_POST['EventBriteTicketStartMeridian'] : null;
					$ticket_start_timestamp = strtotime( $ticket_start );
					$ticket_end = $_POST['EventBriteTicketEndDate'] . ' ' . $_POST['EventBriteTicketEndHours'] . ':' . $_POST['EventBriteTicketEndMinutes'];
					$ticket_end .= ( isset( $_POST['EventBriteTicketEndMeridian'] ) ) ? $_POST['EventBriteTicketEndMeridian'] : null;
					$ticket_end_timestamp = strtotime( $ticket_end );

					if ( $ticket_start_timestamp > $event_end_date ) {
						$date_errors[] = __( 'Ticket sales start date cannot be after the event ends', 'tribe-eventbrite' );
					}

					if ( $ticket_end_timestamp > $event_end_date ) {
						$date_errors[] = __( 'Ticket sales end date cannot be after the event ends', 'tribe-eventbrite' );
					}

					if ( $ticket_start_timestamp > $ticket_end_timestamp ) {
						$date_errors[] = __( 'Ticket sales start date cannot be after ticket sales end date', 'tribe-eventbrite' );
					}

					if ( ! empty( $date_errors ) ) {
						$message .= __( 'The dates you have chosen for your ticket sales are inconsistent', 'tribe-eventbrite' ).':<br>';
						foreach ( $date_errors as $error_messsage ) {
							$message .= '<span class="admin-indent">' . $error_messsage . '</span><br>';
						}
						$message .= '<strong>' . __( 'Please adjust the dates and try saving again.', 'tribe-eventbrite' ) . '</strong><br />';
					}
				}

				if ( ! empty( $message ) ){
					// save the sent fields (flushed on page reload)
					$sent_fields = array();
					foreach ( self::$metaTags as $tag ) {
						$post_tag = substr( $tag, 1 );
						$sent_fields[ $tag ] = isset( $_POST[ $post_tag ] ) ? $_POST[ $post_tag ] : null;
					}
					update_post_meta( $event->ID, 'tribe-eventbrite-saved-data', $sent_fields );

					// The errors (flushed on page reload)
					return update_post_meta( $event->ID, TribeEvents::EVENTSERROROPT, $message );
				}

				$args['tickets'][] = array(
					'name' => $_POST['EventBriteTicketName'],
					'description' => $_POST['EventBriteTicketDescription'],
					'start' => $ticket_start_timestamp,
					'end' => $ticket_end_timestamp,
					'type' => $_POST['EventBriteIsDonation'],
					'cost' => $_POST['EventBriteEventCost'] * 100,
					'qty' => $_POST['EventBriteTicketQuantity'],
					'include_fee' => $_POST['EventBriteIncludeFee'],
				);
			}

			$api->sync_event( $event, $args );
		}

		/**
		 * Get the ticket costs from Eventbrite
		 *
		 * @param $cost the original cost of the event from tribe_get_cost()
		 * @param $postId the TEC event to get the Eventbrite ticket costs from
		 * @param $withCurrencySymbol whether to add the currency symbol
		 *
		 * @return string $cost the cost of the Eventbrite tickets
		 * @see $this->get_cache_expiration()
		 */
		public function filter_get_cost( $cost, $post, $withCurrencySymbol ) {
			if ( is_null( $post ) ){
				$post = get_the_ID();
			}
			$post = get_post( $post );
			if ( ! is_object( $post ) || ! is_a( $post, 'WP_Post' ) ){
				return $cost;
			}

			// if the cache isn't expired we'll use the value stored there
			$cache_expiration = $this->get_cache_expiration();

			// Check if we already have the cost
			$cached_cost_key = 'tribe_eventbrite_cost_' . $post->ID;
			$cached_cost = get_transient( $cached_cost_key );

			$api = EventbriteAPI::instance();

			if ( ! $cached_cost ) {
				// the transient doesn't exist, check the postmeta (that was the pre 3.10 way of storing it)
				$postmeta_cached_cost = get_post_meta( $post->ID, '_EventbriteCost', true );
				if ( ! empty( $postmeta_cached_cost ) ) {
					if ( time() < $postmeta_cached_cost['timestamp'] + $cache_expiration ) {
						// the cost is not expired, let's use it
						$cost = $postmeta_cached_cost['cost'];
					}
					// either we found a valid cached cost or we didn't, but delete the postmeta, we're not using it anymore
					delete_post_meta( $post->ID, '_EventbriteCost' );
				}

				// at this point, if we didn't get the cost from the postmeta or the transient, let's get it from Eventbrite
				if ( ! $cost ) {
					$eb_cost = $api->get_cost( $post );
					if ( $eb_cost ) {
						$cost = $eb_cost;
					} else {
						// we didn't find a value from Eventbrite, just return the original value
						return $cost;
					}
				}
				// Update the transient
				set_transient( $cached_cost_key, $cost, $cache_expiration );
			} else {
				$cost = $cached_cost;
			}

			// If there's more than one price, this will make them into a range
			if ( is_array( $cost ) ) {
				$cost = implode( apply_filters( 'tribe_eb_event_cost_separator', ' - ' ), $cost );
			}

			return apply_filters( 'tribe_eb_event_cost', $cost );

		}

		/**
		 * Clears/deletes all Eventbrite meta from an event
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @param int $postId the ID of the event being edited
		 * @uses self::metaTags
		 * @return void
		 */
		public function clear_details( $event ) {
			$event = get_post( $event );
			if ( ! is_object( $event ) || ! is_a( $event, 'WP_Post' ) ){
				return false;
			}

			foreach ( self::$metaTags as $meta ) {
				delete_post_meta( $event->ID, $meta );
			}
			return true;
		}

		/**
		 * retrieves data from an existing Eventbrite event
		 *
		 * @throws Exception
		 * @return mixed error on failure / json string of the event on success
		 */
		public function import_existing_events() {
			add_filter( 'tribe-post-origin', array( $this, 'filter_imported_origin' ) );

			$api = EventbriteAPI::instance();

			$eventbrite_id = is_numeric( $_GET['eventbrite_id'] ) ? absint( $_GET['eventbrite_id'] ) : false;
			$adjust_timezone = isset( $_GET['eventbrite_tz_correct'] );

			if ( ! $eventbrite_id ){
				throw new TribeEventsPostException( __( 'We were unable to import your Eventbrite event. Please verify the event id and try again.', 'tribe-eventbrite' ) );
			}

			$event = $api->get_event( $eventbrite_id, true );

			if ( ! $event ) {
				throw new TribeEventsPostException( __( 'We were unable to import your Eventbrite event. Please verify the event id and try again.', 'tribe-eventbrite' ) );
			}

			if ( $api->is_event_imported( $event->id ) ) {
				throw new TribeEventsPostException( __( 'Event already imported.', 'tribe-eventbrite' ) );
			}

			// insert new ECP event
			$postdata = array(
				'post_title' => $event->name->text,
				'post_type' => TribeEvents::POSTTYPE,
				'post_content' => ! empty( $event->description ) ? html_entity_decode( (string) $event->description->html ) : '',
				'_EventBriteId' => $event->id,
				'_EventRegister' => 'yes',
				'_EventbriteTZ' => $event->start->timezone,
				'_EventbriteTZAdjust' => $adjust_timezone,
			);

			// Check the Organizer
			if ( 'live' === $event->status ){
				$postdata['post_status'] = 'publish';
			} else {
				$postdata['post_status'] = 'draft';
			}

			// save a new organizer
			if ( ! empty( $event->organizer ) ) {
				$postdata['_OrganizerEventBriteID'] = $event->organizer->id;

				// don't create a new organizer if this one is already imported
				$organizer = $api->is_organizer_imported( $event->organizer->id );
				$organizerData = array();

				if ( ! $organizer ) {
					$organizerData['Organizer'] = $event->organizer->name;
				} else {
					$organizerData['OrganizerID'] = $organizer->ID;
				}

				$postdata['Organizer'] = $organizerData;
				$_POST['Organizer'] = $organizerData;
			}

			if ( ! empty( $event->venue ) ) {
				$postdata['_VenueEventBriteID'] = $event->venue->id;

				// Don't create a new venue if this one is already imported
				$venue = $api->is_venue_imported( $event->venue->id, $event->venue );

				$venueData = array();

				if ( ! $venue ) {
					$venueData['Address']  = ( ! empty( $event->venue->address->address_1 ) ) ? $event->venue->address->address_1 : null;
					$venueData['Address'] .= ( ! empty( $event->venue->address->address_2 ) ) ? $event->venue->address->address_2 : null;
					$venueData['Venue']    = ( ! empty( $event->venue->name ) ) ? $event->venue->name : null;
					$venueData['Country']  = ( ! empty( $event->venue->address->country ) ) ? $event->venue->address->country : null;
					$venueData['Zip']      = ( ! empty( $event->venue->address->postal_code ) ) ? $event->venue->address->postal_code : null;
					$venueData['State']    = ( ! empty( $event->venue->address->region ) ) ? $event->venue->address->region : null;
					$venueData['Province'] = ( ! empty( $event->venue->address->region ) ) ? $event->venue->address->region : null;
					$venueData['City']     = ( ! empty( $event->venue->address->city ) ) ? $event->venue->address->city : null;
				} else {
					$venueData['VenueID'] = $venue->ID;
				}

				$postdata['Venue'] = $venueData;
				$_POST['Venue'] = $venueData;
			}

			// Setup the Correct action
			remove_action( 'tribe_events_update_meta', array( $this, 'action_sync_event' ), 20 );
			add_action( 'tribe_events_update_meta', array( $this, 'link_imported_event_data' ), 10, 2 );

			$start = strtotime( $event->start->local );
			$end = strtotime( $event->end->local );

			$postdata['EventStartDate'] = date( TribeDateUtils::DBDATEFORMAT, $start );
			$postdata['EventEndDate'] = date( TribeDateUtils::DBDATEFORMAT, $end );

			if ( 86400 !== ( $end - $start ) ){
				$postdata['EventStartHour'] = date( TribeDateUtils::HOURFORMAT, $start );
				$postdata['EventStartMinute'] = date( TribeDateUtils::MINUTEFORMAT, $start );
				$postdata['EventStartMeridian'] = date( TribeDateUtils::MERIDIANFORMAT, $start );

				$postdata['EventEndHour'] = date( TribeDateUtils::HOURFORMAT, $end );
				$postdata['EventEndMinute'] = date( TribeDateUtils::MINUTEFORMAT, $end );
				$postdata['EventEndMeridian'] = date( TribeDateUtils::MERIDIANFORMAT, $end );
			} else {
				$postdata['EventAllDay'] = true;

				$postdata['EventStartHour'] = false;
				$postdata['EventStartMinute'] = false;

				$postdata['EventEndHour'] = false;
				$postdata['EventEndMinute'] = false;
			}

			$event_id = tribe_create_event( $postdata );

			if ( is_wp_error( $event_id ) ) {
				throw new TribeEventsPostException( __( 'We were unable to import your Eventbrite event. Please try again.', 'tribe-eventbrite' ) );
			}

			// Update EventBrite Status
			update_post_meta( $event_id, '_EventBriteStatus', $event->status );
			update_post_meta( $event_id, '_EventShowTickets', 'yes' );

			remove_filter( 'tribe-post-origin', array( $this, 'filter_imported_origin' ) );
			return $event_id;
		}

		/**
		 * links existing data with an imported event from Eventbrite
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @param  int $event_id the event ID
		 * @param  mixed $data the event's data
		 * @return void
		 */
		public function link_imported_event_data( $event_id, $data ) {

			$eb_event_id = $data['_EventBriteId'];
			$eb_organizer_id = $data['_OrganizerEventBriteID'];
			$eb_venue_id = isset( $data['_VenueEventBriteID'] ) ? $data['_VenueEventBriteID'] : false;

			$ecp_venue = get_post_meta( $event_id, '_EventVenueID', true );
			$ecp_organizer = get_post_meta( $event_id, '_EventOrganizerID', true );

			update_post_meta( $event_id, '_EventBriteId', $eb_event_id );
			update_post_meta( $event_id, '_EventRegister', 'yes' );

			if ( $ecp_organizer && $eb_organizer_id ) {
				update_post_meta( $ecp_organizer, '_OrganizerEventBriteID', $eb_organizer_id );

				if ( $ecp_venue && $eb_venue_id ) {
					update_post_meta( $ecp_venue, '_VenueEventBriteId' . $eb_organizer_id, $eb_venue_id );
				}
			}
		}

		/**
		 * returns filter value for tribe-post-origin.
		 * @since 1.0
		 * @author PaulHughes01
		 * @return string $origin
		 */
		public function filter_imported_origin() {
			$origin = 'eventbrite-tickets';
			return $origin;
		}

		/**
		 * add the options page for this plugin
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function add_option_page() {
			add_submenu_page(
				'/edit.php?post_type=' . TribeEvents::POSTTYPE,
				__( 'Import: Eventbrite ','tribe-eventbrite' ),
				__( 'Import: Eventbrite','tribe-eventbrite' ),
				'edit_posts',
				'import-eventbrite-events',
				array( $this, 'include_import_page' )
			);
		}

		/**
		 * include the import page view
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @return void
		 */
		public function include_import_page() {
			include_once( $this->pluginPath.'views/eventbrite/import-eventbrite-events.php' );
		}

		/**
		 * the event brite meta box
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @global userdata - the current user data
		 * @param int $postId the ID of the current event
		 * @return void
		 */
		public function add_metabox( $postId ) {
			$postData = get_post_meta( $postId, 'tribe-eventbrite-saved-data', true ); // get sent data
			delete_post_meta( $postId, 'tribe-eventbrite-saved-data' ); // delete sent data
			$EventBriteSavedPaymentOptions = array();
			foreach ( self::$metaTags as $tag ) {
				if ( ! empty( $postData[ $tag ] ) ) {
					$$tag = $postData[$tag];
					if ( substr( $tag, 0, 18 ) == '_EventBritePayment' && $$tag == 1 ){
						array_push( $EventBriteSavedPaymentOptions, $tag );
					}
					$show_tickets = true;
				} elseif ( $postId ) {
					$val = get_post_meta( $postId, $tag, true );
					$$tag = $val;
					if ( substr( $tag, 0, 18 ) == '_EventBritePayment' && $val == 1 ){
						array_push( $EventBriteSavedPaymentOptions, $tag );
					}
				} else {
					$$tag = '';
				}
			}

			$api = EventbriteAPI::instance();
			$event = $api->get_event( $postId );

			$_EventBriteId = ( isset( $event->id ) && is_numeric( $event->id ) ? $event->id : null );
			$isRegisterChecked = ( isset( $event->id ) && is_numeric( $event->id ) ? true : false );
			$displayTickets = ( $_EventShowTickets == 'yes' ) ? true : false;

			$tribe_ecp = TribeEvents::instance();

			include_once( $this->pluginPath . 'views/eventbrite/eventbrite-meta-box-extension.php' );
		}

		/**
		 * displays the Eventbrite ticket form.
		 * Heavily modified by Paul Hughes with the release of TEC 3.0.
		 *
		 * @since 1.0
		 * @author jgabois & Justin Endler
		 * @param string $content the current html content
		 * @return string filtered $content
		 */
		public static function print_ticket_form() {
			tribe_get_template_part( 'eventbrite/hooks/ticket-form' );
			tribe_get_template_part( 'eventbrite/modules/ticket-form' );
		}

		public function notice_missing_token() {
			$api = EventbriteAPI::instance();
			if ( $api->is_ready() ) {
				if ( ! empty( $_GET['success'] ) && ! empty( $_GET['page'] ) && ! empty( $_GET['post_type'] ) && ! empty( $_GET['tab'] ) && 'eventbrite' === $_GET['tab'] ) {
					echo '<div class="updated tribe-notice">';
					echo '<p>' . esc_attr__( 'Success! You have authorized this Application Key.', 'tribe-eventbrite' ) . '</p>';
					echo '</div>';
				}

				return;
			}

			$dismissed = get_transient( 'tribe-eb-dimissed-not_ready' );

			if ( ! empty( $_GET['tribe-eb-dismiss-notice'] ) ){
				$slug = sanitize_title( $_GET['tribe-eb-dismiss-notice'] );
				$dimissed = (int) set_transient( 'tribe-eb-dimissed-' . $slug, 1, WEEK_IN_SECONDS );

				return;
			}

			if ( $dismissed ){
				return;
			}

			?>
			<script type="text/javascript">
			( function( $ ){
				$( document ).ready( function(){
					$( '.tribe-eventbrite-notice' ).on( 'click', '.notice-dismiss', function(){
						var append = [ window.location.href ];
						if ( -1 === window.location.href.indexOf( '?' ) ){
							append.push( '?' )
						} else {
							append.push( '&' )
						}
						append.push( 'tribe-eb-dismiss-notice=' );
						append.push( $( this ).parents( '.tribe-eventbrite-notice' ).data( 'ref' ) )
						window.location.href = append.join('');
					} );
				} );
			}( jQuery ) );
			</script>
			<?php
			echo '<div class="notice updated is-dismissible tribe-eventbrite-notice" data-ref="not_ready">';
			echo '<p>' . sprintf( __( 'Welcome to The Events Calendar: Eventbrite Tickets! We appreciate your support and hope you enjoy the functionality this add-on has to offer. Before jumping into it, make sure you\'ve reviewed our %sEventbrite Tickets new user primer%s so you\'re familiar with the basics.', 'tribe-eventbrite' ), '<a href="' . TribeEvents::$tribeUrl . 'support/documentation/eventbrite-tickets-new-user-primer/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin" target="_blank">', '</a>' ) . '</p>';
			echo '<p>' . sprintf( __( 'Add your %s to your %s. Don\'t have an Application Key? %s and follow our %s to create a new Eventbrite Application for your WordPress site. Then simply create a new event or modify an existing one and enable Eventbrite to add and sell tickets.', 'tribe-eventbrite' ), '<a href="https://www.eventbrite.com/myaccount/apps/?ref=etckt" target="_blank">' . __( 'Eventbrite Application Keys', 'tribe-eventbrite' ) . '</a>', '<a href="' . esc_url( admin_url( 'edit.php?post_type=tribe_events&page=tribe-events-calendar&tab=eventbrite' ) ) . '">' . __( 'Eventbrite settings page', 'tribe-eventbrite' ) . '</a>', '<a href="http://www.eventbrite.com/r/etp" target="_blank">' . __( 'Sign up for Eventbrite now', 'tribe-eventbrite' ) . '</a>', '<a href="' . TribeEvents::$tribeUrl . 'support/documentation/eventbrite-tickets-new-user-primer/?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin" target="_blank">' . __( 'new user primer', 'tribe_eventbrite' ) . '</a>' ) . '</p>';
			echo '</div>';
		}

		public function notice_edit_event(){
			global $post_id, $pagenow;

			$errors = array();

			// Bail if we are not within the post editor
			if ( 'post.php' !== $pagenow ){
				return;
			}

			if ( ! tribe_is_event( $post_id ) ){
				return;
			}

			$api = EventbriteAPI::instance();

			// Bail unless the event is linked to Eventbrite
			$event = $api->get_event( $post_id );
			if ( $event && empty( $event->status ) && empty( $event->id ) ){
				// Inform the user if the event is currently in "draft" mode (on Eventbrite)
				if ( 'draft' === $event->status && ! empty( $event->ticket_classes ) ) {
					$errors[] = __( "Eventbrite status is set to DRAFT. You can update this in the 'Eventbrite Information' section further down this page.", 'tribe-eventbrite' );
				}

				// Inform the user if tickets have not yet been added on Eventbrite
				if ( empty( $event->ticket_classes ) && 'draft' !== $event->status ) {
					$errors[] = __( 'You did not create any tickets for your event.  You will not be able to publish this event on Eventbrite unless you first add a ticket at Eventbrite.com.' , 'tribe-eventbrite' );
				}
			}

			if ( empty( $errors ) ){
				return;
			}

			// Display any appropriate error messages
			foreach ( $errors as $message ) {
				printf( '<div class="error"><p>%s</p></div>', $message );
			}
		}

		/**
		 * Add the eventbrite importer toolbar item.
		 *
		 * @since 1.0.1
		 * @author PaulHughes01
		 * @return void
		 */
		public function addEventbriteToolbarItems() {
			global $wp_admin_bar;

			if ( current_user_can( 'publish_tribe_events' ) ) {
				$import_node = $wp_admin_bar->get_node( 'tribe-events-import' );
				if ( ! is_object( $import_node ) ) {
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-import',
						'title' => __( 'Import', 'tribe-events-calendar' ),
						'parent' => 'tribe-events-import-group',
					) );
				}
			}

			if ( current_user_can( 'publish_tribe_events' ) ) {
				$wp_admin_bar->add_menu( array(
					'id' => 'tribe-eventbrite-import',
					'title' => __( 'Eventbrite', 'tribe-events-calendar' ),
					'href' => trailingslashit( get_admin_url() ) . 'edit.php?post_type=tribe_events&page=import-eventbrite-events',
					'parent' => 'tribe-events-import',
				) );
			}
		}

		/**
		 * Return additional action for the plugin on the plugins page.
		 *
		 * @param array $actions
		 * @since 2.0.8
		 * @return array
		 */
		public function addLinksToPluginActions( $actions ) {
			if ( class_exists( ' TribeEvents' ) ) {
				$actions['settings'] = '<a href="' . add_query_arg( array( 'post_type' => TribeEvents::POSTTYPE, 'page' => 'import-eventbrite-events' ), admin_url( 'edit.php' ) ) .'">' . __('Import Events', 'tribe-eventbrite') . '</a>';
			}
			return $actions;
		}

		/**
		 * Adds the Eventbrite logo to the editing events form.
		 *
		 * @since 1.0.3
		 * @author PaulHughes01
		 * @return void
		 */
		public function addEventbriteLogo() {
			$image_url = trailingslashit( $this->pluginUrl ) . 'resources/images/eventbritelogo.png';
			echo '<img class="tribe-eb-logo" src="' . $image_url . '" />';
		}

		/**
		 * Return the forums link as it should appear in the help tab.
		 *
		 * @param $content
		 * @since 1.0.3
		 * @return string
		 */
		public function _link_support_forum( $content ) {
			$promo_suffix = '?utm_source=helptab&utm_medium=promolink&utm_campaign=plugin';
			return TribeEvents::$tribeUrl . 'support/forums/' . $promo_suffix;
		}

		/**
		 * Filter template paths to add the eventbrite plugin to the queue
		 *
		 * @param array $paths
		 * @return array $paths
		 * @author Jessica Yazbek
		 * @since 3.2.1
		 */
		public function add_eventbrite_template_paths( $paths ) {
			$paths['eventbrite'] = self::instance()->pluginPath;
			return $paths;
		}

		public function add_eventbrite_tab(){
			$fields = array(
				'info-start' => array(
					'type' => 'html',
					'html' => '<div id="modern-tribe-info" style="display: table;">',
				),
				'info-box-title' => array(
					'type' => 'html',
					'html' => '<h2>' . esc_attr__( 'Eventbrite', 'tribe-eventbrite' ) . '</h2>',
				),

				'info-box-description' => array(
					'type' => 'html',
					'html' => '<p style="line-height: 2em;">' . sprintf( __( 'Eventbrite Tickets needs to be connected to your Eventbrite account via an App Key/Client Secret. If you haven\'t yet configured one, do so at %s. When configuring your application, make sure to set the OAuth Redirect URI set to %s. Once your App Key and Client Secret are configured plug them in below, "Save" the page, and hit the "Get Authorization" button that appears once the Key + Secret have saved. After you\'ve been authorized you\'ll be ready to start syncing Events!', 'tribe-eventbrite' ), '<a href="http://m.tri.be/vp" target="_blank">' . __( 'http://m.tri.be/vp', 'tribe_eventbrite' ) . '</a>', '"<a href="' . home_url( '/tribe-oauth/eventbrite' ) . '" target="_blank"><em>' . home_url( '/tribe-oauth/eventbrite' ) . '</em></a>"' ) . '</p>',
				),
				'info-end' => array(
					'type' => 'html',
					'html' => '</div>',
				),
				'tribe-form-content-start' => array(
					'type' => 'html',
					'html' => '<div class="tribe-settings-form-wrap">',
				),

				'tribe-group-eb-title' => array(
					'type' => 'html',
					'html' => '<h3>' . __( 'API Configuration', 'tribe-events-calendar' ) . '</h3>',
				),

				'eventbrite-api_auth_url' => array(
					'type' => 'text',
					'size' => 'large',
					'label' => __( 'Auth URL', 'tribe-events-calendar' ),
					'tooltip' => __( 'When configuring your application, make sure to set the <strong>OAuth Redirect URI</strong> on Eventbrite to the value above.', 'tribe-events-calendar' ),
					'default' => home_url( '/tribe-oauth/eventbrite/' ),
					'value' => home_url( '/tribe-oauth/eventbrite/' ),
				),

				'eventbrite-app_key' => array(
					'type' => 'text',
					'label' => __( 'Application Key', 'tribe-events-calendar' ),
					'validation_type' => 'alpha_numeric',
				),
				'eventbrite-client_secret' => array(
					'type' => 'text',
					'label' => __( 'Client Secret', 'tribe-events-calendar' ),
					'validation_type' => 'alpha_numeric',
				),
				'eventbrite-authorize' => array(
					'type' => 'html',
					'html' => '',
				),

				// TODO: Figure out how properly close this wrapper after the license content
				'tribe-form-content-end'   => array(
					'type' => 'html',
					'html' => '</div>',
				)
			);

			$api = EventbriteAPI::instance();
			if ( ! empty( $api->key ) && ! empty( $api->secret ) ){
				$fields['eventbrite-authorize']['html'] = get_submit_button( esc_attr__( 'Get Authorization', 'tribe-eventbrite' ), 'secondary', 'tribe-eventbrite-authorize', true );
			} else {
				$fields['info-box-description']['html'] = '<div style="float:right; margin-left: 20px; margin-bottom: 5px;"><iframe src="https://player.vimeo.com/video/126437922?title=0&byline=0&portrait=0" width="350" height="196" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe><p style="text-align: right;margin-top: 5px;">' . sprintf( __( 'See our %s.' ), '<a href="http://m.tri.be/vq" target="_blank">' . esc_attr__( 'detailed walkthrough' ) . '</a>' ) . '</p></div>' . $fields['info-box-description']['html'];
			}

			new TribeSettingsTab( 'eventbrite', esc_attr__( 'Eventbrite', 'tribe-eventbrite' ), array(
				'priority'      => 50,
				'fields'        => $fields,
			) );
		}
	} // end Event_Tickets_PRO class
} // end if !class_exists Event_Tickets_PRO
