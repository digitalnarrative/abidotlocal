<?php



/*The function below determines the slug of URIs. 
This is useful for 404 error pages and especially useful for allow users 
to quickly create new pages for pages that do not exist. 


via http://www.webdesignerdepot.com/2013/02/how-to-build-effective-404-error-pages-in-wordpress/

This is separate from the UserPress Wiki since it might be expanded into a standalone plugin.

*/
$options = get_option('wiki_default');

if ($options['override_404'] == 'TRUE') 
	add_filter('template_redirect', 'up546E_userpress_404_override' );

function up546E_userpress_404_override() {
    global $wp_query;

    if (is_404() && $wp_query->query['post_type'] == 'userpress_wiki') {
        status_header( 404 );
        $wp_query->is_404=false;
        $wp_query->is_page=true;
        $wp_query->found_posts=1;
        $wp_query->post_count=1;
        
        add_filter( 'the_title', 'up546E_userpress_404_title' );     
        add_filter( 'the_content', 'up546E_userpress_404_content' );        
       
    }
    
}

function up546E_userpress_slug(){
  global $wp;
  $q = $wp->request;
  $q = preg_replace("/(\.*)(html|htm|php|asp|aspx)$/","",$q);
  $parts = explode('/', $q);
  $q = end($parts);
  return $q;
}


function up546E_userpress_404_posts($posts){
  if(empty($posts))
  return '';
  $list = array();
  foreach($posts as $cpost) {
    $title = get_the_title($cpost);
    $url = get_permalink($cpost);
    $list[] = "<li><a href='{$url}'>{$title}</a></li>"; 
  }
  return implode('', $list);
}
function up546E_userpress_404_title($title) {
	global $up546E_show_title_button_once;
	global $up546E_404_title_once;
	if (in_the_loop() && is_main_query() && $up546E_404_title_once != 1) {
		$up546E_show_title_button_once = TRUE;
		$up546E_404_title_once = 1;
		return 'Error (404)';
		
	}
		
	return $title;
}


function up546E_userpress_404_content($content) {
	global $blog_id, $wp_query, $wiki, $post, $current_user;
	
	ob_start(); ?>
	<p>The wiki page you requested could not be found (or it doesn't exist). But you can go ahead and create a new page titled <a href="<?php echo get_post_type_archive_link( 'userpress_wiki' ); ?>?action=create&wtitle=<?php echo up546E_userpress_slug(); ?>"><?php echo urldecode(up546E_userpress_slug()); ?></a>.</p>

	<!-- Did you mean -->
	<?php 
	$q = up546E_userpress_slug();
	$args = array(
	  'post_type' => 'any',
	  'post_status' => 'publish',
	  'name' => $q,
	  'posts_per_page' => 5
	);
	$query = new WP_Query($args); //query posts by slug
	if(empty($query->posts)){ //search for posts
	  $q = str_replace('-', ' ', $q);
	  $args = array(
		'post_type' => 'any',
		'post_status' => 'publish',
		's' => $q,
		'posts_per_page' => 5
	  );
	  $query->query($args); 
	}
	if(!empty($query->posts)):
	  ?>
	  <h5>Possible matches:</h5>
	  <ul class="posts-list">
	  <?php echo up546E_userpress_404_posts($query->posts);?>
	  </ul>
	<?php endif;?>
	<?php
	
	$content = ob_get_clean();
	
	return $content;
}




?>