<?php if ( $this->current_user_can_post ):
	?>

	<div class="bp-simple-post-form">

		<form class="standard-form bp-simple-post-form"  method="post" action=""  enctype="multipart/form-data">

			<!-- do not modify/remove the line blow -->
			<input type="hidden" name="bp_simple_post_form_id" value="<?php echo $this->id; ?>" />
			<?php wp_nonce_field( 'bp_simple_post_new_post_' . $this->id ); ?>
			<input type="hidden" name="action" value="bp_simple_post_new_post_<?php echo $this->id; ?>" />
			<?php if ( $post_id ): ?>
				<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
			<?php endif; ?>

			<!-- you can modify these, just make sure to not change the name of the fields -->

			<label for="bp_simple_post_title"><?php _e( 'Title:', 'buddyboss' ); ?>
				<input type="text" name="bp_simple_post_title"  value=""/>
			</label>

			<label for="bp_simple_post_text" ><?php _e( 'Post:', 'buddyboss' ); ?>
				<textarea name="bp_simple_post_text" id="bp_simple_post_text" ></textarea>
			</label>
			<!--- generating the file upload box -->
			<?php if ( $this->upload_count ): ?>

				<label> <?php _e( 'Uploads', 'buddyboss' ); ?></label>

				<div class="bp_simple_post_uploads_input">
					<?php for ( $i = 0; $i < $this->upload_count; $i ++  ): ?>
						<label><input type="file" name="bp_simple_post_upload_<?php echo $i; ?>" /></label>
					<?php endfor; ?>

				</div>
			<?php endif; ?>
			<!--- generating the file upload box -->
			<?php if ( $this->has_post_thumbnail ): ?>

				<label> <?php _e( 'Featured Image', 'buddyboss' ); ?></label>

				<div class="bp_simple_post_featured_image_input">
					<label><input type="file" name="bp_simple_post_upload_thumbnail" /></label>
				</div>
			<?php endif; ?>
				
				<div class='simple-post-taxonomies-box clearfix'>
					<?php // $this->render_taxonomies(); ?>
					<div class="clear"></div>
				</div>

			<input  type="hidden" value="<?php echo $_SERVER[ 'REQUEST_URI' ]; ?>" name="post_form_url"  />
			<input  type="hidden" value="<?php bp_group_id(); ?>" name="post_group_id"  />

			<input id="submit" name='bp_simple_post_form_subimitted' type="submit" value="<?php _e( 'Post', 'buddyboss' ); ?>" />


		</form>
	</div>
	<?php
endif;
?>