
<?php
$q = new WP_Query( abi_get_query() );
global $post;
if ( $q->have_posts() ) :
	?>
	<?php abi_loop_start(); //please do not remove it  ?>
	<?php while ( $q->have_posts() ):$q->the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
			<div class="author-box">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), '50' ); ?>
				<p><?php printf( __( 'by %s', 'buddyboss' ), bp_core_get_userlink( $post->post_author ) ) ?></p>
			</div>
			<div class="post-content">
				<h2 class="posttitle"><?php the_title(); ?></h2>
				<p class="date"><?php the_time() ?> <?php printf( __( 'by %s', 'buddyboss' ), bp_core_get_userlink( $post->post_author ) ) ?></em></p>
				<div class="entry">
					<?php the_content( __( 'Read the rest of this entry &rarr;', 'buddyboss' ) ); ?>

					<?php wp_link_pages( array( 'before' => __( '<p><strong>Pages:</strong> ', 'buddyboss' ), 'after' => '</p>', 'next_or_number' => 'number' ) ); ?>
				</div>
			</div>

		</div>
	<?php endwhile; ?>
	<?php abi_loop_end(); //please do not remove it ?>

	<?php
	add_filter( 'comments_open', create_function( '', 'return open;' ) );
	comments_template();
	?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This group has no Blog posts.', 'buddyboss' ); ?></p>
	</div>

<?php endif; ?>
