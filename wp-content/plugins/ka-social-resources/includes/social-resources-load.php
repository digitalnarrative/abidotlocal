<?php

/*Load components*/
class Social_Articles_Component extends BP_Component {

    function __construct() {
        global $bp;
        parent::start(
            'social_articles',
            __( 'Social Resource', 'resources' ),
            SA_BASE_PATH
        );
        $this->includes();
        $bp->active_components[$this->id] = '1';

    }

    function includes($includes = Array()) {
       
        $includes = array(
            'includes/social-resources-screens.php',
            'includes/social-resources-functions.php',
            'includes/social-resources-manage-functions.php',
            'includes/social-resources-notifications.php',
        );
        parent::includes( $includes );
    }

    function setup_globals($args = array()) {
        global $bp;
        if ( !defined( 'SA_SLUG' ) )
            define( 'SA_SLUG', $this->id );
        $globals = array(
            'slug'                  => SA_SLUG,
            'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : SA_SLUG,
            'has_directory'         => false,
            'notification_callback' => 'social_articles_format_notifications',
            'search_string'         => __( 'Search articles...', 'social-resources' )
        );
        //if($this->check_visibility()){
            parent::setup_globals( $globals );
        //}
    }

    function setup_nav($main_nav = Array(), $sub_nav = Array()) {
        $directWorkflow = isDirectWorkflow();
        if( !ka_sr_can_edit_resource( bp_displayed_user_id() ) )
            return false;
        
        //if( bp_displayed_user_id() != bp_loggedin_user_id() )
            //return false;
        
        $main_nav = array(
            'name'                => __( 'Resources', 'social-resources' ),
            'slug'                => SA_SLUG,
            'position'            => 100,
            'screen_function'     => 'social_articles_screen',
            'default_subnav_slug' => 'resources'
        );

        $user_domain = bp_is_user() ? bp_displayed_user_domain() : bp_loggedin_user_domain();
        
        $social_articles_link = trailingslashit( $user_domain . SA_SLUG );
       
        $user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id();
        $publishCount = custom_get_user_posts_count('publish', bp_displayed_user_id());
        //$pendingCount = custom_get_user_posts_count('pending',bp_displayed_user_id());
        $draftCount = custom_get_user_posts_count('draft',bp_displayed_user_id());
        
        $sub_nav[] = array(
            'name'            =>  sprintf( __("Published", "social-resources").'<span>%d</span>', $publishCount),
            'slug'            => 'resources',
            'parent_url'      => $social_articles_link,
            'parent_slug'     => SA_SLUG,
            'screen_function' => 'social_articles_screen',
            'position'        => 10
        );
        
        if(bp_displayed_user_id()==bp_loggedin_user_id()){
            $sub_nav[] = array(
                'name'            =>  sprintf( __("Draft", "social-resources").'<span>%d</span>', $draftCount),
                'slug'            => 'draft',
                'parent_url'      => $social_articles_link,
                'parent_slug'     => SA_SLUG,
                'screen_function' => 'social_articles_screen',
                'position'        => 30
            );
        }
        
        parent::setup_nav( $main_nav, $sub_nav );
    }

    function setup_admin_bar($wp_admin_nav = Array()) {
        global $bp;
        
        $directWorkflow = isDirectWorkflow();

        if ( is_user_logged_in() ) {

            $publishCount = custom_get_user_posts_count('publish');
            //$pendingCount = custom_get_user_posts_count('pending');
            $draftCount = custom_get_user_posts_count('draft');

            $user_domain =  bp_loggedin_user_domain();

            $wp_admin_nav[] = array(
                'parent' => 'my-account-buddypress',
                'id'     => 'my-account-social-resources',
                'title'  => __( 'Resources', 'social-resources' ),
                'href'   => trailingslashit( $user_domain.'resources' )
            );


            $wp_admin_nav[] = array(
                'parent' => 'my-account-social-resources',
                'title'  => sprintf( __("Published", "social-resources").'<span class="count">%d</span>', $publishCount),
                'href'   => trailingslashit( $user_domain.'resources/' )
            );
            
            $wp_admin_nav[] = array(
                'parent' => 'my-account-social-resources',
                'title'  => sprintf( __("Draft", "social-resources").'<span class="count">%d</span>', $draftCount),
                'href'   => trailingslashit( $user_domain.'resources/draft/' )
            );

			/*
            $wp_admin_nav[] = array(
                'parent' => 'my-account-social-resources',
                'title'  => sprintf( __( 'Add New Resource', 'social-resources' )),
                'href'   => trailingslashit( $user_domain.'resources/new' )
            );*/
        }
        if( ka_sr_can_edit_resource( bp_displayed_user_id()) ){
            parent::setup_admin_bar( $wp_admin_nav );
        }
    }

    function check_visibility(){
        return current_user_can('edit_posts') || !is_user_logged_in() || user_can(bp_displayed_user_id(), 'edit_posts');
    }


}

function social_articles_load_core_component() {
    global $bp;
    $bp->social_articles = new Social_Articles_Component;
    do_action('social_articles_load_core_component');
}
add_action( 'bp_loaded', 'social_articles_load_core_component' );
?>
