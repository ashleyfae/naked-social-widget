<?php

/**
 * Base class for getting social site details.
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class Naked_Social_Widget_Site {

	/**
	 * Type of username to enter
	 *
	 * @var string Should be `username` or `url`
	 * @access public
	 * @since  1.0
	 */
	public $profile_type = 'username';

	/**
	 * Name of the option in the wp_options table.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $cache_name;

	/**
	 * Key for this site in the cache.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $cache_key;

	/**
	 * API URL - Used to fetch the follower count.
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $api_url;

	/**
	 * Site Username
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $username;

	/**
	 * Follower Number
	 *
	 * @var int|bool False on failure
	 * @since 1.0
	 */
	protected $followers = false;

	/**
	 * Naked_Social_Widget_Site constructor.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Setup the Site
	 *
	 * @param string $username  Social site username
	 * @param string $widget_id Widget ID
	 * @param string $key       Array value in the cache
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function setup( $username, $widget_id, $key = '' ) {

		$this->set_cache_name( $widget_id . '_followers' );
		$this->set_cache_key( $key );
		$this->set_username( $username );
		$this->set_api_url();

	}

	/**
	 * Set Cache Name
	 *
	 * Sets the name of the option used to store cached values.
	 *
	 * @param string $name
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function set_cache_name( $name ) {
		$this->cache_name = $name;
	}

	/**
	 * Set Cache Key
	 *
	 * Sets the array key for this site - used in the cache option.
	 *
	 * @param string $key
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function set_cache_key( $key ) {
		$this->cache_key = $key;
	}

	/**
	 * Set API URL
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function set_api_url() {

	}

	/**
	 * Get Followers
	 *
	 * @access public
	 * @since  1.0
	 * @return int|bool False on failure
	 */
	public function get_followers() {

	}

	/**
	 * Set Username
	 *
	 * @param string $username
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function set_username( $username ) {
		$this->username = $username;
	}

	/**
	 * Update Cache
	 *
	 * Changes the follower number for this social site in the cache.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function update_cache() {

		if ( ! is_numeric( $this->followers ) ) {
			return;
		}

		$default = array( 'numbers' => array(), 'expires' => false );
		$cache   = get_option( $this->cache_name );
		$cache   = is_array( $cache ) ? $cache : $default;

		$cache['numbers'][ $this->cache_key ] = $this->followers;

		update_option( $this->cache_name, $cache );

	}

	/**
	 * Get Profile URL from Username
	 *
	 * @param string $username Site username.
	 *
	 * @since 1.0
	 * @return string Full URL to profile page.
	 */
	public function get_profile_url( $username ) {
		return $username;
	}

}