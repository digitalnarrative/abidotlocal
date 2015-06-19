<?php
/**
 * Photo View Single Event
 * This file contains one event in the photo view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/photo/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<?php

global $post;

?>

<div class="tribe-events-photo-event-wrap">

	<div class="events-main-location" style="background-image:url(<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( (int) tribe_get_organizer_id( $post->ID ) ), 'full' )[0] ?>)"><?php echo tribe_get_organizer(); ?></div>

	<?php echo tribe_event_featured_image( null, 'medium' ); ?>

	<div class="tribe-events-event-details tribe-clearfix">
	
		<div class="events-main-date">
			<?php $abi_date = explode(" ", tribe_get_start_date( null, false, 'M j' ) );
			echo '<p>' . $abi_date[0] . '<span>' . $abi_date[1] . '</span></p>'; ?>
		</div>
		<div class="events-main-content">
		<!-- Event Title -->
		<?php do_action( 'tribe_events_before_the_event_title' ); ?>
		<h2 class="tribe-events-list-event-title entry-title summary">
			<a class="url" href="<?php echo esc_url( tribe_get_event_link() ); ?>" title="<?php the_title() ?>" rel="bookmark">
				<?php the_title(); ?>
			</a>
		</h2>
		<?php do_action( 'tribe_events_after_the_event_title' ); ?>

		<!-- Event Venue -->
		<div class="events-main-venue"><?php echo tribe_get_venue(); ?></div>

		<!-- Event Meta -->
		<?php do_action( 'tribe_events_before_the_meta' ); ?>
		<div class="tribe-events-event-meta">
			<div class="updated published time-details">
				<?php if ( ! empty( $post->distance ) ) : ?>
					<strong>[<?php echo tribe_get_distance_with_unit( $post->distance ); ?>]</strong>
				<?php endif; ?>
				<?php echo tribe_events_event_schedule_details(); ?>
			</div>
		</div><!-- .tribe-events-event-meta -->
		<?php do_action( 'tribe_events_after_the_meta' ); ?>

		<!-- Event Content -->
		<?php do_action( 'tribe_events_before_the_content' ); ?>
		<div class="tribe-events-list-photo-description tribe-events-content entry-summary description">
			<?php echo substr( get_the_excerpt(), 0, 105) . ' ...'; ?>
		</div>
		<?php do_action( 'tribe_events_after_the_content' ) ?>
		</div>
	</div><!-- /.tribe-events-event-details -->

</div><!-- /.tribe-events-photo-event-wrap -->