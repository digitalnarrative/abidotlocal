<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */
?>
	</div><!-- #main .wrapper -->
</div><!-- #page -->

<footer id="colophon" role="contentinfo">

    <?php if ( is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') || is_active_sidebar('footer-4') || is_active_sidebar('footer-5') ) : ?>

        <div class="footer-inner-top">
            <div class="footer-inner widget-area">
                
                <ul class="footer-menu">
                    <?php if ( has_nav_menu( 'secondary-menu' ) ) : ?>
                        <?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'secondary-menu', 'items_wrap' => '%3$s' ) ); ?>
                    <?php endif; ?>
                </ul>
                <ul class="second-footer-menu">
                    <?php if ( has_nav_menu( 'secondary-menu' ) ) : ?>
                        <?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'second-footer-menu', 'items_wrap' => '%3$s' ) ); ?>
                    <?php endif; ?>
                </ul>


                <?php if ( is_active_sidebar('footer-1') ) : ?>
                    <div class="footer-widget">
                        <?php dynamic_sidebar( 'footer-1' ); ?>
                    </div><!-- .footer-widget -->
                <?php endif; ?>

                <?php if ( is_active_sidebar('footer-2') ) : ?>
                    <div class="footer-widget">
                        <?php dynamic_sidebar( 'footer-2' ); ?>
                    </div><!-- .footer-widget -->
                <?php endif; ?>

                <?php if ( is_active_sidebar('footer-3') ) : ?>
                    <div class="footer-widget">
                        <?php dynamic_sidebar( 'footer-3' ); ?>
                    </div><!-- .footer-widget -->
                <?php endif; ?>

            </div><!-- .footer-inner -->
        </div><!-- .footer-inner-top -->

    <?php endif; ?>

    <div class="footer-inner-bottom">
        <div class="footer-inner">

                <?php if ( is_active_sidebar('footer-4') ) : ?>
                    <div class="footer-widget">
                        <?php dynamic_sidebar( 'footer-4' ); ?>
                    </div><!-- .footer-widget -->
                <?php endif; ?>

                <?php if ( is_active_sidebar('footer-5') ) : ?>
                    <div class="footer-widget last">
                        <?php dynamic_sidebar( 'footer-5' ); ?>
                    </div><!-- .footer-widget -->
                <?php endif; ?>


    	</div><!-- .footer-inner -->
    </div><!-- .footer-inner-bottom -->

    <?php do_action( 'bp_footer' ) ?>

</footer><!-- #colophon -->

</div> <!-- #inner-wrap -->

</div><!-- #main-wrap (Wrap For Mobile) -->

<?php wp_footer(); ?>

</body>
</html>