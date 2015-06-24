<?php
/**
* Wiki object (PHP4 compatible)
* Add a wiki to your blog
* @since 1.0.0a2
*/


class up546E_Wiki {
/**
* @todo Update version number for new releases
* @var		string	$current_version	Current version
*/
var $current_version = '1.2.3.7';

/**
* @var		string	$translation_domain	Translation domain
*/
var $translation_domain = 'wiki';
var $db_prefix = '';

/**
* @var		array	$_options		Consolidated options
*/
var $_options = array();

/**
* Get the table name with prefixes
* @global	object	$wpdb
* @param	string	$table	Table name
* @return	string			Table name complete with prefixes
*/
function tablename($table) {
	global $wpdb;
	// We use a single table for all chats accross the network
	return $wpdb->base_prefix.'wiki_'.$table;
}
/**
* Initializing object
* Plugin register actions, filters and hooks.
*/
function up546E_Wiki() {

	global $wpdb;

	// Activation deactivation hooks
	register_activation_hook(__FILE__, array(&$this, 'install'));
	register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));
	// Actions
	add_action('init', array(&$this, 'init'), 0);
	add_action('init', array(&$this, 'post_action'));
	add_action('admin_print_styles-settings_page_wiki', array(&$this, 'admin_styles'));
	add_action('admin_print_scripts-settings_page_wiki', array(&$this, 'admin_scripts'));
	add_action('add_meta_boxes_userpress_wiki', array(&$this, 'meta_boxes') );
	add_action('wp_insert_post', array(&$this, 'save_wiki_meta'), 10, 2 );
	add_action('admin_menu', array(&$this, 'admin_menu'));
	add_action('admin_menu', array(&$this, 'admin_menu_user_guide'));
	add_action('widgets_init', array(&$this, 'widgets_init'));
	// add_action('pre_post_update', array(&$this, 'send_notifications'), 50, 1);
	add_action('template_redirect', array(&$this, 'load_templates') );
	// add_filter('post_type_link', array(&$this, 'post_type_link'), 10, 3);
	add_filter('term_link', array(&$this, 'term_link'), 10, 3);
	add_filter('name_save_pre', array(&$this, 'name_save'));
	add_filter('role_has_cap', array(&$this, 'role_has_cap'), 10, 3);
	add_filter('user_has_cap', array(&$this, 'user_has_cap'), 10, 3);
	add_filter('get_edit_post_link', array(&$this, 'get_edit_post_link'));
	add_filter('comments_open', array(&$this, 'comments_open'), 10, 1);
	add_filter('rewrite_rules_array', array(&$this, 'add_rewrite_rules'));
	add_action('option_rewrite_rules', array(&$this, 'check_rewrite_rules'));
	add_filter('user_can_richedit', array(&$this, 'user_can_richedit'));
	add_filter('wp_title', array(&$this, 'wp_title'), 10, 3);
	add_filter('the_title', array(&$this, 'the_title'), 10, 2);
	add_filter('404_template', array( &$this, 'not_found_template' ) );
	add_action('pre_get_posts', array( &$this, 'pre_get_posts' ) );
	add_filter('request', array( &$this, 'request') );
	add_filter('body_class', array( &$this, 'body_class'), 10);
	add_action('wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts'), 10);
	// This is to make sure it doesn't conflict with UserTheme's "up546E_foundation_js_init"
	if ( ! function_exists( 'up546E_foundation_js_init' ) ) {
		add_action('wp_footer', 'up546E_foundation_js_init');
	}
	
	// White list the options to make sure non super admin can save wiki options
	// add_filter('whitelist_options', array(&$this, 'whitelist_options'));
	if ( !empty($wpdb->base_prefix) ) {
		$this->db_prefix = $wpdb->base_prefix;
	} 
	else {
		$this->db_prefix = $wpdb->prefix;
	}

	$this->_options['default'] = get_option('wiki_default', array(
			'slug'                      =>  'wiki',
			'breadcrumbs_in_title'      =>  0,
			'frontpage_slug'            =>  __('frontpage', $this->translation_domain),
			'wiki_name'                 =>  __('Wikis', $this->translation_domain),
			'sub_wiki_name'             =>  __('Sub Wikis', $this->translation_domain),
			'default_wiki_editors'      =>  'anyone',
			'sub_wiki_order_by'         =>  'menu_order',
			'sub_wiki_order'            =>  'ASC',
			'subpages_enabled'          =>  0,
			'wiki_html_tag_restriction' =>  'none',
			'wiki_html_tag_declaration' =>  '',
			'wiki_crumbs'				=> 1,
			'modal_links'				=> 1,
			'no_wiki_tags'				=> 'FALSE',
			'override_404'				=> 'FALSE',
			'convert_button'			=> 'FALSE',
		)
	);

	if ( !isset ( $this->_options['default']['slug'] ) ) {
		$this->_options['default']['slug']  =  'wiki';
	}
	if ( !isset ( $this->_options['default']['breadcrumbs_in_title'] ) ) {
		$this->_options['default']['breadcrumbs_in_title']  =  0;
	}
	if ( !isset ( $this->_options['default']['frontpage_slug'] ) ) {
		$this->_options['default']['frontpage_slug']  =  __('frontpage', $this->translation_domain);
	}
	if ( !isset($this->_options['default']['wiki_name'] ) ) {
		$this->_options['default']['wiki_name']  =  __('Wikis', $this->translation_domain);
	}
	if ( !isset($this->_options['default']['sub_wiki_name'] ) ) {
		$this->_options['default']['sub_wiki_name']  =  __('Sub Wikis', $this->translation_domain);
	}
	if ( !isset($this->_options['default']['sub_wiki_order_by'] ) ) {
		$this->_options['default']['sub_wiki_order_by']  =  'menu_order';
	}
	if ( !isset($this->_options['default']['default_wiki_editors'] ) ) {
		$this->_options['default']['default_wiki_editors']  =  'anyone';
	}
	if ( !isset($this->_options['default']['sub_wiki_order'] ) ) {
		$this->_options['default']['sub_wiki_order']  =  'ASC';
	}
	if ( !isset($this->_options['default']['subpages_enabled'] ) ) {
		$this->_options['default']['subpages_enabled']  =  0;
	}
	if ( !isset($this->_options['default']['publish_wiki_privileges'] ) ) {
		$this->_options['default']['publish_wiki_privileges']  =  array('editors');
	}
	if ( !isset ( $this->_options['default']['wiki_html_tag_restriction'] ) ) {
		$this->_options['default']['wiki_html_tag_restriction']  =  'none';
	}
	if ( ! isset ( $this->_options['default']['wiki_html_tag_declaration'] ) ) {
		$this->_options['default']['wiki_html_tag_declaration']  =  '';
	}
	if ( ! isset ( $this->_options['default']['wiki_crumbs'] ) ) {
		$this->_options['default']['wiki_crumbs']  =  1;
	}
	if ( ! isset ( $this->_options['default']['modal_links'] ) ) {
		$this->_options['default']['modal_links']  =  1;
	}
	if ( ! isset ( $this->_options['default']['no_wiki_tags'] ) ) {
		$this->_options['default']['no_wiki_tags']  =  'FALSE';
	}
	if ( ! isset ( $this->_options['default']['override_404'] ) ) {
		$this->_options['default']['override_404']  =  'TRUE';
	}
	if ( ! isset ( $this->_options['default']['convert_button'] ) ) {
		$this->_options['default']['convert_button']  =  'TRUE';
	}
	
	//add_action('save_post',array( &$this, 'save_post_hook'));
	add_filter( 'content_save_pre',  array( &$this, 'save_post_hook'), 10, 1 );
}


// This function is used for automatically deleting undesirable HTML elements 
function save_post_hook($content) {
	global $post;
	$content = htmlspecialchars_decode($content);

	$pattern1  = '#<form(.*?)>(.*?)</form>#is';
	$pattern2  = '#<script(.*?)>(.*?)</script>#is';
	$pattern3  = '#<iframe(.*?)>(.*?)</iframe>#is';
	$pattern4  = '#<embed(.*?)>(.*?)</embed>#is';
	$pattern5  = '#<object(.*?)>(.*?)</object>#is';
	$pattern_array  =  array ( $pattern1, $pattern2, $pattern3, $pattern4, $pattern5 );

	if ( isset ( $this->_options['default']['wiki_html_tag_restriction'] ) ) {
		$tag_restriction_setting  =  $this->_options['default']['wiki_html_tag_restriction'];
	}
	else {
		$tag_restriction_setting  =  'none';
	}

	if ( isset ( $this->_options['default']['wiki_html_tag_declaration'] ) ) {
		$tag_declaration_settings  =  $this->_options['default']['wiki_html_tag_declaration'];
	}
	else {
		$tag_declaration_settings  =  array();
	}

	$tag_declaration_array     =  explode ( "\r", $tag_declaration_settings );
	
	$content = preg_replace_callback( '#\<pre\>(.+?)\<\/pre\>#s', array($this,'save_post_pre_replace'), $content);
	$content = preg_replace_callback( '#\<code\>(.+?)\<\/code\>#s', array($this,'save_post_code_replace'), $content);

	if ( $tag_restriction_setting  ==  'restrict' ) {
		foreach ( $tag_declaration_array as $key ) {
			$new_key  =  preg_replace('/\r|/', '', $key);
			$patterned_key  =  '#<\s*\/?\s*['.$new_key.'](.*?)\s*>#is';
			array_push ( $pattern_array, $patterned_key );
		}

		// <\s*\/?\s*['strong''p''br'](.*?)\s*>
	}
	elseif ( $tag_restriction_setting  ==  'allow' ) {
		
		// original regex <\s*\/?\s*((?!['\/strong''\/p''\/br']).)(.*?)\s*>
		$before_key   =  "'/";
		$after_key    =  "'";
		$regex_begin  =  "#<\s*\/?\s*((?![";
		$regex_end    =  "]).)(.*?)\s*>#is";
		foreach ( $tag_declaration_array as $key ) {
			$new_key     =  preg_replace( '/^[\r\n]+|\.|[\r\n]+$/', '', $key );
			$ready_key  .=  $before_key.$new_key.$after_key;
		}

		$regex_allow  =  $regex_begin . $ready_key . $regex_end;
		array_push ( $pattern_array, $regex_allow );
	
	}
	else {
		// DO NOTHING because it's set to none which doesn't restrict anything beyond the default
	}

	//if (isset($post->post_type) && $post->post_type == 'userpress_wiki'){
	$return = preg_replace( $pattern_array, '', $content);
	
	return $return;
}

function save_post_pre_replace($matches) {
    return "<pre>".htmlentities($matches[1])."</pre>";
}
function save_post_code_replace($matches) {
    return "<code>".htmlentities($matches[1])."</code>";
}

function wiki_tag_search() {
	global $wpdb;
	$s = stripslashes( $_GET['q'] );

	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[count( $s ) - 1];
	}
	
	$s = trim( $s );
	if ( strlen( $s ) < 2 ) {
		die; // require 2 chars for matching
	}

	$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = 'userpress_wiki_tag' AND t.name LIKE (%s)", '%' . like_escape( $s ) . '%' ) );

	echo join( $results, "\n" );
	
	die;
}

function __search_by_title_only( $search, &$wp_query ) {
	
	global $wpdb;

	if ( empty( $search ) ) {
		return $search; // skip processing - no search term in query
	}

	$q = $wp_query->query_vars;    
	$n = ! empty( $q['exact'] ) ? '' : '%';

	$search = '';
	$searchand = '';

	foreach ( (array) $q['search_terms'] as $term ) {
		$term = esc_sql( like_escape( $term ) );
		$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$searchand = ' AND ';
	}

	if ( ! empty( $search ) ) {
		
		$search = " AND ({$search}) ";
		
		if ( ! is_user_logged_in() ) {
			$search .= " AND ($wpdb->posts.post_password = '') ";
		}
	}

	return $search;
}

function wiki_search() {
	
	add_filter( 'posts_search', array(&$this,'__search_by_title_only'), 500, 2 );
	
	global $post;
	
	$posts = get_posts( array(
			'post_type' => 'userpress_wiki',
			's' => trim( esc_attr( strip_tags( $_REQUEST['term'] ) ) ),
		)
	);
	
	remove_filter( 'posts_search', array(&$this,'__search_by_title_only'), 500 );
	
	$suggestions=array();
	

	foreach ($posts as $post) {
		setup_postdata($post);
		$suggestion = array();
		$suggestion['label'] = esc_html($post->post_title);
		$suggestion['link'] = get_permalink();
		$suggestion['ID'] = $post->ID;
		$suggestions[]= $suggestion;
	}

	$response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";  
	echo $response;  	
	exit;
}

function request( $query_vars ) {

	if (!is_admin() && isset($query_vars['post_type']) && ('userpress_wiki' == $query_vars['post_type'] || get_post_meta(get_the_id(), '_has_wiki_features', true ) == 1) && (isset($query_vars['orderby']) && $query_vars['orderby'] == 'menu_order title') && $query_vars['posts_per_page'] == '-1') {
		
		$query_vars['orderby'] = 'menu_order';
		unset($query_vars['posts_per_page']);
		unset($query_vars['posts_per_archive_page']);
		return $query_vars;
		
	}
	return $query_vars;
}

function the_title( $title, $id = false ) {
	global $wp_query, $post;
	
	if (!$id && (get_query_var('post_type') == 'userpress_wiki' || get_post_meta(get_the_id(), '_has_wiki_features', true ) == 1) && $wp_query->is_404) {
		$post_type_object = get_post_type_object( get_query_var('post_type') );
		
		if (current_user_can($post_type_object->cap->publish_posts)) {
			return ucwords(get_query_var('name'));
		}
		
	}
	return $title;
}

function body_class($classes) {
global $wp_query;
$post = $wp_query->post;
	if (get_query_var('post_type') == 'userpress_wiki' || get_post_meta($post->ID, '_has_wiki_features', true ) == 1) {
	
		if (!in_array('userpress_wiki', $classes)) {
			$classes[] = 'userpress_wiki';
		}

		if (is_singular() && !in_array('single-userpress_wiki', $classes)) {
			$classes[] = 'single-userpress_wiki';
		}
		
	}
	return $classes;

}

function not_found_template( $path ) {
	
	global $wp_query;

	if ( 'userpress_wiki' != get_query_var( 'post_type' ) || get_post_meta(get_the_id(), '_has_wiki_features', true ) != 1) {
		return $path;
	}

	$post_type_object = get_post_type_object( get_query_var('post_type') );

	if (current_user_can($post_type_object->cap->publish_posts)) {
		$filter = current_filter();
		$reset = explode( '_', $filter );
		$type = reset( $reset );
		$file = basename( $path );

		if ( file_exists( get_stylesheet_directory() . "/$type-userpress_wiki.php" ) ) {
			$path = get_stylesheet_directory() . "/$type-userpress_wiki.php";
		}
		
	}
	return $path;
}


function load_templates() {
	global $wp_query, $post;
	
	if (get_query_var('post_type') == 'userpress_wiki' || get_post_meta($post->ID, '_has_wiki_features', true ) == 1) {
		if ($wp_query->is_single || $wp_query->is_page) {
			//check for custom theme templates
			$wiki_name = $post->post_name;
			$wiki_id = (int) $post->ID;
			$templates = array();

			if ( $wiki_name ) {
				$templates[] = "userpress_wiki-$wiki_name.php";
			}

			if ( $wiki_id ) {
				$templates[] = "userpress_wiki-$wiki_id.php";
			}
			
			$templates[] = "userpress_wiki.php";
		}
		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'create') {
			$templates = array();
			$templates[] = "userpress_wiki-create.php";
			$templates[] = "page.php";
		}
	
		if (isset($templates)) {

			if ($this->wiki_template = locate_template($templates)) {
				add_filter('template_include', array(&$this, 'custom_template') );
				$explvar = explode('/', $this->wiki_template);
				if (end($explvar) == 'userpress_wiki-create.php') {
					if (!defined('USERPRESS_WIKI_SHOW_TABS_ON_CREATE_PAGE'))
    					define(USERPRESS_WIKI_SHOW_TABS_ON_CREATE_PAGE, FALSE);
				}
			} 
			else {
				//otherwise load the page template and use our own theme
				$wp_query->is_single = null;
				$wp_query->is_page = 1;
				
				if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
					add_action('the_content', array(&$this, 'get_edit_form'));
				} 
				else {
					add_filter('the_content', array(&$this, 'theme'), 1 );
				}
				
			}
			$this->is_wiki_page = true;
		}
	}
}

function custom_template() {
	return $this->wiki_template;
}

function pre_get_posts( $query ) {

	if( $query->is_main_query() && !is_admin() && !empty($query->query_vars['userpress_wiki']) && preg_match('/\//', $query->query_vars['userpress_wiki']) == 0 ) {
		$query->query_vars['post_parent'] = 0;
	}
	
}

function user_can_richedit($wp_rich_edit) {
	
	global $wp_query;
$post = $wp_query->post;
	if (get_query_var('post_type') == 'userpress_wiki' || get_post_meta($post->ID, '_has_wiki_features', true ) == 1) {
		return true;
	}
	
	return $wp_rich_edit;
}

function add_rewrite_rules($rules){
	
	$settings = get_option('userpress_wiki_settings');
	$new_rules = array();
	
	//$new_rules[$this->_options['default']['slug'].'/'.WIKI_SLUG_CATEGORIES.'/(.?.+?)/?$'] = 'index.php?userpress_wiki_category=$matches[1]';
	//$new_rules[$this->_options['default']['slug'].'/'.WIKI_SLUG_TAGS.'/(.?.+?)/?$'] = 'index.php?userpress_wiki_tag=$matches[1]';
	//$new_rules[$this->_options['default']['slug'].'/(.?.+?)/?$'] = 'index.php?userpress_wiki=$matches[1]';

	return array_merge($new_rules, $rules);
}

function check_rewrite_rules($value) {
	
	//prevent an infinite loop
	if ( ! post_type_exists( 'userpress_wiki' ) ) {
		return $value;
	}

	if (!is_array($value)) {
		$value = array();
	}

	$array_keys = array();
	//$array_keys[] = $this->_options['default']['slug'].'/(.?.+?)/?$';
	//$array_keys[] = $this->_options['default']['slug'].'/'.WIKI_SLUG_TAGS.'/(.?.+?)/?$';
	//$array_keys[] = $this->_options['default']['slug'].'/'.WIKI_SLUG_CATEGORIES.'/(.?.+?)/?$';

	foreach ($array_keys as $array_key) {
		
		if ( !array_key_exists($array_key, $value) ) {
			$this->flush_rewrite();
		}

	}

	return $value;
}

function comments_open($open) {

	global $wp_query, $userpress_tab_check;
	$action = isset($_REQUEST['action'])?$_REQUEST['action']:'view';
	
	if ((get_query_var('post_type') == 'userpress_wiki' && ($action != 'discussion'))||(get_query_var('post_type') == 'userpress_wiki' && ($action != 'discussion'))) {
		if ($userpress_tab_check == 0 && !isset($_POST['submit']) && !isset($_POST['Submit'])) {
			return false;
		}
	}

	return $open;
}

function wp_title($title, $sep, $seplocation) {
	
	global $post, $wp_query;
	$tmp_title = "";
	$bc = 0;
	
	if (!$post && (get_query_var('post_type') == 'userpress_wiki' ||get_post_meta(get_the_id(), '_has_wiki_features', true ) == 1) && $wp_query->is_404) {
		
		$post_type_object = get_post_type_object( get_query_var('post_type') );
		
		if (current_user_can($post_type_object->cap->publish_posts)) {
			
			$tmp_title = ucwords(get_query_var('name'));
			
			if ($seplocation == 'left') {
				$title = " {$sep} {$tmp_title}";
			}

			if ($seplocation == 'right') {
				$title = " {$tmp_title} {$sep} ";
			}
			
		}
	} 
	else {
		if (isset($post->ancestors) && is_array($post->ancestors)) {
			foreach($post->ancestors as $parent_pid) {
				if ($bc >= $this->_options['default']['breadcrumbs_in_title']) {
					break;
				}
				$parent_post = get_post($parent_pid);

				if ($seplocation == 'left') {
					$tmp_title .= " {$sep} ";
				}

				$tmp_title .= $parent_post->post_title;

				if ($seplocation == 'right') {
					$tmp_title .= " {$sep} ";
				}

				$bc++;
			}
		}

		$tmp_title = trim($tmp_title);

		if (!empty($tmp_title)) {
			if ($seplocation == 'left') {
				$title = "{$title} {$tmp_title} ";
			}

			if ($seplocation == 'right') {
				$title .= " {$tmp_title} ";
			}
		}
	}

	return $title;
}

/**
* Rename $_POST data from form names to DB post columns.
*
* Manipulates $_POST directly.
*
* @package WordPress
* @since 2.6.0
*
* @param bool $update Are we updating a pre-existing post?
* @param array $post_data Array of post data. Defaults to the contents of $_POST.
* @return object|bool WP_Error on failure, true on success.
*/

function _translate_postdata( $update = false, $post_data = null ) {
	
	if ( empty($post_data) ) {
		$post_data = &$_POST;
	}

	if ( $update ) {
		$post_data['ID'] = (int) $post_data['post_ID'];
	}

	$post_data['post_content'] = isset($post_data['content']) ? $post_data['content'] : '';
	$post_data['post_excerpt'] = isset($post_data['excerpt']) ? $post_data['excerpt'] : '';
	$post_data['post_parent'] = isset($post_data['parent_id'])? $post_data['parent_id'] : '';
	$post_data['menu_order'] = isset($post_data['menu_order'])? $post_data['menu_order'] : '0';

	if ( isset($post_data['trackback_url']) ) {
		$post_data['to_ping'] = $post_data['trackback_url'];
	}
	if ( !isset($post_data['user_ID']) ) {
		$post_data['user_ID'] = $GLOBALS['user_ID'];
	}
	if (!empty ( $post_data['post_author_override'] ) ) {
		$post_data['post_author'] = (int) $post_data['post_author_override'];
		} 
	else {
		if (!empty ( $post_data['post_author'] ) ) {
			$post_data['post_author'] = (int) $post_data['post_author'];
		} 
		else {
			$post_data['post_author'] = (int) $post_data['user_ID'];
		}
	}
	$ptype = get_post_type_object( $post_data['post_type'] );

	if ( isset($post_data['user_ID']) && ($post_data['post_author'] != $post_data['user_ID']) ) {
		if ( !current_user_can( $ptype->cap->edit_others_posts ) || !up546E_user_can_publish() ) {
			if ( 'page' == $post_data['post_type'] ) {
				return new WP_Error( 'edit_others_pages', $update ?
					__( 'You are not allowed to edit pages as this user.' ) :
					__( 'You are not allowed to create pages as this user.' )
				);
			} else {
				return new WP_Error( 'edit_others_posts', $update ?
					__( 'You are not allowed to edit posts as this user.' ) :
					__( 'You are not allowed to post as this user.' )
				);
			}
		}
	}
	// What to do based on which button they pressed
	if ( isset($post_data['saveasdraft']) && '' != $post_data['saveasdraft'] ) {
		$post_data['post_status'] = 'draft';
	}
	if ( isset($post_data['saveasprivate']) && '' != $post_data['saveasprivate'] ) {
		$post_data['post_status'] = 'private';
	}
	if ( isset($post_data['publish']) && ( '' != $post_data['publish'] ) && ( !isset($post_data['post_status']) || $post_data['post_status'] != 'private' ) ) {
		$post_data['post_status'] = 'publish';
	}
	if ( isset($post_data['advanced']) && '' != $post_data['advanced'] ) {
		$post_data['post_status'] = 'draft';
	}
	if ( isset($post_data['pending']) && '' != $post_data['pending'] ) {
		$post_data['post_status'] = 'pending';
	}
	if ( isset( $post_data['ID'] ) ) {
		$post_id = $post_data['ID'];
	}
	else {
		$post_id = false;
	}
	$previous_status = $post_id ? get_post_field( 'post_status', $post_id ) : false;

	// Posts 'submitted for approval' present are submitted to $_POST the same as if they were being published.
	// Change status from 'publish' to 'pending' if user lacks permissions to publish or to resave published posts.

	if ( isset($post_data['post_status']) && ('publish' == $post_data['post_status'] && !current_user_can( $ptype->cap->publish_posts )) ) {
		if ( $previous_status != 'publish' || !current_user_can( 'edit_post', $post_id ) ) {
			$post_data['post_status'] = 'pending';
		}
	}
	if ( ! isset($post_data['post_status']) ) {
		$post_data['post_status'] = $previous_status;
	}
	if (!isset( $post_data['comment_status'] )) {
		$post_data['comment_status'] = 'closed';
	}
	if (!isset( $post_data['ping_status'] )) {
		$post_data['ping_status'] = 'closed';
	}
	
	foreach ( array('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit ) {
		if ( !empty( $post_data['hidden_' . $timeunit] ) && $post_data['hidden_' . $timeunit] != $post_data[$timeunit] ) {
			$post_data['edit_date'] = '1';
			break;
		}
	}

	if ( !empty( $post_data['edit_date'] ) ) {
		$aa = $post_data['aa'];
		$mm = $post_data['mm'];
		$jj = $post_data['jj'];
		$hh = $post_data['hh'];
		$mn = $post_data['mn'];
		$ss = $post_data['ss'];
		$aa = ($aa <= 0 ) ? date('Y') : $aa;
		$mm = ($mm <= 0 ) ? date('n') : $mm;
		$jj = ($jj > 31 ) ? 31 : $jj;
		$jj = ($jj <= 0 ) ? date('j') : $jj;
		$hh = ($hh > 23 ) ? $hh -24 : $hh;
		$mn = ($mn > 59 ) ? $mn -60 : $mn;
		$ss = ($ss > 59 ) ? $ss -60 : $ss;
		$post_data['post_date'] = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss );
		$post_data['post_date_gmt'] = get_gmt_from_date( $post_data['post_date'] );
	}
	return $post_data;
}


/**
* Update an existing post with values provided in $_POST.
*
* @since 1.5.0
*
* @param array $post_data Optional.
* @return int Post ID.
*/
function edit_post( $post_data = null ) {
	if ( empty($post_data) ) {
		$post_data = &$_POST;
	}

	$post_ID = (int) $post_data['post_ID'];
	$ptype = get_post_type_object($post_data['post_type']);

	if ( !current_user_can( $ptype->cap->edit_post, $post_ID ) ) {
		if ( 'page' == $post_data['post_type'] ) {
			wp_die( __('You are not allowed to edit this page.' ));
		}
		else {
			wp_die( __('You are not allowed to edit this post.' ));
		}
	}

	// Autosave shouldn't save too soon after a real save
	if ( 'autosave' == $post_data['action'] ) {
		$post =& get_post( $post_ID );
		$now = time();
		$then = strtotime($post->post_date_gmt . ' +0000');
		$delta = AUTOSAVE_INTERVAL / 2;

		if ( ($now - $then) < $delta ) {
			return $post_ID;
		}
	}

	$post_data = $this->_translate_postdata( true, $post_data );
	$post_data['post_status'] = 'publish';

	if ( is_wp_error($post_data) ) {
		wp_die( $post_data->get_error_message() );
	}
	if ( 'autosave' != $post_data['action']  && 'auto-draft' == $post_data['post_status'] ) {
		$post_data['post_status'] = 'draft';
	}
	if ( isset($post_data['visibility']) ) {
		switch ( $post_data['visibility'] ) {
			case 'public' :
				$post_data['post_password'] = '';
				break;
			case 'password' :
				unset( $post_data['sticky'] );
				break;
			case 'private' :
				$post_data['post_status'] = 'private';
				$post_data['post_password'] = '';
				unset( $post_data['sticky'] );
				break;
		}
	}

	// Post Formats
	if ( current_theme_supports( 'post-formats' ) && isset( $post_data['post_format'] ) ) {
		$formats = get_theme_support( 'post-formats' );
		
		if ( is_array( $formats ) ) {
			$formats = $formats[0];
			
			if ( in_array( $post_data['post_format'], $formats ) ) {
				set_post_format( $post_ID, $post_data['post_format'] );
			} 
			elseif ( '0' == $post_data['post_format'] ) {
				set_post_format( $post_ID, false );
			}
			
		}
		
	}

	// Meta Stuff
	if ( isset($post_data['meta']) && $post_data['meta'] ) {
		foreach ( $post_data['meta'] as $key => $value ) {
		
			if ( !$meta = get_post_meta_by_id( $key ) ) {
				continue;
			}

			if ( $meta->post_id != $post_ID ) {
				continue;
			}

			update_meta( $key, $value['key'], $value['value'] );
			
		}
		
	}

	if ( isset($post_data['deletemeta']) && $post_data['deletemeta'] ) {
	
		foreach ( $post_data['deletemeta'] as $key => $value ) {
			if ( !$meta = get_post_meta_by_id( $key ) ) {
				continue;
			}

			if ( $meta->post_id != $post_ID ) {
				continue;
			}

			delete_meta( $key );
		}
	}

	// add_meta( $post_ID );
	update_post_meta( $post_ID, '_edit_last', $GLOBALS['current_user']->ID );
	update_post_meta($post_ID, '_pingme', '');
	do_all_pings();
	wp_update_post( $post_data );

	// Reunite any orphaned attachments with their parent
	if ( !$draft_ids = get_user_option( 'autosave_draft_ids' ) ) {
		$draft_ids = array();
	}
	if ( $draft_temp_id = (int) array_search( $post_ID, $draft_ids ) ) {
		_relocate_children( $draft_temp_id, $post_ID );
	}

	$this->set_post_lock( $post_ID, $GLOBALS['current_user']->ID );

	if ( current_user_can( $ptype->cap->edit_others_posts ) ) {
	
		if ( ! empty( $post_data['sticky'] ) ) {
			stick_post( $post_ID );
		}
		else {
			unstick_post( $post_ID );
		}
		
	}

	return $post_ID;
}

function post_action() {
	global $post;

	if (isset($_REQUEST['action'])) {
		switch ($_REQUEST['action']) {
			case 'editpost':
				if (wp_verify_nonce($_POST['_wpnonce'], "wiki-editpost_{$_POST['post_ID']}")) {
					$post_id = $this->edit_post($_POST);
					wp_redirect(get_permalink($post_id));
					exit();
				}
				break;
		}
	}
}

function theme($content) {
	
	global $post;

	if ( !post_password_required() ) {

		$revision_id = isset($_REQUEST['revision'])?absint($_REQUEST['revision']):0;
		$left        = isset($_REQUEST['left'])?absint($_REQUEST['left']):0;
		$right       = isset($_REQUEST['right'])?absint($_REQUEST['right']):0;
		$action      = isset($_REQUEST['action'])?$_REQUEST['action']:'view';
		$new_content = '';
		
		if ($action != 'edit') {

			$new_content .= '<div class="userpress_wiki userpress_wiki_single">';
			$new_content .= '<div class="userpress_wiki_tabs userpress_wiki_tabs_top">' . $this->tabs() . '<div class="userpress_wiki_clear"></div></div>';
			$new_content .= $this->decider($content, $action, $revision_id, $left, $right);
		}

		if ( !comments_open() ) {
			$new_content .= '<style type="text/css">'.'#comments { display: none; }'.'.comments { display: none; }'.'</style>';
		} 
		else {
			$new_content .= '<style type="text/css">'.'.hentry { margin-bottom: 5px; }'.'</style>';
		}
	} 
	else {
		$new_content = $content;
	}

	return $new_content;
}


function decider($content, $action, $revision_id = null, $left = null, $right = null, $stray_close = true) {

	global $post;
	$new_content = '';

	switch ($action) {
		case 'backlinks':
			wp_list_comments('type=pings');

		case 'discussion':
			break;

		case 'edit':
			set_include_path(get_include_path().PATH_SEPARATOR.ABSPATH.'wp-admin');
			$post_type_object = get_post_type_object($post->post_type);
			$p = $post;
			
			if ( empty($post->ID) ) {
				wp_die( __('You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?') );
			}
			if ( !current_user_can($post_type_object->cap->edit_post, $post->ID) ) {
				wp_die( __('You are not allowed to edit this item.') );
			}
			if ( isset($_GET['eaction']) && $_GET['eaction'] == 'create_sub' && !up546E_user_can_publish() ) {
				wp_die( __('You are not allowed to create this item.') );
			}
			if ( 'trash' == $post->post_status ) {
				wp_die( __('You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.') );
			}
			if ( null == $post_type_object ) {
				wp_die( __('Unknown post type.') );
			}

			$post_type = $post->post_type;

			if ( $last = $this->check_post_lock( $post->ID ) ) {
				add_action('admin_notices', '_admin_notice_post_locked' );
			} 
			else {
				$this->set_post_lock( $post->ID );
				wp_enqueue_script('autosave');
			}

			$title = $post_type_object->labels->edit_item;
			$post = $this->post_to_edit($post->ID);
			$new_content = $this->get_edit_form(false);
			break;

		case 'restore':

			if ( ! $revision = wp_get_post_revision( $revision_id ) ) {
				break;
			}
			if ( ! current_user_can( 'edit_post', $revision->post_parent ) ) {
				break;
			}
			if ( ! $post = get_post( $revision->post_parent ) ) {
				break;
			}
			
			// Revisions disabled and we're not looking at an autosave
			if ( ( ! WP_POST_REVISIONS || !post_type_supports($post->post_type, 'revisions') ) && !wp_is_post_autosave( $revision ) ) {
				$redirect = get_permalink().'?action=edit';
				break;
			}

			check_admin_referer( "restore-post_$post->ID|$revision->ID" );
			wp_restore_post_revision( $revision->ID );
			$redirect = add_query_arg( array( 'message' => 5, 'revision' => $revision->ID ), get_permalink().'?action=edit'  );
			break;

		case 'diff':

			if ( !$left_revision  = get_post( $left ) ) {
				break;
			}
			if ( !$right_revision = get_post( $right ) ) {
				break;
			}
			// If we're comparing a revision to itself, redirect to the 'view' page for that revision or the edit page for that post
			if ( $left_revision->ID == $right_revision->ID ) {
				$redirect = get_permalink().'?action=edit';
				break;
			}
			// Don't allow reverse diffs?
			if ( strtotime($right_revision->post_modified_gmt) < strtotime($left_revision->post_modified_gmt) ) {
				$redirect = add_query_arg( array( 'left' => $right, 'right' => $left ) );
				break;
			}
			if ( $left_revision->ID == $right_revision->post_parent ) { // right is a revision of left
				$post =& $left_revision;
			}
			elseif ( $left_revision->post_parent == $right_revision->ID ) { // left is a revision of right
				$post =& $right_revision;
			}
			elseif ( $left_revision->post_parent == $right_revision->post_parent ) { // both are revisions of common parent
				$post = get_post( $left_revision->post_parent );
			}
			else {
				break; // Don't diff two unrelated revisions
			}
			if ( ! WP_POST_REVISIONS || !post_type_supports($post->post_type, 'revisions') ) { // Revisions disabled
				if ( ( !wp_is_post_autosave( $left_revision ) && !wp_is_post_autosave( $right_revision ) ) || ( $post->ID !== $left_revision->ID && $post->ID !== $right_revision->ID ) ) {
					$redirect = get_permalink().'?action=edit';
					break;
				}			
			}
			if ( $left_revision->ID == $right_revision->ID || ( !wp_get_post_revision( $left_revision->ID ) && !wp_get_post_revision( $right_revision->ID ) ) ) {
				break;
			}

			$post_title = '<a href="' . get_permalink().'?action=edit' . '">' . get_the_title() . '</a>';
			$h2 = sprintf( __( 'Compare Revisions of &#8220;%1$s&#8221;', $this->translation_domain ), $post_title );
			$title = __( 'Revisions' );
			$left  = $left_revision->ID;
			$right = $right_revision->ID;

		case 'history':

			$args = array( 'format' => 'form-table', 'parent' => false, 'right' => $right, 'left' => $left );

			if ( ! WP_POST_REVISIONS || !post_type_supports($post->post_type, 'revisions') ) {
				$args['type'] = 'autosave';
			}
			if (!isset($h2)) {
				$post_title = '<a href="' . get_permalink().'?action=edit' . '">' . get_the_title() . '</a>';
				$revisions = wp_get_post_revisions( $post->ID );
				$revision = array_shift($revisions);
				$revision_title = wp_post_revision_title( $revision, false );
				$h2 = sprintf( __( 'Revision for &#8220;%1$s&#8221; created on %2$s', $this->translation_domain ), $post_title, $revision_title );
			}

			$new_content .= '<h3 class="long-header">'.$h2.'</h3>';
			$new_content .= '<table class="form-table ie-fixed">';
			$new_content .= '<col class="th" />';

			if ( 'diff' == $action ) {
				$new_content .= '<tr id="revision">';
				$new_content .= '<th scope="row"></th>';
				$new_content .= '<th scope="col" class="th-full">';
				$new_content .= '<span class="alignleft">'.sprintf( __('Older: %s', $this->translation_domain), wp_post_revision_title( $left_revision, false ) ).'</span>';
				$new_content .= '<span class="alignright">'.sprintf( __('Newer: %s', $this->translation_domain), wp_post_revision_title( $right_revision, false ) ).'</span>';
				$new_content .= '</th>';
				$new_content .= '</tr>';
			}

			// use get_post_to_edit filters?
			$identical = true;

			foreach ( _wp_post_revision_fields() as $field => $field_title ) {
				if ( 'diff' == $action ) {
					$left_content = apply_filters( "_wp_post_revision_field_$field", $left_revision->$field, $field );
					$right_content = apply_filters( "_wp_post_revision_field_$field", $right_revision->$field, $field );
					if ( !$rcontent = wp_text_diff( $left_content, $right_content ) ) {
						continue; // There is no difference between left and right
					}
					$identical = false;
				} 
				else {
					add_filter( "_wp_post_revision_field_$field", 'htmlspecialchars' );
					$rcontent = apply_filters( "_wp_post_revision_field_$field", $revision->$field, $field );
				}

				$new_content .= '<tr id="revision-field-<?php echo $field; ?>">';
				$new_content .= '<th scope="row">'.esc_html( $field_title ).'</th>';
				$new_content .= '<td><div class="pre">'.$rcontent.'</div></td>';
				$new_content .= '</tr>';
			}


			if ( 'diff' == $action && $identical ) {
				$new_content .= '<tr><td colspan="2"><div class="updated"><p>'.__( 'These revisions are identical.', $this->translation_domain ). '</p></div></td></tr>';
			}

			$new_content .= '</table>';
			$new_content .= '<br class="clear" />';
			$new_content .= '<div class="userpress_wiki_revisions">' . $this->list_post_revisions( $post, $args ) . '</div>';
			$redirect = false;
			break;

		default:

		$top = "";
		$bottom = "";
		if (isset($this->_options['default']['wiki_crumbs']) && $this->_options['default']['wiki_crumbs'] == TRUE) {
			$crumbs = array();
			if (get_query_var('post_type') == 'userpress_wiki')
				$crumbs = array('<a href="'.home_url($this->_options['default']['slug']).'" class="userpress_wiki_crumbs">'.$this->_options['default']['wiki_name'].'</a>');
		
			foreach($post->ancestors as $parent_pid) {
				$parent_post = get_post($parent_pid);
				$crumbs[] = '<a href="'.get_permalink($parent_pid).'" class="userpress_wiki_crumbs">'.$parent_post->post_title.'</a>';
			}
		
			$crumbs[] = '<span class="userpress_wiki_crumbs">'.$post->post_title.'</span>';
			sort($crumbs);
			$top .= join(get_option("userpress_meta_seperator", " > "), $crumbs);
		}
		$children = get_posts( array(
			'post_parent' => $post->ID,
			'post_type' => 'userpress_wiki',
			'orderby' => $this->_options['default']['sub_wiki_order_by'],
			'order' => $this->_options['default']['sub_wiki_order'],
			'numberposts' => 100000,
			)
		);
		$crumbs = array();

		foreach($children as $child) {
			$crumbs[] = '<a href="'.get_permalink($child->ID).'" class="userpress_wiki_crumbs">'.$child->post_title.'</a>';
		}
		/*
		$bottom = "<h3>" . $this->_options['default']['sub_wiki_name'] . "</h3> <ul><li>";
		$bottom .= join("</li><li>", $crumbs);
		if (count($crumbs) == 0) {
		if(isset($taxonomy))
		$bottom = $taxonomy;
		} else {
		$bottom .= "</li></ul>";
		if (!isset($taxonomy)) $taxonomy = '';
		$bottom = "{$taxonomy} {$bottom}";
		}
		*/

		$revisions = wp_get_post_revisions($post->ID);

		if (current_user_can('edit_wiki', $post->ID)) {
			$bottom = '<div class="userpress_wiki-meta">';

			if (is_array($revisions) && count($revisions) > 0) {
				$revision = array_shift($revisions);
			}

			$bottom .= '</div>';
		}

		/*
			$notification_meta = get_post_meta($post->ID, 'userpress_wiki_email_notification', array('enabled'));
			if (((is_array($notification_meta) && $notification_meta[0] == 'enabled') || ($notification_meta == 'enabled')) && !$this->is_subscribed()) {
				if (is_user_logged_in()) {
				$bottom .= '<div class="userpress_wiki-subscribe"><a href="'.wp_nonce_url(add_query_arg(array('post_id' => $post->ID, 'subscribe' => 1)), "wiki-subscribe-wiki_$post->ID" ).'">'.__('Notify me of changes', $this->translation_domain).'</a></div>';
				} 
				else {
					if (!empty($_COOKIE['userpress_wiki_email'])) {
						$user_email = $_COOKIE['userpress_wiki_email'];
					} 
					else {
						$user_email = "";
					}
					$bottom .= 
						'<div class="userpress_wiki-subscribe">'.
						'<form action="" method="post">'.
						'<label>'.__('E-mail', $this->translation_domain).': <input type="text" name="email" id="email" value="'.$user_email.'" /></label> &nbsp;'.
						'<input type="hidden" name="post_id" id="post_id" value="'.$post->ID.'" />'.
						'<input type="submit" name="subscribe" id="subscribe" value="'.__('Notify me of changes', $this->translation_domain).'" />'.
						'<input type="hidden" name="_wpnonce" id="_wpnonce" value="'.wp_create_nonce("wiki-subscribe-wiki_$post->ID").'" />'.
						'</form>'.
						'</div>';
				}
			}
		*/
	
		$bottom .= '<div id="PrintViewModal" class="reveal-modal" data-reveal></div>';

		$new_content  = '<div class="userpress_wiki_top">' . $top . '</div>'. $new_content;
			$options = get_option('wiki_default');
				if (isset($options['insert_toc']) && $options['insert_toc'] == 'TRUE') {
					$new_content .= up546E_build_subnav_toc(FALSE);
				}
		$new_content .= '<div class="userpress_wiki_content">' . $content .'</div>';
		$new_content .= '<div class="userpress_wiki_bottom">' . $bottom . '</div>';
		$redirect = false;
	}

	if ($stray_close) {
		$new_content .= '</div>';
	}
	// Empty post_type means either malformed object found, or no valid parent was found.
	if ( isset($redirect) && !$redirect && empty($post->post_type) ) {
		//$redirect = 'edit.php';
	}
	if ( !empty($redirect) ) {
		echo 
			'<script type="text/javascript">'.
			'window.location = "'.$redirect.'";'.
			'</script>';
		exit;
	}

	return $new_content;
}


/**
* Default post information to use when populating the "Write Post" form.
*
* @since 2.0.0
*
* @param string $post_type A post type string, defaults to 'post'.
* @return object stdClass object containing all the default post data as attributes
*/

function get_default_post_to_edit( $post_type = 'post', $create_in_db = false, $parent_id = 0 ) {

	global $wpdb;
	$post_title = '';

	if ( !empty( $_REQUEST['post_title'] ) ) {
		$post_title = esc_html( stripslashes( $_REQUEST['post_title'] ));
	}

	$post_content = '';

	if ( !empty( $_REQUEST['content'] ) ) {
		$post_content = esc_html( stripslashes( $_REQUEST['content'] ));
	}

	$post_excerpt = '';

	if ( !empty( $_REQUEST['excerpt'] ) ) {
		$post_excerpt = esc_html( stripslashes( $_REQUEST['excerpt'] ));
	}
	if ( $create_in_db ) {
		// Cleanup old auto-drafts more than 7 days old
		$old_posts = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'auto-draft' AND DATE_SUB( NOW(), INTERVAL 7 DAY ) > post_date" );

		foreach ( (array) $old_posts as $delete ) {
			wp_delete_post( $delete, true ); // Force delete
		}

		$post_id = wp_insert_post( array( 'post_parent' => $parent_id, 'post_title' => __( 'Auto Draft' ), 'post_type' => $post_type, 'post_status' => 'auto-draft' ) );
		$post = get_post( $post_id );

		if ( current_theme_supports( 'post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) && get_option( 'default_post_format' ) ) {
			set_post_format( $post, get_option( 'default_post_format' ) );
		}

		// Copy wiki privileges
		$privileges = get_post_meta($post->post_parent, 'userpress_wiki_privileges');
		
		if (isset($privileges[0])) {
			update_post_meta($post->ID, 'userpress_wiki_privileges', $privileges[0]);
		}

	} 
	else {
		$post->ID = 0;
		$post->post_author = '';
		$post->post_date = '';
		$post->post_date_gmt = '';
		$post->post_password = '';
		$post->post_type = $post_type;
		$post->post_status = 'draft';
		$post->to_ping = '';
		$post->pinged = '';
		$post->comment_status = get_option( 'default_comment_status' );
		$post->ping_status = get_option( 'default_ping_status' );
		$post->post_pingback = get_option( 'default_pingback_flag' );
		$post->post_category = get_option( 'default_category' );
		$post->page_template = 'default';
		$post->post_parent = 0;
		$post->menu_order = 0;
	}

	$post->post_content = apply_filters( 'default_content', $post_content, $post );
	$post->post_title   = apply_filters( 'default_title',   $post_title, $post   );
	$post->post_excerpt = apply_filters( 'default_excerpt', $post_excerpt, $post );
	$post->post_name = '';

	return $post;
}

function enqueue_comment_hotkeys_js() {
	if ( 'true' == get_user_option( 'comment_shortcuts' ) ) {
		wp_enqueue_script( 'jquery-table-hotkeys' );
	}
}

/**
* Get an existing post and format it for editing.
*
* @since 2.0.0
*
* @param unknown_type $id
* @return unknown
*/
function post_to_edit( $id ) {

	$post = get_post( $id, OBJECT, 'edit' );

	if ( $post->post_type == 'page' ) {
		$post->page_template = get_post_meta( $id, '_wp_page_template', true );
	}

	return $post;
}

/**
* Check to see if the post is currently being edited by another user.
*
* @since 2.5.0
*
* @param int $post_id ID of the post to check for editing
* @return bool|int False: not locked or locked by current user. Int: user ID of user with lock.
*/
function check_post_lock( $post_id ) {

	if ( !$post = get_post( $post_id ) ) {
		return false;
	}

	if ( !$lock = get_post_meta( $post->ID, '_edit_lock', true ) ) {
		return false;
	}

	$lock = explode( ':', $lock );
	$time = $lock[0];
	$user = isset( $lock[1] ) ? $lock[1] : get_post_meta( $post->ID, '_edit_last', true );
	$time_window = apply_filters( 'wp_check_post_lock_window', AUTOSAVE_INTERVAL * 2 );

	if ( $time && $time > time() - $time_window && $user != get_current_user_id() ) {
		return $user;
	}

	return false;
}

/**
* Mark the post as currently being edited by the current user
*
* @since 2.5.0
*
* @param int $post_id ID of the post to being edited
* @return bool Returns false if the post doesn't exist of there is no current user
*/

function set_post_lock( $post_id ) {

	if ( !$post = get_post( $post_id ) ) {
		return false;
	}
	if ( 0 == ($user_id = get_current_user_id()) ) {
		return false;
	}

	$now = time();
	$lock = "$now:$user_id";
	update_post_meta( $post->ID, '_edit_lock', $lock );
	
}

function get_new_wiki_form($showheader = false) {
	
	global $wikiformcount;
	global $wp_version, $wp_query, $edit_post, $post_id, $post_ID;

	//if ($wikiformcount === 1) return;
	$wikiformcount = 1;
	
	wp_enqueue_style('my-jquery-ui');
	wp_enqueue_style('userpress_wiki-suggest-css');
	wp_enqueue_script('userpress_wiki_footer_js');
	
	echo '<div class="userpress_wiki userpress_wiki_single">';

	if(get_query_var('name')=='create') {
		echo '<h3>'.__('Create', $this->translation_domain).'</h3>';
	}

	if ($showheader || (defined('USERPRESS_WIKI_SHOW_TABS_ON_CREATE_PAGE') && USERPRESS_WIKI_SHOW_TABS_ON_CREATE_PAGE)) {
		echo '<div class="userpress_wiki_tabs userpress_wiki_tabs_top">' . $this->tabs() . '<div class="userpress_wiki_clear"></div></div>';
	}
	
	echo  '<form action="" method="post">';
	$edit_post = $this->get_default_post_to_edit(get_query_var('post_type'), true, 0);
	$post_id = $edit_post->ID;
	$post_ID = $post_id;
	$slug_parts = array();
	
	if (isset($wp_query->query_vars['userpress_wiki'])) {
		$slug_parts = preg_split('/\//', $wp_query->query_vars['userpress_wiki']);
	}
	if (count($slug_parts) > 1) {

		for ($i=count($slug_parts)-1; $i>=0; $i--) {
			$parent_post = get_posts(array('name' => $slug_parts[$i], 'post_type' => 'userpress_wiki', 'post_status' => 'publish'));
			if (is_array($parent_post) && count($parent_post) > 0) {
				break;
			}
		}

		$parent_post = $parent_post[0];
	}

	echo  '<input type="hidden" name="menu_order" id="menu_order" value="" />';
	echo  '<input type="hidden" name="parent_id" id="parent_id" value="';
	echo isset($parent_post->ID) ? $parent_post->ID : '';
	echo '" />';
	echo  '<input type="hidden" name="original_publish" id="original_publish" value="Publish" />';
	echo  '<input type="hidden" name="publish" id="publish" value="Publish" />';
	echo  '<input type="hidden" name="post_type" id="post_type" value="userpress_wiki" />';
	echo  '<input type="hidden" name="post_ID" id="wiki_id" value="'.$edit_post->ID.'" />';
	echo  '<input type="hidden" name="post_status" id="wiki_id" value="published" />';
	echo  '<input type="hidden" name="comment_status" id="comment_status" value="open" />';
	echo  '<input type="hidden" name="action" id="wiki_action" value="editpost" />';

	if(get_query_var('name')=='create' || !isset($_GET["wtitle"]) ) {
		echo  '<div><input type="text" name="post_title" id="wiki_title" value="" placeholder="Title" class="userpress_wiki_title" size="30" /></div>';
	}
	else {
		echo  '<div><input type="text" name="post_title" id="wiki_title"  placeholder="Title" value="'.$_GET["wtitle"].ucwords(get_query_var('name')).'" class="userpress_wiki_title" size="30" /></div>';
	}
	
	echo '<div id="search_results"></div>';
	echo '<div><input type="text" name="userpress_wiki_subtitle" id="userpress_wiki_subtitle" placeholder="Subtitle" value=""></div>';

	if (version_compare($wp_version, "3.3") >= 0) {
		wp_editor( $edit_post->post_content, 'wikicontent', array( 'textarea_name' => 'content', 'wpautop' => false, ) );
	} 
	else {
		echo '<textarea tabindex="3" name="content" id="wikicontent" class="incusb_wiki_tinymce" cols="40" rows="10" >'.$edit_post->post_content.'</textarea>';
	}

	/*
	INFOBOX
	This feature willl probably be abandoned. But in case it's revived, the is being left here for now.
	echo '<hr>';
	if (version_compare($wp_version, "3.3") >= 0) {
		echo '<label>Infobox</label>';
		wp_editor($meta['userpress_wiki_infobox'][0], 'userpress_wiki_infobox', array('textarea_name' => 'userpress_wiki_infobox',  'media_buttons' => false, 'wpautop' => false));
	} 
	else {
		echo '<textarea tabindex="4" name="userpress_wiki_infobox" id="userpress_wiki_infobox" class="incusb_wiki_tinymce" cols="40" rows="10" >'.$meta['userpress_wiki_infobox'][0].'</textarea>';
	}
	*/
	
	echo  '<input type="hidden" name="_wpnonce" id="_wpnonce" value="'.wp_create_nonce("wiki-editpost_{$edit_post->ID}").'" />';

	if (is_user_logged_in()) {
		echo  $this->get_meta_form();
	}

	echo  '<div class="userpress_wiki_clear right">';
	echo  '<a href="##" onClick="history.go(-1); return false;">'.__('Cancel', $this->translation_domain).'</a> ';
	echo  '<input type="submit" class="button" name="save" id="btn_save" value="'.__('Save', $this->translation_domain).'" />&nbsp;';
	echo  '</div>';
	echo  '</form>';
	echo  '</div>';
	echo '<style type="text/css">'.
		'#comments { display: none; }'.
		'.comments { display: none; }'.
		'</style>';
	return '';
}

function get_edit_form($showheader = false) {

	global $post, $wp_version, $edit_post, $post_id, $post_ID;

	$stack = debug_backtrace();
	
	wp_enqueue_script( 'suggest' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_style( 'my-jquery-ui' );
	wp_enqueue_style( 'userpress_wiki-suggest-css' );

	// Jet pack compatibility
	if (isset($stack[3]) && isset($stack[3]['class']) && isset($stack[3]['function']) && $stack[3]['class'] == 'Jetpack_PostImages' && $stack[3]['function'] == 'from_html') {
		return $showheader;
	}
	if ($showheader) {
		echo '<div class="userpress_wiki userpress_wiki_single">';
		echo '<div class="userpress_wiki_tabs userpress_wiki_tabs_top">' . $this->tabs() . '<div class="userpress_wiki_clear"></div></div>';
	}
	
	echo  '<form action="'.get_permalink().'" method="post">';

	if (isset($_REQUEST['eaction']) && $_REQUEST['eaction'] == 'create') {
		$edit_post = $this->get_default_post_to_edit($post->post_type, true, $post->ID);
		//	echo  '<input type="hidden" name="parent_id" id="parent_id" value="'.$post->ID.'" />';
		echo  '<input type="hidden" name="original_publish" id="original_publish" value="Publish" />';
		echo  '<input type="hidden" name="publish" id="publish" value="Publish" />';
	}
	elseif  (isset($_REQUEST['eaction']) && $_REQUEST['eaction'] == 'create_sub') {
		$edit_post = $this->get_default_post_to_edit($post->post_type, true, $post->ID);
		echo  '<input type="hidden" name="parent_id" id="parent_id" value="'.$post->ID.'" />';
		echo  '<input type="hidden" name="menu_order" id="menu_order" />';
		echo  '<input type="hidden" name="original_publish" id="original_publish" value="Publish" />';
		echo  '<input type="hidden" name="publish" id="publish" value="Publish" />';
	} 
	else {
		$edit_post = $post;
		echo  '<input type="hidden" name="menu_order" id="menu_order" value="'.$post->menu_order.'"/>';
		echo  '<input type="hidden" name="parent_id" id="parent_id" value="'.$post->post_parent.'" />';
		echo  '<input type="hidden" name="original_publish" id="original_publish" value="Update" />';
	}

	$current_user = wp_get_current_user();

	if(in_array('administrator', $current_user->roles)) {
		$admin = 1;
	}
	else {
		$admin = 0;
	}

	$post_id = $edit_post->ID;
	$post_ID = $post_id;
	$post_utitle = '';
	
	if (isset($_GET['utitle'])) {
		$post_utitle = $_GET['utitle'];
	}

	$m = get_post_custom_values('userpress_wiki_subtitle',$post_ID);
	$z = get_post_custom_values('userpress_wiki_infobox',$post_ID);
	echo  '<input type="hidden" name="post_type" id="post_type" value="'.$edit_post->post_type.'" />';
	echo  '<input type="hidden" name="post_ID" id="wiki_id" value="'.$edit_post->ID.'" />';
	echo  '<input type="hidden" name="post_status" id="wiki_id" value="published" />';
	echo  '<input type="hidden" name="comment_status" id="comment_status" value="open" />';
	echo  '<input type="hidden" name="action" id="wiki_action" value="editpost" />';
	//if($admin) {
	echo  '<div><input type="text" name="post_title" id="wiki_title" placeholder="Title" value="'.$edit_post->post_title.$post_utitle.'" class="userpress_wiki_title" size="30" /></div>';
	/*} else {
	echo  '<div><input type="text" disabled="disabled" value="'.$edit_post->post_title.$post_utitle.'" class="userpress_wiki_title" size="30" /></div>';
	}*/
	echo '<div id="search_results"></div>';
	echo  '<div><input type="text" name="userpress_wiki_subtitle" id="userpress_wiki_subtitle" placeholder="Subtitle" value="' . $m[0] . '"></div>';
	echo  '<div>';

	if (version_compare($wp_version, "3.3") >= 0) {
		wp_editor($edit_post->post_content, 'wikicontent', array('textarea_name' => 'content', 'wpautop' => false, ));
	} 
	else {
		echo '<textarea tabindex="3" name="content" id="wikicontent" class="incusb_wiki_tinymce" cols="40" rows="10" >'.$edit_post->post_content.'</textarea>';
	}

	/*
		INFOBOX
		This feature willl probably be abandoned. But in case it's revived, the is being left here for now. 
		echo '<hr>';
		if (version_compare($wp_version, "3.3") >= 0) {
			echo '<label>Infobox</label>';
			wp_editor($z[0], 'userpress_wiki_infobox', array('textarea_name' => 'userpress_wiki_infobox', 'media_buttons' => false, 'wpautop' => false));
		} 
		else {
			echo '<textarea tabindex="4" name="userpress_wiki_infobox" id="userpress_wiki_infobox" class="incusb_wiki_tinymce" cols="40" rows="10" >'.$z[0].'</textarea>';
		}
	*/
	
	echo  '<input type="hidden" name="_wpnonce" id="_wpnonce" value="'.wp_create_nonce("wiki-editpost_{$edit_post->ID}").'" />';

	if (is_user_logged_in()) {
		echo  $this->get_meta_form();
	}

	echo  '<div class="userpress_wiki_clear right alignright">';
	echo  '<a href="'.get_permalink().'">'.__('Cancel', $this->translation_domain).'</a> ';
	echo  '<input type="submit" name="save" class="button" id="btn_save" value="'.__('Save', $this->translation_domain).'" />&nbsp;';
	echo  '</div>';
	echo  '</form>';

	if ($showheader) {
		echo  '</div>';
	}

	echo '<style type="text/css">'.
		'#comments { display: none; }'.
		'.comments { display: none; }'.
		'</style>';

	return '';
}


function get_meta_form() {

	global $post;

	$content  = '';
	$content .= '<div class="userpress_wiki_meta_box">'.$this->wiki_taxonomies(false).'</div>';
	//$content .= '<div class="userpress_wiki_meta_box">'.$this->notifications_meta_box(false).'</div>';
	$content .= '<div class="userpress_wiki_meta_box">'.$this->attributes_meta_box_frontend().'</div>';

	if (current_user_can('edit_wiki_privileges')) {
		$content .= '<div class="userpress_wiki_meta_box">'.$this->privileges_meta_box(false).'</div>';
	}

	return $content;
}


function tabs() {

	global $post, $userpress_tab_check, $wp_query;
	
	if ($wp_query->is_feed) return;

	$userpress_tab_check = 1;
	$classes['page'] = array('userpress_wiki_link_page');
	$classes['discussion'] = array('userpress_wiki_link_discussion');
	$classes['history'] = array('userpress_wiki_link_history');
	$classes['edit'] = array('userpress_wiki_link_edit');
	$classes['advanced_edit'] = array('userpress_wiki_link_advanced_edit');
	$classes['create'] = array('userpress_wiki_link_create');

	if (!isset($_REQUEST['action'])) {
		$classes['page'][] = 'current';
	}
	if (isset($_REQUEST['action'])) {
		switch ($_REQUEST['action']) {
			case 'page':
				$classes['page'][] = 'current';
				break;
			case 'discussion':
				$classes['discussion'][] = 'current';
				break;
			case 'restore':
			case 'diff':
			case 'history':
				$classes['history'][] = 'current';
				break;
			case 'edit':
				if (isset($_REQUEST['eaction']) && $_REQUEST['eaction'] == 'create_sub') {
					$classes['create'][] = 'current';
					return $this->create_wiki_tabs(FALSE);
				}
				else {
					$classes['edit'][] = 'current';
				}
				break;
			case 'create':
				return $this->create_wiki_tabs(FALSE);
				break;	
		}
	}

	$seperator = (preg_match('/\?/i', get_permalink()) > 0)? '&' : '?';
	$tabs  = '<ul class="left">';
	$tabs .= '<li class="'.join(' ', $classes['page']).'" ><a href="'.get_permalink().'" > <i class="fi-page"></i> ' . __('Page', $this->translation_domain) . '</a></li>';

	if (comments_open()) {
		$tabs .= '<li class="'.join(' ', $classes['discussion']).'" ><a href="'.get_permalink().$seperator.'action=discussion" > <i class="fi-comment"></i> ' . __('Discuss', $this->translation_domain) . '</a></li>';
	}

	$tabs .= '<li class="'.join(' ', $classes['history']).'" ><a href="'.get_permalink().$seperator.'action=history" > <i class="fi-page-multiple"></i> ' . __('History', $this->translation_domain) . '</a></li>';
	//		$tabs .= '<li><a href="#" data-reveal-id="infoboxModal"><i class="fi-info"></i> ' . __('Infobox', $this->translation_domain) . '</a></li>';
	$tabs .= '</ul>';
	$post_type_object = get_post_type_object( get_query_var('post_type') );
	$tabs .= '<ul class="right">';
	
	if ($post && current_user_can($post_type_object->cap->edit_post, $post->ID)) {

		$tabs .= '<li class="'.join(' ', $classes['edit']).'" ><a href="'.get_permalink().$seperator.'action=edit" > <i class="fi-page-edit"></i> ' . __('Edit', $this->translation_domain) . '</a></li>';

		if (is_user_logged_in() && up546E_user_can_publish()) {
			/* $tabs .= '<li class="'.join(' ', $classes['advanced_edit']).'" ><a href="'.get_edit_post_link().'" >' . __('Advanced', $this->translation_domain) . '</a></li>'; */

			if ($this->_options['default']['subpages_enabled'] == 1) {
				$tabs .= '<li><a href="#" data-dropdown="create-wiki-dropdown"> <i class="fi-page-add"></i> '.__('Create', $this->translation_domain).'</a></li>
					<ul id="create-wiki-dropdown" data-dropdown-content class="f-dropdown">
					<li class="'.join(' ', $classes['create']).'"><a href="'.get_post_type_archive_link('userpress_wiki').$seperator.'action=create"> <i class="fi-page-add"></i> '.__('Top-Level Page', $this->translation_domain).'</a> </li>
					<li class="'.join(' ', $classes['create']).'"><a href="'.get_permalink().$seperator.'action=edit&eaction=create_sub"> <i class="fi-page-add"></i> '.__('Sub-Page', $this->translation_domain).'</a></li>
					</br>
					</ul>';
			}
			else {
				$tabs .= '<li class="'.join(' ', $classes['create']).'"><a href="'.get_post_type_archive_link('userpress_wiki').'?action=create"> <i class="fi-page-add"></i> '.__('Create', $this->translation_domain).'</a> </li>';
			}
		}
}

	$tabs .= '<li><a href="#" data-dropdown="drop"> <i class="fi-home"></i> '.__('Etc.', $this->translation_domain).'</a></li>
		<ul id="drop" data-dropdown-content class="f-dropdown">
		<li><a href="'.up546E_get_frontpage_uri().'" data-dropdown="drop"> <i class="fi-home"></i> '.__('Frontpage', $this->translation_domain).'</a></li>	
		<li><a href="'.get_post_type_archive_link('userpress_wiki').'?view=created"><i class="fi-burst-new"></i> New Articles</a></li>
		<li><a href="'.get_post_type_archive_link('userpress_wiki').'?view=recently_modified"><i class="fi-clock"></i> Recently Modified</a></li>	
		<li><a href="'.get_post_type_archive_link('userpress_wiki').'?view=recently_discussed"><i class="fi-torsos-all"></i> Recently Discussed</a></li>
		<li><a href="'.get_post_type_archive_link('userpress_wiki').'?view=most_discussed"><i class="fi-comments"></i> Most Discussed</a></li>
		<li><a href="'.get_post_type_archive_link('userpress_wiki').'?view=alpha"><i class="fi-page-multiple"></i> Alphabetical Order</a></li>
		</br>
		</ul>';
	$tabs .= '</ul>'; 
	$userpress_tab_check = 0;

	return $tabs;
}

function create_wiki_tabs() {
	global $wp_query;
	
	if ($wp_query->is_feed) return;
		
		ob_start();
	?>
		<div class="userpress_wiki_tabs">

		<ul class="right">
		<li>
			<a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?action=create"><i class="fi-page-new"></i>
				 Create New Wiki
			</a>
		</li>
	<?php //list terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)

	$taxonomy     = 'userpress_wiki_flags';
	$orderby      = 'name';
	$show_count   = 0;      // 1 for yes, 0 for no
	$pad_counts   = 0;      // 1 for yes, 0 for no
	$hierarchical = 1;      // 1 for yes, 0 for no
	$title        = '';
	$empty        = 0;

	$flags = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty,
		);
	?>

	<li>
	<a href="#" data-dropdown="drop-flag"> <i class="fi-flag"></i> Flags</a></li>
	<ul id="drop-flag" data-dropdown-content class="f-dropdown">
	<?php wp_list_categories( $flags ); ?>
	</ul>

	<li><a href="#" data-dropdown="drop-etc"> <i class="fi-home"></i> Etc.</a></li>
	<ul id="drop-etc" data-dropdown-content class="f-dropdown">
	<li><a href="<?php echo up546E_get_frontpage_uri(); ?>"><i class="fi-home"></i> Frontpage</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=created"><i class="fi-burst-new"></i> New Articles</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=recently_modified"><i class="fi-clock"></i> Recently Modified</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=recently_discussed"><i class="fi-torsos-all"></i> Recently Discussed</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=most_discussed"><i class="fi-comments"></i> Most Discussed</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=alpha"><i class="fi-page-multiple"></i> Alphabetical Order</a></li>
	</br>
	</ul>
	</ul>

	<ul class="left">
	
	</ul>

	</div>

	<?php
	$subtitlebar = ob_get_clean();
	return $subtitlebar;
}

function get_edit_post_link($url, $id = 0, $context = 'display') {
	global $post;
	return $url;
}

/**
* Display list of a post's revisions.
*
* Can output either a UL with edit links or a TABLE with diff interface, and
* restore action links.
*
* Second argument controls parameters:
*   (bool)   parent : include the parent (the "Current Revision") in the list.
*   (string) format : 'list' or 'form-table'.  'list' outputs UL, 'form-table'
*                     outputs TABLE with UI.
*   (int)    right  : what revision is currently being viewed - used in
*                     form-table format.
*   (int)    left   : what revision is currently being diffed against right -
*                     used in form-table format.
*
* @package WordPress
* @subpackage Post_Revisions
* @since 2.6.0
*
* @uses wp_get_post_revisions()
* @uses wp_post_revision_title()
* @uses get_edit_post_link()
* @uses get_the_author_meta()
*
* @todo split into two functions (list, form-table) ?
*
* @param int|object $post_id Post ID or post object.
* @param string|array $args See description {@link wp_parse_args()}.
* @return null
*/
function list_post_revisions( $post_id = 0, $args = null ) {

	if ( !$post = get_post( $post_id ) ) {
		return;
	}

	$content = '';
	$defaults = array( 'parent' => false, 'right' => false, 'left' => false, 'format' => 'list', 'type' => 'all' );
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	switch ( $type ) {
		case 'autosave' :
			if ( !$autosave = wp_get_post_autosave( $post->ID ) ) {
				return;
			}
			$revisions = array( $autosave );
			break;
		case 'revision' : // just revisions - remove autosave later
		case 'all' :
		default :
			if ( !$revisions = wp_get_post_revisions( $post->ID ) ) {
				return;
			}
			break;
	}

	/* translators: post revision: 1: when, 2: author name */
	$titlef = _x( '%1$s by %2$s', 'post revision' );

	if ( $parent ) {
		array_unshift( $revisions, $post );
	}

	$rows = '';
	$class = false;
	$can_edit_post = current_user_can( 'edit_wiki', $post->ID );

	foreach ( $revisions as $revision ) {

		/*
		if ( !current_user_can( 'read_post', $revision->ID ) ) {
			continue;
		}
		*/

		if ( 'revision' === $type && wp_is_post_autosave( $revision ) ) {
			continue;
		}

		$date = wp_post_revision_title( $revision, false );
		$name = get_the_author_meta( 'display_name', $revision->post_author );

		if ( 'form-table' == $format ) {

			if ( $left ) {
				$left_checked = $left == $revision->ID ? ' checked="checked"' : '';
			}
			else {
				$left_checked = (isset($right_checked) && $right_checked) ? ' checked="checked"' : ''; // [sic] (the next one)
			}

			$right_checked = $right == $revision->ID ? ' checked="checked"' : '';
			$class = $class ? '' : " class='alternate'";

			if ( $post->ID != $revision->ID && $can_edit_post && current_user_can( 'read_post', $revision->ID ) ) {
				$actions = '<a href="' . wp_nonce_url( add_query_arg( array( 'revision' => $revision->ID, 'action' => 'restore' ) ), "restore-post_$post->ID|$revision->ID" ) . '">' . __( 'Restore' ) . '</a>';
			}
			else {
				$actions = ' ';
			}

			$rows .= "<tr$class>\n";
			$rows .= "\t<td style='white-space: nowrap' scope='row'><input type='radio' name='left' value='{$revision->ID}' {$left_checked} /></td>\n";
			$rows .= "\t<td style='white-space: nowrap' scope='row'><input type='radio' name='right' value='{$revision->ID}' {$right_checked} /></td>\n";
			$rows .= "\t<td>$date</td>\n";
			$rows .= "\t<td>$name</td>\n";
			$rows .= "\t<td class='action-links'>$actions</td>\n";
			$rows .= "</tr>\n";
		}
		else {
			$title = sprintf( $titlef, $date, $name );
			$rows .= "\t<li>$title</li>\n";
		}
	}

	if ( 'form-table' == $format ) {
		$content .= '<form action="'.get_permalink().'" method="get">';
		$content .= '<div class="tablenav">';
		$content .= '<input type="submit" class="button-secondary" value="'.esc_attr( __('Compare Revisions', $this->translation_domain ) ).'" />';
		$content .= '<input type="hidden" name="action" value="diff" />';
		$content .= '<input type="hidden" name="post_type" value="'.esc_attr($post->post_type).'" />';
		$content .= '</div>';
		$content .= '<br class="clear" />';
		$content .= '<table class="widefat post-revisions" cellspacing="0" id="post-revisions">';
		$content .= '<col /><col /><col style="width: 33%" /><col style="width: 33%" /><col style="width: 33%" />';
		$content .= '<thead>';
		$content .= '<tr>';
		$content .= '<th scope="col">'._x( 'Old', 'revisions column name', $this->translation_domain ).'</th>';
		$content .= '<th scope="col">'._x( 'New', 'revisions column name', $this->translation_domain ).'</th>';
		$content .= '<th scope="col">'._x( 'Date Created', 'revisions column name', $this->translation_domain ).'</th>';
		$content .= '<th scope="col">'.__( 'Author', $this->translation_domain, $this->translation_domain ).'</th>';
		$content .= '<th scope="col" class="action-links">'.__( 'Actions', $this->translation_domain ).'</th>';
		$content .= '</tr>';
		$content .= '</thead>';
		$content .= '<tbody>';
		$content .= $rows;
		$content .= '</tbody>';
		$content .= '</table>';
		$content .= '</form>';
	}
	else {
		$content .= "<ul class='post-revisions'>\n";
		$content .= $rows;
		$content .= "</ul>";
	}

	return $content;
}


function user_has_cap($allcaps, $caps = null, $args = null) {

	global $current_user, $blog_id, $post;
	$capable = false;

	if (preg_match('/(_wiki|_wikis)/i', join($caps, ',')) > 0) {
		if (in_array('administrator', $current_user->roles)) {
			foreach ($caps as $cap) {
				$allcaps[$cap] = 1;
			}

			return $allcaps;
		}

		foreach ($caps as $cap) {
			$capable = false;

			switch ($cap) {
				case 'read_wiki':
					$capable = true;
					break;
				case 'edit_others_wikis':
				case 'edit_published_wikis':
				case 'edit_wikis':
				case 'edit_wiki':
					if (isset($args[2])) {
						$edit_post = get_post($args[2]);
					} 
					elseif (isset($_REQUEST['post_ID'])) {
						$edit_post = get_post($_REQUEST['post_ID']);
					} 
					else {
						$edit_post = $post;
					}
					if ($edit_post) {
						$meta = get_post_custom($edit_post->ID);
						$current_privileges = array();
						if (isset($meta["userpress_wiki_privileges"])) {
							$current_privileges = unserialize($meta["userpress_wiki_privileges"][0]);
						}
						if (empty($current_privileges)) {
							$current_privileges = array('edit_posts');
						}
						if ($edit_post->post_status == 'auto-draft') {
							$capable = true;
						} 
						elseif ($current_user->ID == 0) {
							if (in_array('anyone', $current_privileges)) {
								$capable = true;
							}
						} 
						else {
							if (in_array('edit_posts', $current_privileges) && current_user_can('edit_posts')) {
								$capable = true;
							} 
							elseif (in_array('site', $current_privileges) && current_user_can_for_blog($blog_id, 'read')) {
								$capable = true;
							}
							elseif (in_array('network', $current_privileges) && is_user_logged_in()) {
								$capable = true;
							}
							elseif (in_array('anyone', $current_privileges)) {
								$capable = true;
							}
						}
					}
					elseif (current_user_can('edit_posts')) {
						$capable = true;
					}
					break;
				default:
					if (isset($args[1]) && isset($args[2])) {
						if (current_user_can(preg_replace('/_wiki/i', '_post', $cap), $args[1], $args[2])) {
							$capable = true;
						}
					}
					elseif (isset($args[1])) {
						if (current_user_can(preg_replace('/_wiki/i', '_post', $cap), $args[1])) {
							$capable = true;
						}
					}
					elseif (current_user_can(preg_replace('/_wiki/i', '_post', $cap))) {
						$capable = true;
					}
					break;
			}
			if ($capable) {
				$allcaps[$cap] = 1;
			}
		}
	}

	return $allcaps;
}


function role_has_cap($capabilities, $cap, $name) {
	// nothing to do
	return $capabilities;
}

/**
* Activation hook
*
* Create tables if they don't exist and add plugin options
*
* @see		http://codex.wordpress.org/Function_Reference/register_activation_hook
*
* @global	object	$wpdb
*/
function install() {

	global $wpdb;

	if (get_option('wiki_version', false) == $this->current_version) {
		return;
	}

	/**
	* WordPress database upgrade/creation functions
	*/
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Get the correct character collate
	if ( ! empty($wpdb->charset) ) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty($wpdb->collate) ) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}

	/*
		// Setup the subscription table
		$sql_main =
			"CREATE TABLE `" . $this->db_prefix . "wiki_subscriptions` (
			`ID` bigint(20) unsigned NOT NULL auto_increment,
			`blog_id` bigint(20) NOT NULL,
			`wiki_id` bigint(20) NOT NULL,
			`user_id` bigint(20),
			`email` VARCHAR(255),
			PRIMARY KEY  (`ID`)
			) ENGINE=MyISAM;";
		dbDelta($sql_main);
	*/

	// Default chat options
	$this->_options['default'] = get_option('wiki_default', array(
			'slug'                       =>  'wiki', 
			'breadcrumbs_in_title'       =>  0, 
			'frontpage_slug'             =>  __('frontpage', $this->translation_domain),
			'frontpage_home'			 =>  0,
			'wiki_name'                  =>  __('Wikis', $this->translation_domain), 
			'sub_wiki_name'              =>  __('Sub Wikis', $this->translation_domain),
			'sub_wiki_order_by'          =>  'menu_order', 
			'default_wiki_editors'       =>  'anyone',
			'sub_wiki_order'             =>  'ASC',
			'subpages_enabled'           =>  0,
			'publish_wiki_privileges'    =>  array('editors'),
			'wiki_html_tag_restriction'  =>  'none',
			'wiki_html_tag_declaration'  =>  '',
			'wiki_crumbs' 				 =>  1,
			'modal_links'				 =>  1
		)
	);

	if (!is_array($this->_options['default'])) {
		$this->_options['default'] = array('slug' => 'wiki', 'breadcrumbs_in_title' => 0);
	}
	if (!isset($this->_options['default']['slug'])) {
		$this->_options['default']['slug'] = 'wiki';
	}
	if (!isset($this->_options['default']['breadcrumbs_in_title'])) {
		$this->_options['default']['breadcrumbs_in_title'] = 0;
	}
	if (!isset($this->_options['default']['frontpage_slug'])) {
		$this->_options['default']['frontpage_slug'] = __('frontpage', $this->translation_domain);
	}
	if (!isset($this->_options['default']['frontpage_home'])) {
		$this->_options['default']['frontpage_home'] = 0;
	}
	if (!isset($this->_options['default']['wiki_name'])) {
		$this->_options['default']['wiki_name'] = __('Wikis', $this->translation_domain);
	}
	if (!isset($this->_options['default']['sub_wiki_name'])) {
		$this->_options['default']['sub_wiki_name'] = __('Sub Wikis', $this->translation_domain);
	}
	if (!isset($this->_options['default']['sub_wiki_order_by'])) {
		$this->_options['default']['sub_wiki_order_by'] = 'menu_order';
	}
	if (!isset($this->_options['default']['default_wiki_editors'])) {
		$this->_options['default']['default_wiki_editors'] = 'anyone';
	}
	if (!isset($this->_options['default']['sub_wiki_order'])) {
		$this->_options['default']['sub_wiki_order'] = 'ASC';
	}
	if (!isset($this->_options['default']['subpages_enabled'])) {
		$this->_options['default']['subpages_enabled'] = 0;
	}
	if (!isset($this->_options['default']['publish_wiki_privileges'])) {
		$this->_options['default']['publish_wiki_privileges'] = array('editors');
	}
	if ( ! isset ( $this->_options['default']['wiki_html_tag_restriction'] ) ) {
		$this->_options['default']['wiki_html_tag_restriction']  =  'none'; // options are allow, restrict, none
	}
	if ( ! isset ( $this->_options['default']['wiki_html_tag_declaration'] ) ) {
		$this ->_options['default']['wiki_html_tag_declaration']  =  '';
	}
	if ( ! isset ( $this->_options['default']['wiki_crumbs'] ) ) {
		$this ->_options['default']['wiki_crumbs']  =  1;
	}
	if ( ! isset ( $this->_options['default']['modal_links'] ) ) {
		$this ->_options['default']['modal_links']  =  1;
	}

	$role_obj = get_role('administrator');
	$role_obj->add_cap('edit_wiki_privileges');
	
	update_option('wiki_version', $this->current_version);
	update_option('wiki_default', $this->_options['default']);

}

/**
* Add the admin menus
*
* @see		http://codex.wordpress.org/Adding_Administration_Menus
*/
function admin_menu() {
	$page = add_submenu_page(
			'edit.php?post_type=userpress_wiki',
			__('Wiki Settings', $this->translation_domain),
			__('Wiki Settings', $this->translation_domain),
			'manage_options',
			'userpress_wiki',
			array(&$this, 'options_page')
		);

	add_action( 'admin_print_scripts-' . $page, array(&$this, 'admin_script_settings') );
	add_action( 'admin_print_styles-' . $page, array(&$this, 'admin_css_settings') );
}

function admin_menu_user_guide() {
	global $submenu;
	if (is_array($submenu)) {
		if ($submenu['edit.php?post_type=userpress_wiki']) {
			$key = end(array_keys($submenu['edit.php?post_type=userpress_wiki'])) + 1; 
			$submenu['edit.php?post_type=userpress_wiki'][$key] = array( __('User Guide', $this->translation_domain), 'manage_options' , 'http://www.userpress.org/wiki/frontpage/' );
		}
	}
}

function admin_script_settings() { 
	//wp_enqueue_script( 'userpress_usableforms', plugin_dir_url( __FILE__ ) . 'js/usableforms.js' );
}

function admin_css_settings()  { 
	// Nothing to do
}

function options_page() {

	if(!current_user_can('manage_options')) {
		echo "<p>" . __('Nice Try...', $this->translation_domain) . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}

	if (isset($_GET['userpress_wiki_settings_saved']) && $_GET['userpress_wiki_settings_saved'] == 1) {
		echo '<div class="updated fade"><p>'.__('Settings saved.', $this->translation_domain).'</p></div>';
	}
	?>
		<div class="wrap wiki-form">
		<h2><?php _e('Wiki Settings', $this->translation_domain); ?></h2>
		<form method="post" action="edit.php?post_type=userpress_wiki&amp;page=userpress_wiki">
			<?php wp_nonce_field('userpress_wiki-update-options'); ?>
			<table>
				<tr valign="top">
					<td>
						<label for="userpress_wiki-slug"><?php _e('Wiki Slug', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="text" size="20" id="userpress_wiki-slug" name="wiki_default[slug]" value="<?php print $this->_options['default']['slug']; ?>" />
					</td>
				</tr>
				
				<tr valign="top">
					<td>
						<label for="userpress_wiki-crumbs"><?php _e('Display Breadcrumbs?', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="radio" id="userpress_wiki-crumbs_true" name="wiki_default[wiki_crumbs]" <?php if ($this->_options['default']['wiki_crumbs'] == 1) { print 'checked="checked"'; } ?> rel="wiki_crumbs" value="1">
							Yes
						<br />
						<input type="radio" id="userpress_wiki-crumbs_false"  name="wiki_default[wiki_crumbs]" <?php if ($this->_options['default']['wiki_crumbs'] == 0) { print 'checked="checked"'; } ?> rel="none" value="0">
							No
					</td> 
				</tr>
				<tr valign="top">
					<td>
						<label for="userpress_wiki-breadcrumbs_in_title"><?php _e('Number of breadcrumbs to add to title', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="text" size="20" id="userpress_wiki-breadcrumbs_in_title" name="wiki_default[breadcrumbs_in_title]" value="<?php print $this->_options['default']['breadcrumbs_in_title']; ?>" />
					</td>
				</tr>
				
				<tr valign="top">
					<td>
						<label for="userpress_wiki-frontpage-slug"><?php _e('Frontpage', $this->translation_domain); ?></label>
					</td>
					<? /*
					<td>
						<input type="text" size="20" id="userpress_wiki-frontpage-slug" name="wiki_default[frontpage_slug]" value="<?php print $this->_options['default']['frontpage_slug']; ?>" />
					</td>
					*/ ?>
					<td>
						<?php 
						$args = array(
							'depth'            => 0,
							'child_of'         => 0,
							'sort_column'  	   => 'menu_order, post_title',
        					'sort_order'       => '',
							'selected'         => $this->get_id_by_slug($this->_options['default']['frontpage_slug'], 'userpress_wiki'),
							'echo'             => 0,
							'id'			   => 'userpress_wiki-frontpage-slug',
							'name'             => 'wiki_default[frontpage_slug]',
							'post_type' 	   => 'userpress_wiki');
						
							$upfpdropdown = wp_dropdown_pages( $args ); 
							
							$dom = new DOMDocument();
							$dom->loadHTML($upfpdropdown);
							
							//Evaluate Anchor tag in HTML
							$xpath = new DOMXPath($dom);
							$hrefs = $xpath->evaluate("/html/body//option");
							
							for ($i = 0; $i < $hrefs->length; $i++) {
									$href = $hrefs->item($i);
									$value = $href->getAttribute('value');
									$newvalue = get_post( $value )->post_name;
									$href->removeAttribute('value');
									$href->setAttribute("value", $newvalue);
							}
							
							// save html
							$upfpdropdown = $dom->saveHTML();
							
							echo $upfpdropdown;
							?>
						</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="userpress_wiki-frontpage_home"><?php _e('Set Wiki Frontpage as Blog homepage', $this->translation_domain); ?></label>
					</td>
					<td>
						<?php 
							$upfpid = $this->get_id_by_slug( $this->_options['default']['frontpage_slug'], 'userpress_wiki');
							$upcurrentid = get_option( 'page_on_front');
							if ($upfpid != $upcurrentid) {
								echo 'Current Home Page: '.get_the_title($upcurrentid).'<br> Replace? ';
								echo '<input type="checkbox" name="wiki_default[frontpage_home]" value="1" />';
							} else {
								echo '"'.get_the_title($upfpid).'" is currently set as the site\'s Front Page.<br>Go to <a href="options-reading.php">Settings > Reading</a> to change';
							}
							?>
					</td> 
				</tr>
				<tr valign="top">
					<td>
						<label for="userpress_wiki-wiki_name"><?php _e('What do you want to call Wikis?', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="text" size="20" id="userpress_wiki-wiki_name" name="wiki_default[wiki_name]" value="<?php print $this->_options['default']['wiki_name']; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="userpress_wiki-default_wiki_editors"><?php _e('What should be the default Wiki priviledge?', $this->translation_domain); ?></label>
					</td>
					<td>
						<select id="userpress_wiki-default_wiki_editors" name="wiki_default[default_wiki_editors]" >
							<option value="anyone" <?php if ($this->_options['default']['default_wiki_editors'] == 'anyone'){ echo 'selected="selected"'; } ?> ><?php _e('Anyone', $this->translation_domain); ?></option>
								<?php $wikieditname =  is_multisite() ? 'network' : 'site'; ?>
							<option value="<?php echo $wikieditname; ?>" <?php if ($this->_options['default']['default_wiki_editors'] == $wikieditname){ echo 'selected="selected"'; } ?> ><?php if ( is_multisite() ) {_e('Network Users', $this->translation_domain); } else {  _e('Site Users', $this->translation_domain);} ?></option>
							<option value="edit_posts" <?php if ($this->_options['default']['default_wiki_editors'] == 'edit_posts'){ echo 'selected="selected"'; } ?> ><?php _e('Editors', $this->translation_domain); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label><?php _e('Who can publish wiki pages?', $this->translation_domain); ?></label>
					</td>	
					<?php
						$whocanpublish = array(
							'anyone' => 'Anyone',
							'editors' => 'Editors',
						);
						if (is_multisite()) {
							$whocanpublish['network'] = 'Network Users';
						}
						else {
							$whocanpublish['site'] = 'Site Users';
						};
					?>
					<td>
						<?php
							foreach ($whocanpublish as $role_key => $role_name) {
						?>
						<label>
							<input type="checkbox" name="wiki_default[publish_wiki_privileges][<?php echo $role_key; ?>]" value="<?php echo $role_key; ?>" <?php if (is_array($this->_options['default']['publish_wiki_privileges']) && in_array($role_key, $this->_options['default']['publish_wiki_privileges'])) { echo  'checked="checked"'; } ?> /> <?php echo $role_name; ?>
						</label>
						<br/>
						<?php
							}
						?>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label><?php _e('Who can edit wiki privileges?', $this->translation_domain); ?></label>
					</td>
					<td>
						<select id="userpress_wiki-default_wiki_editors" name="edit_wiki_privileges" >
						<?php
							$editable_roles = get_editable_roles();
							foreach ($editable_roles as $role_key => $role) {
							$role_obj = get_role($role_key);
						?>
							<option value="<?php echo $role_key; ?>" <?php echo $role_obj->has_cap('edit_wiki_privileges')?'selected="selected"':''; ?> >
							<?php
								echo $role['name']; 
							?>
							</option>
						<?php
							}
						?>
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="userpress_wiki-sub_wiki_name"><?php _e('Enable Sub Wikis?', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="radio" id="subpages_true" name="wiki_default[subpages_enabled]" <?php if ($this->_options['default']['subpages_enabled'] == 1) { print 'checked="checked"'; } ?> rel="subwiki_option" value="1">
							Yes
						<br />
						<input type="radio" id="subpages_false"  name="wiki_default[subpages_enabled]" <?php if ($this->_options['default']['subpages_enabled'] == 0) { print 'checked="checked"'; } ?> rel="none" value="0">
							No
					</td> 
				</tr>
				<?php 
					/*
						<tr valign="top" rel="subwiki_option">
						<td><label for="userpress_wiki-sub_wiki_name"><?php _e('What do you want to call Sub Wikis?', $this->translation_domain); ?></label> </td>
						<td><input type="text" size="20" id="userpress_wiki-sub_wiki_name" name="wiki_default[sub_wiki_name]" value="<?php print $this->_options['default']['sub_wiki_name']; ?>" /></td>
						</tr>
						<tr valign="top"  rel="subwiki_option">
						<td><label for="userpress_wiki-sub_wiki_order_by"><?php _e('How should Sub Wikis be sorted?', $this->translation_domain); ?></label> </td>
						<td><select id="userpress_wiki-sub_wiki_order_by" name="wiki_default[sub_wiki_order_by]" >
						<option value="menu_order" <?php if ($this->_options['default']['sub_wiki_order_by'] == 'menu_order'){ echo 'selected="selected"'; } ?> ><?php _e('Menu Order/Order Created', $this->translation_domain); ?></option>
						<option value="title" <?php if ($this->_options['default']['sub_wiki_order_by'] == 'title'){ echo 'selected="selected"'; } ?> ><?php _e('Title', $this->translation_domain); ?></option>
						<option value="rand" <?php if ($this->_options['default']['sub_wiki_order_by'] == 'rand'){ echo 'selected="selected"'; } ?> ><?php _e('Random', $this->translation_domain); ?></option>
						</select></td>
						</tr>
						<tr valign="top" rel="subwiki_option">
						<td><label for="userpress_wiki-sub_wiki_order"><?php _e('How should Sub Wikis be ordered?', $this->translation_domain); ?></label> </td>
						<td><select id="userpress_wiki-sub_wiki_order" name="wiki_default[sub_wiki_order]" >
						<option value="ASC" <?php if ($this->_options['default']['sub_wiki_order'] == 'ASC'){ echo 'selected="selected"'; } ?> ><?php _e('Ascending', $this->translation_domain); ?></option>
						<option value="DESC" <?php if ($this->_options['default']['sub_wiki_order'] == 'DESC'){ echo 'selected="selected"'; } ?> ><?php _e('Descending', $this->translation_domain); ?></option>
						</select></td>
						</tr>
					*/
				?>
				<tr valign="top">
					<td>
						<label for="wiki_default[no_wiki_tags]"><?php _e('Disable Wiki Tags', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[no_wiki_tags]" value="TRUE" <?php if (isset($this->_options['default']['no_wiki_tags']) && $this->_options['default']['no_wiki_tags'] == 'TRUE')echo $this->_options['default']['no_wiki_tags']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="wiki_default[override_404]"><?php _e('Replace Theme 404 page', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[override_404]" value="TRUE" <?php if (isset($this->_options['default']['override_404']) && $this->_options['default']['override_404'] == 'TRUE')echo $this->_options['default']['override_404']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="wiki_default[convert_button]"><?php _e('Add "Convert to Wiki" Button on edit pages', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[convert_button]" value="TRUE" <?php if (isset($this->_options['default']['convert_button']) && $this->_options['default']['convert_button'] == 'TRUE')echo $this->_options['default']['convert_button']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="wiki_default[display_bps_button]"><?php _e('Display Subscribe button with Wiki title', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[display_bps_button]" value="TRUE" <?php if (isset($this->_options['default']['display_bps_button']) && $this->_options['default']['display_bps_button'] == 'TRUE')echo $this->_options['default']['display_bps_button']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="wiki_default[bps_button_everywhere]"><?php _e('Display Subscribe Button on Non-Wiki Posts', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[bps_button_everywhere]" value="TRUE" <?php if (isset($this->_options['default']['bps_button_everywhere']) && $this->_options['default']['bps_button_everywhere'] == 'TRUE')echo $this->_options['default']['bps_button_everywhere']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="wiki_default[insert_toc]">
							<?php _e('Insert Table of Contents above contents', $this->translation_domain); ?>
						</label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[insert_toc]" value="TRUE" <?php if (isset($this->_options['default']['insert_toc']) && $this->_options['default']['insert_toc'] == 'TRUE')echo $this->_options['default']['insert_toc']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr valign="top">
					<td>
						<label for="wiki_default[modal_links]"><?php _e('Open internal wiki links in modal window', $this->translation_domain); ?></label>
					</td>
					<td>
						<input type="checkbox" name="wiki_default[modal_links]" value="TRUE" <?php if (isset($this->_options['default']['modal_links']) && $this->_options['default']['modal_links'] == 'TRUE')echo $this->_options['default']['modal_links']?'checked="checked"':''; ?> />
					</td>
				</tr>
				<tr>&nbsp;</tr>
				<?php
					// Get setting for tag restriction setting otherwise set to none (default)				
					if ( isset ( $this->_options['default']['wiki_html_tag_restriction'] ) ) {
						$tag_restriction_setting  =  $this->_options['default']['wiki_html_tag_restriction'];			
					}
					else {
						$tag_restriction_setting  =  'none';
					}

					// Get setting for tag declarations otherwise set to embed, object, iframe, form, script
					if ( isset ( $this->_options['default']['wiki_html_tag_declaration'] ) ) {
						$tag_restriction_declaration  =  $this->_options['default']['wiki_html_tag_declaration'];
					}
					else {
						$tag_restriction_declaration  =  "";
					}
				?>

				<tr valign="top">
					<td>
						<label for="wiki_default[wiki_html_tag_restriction]">
							<?php _e( 'HTML Tag Restrictions', $this->translation_domain ); ?>
						</label>
					</td>
					<td>
					<input type="radio" name="wiki_default[wiki_html_tag_restriction]" value="allow" <?php if ( $tag_restriction_setting == 'allow' ) { echo "checked"; } ?>>
						Allow Certain HTML Tags
						<br />
						<input type="radio" name="wiki_default[wiki_html_tag_restriction]" value="restrict" <?php if ( $tag_restriction_setting  ==  'restrict' ) { echo "checked"; } ?>>
						Exclude Certain HTML Tags
						<br />
						<input type="radio" name="wiki_default[wiki_html_tag_restriction]" value="none" <?php if ( $tag_restriction_setting  ==  'none' ) { echo "checked"; } ?>>
						No Restrictions
						<br /><br />
					</td>
					<td>&nbsp</td>
				</tr>
				<tr valign="top">
					<td>&nbsp;</td>
					<td>
						
						<span class="howto">(enter one HTML tag per line without brackets)</span>
						<textarea name="wiki_default[wiki_html_tag_declaration]" cols="50" rows="5"><?php echo $tag_restriction_declaration; ?></textarea>
						<span class="howto">The following are disabled by default:<br /><strong>script, form, embed, object, iframe</strong></span>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="submit_settings" value="<?php _e('Save Changes', $this->translation_domain) ?>" />
			</p>
		</form>
	<?php
}

/**
* Deactivation hook
*
* @see		http://codex.wordpress.org/Function_Reference/register_deactivation_hook
*
* @global	object	$wpdb
*/
function uninstall() {
	global $wpdb;
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
	// Nothing to do
}

function wiki_revision_fields($fields) {
	$fields['userpress_wiki_revision_note'] = 'Revision Note';
	return $fields;
}

function wiki_revision_field($value, $field) {
	global $revision;
	if (isset($revision->ID)) {
		$meta = get_post_custom($revision->ID);
	}

	if (!isset($meta["userpress_wiki_revision_note"])) {
		return '';
	}
	
	return $meta['userpress_wiki_revision_note'][0];
}

/**
* Initialize the plugin
*
* @see		http://codex.wordpress.org/Plugin_API/Action_Reference
* @see		http://adambrown.info/p/wp_hooks/hook/init
*/
function init() {
	global $wpdb, $wp_rewrite, $current_user, $blog_id, $wp_roles;

	if ( preg_match('/mu\-plugin/', PLUGINDIR) > 0 ) {
		load_muplugin_textdomain($this->translation_domain, dirname(plugin_basename(__FILE__)).'/languages');
	} 
	else {
		load_plugin_textdomain($this->translation_domain, false, dirname(plugin_basename(__FILE__)).'/languages');
	}

	$this->install();
	$labels = array(
		'name' => __('Wikis', $this->translation_domain),
		'singular_name' => __('Wiki', $this->translation_domain),
		'add_new' => __('Add Wiki', $this->translation_domain),
		'add_new_item' => __('Add New Wiki', $this->translation_domain),
		'edit_item' => __('Edit Wiki', $this->translation_domain),
		'new_item' => __('New Wiki', $this->translation_domain),
		'view_item' => __('View Wiki', $this->translation_domain),
		'search_items' => __('Search Wiki', $this->translation_domain),
		'not_found' =>  __('No wiki found', $this->translation_domain),
		'not_found_in_trash' => __('No wikis found in Trash', $this->translation_domain),
		'menu_name' => __('Wikis', $this->translation_domain),
	);
	$supports = array( 'title', 'editor', 'author', 'revisions', 'comments', 'thumbnail', 'trackbacks');

	// Has to come before the 'userpress_wiki' post type definition
	register_taxonomy( 'userpress_wiki_category', 'userpress_wiki', array(
			'hierarchical' => true,
			'rewrite' => array(
					'slug' => $this->_options['default']['slug'] . '/' . WIKI_SLUG_CATEGORIES,
					'with_front' => false,
				),
			'capabilities' => array(
					'manage_terms' => 'edit_others_wikis',
					'edit_terms' => 'edit_others_wikis',
					'delete_terms' => 'edit_others_wikis',
					'assign_terms' => 'edit_published_wikis',
				),
			'labels' => array(
					'name' => __( 'Wiki Categories', $this->translation_domain ),
					'singular_name' => __( 'Wiki Category', $this->translation_domain ),
					'search_items' => __( 'Search Wiki Categories', $this->translation_domain ),
					'all_items' => __( 'All Wiki Categories', $this->translation_domain ),
					'parent_item' => __( 'Parent Wiki Category', $this->translation_domain ),
					'parent_item_colon' => __( 'Parent Wiki Category:', $this->translation_domain ),
					'edit_item' => __( 'Edit Wiki Category', $this->translation_domain ),
					'update_item' => __( 'Update Wiki Category', $this->translation_domain ),
					'add_new_item' => __( 'Add New Wiki Category', $this->translation_domain ),
					'new_item_name' => __( 'New Wiki Category Name', $this->translation_domain ),
				),
		)
	);

	// Has to come before the 'userpress_wiki' post type definition
	register_taxonomy( 'userpress_wiki_tag', 'userpress_wiki', array(
			'rewrite' => array( 
					'slug' => $this->_options['default']['slug'] . '/' . WIKI_SLUG_TAGS,
					'with_front' => false,
				),
			'capabilities' => array(
					'manage_terms' => 'edit_others_wikis',
					'edit_terms' => 'edit_others_wikis',
					'delete_terms' => 'edit_others_wikis',
					'assign_terms' => 'edit_published_wikis',
				),
			'labels' => array(
					'name'			=> __( 'Wiki Tags', $this->translation_domain ),
					'singular_name'	=> __( 'Wiki Tag', $this->translation_domain ),
					'search_items'	=> __( 'Search Wiki Tags', $this->translation_domain ),
					'popular_items'	=> __( 'Popular Wiki Tags', $this->translation_domain ),
					'all_items'		=> __( 'All Wiki Tags', $this->translation_domain ),
					'edit_item'		=> __( 'Edit Wiki Tag', $this->translation_domain ),
					'update_item'	=> __( 'Update Wiki Tag', $this->translation_domain ),
					'add_new_item'	=> __( 'Add New Wiki Tag', $this->translation_domain ),
					'new_item_name'	=> __( 'New Wiki Tag Name', $this->translation_domain ),
					'separate_items_with_commas'	=> __( 'Separate wiki tags with commas', $this->translation_domain ),
					'add_or_remove_items'			=> __( 'Add or remove wiki tags', $this->translation_domain ),
					'choose_from_most_used'			=> __( 'Choose from the most used wiki tags', $this->translation_domain ),
				),
		)
	);

	register_taxonomy( 'userpress_wiki_flags', 'userpress_wiki', array(
			'rewrite' => array(
					'slug' => $this->_options['default']['slug'] . '/' . WIKI_SLUG_FLAGS,
					'with_front' => false,
				),
			'capabilities' => array(
					'manage_terms' => 'edit_others_wikis',
					'edit_terms' => 'edit_others_wikis',
					'delete_terms' => 'edit_others_wikis',
					'assign_terms' => 'edit_published_wikis',
				),
			'labels' => array(
				'name'                        =>  __( 'Wiki Flags', $this->translation_domain ),
				'singular_name'               =>  __( 'Wiki Flag', $this->translation_domain ),
				'search_items'                =>  __( 'Search Wiki Flags', $this->translation_domain ),
				'popular_items'               =>  __( 'Popular Wiki Flags', $this->translation_domain ),
				'all_items'                   =>  __( 'All Wiki Flags', $this->translation_domain ),
				'edit_item'                   =>  __( 'Edit Wiki Flag', $this->translation_domain ),
				'update_item'	              =>  __( 'Update Wiki Flag', $this->translation_domain ),
				'add_new_item'	              =>  __( 'Add New Wiki Flag', $this->translation_domain ),
				'new_item_name'	              =>  __( 'New Wiki Flag Name', $this->translation_domain ),
				'separate_items_with_commas'  =>  __( 'Separate wiki flags with commas', $this->translation_domain ),
				'add_or_remove_items'         =>  __( 'Add or remove wiki flags', $this->translation_domain ),
				'choose_from_most_used'       =>  __( 'Choose from the most used wiki flags', $this->translation_domain ),
				),
		)
	);

	register_post_type( 'userpress_wiki', array(
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'publicly_queryable' => true,
			'capability_type'    => 'wiki',
			'hierarchical'       => true,
			'map_meta_cap'       => true,
			'query_var'          => true,
			'supports'           => $supports,
			'has_archive'        => true,
			'rewrite'            => array(
					'slug' => $this->_options['default']['slug'],
					'with_front' => false,
				),
		)
	);

	wp_insert_term('Spam', 'userpress_wiki_flags',array('description'=>'This is page does not contain content relevant to the site.'));
	wp_insert_term('Poor Attribution', 'userpress_wiki_flags',array('description'=>'This page does not properly cite its sources.'));
	wp_insert_term('Readability', 'userpress_wiki_flags',array('description'=>'This page is difficult to read and requires additional editing.'));

	/*
		if (isset($_REQUEST['subscribe']) && wp_verify_nonce($_REQUEST['_wpnonce'], "wiki-subscribe-wiki_{$_REQUEST['post_id']}")) {
		if (isset($_REQUEST['email'])) {
		if ($wpdb->insert("{$this->db_prefix}wiki_subscriptions",
		array('blog_id' => $blog_id,
		'wiki_id' => $_REQUEST['post_id'],
		'email' => $_REQUEST['email']))) {
		setcookie('userpress_wiki_email', $_REQUEST['email'], time()+3600*24*365, '/');
		wp_redirect(get_permalink($_REQUEST['post_id']));
		exit();
		}
		} else if (is_user_logged_in()){
		if ($wpdb->insert("{$this->db_prefix}wiki_subscriptions",
		array('blog_id' => $blog_id,
		'wiki_id' => $_REQUEST['post_id'],
		'user_id' => $current_user->ID))) {
		wp_redirect(get_permalink($_REQUEST['post_id']));
		exit();
		}
		}
		}
		if (isset($_GET['action']) && $_GET['action'] == 'cancel-wiki-subscription') {
		if ($wpdb->query("DELETE FROM {$this->db_prefix}wiki_subscriptions WHERE ID = ".intval($_GET['sid']).";")) {
		wp_redirect(get_option('siteurl'));
		exit();
		}
		}
	*/

	if (isset($_POST['wiki_default']) && wp_verify_nonce($_POST['_wpnonce'], 'userpress_wiki-update-options')) {
		$this->_options['default']['slug'] = untrailingslashit($_POST['wiki_default']['slug']);
		$this->_options['default']['breadcrumbs_in_title'] = intval($_POST['wiki_default']['breadcrumbs_in_title']);
		$this->_options['default']['frontpage_slug'] = $_POST['wiki_default']['frontpage_slug'];
		if (isset($_POST['wiki_default']['frontpage_home']) && $_POST['wiki_default']['frontpage_home'] == 1) {	
			$fpslug = $this->_options['default']['frontpage_slug'];
			if ($fpid = $this->get_id_by_slug($fpslug, 'userpress_wiki')) {
				update_option( 'page_on_front', $fpid );
				update_option( 'show_on_front', 'page' );
			}
		}
		$this->_options['default']['wiki_name'] = $_POST['wiki_default']['wiki_name'];
		$this->_options['default']['wiki_crumbs'] = $_POST['wiki_default']['wiki_crumbs'];
		if (isset($_POST['wiki_default']['modal_links'])){
			$this->_options['default']['modal_links'] = $_POST['wiki_default']['modal_links'];
		}
		if (isset($_POST['wiki_default']['sub_wiki_name'])) {
			$this->_options['default']['sub_wiki_name'] = $_POST['wiki_default']['sub_wiki_name'];
		}
		if (isset($_POST['wiki_default']['sub_wiki_order_by'])) {
			$this->_options['default']['sub_wiki_order_by'] = $_POST['wiki_default']['sub_wiki_order_by'];
		}
		$this->_options['default']['default_wiki_editors'] = $_POST['wiki_default']['default_wiki_editors'];
		if (isset($_POST['wiki_default']['sub_wiki_order'])) {
			$this->_options['default']['sub_wiki_order'] = $_POST['wiki_default']['sub_wiki_order'];
		}
		$this->_options['default']['subpages_enabled'] = $_POST['wiki_default']['subpages_enabled'];

		if (isset($_POST['wiki_default']['insert_toc'])) {
			$this->_options['default']['insert_toc'] = $_POST['wiki_default']['insert_toc'];
		} 
		else {
			$this->_options['default']['insert_toc'] = 'FALSE';
		}
		if (isset($_POST['wiki_default']['display_bps_button'])) {
			$this->_options['default']['display_bps_button'] = $_POST['wiki_default']['display_bps_button'];
		}
		else {
			$this->_options['default']['display_bps_button'] = 'FALSE';
		}
		if (isset($_POST['wiki_default']['bps_button_everywhere'])) {
			$this->_options['default']['bps_button_everywhere'] = $_POST['wiki_default']['bps_button_everywhere'];
		}
		else {
			$this->_options['default']['bps_button_everywhere'] = 'FALSE';
		}
		if (isset($_POST['wiki_default']['no_wiki_tags'])) {
			$this->_options['default']['no_wiki_tags'] = $_POST['wiki_default']['no_wiki_tags'];
		}
		else {
			$this->_options['default']['no_wiki_tags'] = 'FALSE';
		}
		if (isset($_POST['wiki_default']['override_404'])) {
			$this->_options['default']['override_404'] = $_POST['wiki_default']['override_404'];
		}
		else {
			$this->_options['default']['override_404'] = 'FALSE';
		}
		if (isset($_POST['wiki_default']['convert_button'])) {
			$this->_options['default']['convert_button'] = $_POST['wiki_default']['convert_button'];
		}
		else {
			$this->_options['default']['convert_button'] = 'FALSE';
		}
		
		if (isset($_POST['wiki_default']['publish_wiki_privileges'])) {
			$this->_options['default']['publish_wiki_privileges'] = $_POST['wiki_default']['publish_wiki_privileges'];
		}
		else {
			$this->_options['default']['publish_wiki_privileges'] = array();
		}
		if ( isset ( $_POST['wiki_default']['wiki_html_tag_restriction'] ) ) {
			$this->_options['default']['wiki_html_tag_restriction']  =  $_POST['wiki_default']['wiki_html_tag_restriction'];
		}
		if ( isset ( $_POST['wiki_default']['wiki_html_tag_declaration'] ) ) {
			$this->_options['default']['wiki_html_tag_declaration']  =  $_POST['wiki_default']['wiki_html_tag_declaration'];
		}
		update_option('wiki_default', $this->_options['default']);

		foreach ($wp_roles->roles as $role_key => $role) {
			$role_obj = get_role($role_key);
			if (isset($_POST['edit_wiki_privileges']) && $role_key == $_POST['edit_wiki_privileges']) {
				$role_obj->add_cap('edit_wiki_privileges');
			}
			else {
				$role_obj->remove_cap('edit_wiki_privileges');
			}
		}

		wp_redirect('edit.php?post_type=userpress_wiki&page=userpress_wiki&userpress_wiki_settings_saved=1');
		exit();
	}
	
	// This is to make sure it doesn't conflict with UserTheme's "up546E_foundation_js_init"
	if ( ! function_exists( 'up546E_foundation_js_init' ) ) {
		function up546E_foundation_js_init () {
			echo "
				<script>
				jQuery.noConflict();
				jQuery(function() {
				jQuery(document).foundation();
				});
				</script>
			";
		}
	}

	add_action('wp_ajax_wiki_tag_search',array(&$this,'wiki_tag_search'));
	add_action('wp_ajax_nopriv_wiki_tag_search',array(&$this,'wiki_tag_search'));
	add_action('wp_ajax_wiki_title_search',array(&$this,'wiki_search'));
	add_action('wp_ajax_nopriv_wiki_title_search',array(&$this,'wiki_search'));
	wp_register_style('my-jquery-ui','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');   
	add_filter( '_wp_post_revision_fields', array(&$this,'wiki_revision_fields') ); 
	add_filter( '_wp_post_revision_field_userpress_wiki_revision_note', array(&$this,'wiki_revision_field'),10,2);
	add_action( 'wp_head', array(&$this,'wiki_header_js'));
	
	$options = get_option('wiki_default');
	if ($options['no_wiki_tags'] == 'TRUE')
		add_action( 'init', array(&$this, 'disable_wiki_tags'));
	
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['wiki/?$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
	


}

function get_id_by_slug( $slug, $post_type = '' ) {
    $id = null;
    $args = array(
        'name'          => $slug,
        'max_num_posts' => 1
    );
    if( '' != $post_type ) {
        $args = array_merge( $args, array( 'post_type' => $post_type ) );
    }

    $query = new WP_Query( $args );
    if( $query->have_posts() ) {
        $query->the_post();
        $id = get_the_ID();
    }
    wp_reset_postdata();

    return $id;
}

function wiki_header_js() {
	ob_start();
	?>
		<script>
			var wpurl = <?php echo json_encode(get_bloginfo('wpurl')); ?>;
			var ajaxurl = <?php echo json_encode(admin_url( 'admin-ajax.php' )); ?>;
		</script>
	<?php
	echo ob_get_clean();
}

function wp_enqueue_scripts() {
	wp_register_script('userpress_wiki_js', plugins_url('js/wiki-utils.js',__FILE__), null, $this->current_version);
	wp_register_script('userpress_wiki_modal_js', plugins_url('js/wiki-modal.js',__FILE__), null, $this->current_version);
	wp_register_script('userpress_wiki_footer_js', plugins_url('js/wiki-footer.js',__FILE__), array('jquery'), $this->current_version, TRUE);
	/** 
		UserPress uses a customized version of Zurb's Foundation javascript on UserPress-related pages (for things like dropdown buttons and modals). If you intend to use Foundation in your own custom theme you should deregister "userpress_foundation_js" and include your own version via your theme's functions.php file.
		http://codex.wordpress.org/Function_Reference/wp_register_script
		http://codex.wordpress.org/Function_Reference/wp_deregister_script
		http://codex.wordpress.org/Function_Reference/wp_script_is
	**/
	wp_register_script('userpress_foundation_js', plugins_url('js/foundation.min.js',__FILE__), array('jquery'), $this->current_version, TRUE);
	wp_register_style('userpress_wiki_style', plugins_url('css/style.css',__FILE__), null, $this->current_version);
	wp_register_style('userpress_wiki-print-css', plugins_url('css/print.css',__FILE__), null, $this->current_version, 'print');
	//wp_register_style('userpress_wiki-suggest-css', plugins_url('css/suggest.css',__FILE__), null, $this->current_version);
	wp_register_style('userpress_foundation_css', plugins_url('css/foundation.min.css',__FILE__), null, $this->current_version);
	wp_register_style('userpress_normalize_css', plugins_url('css/normalize.css',__FILE__), null, $this->current_version);
	wp_register_style('userpress_foundation_icons', plugins_url('icons/foundation-icons.css',__FILE__), null, $this->current_version);
	//This only affects wiki pages
	if (get_query_var('post_type') == 'userpress_wiki' || get_post_meta(get_the_id(), '_has_wiki_features', true ) == 1 || is_post_type_archive('userpress_wiki') || is_tax('userpress_wiki_tag') || is_tax('userpress_wiki_flags') || is_tax('userpress_wiki_category')) {
		wp_enqueue_script('utils');
		wp_enqueue_script('userpress_wiki_js');
		wp_enqueue_script('userpress_wiki_footer_js');
		wp_enqueue_script('suggest');
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_style('userpress_wiki_style');
		wp_enqueue_style('userpress_wiki-print-css');
		wp_enqueue_style('userpress_foundation_css');
		wp_enqueue_style('userpress_normalize_css');
		wp_enqueue_style('userpress_foundation_icons');
	}
	wp_enqueue_script( 'userpress_foundation_js', array('jquery'), false, true);
}

/*
	function is_subscribed() {
	global $wpdb, $current_user, $post, $blog_id;
	if (is_user_logged_in()) {
	if ($wpdb->get_var("SELECT ID FROM {$this->db_prefix}wiki_subscriptions WHERE blog_id = {$blog_id} AND wiki_id = {$post->ID} AND user_id = {$current_user->ID}") > 0) {
	return true;
	}
	} else if (isset($_COOKIE['userpress_wiki_email']) && $wpdb->get_var("SELECT ID FROM {$this->db_prefix}wiki_subscriptions WHERE blog_id = {$blog_id} AND wiki_id = {$post->ID} AND email = '{$_COOKIE['userpress_wiki_email']}'") > 0) {
	return true;
	}
	return false;
	}
*/

function meta_boxes() {

	global $post, $current_user;

	if ($post->post_author == $current_user->ID || current_user_can('edit_posts')) {
		add_meta_box('userpress-wiki-privileges', __('Wiki Privileges', $this->translation_domain), array(&$this, 'privileges_meta_box'), 'userpress_wiki', 'side');
		//add_meta_box('userpress-wiki-notifications', __('Wiki E-mail Notifications', $this->translation_domain), array(&$this, 'notifications_meta_box'), 'userpress_wiki', 'side');
		//add_meta_box('userpress-wiki-moreattributes', __('Wiki Attributes', $this->translation_domain), array(&$this, 'attributes_meta_box'), 'userpress_wiki', 'normal','high');			
		add_meta_box('userpress-wiki-subtitle', __('Wiki Subtitle', $this->translation_domain), array(&$this, 'subtitle_meta_box'), 'userpress_wiki', 'normal','high');
		add_meta_box('userpress-wiki-revisionnote', __('Wiki Revision Note', $this->translation_domain), array(&$this, 'revisionnote_meta_box'), 'userpress_wiki', 'normal','high');			
	}
}

function post_type_link($permalink, $post_id, $leavename) {

	$post = get_post($post_id);
	$rewritecode = array(
			'%userpress_wiki%',
		);

	if ($post->post_type == 'userpress_wiki' && '' != $permalink) {
		$ptype = get_post_type_object($post->post_type);

		if ($ptype->hierarchical) {
			$uri = get_page_uri($post);
			$uri = untrailingslashit($uri);
			$uri = strrev( stristr( strrev( $uri ), '/' ) );
			$uri = untrailingslashit($uri);

			if (!empty($uri)) {
				$uri .= '/';
				$permalink = str_replace('%userpress_wiki%', "{$uri}%userpress_wiki%", $permalink);
			}
		}

		$rewritereplace = array(
			($post->post_name == "")?(isset($post->id)?$post->id:0):$post->post_name
		);

		$permalink = str_replace($rewritecode, $rewritereplace, $permalink);

	}
	else {
		// if they're not using the fancy permalink option
	}

	return $permalink;
}

function term_link($termlink, $term, $taxonomy) {

	$rewritecode = array(
			'%userpress_wiki_category%',
			'%userpress_wiki_tag%',
		);

	if (preg_match('/^userpress_wiki_/', $term->taxonomy) > 0 && '' != $termlink) {
		$rewritereplace = array(
				($term->slug == "")?(isset($term->term_id)?$term->term_id:0):$term->slug,
				($term->slug == "")?(isset($term->term_id)?$term->term_id:0):$term->slug,
			);
		$termlink = str_replace($rewritecode, $rewritereplace, $termlink);
	}
	else {
		// if they're not using the fancy permalink option
	}

	return $termlink;
}

function name_save($post_name) {

	if (isset($_POST['post_type']) && $_POST['post_type'] == 'userpress_wiki' && empty($post_name)) {
		$post_name = $_POST['post_title'];
	}

	return $post_name;
}

function privileges_meta_box($echo = true) {

	global $post, $edit_post;

	$wiki = isset($post)?$post:$edit_post;
	$settings = get_option('userpress_wiki_settings');
	$meta = get_post_custom($wiki->ID);
	$content  = '';
	if (isset($meta["userpress_wiki_privileges"][0])) {
		$current_privileges = unserialize($meta["userpress_wiki_privileges"][0]);
	}
	if (!isset($current_privileges) || !is_array($current_privileges)) {
		$current_privileges = array('edit_posts');
	}
	$privileges = array(
			'anyone' => __('Anyone', $this->translation_domain),
			'network' => __('Network users', $this->translation_domain),
			'site' => __('Site users', $this->translation_domain),
			'edit_posts' => __('Users who can edit posts in this site', $this->translation_domain),
		);
	$content .= '<input type="hidden" name="userpress_wiki_privileges_meta" value="1" />';
	$content .= '<div id="userModal" class="reveal-modal small" data-reveal>';

	if (!is_admin()) {
		$content .= '<a class="close-reveal-modal right">X</a>';
	}
	
	if (isset($_GET['action']) && $_GET['action'] == 'create') {
		$wikioptions = get_option('wiki_default');
		foreach ($privileges as $key => $privilege) {
			$content .= '<p><label class="userpress_wiki_label_roles"><input type="radio" name="userpress_wiki_privileges[]" value="'.$key.'" '.((isset($wikioptions['default_wiki_editors']) && $key == $wikioptions['default_wiki_editors']) ?'checked="checked"':'').' /> '.$privilege.'</label><br class="userpress_wiki_br_roles"/></p>';
		}
	}
	else {
		foreach ($privileges as $key => $privilege) {
			$content .= '<p><label class="userpress_wiki_label_roles"><input type="radio" name="userpress_wiki_privileges[]" value="'.$key.'" '.((in_array($key, $current_privileges))?'checked="checked"':'').' /> '.$privilege.'</label><br class="userpress_wiki_br_roles"/></p>';
		}
	}

	$content .= '</div><div class="clear"></div>';

	if ($echo) {
		echo $content;
	}

	return $content;
}

function wiki_taxonomies($echo = true) {

	global $post, $edit_post;

	$wiki = isset($post)?$post:$edit_post;
	$wiki_tags = wp_get_object_terms( $wiki->ID, 'userpress_wiki_tag', array( 'fields' => 'names' ) );
	$all_wiki_flags = get_terms('userpress_wiki_flags',array('hide_empty'=>0));
	$wiki_flags = wp_get_object_terms( $wiki->ID, 'userpress_wiki_flags', array( 'fields' => 'names' ) );
	$wiki_cats = wp_get_object_terms( $wiki->ID, 'userpress_wiki_category', array( 'fields' => 'ids' ) );
	$wiki_all_cats = get_terms('userpress_wiki_category',array('hide_empty'=>0));
	$wiki_cat = empty( $wiki_cats ) ? false : reset( $wiki_cats );
	$content  = '';

	// TAGS
	$options = get_option('wiki_default');
	if ($options['no_wiki_tags'] != 'TRUE') {
		$content .= '<label for="wiki-tags">'.__('Tags:', $this->translation_domain).'</label>';
		$content .= '<input type="text" size="20" id="userpress_wiki-tags" name="userpress_wiki_tags" value="'. implode( ', ', $wiki_tags ).'" />';
	}
	
	$content .= '<table id="wiki-taxonomies">';
	$content .= '<tr>';
	$terms = get_terms("userpress_wiki_category");
	$count = count($terms);
	
	if ( $count > 0 ){
		$content .= '<td id="wiki-category-td">';
		$content .= '<label for="wiki-cats"><a href="#" data-reveal-id="catModal"> <i class="fi-folder"></i>  '.__('Categories', $this->translation_domain).'</a>	</label>';
	}
	
	$content .= '<div id="catModal" class="reveal-modal small" data-reveal><a class="close-reveal-modal right">X</a>';
	ob_start();
	
	foreach($wiki_all_cats as $cat) {
		if(in_array($cat->term_id, $wiki_cats)) {
			$checkbox = ' checked="checked" ';
		}
		else {
			$checkbox = '';
		}
		$content .= '<p><label for="userpress_wiki_cats_' . $cat->name . '"><input type="checkbox" id="userpress_wiki_cats_' . $cat->name . '" name="userpress_wiki_category[]" value="'. $cat->term_id .'"' . $checkbox . ' /> ' . $cat->name . '<br />'  . $cat->description . '</label></p>';	
	}
	$content .= ob_get_clean();
	$content .= '</div>';
	$content .= ' </td>';
	$content .= '<td id="wiki-flags-label">';
	$content .= '<label for="wiki-flags"><a href="#" data-reveal-id="flagModal"><i class="fi-flag"></i> '.__('Flags', $this->translation_domain).'</a>	</label>';
	$content .= '</td>';
	$content .= '<td id="wiki-permissions-label">';
	$content .= '<label for="wiki-permissions"><a href="#" data-reveal-id="userModal"><i class="step fi-lock"></i> '. __('Edit User Permissions', $this->translation_domain).'</a></label>';
	$content .= '</td>';
	$content .= '<td id="wiki-flags-td"><div id="flagModal" class="reveal-modal small" data-reveal><a class="close-reveal-modal right">X</a>';
	
	foreach($all_wiki_flags as $flag) {
		if(in_array($flag->name, $wiki_flags)) {
			$checkbox = ' checked="checked" ';
		}
		else {
			$checkbox = '';
		}
		$content .= '<p><label for="userpress_wiki_flags_' . $flag->name . '"><input type="checkbox" id="userpress_wiki_flags_' . $flag->name . '" name="userpress_wiki_flags[]" value="'. $flag->name .'"' . $checkbox . ' /> ' . $flag->name . '<br><span class="flag-description">'  . $flag->description .'</span></label></p>';	
	}

	$content .= '</div></td></tr></table>';

	if ($echo) {
		echo $content;
	}

	return $content;
}

function subtitle_meta_box( $echo = true) {
	global $post, $edit_post;
	$wiki = isset($post)?$post:$edit_post;
	$meta = get_post_custom($wiki->ID);

	if (!isset($meta["userpress_wiki_subtitle"])) {
		$meta['userpress_wiki_subtitle'][0] = '';
	}

	$text = $meta['userpress_wiki_subtitle'][0];
	
	if($echo) {
		$content = '<input type="text" class = "widefat" name="userpress_wiki_subtitle" id="userpress_wiki_subtitle" placeholder="Subtitle" value="' . $text . '"><br>';
	}

	if ($echo) {
	echo $content;
	}

	return $content;
}

function revisionnote_meta_box( $echo = true) {

	global $post, $edit_post;
	$wiki = isset($post)?$post:$edit_post;
	$meta = get_post_custom($wiki->ID);
	
	if (!isset($meta["userpress_wiki_revision_note"])) {
		$meta['userpress_wiki_revision_note'][0] = '';
	}

	$text = $meta['userpress_wiki_revision_note'][0];
	
	if($echo) {
	}
	
	$content = '<textarea class = "widefat" name="userpress_wiki_revision_note" id="userpress_wiki_revision_note" placeholder="Revision Note">' . $text . '</textarea><br>';

	if ($echo) {
		echo $content;
	}

	return $content;
}

function attributes_meta_box_frontend() {

	global $post, $edit_post;
	
	$content = '';
	$wiki = isset($post)?$post:$edit_post;
	$meta = get_post_custom($wiki->ID);

	if (!isset($meta["userpress_wiki_revision_note"])) {
		$meta['userpress_wiki_revision_note'] = '';
	}
	
	if (!isset($meta["userpress_wiki_infobox"])) {
		$meta['userpress_wiki_infobox'] = '';
	}

	$content .= '<textarea name="userpress_wiki_revision_note" id="userpress_wiki_revision_note" placeholder="Revision Note"></textarea><br>';
	echo $content;
	
	//return $content;
}

/*
	function notifications_meta_box($echo = true) {
	global $post, $edit_post;
	$wiki = isset($post)?$post:$edit_post;
	$settings = get_option('userpress_wiki_settings');
	$meta = get_post_custom($wiki->ID);
	if (!isset($meta["userpress_wiki_email_notification"])) {
	$meta = array('userpress_wiki_email_notification' => array('enabled'));
	}
	$content  = '';
	$content .= '<input type="hidden" name="userpress_wiki_notifications_meta" value="1" />';
	$content .= '<div class="alignleft">';
	$content .= '<label><input type="checkbox" name="userpress_wiki_email_notification" value="enabled" '.(($meta["userpress_wiki_email_notification"][0] == "")?'':'checked="checked"').' /> '.__('Enable e-mail notifications', $this->translation_domain).'</label>';
	$content .= '</div>';
	$content .= '<div class="clear"></div>';
	if ($echo) {
	echo $content;
	}
	return $content;
	}
*/

function save_wiki_meta($post_id, $post = null) {
	//skip quick edit
	if ( defined('DOING_AJAX') ) {
		return;
	}
	//if (is_admin())
	//	return; 
	if ( $post->post_type == "userpress_wiki" && isset( $_POST['userpress_wiki_tags'] ) ) {
		$wiki_tags = $_POST['userpress_wiki_tags'];
		wp_set_post_terms( $post_id, $wiki_tags, 'userpress_wiki_tag' );
		//for any other plugin to hook into
		do_action( 'userpress_wiki_save_taxonomy_tags', $post_id, $wiki_tags );
	}

	if ( $post->post_type == "userpress_wiki" && isset( $_POST['userpress_wiki_flags'] ) ) {
		$wiki_flags = $_POST['userpress_wiki_flags'];
		wp_set_post_terms( $post_id, $wiki_flags, 'userpress_wiki_flags' );
		//for any other plugin to hook into
		do_action( 'userpress_wiki_save_taxonomy_flags', $post_id, $wiki_tags );
	}

	if ( $post->post_type == "userpress_wiki" && isset( $_POST['userpress_wiki_category'] ) && !is_admin()) {
		$wiki_category = array();

		foreach ($_POST['userpress_wiki_category'] as $this_wiki_cat) {
			$wiki_category[] = $this_wiki_cat;
		}

		wp_set_post_terms( $post_id, $wiki_category, 'userpress_wiki_category' );
		//for any other plugin to hook into
		do_action( 'userpress_wiki_save_taxonomy_category', $post_id, $wiki_category );
	}
	elseif ( $post->post_type == "userpress_wiki" && !isset( $_POST['userpress_wiki_category'] ) && !is_admin() ){
		$wiki_category = array();
		wp_set_post_terms( $post_id, $wiki_category, 'userpress_wiki_category' );
		do_action( 'userpress_wiki_save_taxonomy_category', $post_id, $wiki_category );
	}

	if ( $post->post_type == "userpress_wiki" && isset( $_POST['userpress_wiki_privileges_meta'] ) ) {
		$meta = get_post_custom($post_id);
		$privs = array();

			if (isset($_POST['userpress_wiki_privileges'])) {
			
			foreach ($_POST['userpress_wiki_privileges'] as $priv) {
				$privs[] = $priv;
			}
			
		}
		
		//print_r($privs);
		update_post_meta($post_id, 'userpress_wiki_privileges', $privs);
		//for any other plugin to hook into
		do_action( 'userpress_wiki_save_privileges_meta', $post_id, $meta );
	}

	if ( $post->post_type == "userpress_wiki" && isset( $_POST['userpress_wiki_notifications_meta'] ) ) {
		$meta = get_post_custom($post_id);
		update_post_meta($post_id, 'userpress_wiki_email_notification', $_POST['userpress_wiki_email_notification']);
		//for any other plugin to hook into
		do_action( 'userpress_wiki_save_notifications_meta', $post_id, $meta );
	}

	if ( $post->post_type == "userpress_wiki"  ) {
		$meta = get_post_custom($post_id);

		if(isset($_POST['userpress_wiki_subtitle'])) {
			update_post_meta($post_id, 'userpress_wiki_subtitle', $_POST['userpress_wiki_subtitle']);
		}
		if(isset($_POST['userpress_wiki_infobox'])) {
			update_post_meta($post_id, 'userpress_wiki_infobox', $_POST['userpress_wiki_infobox']);	
		}
		if(isset($_POST['userpress_wiki_revision_note'])) {
			update_post_meta($post_id, 'userpress_wiki_revision_note', $_POST['userpress_wiki_revision_note']);		
		}
	}
}


function widgets_init() {
	include_once 'lib/classes/WikiWidget.php';
	include_once 'lib/classes/SearchWikisWidget.php';
	include_once 'lib/classes/NewWikisWidget.php';
	include_once 'lib/classes/PopularWikisWidget.php';
	include_once 'lib/classes/WikiCategoriesWidget.php';
	include_once 'lib/classes/WikiTagsWidget.php';
	include_once 'lib/classes/WikiTagCloudWidget.php';

	register_widget('WikiWidget');
	register_widget('SearchWikisWidget');
	register_widget('NewWikisWidget');
	register_widget('PopularWikisWidget');
	register_widget('WikiCategoriesWidget');
	register_widget('WikiTagsWidget');
	register_widget('WikiTagCloudWidget');
}

function disable_wiki_tags(){
	global $wp_taxonomies;
	$taxonomy = 'userpress_wiki_tag';
	if ( taxonomy_exists( $taxonomy))
		unset( $wp_taxonomies[$taxonomy]);
}
 

/*
	function send_notifications($post_id) {
	global $wpdb;
	// We do autosaves manually with wp_publish_posts_autosave()
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	return;
	if ( !$post = get_post( $post_id, ARRAY_A ) )
	return;
	if ( $post['post_type'] != 'userpress_wiki' || !post_type_supports($post['post_type'], 'revisions') )
	return;
	// all revisions and (possibly) one autosave
	$revisions = wp_get_post_revisions($post_id, array( 'order' => 'ASC' ));
	$revision = array_pop($revisions);
	$post = get_post($post_id);
	$cancel_url = get_option('siteurl') . '?action=cancel-wiki-subscription&sid=';
	$admin_email = get_option('admin_email');
	$post_title = $post->post_title;
	$post_content = $post->post_content;
	$post_url = get_permalink($post_id);
	$revisions = wp_get_post_revisions($post->ID);
	$revision = array_shift($revisions);
	if ($revision) {
	$revert_url = wp_nonce_url(add_query_arg(array('revision' => $revision->ID), admin_url('revision.php')), "restore-post_$post->ID|$revision->ID" );
	} else {
	$revert_url = "";
	}
	//cleanup title
	$blog_name = get_option('blogname');
	$post_title = strip_tags($post_title);
	//cleanup content
	$post_content = strip_tags($post_content);
	//get excerpt
	$post_excerpt = $post_content;
	if (strlen($post_excerpt) > 255) {
	$post_excerpt = substr($post_excerpt,0,252) . '...';
	}
	$wiki_notification_content = array();
	$wiki_notification_content['user'] = "Dear Subscriber,
	POST_TITLE was changed
	You can read the Wiki page in full here: POST_URL
	EXCERPT
	Thanks,
	BLOGNAME
	Cancel subscription: CANCEL_URL";
	if ($revision) {
	$wiki_notification_content['author'] = "Dear Author,
	POST_TITLE was changed
	You can read the Wiki page in full here: POST_URL
	You can revert the changes: REVERT_URL
	EXCERPT
	Thanks,
	BLOGNAME
	Cancel subscription: CANCEL_URL";
	} else {
	$wiki_notification_content['author'] = "Dear Author,
	POST_TITLE was changed
	You can read the Wiki page in full here: POST_URL
	EXCERPT
	Thanks,
	BLOGNAME
	Cancel subscription: CANCEL_URL";
	}
	//format notification text
	foreach ($wiki_notification_content as $key => $content) {
	$wiki_notification_content[$key] = str_replace("BLOGNAME",$blog_name,$wiki_notification_content[$key]);
	$wiki_notification_content[$key] = str_replace("POST_TITLE",$post_title,$wiki_notification_content[$key]);
	$wiki_notification_content[$key] = str_replace("EXCERPT",$post_excerpt,$wiki_notification_content[$key]);
	$wiki_notification_content[$key] = str_replace("POST_URL",$post_url,$wiki_notification_content[$key]);
	$wiki_notification_content[$key] = str_replace("REVERT_URL",$revert_url,$wiki_notification_content[$key]);
	$wiki_notification_content[$key] = str_replace("\'","'",$wiki_notification_content[$key]);
	}
	global $blog_id;
	$query = "SELECT * FROM " . $this->db_prefix . "wiki_subscriptions WHERE blog_id = {$blog_id} AND wiki_id = {$post->ID}";
	$subscription_emails = $wpdb->get_results( $query, ARRAY_A );
	if (count($subscription_emails) > 0){
	foreach ($subscription_emails as $subscription_email){
	$loop_notification_content = $wiki_notification_content['user'];
	$loop_notification_content = $wiki_notification_content['user'];
	if ($subscription_email['user_id'] > 0) {
	if ($subscription_email['user_id'] == $post->post_author) {
	$loop_notification_content = $wiki_notification_content['author'];
	}
	$user = get_userdata($subscription_email['user_id']);
	$subscription_to = $user->user_email;
	} else {
	$subscription_to = $subscription_email['email'];
	}
	$loop_notification_content = str_replace("CANCEL_URL",$cancel_url . $subscription_email['ID'],$loop_notification_content);
	$subject_content = $blog_name . ': ' . __('Wiki Page Changes', $this->translation_domain);
	$from_email = $admin_email;
	$message_headers = "MIME-Version: 1.0\n" . "From: " . $blog_name .  " <{$from_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
	wp_mail($subscription_to, $subject_content, $loop_notification_content, $message_headers);
	}
	}
	}
*/   

}


function up546E_get_frontpage_uri() {
	$options = get_option('wiki_default');
	$fps = $options['frontpage_slug']; 
	return get_post_type_archive_link('userpress_wiki').$fps.'/';
}

include_once 'lib/classes/FrontPageRedirect.php';
$wiki = new up546E_Wiki();

function up546E_add_subscribe_button_to_title($title, $id) {
	global $post;
	global $up546E_show_title_button_once;
	$options = get_option('wiki_default');
	if (!isset($options['display_bps_button']) || $options['display_bps_button'] !='TRUE') {
		return $title;
	}
	if ((!isset($options['bps_button_everywhere']) || $options['bps_button_everywhere'] !='TRUE') AND get_post_type($id) != 'userpress_wiki') {
		return $title;
	}
	
	if ($id == get_the_id() && !is_archive() && !is_search() && in_the_loop() && $up546E_show_title_button_once != TRUE) {
		$title .= '<div style="float:right;">'.up546E_bppostsubbutton().'</div>';
		$up546E_show_title_button_once = TRUE;
	}
	
	return $title;
}

function up546E_register_subsribe_button_filter() {
	add_filter( 'the_title', 'up546E_add_subscribe_button_to_title', 20, 2 );
}

add_action('init','up546E_register_subsribe_button_filter');
function up546E_no_parrent_posts_allowed($data , $postarr) {
	$options = get_option('wiki_default');
	
	if (!isset($options['subpages_enabled']) || $options['subpages_enabled'] == 1 || $postarr['post_type'] != 'userpress_wiki') {
		return $data;
	}
	
	$data['post_parent'] = 0;
	return $data;
}

add_filter('wp_insert_post_data', 'up546E_no_parrent_posts_allowed', '99', 2 ); 
function up546E_add_wiki_archivepage_subnav_bar() {
	global $archivepageloop;
	global $wp_query;
	
	if (!is_main_query() || $archivepageloop === 1 || isset($_GET['action']) || is_feed()) {
		return;
	}
	if (!is_post_type_archive('userpress_wiki') && !is_tax('userpress_wiki_tag') && !is_tax('userpress_wiki_flags') && !is_tax('userpress_wiki_category')) {
		return;
	}
	
	$archivepageloop = 1;
	ob_start();
	?>
		<div class="userpress_wiki_tabs userpress_wiki_tabs_top"  style = "clear:both; z-index:1000; display:block; margin-bottom:100px">
		<div class="subtitle">

		<ul class="right">
		<li>
			<a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?action=create"><i class="fi-page-new"></i>
				 Create New Wiki
			</a>
		</li>
	<?php //list terms in a given taxonomy using wp_list_categories (also useful as a widget if using a PHP Code plugin)

	$taxonomy     = 'userpress_wiki_flags';
	$orderby      = 'name';
	$show_count   = 0;      // 1 for yes, 0 for no
	$pad_counts   = 0;      // 1 for yes, 0 for no
	$hierarchical = 1;      // 1 for yes, 0 for no
	$title        = '';
	$empty        = 0;

	$flags = array(
			'taxonomy'     => $taxonomy,
			'orderby'      => $orderby,
			'show_count'   => $show_count,
			'pad_counts'   => $pad_counts,
			'hierarchical' => $hierarchical,
			'title_li'     => $title,
			'hide_empty'   => $empty,
		);
	?>

	<li>
	<a href="#" data-dropdown="drop-flag"> <i class="fi-flag"></i> Flags</a></li>
	<ul id="drop-flag" data-dropdown-content class="f-dropdown">
	<?php wp_list_categories( $flags ); ?>
	</ul>

	<li><a href="#" data-dropdown="drop-etc"> <i class="fi-home"></i> Etc.</a></li>
	<ul id="drop-etc" data-dropdown-content class="f-dropdown">
	<li><a href="<?php echo up546E_get_frontpage_uri(); ?>"><i class="fi-home"></i> Frontpage</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=created"><i class="fi-burst-new"></i> New Articles</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=recently_modified"><i class="fi-clock"></i> Recently Modified</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=recently_discussed"><i class="fi-torsos-all"></i> Recently Discussed</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=most_discussed"><i class="fi-comments"></i> Most Discussed</a></li>
	<li><a href="<?php echo get_post_type_archive_link('userpress_wiki') ; ?>?view=alpha"><i class="fi-page-multiple"></i> Alphabetical Order</a></li>
	</br>
	</ul>
	</ul>

	<ul class="left">
	</ul>

	</div>
	</div>

	<?php
	$subtitlebar = ob_get_clean();
	echo $subtitlebar;
}

add_action( 'loop_start', 'up546E_add_wiki_archivepage_subnav_bar');
function up546E_user_can_publish() {
	
	$options = get_option('wiki_default');

	if (!empty($options)) {
		if (is_array($options['publish_wiki_privileges']) && in_array('editors', $options['publish_wiki_privileges']) && current_user_can('edit_posts')) {
			return TRUE;
		}
		if (is_array($options['publish_wiki_privileges']) && (in_array('network', $options['publish_wiki_privileges']) || in_array('site', $options['publish_wiki_privileges'])) && is_user_logged_in()) {
			return TRUE;
		}
		if (is_array($options['publish_wiki_privileges']) && in_array('anyone', $options['publish_wiki_privileges'])) {
			return TRUE;
		}
		return FALSE;
	}
}
function load_convert_to_wiki() {
	$options = get_option('wiki_default');
	
	if ($options['convert_button'] != 'TRUE') return;

	if ( is_admin() ) {
		$convertowiki = new UserPress_convert_to_wiki();
	}
}

add_action('admin_init','load_convert_to_wiki');

/** 
 * The Class.
 */
class UserPress_convert_to_wiki {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'wp_ajax_change_to_wiki', array($this, 'change_to_wiki_callback') );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action('admin_head', array($this, 'my_action_javascript') );
		
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            $post_types = array('post', 'page');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
		add_meta_box(
			'convert_to_wiki_box'
			,__( 'Convert Page to Wiki', 'wiki' )
			,array( $this, 'render_meta_box_content' )
			,$post_type
			,'side'
			,'default'
		);
            }
	}

	public function render_meta_box_content( $post ) {
	
		echo '<button type = "button" id = "changetowiki" class="button button-primary button-large">';
		if (get_post_meta($post->ID, '_has_wiki_features', true ) == 1) {
							echo 'Remove Wiki Features';
						} else {
							echo 'Convert to Wiki';
						}
		
		echo'</button>';
		//echo '<a href="#ajaxthing" class="myajax">Test</a>';
		
	}

	
	
	
	function my_action_javascript() {
		global $post;
		?>
		<script type="text/javascript" >
		jQuery(document).ready(function($) {
		
			$('#changetowiki').click(function(){
				data = {
					action: "change_to_wiki",
					postid: "<?php echo $post->ID; ?>",
					poststatus: <?php
						if (get_post_meta($post->ID, '_has_wiki_features', true ) == 1) {
							echo '0';
						} else {
							echo '1';
						}
						?>
				};
				jQuery.ajax({
					url: ajaxurl,
					data: data,
					dataType: 'json',
        			type: 'post',
					success: function(response) {
						console.log(response);
						window.location.reload();
					}
				});
		
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				
			});
		
		
		});
		</script>
		<?php
	}
	
	function change_to_wiki_callback() {
		global $wpdb; // this is how you get access to the database
	
		$postid = $_POST['postid'];
		$ID = json_decode($postid);
		
		$status = $_POST['poststatus'];
		$TF = json_decode($status);
		
		update_post_meta( $ID, '_has_wiki_features', $TF);
		//ob_clean();
		
		echo $status;
	
		exit(); // this is required to return a proper result & exit is faster than die();
	}	

}
