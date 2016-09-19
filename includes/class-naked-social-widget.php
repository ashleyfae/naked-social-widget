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

		$profile = new Naked_Social_Profile( $instance, $this->id );

		echo $profile->display();

		echo $args['after_widget'];

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

		global $naked_social_widget_options;

		// Get default values.
		$defaults = array(
			'title'        => __( 'Follow Me', 'naked-social-widget' ),
			'user_id'      => '',
			'show_numbers' => true,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Args for the user dropdown.
		$args = array(
			'selected' => esc_attr( $instance['user_id'] ),
			'name'     => $this->get_field_name( 'user_id' ),
			'id'       => $this->get_field_id( 'user_id' ),
			'class'    => 'widefat',
		);
		?>
		<p><?php printf( __( 'Make sure you configure your settings on the <a href="%s">settings page</a>.', 'naked-social-widget' ), admin_url( 'options-general.php?page=naked-social-widget' ) ); ?></p>

		<?php if ( $naked_social_widget_options['icon_type'] == 'font_awesome' ) : ?>
			<p><?php printf( __( 'You\'ve selected Font Awesome, which means you need to enter the icon name you want to use for each site. You can get icon names from the <a href="%s">Font Awesome website</a>. Enter in the icon name without the fa fa- class prefix. Example: <mark>twitter-square</mark><br><br> If one of your sites isn\'t supported on Font Awesome, you can upload a custom icon image instead. If you upload an image, that will take priority over anything you add in the text box.', 'naked-social-widget' ), 'http://fortawesome.github.io/Font-Awesome/icons/' ); ?></p>
		<?php endif; ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'naked-social-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( 'Select the user that this is for:', 'naked-social-widget' ); ?></label>
			<?php wp_dropdown_users( $args ); ?>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_numbers' ); ?>" name="<?php echo $this->get_field_name( 'show_numbers' ); ?>" <?php checked( (bool) $instance['show_numbers'], true ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_numbers' ); ?>"><?php _e( 'Check to display follower numbers', 'naked-social-widget' ); ?></label>
		</p>

		<?php
		if ( ! is_array( $naked_social_widget_options['social_sites'] ) ) {
			return;
		}

		// Display fields for each social media site.
		foreach ( $naked_social_widget_options['social_sites'] as $i => $site ) {

			if ( ! array_key_exists( 'name', $site ) ) {
				continue;
			}

			$is_mapped = ( array_key_exists( 'site', $site ) && ! empty( $site['site'] ) && array_key_exists( $site['site'], naked_social_widget_get_auto_sites() ) );
			$key       = naked_social_widget_sanitize_key( $site['name'] . '_' . $i );

			echo '<hr>';
			echo '<p><strong>' . esc_html( $site['name'] ) . '</strong></p>';

			// Our saved values.
			$profile_url    = isset( $instance[ $key . '_profile_url' ] ) ? esc_attr( $instance[ $key . '_profile_url' ] ) : '';
			$followers      = isset( $instance[ $key . '_followers' ] ) ? esc_attr( $instance[ $key . '_followers' ] ) : '';
			$follower_label = isset( $instance[ $key . '_label' ] ) ? esc_attr( $instance[ $key . '_label' ] ) : '';
			$input_type     = ( $is_mapped && naked_social_widget_site_class( $site['site'] )->profile_type == 'username' ) ? 'text' : 'url';

			$field_title = ( $input_type == 'text' ) ? esc_html__( 'Username', 'naked-social-widget' ) : esc_html__( 'URL', 'naked-social-widget' );
			?>
			<p>
				<label for="<?php echo $this->get_field_id( $key . '_profile_url' ); ?>"><?php printf( '%s %s', $site['name'], $field_title ); ?></label>
				<input type="<?php echo $input_type; ?>" class="widefat" id="<?php echo $this->get_field_id( $key . '_profile_url' ); ?>" name="<?php echo $this->get_field_name( $key . '_profile_url' ); ?>" value="<?php echo esc_attr( $profile_url ); ?>" placeholder="<?php echo ! $is_mapped ? 'http://' : ''; ?>">
			</p>
			<?php

			// If this isn't a mapped site then include a box for the follower number.
			if ( ! $is_mapped ) : ?>
				<p>
					<label for="<?php echo $this->get_field_id( $key . '_followers' ); ?>"><?php printf( __( 'Number of %s followers', 'naked-social-widget' ), esc_html( $site['name'] ) ); ?></label>
					<input type="<?php echo $input_type; ?>" class="widefat" id="<?php echo $this->get_field_id( $key . '_followers' ); ?>" name="<?php echo $this->get_field_name( $key . '_followers' ); ?>" value="<?php echo esc_attr( $followers ); ?>">
				</p>
			<?php endif;

			// Follower label.
			?>
			<p>
				<label for="<?php echo $this->get_field_id( $key . '_label' ); ?>"><?php _e( 'Label (appears below number)', 'naked-social-widget' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( $key . '_label' ); ?>" name="<?php echo $this->get_field_name( $key . '_label' ); ?>" value="<?php echo esc_attr( $follower_label ); ?>">
			</p>
			<?php

			// Otherwise, Font Awesome, baby!
			if ( $naked_social_widget_options['icon_type'] == 'font_awesome' ) {
				$fa_icon = isset( $instance[ $key . '_fa' ] ) ? esc_attr( $instance[ $key . '_fa' ] ) : '';
				?>
				<p>
					<label for="<?php echo $this->get_field_id( $key . '_fa' ); ?>"><?php printf( __( '<a href="%s" target="_blank">Font Awesome</a> Icon Name', 'naked-social-widget' ), esc_url( 'http://fortawesome.github.io/Font-Awesome/icons/' ) ); ?></label>
					<input type="text" class="widefat" id="<?php echo $this->get_field_id( $key . '_fa' ); ?>" name="<?php echo $this->get_field_name( $key . '_fa' ); ?>" value="<?php echo esc_attr( $fa_icon ); ?>" placeholder="twitter">
				</p>
				<?php
			}

			// Upload a custom image.
			$icon        = isset( $instance[ $key . '_icon' ] ) ? $instance[ $key . '_icon' ] : '';
			$input_label = ( $naked_social_widget_options['icon_type'] == 'font_awesome' ) ? __( 'Icon (if not available on FA):', 'naked-social-widget' ) : __( 'Icon:', 'naked-social-widget' );
			?>
			<div id="<?php echo $this->get_field_id( $key . '_icon' ); ?>_wrapper" class="upload_wrapper" style="margin: 1em 0;">
				<label for="<?php echo $this->get_field_id( $key . '_icon' ); ?>"><?php echo $input_label; ?></label>
				<br><br>

				<?php
				if ( ! empty( $icon ) ) {
					$attr = array(
						'id'    => $this->get_field_id( $key . '_icon' ) . '_image',
						'style' => 'margin:0 auto 5px;padding:0;max-width:100%;display:block;height:auto;'
					);
					echo wp_get_attachment_image( intval( $icon ), 'full', false, $attr );
				} else {
					?>
					<img id="<?php echo $this->get_field_id( $key . '_icon' ); ?>_image" src="" style="display: none;">
					<?php
				}
				?>
				<input type="hidden" class="widefat naked_social_widget_image_url" name="<?php echo $this->get_field_name( $key . '_icon' ); ?>" id="<?php echo $this->get_field_id( $key . '_icon' ); ?>" value="<?php echo esc_attr( $icon ); ?>">

				<div style="clear: both; overflow: hidden;">
					<input type="button" value="<?php _e( 'Upload Icon', 'ubb' ); ?>" class="button naked_social_widget_upload_image_button widefat" id="<?php echo $this->get_field_id( $key . '_icon' ); ?>_upload" style="float: left; width: 48%;" onclick="return naked_social_widget_open_uploader('<?php echo $this->get_field_id( $key . '_icon' ); ?>');">
					<input type="button" value="<?php _e( 'Remove Icon', 'ubb' ); ?>" class="button naked_social_widget_image_remove_button" id="<?php echo $this->get_field_id( $key . '_icon' ); ?>_remove" style="float: right; width: 48%; <?php echo empty( $icon ) ? 'display: none;' : ''; ?>" onclick="return naked_social_widget_clear_uploader('<?php echo $this->get_field_id( $key . '_icon' ); ?>');">
				</div>
			</div>
			<?php

		}

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
		global $naked_social_widget_options;
		$instance = array();

		$instance['title']        = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['user_id']      = absint( $new_instance['user_id'] );
		$instance['show_numbers'] = strip_tags( $new_instance['show_numbers'] );

		if ( is_array( $naked_social_widget_options['social_sites'] ) ) {

			foreach ( $naked_social_widget_options['social_sites'] as $i => $site ) {

				if ( ! array_key_exists( 'name', $site ) ) {
					continue;
				}

				$is_mapped = ( array_key_exists( 'site', $site ) && ! empty( $site['site'] ) && array_key_exists( $site['site'], naked_social_widget_get_auto_sites() ) );
				$key       = naked_social_widget_sanitize_key( $site['name'] . '_' . $i );

				// Update the username/URL.
				$instance[ $key . '_profile_url' ] = isset( $new_instance[ $key . '_profile_url' ] ) ? trim( strip_tags( $new_instance[ $key . '_profile_url' ] ) ) : '';

				// Update the number of followers.
				if ( ! $is_mapped ) {
					$instance[ $key . '_followers' ] = isset( $new_instance[ $key . '_followers' ] ) ? strip_tags( $new_instance[ $key . '_followers' ] ) : 0;
				}

				// Update the follower label.
				$instance[ $key . '_label' ] = isset( $new_instance[ $key . '_label' ] ) ? sanitize_text_field( $new_instance[ $key . '_label' ] ) : '';

				// If we're using Font Awesome, save that.
				if ( $naked_social_widget_options['icon_type'] == 'font_awesome' ) {
					$icon_name = isset( $new_instance[ $key . '_fa' ] ) ? sanitize_html_class( $new_instance[ $key . '_fa' ] ) : '';
					// Strip "fa " and "fa-" if they entered those.
					$instance[ $key . '_fa' ] = str_replace( array( 'fa ', 'fa-' ), '', $icon_name );
				}

				// Save the custom icon
				$instance[ $key . '_icon' ] = ( isset( $new_instance[ $key . '_icon' ] ) && is_numeric( $new_instance[ $key . '_icon' ] ) ) ? absint( $new_instance[ $key . '_icon' ] ) : '';

			}

		}

		// Delete cache.
		delete_option( 'naked_social_widget_followers_' . absint( $instance['user_id'] ) . '_expiry' );

		return $instance;
	}

}

/**
 * Register widget.
 */
add_action( 'widgets_init', function () {
	register_widget( 'Naked_Social_Widget' );
} );