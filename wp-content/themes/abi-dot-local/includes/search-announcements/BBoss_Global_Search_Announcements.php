<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'BBoss_Global_Search_Announcements' ) ):

	/**
	 *
	 * BuddyPress Global Search  - search announcements
	 * **************************************
	 *
	 *
	 */
	class BBoss_Global_Search_Announcements extends BBoss_Global_Search_Type {

		private $type = 'announcements';

		/**
		 * Insures that only one instance of Class exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @return object BBoss_Global_Search_Announcements
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if ( null === $instance ) {
				$instance = new BBoss_Global_Search_Announcements();
			}

			// Always return the instance
			return $instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @since 1.0.0
		 */
		private function __construct() { /* Do nothing here */
		}

		function sql( $search_term, $only_totalrow_count = false ) {
			
			global $wpdb, $bp;

			$query_placeholder = array();

			$sql = " SELECT ";

			if ( $only_totalrow_count ) {
				$sql .= " COUNT( DISTINCT id ) ";
			} else {
				$sql .= " DISTINCT a.id , 'activity' as type, a.content LIKE '%%%s%%' AS relevance, a.date_recorded as entry_date  ";
				$query_placeholder[] = $search_term;
			}

			//searching only activity updates, others don't make sense
			$sql .= " FROM 
						{$bp->activity->table_name} a 
					WHERE 
						1=1 
						AND is_spam = 0 
						AND a.content LIKE '%%%s%%' 
						AND a.hide_sitewide = 1 
						AND a.type = 'activity_update' 
				";
			$query_placeholder[] = $search_term;
			return $wpdb->prepare( $sql, $query_placeholder );
		}

		protected function generate_html( $template_type = '' ) {
			$post_ids_arr = array();
			foreach ( $this->search_results[ 'items' ] as $item_id => $item_html ) {
				$post_ids_arr[] = $item_id;
			}

			$post_ids = implode( ',', $post_ids_arr );

			if ( bp_has_activities( array( 'include' => $post_ids, 'per_page' => count( $post_ids_arr ) ) ) ) {
				while ( bp_activities() ) {
					bp_the_activity();

					$result = array(
						'id' => bp_get_activity_id(),
						'type' => $this->type,
						'title' => $this->search_term,
						'html' => buddyboss_global_search_buffer_template_part( 'loop/activity', $template_type, false ),
					);

					$this->search_results[ 'items' ][ bp_get_activity_id() ] = $result;
				}
			}
		}

	}

// End class BBoss_Global_Search_Posts

endif;
?>