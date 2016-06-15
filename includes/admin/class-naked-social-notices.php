<?php

/**
 * Admin Notices Class
 *
 * Handles displaying informational admin notices.
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
 * Class Naked_Social_Notices
 * 
 * @since 1.0
 */
class Naked_Social_Notices {

	/**
	 * Naked_Social_Notices constructor.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'naked-social-widget/dismiss/notices', array( $this, 'dismiss_notices' ) );
	}

	/**
	 * Show Notices
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function show_notices() {
		$notices = array(
			'updated' => array(),
			'error'   => array()
		);

		if ( isset( $_GET['naked-social-widget-message'] ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				switch ( $_GET['naked-social-widget-message'] ) {
					case 'settings-imported' :
						$notices['updated']['naked-social-widget-settings-imported'] = __( 'The settings have been successfully imported.', 'naked-social-widget' );
						break;
				}
			}
		}

		if ( count( $notices['updated'] ) ) {
			foreach ( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'naked-social-widget-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) ) {
			foreach ( $notices['error'] as $notice => $message ) {
				add_settings_error( 'naked-social-widget-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'naked-social-widget-notices' );
	}

	/**
	 * Dismiss Notices
	 *
	 * Update current user's meta to mark this notice as dismissed.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function dismiss_notices() {
		if ( isset( $_GET['naked_social_widget_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_naked_social_widget_' . $_GET['naked_social_widget_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'naked_social_widget_action', 'naked_social_widget_notice' ) ) );
			exit;
		}
	}

}

new Naked_Social_Notices;