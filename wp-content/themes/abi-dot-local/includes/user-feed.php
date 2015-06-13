<?php

//Front end post creation form
function abi_frontend_post_form() {

	if ( ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="abi-form-wrapper"><?php
		$message_option = get_option( 'abi-post-message' );
		if ( ! empty( $message_option ) ) {
			echo '<span class="abi-post-messages">' . $message_option . '</span>';
			delete_option( 'abi-post-message' );
		}
		?>

		<form id="abi-user-post-creation" method="post" action="" enctype="multipart/form-data" >
	<?php wp_nonce_field( 'bp_simple_post_new_post' ); ?>
			<input type="hidden" name="action" value="bp_simple_post_new_post" />
			<input type="hidden" name="bp_get_loggedin_user_fullname" value="<?php bp_loggedin_user_fullname(); ?>" />

			<label><?php _e( 'Post Title', 'buddyboss' ); ?></label>
			<div class="abi-form-field"><input name="abi_form_post_title" type="text" required="true" value="" /></div>

			<label><?php _e( 'Post Content', 'buddyboss' ); ?></label>
			<div class="abi-form-field"><?php wp_editor( '', 'abi-cutom-editor' ); ?></div>

			<label><?php _e( 'Post Image', 'buddyboss' ); ?></label>
			<div class="abi-form-field"><input type="file" name="abi_form_post_image"></div>

			<div class="abi-form-field"><input class="abi-form-submit-button" type="submit" name="abi_form_submit_btn" value="Submit Post"></div>
		</form>
	</div><?php
}

add_shortcode( 'abi-post-form', 'abi_frontend_post_form' );

//Handle form data
add_action( 'bp_ready', 'abi_handle_form_data' );

function abi_handle_form_data() {
	if ( ! empty( $_POST[ 'abi_form_submit_btn' ] ) ) {

		//verify nonce
		if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'bp_simple_post_new_post' ) ) {
			bp_core_add_message( __( 'The Security check failed!', 'bpsfep' ), 'error' );
			return; //do not proceed
		}

		$title = $_POST[ 'abi_form_post_title' ];
		$content = $_POST[ 'abi-cutom-editor' ];

		if ( empty( $title ) || empty( $content ) ) {
			$error = true;
			$message = __( 'Please make sure to fill the required fields', 'bsfep' );
		}

		if ( ! $error ) {
			$post_data = array(
				'post_author' => bp_loggedin_user_id(),
				'post_content' => $content,
				'post_type' => 'post',
				'post_status' => 'publish',
				'post_title' => $title
			);
			$post_id = wp_insert_post( $post_data );
			//if everything worked fine, the post was saved
			if ( ! is_wp_error( $post_id ) ) {

				$feed_term = strtolower( str_replace( ' ', '-', $_POST[ 'bp_get_loggedin_user_fullname' ] ) );
				wp_set_object_terms( $post_id, $feed_term, 'userfeed-taxonomy' );

				//check for upload 
				$input_field_name = 'abi_form_post_image';
				$attachment = abi_handle_upload( $post_id, $input_field_name, 'bpsfep_new_post' );
				if ( $post_id && $attachment && wp_attachment_is_image( $attachment ) )
					set_post_thumbnail( $post_id, $attachment );

				abi_post_activity_url( $post_id, $title, $content, $attachment );
			}

			$message = __( 'Post saved successfully.', 'buddyboss' );
		}
		else {
			$message = __( 'There was a problem saving your post. Please try again later.', 'buddyboss' );
		}

		update_option( 'abi-post-message', $message );
	}
}

function abi_handle_upload( $post_id, $input_field_name, $action ) {
	require_once( ABSPATH . 'wp-admin/includes/admin.php' );
	$post_data = array();
	$override = array( 'test_form' => false, 'action' => $action );
	$attachment = media_handle_upload( $input_field_name, $post_id, $post_data, $override );

	return $attachment;
}

function abi_post_activity_url( $post_id, $title, $content, $attachment ) {

	$attachment_img_src = wp_get_attachment_image_src( $attachment, 'medium' );
	$post_url = get_the_permalink( $post_id );

	$updated_content .= '<div class="bb_user_post">';

	if ( ! empty( $attachment_img_src ) ) {
		$updated_content .= '<div class="bb_img_preview_container">';
		$updated_content .= '<a href="' . $post_url . '"><img src="' . $attachment_img_src[ 0 ] . '" /></a>';
		$updated_content .= '</div>';
	}

	$updated_content .= '<div class="bb_post_contents">';
	$updated_content .= '<h3 class="bb_post_title"><a href="' . $post_url . '">' . $title . '</a></h3>';
	$updated_content .= '<p class="bb_link_preview_body">' . $content . '</p>';
	$updated_content .= '</div>';
	$updated_content .= '</div>';
	$updated_content .= '<br/>';

	$activity_id = bp_activity_post_update( array( 'content' => $content ) );
	bp_activity_update_meta( $activity_id, 'abi-user-posts-activity-content', $updated_content );
	bp_activity_update_meta( $activity_id, 'abi-user-posts-id', $post_id );
}

add_action( 'bp_get_activity_content_body', 'abi_activity_content_filter' );

function abi_activity_content_filter( $content ) {
	
	global $activities_template;

	$curr_id = $activities_template->current_activity;
	$act_id = ( int ) $activities_template->activities[ $curr_id ]->id;

	// Check for activity ID in $_POST if this is a single
	// activity request from a [read more] action
	if ( $act_id === 0 && ! empty( $_POST[ 'activity_id' ] ) ) {
		$activity_array = bp_activity_get_specific( array(
			'activity_ids' => $_POST[ 'activity_id' ],
			'display_comments' => 'stream'
				) );

		$activity = ! empty( $activity_array[ 'activities' ][ 0 ] ) ? $activity_array[ 'activities' ][ 0 ] : false;
		$act_id = ( int ) $activity->id;
	}

	// This should never happen, but if it does, bail.
	if ( $act_id === 0 ) {
		return $content;
	}

	$url_preview_html = bp_activity_get_meta( $act_id, 'abi-user-posts-activity-content', true );

	if ( empty( $url_preview_html ) ) {
		return $content;
	}

	$content = $url_preview_html;

	return $content;
}

add_action( 'bp_ready', 'abi_add_profile_tabs', 100 );

function abi_add_profile_tabs() {

	bp_core_new_nav_item( array(
		'name' => sprintf( __( 'Blog', 'buddyboss' ) ),
		'slug' => 'blog',
		'position' => 80,
		'screen_function' => 'bp_activity_screen_blog',
		'default_subnav_slug' => 'my-gallery'
	) );
}

function bp_activity_screen_blog() {
	add_action( 'bp_template_content', 'abi_get_blog_content' );
	bp_core_load_template( apply_filters( 'bp_activity_screen_blog', 'activity/single/plugins' ) );
}

function abi_get_blog_content() {
	abi_load_template( 'blog-activity-loop.php' );
}

/**
 * Filters the activity stream to display only blog activities.
 */
function abi_blog_activity_querystring_filter( $query_string = '', $object = '' ) {
	if ( $object != 'activity' )
		return $query_string;

	global $bp;
	
	$args = wp_parse_args( $query_string, array(
		'action' => false,
		'type' => false,
		'user_id' => false,
		'page' => 1
			) );

	if ( is_activity_blog_page() ) {
		if ( bp_is_user() )
			$args[ 'user_id' ] = bp_displayed_user_id();
			$args[ 'type' ] = 'activity_update';

		if ( ! isset( $args[ 'meta_query' ] ) || ! is_array( $args[ 'meta_query' ] ) )
			$args[ 'meta_query' ] = array();

		$args[ 'meta_query' ][] = array(
			'key' => 'abi-user-posts-activity-content',
			'compare' => 'EXISTS'
		);
		$args[ 'meta_query' ][ 'relation' ] = 'OR';
		$query_string = empty( $args ) ? $query_string : $args;
	}
	
	if ( !bp_is_current_action( 'announcements' ) ) {
		if ( ! isset( $args[ 'meta_query' ] ) || ! is_array( $args[ 'meta_query' ] ) )
			$args[ 'meta_query' ] = array();

		$args[ 'meta_query' ][] = array(
			'key' => 'announcements',
			'compare' => 'NOT EXISTS'
		);
		$args[ 'meta_query' ][ 'relation' ] = 'OR';
		$query_string = empty( $args ) ? $query_string : $args;
		
	}
	
	if ( is_activity_announcements_page() ) {
		$args[ 'type' ] = 'activity_update';
		if ( ! isset( $args[ 'meta_query' ] ) || ! is_array( $args[ 'meta_query' ] ) )
			$args[ 'meta_query' ] = array();

		$args[ 'meta_query' ][] = array(
			'key' => 'announcements',
			'compare' => 'EXISTS'
		);
		$args[ 'meta_query' ][ 'relation' ] = 'OR';
		$query_string = empty( $args ) ? $query_string : $args;
		
	}


	return $query_string;
}

add_filter( 'bp_ajax_querystring', 'abi_blog_activity_querystring_filter', 112, 2 );

function is_activity_blog_page() {
	global $bp;
	$current_component = $bp->current_component;

	if ( 'blog' == $current_component ) {
		return true;
	}
	return false;
}

function is_activity_announcements_page() {
	global $bp;
	if ( bp_is_current_component( $bp->groups->slug ) && bp_is_current_action( 'announcements' ) ) {
		return true;
	}
	return false;
}

//Updating the adminbar
add_action( 'wp_before_admin_bar_render', 'abi_add_wp_menu', 9999 );

function abi_add_wp_menu() {
	global $wp_admin_bar;
	$href = bp_loggedin_user_domain().'blog';
	
	$wp_admin_bar->add_menu( array(
		'parent' => buddypress()->my_account_menu_id,
		'id' => 'my-account-blog',
		'title' => 'Blog',
		'href' => $href
	) );
}

//Delete post on activity delete
add_action('bp_before_activity_delete','abi_delete_poston_activity_delete');
function abi_delete_poston_activity_delete( $args ) {
	
	$act_id = $args['id'];
	$pid = bp_activity_get_meta($act_id,'abi-user-posts-id',true );
	wp_delete_post( $pid , true );
	
}

//Announcements update ajax

add_action( 'wp_ajax_announcement_post_update', 'bp_activity_action_announcement_post_update' );
add_action( 'wp_ajax_nopriv_announcement_post_update', 'bp_activity_action_announcement_post_update' );

function bp_activity_action_announcement_post_update() {

	// Do not proceed if user is not logged in, not viewing activity, or not posting
	if ( ! is_user_logged_in() || !is_activity_announcements_page() )
		return false;
	// Check the nonce
	check_admin_referer( 'announcement_post_update', '_wpnonce_post_update' );

	$content = $_POST[ 'content' ];


	// No activity content so provide feedback and redirect
	if ( empty( $content ) ) {
		bp_core_add_message( __( 'Please enter some content to post.', 'buddypress' ), 'error' );
		bp_core_redirect( wp_get_referer() );
	}
	
	// Record this on the user's profile
	$primary_link     = bp_core_get_userlink( bp_loggedin_user_id(), false, true );
	$add_primary_link = apply_filters( 'bp_activity_new_update_primary_link', $primary_link );
	$post_action	= '<a href="'.$add_primary_link.'" title="'. bp_user_firstname().'">'. bp_user_firstname().'</a> added an announcement';
	
	$activity_id = bp_activity_add( array(
		'user_id'      => bp_loggedin_user_id(),
		'content'      => $content,
		'action'	   => $post_action,
		'primary_link' => $add_primary_link,
		'component'    => buddypress()->groups->id,
		'type'         => 'activity_update',
		'recorded_time'     => bp_core_current_time(),
		'item_id'			=> bp_get_current_group_id(),
		'hide_sitewide'     => false
	) );
	
	bp_activity_update_meta($activity_id, 'announcements', 'true' );


	// Provide user feedback
	if ( ! empty( $activity_id ) )
		bp_core_add_message( __( 'Added announcement!', 'buddypress' ) );
	else
		bp_core_add_message( __( 'There was an error when posting your update. Please try again.', 'buddypress' ), 'error' );
	
	die();
	
}
