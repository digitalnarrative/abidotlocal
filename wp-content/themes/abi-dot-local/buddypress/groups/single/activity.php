<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<li class="feed"><a href="<?php bp_group_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'buddypress' ); ?>"><?php _e( 'RSS', 'buddypress' ); ?></a></li>

		<?php

		/**
		 * Fires inside the syndication options list, after the RSS option.
		 *
		 * @since BuddyPress (1.2.0)
		 */
		do_action( 'bp_group_activity_syndication_options' ); ?>

		<li id="activity-filter-select" class="last">
			<label for="activity-filter-by"><?php _e( 'Show:', 'buddypress' ); ?></label>
			<select id="activity-filter-by">
				<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'buddypress' ); ?></option>

				<?php bp_activity_show_filters( 'group' ); ?>

				<?php

				/**
				 * Fires inside the select input for group activity filter options.
				 *
				 * @since BuddyPress (1.2.0)
				 */
				do_action( 'bp_group_activity_filter_options' ); ?>
			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php

/**
 * Fires before the display of the group activity post form.
 *
 * @since BuddyPress (1.2.0)
 */
do_action( 'bp_before_group_activity_post_form' ); ?>

<?php if ( is_user_logged_in() && bp_group_is_member() ) : ?>

	<?php bp_get_template_part( 'activity/post-form' ); ?>

<?php endif; ?>

<ul class="activity-filter">
	<li data-value="-1" class="current">All</li>
	<li data-value="activity_update">Updates</li>
	<li data-value="group_details_updated">City Announcements</li>
	<li data-value="joined_group">New Members</li>
	<li data-value="bbp_topic_create">Forum Topics</li>
	<li data-value="bbp_reply_create">Forum Replies</li>
</ul>

<?php

/**
 * Fires after the display of the group activity post form.
 *
 * @since BuddyPress (1.2.0)
 */
do_action( 'bp_after_group_activity_post_form' ); ?>
<?php

/**
 * Fires before the display of the group activities list.
 *
 * @since BuddyPress (1.2.0)
 */
do_action( 'bp_before_group_activity_content' ); ?>

<div class="activity single-group">

	<?php bp_get_template_part( 'activity/activity-loop' ); ?>

</div><!-- .activity.single-group -->

<?php

/**
 * Fires after the display of the group activities list.
 *
 * @since BuddyPress (1.2.0)
 */
do_action( 'bp_after_group_activity_content' ); ?>
