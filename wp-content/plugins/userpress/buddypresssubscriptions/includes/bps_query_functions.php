<?php
/**
 * BuddyPress Subscription Database Queries
 *
 * Database queries for BPS Plugin
 *
 * @package Buddy Press Subscriptions
 */
 
function up546E_bps_get_sub_database_results($start = '0', $userID = NULL) {
	if (isset($userID)) {
		$user_id = $userID;
	} else {
		$user_id = get_current_user_id();
	}
	
	global $wpdb;
	$usersubs = up546E_bps_get_user_pages($user_id);
	
	$n = 0;
	$query = '';
	foreach ($usersubs as $thissub) {
		
		$query .= ($n != 1 ? '' : ' UNION ');
		switch_to_blog($thissub->blog_id);
		$query .= "
		SELECT ".$wpdb->prefix."posts.id AS post_id, NULL as comment_id, ".$wpdb->prefix."posts.post_parent AS parrent_id, ".$wpdb->prefix."posts.guid as link, ".$wpdb->prefix."posts.post_title, ".$wpdb->prefix."posts.post_date_gmt AS postdate, ".$wpdb->prefix."posts.post_author AS user_id, NULL AS comment , ".$wpdb->prefix."posts.post_type AS src, '".$thissub->blog_id."' AS blog_id
		FROM ".$wpdb->prefix."posts, ".$wpdb->base_prefix."bps_post_subscriptions
		WHERE ".$wpdb->prefix."posts.id = ".$wpdb->base_prefix."bps_post_subscriptions.post_id AND ".$wpdb->prefix."posts.post_date_gmt > ".$wpdb->base_prefix."bps_post_subscriptions.time AND ".$wpdb->base_prefix."bps_post_subscriptions.user_id = ".$user_id." AND ".$wpdb->base_prefix."bps_post_subscriptions.blog_id = ".$thissub->blog_id."
			OR ".$wpdb->prefix."posts.post_parent = ".$wpdb->base_prefix."bps_post_subscriptions.post_id AND ".$wpdb->prefix."posts.post_type = 'revision' AND ".$wpdb->prefix."posts.post_date_gmt > ".$wpdb->base_prefix."bps_post_subscriptions.time AND ".$wpdb->base_prefix."bps_post_subscriptions.user_id = ".$user_id."  AND ".$wpdb->base_prefix."bps_post_subscriptions.blog_id = ".$thissub->blog_id."
		UNION 
		SELECT ".$wpdb->prefix."comments.comment_post_ID AS post_id, ".$wpdb->prefix."comments.comment_ID AS comment_id, Null AS parrent_id, Null as link, NULL AS post_title, ".$wpdb->prefix."comments.comment_date_gmt AS postdate, ".$wpdb->prefix."comments.user_id AS user_id, ".$wpdb->prefix."comments.comment_content AS comment ,  'comment' AS src, '".$thissub->blog_id."' AS blog_id
		FROM ".$wpdb->prefix."comments, ".$wpdb->base_prefix."bps_post_subscriptions
		WHERE ".$wpdb->prefix."comments.comment_post_ID = ".$wpdb->base_prefix."bps_post_subscriptions.post_id AND ".$wpdb->prefix."comments.comment_date_gmt > ".$wpdb->base_prefix."bps_post_subscriptions.time AND ".$wpdb->base_prefix."bps_post_subscriptions.user_id = ".$user_id." AND ".$wpdb->base_prefix."bps_post_subscriptions.blog_id = ".$thissub->blog_id."
		
		";
		$n = 1;
		restore_current_blog();
	}
	
	if (!empty($query)){
		$query .= "
			ORDER BY postdate DESC
			LIMIT ".$start.",20
			";
		
		$gethistory = $wpdb->get_results($query);
	} else {
		return;
	}

	return $gethistory;
}

function up546E_bps_get_sub_database_count($userID = NULL) {
		$user_id = (isset($userID) ? $userID : get_current_user_id());
	global $wpdb;
	$usersubs = up546E_bps_get_user_pages();
	
	$query = '';
	$n = 0;
	foreach ($usersubs as $thissub) {
		$query .= ($n != 1 ? '' : ' UNION ');
		switch_to_blog($thissub->blog_id);
		$query .= "
		SELECT ".$wpdb->prefix."posts.post_date_gmt AS postdate, '".$wpdb->prefix."post' as type
		FROM ".$wpdb->prefix."posts, ".$wpdb->base_prefix."bps_post_subscriptions
		WHERE ".$wpdb->prefix."posts.id = ".$wpdb->base_prefix."bps_post_subscriptions.post_id AND ".$wpdb->prefix."posts.post_date_gmt > ".$wpdb->base_prefix."bps_post_subscriptions.time AND ".$wpdb->base_prefix."bps_post_subscriptions.user_id = ".$user_id." AND ".$wpdb->base_prefix."bps_post_subscriptions.blog_id = '".$thissub->blog_id."'
		OR ".$wpdb->prefix."posts.post_parent = ".$wpdb->base_prefix."bps_post_subscriptions.post_id AND ".$wpdb->prefix."posts.post_type = 'revision' AND ".$wpdb->prefix."posts.post_date_gmt > ".$wpdb->base_prefix."bps_post_subscriptions.time AND ".$wpdb->base_prefix."bps_post_subscriptions.user_id = ".$user_id." AND ".$wpdb->base_prefix."bps_post_subscriptions.blog_id = '".$thissub->blog_id."'
		UNION
		SELECT ".$wpdb->prefix."comments.comment_date_gmt AS postdate, '".$wpdb->prefix."comment' as type
		FROM ".$wpdb->prefix."comments, ".$wpdb->base_prefix."bps_post_subscriptions
		WHERE ".$wpdb->prefix."comments.comment_post_ID = ".$wpdb->base_prefix."bps_post_subscriptions.post_id AND ".$wpdb->prefix."comments.comment_date_gmt > ".$wpdb->base_prefix."bps_post_subscriptions.time AND ".$wpdb->base_prefix."bps_post_subscriptions.user_id = ".$user_id." AND ".$wpdb->base_prefix."bps_post_subscriptions.blog_id = ".$thissub->blog_id."
		
		";
		$n = 1;
		restore_current_blog();
	}
	$gettotals = $wpdb->get_results($query);
	
		$result = 0;
		if ($gettotals) {			
			foreach ($gettotals as $total) {
			$result = $result + 1;
			}
		}
		return $result;
}

?>
