<?php
/**
 * The template for displaying BuddyPress content.
 * Template Name: Groups Directory
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */

get_header(); ?>


		<div class="home_slider">
			<?php putRevSlider("cities_slider") ?>
		</div><!-- .home_slider -->
		
		<div class="bp-members-title">
			<?php $post = get_post(6);?>
	    	<h1><?php echo $post->title;?></h1>
	        <p><?php echo get_field('subtitle', 6);?></p>
	    </div>
	   	<article>
	   	<!-- Buddypress Groups Loop -->

			<?php

			/**
			 * Fires before the display of groups from the groups loop.
			 *
			 * @since BuddyPress (1.2.0)
			 */
			do_action( 'bp_before_groups_loop' ); ?>

			<?php global $groups_template;

			if ( bp_has_groups( bp_ajax_querystring( 'groups' ) ) ) : 


				/**
				 * Fires before the listing of the groups list.
				 *
				 * @since BuddyPress (1.1.0)
				 */
				do_action( 'bp_before_directory_groups_list' ); ?>

				<ul id="groups-list" class="item-list">

				<?php while ( bp_groups() ) : bp_the_group();?>
					
					<li <?php bp_group_class(); ?>>
						<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
							<div class="item-avatar">
								<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( 'full' ); ?></a>
							</div>
						<?php endif; ?>

						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a></div>
							<div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ); ?></span></div>

							<div class="item-desc"><?php echo wp_trim_words( bp_get_group_description(), 20, '... / <a href="' . get_the_permalink() .'">Read More</a>' ); ?></div>

							<?php

							/**
							 * Fires inside the listing of an individual group listing item.
							 *
							 * @since BuddyPress (1.1.0)
							 */
							do_action( 'bp_directory_groups_item' ); ?>

						</div>

						<div class="action">


							<div class="meta">

								<span class="count-circle"><?php echo bp_get_group_total_members(); ?></span> <?php
								if(bp_get_group_total_members() > 1 ) {
									echo 'members';
								} else {
									echo 'member';
									}?>

							</div>

							<?php

							/**
							 * Fires inside the action section of an individual group listing item.
							 *
							 * @since BuddyPress (1.1.0)
							 */
							do_action( 'bp_directory_groups_actions' ); ?>

						</div>

						<div class="clear"></div>
					</li>

				<?php endwhile; ?>

				</ul>

				<?php

				/**
				 * Fires after the listing of the groups list.
				 *
				 * @since BuddyPress (1.1.0)
				 */
				do_action( 'bp_after_directory_groups_list' ); ?>

				
			<?php else: ?>

				<div id="message" class="info">
					<p><?php _e( 'There were no groups found.', 'buddypress' ); ?></p>
				</div>

			<?php endif; ?>

			<?php

			/**
			 * Fires after the display of groups from the groups loop.
			 *
			 * @since BuddyPress (1.2.0)
			 */
			do_action( 'bp_after_groups_loop' ); ?>
		</article>
			
		<div class="entry-content">
			<?php echo apply_filters( 'the_content', $post->post_content ); // Do this instead; ?>
		</div>

<?php get_footer();?>