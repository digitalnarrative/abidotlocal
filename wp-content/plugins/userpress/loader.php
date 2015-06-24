<?php
/*

 * Plugin Name: UserPress
 * Plugin URI:  http://www.userpress.org
 * Description: UserPress Suite adds wiki functionality to your WordPress site along with other features (automatic table of contents, revision notes, content moderation, and more.)
 * Author:      UserPress
 * Author URI:  http://www.userpress.org
 * Version:     2.0.9
 
 */
 

include dirname(__FILE__) . '/enhanced-404/e404.php';
include dirname(__FILE__) . '/wiki/wiki.php';
include dirname(__FILE__) . '/query-filter/qfilter.php';
include dirname(__FILE__) . '/toc/toc.php';
include dirname(__FILE__) . '/revisions/custom-field-revisions.php';
include dirname(__FILE__) . '/post-favorites/post-favorites.php';
include dirname(__FILE__) . '/comments-query/comments-query.php';
include dirname(__FILE__) . '/buddypresssubscriptions/bps.php';
include dirname(__FILE__) . '/print-view/pv.php';
include dirname(__FILE__) . '/page-tree/pt.php';




// ADD DEFAULT WIKI PAGE UPON INSTALL 
register_activation_hook( __FILE__, 'up546E_insert_default_page');

function up546E_insert_default_page()
  {
   //post status and options
    $post = array(
          'post_author' => 1,
          'post_name' => 'frontpage',
          'post_content' => 'This is your first wiki page. You should edit it.',          
          'post_status' => 'publish' ,
          'post_title' => 'Frontpage',
          'post_type' => 'userpress_wiki',
    );  
 
 
     $page_exists = get_page_by_path( 'frontpage', OBJECT, 'userpress_wiki' );

    if( $page_exists == null ) {
        // Page doesn't exist, so lets add it
        
 //insert page and save the id
    $newvalue = wp_insert_post( $post, false );
    //save the id in the database

    } 
 
}

function up546E_activate_flushrewrite() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules( false );
}

register_activation_hook( __FILE__, 'up546E_activate_flushrewrite' );

global $bpsubscriptions_db_version;
$bpsubscriptions_db_version = "1.0";
	
function up546E_bps_install_db() {
   global $wpdb;
   global $bpsubscriptions_db_version;

   $table_name = $wpdb->base_prefix . "bps_post_subscriptions";
	  
   $sql = "CREATE TABLE $table_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  post_id bigint(20) NOT NULL,
  user_id bigint(20) NOT NULL,
  blog_id bigint(20) NOT NULL,
  UNIQUE KEY id (id)
	);";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);
 
   add_option("bpsubscriptions_db_version", $bpsubscriptions_db_version);
}

register_activation_hook(__FILE__,'up546E_bps_install_db');

// END DEFAULT PAGE 


/* hook updater to init */
add_action( 'init', 'userpress_updater_init' );

/**
 * Load and Activate Plugin Updater Class.
 */
function userpress_updater_init() {

    /* Load Plugin Updater */
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'UserPress_Updater.php' );

    /* Updater Config */
    $config = array(
        'base'      => plugin_basename( __FILE__ ), //required
        'dashboard' => false,
        'username'  => false,
        'key'       => '',
        'repo_uri'  => 'http://userpie.com/',
        'repo_slug' => 'userpress',
    );

    /* Load Updater Class */
    new UserPress_Updater( $config );
}
  
  
?>
