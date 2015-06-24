<?php
/**
 * BuddyPress Subscription 'revision_note' post_meta handler
 *
 * Saves the revision note to the post revision saves
 *
 * @package Buddy Press Subscriptions
 */

function up546E_bps_save_post_revision_note( $post_id) {
/*
	

	$mydata = sanitize_text_field( $_POST['userpress_wiki_revision_note'] );
  	update_post_meta( $post_id, 'userpress_wiki_revision_note', $mydata );
  	*/
	$parent_id = wp_is_post_revision( $post_id );

	if ( $parent_id ) {

		$parent  = get_post( $parent_id );
		$my_meta = get_post_meta( $parent->ID, 'userpress_wiki_revision_note', true );
		
		//echo $post_id.$my_meta;
		
		if ( false !== $my_meta && isset($_POST['userpress_wiki_revision_note']) )
			add_metadata( 'post', $post_id, 'userpress_wiki_revision_note', $_POST['userpress_wiki_revision_note'] );

	}

}
add_action( 'save_post', 'up546E_bps_save_post_revision_note' );

function up546E_bps_revision_fields( $fields ) {

	$fields['userpress_wiki_revision_note'] = 'Revision Note';
	return $fields;

}
add_filter( '_wp_post_revision_fields', 'up546E_bps_revision_fields' );

function up546E_bps_revision_field( $value, $field ) {

	global $revision;
	return get_metadata( 'post', $revision->ID, $field, true );

}
add_filter( '_wp_post_revision_field_my_meta', 'up546E_bps_revision_field', 10, 2 );

?>