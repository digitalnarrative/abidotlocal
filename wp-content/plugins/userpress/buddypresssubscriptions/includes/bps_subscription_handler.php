<?php
/**
 * BuddyPress Subscription Handler
 *
 * The functions that handles adding, deleting, and checking subscription status
 * Manages adds and deletes via AJAX.
 *
 * @package Buddy Press Subscriptions
 */
 
//CHECK USER SUBSCRIPTION STATUS
function up546E_bps_isusersubed($post_id = "", $user_id = '', $blog_id = '') {
	$user_id = ($user_id ? $user_id : get_current_user_id());
	$post_id = ($post_id ? $post_id : get_the_ID());
	$blog_id = ($blog_id ? $blog_id : get_current_blog_id());
	
	global $wpdb;
	$table_name = $wpdb->base_prefix . "bps_post_subscriptions";
	$qry = 'SELECT * FROM '.$table_name.' WHERE post_id = "'.$post_id.'" AND user_id = "'.$user_id.'" AND blog_id = "'.$blog_id.'"';
	$results = $wpdb->get_results( $qry );

	if ($results) {
    	return true;
	} else {
		return false;
	}

	
};



//SUBSCRIBE USER TO POST/PAGE
function up546E_bps_sub_user_to_page($post_id, $user_id, $blog_id) {
	$result .= 'subsribe user function called | ';
	
	$date = current_time( 'mysql' );

	global $wpdb;
	$table_name = $wpdb->base_prefix . "bps_post_subscriptions";
		
	$rows_affected = $wpdb->insert( 
				$table_name, 
				array( 
					'post_id' => $post_id, 
					'user_id' => $user_id,
					'blog_id' => $blog_id,
					'time' => $date
					)
				);
	
	return $result;
}

//UNSUBSCRIBE USER FROM POST/PAGE

function up546E_bps_unsub_user_to_page($post_id, $user_id, $blog_id) {
	$result .= 'unsubsribe user function called | ';

	global $wpdb;
	$table_name = $wpdb->base_prefix . "bps_post_subscriptions";
		
	$rows_affected = $wpdb->delete( 
				$table_name, 
				array( 
					'post_id' => $post_id, 
					'user_id' => $user_id,
					'blog_id' => $blog_id
					)
				);
	
	return $result;
}

//AJAX HANDLE SUB/UNSUB

add_action("wp_ajax_bps_ajax_subscription_handler", "up546E_bps_ajax_subscription_handler");
add_action("wp_ajax_nopriv_bps_must_login", "up546E_bps_must_login");

function up546E_bps_ajax_subscription_handler() {
	global $wpdb;
	if ( !wp_verify_nonce(  $_POST['nonce'], "bps_subchange_nonce")) {
		exit("Subscription change request from unverified source");
	}   
	$result = 'sub handler called | ';
	if ($_POST['bps_sub_status'] == 'sub') {
		$result .= 'post id: '.$_POST['post_id'].' user id: '.$_POST['user_id'].' | ';
		$result .= up546E_bps_sub_user_to_page( $_POST['post_id'],  $_POST['user_id'], $_POST['blog_id']);
		$result .= "success";
	} elseif ($_POST['bps_sub_status'] == 'unsub') {
		$result .= 'post id: '.$_POST['post_id'].' user id: '.$_POST['user_id'].' | ';
		$result .= up546E_bps_unsub_user_to_page( $_POST['post_id'],  $_POST['user_id'], $_POST['blog_id']);
		$result .= "success";
	} else {
		
	}
	print_r($_POST);	
	echo $result;

	die();
}

function up546E_bps_must_login() {
   echo "You must be logged in to subscribe";
   die();
}

function up546E_bps_get_user_pages($user_id = "") {
	$user_id = ($user_id ?: get_current_user_id());
	global $wpdb;
	
	$table_name = $wpdb->base_prefix . "bps_post_subscriptions";
	$qry = 'SELECT * FROM '.$table_name.' WHERE user_id = "'.$user_id.'" ORDER BY time DESC';
	$results = $wpdb->get_results( $qry );
	
	return $results;
}
?>