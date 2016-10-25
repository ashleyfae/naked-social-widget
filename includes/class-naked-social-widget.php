<?php

/**
 * Social Media Widget
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */
class Naked_Social_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			'naked_social_widget',
			__( 'Naked Social Widget', 'naked-social-widget' ),
			array( 'description' => __( 'Display your social media icons', 'naked-social-widget' ), )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see    WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		// Widget title
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$classes = array( 'naked-social-widget-sites' );

		if ( array_key_exists( 'center_icons', $instance ) && $instance['center_icons'] ) {
			$classes[] = 'nsw-centered';
		}
		if ( array_key_exists( 'format_icons', $instance ) && $instance['format_icons'] ) {
			$classes[] = 'nsw-flex';
		}

		$classes = apply_filters( 'naked-social-widget/widget/ul-classes', $classes );
		$classes = array_map( 'sanitize_html_class', $classes );
		?>
		<ul class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php foreach ( $instance['sites'] as $key => $site ) :
				if ( empty( $site['url'] ) ) {
					continue;
				}
				?>
				<li class="nsw-<?php echo esc_attr( $this->sanitize_key( $site['name'] ) ); ?>">
					<a href="<?php echo esc_url( $site['url'] ); ?>">

						<?php if ( $site['icon'] ) : ?>
							<i class="fa fa-<?php echo esc_attr( $site['icon'] ); ?>"></i>
						<?php endif; ?>

						<?php if ( $site['name'] && $instance['hide_site_name'] !== true ) : ?>
							<span class="nsw-site-name"><?php echo $site['name']; ?></span>
						<?php endif; ?>

						<?php if ( $site['followers'] ) : ?>
							<span class="nsw-site-followers"><?php echo $site['followers']; ?></span>
						<?php endif; ?>

						<?php if ( $site['label'] ) : ?>
							<span class="nsw-site-label"><?php echo $site['label']; ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php

		echo $args['after_widget'];

	}

	/**
	 * Sanitize Key
	 *
	 * Keys are used as internal identifiers. Alphanumeric characters, dashes,
	 * underscores, stops, colons and slashes are allowed.
	 *
	 * @param $key
	 *
	 * @access protected
	 * @since  1.0
	 * @return string
	 */
	protected function sanitize_key( $key ) {

		$raw_key = $key;
		$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', strtolower( $key ) );

		return apply_filters( 'naked-social-widget/sanitize-key', $key, $raw_key );

	}

	/**
	 * Back-end widget form.
	 *
	 * @see    WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function form( $instance ) {

		// Get default values.
		$defaults = array(
			'title'          => __( 'Follow Me', 'naked-social-widget' ),
			'center_icons'   => true,
			'format_icons'   => true,
			'hide_site_name' => true,
			'sites'          => array(
				array(
					'name'      => esc_html__( 'Twitter', 'naked-social-share' ),
					'site'      => 'Twitter',
					'url'       => '',
					'followers' => '',
					'label'     => esc_html__( 'Followers', 'naked-social-share' ),
					'icon'      => 'twitter',
				),
				array(
					'name'      => esc_html__( 'RSS', 'naked-social-share' ),
					'site'      => 'RSS',
					'url'       => home_url( '/feed/' ),
					'followers' => '',
					'label'     => esc_html__( 'Followers', 'naked-social-share' ),
					'icon'      => 'rss'
				)
			)
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>

		<p><?php printf( __( 'You can get icon names from the <a href="%s">Font Awesome website</a>. Enter in the icon name without the fa fa- class prefix. Example: <mark>twitter-square</mark>', 'naked-social-widget' ), 'http://fortawesome.github.io/Font-Awesome/icons/' ); ?></p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'naked-social-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>

		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'center_icons' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'center_icons' ) ); ?>" type="checkbox" value="1" <?php checked( true, $instance['center_icons'] ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'center_icons' ) ); ?>"><?php esc_html_e( 'Center Icons', 'naked-social-widget' ); ?></label>
		</p>

		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'format_icons' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'format_icons' ) ); ?>" type="checkbox" value="1" <?php checked( true, $instance['format_icons'] ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'format_icons' ) ); ?>"><?php esc_html_e( 'Format icons', 'naked-social-widget' ); ?></label>
		</p>

		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hide_site_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_site_name' ) ); ?>" type="checkbox" value="1" <?php checked( true, $instance['hide_site_name'] ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_site_name' ) ); ?>"><?php esc_html_e( 'Hide site name', 'naked-social-widget' ); ?></label>
		</p>

		<div id="nsw-<?php echo esc_attr( $this->id ); ?>" class="naked-social-widget-sites">
			<?php
			// Display fields for each social media site.
			foreach ( $instance['sites'] as $i => $site ) {

				?>
				<div class="naked-social-widget-site">
					<p>
						<label for="<?php echo $this->get_field_id( 'sites[' . $i . '][name]' ); ?>"><?php _e( 'Site Name', 'naked-social-widget' ); ?></label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'sites[' . $i . '][name]' ); ?>" name="<?php echo $this->get_field_name( 'sites[' . $i . '][name]' ); ?>" value="<?php echo esc_attr( $site['name'] ); ?>">
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'sites[' . $i . '][url]' ); ?>"><?php _e( 'Profile URL', 'naked-social-widget' ); ?></label>
						<input type="url" class="widefat" id="<?php echo $this->get_field_id( 'sites[' . $i . '][url]' ); ?>" name="<?php echo $this->get_field_name( 'sites[' . $i . '][url]' ); ?>" value="<?php echo esc_attr( $site['url'] ); ?>" placeholder="http://">
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'sites[' . $i . '][followers]' ); ?>"><?php _e( 'Number of followers', 'naked-social-widget' ); ?></label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'sites[' . $i . '][followers]' ); ?>" name="<?php echo $this->get_field_name( 'sites[' . $i . '][followers]' ); ?>" value="<?php echo esc_attr( $site['followers'] ); ?>">
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'sites[' . $i . '][label]' ); ?>"><?php _e( 'Label (appears below number)', 'naked-social-widget' ); ?></label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'sites[' . $i . '][label]' ); ?>" name="<?php echo $this->get_field_name( 'sites[' . $i . '][label]' ); ?>" value="<?php echo esc_attr( $site['label'] ); ?>">
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'sites[' . $i . '][icon]' ); ?>"><?php printf( __( '<a href="%s" target="_blank">Font Awesome</a> Icon Name', 'naked-social-widget' ), esc_url( 'http://fortawesome.github.io/Font-Awesome/icons/' ) ); ?></label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'sites[' . $i . '][icon]' ); ?>" name="<?php echo $this->get_field_name( 'sites[' . $i . '][icon]' ); ?>" value="<?php echo esc_attr( $site['icon'] ); ?>">
					</p>

					<div class="nsw-site-actions">
						<button type="button" class="button" title="<?php esc_attr_e( 'Remove this site', 'naked-social-widget' ); ?>" onclick="jQuery(this).parent().parent().remove(); return false;">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				</div>
				<?php

			}
			?>
		</div>

		<div class="nsw-add-another-site">
			<button type="button" class="button" title="<?php esc_attr_e( 'Add another site', 'naked-social-widget' ); ?>" onclick="return nwsAddSite('#nsw-<?php echo esc_attr( $this->id ); ?>');">
				<?php esc_html_e( 'Add Site', 'naked-social-widget' ); ?>
			</button>
		</div>
		<?php

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see    WP_Widget::update()
	 *
	 * @param array  $new_instance                Values just sent to be saved.
	 * @param array  $old_instance                Previously saved values from database.
	 *
	 * @global array $naked_social_widget_options Plugin settings
	 *
	 * @access public
	 * @since  1.0
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['sites'] = array();

		// Save checkboxes
		foreach ( array( 'center_icons', 'format_icons', 'hide_site_name' ) as $option ) {
			$instance[ $option ] = isset( $new_instance[ $option ] ) ? true : false;
		}

		// Save sites
		if ( isset( $new_instance['sites'] ) && is_array( $new_instance['sites'] ) ) {
			foreach ( $new_instance['sites'] as $key => $options ) {
				if ( ! is_array( $options ) ) {
					continue;
				}

				$sanitized_values = array(
					'name'      => array_key_exists( 'name', $options ) ? sanitize_text_field( $options['name'] ) : '',
					'site'      => array_key_exists( 'site', $options ) ? wp_strip_all_tags( $options['site'] ) : '',
					'url'       => ( array_key_exists( 'url', $options ) && $options['url'] ) ? esc_url_raw( $options['url'] ) : '',
					'followers' => array_key_exists( 'followers', $options ) ? sanitize_text_field( $options['followers'] ) : '',
					'label'     => array_key_exists( 'label', $options ) ? sanitize_text_field( $options['label'] ) : '',
					'icon'      => array_key_exists( 'icon', $options ) ? sanitize_html_class( $options['icon'] ) : '',
				);

				$instance['sites'][ $key ] = $sanitized_values;
			}
		}

		return $instance;
	}

}

/**
 * Register widget.
 */
add_action( 'widgets_init', function () {
	register_widget( 'Naked_Social_Widget' );
} );