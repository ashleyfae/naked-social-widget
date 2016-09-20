<?php
/**
 * Functions for the admin pages.
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is Admin Page
 *
 * Checks whether or not the current page is a Naked Social Widget admin page.
 *
 * @since 1.0
 * @return bool
 */
function naked_social_widget_is_admin_page() {
	$screen      = get_current_screen();
	$is_nsw_page = false;

	if ( $screen->id == 'widgets' ) {
		$is_nsw_page = true;
	}

	return apply_filters( 'naked-social-widget/is-admin-page', $is_nsw_page, $screen );
}

/**
 * Load Admin Scripts
 *
 * Adds all admin scripts and stylesheets to the admin panel.
 *
 * @param string $hook Currently loaded page
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_load_admin_scripts( $hook ) {
	if ( ! apply_filters( 'naked-social-widget/load-admin-scripts', naked_social_widget_is_admin_page(), $hook ) ) {
		return;
	}

	$js_dir  = NAKED_SOCIAL_WIDGET_URL . 'assets/js/';
	$css_dir = NAKED_SOCIAL_WIDGET_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$admin_deps = array(
		'jquery'
	);

	wp_enqueue_script( 'naked-social-widget-admin', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, NAKED_SOCIAL_WIDGET_VERSION, true );

	/*
	 * Stylesheets
	 */
	wp_enqueue_style( 'naked-social-widget-admin', $css_dir . 'admin' . $suffix . '.css', NAKED_SOCIAL_WIDGET_VERSION );
}

add_action( 'admin_enqueue_scripts', 'naked_social_widget_load_admin_scripts', 100 );