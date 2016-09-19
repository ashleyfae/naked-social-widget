<?php

/**
 * Class responsible for building a user's profile of social media
 * sites and related stats.
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class Naked_Social_Profile {

	/**
	 * Whether or not to cache the follower numbers
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $cached = true;

	/**
	 * Widget Instance
	 *
	 * Contains all values saved to the widget.
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $widget_instance;

	/**
	 * Widget instance ID
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $widget_id;

	/**
	 * Social Sites
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $social_sites = array();

	/**
	 * Plugin Settings
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $plugin_settings;

	/**
	 * Array of Follower Counts
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $follower_counts;

	/**
	 * Whether or not the stats need to be updated via ajax after
	 * the page loads. This will be set to 'true' if the cache has
	 * expired.
	 *
	 * @var bool
	 * @access protected
	 * @since  1.0
	 */
	protected $needs_update = false;

	/**
	 * Naked_Social_Profile constructor.
	 *
	 * @param array  $widget_instance Widget settings
	 * @param string $id              Widget instance ID
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct( $widget_instance, $id ) {

		global $naked_social_widget_options;

		$this->widget_instance = $widget_instance;
		$this->widget_id       = $id;
		$this->plugin_settings = $naked_social_widget_options;
		$this->social_sites    = $this->get_social_sites();
		$this->follower_counts = $this->get_follower_counts();

	}

	/**
	 * Get Social Sites
	 *
	 * Reformats the enabled social sites from the settings panel.
	 *      + Creates a key based on the name.
	 *      + Checks to see if the site is mapped.
	 *
	 * @access protected
	 * @since  1.0
	 * @return array
	 */
	protected function get_social_sites() {

		$sites = array();

		if ( ! is_array( $this->plugin_settings['social_sites'] ) ) {
			return $sites;
		}

		foreach ( $this->plugin_settings['social_sites'] as $i => $site ) {
			if ( ! array_key_exists( 'name', $site ) ) {
				continue;
			}

			$is_mapped = ( array_key_exists( 'site', $site ) && ! empty( $site['site'] ) && array_key_exists( $site['site'], naked_social_widget_get_auto_sites() ) );
			$key       = naked_social_widget_sanitize_key( $site['name'] . '_' . $i );

			if ( ! array_key_exists( $key . '_profile_url', $this->widget_instance ) ) {
				continue;
			}

			$sites[ $key ] = array(
				'profile_url'  => $this->widget_instance[ $key . '_profile_url' ],
				'name'         => $site['name'],
				'mapped'       => $is_mapped,
				'mapped_value' => $site['site']
			);

		}

		return apply_filters( 'naked-social-widget/profile/get-social-sites', $sites, $this );

	}

	/**
	 * Get Follower Counts
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_follower_counts() {

		if ( ! array_key_exists( 'show_numbers', $this->widget_instance ) || empty( $this->widget_instance['show_numbers'] ) ) {
			return array();
		}

		$cache          = get_option( $this->widget_id . '_followers' );
		$cached_numbers = ( is_array( $cache ) && array_key_exists( 'numbers', $cache ) && is_array( $cache['numbers'] ) ) ? $cache['numbers'] : array();
		$cache_epiry    = ( is_array( $cache ) && array_key_exists( 'expires', $cache ) ) ? $cache['expires'] : false;

		if ( $this->is_expired( $cache_epiry ) ) {
			$this->needs_update = true;
		}

		foreach ( $this->social_sites as $key => $site ) {
			if ( ! array_key_exists( 'name', $site ) ) {
				continue;
			}

			// Get the number from non-mapped ones.
			if ( ! $site['mapped'] ) {
				$cached_numbers[ $key ] = array_key_exists( $key . '_followers', $this->widget_instance ) ? $this->widget_instance[ $key . '_followers' ] : 0;
			}
		}

		return apply_filters( 'naked-social-widget/profile/get-follower-counts', $cached_numbers, $this );

	}

	/**
	 * Is Expired?
	 *
	 * Determines whether or not the follower numbers have expired.
	 *
	 * @param array|false $cache
	 *
	 * @access private
	 * @since  1.0
	 * @return bool True if they have expired or do not yet exist, false if they exist but have not expired.
	 */
	private function is_expired( $cache ) {
		if ( $this->cached === false || $cache == false || ! is_numeric( $cache ) || $cache < time() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Profile URL
	 *
	 * Mapped sites only have a username so we need to build the
	 * full URL.
	 *
	 * @param array $site Array of site data.
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_profile_url( $site ) {
		$url = $site['profile_url'];

		if ( $site['mapped'] ) {
			$class_name = 'NSW_' . $site['mapped_value'];
			$url        = $class_name->get_profile_url( $url );
		}

		return apply_filters( 'naked-social-widget/profile/get-profile-url', $url, $site );
	}

	/**
	 * Display Widget
	 *
	 * @access public
	 * @since  1.0
	 * @return string
	 */
	public function display() {

		if ( ! is_array( $this->plugin_settings['social_sites'] ) ) {
			return '';
		}

		ob_start();

		foreach ( $this->social_sites as $key => $site ) {

			$profile_url = array_key_exists( 'profile_url', $site ) ? $site['profile_url'] : '';
			$label       = array_key_exists( $key . '_label', $this->widget_instance ) ? $this->widget_instance[ $key . '_label' ] : false;

			if ( empty( $profile_url ) ) {
				continue;
			}
			?>
			<li class="nsw-<?php echo strtolower( sanitize_html_class( $key ) ); ?> <?php echo ( $site['mapped'] && $this->needs_update ) ? 'nsw-ajax-update' : ''; ?>" data-site="<?php echo array_key_exists( 'mapped_value', $site ) ? esc_attr( $site['mapped_value'] ) : ''; ?>" data-username="<?php echo $site['mapped'] ? esc_attr( $profile_url ) : ''; ?>" data-key="<?php echo esc_attr( $key ); ?>">
				<a href="<?php echo esc_url( $this->get_profile_url( $site ) ); ?>" target="_blank">
					<?php $this->display_icon( $key ); ?>
					<span class="nsw-follower-number"><?php echo array_key_exists( $key, $this->follower_counts ) ? esc_html( $this->follower_counts[ $key ] ) : ''; ?></span>

					<?php if ( ! empty( $label ) ) : ?>
						<span class="nsw-follower-label"><?php echo $label; ?></span>
					<?php endif; ?>
				</a>
			</li>
			<?php

		}

		$output = '<ul class="naked-social-widget-profile" data-id="' . esc_attr( $this->widget_id ) . '">' . ob_get_clean() . '</ul>';

		return apply_filters( 'naked-social-widget/profile/display', $output );

	}

	/**
	 * Display Icon
	 *
	 * @param string $key
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function display_icon( $key ) {

		if ( $this->plugin_settings['icon_type'] == 'font_awesome' && isset( $this->widget_instance[ $key . '_fa' ] ) ) {
			?>
			<i class="fa fa-<?php echo sanitize_html_class( $this->widget_instance[ $key . '_fa' ] ); ?>"></i>
			<?php
		}

	}

}