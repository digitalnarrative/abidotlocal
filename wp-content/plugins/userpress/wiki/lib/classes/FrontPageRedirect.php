<?php add_action( 'template_redirect', 'userpress_frontpage' );

function userpress_frontpage(){
	$view = (!isset($_GET['view'])) ? FALSE : TRUE;
	$action = (!isset($_GET['action'])) ? FALSE : TRUE;
	if ( 'userpress_wiki' == get_post_type() 
		AND !is_tax() 
		AND !is_search() 
		AND !is_singular( 'userpress_wiki' ) 
		AND !is_feed()
		AND $view == FALSE
		AND $action == FALSE
	)
	{ 
		wp_redirect(up546E_get_frontpage_uri()); 
		exit; 
	}
}
?>