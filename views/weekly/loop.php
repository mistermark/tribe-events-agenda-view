<?php 
/**
 * Weekly View Loop
 * This file sets up the structure for the weekly loop
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/weekly/loop.php
 *
 * @package CalendarWeekly
 * @since  0.1
 * @author Mark de Jong
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php 

global $more, $wp_query;
$more = false;
// $current_timeslot = null;
$days_of_week = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
$wday = null;
?>

<div class="tribe-events-loop hfeed vcalendar tribe-weekly-view clearfix">
	
	<?php $n=0; foreach($days_of_week as $day) { $n++; ?>
	<div class="tribe-events-day-time-slot">

		<h5><?php echo $day; ?></h5>

		<?php
		
			while ( have_posts() ) : the_post(); global $post;
				$post_date = getdate(strtotime($post->timeslot));
				if($post_date['wday'] == $n) {
				do_action( 'tribe_events_inside_before_loop');
				?>

				<!-- Event  -->
				<div id="post-<?php the_ID() ?>" class="<?php tribe_events_event_classes() ?>">
					<?php tribe_get_template_part( 'weekly/single', 'event' ) ?>
				</div><!-- .hentry .vevent -->
			
			<?php
				do_action( 'tribe_events_inside_after_loop' );
			}
			?>
		<?php endwhile; ?>

	</div><!-- .tribe-events-day-time-slot -->
	<?php } ?>

</div><!-- .tribe-events-loop -->
