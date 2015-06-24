<?php
/* 
Plugin Name: BuddyBoss Group Custom Fields 
Description: Add power to add custom fields into buddypress.
Version: 0.0.1
Author: BuddyBoss
Author URI: http://www.buddyboss.com
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class bb_gcf {
   
   var $plugin_url;
   var $plugin_dir;
   var $plugin_prefix;
   var $plugin_version;
   var $domain;
   var $c; //contain the classes
   
   function __construct() {
        
        $this->plugin_version = '1.0';
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        $this->plugin_prefix = "bb_gcf";
        $this->domain = "bb_gcf";
        $this->c = new stdClass();
        
        //register all hooks.
        $this->hooks();
                
   }
   
   function hooks() {
        add_action( 'wp_enqueue_scripts', array($this,"enqueue_assets") );
        add_action( 'admin_enqueue_scripts', array($this,"admin_enqueue_assets") );
        add_action( 'plugins_loaded', array($this,"load_textdomain") );
	register_activation_hook( __FILE__, array($this,'on_plugin_activate') );
	//$this->on_plugin_activate();
   }
   
   /*
    * load all core style and js files.
    */
   function enqueue_assets() {
	wp_enqueue_style( $this->plugin_prefix.'style', $this->plugin_url."asset/style.css" );
	wp_enqueue_script( $this->plugin_prefix.'action', $this->plugin_url."asset/action.js", array('jquery'), '1.0.0', true );
    }
    
    /*
    * load all core style and js files for backend.
    */
    function admin_enqueue_assets() {
	wp_enqueue_style( $this->plugin_prefix.'style', $this->plugin_url."asset/style-admin.css" );
	wp_enqueue_script( $this->plugin_prefix.'action', $this->plugin_url."asset/action-admin.js", array('jquery'), '1.0.0', true );
	
    }
   
   /*
    * Load the plugin language
    */
   function load_textdomain() {
        load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' ); 
    }
   
   function load_classes() { 
    
    //list all of classes.
    $classes = array(
                     "bb_gcf_field" =>  false,
                     "bb_gcf_main"  =>  true,
                     "bb_gcf_admin" =>  true,
                     "bb_gcf_groups" =>  true,
                     ); 
    
    foreach($classes as $c => $load) {
        $file = dirname(__FILE__)."/include/class.".$c.".php";
        if(file_exists($file)) {
            include(dirname(__FILE__)."/include/class.".$c.".php");
           
            if($load) {
                $this->c->{$c} = new $c;
            }
        }
    }
    
   }
   
   function on_plugin_activate() {
	
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'bb_gcf';
	
	$sql = "CREATE TABLE $table_name (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  field_name varchar(255) NOT NULL,
	  field_desc varchar(255) NOT NULL,
	  field_type varchar(255) NOT NULL,
	  is_required int(1) NOT NULL,
	  UNIQUE KEY id (id)
	  ) $charset_collate;";
	
	dbDelta( $sql ); 
	
   }
   
}

global $bb_gcf;
//load the main class
$bb_gcf = new bb_gcf();
$bb_gcf->load_classes();

/*
 * Easy to call function.
 **/
function bb_gcf() {
    global $bb_gcf;
    return $bb_gcf;
}
