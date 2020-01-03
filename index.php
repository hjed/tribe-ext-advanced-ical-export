<?php
/**
 * Plugin Name:       The Events Calendar Extension: Advanced iCal Export
 * Plugin URI:        https://theeventscalendar.com/extensions/advanced-ical-export/
 * GitHub Plugin URI: https://github.com/mt-support/tribe-ext-advanced-ical-export/
 * Description:       The extension gives you advanced export possibilities through the iCal feed.
 * Version:           1.1.0
 * Extension Class:   Tribe__Extension__Advanced_iCal_Export
 * Author:            Modern Tribe, Inc.
 * Author URI:        http://m.tri.be/1971
 * License:           GPL version 3 or any later version
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       tribe-ext-advanced-ical-export
 *
 *     This plugin is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     any later version.
 *
 *     This plugin is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *     GNU General Public License for more details.
 */

// Do not load unless Tribe Common is fully loaded and our class does not yet exist.
if ( class_exists( 'Tribe__Extension' ) && ! class_exists( 'Tribe__Extension__Advanced_iCal_Export' ) ) {
	/**
	 * Extension main class, class begins loading on init() function.
	 */
	class Tribe__Extension__Advanced_iCal_Export extends Tribe__Extension {

		/**
		 * Setup the Extension's properties.
		 *
		 * This always executes even if the required plugins are not present.
		 */
		public function construct() {
			$this->add_required_plugin( 'Tribe__Events__Main' );
		}

		/**
		 * Extension initialization and hooks.
		 */
		public function init() {
			// Load plugin textdomain
			load_plugin_textdomain( 'tribe-ext-advanced-ical-export', false, basename( dirname( __FILE__ ) ) . '/languages/' );

			/**
			 * Protect against fatals by specifying the required minimum PHP
			 * version. Make sure to match the readme.txt header.
			 */
			$php_required_version = '5.6';

			if ( version_compare( PHP_VERSION, $php_required_version, '<' ) ) {
				if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
					$message  = '<p>';
					$message .= sprintf( __( '%s requires PHP version %s or newer to work. Please contact your website host and inquire about updating PHP.', 'tribe-ext-advanced-ical-export' ), $this->get_name(), $php_required_version );
					$message .= sprintf( ' <a href="%1$s">%1$s</a>', 'https://wordpress.org/about/requirements/' );
					$message .= '</p>';
					tribe_notice( $this->get_name(), $message, 'type=error' );
				}
				return;
			}

			// Insert filters and hooks here
			add_filter( 'tribe_ical_feed_month_view_query_args', array( $this, 'filter_ical_query' ) );
			add_filter( 'tribe_events_ical_events_list_args', array( $this, 'filter_ical_query' ) );
			add_filter( 'tribe_ical_feed_posts_per_page', array($this, 'filter_ical_posts_per_page' ) );
			add_filter( 'tribe_ical_properties', array( $this, 'filter_ical_feed_properties' ) );
			add_action( 'init', array( $this, 'ical_rewrite_rule' ) );
			add_action( 'pre_get_posts', array( $this, 'add_ical_query_vars' ) );
		}

		/**
		 * Add refresh-interval to ical feed.
		 *
		 * @param $query
		 */
		public function filter_ical_feed_properties ( $content ) {

			$content .= "REFRESH-INTERVAL;VALUE=DURATION:PT1H\r\n";
			$content .= "X-PUBLISHED-TTL:PT1H\r\n";

			return $content;

		}


		/**
		 * It sucks to hardcode start/end dates into a feed, so let's hide the query string
		 * behind a friendly rewritten url.
		 *
		 * @param $query
		 */
		public function ical_rewrite_rule () {
			$start_date = new DateTime();
			$end_date = new DateTime();

			$start_date->modify('-3 months');
			$end_date->modify('+1 year');

			$qs = http_build_query( array(
				'post_type' => 'tribe_events',
				'eventDisplay' => 'custom',
				'ical' => 1,
				'start_date' => $start_date->format('Y-m-d'),
				'end_date' => $end_date->format('Y-m-d')
			) );

			add_rewrite_rule(
				'ical\??.*/?$',
				'index.php?' . $qs,
				'top'
			);
		}

		/**
		 * This plugin relies on values in $_GET that aren't set if we're behind the
		 * rewritten url /ical. We set those values here.
		 *
		 * @param $query
		 */
		public function add_ical_query_vars ($query) {
			if ($query->query['ical'] != 1) {
				return;
			}

			$_GET['ical'] = 1;
			$_GET['tribe_display'] = $query->query['eventDisplay'];
			$_GET['start_date'] = $query->query['start_date'];
			$_GET['end_date'] = $query->query['end_date'];
		}

		/**
		 * Tribe__Events__iCal::feed_posts_per_page() doesn't allow posts_per_page to be
		 * set to -1, and defaults to 30 if it is. We override this to an arbitrarily large
		 * number here.
		 *
		 * @param $count
		 *
		 * @return int
		 */
		public function filter_ical_posts_per_page( $count ) {
			if ( ! $this->is_ical_query() ) {
				return;
			}

			return 9999;
		}

		private function is_ical_query() {
			$filters = [
				'ical'					=> FILTER_SANITIZE_NUMBER_INT
			];
			$vars = filter_var_array( $_GET, $filters );

			// If ical is not set in the URL then bail
			if ( ! isset( $vars['ical'] ) || $vars['ical'] != 1 ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Filtering the query for dates
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function filter_ical_query( $args ) {

			$filters = [
				'ical'          => FILTER_SANITIZE_NUMBER_INT,
				'tribe_display' => FILTER_SANITIZE_STRING,
				'start_date'    => FILTER_SANITIZE_STRING,
				'end_date'      => FILTER_SANITIZE_STRING,
			];
			$vars = filter_var_array( $_GET, $filters );

			// If ical is not set in the URL then bail
			if ( ! isset( $vars['ical'] ) || $vars['ical'] != 1 ) {
				return;
			}


			if ( $vars['tribe_display'] === 'custom' ) {

				// Check if there is a start_date set
				if ( isset( $vars['start_date'] ) && ! empty( $vars['start_date'] ) ) {
					// Full date
					if ( $this->validate_date( $vars['start_date'], 'Y-m-d' ) ) {
						$start_of_year = $vars['start_date'];
					}
					// Only year, then from beginning of that year
					elseif ( $this->validate_date( $vars['start_date'], 'Y' ) ) {
						$start_of_year = $vars['start_date'] . '-01-01';
					}
					// If set to anything else then fall back to this year's beginning
					else {
						$start_of_year = date( 'Y' ) . '-01-01';
					}
				}
				// If not, fall back to this year's beginning
				else {
					$start_of_year = date( 'Y' ) . '-01-01';
				}

				// Check if there is an end_date set
				if ( isset( $vars['end_date'] ) && ! empty( $vars['end_date'] ) ) {
					// Full date
					if ( $this->validate_date( $vars['end_date'], 'Y-m-d' ) ) {
						$end_of_year = $vars['end_date'];
					}
					// If only year is submitted, then until end of that year (Max. 3 years ahead)
					elseif ( $this->validate_date( $vars['end_date'], 'Y' ) && date( 'Y' ) <= $vars['end_date'] && $vars['end_date'] <= date( 'Y' ) + 3 ) {
						$end_of_year = $vars['end_date'] . '-12-31';
					}
				}
				// If there is no end date but there was a start year defined, then till the end of that year
				elseif ( $this->validate_date( $vars['start_date'], 'Y' ) ) {
					$end_of_year = $vars['start_date'] . '-12-31';
				}
				// If no end date defined, fall back to this year's end
				else {
					$end_of_year = date( 'Y' ) . '-12-31';
				}

				// Adding one day to the end date to include the submitted end day
				$end_of_year = date( 'Y-m-d', strtotime( $end_of_year . '+1 days' ) );

				$args['eventDisplay']   = 'custom';
				$args['start_date']     = $start_of_year;
				$args['end_date']       = $end_of_year;
				$args['posts_per_page'] = -1;
				$args['hide_upcoming']  = true;
				$args['tribe_remove_date_filters'] = true;

			}

			return $args;
		}

		/**
		 * Validates the date
		 *
		 * param $date
		 *
		 * @param string $format
		 *
		 * @return bool
		 */
		public function validate_date( $date, $format = 'Y-m-d H:i:s' ) {
			$d = DateTime::createFromFormat( $format, $date );

			return $d && $d->format( $format ) == $date;
		}

	} // end class
} // end if class_exists check
