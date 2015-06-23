<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

/**
 * Sets up theme defaults
 *
 * @since BuddyBoss 3.0
 */
function buddyboss_child_setup()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   * Read more at: http://www.buddyboss.com/tutorials/language-translations/
   */

  // Translate text from the PARENT theme.
	load_theme_textdomain( 'buddyboss', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss' instances in all child theme files to 'buddyboss_child'.
	load_theme_textdomain( 'buddyboss_child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_child_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since BuddyBoss 3.0
 */
function buddyboss_child_scripts_styles()
{
  /*
   * Styles
   */
  // Premium Web Fonts
  wp_enqueue_style( 'premium-web-font', get_stylesheet_directory_uri() . '/fonts/style.css' );

  wp_enqueue_style( 'header-account', get_stylesheet_directory_uri().'/css/header-account.css' );
  wp_enqueue_style( 'buddyboss-child-custom', get_stylesheet_directory_uri().'/css/custom.css' );
  wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri(). '/js/custom-scripts.js', array( 'jquery' ),'1.0',true );
  wp_dequeue_script( 'buddyboss-main' ); 
  wp_enqueue_script( 'buddyboss-js', get_stylesheet_directory_uri(). '/js/buddyboss.js', array( 'jquery' ),'1.0',true );

  // Remove filterbar CSS
	wp_dequeue_style( 'Tribe__Events__Filterbar__View-css' );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here


function add_second_footer_menu(){
	register_nav_menus( array(
		'second-footer-menu'   => __( 'Second Footer Menu', 'buddyboss' ),
	) );
}
add_action('after_setup_theme', 'add_second_footer_menu' );

//City Taxonomy
add_action( 'init', 'abi_create_taxonomies' );

function abi_create_taxonomies() {

	/* City Taxonomy */
	register_taxonomy( 'cities-taxonomy', 'post', array(
		'hierarchical' => true,
		'update_count_callback' => '',
		'rewrite' => true,
		'query_var' => 'cities-taxonomy',
		'public' => true,
		'show_ui' => null,
		'show_tagcloud' => null,
		'_builtin' => false,
		'labels' => array(
			'name' => _x( 'Cities Taxonomies', 'taxonomy general name', 'buddyboss' ),
			'singular_name' => _x( 'City Taxonomy', 'taxonomy singular name', 'buddyboss' ),
			'search_items' => __( 'Search City Taxonomies', 'buddyboss' ),
			'all_items' => __( 'All Custom Cities', 'buddyboss' ),
			'parent_item' => array( null, __( 'Parent City Taxonomy', 'buddyboss' ) ),
			'parent_item_colon' => array( null, __( 'Parent City Taxonomy:', 'buddyboss' ) ),
			'edit_item' => __( 'Edit City Taxonomy', 'buddyboss' ),
			'view_item' => __( 'View City Taxonomy', 'buddyboss' ),
			'update_item' => __( 'Update City Taxonomy', 'buddyboss' ),
			'add_new_item' => __( 'Add New City Taxonomy', 'buddyboss' ),
			'new_item_name' => __( 'New City Taxonomy Name', 'buddyboss' ) ),
		'capabilities' => array(),
		'show_in_nav_menus' => null,
		'label' => __( 'City Taxonomies', 'buddyboss' ),
		'sort' => true,
		'args' => array( 'orderby' => 'term_order' ) )
	);
	
	/* User feed Taxonomy */
	register_taxonomy( 'userfeed-taxonomy', 'post', array(
		'hierarchical' => true,
		'update_count_callback' => '',
		'rewrite' => true,
		'query_var' => 'userfeed-taxonomy',
		'public' => true,
		'show_ui' => null,
		'show_tagcloud' => null,
		'_builtin' => false,
		'labels' => array(
			'name' => _x( 'User Feed Taxonomies', 'taxonomy general name', 'buddyboss' ),
			'singular_name' => _x( 'User Feed Taxonomy', 'taxonomy singular name', 'buddyboss' ),
			'search_items' => __( 'User Feed Taxonomies', 'buddyboss' ),
			'all_items' => __( 'All Custom User Feed', 'buddyboss' ),
			'parent_item' => array( null, __( 'Parent User Feed Taxonomy', 'buddyboss' ) ),
			'parent_item_colon' => array( null, __( 'Parent User Feed Taxonomy:', 'buddyboss' ) ),
			'edit_item' => __( 'Edit User Feed Taxonomy', 'buddyboss' ),
			'view_item' => __( 'View User Feed Taxonomy', 'buddyboss' ),
			'update_item' => __( 'Update User Feed Taxonomy', 'buddyboss' ),
			'add_new_item' => __( 'Add New User Feed Taxonomy', 'buddyboss' ),
			'new_item_name' => __( 'New User Feed Taxonomy Name', 'buddyboss' ) ),
		'capabilities' => array(),
		'show_in_nav_menus' => null,
		'label' => __( 'User Feed Taxonomies', 'buddyboss' ),
		'sort' => true,
		'args' => array( 'orderby' => 'term_order' ) )
	);
}

//Adding a term in Cities taxonomy when group is created

add_action('groups_group_create_complete','abi_create_group_term');

function abi_create_group_term( $group_id ) {
	
	$group_info = groups_get_group( array( 'group_id' => $group_id ) );
	$city_term = strtolower(str_replace( ' ', '-', $group_info->name ));
	
	wp_insert_term( $city_term, 'cities-taxonomy' );
	
}

//Removing term when group is deleted

add_action('groups_before_delete_group','abi_delete_group_term');

function abi_delete_group_term( $group_id ) {
	
	$group_info = groups_get_group( array( 'group_id' => (int)$group_id ) );
	$city_term = strtolower(str_replace( ' ', '-', $group_info->name ));
	$term = get_term_by('slug', $city_term, 'cities-taxonomy');
	
	wp_delete_term( (int)$term->term_id, 'cities-taxonomy' );
	
}

/**
 * Load a template
 * @param type $template
 */
function abi_load_template( $template ) {
	
	$load = get_stylesheet_directory().'/includes/'.$template;
    include_once $load;
}

//this function returns the generated content for group blogs
function abi_get_page_content(){
	abi_load_template('home.php');
}

function abi_get_announcement_content() {
	abi_load_template( 'announcement-activity-loop.php' );
}

//setup blog subnav
add_action( 'groups_setup_nav', 'abi_setup_nav' );

function abi_setup_nav( $current_user_access ) {

	if ( ! bp_is_group() )
		return;

	$current_group = groups_get_current_group();

	$group_link = bp_get_group_permalink( $current_group );

	bp_core_new_subnav_item( array(
		'name' => __( 'Blog', 'buddyboss' ),
		'slug' => 'blog',
		'parent_url' => $group_link,
		'parent_slug' => $current_group->slug,
		'screen_function' => 'abi_screen_group_blog',
		'position' => 10,
		'user_has_access' => $current_user_access,
		'item_css_id' => 'blog'
	) );
	
	bp_core_new_subnav_item( array(
		'name' => __( 'Announcements', 'buddyboss' ),
		'slug' => 'announcements',
		'parent_url' => $group_link,
		'parent_slug' => $current_group->slug,
		'screen_function' => 'bp_activity_screen_announcements',
		'position' => 10,
		'user_has_access' => $current_user_access,
		'item_css_id' => 'blog'
	) );
}

//load the blog home page for group
function abi_screen_group_blog() {
	
	add_action( 'bp_template_content', 'abi_get_page_content' );
	bp_core_load_template(  'groups/single/plugins'  ); 
	
}

//load the Announcements tab for group
function bp_activity_screen_announcements() {
	add_action( 'bp_template_content', 'abi_get_announcement_content' );
	bp_core_load_template( 'groups/single/plugins' );
}

//get the appropriate query for various screens
function abi_get_query() {
    global $bp;
	$curr_group_name = $bp->groups->current_group->name;
    $term = strtolower(str_replace( ' ', '-', $curr_group_name ));
	
    $qs = array(
        'post_type' => 'post',
        'post_status' => 'publish'
    );
    if (empty($term)) {
        $qs ['name'] = -1; //we know it will not find anything
    }

    if (abi_is_single_post()) {
        $slug = $bp->action_variables[0];
        $qs['name'] = $slug;
    }

    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    
	$qs['tax_query'] = array(
		array(
			'taxonomy' => 'cities-taxonomy',
			'field'	=> 'slug',
			'terms' => $term
		)
	);
    $qs ['paged'] = $paged;
	
    return ($qs);
}

/**
 * Misc group blog functions
 * @return type 
 */
function abi_is_component() {
	global $bp;
	if ( bp_is_current_component( $bp->groups->slug ) && bp_is_current_action( 'blog' ) )
		return true;

	return false;
}

function abi_is_single_post() {
	global $bp;
	if ( abi_is_component() && ! empty( $bp->action_variables[ 0 ] ) && ( ! in_array( $bp->action_variables[ 0 ], array( 'create', 'cities-taxonomy' ) ) ) )
		return true;
}

function abi_is_home() {
	global $bp;
	if ( abi_is_component() && empty( $bp->action_variables[ 0 ] ) )
		return true;
}

function is_abi_pages() {
	return abi_is_component();
}

function abi_is_post_create() {
	global $bp;
	if ( abi_is_component() && ! empty( $bp->action_variables[ 0 ] ) && $bp->action_variables[ 0 ] == 'create' )
		return true;
}

function abi_is_category() {
	global $bp;
	if ( abi_is_component() && ! empty( $bp->action_variables[ 1 ] ) && $bp->action_variables[ 0 ] == abi_get_taxonomy() )
		return true;
}

function abi_get_home_url( $group_id = null ) {
	if ( ! empty( $group_id ) )
		$group = new BP_Groups_Group( $group_id );
	else
		$group = groups_get_current_group();

	return apply_filters( 'abi_home_url', bp_get_group_permalink( $group ) . 'blog' );
}

//if inside the post loop
function in_abi_loop(){
    global $bp;

    return isset( $bp->abi )? $bp->abi->in_the_loop : false;
}
//use it to mark the start of abi post loop
function abi_loop_start(){
    global $bp;
    $bp->abi = new stdClass();
    $bp->abi->in_the_loop = true;
}

//use it to mark the end of abi loop
function abi_loop_end(){
    global $bp;
    
    $bp->abi->in_the_loop = false;
}

//get post permalink which leads to group blog single post page
function abi_get_post_permalink( $post ) {
	return bp_get_group_permalink( groups_get_current_group() ) . 'blog' . '/' . $post->post_name;
}

//Fix post permalink,
add_filter( 'post_link', 'abi_fix_permalink', 10, 3 );
function abi_fix_permalink( $post_link, $id, $leavename ) {
	if ( ! is_abi_pages() || ! in_abi_loop() )
		return $post_link;

	$post_link = abi_get_post_permalink( get_post( $id ) );
	return $post_link;
}

/**
 * Can the current user post to group blog
 * @global type $bp
 * @return type 
 */
function abi_current_user_can_post() {
    $user_id = bp_loggedin_user_id();
    $group_id = bp_get_current_group_id();
	$group_role = abi_get_group_role($user_id,$group_id);
    $can_post = is_user_logged_in() && ( groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id ) || ('author' == $group_role ) || ('contributor' == $group_role ) );

    return apply_filters( 'abi_current_user_can_post', $can_post, $group_id, $user_id);
}

/**
 * Generate Pagination Link for posts
 * @param type $q 
 */
function abi_pagination( $q ) {

	$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
	$paged = intval( get_query_var( 'paged' ) );
	$numposts = $q->found_posts;
	if ( empty( $paged ) || $paged == 0 ) {
		$paged = 1;
	}

	$page_links = paginate_links( array(
		'base' => add_query_arg( array( 'paged' => '%#%', 'num' => $posts_per_page ) ),
		'format' => '',
		'total' => ceil( $numposts / $posts_per_page ),
		'current' => $paged,
		'prev_text' => '&larr;',
		'next_text' => '&rarr;',
		'mid_size' => 1
	) );
	echo $page_links;
}

function abi_posts_pagination_count( $q ) {

	$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
	$paged = intval( get_query_var( 'paged' ) );
	$numposts = $q->found_posts;
	if ( empty( $paged ) || $paged == 0 ) {
		$paged = 1;
	}

	$start_num = intval( $posts_per_page * ( $paged - 1 ) ) + 1;
	$from_num = bp_core_number_format( $start_num );
	$to_num = bp_core_number_format( ( $start_num + ( $posts_per_page - 1 ) > $numposts ) ? $numposts : $start_num + ( $posts_per_page - 1 )  );
	$total = bp_core_number_format( $numposts );

	$post_type_object = get_post_type_object( 'post' );

	printf( __( 'Viewing %1s %2$s to %3$s (of %4$s )', 'buddyboss' ), $post_type_object->labels->name, $from_num, $to_num, $total ) . "&nbsp;";

}

//Post form
function abi_get_post_form( $group_id ) {

	abi_load_template('group-frontend-postform/bp-simple-front-end-post.php');
	
	if ( function_exists( 'bp_get_simple_blog_post_form' ) ) {
		$form = bp_get_simple_blog_post_form( 'abi_form' );
		
		if ( $form ) {
			$form->show();
		}
	}
}

/**
 * Register the simple front end post plugin
 */
add_action( 'bp_init','abi_register_form');
		
function abi_register_form() {

	abi_load_template('group-frontend-postform/bp-simple-front-end-post.php');
	
	$groupblog_role = abi_get_group_role( bp_loggedin_user_id(), bp_get_current_group_id() );
	if ( 'author' == $groupblog_role ) {
		$publish_status = 'publish';
	} else {
		$publish_status = 'draft';
	}
	
	if ( function_exists( 'bp_new_simple_blog_post_form' ) ) { 
		$form_params = array(
			'post_type' => 'post',
			'post_author' => bp_loggedin_user_id(),
			'post_status' => $publish_status,
			'current_user_can_post' => abi_current_user_can_post(),
			'show_tags' => false, 
			'allowed_tags' => array()
		);

		$form = bp_new_simple_blog_post_form( 'abi_form', apply_filters( 'abi_form_args', $form_params ) );
		
	}
}

/**
 * Enqueue comment js on single post screen
 */
add_action( 'bp_enqueue_scripts', 'abi_enqueue_script' );
function abi_enqueue_script() {
	if ( abi_is_single_post() )
		wp_enqueue_script( 'comment-reply' );
}

//comment posting fix
add_action( 'comment_form', 'abi_fix_comment_form' );

function abi_fix_comment_form( $post_id ) {
	if ( ! abi_is_single_post() )
		return;
	$post = get_post( $post_id );
	$permalink = abi_get_post_permalink( $post );
?>
	<input type='hidden' name='redirect_to' value="<?php echo esc_url( $permalink ); ?>" /><?php
}

add_action('bp_init','abi_load_blog_admin_menu');
function abi_load_blog_admin_menu() {
	
	abi_load_template('blog-admin.php');
	
}

//Members group role
add_action('bp_init','abi_members_group_role_col');
function abi_members_group_role_col() {
	global $wpdb;
	$db_version = get_option( 'abi-theme-db' );

	if ( $db_version !== '1.0' ) {

		$table_prefix = bp_core_get_table_prefix();

		$wpdb->query( "ALTER TABLE {$table_prefix}bp_groups_members ADD COLUMN group_role varchar(20) NULL DEFAULT NULL" );

		add_option( 'abi-theme-db', '1.0' );
	}
}

//Adding default entry to group_role
add_action('groups_join_group','abi_update_group_deafult_role');

function abi_update_group_deafult_role( $group_id ) {
	global $wpdb;
	$table_prefix = bp_core_get_table_prefix();
	$groupblog_default_admin_role = groups_get_groupmeta( $group_id, 'groupblog_default_admin_role' );
		
	$wpdb->query( $wpdb->prepare( "UPDATE {$table_prefix}bp_groups_members SET group_role=%s WHERE group_id=%d AND user_id=%d", $groupblog_default_admin_role, $group_id, get_current_user_id() ) );
		
}

//Updating group_role

function abi_update_group_role( $group_role, $group_id, $user_id ) {
	global $wpdb;
	$table_prefix = bp_core_get_table_prefix();
		
	$wpdb->query( $wpdb->prepare( "UPDATE {$table_prefix}bp_groups_members SET group_role=%s WHERE group_id=%d AND user_id=%d", $group_role, $group_id, $user_id ) );
		
}

//Getting user group role
function abi_get_group_role( $user_id , $group_id  ) {
	global $wpdb;
	$table_prefix = bp_core_get_table_prefix();
	$role = $wpdb->get_col($wpdb->prepare("SELECT group_role from {$table_prefix}bp_groups_members WHERE group_id=%d AND user_id=%d", $group_id, $user_id ));
	
	if ( !  empty($role) ) {
		return $role[0];
	}
	return false;
}

//group role editor
function abi_group_role_editor( $user_id ) {
	
	$user_role = abi_get_group_role( $user_id, bp_get_current_group_id() );
	
	$html = '<select name="abi-group-role-editor" class="abi-group-role-editor">';

    $options = array(
        'author'	=> __('Author', 'buddyboss'),
        'contributor'	=> __('Contributor', 'buddyboss'),
        'subscriber'	=> __('Subscriber', 'buddyboss'),
    );

    foreach( $options as $key=>$val ) {
		$selected = ( $user_role == $key ) ? 'selected="selected"' : '';
        $html .= "<option value='" . esc_attr( $key ) . "' $selected>$val</option>";
    }
    $html .= '</select>';

    return $html;
	
}

//Group role update ajax

add_action( 'wp_ajax_abi_group_role_update', 'abi_group_role_update' );
add_action( 'wp_ajax_nopriv_abi_group_role_update', 'abi_group_role_update' );

function abi_group_role_update() {
	
	// Sanitize the post object
	$group_role = esc_attr( $_POST[ 'role' ] );
	$user_id = esc_attr($_POST['user_id']);
	$group_id = esc_attr($_POST['group_id']);
	
	abi_update_group_role( $group_role, $group_id, $user_id );
	echo 'success';
	die();
}

//load user-feed
add_action( 'init', 'abi_load_user_feed' );

function abi_load_user_feed() {

	abi_load_template( 'user-feed.php' );
}

//Modifying the main query to exclude custom blog posts
add_action( 'pre_get_posts', 'abi_filter_main_query' );

function abi_filter_main_query( $query ) {

	if ( $query->is_home() && false == $query->query_vars['suppress_filters'] ) {

		$terms1 = get_terms( array( 'userfeed-taxonomy' ), array( 'fields' => 'ids' ) );
		$terms2 = get_terms( array( 'cities-taxonomy' ), array( 'fields' => 'ids' ) );


		$query->set( 'tax_query', array(
			'relation' => 'OR',
			array(
				array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'userfeed-taxonomy',
						'field' => 'id',
						'terms' => $terms1
					),
					array(
						'taxonomy' => 'category',
						'field' => 'slug',
						'terms' => array( 'featured-news' )
					),
				),
			),
			array(
				array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'cities-taxonomy',
						'field' => 'id',
						'terms' => $terms2
					),
					array(
						'taxonomy' => 'category',
						'field' => 'slug',
						'terms' => array( 'featured-news' )
					),
				),
			),
			array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'userfeed-taxonomy',
					'field' => 'id',
					'terms' => $terms1,
					'operator' => 'NOT IN'
				),
				array(
					'taxonomy' => 'cities-taxonomy',
					'field' => 'id',
					'terms' => $terms2,
					'operator' => 'NOT IN'
				)
			)
		) );
	}
}

//Group single blog redirect
add_action( 'wp', 'boss_group_posts_redirect' );

function boss_group_posts_redirect() {

	if ( is_single() && has_term( '', 'cities-taxonomy' ) ) {

		$city_term = wp_get_post_terms( get_the_ID(), 'cities-taxonomy' );

		wp_redirect( home_url() . '/groups/' . $city_term[ 0 ]->slug . '/blog/' . get_queried_object()->post_name );
	}
}

//Renaming groups in global search plugin
add_filter( 'bboss_global_search_label_search_type', 'renaming_groups_global_search' );

function renaming_groups_global_search( $item ) {

	if ( 'groups' == $item ) {
		return 'cities';
	}

	return $item;
}

//Adding City widget
add_action( 'widgets_init', 'boss_groups_widget');

function boss_groups_widget() {
	global $wpdb;
	$groups_sql_arr = $wpdb->get_results( "SELECT name,slug FROM {$wpdb->prefix}bp_groups" );
	unregister_sidebar('group');
	foreach ( $groups_sql_arr as $group ) {
			
		register_sidebar( array(
			'name' => $group->name .' (City)',
			'id' => $group->slug,
			'description' => 'Single city sidebar.',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widgettitle">',
			'after_title' => '</h3>'
		) );
		
	}
}

//Removing groups single sidebar
add_action( 'widgets_init', 'boss_remove_group_single_widget',11);
function boss_remove_group_single_widget() {
	
	unregister_sidebar('group');
	
}

//Add announcements search class
add_action('bp_ready','boss_announcements_search',9999);

function boss_announcements_search() {
	
	include_once( get_stylesheet_directory() . '/includes/search-announcements/BBoss_Global_Search_Announcements.php' );
	
}

add_action('init','boss_announcements_search_helper_class',82);

function boss_announcements_search_helper_class() {
	
	include_once( get_stylesheet_directory() . '/includes/search-announcements/index.php' );
	
}







/**
 * Strip all waste and unuseful nodes and keep components only and memory for notification
 * @since Boss 1.0.0
 **/
function buddyboss_strip_unnecessary_admin_bar_nodes( &$wp_admin_bar ) {
	global $admin_bar_myaccount,$bb_adminbar_notifications,$bp;
	
	if(is_admin()) { //nothing to do on admin
		return;
	}
	$nodes = $wp_admin_bar->get_nodes();
	
	$bb_adminbar_notifications[] = @$nodes["bp-notifications"];
	
	$current_href = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	
	foreach($nodes as $name => $node) {
		
		if($node->parent == "bp-notifications") {
			$bb_adminbar_notifications[] = $node;
		}
		
		if($node->parent == "" || $node->parent == "top-secondary"   AND $node->id != "top-secondary") {
			if($node->id == "my-account") { continue; }
			$wp_admin_bar->remove_node($node->id);
		}
		
		//adding active for parent link
		if($node->id == "my-account-xprofile-edit" ||
		   $node->id == "my-account-groups-create"  ) {
			
			if(strpos("http://".$current_href,$node->href) !== false ||
			   strpos("https://".$current_href,$node->href) !== false) {
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
				
			}
		}
        
		if($node->id == "my-account-activity-personal") {
			if($bp->current_component == "activity" AND $bp->current_action == "just-me" AND bp_displayed_user_id() == get_current_user_id()) {
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
			}
		}
		
		if( $node->id == "my-account-xprofile-public") {
			if($bp->current_component == "profile" AND $bp->current_action == "public" AND bp_displayed_user_id() == get_current_user_id()) {
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
			}
		}
		
		if($node->id == "my-account-messages-inbox") {
			if($bp->current_component == "messages" AND $bp->current_action == "inbox") {
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
			}
		}
		
		//adding active for child link
		if($node->id == "my-account-settings-general" ) {
			if($bp->current_component == "settings" ||
			   $bp->current_action == "general") {
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
			}
		}
		
		/*
		//add active class if it has viewing page href
		if(!empty($node->href)) {
			if("http://".$current_href == $node->href AND "https://".$current_href == $node->href ) {
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
			}
		}*/
		
		
		//add active class if it has viewing page href
		if(!empty($node->href)) {
			if( 
					( "http://".$current_href == $node->href || "https://".$current_href == $node->href ) 
					||
					( $node->id='my-account-xprofile-edit' && strpos( "http://".$current_href, $node->href )===0 )
				)
				{
				buddyboss_adminbar_item_add_active($wp_admin_bar,$name);
				//add active class to its parent
				if( $node->parent!='' && $node->parent!='my-account-buddypress' ){
					foreach($nodes as $name_inner => $node_inner) {
						if( $node_inner->id==$node->parent ){
							buddyboss_adminbar_item_add_active($wp_admin_bar,$name_inner);
							break;
						}
					}
				}
			}
		}
        
	}
	
} 
add_action( 'admin_bar_menu', 'buddyboss_strip_unnecessary_admin_bar_nodes', 999 );

function buddyboss_adminbar_item_add_active(&$wp_admin_bar,$name) {
	$gnode = $wp_admin_bar->get_node($name);
	if( $gnode ){
		$gnode->meta["class"] =  isset( $gnode->meta["class"] ) ? $gnode->meta["class"] . " active" : " active";
		$wp_admin_bar->add_node($gnode); //update
	}
}

/**
 * Store adminbar specific nodes to use later for buddyboss
 * @since Boss 1.0.0
 **/
function buddyboss_memory_admin_bar_nodes() { 

	static $bb_memory_admin_bar_step;
	global $bb_adminbar_myaccount;
    
	if(is_admin()) { //nothing to do on admin
		return;
	}
	
	if(!empty($bb_adminbar_myaccount)) { //avoid multiple run
		return false;
	}
	
	if(empty($bb_memory_admin_bar_step)) {
		$bb_memory_admin_bar_step = 1;
		ob_start();
	} else {
		$admin_bar_output = ob_get_contents();
		ob_end_clean();
		
		//strip some waste
		$admin_bar_output = str_replace(array('id="wpadminbar"',
						      'role="navigation"',
						      'class ',
						      'class="nojq nojs"',
						      'class="quicklinks" id="wp-toolbar"',
						      'id="wp-admin-bar-top-secondary" class="ab-top-secondary ab-top-menu"',
						      ),'',$admin_bar_output);
		
		//remove screen shortcut link
		$admin_bar_output = @explode('<a class="screen-reader-shortcut"',$admin_bar_output,2);
		$admin_bar_output2 = @explode("</a>",$admin_bar_output[1],2);
		$admin_bar_output = $admin_bar_output[0].$admin_bar_output2[1];
		
		//remove script tag
		$admin_bar_output = @explode('<script',$admin_bar_output,2);
		$admin_bar_output2 = @explode("</script>",$admin_bar_output[1],2);
		$admin_bar_output = $admin_bar_output[0].$admin_bar_output2[1];
		
		//remove user details
		$admin_bar_output = @explode('<a class="ab-item"',$admin_bar_output,2);
		$admin_bar_output2 = @explode("</a>",$admin_bar_output[1],2);
		$admin_bar_output = $admin_bar_output[0].$admin_bar_output2[1];
		
		//add active class into vieving link item
		$current_link = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		
		$bb_adminbar_myaccount = $admin_bar_output;
		
	}
	
}

add_action("wp_before_admin_bar_render","buddyboss_memory_admin_bar_nodes");
add_action("wp_after_admin_bar_render","buddyboss_memory_admin_bar_nodes");

/**
 * Get adminbar myaccount section output
 * Note :- this function can be overwrite with child-theme.
 * @since Boss 1.0.0
 * 
 **/

function buddyboss_adminbar_myaccount() {
	global $bb_adminbar_myaccount;
	echo $bb_adminbar_myaccount;
 }
 
 
 /**
  * Get Notification from admin bar
  * @since Boss 1.0.0
  **/
 function buddyboss_adminbar_notification() {
	global $bb_adminbar_notifications;
	return @$bb_adminbar_notifications;
 }
 
//POST THUMBNAIL AND CAPTION STYLED SIMILAR TO .wp-caption//
function the_post_thumbnail_and_caption($size = '', $attr = '') {
global $post;
$thumb_id = get_post_thumbnail_id($post->ID);
        $args = array(
                'post_type' => 'attachment',
                'post_status' => null,
                'parent' => $post->ID,
                'include'  => $thumb_id
        );
 
$thumbnail_image = get_posts($args);
$get_description = get_post(get_post_thumbnail_id())->post_excerpt;

if ($thumb_id && $get_description && $thumbnail_image && isset($thumbnail_image[0])) {
        $image = wp_get_attachment_image_src( $thumb_id, $size );
        $image_width = $image[1];
 
        if($attr) $attr_class = $attr['class'];
        $attr['class'] = ''; //move any 'class' attributes to the outer div, and remove from the thumbnail
 
        $output = '<div class="thumbnail-caption attachment-'.$size.($attr?' '.$attr_class:'').'" style="width: ' . ($image_width) . 'px">';
 
        $output .= get_the_post_thumbnail($post->ID, $size, $attr);
 
        /* to show the thumbnail caption */
        $caption = $thumbnail_image[0]->post_excerpt;
        if($caption) {
                $output .= '<p class="thumbnail-caption-text">';
                $output .= $caption;
                $output .= '</p>';
        }
 
        $output .= '</div>';
        } else {
		$output = get_the_post_thumbnail();
		}
	echo $output;
}


function abi_entry_meta(){
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'buddyboss' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'buddyboss' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s <span class="author_name">%4$s</span></a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'buddyboss' ), get_the_author() ) ),
		get_avatar( get_the_author('id'), 32 ),
		get_the_author()
	);

	echo $author . '<span class="meta-pipe">|</span>' . $date;
}



add_filter('widget_text', 'do_shortcode');


function jm_get_recent_posts(){
	global $post;
	$recentPosts = new WP_Query();
	$recentPosts->query('showposts=3');

	while ($recentPosts->have_posts()) : 
		$recentPosts->the_post();
		$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), array( 200,200 ), false, '' ); 
		$html .= '<article class="recent-post">';
			if( get_the_post_thumbnail( )) {
			$class = 'has_thumbnail'; 
		$html .= '<a class="thumb" href="' . get_the_permalink() . '" style="background-image:url(' . $src[0] . ');"></a>';
			} else {
			$class = '';  
			} 
		$html .=	'<div class="recent-post-content ' . $class . '">';
		$html .=	'<h3 class="title">';
		$html .=	'<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>';
		$html .= 	'</h3>';
		$html .=	excerpt(20);
		$html .=	'<p class="recent-post_comments"> ';
		$num_comments = get_comments_number();
		if ($num_comments == 0){
		}
		else if ($num_comments == 1){
			$html .= '<i class="fa fa-comments"></i> 1 comment</p>';
		} else if ($num_comments > 1){
			$html .= '<i class="fa fa-comments"></i> ' . $num_comments . ' comments</p>';
		}
		$html .=	'</div>';
		$html .=    '</article>';
	endwhile;
	return $html; 
}

add_shortcode( 'jm_recent_posts', 'jm_get_recent_posts' );


function new_excerpt_more( $more ) {
	return '... <a class="read-more" href="' . get_permalink( get_the_ID() ) . '">' . __( 'Read More', 'buddyboss-child' ) . '</a>';
}
add_filter( 'excerpt_more', 'new_excerpt_more' );

function excerpt($limit) {
 $excerpt = explode(' ', get_the_excerpt(), $limit);
 if (count($excerpt)>=$limit) {
 array_pop($excerpt);
 $excerpt = implode(" ",$excerpt).'...';
 } else {
 $excerpt = implode(" ",$excerpt);
 }
 $excerpt = preg_replace('`[[^]]*]`','',$excerpt);
 return $excerpt;
}




function jm_recent_comments() {
	$args = array(
       	'number' => '5', 
       	'status' => 'approve',
             );
    $posts = get_comments($args);

    foreach ($posts as $post) {
     	$avatar = get_avatar(  $post->user_id , 40 );

		$html .=  '<div class="jm_recent_comment">';
		$html .=  '<div class="jm_recent_comment_avatar">' . $avatar . '</div>';
		$html .=  '<div class="jm_recent_comment_body">';
		$html .=  '<p class="jm_recent_comment_content"><span class="jm_recent_author">' . $post->comment_author . '</span> commented on <a class="jm_recent_link" href="' . get_the_permalink($post->comment_post_ID) . '">' . get_the_title($post->comment_post_ID) . '</a></p>';
		$html .=  '<p class="jm_recent_comment_date">' . date('F j, Y, g:i a', strtotime($post->comment_date)) . '.</p>';
		$html .=  '</div><!--.jm_recent_comment_body-->';
		$html .=  '</div><!--.jm_recent_comment-->';

    }
    return $html;
}
add_shortcode( 'jm_recent_comments', 'jm_recent_comments' );


// Remove "Upcoming Events" page title
add_filter( 'tribe_get_events_title', '__return_false' );
