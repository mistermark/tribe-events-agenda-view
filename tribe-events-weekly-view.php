<?php
/*
 Plugin Name: The Events Calendar: Weekly View
 Description: This plugin adds an weekly view to your Tribe The Events Calendar suite.
 Version: 0.1
 Author: Mark de Jong
 Author URI: http://www.markdejong.com
 Text Domain: 'tribe-event-weekly-view'
 License: GPLv2 or later

Copyright 2012-2013 by Mark de Jong and contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Organizer icon by [Lynn Christensen](http://thenounproject.com/term/user/4520/)
Location icon by [Steven Walker](http://thenounproject.com/term/map-marker/6680/)

*/

if ( !defined( 'ABSPATH' ) )
	die( '-1' );

if ( ! class_exists( 'CalendarWeekly' ) ) {
	class CalendarWeekly {

		protected static $instance;

		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;

		public $weeklySlug = 'weekly';

		const PLUGIN_NAME = 'The Events Calendar: Weekly View';
		const DOMAIN = 'tribe-event-weekly-view';
		const MIN_WP_VERSION = '3.5';
		const REQUIRED_TEC_VERSION = '3.0';

		function __construct() {

			$this->pluginPath = trailingslashit( dirname( __FILE__ ) );
			$this->pluginDir = trailingslashit( basename( $this->pluginPath ) );
			$this->pluginUrl = trailingslashit( plugins_url().'/'.$this->pluginDir );
			$this->weeklySlug = sanitize_title(__('weekly', 'tribe-event-weekly-view'));

			require_once( 'template-tags.php' );
			require_once( 'tribe-weekly-view-template-class.php' );

			// settings tab
			add_action( 'tribe_settings_do_tabs', array( $this, 'settings_tab' ) );

			// inject weekly view into events bar & (display) settings
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_weekly_in_bar' ), 40, 1 );

			// setup permalink routes
			add_filter( 'generate_rewrite_rules', array( $this, 'add_routes' ) );

			// make sure everything is ready in the query for weekly
			add_filter( 'tribe_events_pre_get_posts', array( $this, 'pre_get_posts'));

			// instantiate the template class
			add_action( 'template_redirect', array( $this, 'setup_template_class') );

			// load the proper template hooks (weekly) for the permalink
			add_filter( 'tribe_current_events_page_template', array( $this, 'select_page_template' ) );

			// add this plugin path to the views path
			add_filter( 'tribe_events_template_paths', array( $this, 'template_paths' ) );

		}

		public function settings_tab() {

			$settings = array(
				'priority' => 20,
				'fields' => array(
					'info-start' => array(
						'type' => 'html',
						'html' => '<div id="modern-tribe-info">'
					),
					'info-box-title' => array(
						'type' => 'html',
						'html' => '<h2>' . __('Weekly View', 'tribe-event-weekly-view') . '</h2>',
					),
					'info-box-description' => array(
						'type' => 'html',
						'html' => '<p>' . __('Customize your weekly view layout.', 'tribe-event-weekly-view') . '</p>',
					),
					'info-end' => array(
						'type' => 'html',
						'html' => '</div>',
					),
					'form-content-start' => array(
						'type' => 'html',
						'html' => '<div class="tribe-settings-form-wrap">',
					),
					'weeklyViewLimit' => array(
						'type' => 'text',
						'label' => __( 'Limit Events', 'tribe-event-weekly-view' ),
						'tooltip' => __( 'Limit the amount of events shown per day on the Weekly View.', 'tribe-event-weekly-view' ),
						'size' => 'small',
						'default' => get_option( 'posts_per_page' ),
						'validation_type' => 'positive_int'
					),
					'form-content-end' => array(
						'type' => 'html',
						'html' => '</div>',
					),
				)
			);

			// instantiate the tab (positioned order before core help tab)
			new TribeSettingsTab( 'weekly', __( 'Weekly', 'tribe-event-weekly-view' ), $settings );
		}

		function setup_weekly_in_bar( $views ) {
			$views[] = array( 'displaying' => 'weekly',
												'anchor'		 => __( 'Weekly', 'tribe-event-weekly-view' ),
												'url'				=> tribe_get_weekly_permalink() );
			return $views;
		}

		function add_routes( $wp_rewrite ){

			// create new rules for the weekly permalinks
			$newRules = array();
			$newRules[trailingslashit( TribeEvents::instance()->rewriteSlug ) . trailingslashit($this->weeklySlug) . '?$'] = 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=weekly';

			$wp_rewrite->rules = $newRules + $wp_rewrite->rules;

		}

		function pre_get_posts( $query ){
			$weekly_query = false;
			$query->tribe_is_weekly = false;
			if(!empty( $query->query_vars['eventDisplay'] )) {
				$weekly_query = true;
				if ( $query->query_vars['eventDisplay'] == 'weekly' ) {

					$event_date = $query->get('eventDate') != '' ? $query->get('eventDate') : tribe_event_beginning_of_day( Date('Y-m-d') );
					$week = get_weekstartend($event_date);
					$weekStart = Date("Y-m-d H:i:s", $week['start']);
					$weekEnd = Date("Y-m-d H:i:s", $week['end']);
					
					$query->set( 'start_date', $weekStart );
					$query->set( 'end_date', $weekEnd );
					$query->set( 'eventDate', $event_date );
					$query->set( 'orderby', 'event_date' );
					$query->set( 'order', 'ASC' );
					$query->set( 'posts_per_page', tribe_get_option( 'weeklyViewLimit', '10' ) ); // show ALL day posts
					$query->set( 'hide_upcoming', false );
					$query->tribe_is_weekly = true;
				}
			}
			$query->tribe_is_event_weekly_query = $weekly_query;
			return $query->tribe_is_event_weekly_query ? apply_filters('tribe_events_weekly_pre_get_posts', $query) : $query;
		}

		function setup_template_class () {
			if (tribe_is_weekly()) {
				tribe_initialize_view('Tribe_Events_Weekly_Template');
			}
		}

		function select_page_template( $template ){
			// weekly view
			if( tribe_is_weekly() ) {
				$template = TribeEventsTemplates::getTemplateHierarchy('weekly');
			}
			return $template;
		}

		/**
		 * Check the minimum WP version and if TribeEvents exists
		 *
		 * @static
		 * @param string $wp_version
		 * @return bool Whether the test passed
		 */
		public static function prerequisites( $wp_version = null ) {;
			$pass = TRUE;
			$pass = $pass && class_exists( 'TribeEvents' );
			$pass = $pass && version_compare( is_null( $wp_version ) ? get_bloginfo( 'version' ) : $wp_version, self::MIN_WP_VERSION, '>=' );
			return $pass;
		}

    /**
     * Display a failure notice in the WordPress admin if the versions are not compatible
     *
     * @return void
     */

		public function fail_notices() {
			printf( '<div class="error"><p>%s</p></div>', sprintf( __( '%1$s requires WordPress v%2$s or higher and The Events Calendar v%3$s or higher.' ), self::PLUGIN_NAME, self::MIN_WP_VERSION, self::REQUIRED_TEC_VERSION ) );
		}

    /**
     * Static Singleton Factory Method
     *
     * @return CalendarWeekly
     **/
    public static function instance() {
         if ( !isset( self::$instance ) ) {
              $className = __CLASS__;
              self::$instance = new $className;
         }
         return self::$instance;
    }

		/**
		 * Add weekly plugin path to the templates array
		 *
		 * @param $template_paths array
		 * @return array
		 * @since 1.0
		 **/
		function template_paths( $template_paths = array() ) {

			array_unshift($template_paths, $this->pluginPath);
			return $template_paths;

		}

	}

	/**
	 * Instantiate class and set up WordPress actions.
	 *
	 * @return void
	 */
	function Load_CalendarWeekly() {
		if ( apply_filters( 'tec_rating_pre_check', class_exists( 'CalendarWeekly' ) && CalendarWeekly::prerequisites() ) ) {
			add_action( 'init', array( 'CalendarWeekly', 'instance' ), -100, 0 );
		} else {
			// let the user know prerequisites weren't met
			add_action( 'admin_head', array( 'CalendarWeekly', 'fail_notices' ), 0, 0 );
		}
	}
	add_action( 'plugins_loaded', 'Load_CalendarWeekly', 1 ); // high priority so that it's not too late for addon overrides

}
