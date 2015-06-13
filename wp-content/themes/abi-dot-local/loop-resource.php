<article id="post-<?php the_ID(); ?>" <?php post_class( 'loop-resource'); ?>>
	<div class='loop-resource-inner'>
		<header >
			<h2 class="media-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
                        <?php echo __( 'By', 'boss-child' ) . ' ' . bp_core_get_userlink( get_the_author_meta( 'ID' ) ) . ' ';?> 
                        <?php echo __( 'in', 'boss-child' ) . ' '; ?> <span class="media-cat"><?php the_category( ', ' );?> </span>
                        <span class='post_date'><i class='fa fa-calendar'></i> <?php the_date();?></span>
		</header>

		<div class='entry-content'>
                    <?php the_excerpt(); ?>
		</div>
		
		<footer class="entry-meta table">
                    <div class="btnn-group-left">
                        <span class='btnn btn-download-count'><i class='fa fa-download'></i> <?php echo (int)get_post_meta( get_the_ID(), '_resource_attachments_download_count', true );?></span>

                        <a class='btnn btn-view' href='<?php the_permalink();?>'><i class='fa fa-search'></i> <?php _e( 'View', 'boss-child' );?></a>
                    </div>
		</footer>
	</div>

</article><!-- #post -->