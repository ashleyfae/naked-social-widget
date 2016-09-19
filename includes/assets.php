<?php
/**
 * Load Assets on the Front-End
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Load Assets
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_load_js() {
	$js_dir  = NAKED_SOCIAL_WIDGET_URL . 'assets/js/';
	$css_dir = NAKED_SOCIAL_WIDGET_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	if ( ! apply_filters( 'naked-social-widget/assets/disable-font-awesome', false ) ) {
		wp_enqueue_style( 'font-awesome', $css_dir . 'font-awesome' . $suffix . '.css', array(), '4.6.1' );
	}

	if ( ! apply_filters( 'naked-social-widget/assets/disable-styles', false ) ) {
		wp_enqueue_style( 'naked-social-widget', $css_dir . 'front-end' . $suffix . '.css', array(), NAKED_SOCIAL_WIDGET_VERSION );
	}

	if ( ! apply_filters( 'naked-social-widget/assets/disable-js', false ) ) {
		wp_enqueue_script( 'naked-social-widget', $js_dir . 'naked-social-widget' . $suffix . '.js', array( 'jquery' ), NAKED_SOCIAL_WIDGET_VERSION, true );

		$settings = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'nsw_update_follower_numbers' )
		);

		wp_localize_script( 'naked-social-widget', 'NAKED_SOCIAL_WIDGET', $settings );
	}
}

add_action( 'wp_enqueue_scripts', 'naked_social_widget_load_js' );