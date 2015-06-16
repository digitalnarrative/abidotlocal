<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<header>
                                    <h1 class="entry-title"><?php the_title(); ?></h1>
                                    <div class="entry-meta">
                                        <?php buddyboss_entry_meta(); ?>
                                    </div>
                                </header><!-- .archive-header -->

                                <div class="entry-content">
                                    <?php the_content(); ?>
                                    
                                </div><!-- .entry-content -->

                                <footer class="entry-footer">
                                    <?php edit_post_link( __( 'Edit', 'buddyboss' ), '<span class="edit-link">', '</span>' ); ?>
                                </footer><!-- .entry-footer -->

                                <?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>