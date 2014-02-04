<?php
/**
 * @for Day Template
 * This file contains hooks and functions required to set up the day view.
 *
 * @package CalendarWeekly
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists('Tribe_Events_Weekly_Template')){
	class Tribe_Events_Weekly_Template extends Tribe_Template_Factory {

		protected $body_class = 'tribe-events-weekly';

		/**
		 * Constructor
		 *
		 * @return void
		 * @author Modern Tribe
		 **/
		public function __construct() {
			parent::__construct();
			wp_enqueue_style( 'tribe-weekly-view', CalendarWeekly::instance()->pluginUrl . "inc/css/weekly-view.css", array(), '0.1', 'screen' );
			wp_enqueue_script( 'tribe-weekly-view-scripts', CalendarWeekly::instance()->pluginUrl . "inc/js/weekly-view.js", array('jquery'), null, true );			
		}

		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 * @since 1.0
		 **/
		public function hooks() {

			parent::hooks();

			add_filter( 'tribe_get_ical_link', array( $this, 'ical_link' ), 20, 1 );
			add_filter( 'tribe_events_header_attributes',  array( $this, 'header_attributes' ) );
			add_filter( 'tribe_get_events_title',  array( $this, 'the_title' ) );
		}

		/**
		 * Filter the view title for Weekly view
		 *
		 * @return void
		 * @author 
		 **/
		function the_title( $title ) {
			if ( tribe_is_weekly() ) {
				global $wp_query;
				$format = "F jS Y";
				$title = sprintf( '%1$s %2$s %3$s %4$s',
					__('Week from ', 'tribe-event-weekly-view'), 
					Date($format, strtotime($wp_query->get('start_date')) ),
					__(' to ', 'tribe-event-weekly-view'), 
					Date($format, strtotime($wp_query->get('end_date')) )
				);
		}

	    return $title;
    
    }

		/**
		 * Add header attributes for day view
		 *
		 * @return string
		 * @since 1.0
		 **/
		public function header_attributes( $attrs ) {

			global $wp_query;
			$current_day = $wp_query->get('start_date');

			$attrs['data-view'] = 'day';
			$attrs['data-baseurl'] = tribe_get_day_link( $current_day );
			$attrs['data-date'] = Date('Y-m-d', strtotime( $current_day) );
			$attrs['data-header'] = Date("l, F jS Y", strtotime( $current_day ) );

			return apply_filters('tribe_events_pro_header_attributes', $attrs);
		}

		public function ical_link( $link ){
			global $wp_query;
			$day = $wp_query->get('start_date');
			return trailingslashit( esc_url(trailingslashit( tribe_get_day_link( $day ) ) . 'ical' ) );
		}

		/**
		 * Organize and reorder the events posts according to time slot
		 *
		 * @return void
		 * @since 1.0
		 **/
		public function setup_view() {

			global $wp_query;

			if ( $wp_query->have_posts() ) {
				foreach ( $wp_query->posts as &$post ) {
					$post->timeslot = tribe_event_is_all_day( $post->ID )
						? __( 'All Day', 'tribe-events-calendar-pro' )
						: $post->timeslot = tribe_get_start_date( $post, false, 'l, F jS Y g:i A' );
				}
				$wp_query->rewind_posts();
			}

		}
	}
}