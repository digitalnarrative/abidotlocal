<?php

add_filter( 'the_content', 'up546E_table_of_contents_content_filter', 100, 100 );

add_action( 'wp_enqueue_scripts', 'up546E_toc_enqueue_scripts' );

function up546E_toc_enqueue_scripts() {
	wp_register_style( 'userpress_toc_style', plugins_url('toc/tocstyle.css', dirname ( __FILE__ )));
	wp_enqueue_style( 'userpress_toc_style' );
}

function up546E_table_of_contents_content_filter($content = NULL) {
	
	if ($content == NULL) return $content;
	
	global $mytoc, $mytoccount;
	$mytoc = array();
	$mytoccount = 0;

	$new_content = preg_replace_callback(
   		'#<h(\d)[^>]*?>(.*?)<[^>]*?/h\d>#i',
		function ($matches) {
			global $mytoccount, $mytoc;
			$mytoc[$mytoccount]['level'] = $matches[1];
			$mytoc[$mytoccount]['name'] = strip_tags($matches[2]);
			$output = '<a name="target-toc-'.$mytoccount.'"></a>'.$matches[0];
			$mytoccount++;
			return $output;
			
		},
   	 	$content
	);
	return $new_content;

}

function up546E_build_subnav_toc($echo = TRUE, $widget = FALSE) {

	global $mytoc;
	if (!isset($mytoc)) {
		global $post;
		up546E_table_of_contents_content_filter($post->post_content);
	}
	if (isset($mytoc) && is_array($mytoc) && !empty($mytoc)) {

		if (count($mytoc) < 2) return;
		if ($widget) {
			$output = '<div class = "userpress_wiki_toc widget">';
			$output .= '<h5 class = "widget_title">Contents</h5>';
		} else {
			$output = '<div class = "userpress_wiki_toc">';
			$output .= '<h4>Contents</h4>';
		}
		$output .= '<ol id="userpress_toc">';
		$close = '';
		$lastlevel = 1;
		foreach ($mytoc as $id => $item) {
			$thislevel = $item['level'];
			if ($thislevel != $lastlevel) { 
				$offset = $thislevel - $lastlevel;
				if ($offset > 0) {
					$output .= str_repeat('<ol>', abs($offset));
				} elseif ($offset < 0) {
					$close = str_repeat('</ol>', abs($offset)).$close;
					$output .= $close;
					$close = '';
				}
			}
			
			$output .= '<li><a href = "#target-toc-'.$id.'">'.$item['name'].'</a>';
			$close = '</li>'.$close;
			$lastlevel = $thislevel;
		}
		$close = '</ol>'.$close;
		$output .= $close;
		$output .= '</div>';
	} 
	
	if (isset($output)) {
		if ($echo !== TRUE)
			return $output;
		echo $output;
	}
}


// WIDGET

wp_register_sidebar_widget(
		'userpress_wiki_toc',        // your unique widget id
		'Table of Contents',          // widget name
		'up546E_userpresstoc_func',  // callback function
		array(                  // options
			'description' => 'Automatically generated table of contents based on post/page headings.'
		)
	);
	
	
// SHORT CODE

function up546E_userpresstoc_func( $atts ){
	if (!is_singular() || is_search() || is_feed())
		return;
	up546E_build_subnav_toc(TRUE,TRUE);

}
add_shortcode( 'userpresstoc', 'up546E_userpresstoc_func' );	