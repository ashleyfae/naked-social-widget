<?php

/**
 * Naked Social Widget Site
 *
 * Social sites that auto-update.
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class NSW_Site {

	/**
	 * ID of the site. Used as the key in the options array.
	 *
	 * @var string
	 * @access public
	 * @since  0.3.0
	 */
	public $site_id;

	/**
	 * Profile URL
	 *
	 * @var string
	 * @access public
	 * @since  0.3.0
	 */
	public $profile_url;

	/**
	 * ID of the corresponding widget. Used when caching numbers.
	 *
	 * @var int
	 * @access public
	 * @since  0.3.0
	 */
	public $widget_id;

	/**
	 * API URL
	 *
	 * @var string
	 * @access protected
	 * @since  0.3.0
	 */
	protected $api_url;

	/**
	 * Number of followers
	 *
	 * @var int|false
	 * @access public
	 * @since  0.3.0
	 */
	public $number_followers;

	/**
	 * Whether or not the current numbers have expired.
	 *
	 * @var bool
	 * @access public
	 * @since  0.3.0
	 */
	public $is_expired = false;

	/**
	 * Whether or not to cache results.
	 *
	 * @var bool
	 * @access public
	 * @since  0.3.0
	 */
	public $cache = true;

	/**
	 * Cache length in seconds.
	 *
	 * @var int
	 * @access public
	 * @since  0.3.0
	 */
	public $cache_length = 21600;

	/**
	 * NSW_Site constructor.
	 *
	 * @param string $profile_url Profile URL to get follower number for.
	 * @param int    $widget_id   ID of the corresponding widget.
	 *
	 * @access public
	 * @since  0.3.0
	 * @return void
	 */
	public function __construct( $profile_url, $widget_id = 0 ) {

		$this->profile_url = $profile_url;
		$this->widget_id   = $widget_id;

	}

	/**
	 * Set Cache Length
	 *
	 * @param int $length Desired length in seconds.
	 *
	 * @access public
	 * @since  0.3.0
	 * @return void
	 */
	public function set_cache_length( $length ) {
		$this->cache_length = absint( $length );
	}

	/**
	 * Get Follower Number
	 *
	 *      + Fetches saved numbers.
	 *      + If saved numbers are expired, query for new ones.
	 *      + Save the result.
	 *
	 * @access public
	 * @since  0.3.0
	 * @return int
	 */
	public function get_number() {

		$saved_numbers = $this->get_saved_number();

		// Return these saved numbers if they haven't expired.
		if ( false === $this->is_expired && true === $this->cache ) {
			return apply_filters( 'naked-social-widget/site/get-number', $saved_numbers, $this->site_id, $this );
		}

		// Otherwise, fetch new numbers.
		$new_number = $this->get_new_number();

		// Set the property, depending on the result of `$new_number`.
		if ( is_numeric( $new_number ) ) {
			$this->number_followers = $new_number;
		} else {
			// If we have an error, use saved numbers.
			$this->number_followers = $saved_numbers;
		}

		// If we have caching enabled, save this new number.
		$this->save_number( $this->number_followers );

		// Return the result.
		return apply_filters( 'naked-social-widget/site/get-number', $this->number_followers, $this->site_id, $this );
	}

	/**
	 * Get Saved Number
	 *
	 * Returns the saved follower number. Also sets `$is_expired`.
	 *
	 * @access public
	 * @since  0.3.0
	 * @return int|bool Number of followers or false if none exist yet.
	 */
	public function get_saved_number() {

		$all_numbers            = get_option( 'nsw_followers_' . absint( $this->widget_id ), array() );
		$this->number_followers = ( is_array( $all_numbers ) && array_key_exists( $this->site_id, $all_numbers ) ) ? absint( $all_numbers[ $this->site_id ] ) : false;

		if ( ! is_array( $all_numbers ) || ! array_key_exists( 'expires', $all_numbers ) || false === $this->number_followers ) {
			$this->is_expired = true;
		} elseif ( is_array( $all_numbers ) && array_key_exists( 'expires', $all_numbers ) && $all_numbers['expires'] < time() ) {
			$this->is_expired = true;
		} else {
			$this->is_expired = false;
		}

		// Switch followers to zero.
		if ( false === $this->number_followers ) {
			$this->number_followers = 0;
		}

		return apply_filters( 'naked-social-widget/site/get-saved-number', $this->number_followers, $this->is_expired, $this->site_id, $this );

	}

	/**
	 * Get New Number
	 *
	 * Call the API to fetch a new follower number.
	 *
	 * @access public
	 * @since  0.3.0
	 * @return int|WP_Error Follower number on success or WP_Error on failure.
	 */
	public function get_new_number() {

		$response = wp_remote_get( $this->api_url );

		// Invalid response.
		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'invalid-response', __( 'Invalid response.', 'naked-social-widget' ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		// @todo something here to get numbers

		// @todo return numbers

	}

	/**
	 * Save Number
	 *
	 * @param int  $number        Number to save.
	 * @param bool $update_expiry Whether or not to update the expiry time.
	 *
	 * @access public
	 * @since  0.3.0
	 * @return bool Whether or not the update was successful.
	 */
	public function save_number( $number = 0, $update_expiry = true ) {

		$all_numbers = get_option( 'nsw_followers_' . absint( $this->widget_id ), array() );

		if ( ! is_array( $all_numbers ) ) {
			$all_numbers = array();
		}

		$all_numbers[ $this->site_id ] = absint( $number );

		if ( $update_expiry ) {
			$all_numbers['expires'] = time() + $this->cache_length;
		}

		return update_option( 'nsw_followers_' . absint( $this->widget_id ), $all_numbers );

	}

}