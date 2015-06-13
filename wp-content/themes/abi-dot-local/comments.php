<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to buddyboss_comment() which is
 * located in the functions.php file.
 *
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 3.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() )
	return;
?>
<?php $avatar = bp_core_fetch_avatar ( array( 'item_id' => get_current_user_id(), 'type' => 'full', 'width' => '32', 'height' => '32' ) );?>
<?php if ( comments_open() || have_comments() ) : ?>
	
	<div id="comments" class="comments-area">

		<?php // You can start editing here -- including this comment! ?>
		
		<h2 class="comments-title">
			<?php
				printf( _n( '<i class="fa fa-comments"></i> %1$s Comment', '<i class="fa fa-comments"></i> %1$s Comments', get_comments_number(), 'buddyboss' ),
					number_format_i18n( get_comments_number() ) );
			?>
		</h2>

			<?php
				// Comment Form Arguments
				$args = array(
				  'id_form'           => 'commentform',
				  'id_submit'         => 'submit',
				  'class_submit'      => 'submit',
				  'name_submit'       => 'submit',
				  'title_reply'       => '',
				  'title_reply_to'    => '',
				  'cancel_reply_link' => __( 'Cancel Reply' ),
				  'label_submit'      => __( 'Post Comment' ),
				  'format'            => 'xhtml',

				  'comment_field' =>  '<p class="comment-form-comment"><textarea id="comment" placeholder="Share your thoughts." name="comment" cols="45" rows="8" aria-required="true">' .
				    '</textarea></p>',

				  'must_log_in' => '<p class="must-log-in">' .
				    sprintf(
				      __( 'You must be <a href="%s">logged in</a> to post a comment.' ),
				      wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
				    ) . '</p>',

				  'logged_in_as' => '',

				  'comment_notes_before' => '<p class="comment-notes">' .
				    __( 'Your email address will not be published.' ) . ( $req ? $required_text : '' ) .
				    '</p>',

				  'comment_notes_after' => '<p class="logged-in-as">' .
				    sprintf(
				    __( '<a href="%1$s" class="user">%2$s %3$s</a> <a href="%3$s" title="Log out of this account">Not %3$s?</a>' ),
				      admin_url( 'profile.php' ),
				      $avatar,
				      $user_identity,
				      wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) )
				    ) . '</p><p class="form-allowed-tags">' .
				    sprintf(
				      __( '<u>HTML tags and attributes allowed</u>' )  ) . '</p>',

				  'fields' => apply_filters( 'comment_form_default_fields', $fields ),
				);?>

		<?php comment_form($args); ?>

		<?php if ( have_comments() ) : ?>
			

			<ol class="commentlist">
				<?php wp_list_comments( array( 'callback' => 'buddyboss_comment', 'style' => 'ol' ) ); ?>
			</ol><!-- .commentlist -->

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment-nav-below" class="navigation" role="navigation">
				<h1 class="assistive-text section-heading"><?php _e( 'Comment navigation', 'buddyboss' ); ?></h1>
				<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'buddyboss' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'buddyboss' ) ); ?></div>
			</nav>
			<?php endif; // check for comment navigation ?>

			<?php
			/* If there are no comments and comments are closed, let's leave a note.
			 * But we only want the note on posts and pages that had comments in the first place.
			 */
			if ( ! comments_open() && get_comments_number() ) : ?>
			<p class="nocomments"><?php _e( 'Comments are closed.' , 'buddyboss' ); ?></p>
			<?php endif; ?>

		<?php endif; // have_comments() ?>

	</div><!-- #comments .comments-area -->

<?php endif; // comments_open() ?>