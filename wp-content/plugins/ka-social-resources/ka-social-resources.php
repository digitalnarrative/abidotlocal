<?php
/*
 Plugin Name: Kemarise Social Resource
 Description: Kemarise Social Resource
 Version: 1.0.0
 Author: #
 Author URI: #
 Text Domain: social-resources
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if (!class_exists('SocialArticles') && is_plugin_active( 'buddypress/bp-loader.php' )) {

    require_once plugin_dir_path( __FILE__ ) . 'includes/social-resources-tools.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';

    class SocialArticles extends KA_SA_Plugin_Admin {

        var $options;
        public function __construct() {
            global $sa_actions;
            $sa_actions = array('resources', 'draft', 'publish', 'under-review', 'new');
            $this -> options = get_option('social_articles_options');
            $this -> loadConstants();
            add_action('plugins_loaded', array(&$this, 'start'));
            add_action( 'bp_include', array(&$this, 'bpInit'));
            register_activation_hook(__FILE__, array(&$this, 'activate'));
        }

        public function start() {
            load_plugin_textdomain('social-resources', false, SA_DIR_NAME . '/languages');
            add_filter('plugin_action_links_' . SA_BASE_NAME, array($this, 'pluginActionLinks'));


            if ( is_admin() ){
                add_action('admin_menu', array(&$this,'adminMenu'));
                add_action('admin_print_scripts', array(&$this,'loadAdminScripts'));
                add_action('admin_print_styles', array(&$this,'loadAdminStyles'));
		add_action('wp_ajax_gtype_completion_post_photo', array( &$this, 'gtype_media_post_photo' ) );
            }else{
                add_action('wp_print_scripts', array(&$this, 'loadScripts'));
                add_action('wp_print_styles', array(&$this, 'loadStyles'));
				
            }
        }

        public function bpInit() {
            if ( version_compare( BP_VERSION, '1.5', '>' ) )
                require(dirname(__FILE__) . '/includes/social-resources-load.php');
        }

        private function loadConstants() {
            define('SA_PLUGIN_DIR', dirname(__FILE__));
            define('SA_SLUG', 'resources');
            define('SA_ADMIN_SLUG', 'social-resources');
            define('SA_DIR_NAME', plugin_basename(dirname(__FILE__)));
            define('SA_BASE_NAME', plugin_basename(__FILE__));
            define('SA_BASE_PATH', WP_PLUGIN_DIR . '/' . SA_DIR_NAME);
            define('SA_BASE_URL', WP_PLUGIN_URL . '/' . SA_DIR_NAME);

            $upload_dir = wp_upload_dir();
            define('SA_TEMP_IMAGE_URL',$upload_dir['baseurl'].'/');
            define('SA_TEMP_IMAGE_PATH',$upload_dir['basedir'].'/');
        }

        public function activate(){

            $options = get_option('social_articles_options');

            if (!isset($options['post_per_page']))
                $options['post_per_page'] = '10';

            if (!isset($options['excerpt_length']))
                $options['excerpt_length'] = '30';

            if (!isset($options['excerpt_length']))
                $options['category_type'] = 'single';

            if (!isset($options['workflow']))
                $options['workflow'] = 'approval';

            if (!isset($options['bp_notifications']))
                $options['bp_notifications'] = 'true';

            if (!isset($options['allow_author_adition']))
                $options['allow_author_adition'] = 'true';

            if (!isset($options['allow_author_deletion']))
                $options['allow_author_deletion'] = 'true';

            update_option('social_articles_options', $options);
        }

        public function deactivate(){
        }

        public function loadScripts() {
            global $bp, $sa_actions;
            if( in_array($bp->current_action, $sa_actions) || is_page( ka_misc_settings( 'page_add_resource' ) ) ){

                if (!wp_script_is( 'jquery', 'queue' )){
                    wp_enqueue_script( 'jquery' );
                }

                if (!wp_script_is( 'jquery-ui-core', 'queue' )){
                    wp_enqueue_script( 'jquery-ui-core' );
                }

                wp_enqueue_script('ajaxupload', SA_BASE_URL . '/assets/js/ajaxupload.js', array( 'jquery' ));
                wp_enqueue_script('jquery.templates', SA_BASE_URL . '/assets/js/jquery.tmpl.min.js', array( 'jquery' ));
                wp_enqueue_script('advance', SA_BASE_URL . '/assets/js/advanced.js');
                wp_enqueue_script('wysihtml5', SA_BASE_URL . '/assets/js/wysihtml5-0.3.0.min.js');
                wp_enqueue_script('social-resources', SA_BASE_URL . '/assets/js/social-resources.js');
                wp_localize_script( 'social-resources', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'baseUrl' =>SA_BASE_URL,
                    'tmpImageUrl' =>SA_TEMP_IMAGE_URL) );
            }

        }

        public function loadAdminScripts (){
            if (isset($_GET['page']) && $_GET['page'] == SA_ADMIN_SLUG) {
                wp_enqueue_script('postbox');
                wp_enqueue_script('dashboard');
            }
        }

        public function loadAdminStyles() {
            if (isset($_GET['page']) && $_GET['page'] == SA_ADMIN_SLUG) {
                wp_enqueue_style('dashboard');
                wp_enqueue_style('global');
                wp_enqueue_style('wp-admin');
                wp_register_style( 'admin-stylesheet', SA_BASE_URL . '/assets/css/admin-stylesheet.css', null, true );
                wp_enqueue_style( 'admin-stylesheet' );
            }
        }

        public function loadStyles() {
            global $bp, $sa_actions;
            if(in_array($bp->current_action, $sa_actions) || is_page( ka_misc_settings( 'page_add_resource' ) ) ){
                wp_register_style( 'stylesheet',SA_BASE_URL.'/assets/css/stylesheet.css', array(),'20140825','all' );
                wp_enqueue_style( 'stylesheet' );
            }
        }
		
		function gtype_media_post_photo(){
		
		global $bp;
		if ( !is_user_logged_in() ) {
			echo '-1';
			return false;
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) )
		{
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
			require_once(ABSPATH . "wp-admin" . '/includes/file.php');
			require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		}

		if ( ! function_exists('media_handle_upload' ) )
		{
			require_once(ABSPATH . 'wp-admin/includes/admin.php');
		}

		$aid = media_handle_upload( 'file', 0 );

		// Image rotation fix
		do_action( 'buddyboss_media_add_attachment', $aid );

		$attachment = get_post( $aid );

		$name = $url = null;

		if ( $attachment !== null )
		{
			$name = $attachment->post_title;

			//$img_size = 'buddyboss_media_photo_wide';
			$img_size = 'buddyboss_media_photo_tn';

			$url_nfo = wp_get_attachment_image_src( $aid, $img_size );

			$url = is_array( $url_nfo ) && !empty( $url_nfo ) ? $url_nfo[0] : null;
		}

		$result = array(
			'status'          => ( $attachment !== null ),
			'attachment_id'   => (int)$aid,
			'url'             => esc_url( $url ),
			'name'            => esc_attr( $name )
		);

		echo htmlspecialchars( json_encode( $result ), ENT_NOQUOTES );

		exit(0);
	}

        public function adminMenu() {
            include (SA_BASE_PATH . '/includes/social-resources-options.php');
            add_options_page('Social Resource', 'Social Resource', 'manage_options', SA_ADMIN_SLUG, 'social_articles_page');
        }

        public function pluginActionLinks($links) {
            $settings_link = '<a href="' . menu_page_url( SA_ADMIN_SLUG, false ) . '">'
                . esc_html( __( 'Settings', 'social-resources' ) ) . '</a>';

            array_unshift( $links, $settings_link );

            return $links;
        }


    }
    /*
     * Initiate the plug-in.
     */
    global $socialArticles;
    $socialArticles = new SocialArticles();
}else {
    add_action( 'admin_notices', 'bp_social_articles_install_buddypress_notice');
}

function bp_social_articles_install_buddypress_notice() {
    echo '<div id="message" class="error fade"><p style="line-height: 150%">';
    _e('<strong>Social Resource</strong></a> requires the BuddyPress plugin to work. Please <a href="http://buddypress.org">install BuddyPress</a> first, or <a href="plugins.php">deactivate Social Resource</a>.', 'social-resources');
    echo '</p></div>';
}

if ( ! function_exists('resource_custom_post_type') ) {

// Register Custom Post Type
function resource_custom_post_type() {

	$labels = array(
		'name'                => _x( 'Resources', 'Post Type General Name', 'social-resources' ),
		'singular_name'       => _x( 'Resource', 'Post Type Singular Name', 'social-resources' ),
		'menu_name'           => __( 'Resources', 'social-resources' ),
		'name_admin_bar'      => __( 'Resources', 'social-resources' ),
		'parent_item_colon'   => __( 'Parent Item:', 'social-resources' ),
		'all_items'           => __( 'All Items', 'social-resources' ),
		'add_new_item'        => __( 'Add New Item', 'social-resources' ),
		'add_new'             => __( 'Add New', 'social-resources' ),
		'new_item'            => __( 'New Item', 'social-resources' ),
		'edit_item'           => __( 'Edit Item', 'social-resources' ),
		'update_item'         => __( 'Update Item', 'social-resources' ),
		'view_item'           => __( 'View Item', 'social-resources' ),
		'search_items'        => __( 'Search Item', 'social-resources' ),
		'not_found'           => __( 'Not found', 'social-resources' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'social-resources' ),
	);
	$args = array(
		'label'               => __( 'resource', 'social-resources' ),
		'description'         => __( 'These are Resources', 'social-resources' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'trackbacks', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'resource', $args );

}

// Hook into the 'init' action
add_action( 'init', 'resource_custom_post_type', 0 );

}

add_action( 'bp_loaded', 'sl_gtypes_additional_tabs_init' );
function sl_gtypes_additional_tabs_init(){
    include_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class.Resource_Group_Extension.php';
}
