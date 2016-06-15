<?php
/**
 * functions.php
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Get Automatically Updating Sites
 *
 * Returns an array of all the sites that we can fetch follower numbers
 * from automatically. Others require manual input.
 *
 * @since 1.0
 * @return array
 */
function naked_social_widget_get_auto_sites() {
	$sites = array(
		'Bloglovin' => esc_html__( 'Bloglovin\'', 'naked-social-widget' ),
		'BookLikes' => esc_html__( 'BookLikes', 'naked-social-widget' ),
		'Goodreads' => esc_html__( 'Goodreads', 'naked-social-widget' ),
		'Twitter'   => esc_html__( 'Twitter', 'naked-social-widget' )
	);

	return apply_filters( 'naked-social-widget/get-auto-sites', $sites );
}

/**
 * Update Follower Number
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_update_number() {
	// Security check.
	check_ajax_referer( 'nsw_update_follower_numbers', 'nonce' );

	$widget_id = wp_strip_all_tags( $_POST['widget_id'] );
	$site_name = wp_strip_all_tags( $_POST['site_name'] );
	$site_key  = wp_strip_all_tags( $_POST['site_key'] );
	$username  = wp_strip_all_tags( $_POST['username'] );

	if ( empty( $site_name ) ) {
		wp_send_json_error( __( 'Missing site name.', 'naked-social-widget' ) );
	}

	if ( empty( $username ) ) {
		wp_send_json_error( sprintf( __( 'Missing username for %s.', 'naked-social-widget' ), esc_html( $site_name ) ) );
	}

	if ( ! array_key_exists( $site_name, naked_social_widget_get_auto_sites() ) ) {
		wp_send_json_error( sprintf( __( '%s: Invalid site name.', 'naked-social-widget' ), esc_html( $site_name ) ) );
	}

	$class_name = 'NSW_' . $site_name;

	if ( ! class_exists( $class_name ) ) {
		wp_send_json_error( sprintf( __( 'Class does not exist - %s', 'naked-social-widget' ), esc_html( $class_name ) ) );
	}

	$site      = new $class_name( $username, $widget_id, $site_key );
	$followers = $site->get_followers();

	if ( ! $followers ) {
		wp_send_json_error( sprintf( __( 'Could not update %s followers.', 'naked-social-widget' ), esc_html( $site_name ) ) );
	}

	$site->update_cache();

	wp_send_json_success( absint( $followers ) );

	exit;

}

add_action( 'wp_ajax_naked_social_widget_update_number', 'naked_social_widget_update_number' );
add_action( 'wp_ajax_nopriv_naked_social_widget_update_number', 'naked_social_widget_update_number' );

/**
 * Update Expiry Time
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_update_expiry() {
	// Security check.
	check_ajax_referer( 'nsw_update_follower_numbers', 'nonce' );

	$widget_id = wp_strip_all_tags( $_POST['widget_id'] );

	$option_name      = $widget_id . '_followers';
	$cache            = get_option( $option_name );
	$cache            = is_array( $cache ) ? $cache : array();
	$cache_time       = apply_filters( 'naked-social-widget/cache-length', DAY_IN_SECONDS );
	$cache['expires'] = time() + $cache_time;

	update_option( $option_name, $cache );

	wp_send_json_success( time() + $cache_time );

	exit;
}

add_action( 'wp_ajax_naked_social_widget_update_expiry', 'naked_social_widget_update_expiry' );
add_action( 'wp_ajax_nopriv_naked_social_widget_update_expiry', 'naked_social_widget_update_expiry' );