<?php
/**
 * BuddyPress Subscriptions What's New page Template
 *
 * The 'What's New' page template in the BPS Subscriptions menu
 *
 * @package Buddy Press Subscriptions
 */


if (!isset($_GET['start'])){
	$start = 0;
} else {
	$start = $_GET['start'];
}
if($start < 0){
	header("Location: index.php?start=0");
}
$newestids = array();
$results = up546E_bps_get_sub_database_results($start);
$altthumb = plugins_url( '../images/nothumb.png' , __FILE__ );
$totalcount = up546E_bps_get_sub_database_count();
//print_r($results);
if (!$results) {
	echo 'No Updates Available';
} else {
	?>
<div id="item-body" role="main">
<ul id="activity-stream" class="activity-list item-list">

	<?php
	foreach ($results as $result) {
		switch_to_blog($result->blog_id);
		$id = ($result->parrent_id == 0 ? $result->post_id : $result->parrent_id);
		
		$userID = $result->user_id;
		
		$thumb = get_avatar( $userID, 50);
		
		$user_info = get_userdata($userID);
		if ($user_info) {
			$username = $user_info->user_login;
			$userlink = bp_core_get_userlink( $userID );
			} else {
			$username = 'Someone';
			$userlink = '<a href="#">'.$username.'</a>';
			}
		
		$userlinkurl = bp_core_get_userlink( $userID, false, true );
		
		$title = $result->post_title;
		$title = ($title ? : get_the_title($id));
		
		if ($result->parrent_id == 0 && $result->src != 'comment') {
			$linkurl = get_permalink($result->post_id);
		} elseif ($result->parrent_id != 0 && $result->src != 'comment') {
			$linkurl = get_permalink($result->parrent_id);
		} elseif ($result->src == 'comment') {
			$linkurl = get_permalink($result->post_id).'#comment-'.$result->comment_id;
		}
		
		$date = $result->postdate;
		$nicedate = up546E_bps_time_elapsed($date);
		
		$comment = $result->comment;
		$comment = ($comment ? : get_post_meta( $result->post_id, 'userpress_wiki_revision_note', true ) );
		$comment = ($comment ? substr($comment,0,80) : '' );
		
		$info = $userlink;
		$info .= ($result->src == 'comment' ? ' added a comment to ' : ' edited ');
		$info .= '"<a href = "'.$linkurl.'">'.$title.'</a>" ';
		$info .= $nicedate;
		if (is_multisite()) {$info .= '<br><span class = "bsp_blog_name">'.get_bloginfo('name').'</span></br>';}
		
		$info .= ($comment ? '<p class = "comment">'.$comment.'</p>' : '' );
	
	?>
		<li class="bbpress bbp_topic_create activity-item">
		<div class="activity-avatar"><a href="<?php echo $userlinkurl; ?>"><?php echo $thumb; ?></a>
		</div>
		<div class="activity-content">
		<div class="activity-header">
		<p><?php echo $info; ?></p>
		</div><!--END ACTIVITY HEADER-->
		</div><!--END ACTIVITY CONTENT-->
		</li>

		<?php
	restore_current_blog();
	};
	?>
</ul>		
</div><!--END ITEM BODY-->
	<?php
		
		if ($start >= 1) { ?>
			<a id = "bps_prev_link" href="?start=<?php echo $start - 20; ?>"><< Previous </a>
		<?php }
		
		if ($totalcount > ($start + 20)) { ?>
			<a id = "bps_next_link" href="?start=<?php echo $start + 20; ?>">Next >></a>
		<?php } 
	
}?>

		