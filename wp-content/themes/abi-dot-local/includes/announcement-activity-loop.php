<?php do_action( 'bp_before_directory_activity' ); ?>

<div id="buddypress">

	<?php do_action( 'bp_before_directory_activity_content' ); ?>

	<?php
	global $wpdb,$bp,$groups_template;
	$loggedin_user_id = bp_loggedin_user_id();
	$group_admins = $groups_template->group->admins; // bp_group_is_admin is not working
	$group_admin_arr = array();
	
	foreach ( $group_admins as $group_admin ) {
		$group_admin_arr[] = $group_admin->user_id;
	}
	?>
	
	<?php if ( is_user_logged_in() && ( in_array( $loggedin_user_id, $group_admin_arr ) || $loggedin_user_id == bp_group_is_mod()  ) ) : ?>

		<?php bp_get_template_part( 'includes/announcements-form' ); ?>

	<?php endif; ?>


	<?php do_action( 'bp_before_directory_activity_list' ); ?>

	<div class="activity" role="main">

		<?php bp_get_template_part( 'activity/activity-loop' ); ?>

	</div><!-- .activity -->

	<?php do_action( 'bp_after_directory_activity_list' ); ?>

	<?php do_action( 'bp_directory_activity_content' ); ?>

	<?php do_action( 'bp_after_directory_activity_content' ); ?>

	<?php do_action( 'bp_after_directory_activity' ); ?>

</div>
