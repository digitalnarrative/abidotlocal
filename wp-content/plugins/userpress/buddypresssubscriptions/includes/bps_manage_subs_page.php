<?php
/**
 * BuddyPress Subscriptions Manage Subscriptions Template
 *
 * The 'Manage Subscriptions' page template in the BPS Subscriptions menu
 *
 * @package Buddy Press Subscriptions
 */

$pages = up546E_bps_get_user_pages();

if (!$pages) {
	echo 'No Subscriptions Found';
} else {
	?>
	<table id="bps_man_subs">
	<?php
	foreach ($pages as $page) {
	switch_to_blog($page->blog_id);
	$thumb = get_the_post_thumbnail($page->post_id, array(50,50));
	$thumb = ($thumb ?: '<img src="'.plugins_url( '../images/nothumb.png' , __FILE__ ).'">');
	?>
	<tr>
		<td class="thumbnail"><a href="<?php echo get_permalink($page->post_id); ?>"><?php echo $thumb; ?></a></td>
		<td class="title"><a href="<?php echo get_permalink($page->post_id); ?>"><?php echo get_the_title($page->post_id); if (is_multisite()) {echo ': '.get_bloginfo('name');} ?></a></td>
		<td class="delete"><?php echo up546E_bppostsubbutton($page->post_id,$page->blog_id,'delete'); ?></td>
	</tr>
	<?php
	restore_current_blog();
	};
	?>
	</table>
	

<?php
}
?>