<?php
/* 
Plugin Name: Abilocal Core
Description: All Abilocal Custom Features and Functionality.
Version: 0.0.1
Author: BuddyBoss
Author URI: http://www.buddyboss.com
*/

//error_reporting(0);

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


class abilocal {
   
   var $plugin_url;
   var $plugin_dir;
   var $includes_url;
   var $includes_dir;
   var $plugin_prefix;
   var $plugin_version;
   var $domain;
   var $plugin_domain;
   var $c; //contain the classes
   
   function __construct() {
        
        $this->plugin_version = '1.0';
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        $this->includes_dir = $this->plugin_dir.'/include';
        $this->includes_url = $this->plugin_url.'/include';
        $this->plugin_prefix = "al";
        $this->domain = "al_text";
        $this->c = new stdClass();
        
        //register all hooks.
        $this->hooks();
                
   }
   
   function hooks() {
        add_action( 'wp_enqueue_scripts', array($this,"enqueue_assets") );
        add_action( 'admin_enqueue_scripts', array($this,"admin_enqueue_assets") );
        add_action( 'plugins_loaded', array($this,"load_textdomain") );
        
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
	wp_enqueue_style( $this->plugin_prefix.'font-awesome', $this->plugin_url."asset/font-awesome.css" );
	wp_enqueue_script( $this->plugin_prefix.'action', $this->plugin_url."asset/action-admin.js", array('jquery'), '1.0.0', true );
    }
   
   /*
    * Load the plugin language
    */
   function load_textdomain() {
        load_plugin_textdomain( $this->plugin_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' ); 
    }
   
   function load_classes() {
    
    //list all of classes.
    $classes = array(
                     "al_common",
                     "al_reply_from_email",
                     );
    
    foreach($classes as $c) {
        $file = dirname(__FILE__)."/include/class.".$c.".php";
        if(file_exists($file)) {
            include(dirname(__FILE__)."/include/class.".$c.".php");
            $this->c->$c = new $c;
        }
    }
    
   }   
   
}

global $abilocal;
//load the main class
$abilocal = new abilocal();
$abilocal->load_classes();

/*
 * Easy to call function.
 **/
function abilocal() {
    global $abilocal;
    return $abilocal;
}

