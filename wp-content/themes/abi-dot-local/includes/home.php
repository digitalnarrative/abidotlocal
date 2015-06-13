<div id="subnav" class="item-list-tabs no-ajax">
	<ul>
		<li <?php if ( abi_is_home() ) { ?> class="current"<?php } ?>>
			<a href="<?php echo abi_get_home_url(); ?>"><?php _e( "Posts", "abi" ); ?></a>
		</li>
		<?php if ( abi_current_user_can_post() ) { ?>
			<li <?php if ( abi_is_post_create() ): ?> class="current"<?php endif; ?>><a href="<?php echo abi_get_home_url(); ?>/create"><?php _e( "Create New Post", "abi" ); ?></a></li>
			<?php } ?>
	</ul>
</div>

<?php
if ( abi_is_single_post() )
	abi_load_template( 'single-post.php' );
else if ( abi_is_post_create() )
	abi_load_template( 'create.php' );
else
	abi_load_template( 'blog.php' );
?>
