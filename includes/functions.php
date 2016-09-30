<?php
/**
 * functions.php
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

function nsw_get_mapped_sites() {
	$sites = array(
		'twitter' => array(
			'name'  => esc_html__( 'Twitter', 'naked-social-widget' ),
			'class' => 'NSW_Twitter',
			'url'   => 'twitter.com'
		)
	);

	return apply_filters( 'naked-social-widget/get-mapped-sites', $sites );
}

/**
 * @param string $url Profile URL to check.
 *
 * @since 0.3.0
 * @return string|false Name of the class or false if not mapped.
 */
function nsw_is_mapped_site( $url ) {

	foreach ( nsw_get_mapped_sites() as $id => $options ) {

		// If the site is in this URL, return the class name.
		if ( strpos( $url, $options['url'] ) !== false && class_exists( $options['class'] ) ) {
			return $options['class'];
		}

	}

	return false;

}

/**
 * Update Follower Numbers
 *
 * @since 0.3.0
 * @return void
 */
function nsw_update_followers() {
	$widget_id = wp_strip_all_tags( $_POST['widget_id'] );
	$url       = wp_strip_all_tags( $_POST['profile_url'] );

	$class_name = nsw_is_mapped_site( $url );

	if ( false === $class_name ) {
		wp_send_json_error( sprintf( __( '%s is not a mapped site.', 'naked-social-widget' ), esc_url( $url ) ) );
	}

	$mapped_site = new $class_name( $url, $widget_id );
	$new_number  = $mapped_site->get_new_number();

	if ( is_wp_error( $new_number ) ) {
		wp_send_json_error( sprintf( __( 'Error: %s.', 'naked-social-widget' ), $new_number->get_error_message() ) );
	}

	// Save results
	$mapped_site->save_number( $new_number );

	wp_send_json_success( $new_number );
}

add_action( 'wp_ajax_nsw_update_followers', 'nsw_update_followers' );
add_action( 'wp_ajax_nopriv_nsw_update_followers', 'nsw_update_followers' );