<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each specific one. For example, BuddyBoss already
 * has tag.php for Tag archives, category.php for Category archives, and
 * author.php for Author archives.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */

get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title">
                                    <?php _e( 'Resources', 'TEXTDOMAIN ' );?>
                                    <?php $add_resouce_page = get_permalink( ka_misc_settings( 'page_add_resource' ) );?>
                                    <a class='button btn-add-resource' href='<?php echo esc_url( $add_resouce_page );?>'><?php _e( 'Add Resource', 'TEXTDOMAIN' );?></a>
                                </h1>
			</header><!-- .archive-header -->

			<?php
			/* Start the Loop */
			 /* Start the Loop */
                        while ( have_posts() ) : the_post();

                            get_template_part( 'loop', 'resource' );

                        endwhile;

                        buddyboss_pagination();
			?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>