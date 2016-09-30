<?php

/**
 * Fetch Follower Number from Twitter
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class NSW_Twitter extends NSW_Site {

	/**
	 * ID of the site. Used as the key in the options array.
	 *
	 * @var string
	 * @access public
	 * @since  0.3.0
	 */
	public $site_id = 'twitter';

	/**
	 * API URL
	 *
	 * @var string
	 * @access protected
	 * @since  0.3.0
	 */
	protected $api_url = 'https://www.nosegraze.com/ubb/twitter.php';

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

		$username = $this->get_username();

		if ( empty( $username ) ) {
			return new WP_Error( 'missing-username', __( 'Username not found.', 'naked-social-widget' ) );
		}

		$url = add_query_arg( array(
			'username' => urlencode( $username )
		), $this->api_url );

		$response = wp_remote_get( $url );

		// Invalid response checks.
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'invalid-response', __( 'Invalid response.', 'naked-social-widget' ) );
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'invalid-response', __( 'Invalid response.', 'naked-social-widget' ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! $body->success || ! $body->followers ) {
			return new WP_Error( 'number-not-found', __( 'Unable to find Twitter follower number.', 'naked-social-widget' ) );
		}

		return apply_filters( 'naked-social-widget/site/get-new-number', $body->followers, $this->site_id, $this );

	}

	/**
	 * Get Username from URL
	 *
	 * @access public
	 * @since  0.3.0
	 * @return string
	 */
	public function get_username() {

		$url      = untrailingslashit( $this->profile_url );
		$pieces   = explode( '/', $url );
		$array    = array_slice( $pieces, - 1 );
		$username = array_pop( $array );

		return $username;

	}

}