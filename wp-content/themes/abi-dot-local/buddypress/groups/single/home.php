<div id="buddypress">

	<?php if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group(); ?>

	<?php

	/**
	 * Fires before the display of the group home content.
	 *
	 * @since BuddyPress (1.2.0)
	 */
	do_action( 'bp_before_group_home_content' ); ?>

	<div id="item-header" role="complementary">

		<?php bp_get_template_part( 'groups/single/group-header' ); ?>

	</div><!-- #item-header -->

	<div id="item-nav">
		<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
			<ul>

				<?php bp_get_options_nav(); ?>

				<?php

				/**
				 * Fires after the display of group options navigation.
				 *
				 * @since BuddyPress (1.2.0)
				 */
				do_action( 'bp_group_options_nav' ); ?>

			</ul>
		</div>
		<?php if(bb_gcf_get_current_group_field_data(2) && bb_gcf_get_current_group_field_data(1)){?>
			<a href="<?php echo bb_gcf_get_current_group_field_data(2);?>">
				<img src="<?php echo bb_gcf_get_current_group_field_data(1);?>">
			</a>
		<?php } ?>

		<?php if(bb_gcf_get_current_group_field_data(3) && bb_gcf_get_current_group_field_data(4)){?>
			<a href="<?php echo bb_gcf_get_current_group_field_data(4);?>">
				<img src="<?php echo bb_gcf_get_current_group_field_data(3);?>">
			</a>
		<?php } ?>

		<?php if(bb_gcf_get_current_group_field_data(5) && bb_gcf_get_current_group_field_data(6)){?>
			<a href="<?php echo bb_gcf_get_current_group_field_data(6);?>">
				<img src="<?php echo bb_gcf_get_current_group_field_data(5);?>">
			</a>
		<?php } ?>

		<?php if(bb_gcf_get_current_group_field_data(7) && bb_gcf_get_current_group_field_data(8)){?>
			<a href="<?php echo bb_gcf_get_current_group_field_data(8);?>">
				<img src="<?php echo bb_gcf_get_current_group_field_data(7);?>">
			</a>
		<?php } ?>

		<?php if(bb_gcf_get_current_group_field_data(9) && bb_gcf_get_current_group_field_data(10)){?>
			<a href="<?php echo bb_gcf_get_current_group_field_data(10);?>">
				<img src="<?php echo bb_gcf_get_current_group_field_data(9);?>">
			</a>
		<?php } ?>

		<?php if(bb_gcf_get_current_group_field_data(11) && bb_gcf_get_current_group_field_data(12)){?>
			<a href="<?php echo bb_gcf_get_current_group_field_data(12);?>">
				<img src="<?php echo bb_gcf_get_current_group_field_data(11);?>">
			</a>
		<?php } ?>

	</div><!-- #item-nav -->

	<div id="item-body">
		<?php

		/**
		 * Fires before the display of the group home body.
		 *
		 * @since BuddyPress (1.2.0)
		 */
		do_action( 'bp_before_group_body' );

		/**
		 * Does this next bit look familiar? If not, go check out WordPress's
		 * /wp-includes/template-loader.php file.
		 *
		 * @todo A real template hierarchy? Gasp!
		 */

			// Looking at home location
			if ( bp_is_group_home() ) :

				if ( bp_group_is_visible() ) {

					// Use custom front if one exists
					$custom_front = bp_locate_template( array( 'groups/single/front.php' ), false, true );
					if     ( ! empty( $custom_front   ) ) : load_template( $custom_front, true );

					// Default to activity
					elseif ( bp_is_active( 'activity' ) ) : bp_get_template_part( 'groups/single/activity' );

					// Otherwise show members
					elseif ( bp_is_active( 'members'  ) ) : bp_groups_members_template_part();

					endif;

				} else {

					/**
					 * Fires before the display of the group status message.
					 *
					 * @since BuddyPress (1.1.0)
					 */
					do_action( 'bp_before_group_status_message' ); ?>

					<div id="message" class="info">
						<p><?php bp_group_status_message(); ?></p>
					</div>

					<?php

					/**
					 * Fires after the display of the group status message.
					 *
					 * @since BuddyPress (1.1.0)
					 */
					do_action( 'bp_after_group_status_message' );

				}

			// Not looking at home
			else :

				// Group Admin
				if     ( bp_is_group_admin_page() ) : bp_get_template_part( 'groups/single/admin'        );

				// Group Activity
				elseif ( bp_is_group_activity()   ) : bp_get_template_part( 'groups/single/activity'     );

				// Group Members
				elseif ( bp_is_group_members()    ) : bp_groups_members_template_part();

				// Group Invitations
				elseif ( bp_is_group_invites()    ) : bp_get_template_part( 'groups/single/send-invites' );

				// Old group forums
				elseif ( bp_is_group_forum()      ) : bp_get_template_part( 'groups/single/forum'        );

				// Membership request
				elseif ( bp_is_group_membership_request() ) : bp_get_template_part( 'groups/single/request-membership' );

				// Anything else (plugins mostly)
				else                                : bp_get_template_part( 'groups/single/plugins'      );

				endif;

			endif;

		/**
		 * Fires after the display of the group home body.
		 *
		 * @since BuddyPress (1.2.0)
		 */
		do_action( 'bp_after_group_body' ); ?>

	</div><!-- #item-body -->

	<?php

	/**
	 * Fires after the display of the group home content.
	 *
	 * @since BuddyPress (1.2.0)
	 */
	do_action( 'bp_after_group_home_content' ); ?>

	<?php endwhile; endif; ?>

</div><!-- #buddypress -->