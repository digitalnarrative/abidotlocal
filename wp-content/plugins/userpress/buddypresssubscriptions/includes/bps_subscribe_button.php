<?php
/**
 * BuddyPress Subscription Button
 *
 * The functions that make up the code for the button, and activating it
 * as both a widget and a [bpsbutton] shortcode
 *
 * @package BuddyPress Subscriptions
 */

// WIDGET CODE FOR BUTTON

wp_register_sidebar_widget(
		'bps_subscribe_button',        // your unique widget id
		'BuddyPress Subscribe Button',          // widget name
		'up546E_bpsubbutton_func',  // callback function
		array(                  // options
			'description' => 'Dynamic Button to subscribe to posts and pages'
		)
	);


// SHORT CODE FOR BUTTON 

function up546E_bpsubbutton_func( $atts ){
	echo up546E_bppostsubbutton();
}
add_shortcode( 'bpsubbutton', 'up546E_bpsubbutton_func' );

// THE BUTTON!

function up546E_bppostsubbutton($postID = "", $blog_id = "", $mode = "") {
	$button = '';
	if ( is_user_logged_in() ) {
		if ($mode == "delete") {
			$user_id = get_current_user_id();
			$nonce = wp_create_nonce("bps_subchange_nonce");
			
			$button = '<div id = "bpsbuttoncontainer_' . $postID . '" class = "bpsbuttoncontainer"><div id="bpsubbutton_'.$postID.'" class="bpsbutton bpsubbutton_'.$postID.' bpsubbutton delete" data-nonce="' . $nonce . '" data-user_id="'.$user_id.'" data-post_id="' . $postID . '" data-blog_id="' . $blog_id . '" data-sub-status="">Delete</div>';
			$button .= '<div id = "bpsauscontainer_' . $postID . '"  class = "bpsauscontainer"><div id="bpsausbuttonbox">Are You Sure? <div id="bpsunsubyes_'.$postID.'" class="bpsausbutton bpsbutton bpsunsub bpsunsubyes"data-nonce="' . $nonce . '" data-user_id="'.$user_id.'" data-post_id="' . $postID . '" data-blog_id="' . $blog_id . '" data-sub-status="unsub">Yes</div><div id="bpsunsubno_'.$postID.'" class="bpsausbutton bpsbutton bpsunsub bpsunsubno">Cancel</div></div></div></div>';	
			
		} else {
			$user_id = get_current_user_id();
			$postID = ($postID ?: get_the_ID());
			$blog_id = ($blog_id ?: get_current_blog_id());
			if ($postID != '' || $postID != '0') {
				$nonce = wp_create_nonce("bps_subchange_nonce");
				$bpsubstatus = up546E_bps_isusersubed($postID,$user_id,$blog_id);
	
				$button = '<div id = "bpsbuttoncontainer_' . $postID . '" class = "bpsbuttoncontainer"><div id="bpsubbutton_'.$postID.'" class="bpsbutton bpsubbutton_'.$postID.' bpsubbutton '.($bpsubstatus ? 'subscribed':'unsubscribed').'" data-nonce="' . $nonce . '" data-user_id="'.$user_id.'" data-post_id="' . $postID . '" data-blog_id="' . $blog_id . '" data-sub-status="'.($bpsubstatus == FALSE ? 'sub':'').'">'.($bpsubstatus == TRUE ? 'Subscribed':'Subscribe').'</div>';
				$button .= '<div id = "bpsauscontainer_' . $postID . '" class = "bpsauscontainer"><div class="bpsausbuttonbox">Are You Sure? <div id="bpsunsubyes_'.$postID.'" class="bpsausbutton bpsbutton bpsunsub bpsunsubyes"data-nonce="' . $nonce . '" data-user_id="'.$user_id.'" data-post_id="' . $postID . '" data-blog_id="' . $blog_id . '" data-sub-status="unsub">Yes</div><div id="bpsunsubno_'.$postID.'" class="bpsausbutton bpsbutton bpsunsub bpsunsubno">Cancel</div></div></div></div>';	
		
			}
		}
		return $button;
	}
}
