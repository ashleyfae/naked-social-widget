<?php

/**
 * Get Goodreads Friends
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class NSW_Goodreads extends Naked_Social_Widget_Site {

	/**
	 * Set API URL
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	protected function set_api_url() {

		$url = 'https://www.nosegraze.com/ubb/grapi.php';
		$url = add_query_arg( array( 'username' => urlencode( $this->username ) ), $url );

		$this->api_url = $url;

	}

	/**
	 * Get Followers
	 *
	 * @access public
	 * @since  1.0
	 * @return int|bool False on failure
	 */
	public function get_followers() {

		$response = wp_remote_get( $this->api_url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		// The response code wasn't 200, bail.
		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			return false;
		}

		// Otherwise, let's get that follower number, biatch!
		$body = json_decode( wp_remote_retrieve_body( $response ) );

		// The data doesn't exist, bail.
		if ( ! $body->contact || ! $body->exists || ! $body->count || $body->count == 0 ) {
			return false;
		}

		$this->followers = $body->count;

		return $body->count;

	}

}