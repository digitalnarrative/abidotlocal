<?php 




/**
 * 
 * Alters the UserPress archive loop
 * @uses pre_get_posts hook
*/

function userpress_archive_filter( $query ) {
	
// Recently Created
	if (isset($_GET["view"]) && $_GET["view"] == 'created') {

// Recently Updated
	} elseif (isset($_GET["view"]) && $_GET["view"] == 'recently_modified') {
        $query->set( 'orderby', 'modified' );
        
// Recently Discussed
	} elseif (isset($_GET["view"]) && $_GET["view"] == 'recently_discussed') { 
        $query->set( 'orderby_last_comment', 'true' );

// Most Discussed    
	} elseif (isset($_GET["view"]) && $_GET["view"] == 'most_discussed') { 
        $query->set( 'orderby', 'comment_count' );
        $query->set( 'order', 'DESC' );       

// Alphabetical Order
	} elseif (isset($_GET["view"]) && $_GET["view"] == 'alpha') { 
        $query->set( 'orderby', 'title' );
        $query->set( 'order', 'ASC' ); 
        
// Create New Wiki -- Yes. This is an ugly hack. But it works.
	} elseif (isset($_GET["action"]) && $_GET["action"] == 'create') {
		if ($query->is_main_query()) $query->set( 'posts_per_page', 1 );
	}
	
}


add_action( 'pre_get_posts', 'userpress_archive_filter' );

function add_userpress_archive_rss_link() {
	if (isset($_GET["view"])) {
		$view = $_GET["view"];
		$text = str_replace("_"," ",$view);
		$text = ucwords($text);
		$options = get_option('wiki_default');
		echo '<link rel="alternate" type="application/rss+xml" title="Userpress '.$text.' Feed" href="'.home_url().'/feed/wikifeed/?view='.$view.'"/><br/>';
	}
}

add_action('wp_head', 'add_userpress_archive_rss_link');


// Create New Wiki


if 	(isset($_GET["action"]) && $_GET["action"] == 'create') {

	add_action( 'init', 'up546E_cannot_create');

	function up546E_cannot_create() {
		if (!up546E_user_can_publish()) {
			header( 'Location: '.get_post_type_archive_link('userpress_wiki'));
			exit();
		}
	}

	/*add_action( 'template_redirect', 'upw_create_new_wiki' );
		function upw_create_new_wiki()
		{
   			 include( get_template_directory() . '/page.php' );
   			 exit();
		}
	*/

	add_filter( 'the_content', 'upw_insert_wiki_form' );
		function upw_insert_wiki_form($content)
		{
		global $blog_id, $wp_query, $wiki, $post, $current_user;
		$content = $wiki->get_new_wiki_form();
		return $content;
		}	
		
	add_filter( 'the_title', 'upw_new_wiki_title', 10, 2 );
		function upw_new_wiki_title($title, $id)
		{
		global $title_CNW_count;
		if ($id == get_the_id() && $title_CNW_count != 1) {
			$title = "Create New Wiki";
			$title_CNW_count = 1;
		}
		return $title;
		}	
	add_action( 'the_post', 'upw_new_wiki_post_intercept');
		function upw_new_wiki_post_intercept($post) {
			$post->ID = '-1';
			//print_r($post);
		}
}

if (isset($_GET["action"]) && ($_GET["action"] == 'edit' || $_GET["action"] == 'create')) {
	
	add_filter( 'the_content', 'up546E_remove_all_shortcodes_from_content', 0 );
	add_filter( 'the_content', 'up546E_reactivate_all_shortcodes', 99 );


}

function up546E_remove_all_shortcodes_from_content( $content ) {

	global $shortcode_tags, $remember_shortcode_tags;
	
	$remember_shortcode_tags = $shortcode_tags;

	/* Loop through the shortcodes and remove them. */
	foreach ( $shortcode_tags as $shortcode_tag => $function)
		remove_shortcode( $shortcode_tag );

	/* Return the post content. */
	return $content;
}


function up546E_reactivate_all_shortcodes( $content ) {

	global $remember_shortcode_tags;
	
	foreach ( $remember_shortcode_tags as $shortcode_tag => $function)
		add_shortcode( $shortcode_tag, $function );


	return $content;
}

class userpress_rss_feeds {

  
	public $feed = 'wikifeed';
 
	public function __construct() {
	
		add_action( 'init', array( $this, 'init' ) );
		
	}
  
	public function init() {
	
		// feed name to access in URL eg. /feed/custom-xml/
		add_feed( $this->feed, array( $this, 'getfeed' ) );
		if ( ! isset( $rules['(.?.+?)/(feed|rdf|rss|rss2|atom|wikifeed|subs)/?$'] ) ) {
			global $wp_rewrite;
	   		$wp_rewrite->flush_rules();
		}
			
	}
  	
  	
	public function getfeed() {
	
		global $wp_query;
		
		$args = array(
			'post_type' => 'userpress_wiki',
			'orderby'=> 'created', 
		);
		// Recently Created
		if (isset($_GET["view"]) && $_GET["view"] == 'created') {
	
	// Recently Updated
		} elseif (isset($_GET["view"]) && $_GET["view"] == 'recently_modified') {
			$args['orderby'] = 'modified';
			
	// Recently Discussed
		} elseif (isset($_GET["view"]) && $_GET["view"] == 'recently_discussed') { 
			$args['orderby_last_comment'] = 'true';
			unset($args['orderby']);
	
	// Most Discussed    
		} elseif (isset($_GET["view"]) && $_GET["view"] == 'most_discussed') { 
			$args['orderby'] = 'comment_count';
			$args['order'] = 'DESC';      
	
	// Alphabetical Order
		} elseif (isset($_GET["view"]) && $_GET["view"] == 'alpha') { 
			$args['orderby'] = 'title';
			$args['order'] = 'ASC'; 
		}
		//print_r($args);
		query_posts($args);
		
		if (isset($_GET["view"])) {
			$view = $_GET["view"];
			$text = str_replace("_"," ",$view);
			$text = ucwords($text);
		}
		
		
		// either output template & loop here or include a template
		
		/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

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
	<title>Userpress <?php echo $text; ?> Feed</title>
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

	while( have_posts()) : the_post();
	?>
	<item>
		<title><?php the_title_rss() ?></title>
		<link><?php the_permalink_rss() ?></link>
		<comments><?php comments_link_feed(); ?></comments>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
		<dc:creator><![CDATA[<?php the_author() ?>]]></dc:creator>
		<?php the_category_rss('rss2') ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
<?php if (get_option('rss_use_excerpt')) : ?>
		<description><![CDATA[<?php the_excerpt(); ?>]]></description>
<?php else : ?>
		<description><![CDATA[<?php the_excerpt(); ?>]]></description>
	<?php $content = get_the_content(); ?>
	<?php if ( strlen( $content ) > 0 ) : ?>
		<content:encoded><![CDATA[<?php echo $content; ?>]]></content:encoded>
	<?php else : ?>
		<content:encoded><![CDATA[<?php the_excerpt_rss(); ?>]]></content:encoded>
	<?php endif; ?>
<?php endif; ?>
		<wfw:commentRss><?php echo esc_url( get_post_comments_feed_link(null, 'rss2') ); ?></wfw:commentRss>
		<slash:comments><?php echo get_comments_number(); ?></slash:comments>
<?php rss_enclosure(); ?>
	<?php
	/**
	 * Fires at the end of each RSS2 feed item.
	 *
	 * @since 2.0.0
	 */
	do_action( 'rss2_item' );
	?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
		<?php
	}
  
}

	$userpress_rss_feeds = new userpress_rss_feeds();
?>