<?php

/**
 * displays the existing Eventbrite event in the event meta box
 * expects _EventBriteId is present when editing an event
 *
 * @package TribeEventsEventBrite
 * @since  3.0
 * @author Modern Tribe Inc.
 */
?>
<tr>
	<td colspan="2" class="snp_sectionheader">
		<h4><?php _e( 'Eventbrite Information', 'tribe-eventbrite' ); ?></h4>
	</td>
</tr>
<tr id="eventbrite-id-table">
	<td width="125">
		<?php _e( 'Eventbrite Event ID:','tribe-eventbrite' ); ?>
	</td>
	<td>
		<a target="_blank" href="http://www.eventbrite.com/edit?eid=<?php echo $event->id; ?>&ref=etckt"><?php echo $event->id; ?></a>
	</td>
</tr>
<tr>
	<td>
		<?php _e( 'Eventbrite Event Status:', 'tribe-eventbrite' ); ?>
	</td>
	<td>
		<?php if ( count( $event->ticket_classes ) > 0 ){ ?>
			<?php if ( ! in_array( $event->status, array( 'canceled', 'deleted' ) ) ) {  ?>
			<select name="EventBriteStatus" tabindex="<?php $tribe_ecp->tabIndex(); ?>">
				<option value='draft' <?php selected( $event->status, 'draft' ) ?>><?php _e( 'Draft', 'tribe-eventbrite' ); ?></option>
				<option value='live' <?php selected( $event->status, 'live' ) ?>><?php _e( 'Live', 'tribe-eventbrite' ); ?></option>
			</select>
			<?php } else { ?>
				<b><?php echo esc_attr( ucfirst( $event->status ) ); ?></b>
				<p class="tec_eb_status_cancel_notice"><?php _e( 'At this time, the event can only be made live again through the Quick Links in the My Events tab of your account dashboard.', 'tribe-eventbrite' ); ?> <a href="http://www.eventbrite.com/myevent?eid=<?php echo $event->id; ?>&ref=etckt"><?php _e( 'Manage Event', 'tribe-eventbrite' ); ?> &raquo;</a></p>
			<?php } ?>
		<?php } else { ?>
			<p><?php _e( 'This event was created without a ticket. You need to create a ticket before you can change this event\'s status.', 'tribe-eventbrite' ); ?> <a href="http://www.eventbrite.com/myevent?eid=<?php echo $_EventBriteId; ?>&ref=etckt"><?php _e('Manage Event', 'tribe-eventbrite'); ?> &raquo;</a></p>
		<?php } ?>
	</td>
</tr>
<tr>
	<td><?php _e('Eventbrite Event Privacy: ', 'tribe-eventbrite') ?></td>
	<td>
		<?php
		if ( isset( $event->listed ) && $event->listed ) {
			_e( 'Event is listed publicly on Eventbrite and search engines.', 'tribe-eventbrite' );
		} else {
			_e( 'Event is not listed publicly.', 'tribe-eventbrite' );
		}
		?><br />
		<small><a href="<?php echo esc_url('http://www.eventbrite.com/edit?eid=' . $event->id ) ?>#privacy_and_promotion_wrapper" target="_blank"><?php _e('Edit event privacy settings', 'tribe-eventbrite') ?></a></small>
	</td>
</tr>
<?php if ( ! empty( $event->ticket_classes ) ) { ?>
	<?php
	$ebTecMultipleCosts = false;
	$ebTecLastPrice = isset( $event->ticket_classes[0]->price ) ? $event->ticket_classes[0]->price : null;
	?>
	<tr>
		<td colspan="2" class="snp_sectionheader">
			<h4><?php _e('Associated Tickets:', 'tribe-eventbrite'); ?></h4>
			<p><?php _e('The following Eventbrite tickets are associated to this event', 'tribe-eventbrite') ?></p>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="snp_sectionheader">
			<table class="EB-table">
				<tr>
					<td width="120px"><?php esc_attr_e( 'Ticket', 'tribe-eventbrite' ); ?></td>
					<td width="85px"><?php esc_attr_e( 'Cost', 'tribe-eventbrite' ) ?></td>
					<td width="40px"><?php esc_attr_e( 'Sold', 'tribe-eventbrite' ); ?></td>
					<td width="40px"><?php esc_attr_e( 'Available', 'tribe-eventbrite' ); ?></td>
					<td width="100px"><?php esc_attr_e( 'End Sales', 'tribe-eventbrite' ); ?></td>
				</tr>
			<?php foreach ( $event->ticket_classes as $ticket ) {
				if ( ! $ebTecMultipleCosts && isset( $ticket->price ) ) {
					$ebTecMultipleCosts = ( $ticket->price == $ebTecLastPrice ) ? false : true;
					$ebTecLastPrice = $ticket->price;
				}
				?>
				<tr>
					<td><a href="<?php echo esc_url( $event->url ); ?>" target="_blank"><?php echo esc_attr( $ticket->name ); ?></a></td>
					<td>
					<?php
					if ( $ticket->free ) {
						esc_attr_e( 'Free', 'events-eventbrite' );
					} elseif ( $ticket->donation ){
						esc_attr_e( 'Donation', 'events-eventbrite' );
					} else {
						echo esc_attr( $ticket->cost->display );
					}
					?>
					</td>
					<td><?php echo isset( $ticket->quantity_sold ) ? $ticket->quantity_sold : '&nbsp;'; ?></td>
					<td><?php echo isset( $ticket->quantity_total ) ? $ticket->quantity_total : $event->capacity; ?></td>
					<td><?php echo isset( $ticket->sales_end ) ? date('Y-m-d', strtotime( $ticket->sales_end ) ) : date('Y-m-d', strtotime( $event->start->utc ) ); ?></td>
				</tr>
			<?php } ?>
				<tr>
					<td>
						<a id="edit-ticket" href="<?php echo esc_url( 'http://www.eventbrite.com/myevent?eid=' . $event->id . '/#viewtickets' ) ?>"><?php esc_attr_e( 'Edit existing tickets', 'tribe-eventbrite' ) ?></a><br>
						<a id="new-ticket" href="<?php echo esc_url( 'http://www.eventbrite.com/edit?eid=' . $event->id ) ?>">+ <?php esc_attr_e( 'Create a new ticket', 'tribe-eventbrite' ) ?></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?php } elseif ( ! isset( $event->status ) || 'draft' != $event->status ) { ?>
	<tr>
		<td colspan="2" class="snp_sectionheader">
			<h4><?php esc_attr_e( 'There are no tickets associated with this event!', 'tribe-eventbrite' ); ?></h4>
		 <div style='color:red'><?php esc_attr_e( 'You cannot publish this event in Eventbrite unless you first add a ticket on Eventbrite.com.', 'tribe-eventbrite' ); ?></div>
		</td>
	</tr>
<?php } ?>
<tr>
	<td colspan="2" class="snp_sectionheader">
		<h4><?php esc_attr_e( 'Eventbrite Shortcuts:', 'tribe-eventbrite' ); ?></h4>
	</td>
</tr>
<tr>
	<td colspan="2">
		<ul class='event_links'>
			<li><a href="http://www.eventbrite.com/myevent?eid=<?php echo absint( $event->id ); ?>&ref=etckt"><?php esc_attr_e( 'Manage my Event', 'tribe-eventbrite' ); ?></a></li>
			<li><a href="http://www.eventbrite.com/discounts?eid=<?php echo absint( $event->id ); ?>&ref=etckt"><?php esc_attr_e( 'Manage Discounts', 'tribe-eventbrite' ); ?></a></li>
			<li><a href="http://www.eventbrite.com/attendees?eid=<?php echo absint( $event->id ); ?>&ref=etckt"><?php esc_attr_e( 'Manage Attendees', 'tribe-eventbrite' ); ?></a></li>
			<li><a href="http://www.eventbrite.com/attendees-email?eid=<?php echo absint( $event->id ); ?>&ref=etckt"><?php esc_attr_e( 'Email Attendees', 'tribe-eventbrite' ); ?></a></li>
			<li><a href="http://www.eventbrite.com/attendees-badges?eid=<?php echo absint( $event->id ); ?>&ref=etckt"><?php esc_attr_e( 'Print Badges', 'tribe-eventbrite' ); ?></a></li>
			<li><a href="http://www.eventbrite.com/attendees-list?eid=<?php echo absint( $event->id ); ?>&ref=etckt"><?php esc_attr_e( 'Print Check-In List', 'tribe-eventbrite' ); ?></a></li>
		</ul>
	</td>
</tr>