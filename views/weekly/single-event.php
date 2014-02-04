<?php 
/**
 * Weekly View Single Event
 * This file contains one event in the weekly view
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/weekly/single-event.php
 *
 * @package CalendarWeekly
 * @since  0.1
 * @author Mark de Jong
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php 

// Setup an array of venue details for use later in the template
$event_details = array();

if ($venue_name = tribe_get_meta( 'tribe_event_venue_name' ) ) {
	$event_details['Venue'] = $venue_name;	
}

if ($organizer_name = tribe_get_meta( 'tribe_event_organizer_name' ) ) {
	$event_details['Organizer'] = $organizer_name;
}

// Venue microformats
$has_venue = ( $event_details ) ? ' vcard': '';
$has_organizer_name = ( $organizer_name ) ? ' location': '';
?>

<!-- Event Title -->
<div class="weekly-event-block">
	
	<?php do_action( 'tribe_events_before_the_event_title' ) ?>
	<?php $start = tribe_get_start_date( $post, FALSE, 'G:i' ); ?>
	<h3 class="event-time">
		<?php echo $start; ?>
	</h3>
	<h2 class="entry-title summary">
		<a class="url" href="<?php echo tribe_get_event_link() ?>" title="<?php the_title() ?>" rel="bookmark"><?php the_title() ?></a>
	</h2>
	<?php do_action( 'tribe_events_after_the_event_title' ) ?>

	<!-- Event Meta -->
	<?php do_action( 'tribe_events_before_the_meta' ) ?>
	<div class="tribe-events-event-meta">
		<h3 class="updated published">
			<?php
			global $post;
			if ( !empty( $post->distance ) ) { ?>
				<strong><?php echo '['. tribe_get_distance_with_unit( $post->distance ) .']'; ?></strong>
			<?php } ?>
		</h3>

		<?php if ( $event_details ) : foreach($event_details as $detail => $value) { ?>
			<!-- Venue Display Info -->
			<?php $meta = ($detail == 'Organizer') ? 'Instructor' : $detail; ?>
			<div class="tribe-events-event-details<?php echo ' event-meta-'.strtolower($meta); ?>">
				<i></i>
				<span><?php echo strip_tags($value); ?></span>
			</div> <!-- .tribe-events-venue-details -->
		<?php
		}; endif;
		?>

	</div><!-- .tribe-events-event-meta -->
	<?php do_action( 'tribe_events_after_the_meta' ) ?>
</div> <!-- .weekly-event-heading -->
