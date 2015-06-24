<?php




/* INCLUDE SCRIPTS */


function up546E_bps_scripts() {
	wp_enqueue_style( 'bps_button_style', plugins_url( '/css/bps_buttonstyle.css' , __FILE__ ) );
	wp_enqueue_style( 'bps_manage_subs_page_stlye', plugins_url( '/css/bps_manage_subs_page_style.css' , __FILE__ ) );
	wp_enqueue_style( 'bps_whats_new_page_stlye', plugins_url( '/css/bps_whats_new_page_style.css' , __FILE__ ) );


	wp_register_script( "bps_button_script", plugins_url( '/js/bps_button.js' , __FILE__ ), array('jquery') );
	wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );       
	wp_enqueue_script( 'bps_button_script' );
}

add_action( 'wp_enqueue_scripts', 'up546E_bps_scripts' );

// INCLUDE SUB FILES 

function up546E_bps_include_files() {
	include dirname(__FILE__).'/includes/bps_subscribe_button.php';
	include dirname(__FILE__).'/includes/bps_query_functions.php';
	include dirname(__FILE__).'/includes/bps_subscription_handler.php';
	include dirname(__FILE__).'/includes/bps_revision_postmeta.php';
	include dirname(__FILE__).'/includes/bps_functions.php';
	
	add_feed( 'subs', 'up546E_bps_menu_subscription_feed' );
	
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['(.?.+?)/(feed|rdf|rss|rss2|atom|wikifeed|subs)/?$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
	
}

add_action( 'init', 'up546E_bps_include_files');
/*
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
*/

// ADD MENU ITEMS TO BUDDY PRESS

function up546E_bps_add_nav_items() {
	global $bp;
	bp_core_new_nav_item( 
		array( 
			'name' => __('Subscriptions', 'buddypress'), 
			'slug' => 'subscriptions', 
			'position' => 60, 
			'show_for_displayed_user' => false, 
			'screen_function' => 'up546E_bps_menu_subscription', 
			'default_subnav_slug' => 'whats-new', 
		));
		
	bp_core_new_subnav_item( array( 
        'name' => __('What\'s New', 'buddypress'),
        'slug' => 'whats-new',
        'parent_url' => $bp->loggedin_user->domain . 'subscriptions/',
        'parent_slug' => 'subscriptions',
        'screen_function' => 'up546E_bps_menu_subscription_whatsnew',
        'position' => 10,
    ) );
    
    bp_core_new_subnav_item( array( 
        'name' => __('Manage Subscriptions', 'buddypress'),
        'slug' => 'manage-subs',
        'parent_url' => $bp->loggedin_user->domain . 'subscriptions/',
        'parent_slug' => 'subscriptions',
        'screen_function' => 'up546E_bps_menu_subscription_mansubs',
        'position' => 20,
    ) );
}

add_action( 'bp_setup_nav','up546E_bps_add_nav_items');


function up546E_bps_menu_subscription_whatsnew() {

	add_action( 'bp_template_content', 'up546E_bps_menu_subscription_whatsnew_show' );
	add_action('wp_head', 'add_bps_rss_link');
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function up546E_bps_menu_subscription_whatsnew_show() {
	include dirname(__FILE__).'/includes/bps_whats_new_page.php';

}

function up546E_bps_menu_subscription_mansubs() {

	add_action( 'bp_template_content', 'up546E_bps_menu_subscription_mansubs_show' );
	add_action('wp_head', 'add_bps_rss_link');
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function up546E_bps_menu_subscription_mansubs_show() {
	include dirname(__FILE__).'/includes/bps_manage_subs_page.php';
	
}

function add_bps_rss_link() {
		echo '<link rel="alternate" type="application/rss+xml" title="Buddypress Subscriptions Feed" href="'.home_url().'/feed/subs/?user='.get_current_user_id().'"/><br/>';
}



function up546E_bps_menu_subscription_feed() {
	if (isset($_GET["user"]) && get_user_by( 'id', $_GET["user"] )) {
	
	$results = up546E_bps_get_sub_database_results(0, $_GET["user"]);
		//echo '<pre>';
		//print_r($results);

		header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
		$more = 1;
		
		echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
		
		<rss version="2.0"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:atom="http://www.w3.org/2005/Atom"
			xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
			xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
			<?php
			/**
			 * Fires at the end of the RSS root to add namespaces.
			 *
			 * @since 2.0.0
			 */
			do_action( 'rss2_ns' );
			?>
		>
		
		<channel>
			<title>BuddyPress Subscriptions Feed</title>
			<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
			<link><?php bloginfo_rss('url') ?></link>
			<description><?php bloginfo_rss("description") ?></description>
			<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
			<language><?php bloginfo_rss( 'language' ); ?></language>
			<?php
			$duration = 'hourly';
			/**
			 * Filter how often to update the RSS feed.
			 *
			 * @since 2.1.0
			 *
			 * @param string $duration The update period.
			 *                         Default 'hourly'. Accepts 'hourly', 'daily', 'weekly', 'monthly', 'yearly'.
			 */
			?>
			<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', $duration ); ?></sy:updatePeriod>
			<?php
			$frequency = '1';
			/**
			 * Filter the RSS update frequency.
			 *
			 * @since 2.1.0
			 *
			 * @param string $frequency An integer passed as a string representing the frequency
			 *                          of RSS updates within the update period. Default '1'.
			 */
			?>
			<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', $frequency ); ?></sy:updateFrequency>
			<?php
			/**
			 * Fires at the end of the RSS2 Feed Header.
			 *
			 * @since 2.0.0
			 */
			do_action( 'rss2_head');
		
			foreach ($results as $result) :
			?>
			<item>
				<title><?php $title = $result->post_title;
							 $title = ($title ? : get_the_title($result->post_id)); 
							echo $title;
						?></title>
				<link><?php if ($result->parrent_id == 0 && $result->src != 'comment') {
								$linkurl = get_permalink($result->post_id);
							} elseif ($result->parrent_id != 0 && $result->src != 'comment') {
								$linkurl = get_permalink($result->parrent_id);
							} elseif ($result->src == 'comment') {
								$linkurl = get_permalink($result->post_id).'#comment-'.$result->comment_id;
							}
							echo $linkurl;
							?>
				</link>
				<pubDate><?php echo $result->postdate; ?></pubDate>
				<dc:creator><?php
				$user = get_userdata( $result->user_id );
				echo $user->user_login;		
				?></dc:creator>
				<content:encoded><![CDATA[
					<?php
					$userID = $result->user_id;
					$user_info = get_userdata($userID);
					$username = $user_info->user_login;
					$userlink = bp_core_get_userlink( $userID );
					$userlinkurl = bp_core_get_userlink( $userID, false, true );
					
					$comment = $result->comment;
					$comment = ($comment ? : get_post_meta( $result->post_id, 'userpress_wiki_revision_note', true ) );
					$comment = ($comment ? substr($comment,0,80) : '' );
			
					$info = $userlink;
					$info .= ($result->src == 'comment' ? ' added a comment to ' : ' edited ');
					$info .= '"<a href = "'.$linkurl.'">'.$title.'</a>" ';
					$info .= $nicedate;
					if (is_multisite()) {$info .= '<br><span class = "bsp_blog_name">'.get_bloginfo('name').'</span></br>';}
					
					$info .= ($comment ? '<p class = "comment">'.$comment.'</p>' : '' );
					
					echo $info;
					?>
				]]></content:encoded>
				
			<?php
			/**
			 * Fires at the end of each RSS2 feed item.
			 *
			 * @since 2.0.0
			 */
			do_action( 'rss2_item' );
			?>
			</item>
			<?php endforeach; ?>
		</channel>
		</rss>

<?php
	}
}

?>