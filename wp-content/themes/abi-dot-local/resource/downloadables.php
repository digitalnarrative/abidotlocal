<?php 
$attachment_ids = get_post_meta( get_the_ID(), '_resource_attachments', true );
if( !empty( $attachment_ids ) ):
?>
<div class='downloadables'>
	<h3><?php _e( 'Downloads', 'boss-child' );?></h3>
	<div class="media-list clearfix">
		<?php 
		foreach( $attachment_ids as $a_id ):
			$file_info = wp_prepare_attachment_for_js( $a_id );
			kasa_the_attachment( $file_info );
			
			?>
			<div class="<?php kasa_file_cssclass();?>">
				<div class="media-left">
					<img src="<?php kasa_file_thumbnail_url();?>">
				</div>
				<div class='media-body'>
					<p class='media-title filename'><?php kasa_file_name();?></p>
					<a href="<?php kasa_file_download_url();?>" class="button"><i class="fa fa-download"></i> <?php _e( 'Download', 'TEXTDOMAIN' );?></a>
					<span class='download_count'><?php printf( __( '%s Downloads', 'TEXTDOMAIN' ), kasa_file_download_count() );?></span>
				</div>
			</div>
		<?php endforeach;?>
	</div>
</div>
<?php 
endif;
