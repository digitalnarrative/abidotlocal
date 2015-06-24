<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'BBoss_Global_Search_Announcements_Loader' ) ):

	class BBoss_Global_Search_Announcements_Loader {

		private $search_type = 'announcements',
				$search_type_label = 'announcements';

		/**
		 * Empty constructor function to ensure a single instance
		 */
		public function __construct() {
			// ... leave empty, see Singleton below
		}

		/**
		 * singleton
		 *
		 * @return object BBoss_Global_Search_AWPCP_Loader
		 */
		public static function instance() {
			static $instance = null;

			if ( null === $instance ) {
				$instance = new BBoss_Global_Search_Announcements_Loader();
				$instance->setup();
			}

			return $instance;
		}

		private function setup() {

			/**
			 * The filter below can be used, if you need some other text insted of 'Classifieds'.
			 */
			$this->search_type_label = apply_filters( 'bboss_global_search_label_awpcp_ad_listing', __( 'Announcements', 'buddypress-global-search' ) );

			//1. display setting
			add_action( 'bboss_global_search_settings_items_to_search', array( $this, 'print_announcement_search_option' ) );

			//2. load search helper
			add_filter( 'bboss_global_search_additional_search_helpers', array( $this, 'load_search_helper' ) );

			//3. filter search type display text
			add_filter( 'bboss_global_search_label_search_type', array( $this, 'search_type_label' ) );
		}

		/**
		 * Print 'Classified listings' on settings screen.
		 * @param array $items_to_search
		 */
		public function print_announcement_search_option( $items_to_search ) {
			$checked = ! empty( $items_to_search ) && in_array( $this->search_type, $items_to_search ) ? ' checked' : '';
			echo "<label><input type='checkbox' value='{$this->search_type}' name='buddyboss_global_search_plugin_options[items-to-search][]' {$checked}>{$this->search_type_label}</label><br>";
		}

		public function load_search_helper( $helpers ) {
			require_once get_stylesheet_directory() . '/includes/search-announcements/BBoss_Global_Search_Announcements.php';
			$helpers[ $this->search_type ] = BBoss_Global_Search_Announcements::instance();

			return $helpers;
		}

		public function search_type_label( $label ) {
			if ( $label == $this->search_type ) {
				$label = $this->search_type_label;
			}
			return $label;
		}

	}

	endif;

BBoss_Global_Search_Announcements_Loader::instance(); //instantiate