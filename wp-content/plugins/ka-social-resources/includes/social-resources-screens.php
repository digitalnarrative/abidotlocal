<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;



/**
 * social_articles_screen()
 *
 * Sets up and displays the screen output for the sub nav item "social-resources/screen-one"
 */
function social_articles_screen00() {
	global $bp;
	bp_update_is_directory( true, 'resources' );
	/* Add a do action here, so your component can be extended by others. */
	do_action( 'social_articles_screen' );
        
	bp_core_load_template( apply_filters( 'social_articles_screen', 'members/single/resources' ) );
}

function social_articles_screen(){
	global $bp;
	bp_update_is_directory( true, 'resources' );
	/* Add a do action here, so your component can be extended by others. */
	//do_action( 'social_articles_screen' );

	add_action( 'bp_template_title', 'social_articles_screen_title' );
    add_action( 'bp_template_content', 'social_articles_screen_content' );
    bp_core_load_template( apply_filters('bp_core_template_plugin', 'members/single/plugins') );
}

function social_articles_screen_title(){
    $bp = buddypress();
    $title = __( 'My resources', 'TEXTDOMAIN' );
    switch($bp->current_action){
        case 'new':
            $title .= ' - '  . __( 'New', 'TEXTDOMAIN' );
            break;
        case 'draft':
            $title .= ' - '  . __( 'Draft', 'TEXTDOMAIN' );
            break;
        case 'resources':
        default:
            $title .= ' - '  . __( 'Published', 'TEXTDOMAIN' );
            break;
    }
    echo $title;
}

function social_articles_screen_content(){
    $bp = buddypress();
    switch($bp->current_action){
        case 'new':
            social_articles_load_sub_template( 'members/single/resources/new' );
            break;
        
        case 'draft':
            social_articles_load_sub_template( 'members/single/resources/draft' );
            break;
        case 'under-review':
            social_articles_load_sub_template( 'members/single/resources/pending' );
            break;
        case 'resources':
        default:
            social_articles_load_sub_template( 'members/single/resources/loop' );;
            break;
    }
}

/**
 * my_articles_screen()
 *
 * Sets up and displays the screen output for the sub nav item "social-resources/screen-two"
 */
function my_articles_screen() {
	global $bp;
	bp_update_is_directory( true, 'my_articles' );
	/* Add a do action here, so your component can be extended by others. */
	//do_action( 'my_articles_screen' );
	//bp_core_load_template( 'members/single/resources/loop' );
}

/**
 * new_article_screen()
 *
 * Sets up and displays the screen output for the sub nav item "social-resources/screen-two"
 */
function new_article_screen() {
	global $bp;
	bp_update_is_directory( true, 'new_article' );
	
	/* Add a do action here, so your component can be extended by others. */
	do_action( 'new_article_screen' );
	
	bp_core_load_template( apply_filters( 'new_article_screen', 'members/single/resources' ) );
}



/**
 * draft_article_screen()
 *
 * Sets up and displays the screen output for the sub nav item "social-resources/screen-two"
 */
function draft_articles_screen() {
    global $bp;
    bp_update_is_directory( true, 'draft_articles' );
    do_action( 'draft_articles_screen' );
    bp_core_load_template( apply_filters( 'draft_articles_screen', 'members/single/resources' ) );
}


/**
 * draft_article_screen()
 *
 * Sets up and displays the screen output for the sub nav item "social-resources/screen-two"
 */
function pending_articles_screen() {
    global $bp;
    bp_update_is_directory( true, 'pending_articles' );
    do_action( 'pending_articles_screen' );
    bp_core_load_template( apply_filters( 'pending_articles_screen', 'members/single/resources' ) );
}



if ( class_exists( 'BP_Theme_Compat' ) ) {

    class SA_Theme_Compat {


        public function __construct() {

            add_action( 'bp_setup_theme_compat', array( $this, 'is_bp_plugin' ) );
        }

        public function is_bp_plugin() {

            if ( bp_is_current_component( 'resources' ) ) {
                // first we reset the post
                add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
                // then we filter ‘the_content’ thanks to bp_replace_the_content
                add_filter( 'bp_replace_the_content', array( $this, 'directory_content'    ) );
            }
        }

        public function directory_dummy_post() {

        }

        public function directory_content() {
            bp_buffer_template_part( 'members/single/home' );
            bp_buffer_template_part( 'members/single/resources' );
        }
    }

    //new SA_Theme_Compat ();


    function bp_sa_add_template_stack( $templates ) {

        if ( bp_is_current_component( 'social_articles' ) && !bp_sa_is_bp_default() ) {
            $templates[] = SA_PLUGIN_DIR . '/includes/templates';
        }
        return $templates;
    }

    add_filter( 'bp_get_template_stack', 'bp_sa_add_template_stack', 10, 1 );
}
