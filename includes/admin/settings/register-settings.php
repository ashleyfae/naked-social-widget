<?php
/**
 * Register Settings
 *
 * Taken from Easy Digital Downloads
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
 * Get an Option
 *
 * Looks to see if the specified setting exists, returns the default if not.
 *
 * @param string $key     Key to retrieve
 * @param mixed  $default Default option
 *
 * @global       $naked_social_widget_options
 *
 * @since 1.0
 * @return mixed
 */
function naked_social_widget_get_option( $key = '', $default = false ) {
	global $naked_social_widget_options;

	$value = ( array_key_exists( $key, $naked_social_widget_options ) && ! empty( $naked_social_widget_options[ $key ] ) ) ? $naked_social_widget_options[ $key ] : $default;
	$value = apply_filters( 'naked-social-widget/options/get', $value, $key, $default );

	return apply_filters( 'naked-social-widget/options/get/' . $key, $value, $key, $default );
}

/**
 * Update an Option
 *
 * Updates an existing setting value in both the DB and the global variable.
 * Passing in an empty, false, or null string value will remove the key from the naked_social_widget_settings array.
 *
 * @param string $key   Key to update
 * @param mixed  $value The value to set the key to
 *
 * @global       $naked_social_widget_options
 *
 * @since 1.0
 * @return bool True if updated, false if not
 */
function naked_social_widget_update_option( $key = '', $value = false ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = naked_social_widget_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'naked_social_widget_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'naked-social-widget/options/update', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'naked_social_widget_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $naked_social_widget_options;
		$naked_social_widget_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an Option
 *
 * Removes an setting value in both the DB and the global variable.
 *
 * @param string $key The key to delete.
 *
 * @global       $naked_social_widget_options
 *
 * @since 1.0
 * @return boolean True if updated, false if not.
 */
function naked_social_widget_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'naked_social_widget_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'naked_social_widget_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $naked_social_widget_options;
		$naked_social_widget_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array Naked Social Widget settings
 */
function naked_social_widget_get_settings() {
	$settings = get_option( 'naked_social_widget_settings', array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	return apply_filters( 'naked-social-widget/get-settings', $settings );
}

/**
 * Add all settings sections and fields.
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_register_settings() {

	if ( false == get_option( 'naked_social_widget_settings' ) ) {
		add_option( 'naked_social_widget_settings' );
	}

	foreach ( naked_social_widget_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {
			add_settings_section(
				'naked_social_widget_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'naked_social_widget_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'naked_social_widget_settings[' . $option['id'] . ']',
					$name,
					function_exists( 'naked_social_widget_' . $option['type'] . '_callback' ) ? 'naked_social_widget_' . $option['type'] . '_callback' : 'naked_social_widget_missing_callback',
					'naked_social_widget_settings_' . $tab . '_' . $section,
					'naked_social_widget_settings_' . $tab . '_' . $section,
					array(
						'section'     => $section,
						'id'          => isset( $option['id'] ) ? $option['id'] : null,
						'desc'        => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'        => isset( $option['name'] ) ? $option['name'] : null,
						'size'        => isset( $option['size'] ) ? $option['size'] : null,
						'options'     => isset( $option['options'] ) ? $option['options'] : '',
						'std'         => isset( $option['std'] ) ? $option['std'] : '',
						'min'         => isset( $option['min'] ) ? $option['min'] : null,
						'max'         => isset( $option['max'] ) ? $option['max'] : null,
						'step'        => isset( $option['step'] ) ? $option['step'] : null,
						'chosen'      => isset( $option['chosen'] ) ? $option['chosen'] : null,
						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null
					)
				);
			}
		}
	}

	// Creates our settings in the options table
	register_setting( 'naked_social_widget_settings', 'naked_social_widget_settings', 'naked_social_widget_settings_sanitize' );

}

add_action( 'admin_init', 'naked_social_widget_register_settings' );

/**
 * Registered Settings
 *
 * Sets and returns the array of all plugin settings.
 * Developers can use the following filters to add their own settings or
 * modify existing ones:
 *
 *  + naked-social-widget/settings/{key} - Where {key} is a specific tab. Used to modify a single tab/section.
 *  + naked-social-widget/settings/registered-settings - Includes the entire array of all settings.
 *
 * @since 1.0
 * @return array
 */
function naked_social_widget_get_registered_settings() {

	$naked_social_widget_settings = array(
		/* General Settings */
		'general' => apply_filters( 'naked-social-widget/settings/general', array(
			'main' => array(
				'icon_type'    => array(
					'id'      => 'icon_type',
					'name'    => esc_html__( 'Icon Type', 'naked-social-widget' ),
					'type'    => 'radio',
					'options' => array(
						'default'      => esc_html__( 'Default', 'naked-social-widget' ),
						'font_awesome' => esc_html__( 'Font Awesome (limited icon availability)', 'naked-social-widget' ),
						'custom'       => __( 'Custom Images', 'naked-social-widget' )
					),
					'std'     => 'font_awesome'
				),
				'social_sites' => array(
					'id'   => 'social_sites',
					'name' => esc_html__( 'Social Sites', 'naked-social-widget' ),
					'desc' => __( 'Add your chosen social media sites. You\'ll be able to set up your usernames and profile links in the actual widget.', 'naked-social-widget' ),
					'type' => 'repeater',
					'std'  => array(
						array(
							'name' => esc_html__( 'Twitter', 'naked-social-widget' ),
							'site' => 'Twitter'
						),
						array(
							'name' => esc_html__( 'Facebook', 'naked-social-widget' ),
							'site' => ''
						),
						array(
							'name' => esc_html__( 'Goodreads', 'naked-social-widget' ),
							'site' => 'Goodreads'
						),
						array(
							'name' => esc_html__( 'RSS', 'naked-social-widget' ),
							'site' => ''
						)
					)
				)
			)
		) )
	);

	return apply_filters( 'naked-social-widget/settings/registered-settings', $naked_social_widget_settings );

}

/**
 * Sanitize Settings
 *
 * Adds a settings error for the updated message.
 *
 * @param array  $input                       The value inputted in the field
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget options
 *
 * @since 1.0
 * @return array New, sanitized settings.
 */
function naked_social_widget_settings_sanitize( $input = array() ) {

	global $naked_social_widget_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = naked_social_widget_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input = $input ? $input : array();
	$input = apply_filters( 'naked-social-widget/settings/sanitize/' . $tab . '/' . $section, $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {
		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $section ][ $key ]['type'] ) ? $settings[ $tab ][ $section ][ $key ]['type'] : false;
		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'naked-social-widget/settings/sanitize/' . $type, $value, $key );
		}
		// General filter
		$input[ $key ] = apply_filters( 'naked-social-widget/settings/sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array(); // Check for extensions that aren't using new sections
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();
	$found_settings   = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) ) {
		foreach ( $found_settings as $key => $value ) {
			if ( empty( $input[ $key ] ) && array_key_exists( $key, $naked_social_widget_options ) ) {
				unset( $naked_social_widget_options[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $naked_social_widget_options, $input );

	return $output;

}

/**
 * Retrieve settings tabs
 *
 * @since 1.0
 * @return array $tabs
 */
function naked_social_widget_get_settings_tabs() {
	$tabs            = array();
	$tabs['general'] = __( 'General', 'naked-social-widget' );
	$tabs['styles']  = __( 'Styles', 'naked-social-widget' );

	return apply_filters( 'naked-social-widget/settings/tabs', $tabs );
}


/**
 * Retrieve settings tabs
 *
 * @since 1.0
 * @return array $section
 */
function naked_social_widget_get_settings_tab_sections( $tab = false ) {
	$tabs     = false;
	$sections = naked_social_widget_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  1.0
 * @return array Array of tabs and sections
 */
function naked_social_widget_get_registered_settings_sections() {
	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'general' => apply_filters( 'naked-social-widget/settings/sections/general', array(
			'main' => __( 'General', 'naked-social-widget' )
		) ),
		'styles'  => apply_filters( 'naked-social-widget/settings/sections/styles', array(
			'main' => __( 'Styles', 'naked-social-widget' )
		) )
	);

	$sections = apply_filters( 'naked-social-widget/settings/sections', $sections );

	return $sections;
}

/**
 * Sanitizes a string key for Naked Social Widget Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are
 * allowed
 *
 * @param  string $key String key
 *
 * @since 1.0
 * @return string Sanitized key
 */
function naked_social_widget_sanitize_key( $key ) {
	$raw_key = $key;
	$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	return apply_filters( 'naked-social-widget/sanitize-key', $key, $raw_key );
}

/**
 * Sanitize: Colour Field
 *
 * @param string $value
 * @param string $key
 *
 * @since 1.0
 * @return string
 */
function naked_social_widget_sanitize_color_field( $value, $key ) {
	if ( '' === $value ) {
		return '';
	}

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $value ) ) {
		return $value;
	}
}

add_filter( 'naked-social-widget/settings/sanitize/color', 'naked_social_widget_sanitize_color_field', 10, 2 );

/*
 * Callbacks
 */

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @param array $args Arguments passed by the setting
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'naked-social-widget' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget settings
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_text_callback( $args ) {
	global $naked_social_widget_options;

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="naked_social_widget_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$type = array_key_exists( 'input-type', $args ) ? $args['input-type'] : 'text';

	$readonly = ( array_key_exists( 'readonly', $args ) && $args['readonly'] ) === true ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	?>
	<input type="<?php echo esc_attr( $type ); ?>" class="<?php echo sanitize_html_class( $size ); ?>-text" id="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" <?php echo $name; ?> value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo $readonly; ?>>
	<label for="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Header Callback
 *
 * Simply renders a title and description.
 *
 * @param array $args Arguments passed by the setting
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_header_callback( $args ) {
	if ( array_key_exists( 'desc', $args ) ) {
		echo '<div class="desc">' . wp_kses_post( $args['desc'] ) . '</div>';
	}
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget settings
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_textarea_callback( $args ) {
	global $naked_social_widget_options;

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}
	?>
	<textarea class="large-text" id="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" name="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>]" rows="10" cols="50"><?php echo esc_textarea( $value ); ?></textarea>
	<label for="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the EDD Options
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_color_callback( $args ) {
	global $naked_social_widget_options;

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';
	?>
	<input type="text" class="naked-social-widget-color-picker" id="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>" name="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>">
	<label for="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * License Key Callback
 *
 * Renders license key fields.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget settings
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_license_key_callback( $args ) {
	global $naked_social_widget_options;

	$messages = array();
	$class    = '';
	$license  = get_option( $args['options']['is_valid_license_option'] );

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( ! empty( $license ) && is_object( $license ) ) {

		if ( false === $license->success ) {

			switch ( $license->error ) {

				case 'expired' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'naked-social-widget' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
						'https://shop.nosegraze.com/checkout/?naked_social_widget_license_key=' . urlencode( $value ) . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'missing' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'naked-social-widget' ),
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'invalid' :
				case 'site_inactive' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'naked-social-widget' ),
						$args['name'],
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'item_name_mismatch' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'This is not a %s.', 'naked-social-widget' ),
						$args['name']
					);

					$license_status = 'license-' . $class . '-notice';

					break;

				case 'no_activations_left' :

					$class      = 'error';
					$messages[] = sprintf(
						__( 'Your license key has reached its activation limit. <a href="%s" target="_blank" title="View upgrades">View possible upgrades.</a>', 'naked-social-widget' ),
						'https://shop.nosegraze.com/my-account/?utm_campaign=admin&utm_source=licenses&utm_medium=no_activations_left'
					);

					$license_status = 'license-' . $class . '-notice';

					break;

			}

		} else {

			$class      = 'valid';
			$now        = current_time( 'timestamp' );
			$expiration = strtotime( $license->expires, current_time( 'timestamp' ) );

			if ( 'lifetime' === $license->expires ) {

				$messages[]     = __( 'License key never expires.', 'naked-social-widget' );
				$license_status = 'license-lifetime-notice';

			} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

				$messages[] = sprintf(
					__( 'Your license key is about to expire! It expires on %1$s. <a href="%2$s" target="_blank" title="Renew license key">Renew your license key</a> to continue getting updates and support.', 'naked-social-widget' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
					'https://shop.nosegraze.com/checkout/?naked_social_widget_license_key=' . urlencode( $value ) . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
				);

				$license_status = 'license-expires-soon-notice';

			} else {

				$messages[] = sprintf(
					__( 'Your license key expires on %s.', 'naked-social-widget' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
				);

				$license_status = 'license-expiration-date-notice';

			}

		}

	} else {
		$license_status = null;
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';

	$wrapper_class = isset( $license_status ) ? $license_status : 'license-null';
	?>
	<div class="<?php echo sanitize_html_class( $wrapper_class ); ?>">
		<input type="text" class="<?php echo sanitize_html_class( $size ); ?>-text" id="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" name="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>">
		<?php

		// License key is valid, so let's show a deactivate button.
		if ( ( is_object( $license ) && 'valid' == $license->license ) || 'valid' == $license ) {
			?>
			<input type="submit" class="button-secondary" name="<?php echo esc_attr( $args['id'] ); ?>_deactivate" value="<?php _e( 'Deactivate License', 'naked-social-widget' ); ?>">
			<?php
		}

		?>
		<label for="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
		<?php

		if ( ! empty( $messages ) && is_array( $messages ) ) {
			foreach ( $messages as $message ) {
				?>
				<div class="naked-social-widget-license-data naked-social-widget-license-<?php echo sanitize_html_class( $class ); ?> desc">
					<p><?php echo $message; ?></p>
				</div>
				<?php
			}
		}

		wp_nonce_field( naked_social_widget_sanitize_key( $args['id'] ) . '-nonce', naked_social_widget_sanitize_key( $args['id'] ) . '-nonce' );
		?>
	</div>
	<?php
}

/**
 * Checkbox Callback
 *
 * Renders a checkbox field.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget settings
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_checkbox_callback( $args ) {
	global $naked_social_widget_options;

	$checked = isset( $naked_social_widget_options[ $args['id'] ] ) ? checked( 1, $naked_social_widget_options[ $args['id'] ], false ) : '';
	?>
	<input type="checkbox" id="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" name="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" value="1" <?php echo $checked; ?>>
	<label for="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget Options
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_select_callback( $args ) {
	global $naked_social_widget_options;

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['placeholder'] ) ) {
		$placeholder = $args['placeholder'];
	} else {
		$placeholder = '';
	}

	if ( isset( $args['chosen'] ) ) {
		$chosen = 'class="naked-social-widget-chosen"';
	} else {
		$chosen = '';
	}

	$html = '<select id="naked_social_widget_settings[' . naked_social_widget_sanitize_key( $args['id'] ) . ']" name="naked_social_widget_settings[' . esc_attr( $args['id'] ) . ']" ' . $chosen . 'data-placeholder="' . esc_html( $placeholder ) . '">';

	foreach ( $args['options'] as $option => $name ) {
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
	}

	$html .= '</select>';
	$html .= '<label for="naked_social_widget_settings[' . naked_social_widget_sanitize_key( $args['id'] ) . ']" class="desc"> ' . wp_kses_post( $args['desc'] ) . '</label>';

	echo $html;
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget Options
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_radio_callback( $args ) {
	global $naked_social_widget_options;

	foreach ( $args['options'] as $key => $option ) {
		$checked = false;

		if ( isset( $naked_social_widget_options[ $args['id'] ] ) && $naked_social_widget_options[ $args['id'] ] == $key ) {
			$checked = true;
		} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! isset( $naked_social_widget_options[ $args['id'] ] ) ) {
			$checked = true;
		}

		echo '<input name="naked_social_widget_settings[' . naked_social_widget_sanitize_key( $args['id'] ) . ']" id="naked_social_widget_settings[' . naked_social_widget_sanitize_key( $args['id'] ) . '][' . naked_social_widget_sanitize_key( $key ) . ']" type="radio" value="' . naked_social_widget_sanitize_key( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
		echo '<label for="naked_social_widget_settings[' . naked_social_widget_sanitize_key( $args['id'] ) . '][' . naked_social_widget_sanitize_key( $key ) . ']">' . esc_html( $option ) . '</label><br/>';

	}

	echo '<p class="desc">' . wp_kses_post( $args['desc'] ) . '</p>';
}

/**
 * TinyMCE Callback
 *
 * Renders a rich text editor.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget Options
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_tinymce_callback( $args ) {
	global $naked_social_widget_options;

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];

		if ( empty( $args['allow_blank'] ) && empty( $value ) ) {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	wp_editor( stripslashes( $value ), 'naked_social_widget_settings' . esc_attr( $args['id'] ), array(
		'textarea_name' => 'naked_social_widget_settings[' . esc_attr( $args['id'] ) . ']',
		'textarea_rows' => absint( $rows )
	) );
	?>
	<br>
	<label for="naked_social_widget_settings[<?php echo naked_social_widget_sanitize_key( $args['id'] ); ?>]" class="desc">
		<?php echo wp_kses_post( $args['desc'] ); ?>
	</label>
	<?php
}

/**
 * Sorter Callback
 *
 * Renders 'enabled' and 'disabled' sortable columns.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget Options
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_sorter_callback( $args ) {
	global $naked_social_widget_options;

	// Get the configuration
	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$config = $naked_social_widget_options[ $args['id'] ];
	} else {
		$config = isset( $args['std'] ) ? $args['std'] : array();
	}

	$config = naked_social_widget_update_sorter_array( $config, $args['id'] );

	$html = '<div id="' . $args['id'] . '" class="sorter">';
	$html .= '<input type="hidden" class="naked-social-widget-settings-key" value="naked_social_widget_settings">';

	// Loop through each column
	foreach ( $config as $column => $section ) {

		$html .= '<ul id="' . $args['id'] . '_' . $column . '" class="sortlist_' . $args['id'] . ' sorter-box-' . $column . '"><h3>' . $column . '</h3>';

		$html .= '<input class="sorter-placebo" type="hidden" name="naked_social_widget_settings[' . $args['id'] . '][' . $column . '][placebo]" value="placebo">';

		foreach ( $section as $key => $item ) {
			// Don't add a list item for the placebo.
			if ( $key == 'placebo' ) {
				continue;
			}
			$name = is_array( $item ) ? $item['name'] : $item;
			$html .= '<li id="' . $key . '">';
			$html .= '<input type="hidden" name="naked_social_widget_settings[' . $args['id'] . '][' . $column . '][' . $key . '][name]" value="' . esc_attr( $name ) . '" data-key="name" class="sorter-input sorter-input-name">';
			$html .= $name;
			$html .= '</li>';
		}

		$html .= '</ul>';

	}

	$html .= '</div>';

	echo $html;
}

/**
 * Checks through an options array and matches it against the default values.
 * If one of the default values doesn't exist in the saved options array, it's
 * forced into the 'disabled' column
 *
 * @param array  $config Array of config settings
 * @param string $key    ID of the field
 *
 * @since  1.0
 * @return array The modified array of config settings
 */
function naked_social_widget_update_sorter_array( $config, $key ) {
	$defaults = naked_social_widget_get_registered_settings();

	if ( ! isset( $defaults['general']['main'][ $key ] ) ) {
		return $config;
	}

	$defaults = $defaults['general']['main'][ $key ];

	// Loop through each section ('enabled' and 'disabled')
	// in the default configuration.
	foreach ( $defaults as $section ) {
		// Loop through each entry in this section.
		foreach ( $section as $key => $value ) {
			// Check to see if this key exists in the saved config.
			$in_enabled  = array_key_exists( $key, $config['enabled'] );
			$in_disabled = array_key_exists( $key, $config['disabled'] );

			// If it doesn't exist in the enabled or disabled column,
			// force it into the disabled one.
			if ( $in_enabled === false && $in_disabled === false ) {
				$config['disabled'][ $key ] = $value;
			}
		}
	}

	return $config;
}

/**
 * Repeater Callback
 *
 * Renders 'enabled' and 'disabled' sortable columns.
 *
 * @param array  $args                        Arguments passed by the setting
 *
 * @global array $naked_social_widget_options Array of all the Naked Social Widget Options
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_repeater_callback( $args ) {
	global $naked_social_widget_options;

	if ( isset( $naked_social_widget_options[ $args['id'] ] ) ) {
		$value = $naked_social_widget_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : array();
	}

	if ( ! is_array( $value ) ) {
		return;
	}

	$i = 1;
	$j = 0;

	$auto_sites = array_merge( array( 'other' => esc_html__( 'Other', 'naked-social-widget' ) ), naked_social_widget_get_auto_sites() );
	?>
	<table id="naked-social-widget-sites" class="wp-list-table widefat fixed posts">
		<thead>
		<tr>
			<th id="naked-social-site-name"><?php _e( 'Site Name', 'naked-social-widget' ); ?></th>
			<th id="naked-social-site-map"><?php _e( 'Site (used for fetching follower number)', 'naked-social-widget' ); ?></th>
			<th id="naked-social-site-link-remove"><?php _e( 'Remove', 'naked-social-widget' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $value as $link ) :
			$name = isset( $link['name'] ) ? $link['name'] : '';
			$site_map = isset( $link['site'] ) ? $link['site'] : 'other';
			?>
			<tr class="naked-social-widget-cloned">
				<td>
					<label for="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>]_name_<?php echo $i; ?>" class="screen-reader-text"><?php _e( 'Enter the name of the social media site', 'naked-social-widget' ); ?></label>
					<input type="text" class="regular-text" id="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>]_name_<?php echo $i; ?>" name="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>][<?php echo $j; ?>][name]" value="<?php esc_attr_e( stripslashes( $name ) ); ?>">
				</td>
				<td>
					<label for="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>]_map_<?php echo $i; ?>" class="screen-reader-text"><?php _e( 'Choose from one of the available sites', 'naked-social-widget' ); ?></label>
					<select id="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>]_map_<?php echo $i; ?>" name="naked_social_widget_settings[<?php echo esc_attr( $args['id'] ); ?>][<?php echo $j; ?>][site]">
						<?php foreach ( $auto_sites as $key => $name ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $site_map, $key ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td>
					<button class="button-secondary naked-social-site-remove-link" onclick="<?php echo ( $i > 1 ) ? 'jQuery(this).parent().parent().remove(); return false' : 'return false'; ?>"><?php _e( 'Remove', 'naked-social-widget' ); ?></button>
				</td>
			</tr>
			<?php
			$i ++;
			$j ++;
			?>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div id="naked-social-widget-clone-buttons">
		<button id="naked-social-widget-add-site" class="button button-secondary" rel=".naked-social-widget-cloned"><?php _e( 'Add Site', 'naked-social-widget' ); ?></button>
	</div>
	<?php
}