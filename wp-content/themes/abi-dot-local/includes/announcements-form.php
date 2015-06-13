<?php

/**
 * BuddyPress - Activity Post Form
 */
?>

<form action="<?php bp_activity_post_form_action(); ?>" method="post" id="custom-announcements-form" name="custom-announcements-form" role="complementary">

	<div id="custom-announcements-content">
		<p class="greeting-text"><?php _e('Post Announcement','boss'); ?></p>
		<div id="custom-announcements-textarea">
			<textarea class="bp-announcements" name="custom-announcements" id="custom-announcements"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_textarea( $_GET['r'] ); ?> <?php endif; ?></textarea>
		</div>

		<div id="custom-announcements-options">
			<div id="custom-announcements-submit">
				<input type="submit" name="aw-custom-announcements-submit" id="aw-custom-announcements-submit" value="<?php esc_attr_e( 'Post Update', 'buddypress' ); ?>" />
			</div>

		</div><!-- #custom-announcements-options -->
	</div><!-- #custom-announcements-content -->

	<?php wp_nonce_field( 'announcement_post_update', '_wpnonce_post_update' ); ?>

</form><!-- #custom-announcements-form -->
