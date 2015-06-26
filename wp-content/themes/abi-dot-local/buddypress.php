<?php
/**
 * The template for displaying BuddyPress content.
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */

get_header(); ?>

	<!-- if widgets are loaded for any BuddyPress component, display the BuddyPress sidebar -->
	<?php if (
		( is_active_sidebar('members') && bp_is_current_component( 'members' ) && !bp_is_user() ) ||
		( is_active_sidebar('profile') && bp_is_user() ) ||
		( is_active_sidebar('groups') && bp_is_current_component( 'groups' ) && !bp_is_group() && !bp_is_user() ) ||
		( is_active_sidebar(bp_get_current_group_slug()) && bp_is_group() ) ||
		( is_active_sidebar('activity') && bp_is_current_component( 'activity' ) && !bp_is_user() ) ||
		( is_active_sidebar('blogs') && is_multisite() && bp_is_current_component( 'blogs' ) && !bp_is_user() ) ||
		( bp_is_current_component( 'groups' ) && bp_is_single_item() ) ||		
		( is_active_sidebar('forums') && bp_is_current_component( 'forums' ) && !bp_is_user() )
	): ?>
		<div class="page-right-sidebar">

	<!-- if not, hide the sidebar -->
	<?php else: ?>
		<div class="page-full-width">
	<?php endif; ?>

		<!-- Group Single Cover Photo -->
		<?php if ( bp_is_single_item() ) {

            global $bp;
            $group_id = $bp->groups->current_group->id;
            $uploaded_cover = groups_get_groupmeta( $group_id, '_group_cover_photo' );
    ?>

            <div class="group-cover-photo">
				<div class="bg" style="background-image:url(<?php echo $uploaded_cover['url'];?>)">
					<div class="grey-mask"></div>
				</div>
				<p id="breadcrumbs">
					<a href="<?php bloginfo('url');?>">Home</a>  / <a href="<?php bloginfo('url');?>/groups">Cities</a> / <a href="<?php the_permalink();?>"><?php the_title();?></a> 
				</p>
				<?php while ( have_posts() ): the_post(); ?>
					<div id="item-header-avatar">
						<a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>">

							<?php bp_group_avatar(); ?>

						</a>
					</div><!-- #item-header-avatar -->
					<h1><?php the_title();?></h1>
					
				<?php endwhile; // end of the loop. ?>

		        <div class="clearfix"></div>
			</div>

		<?php } // endif ?>
			<!-- BuddyPress template content -->
			<div id="primary" class="site-content">

					<div id="content" role="main">

						<article>
						<?php while ( have_posts() ): the_post(); ?>
							<?php get_template_part( 'content', 'buddypress' ); ?>
							<?php comments_template( '', true ); ?>
						<?php endwhile; // end of the loop. ?>
						</article>

					</div><!-- #content -->

			</div><!-- #primary -->

			<?php get_sidebar('buddypress'); ?>

		</div><!-- closing div -->

<?php get_footer(); ?>