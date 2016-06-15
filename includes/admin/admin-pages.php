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
 * Creates a admin submenu page under 'Settings'.
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_add_options_link() {
	add_options_page( esc_html__( 'Naked Social Widget Settings', 'naked-social-widget' ), esc_html__( 'Naked Social Widget', 'naked-social-widget' ), 'manage_options', 'naked-social-widget', 'naked_social_widget_options_page' );
}

add_action( 'admin_menu', 'naked_social_widget_add_options_link' );

/**
 * Is Admin Page
 *
 * Checks whether or not the current page is a Naked Social Widget admin page.
 *
 * @since 1.0
 * @return bool
 */
function ask_me_anything_is_admin_page() {
	$screen      = get_current_screen();
	$is_nsw_page = false;

	if ( $screen->id == 'settings_page_naked-social-widget' ) {
		$is_nsw_page = true;
	}

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
	if ( ! apply_filters( 'naked-social-widget/load-admin-scripts', ask_me_anything_is_admin_page(), $hook ) ) {
		return;
	}

	$js_dir  = NAKED_SOCIAL_WIDGET_URL . 'assets/js/';
	$css_dir = NAKED_SOCIAL_WIDGET_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$admin_deps = array(
		'jquery',
		'jquery-ui-sortable',
		'wp-color-picker'
	);

	wp_enqueue_script( 'recopy', $js_dir . 'jquery.recopy' . $suffix . '.js', array('jquery'), '1.1.0', true );
	wp_enqueue_script( 'naked-social-widget-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, NAKED_SOCIAL_WIDGET_VERSION, true );

	$settings = array(
		'text_remove' => __( 'Remove', 'naked-social-widget' )
	);

	wp_localize_script( 'naked-social-widget-admin-scripts', 'ASK_ME_ANYTHING', apply_filters( 'naked-social-widget/admin-scripts-settings', $settings ) );

	/*
	 * Stylesheets
	 */
	wp_enqueue_style( 'naked-social-widget-admin', $css_dir . 'admin' . $suffix . '.css', NAKED_SOCIAL_WIDGET_VERSION );
	wp_enqueue_style( 'wp-color-picker' );
}

add_action( 'admin_enqueue_scripts', 'naked_social_widget_load_admin_scripts', 100 );