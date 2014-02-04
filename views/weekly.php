<?php
/**
 * Weekly View Template
 * The wrapper template for the sample weekly view plugin. 
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/weekly.php
 *
 * @package CalendarWeekly
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php do_action( 'tribe_events_before_template' ); ?>

<!-- Tribe Bar -->
<?php tribe_get_template_part( 'modules/bar' ); ?>

<!-- Main Events Content -->
<?php tribe_get_template_part( 'weekly/content' ); ?>

<div class="tribe-clear"></div>

<?php do_action( 'tribe_events_after_template' ) ?>