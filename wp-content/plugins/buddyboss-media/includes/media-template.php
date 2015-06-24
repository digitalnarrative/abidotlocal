<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Media
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function buddyboss_media_content_all_media( $content ){
	if( !is_main_query() )
		return $content;
	
	if( buddyboss_media()->option('all-media-page') && is_page( buddyboss_media()->option('all-media-page') ) ){
		//this is the page that was set in admin to display all media content
		//lets generate the html for all media content
		
		//Fix for MAX_JOIN_SIZE rows
		global $wpdb;
		$wpdb->query('SET SQL_BIG_SELECTS=1');
		
		//albums tab
		if( ( $mediatype = get_query_var( 'mediatype' ) )=='albums' ){
			$content .= buddyboss_media_buffer_template_part( 'global-media-albums', false );
		} else { 
			//photos tab
			//add_filter( 'buddyboss_media_screen_content_pages_sql',		'buddyboss_media_screen_content_pages_sql' );
			//add_filter( 'buddyboss_media_screen_content_sql',			'buddyboss_media_screen_content_sql' );

			$content .= buddyboss_media_buffer_template_part( 'global-media', false );

			//remove_filter( 'buddyboss_media_screen_content_sql',		'buddyboss_media_screen_content_sql' );
			//remove_filter( 'buddyboss_media_screen_content_pages_sql',	'buddyboss_media_screen_content_pages_sql' );
		}
	}
	return $content;
}
add_filter( 'the_content', 'buddyboss_media_content_all_media' );

/**
 * Filters the activity stream to display only media uploads on desired locations.
 * 
 * @since: 1.1
 */
function buddyboss_media_activity_querystring_filter( $query_string = '', $object = '' ) {
    if( $object != 'activity' )
        return $query_string;
	
    $args = wp_parse_args( $query_string, array(
        'action'  => false,
        'type'    => false,
        'user_id' => false,
        'page'    => 1
    ) );
	
	/**
	 * Are we on 'all media' page or 'photos' section of user profile ?
	 * If so, restrict activity stream to only media uploads
	 */
	if( buddyboss_media_is_media_listing() ){
		if( bp_is_user() )
            $args['user_id'] = bp_displayed_user_id();
		
		$args['type'] = 'activity_update';
		
		if( !isset( $args['meta_query'] ) || !is_array( $args['meta_query'] ) )
			$args['meta_query'] = array();
		
		/**
		 * filter activities to show photos from given single album, if we are on single album page.
		 */
		if( buddyboss_media_is_single_album() ){
			$args['meta_query'][] = array(
                /* this is the meta_key you want to filter on */
                'key'     => 'buddyboss_media_album_id',
                /* You need to get all values that are >= to 1 */
                'value'   => buddyboss_media_single_album_id(),
                'type'    => 'numeric',
                'compare' => '='
            );
		} else {
			$meta_keys = buddyboss_media_compat('activity.item_keys');
			foreach( $meta_keys as $meta_key ){
				$args['meta_query'][] = array(
					'key'     => $meta_key,
					'compare' => 'EXISTS',
				);
			}
			
			$args['meta_query']['relation']	= 'OR';
		}
		
        $query_string = empty( $args ) ? $query_string : $args;
	}
	
	return $query_string;
}
add_filter( 'bp_ajax_querystring', 'buddyboss_media_activity_querystring_filter', 12, 2 );

/**
 * load the template file by looking into childtheme, parent theme, plugin's template folder, in that order.
 * looks for buddyboss-media/$template.php inside child/parent themes.
 * 
 * @param string $template name of the template file, without '.php'
 */
function buddyboss_media_load_template($template){
	$template .= '.php';
    if(file_exists(STYLESHEETPATH.'/buddyboss-media/'.$template))
        include_once(STYLESHEETPATH.'/buddyboss-media/'.$template);
    else if(file_exists(TEMPLATEPATH.'buddyboss-media/'.$template))
        include_once (TEMPLATEPATH.'/buddyboss-media/'.$template);
    else 
        include_once buddyboss_media()->templates_dir.'/'.$template;
}

function buddyboss_media_buffer_template_part( $template, $echo=true ){
	ob_start();
	
	buddyboss_media_load_template( $template );
	// Get the output buffer contents
	$output = ob_get_clean();

	// Echo or return the output buffer contents
	if ( true === $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

global $bb_load_media_template;
/**
 * Should we load activity/buddyboss-media-entry.php template file instead of activity/entry.php ?
 * 
 * @return boolean
 */
function buddyboss_media_check_custom_activity_template_load(){
	global $bb_load_media_template;
	if ( ! $bb_load_media_template ) {
		//lets not tamper with normal activity page
		$bb_load_media_template = 'no';

		if( buddyboss_media_is_media_listing() ){
			$bb_load_media_template = 'yes';
		}

		if( $bb_load_media_template=='yes' ){
			//do anything if template override was allowed from settings
			$option = buddyboss_media()->option( 'activity-custom-template' );
			if( !$option ){
				$option = 'yes';
			}

			if( $option != 'yes' )
				$bb_load_media_template = 'no';
		}

		$bb_load_media_template = apply_filters( 'buddyboss_media_load_custom_activity_template', $bb_load_media_template );
		
		if( $bb_load_media_template=='yes' ){
			//this is probably not the best place to add this hoook.
			//
			//add new location in template stack
			//13 is between parent theme and buddypress's temlate directory
			bp_register_template_stack( 'buddyboss_media_register_template_stack', 13 );
		}
	}
	
	return $bb_load_media_template=='yes' ? true : false;
}

function buddyboss_media_load_activity_template( $templates, $slug, $name ){
	if( $slug=='activity/entry' && buddyboss_media_check_custom_activity_template_load() ){
		//must be a buddyboss media upload activity
		if( buddyboss_media_compat_get_meta( bp_get_activity_id(), 'activity.action_keys' ) ){
			$new_templates = array( 'activity/buddyboss-media-entry.php' );//should be the first in list
			if( !empty( $templates ) ){
				foreach( $templates as $template ){
					$new_templates[] = $template;
				}
			}

			$templates = $new_templates;
		}
	}
	return $templates;
}
add_filter( 'bp_get_template_part', 'buddyboss_media_load_activity_template', 10, 3 );

function buddyboss_media_register_template_stack(){
	return buddyboss_media()->templates_dir;
}

/** Fix for Jetpack plugin **/
function bbm_global_media_page_jetpack_fix() {

	if ( buddyboss_media()->option( 'all-media-page' ) && is_page( buddyboss_media()->option( 'all-media-page' ) ) ) {
		remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
	}
}

add_action( 'wp', 'bbm_global_media_page_jetpack_fix' );