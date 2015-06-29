<?php
/*
Plugin Name: Groups Extras menu order fix
Plugin URI: #
Description: Fixed nav re-ordering for BuddyPress Group Extras plugin
Version: 1.0.0
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'GEMOF_VERSION', '1.0.0' ); # Maybe Needed In Future.
define( 'GEMOF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );  # URL to plugin dir.
define( 'GEMOF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); # Path to plugin Dir.
define( 'GEMOF_PREFIX', 'gemof_' ); # Path to plugin Dir.

/**
* Load Library Class
*/
include(dirname(__FILE__)."/include/class.gemof_common.php");


/**
* Initialization required class
*/

if( function_exists('bpge_load') ){
    global $gemof;
    $gemof["gemof_common"] = new gemof_common(); //contain some common functions ..

    remove_action( 'bp_init', 'bpge_load' );
    add_action( 'bp_init', array($gemof["gemof_common"], 'gemof_bpge_load') );
}

