<?php
/**
 * Plugin Name: Naked Social Widget
 * Plugin URI: https://www.nosegraze.com
 * Description: Simple social media profile widget with Font Awesome icons.
 * Version: 0.2
 * Author: Nose Graze
 * Author URI: https://www.nosegraze.com
 * License: GPL2
 * GitHub Plugin URI: https://github.com/nosegraze/naked-social-widget
 * GitHub Branch: master
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 */
if ( ! defined( 'NAKED_SOCIAL_WIDGET_VERSION' ) ) {
	define( 'NAKED_SOCIAL_WIDGET_VERSION', '0.2' );
}
if ( ! defined( 'NAKED_SOCIAL_WIDGET_DIR' ) ) {
	define( 'NAKED_SOCIAL_WIDGET_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'NAKED_SOCIAL_WIDGET_URL' ) ) {
	define( 'NAKED_SOCIAL_WIDGET_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'NAKED_SOCIAL_WIDGET_FILE' ) ) {
	define( 'NAKED_SOCIAL_WIDGET_FILE', __FILE__ );
}

/**
 * Require PHP 5.3
 */
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	if ( is_admin() ) {
		/**
		 * Insufficient PHP version notice.
		 *
		 * @since 1.0
		 * @return void
		 */
		function naked_social_widget_insufficient_php_version() {
			?>
			<div class="notice notice-error">
				<p><?php printf( __( 'Naked Social Widget requires PHP version 5.3 or greater. You have version %s. Please contact your web host to upgrade your version of PHP.', 'naked-social-widget' ), PHP_VERSION ); ?></p>
			</div>
			<?php
		}

		add_action( 'admin_notices', 'naked_social_widget_insufficient_php_version' );
	}

	return;
}

/**
 * Load plugin files.
 */
require_once NAKED_SOCIAL_WIDGET_DIR . 'includes/assets.php';
require_once NAKED_SOCIAL_WIDGET_DIR . 'includes/class-naked-social-widget.php';

if ( is_admin() ) {
	require_once NAKED_SOCIAL_WIDGET_DIR . 'includes/admin/admin-pages.php';
}

/**
 * Loads the plugin language files.
 *
 * @since  1.0
 * @return void
 */
function naked_social_widget_load_textdomain() {

	$lang_dir = dirname( plugin_basename( NAKED_SOCIAL_WIDGET_FILE ) ) . '/languages/';
	$lang_dir = apply_filters( 'naked-social-widget/languages-directory', $lang_dir );
	load_plugin_textdomain( 'naked-social-widget', false, $lang_dir );

}

add_action( 'plugins_loaded', 'naked_social_widget_load_textdomain' );