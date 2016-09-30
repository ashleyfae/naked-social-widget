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