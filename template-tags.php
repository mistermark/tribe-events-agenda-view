<?php

	/**
	 * Is Weekly View
	 *
	 * @return bool
	 * @since 3.0
	 */
	function tribe_is_weekly()  {
		$is_weekly = (TribeEvents::instance()->displaying == 'weekly') ? true : false;
		return apply_filters('tribe_is_weekly', $is_weekly);
	}

	function get_day_of_week($date) {
		var_dump($date);
	}

	/**
	 * Get week permalink
	 * 
	 * @param string $week
	 * @return string $permalink
	 * @since 3.0
	 */
	function tribe_get_weekly_permalink( $set_date = null ){
		$tec = TribeEvents::instance();
		$set_date = is_null($set_date) ? '' : date('Y-m-d', strtotime( $set_date ) );
		$permalink = get_site_url() . '/' . $tec->rewriteSlug . '/' . trailingslashit( CalendarWeekly::instance()->weeklySlug . '/' . $set_date );
		return apply_filters('tribe_get_weekly_permalink', $permalink);
	}