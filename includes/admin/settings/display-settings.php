<?php
/**
 * Display the admin options page.
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
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function naked_social_widget_options_page() {

	$settings_tabs = naked_social_widget_get_settings_tabs();
	$settings_tabs = empty( $settings_tabs ) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $settings_tabs ) ? $_GET['tab'] : 'general';
	$sections      = naked_social_widget_get_settings_tab_sections( $active_tab );
	$key           = 'main';

	if ( is_array( $sections ) ) {
		$key = key( $sections );
	}

	$registered_sections = naked_social_widget_get_settings_tab_sections( $active_tab );
	$section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( $_GET['section'], $registered_sections ) ? $_GET['section'] : $key;
	ob_start();
	?>
	<div class="wrap">
		<h1 class="nav-tab-wrapper">
			<?php
			foreach ( naked_social_widget_get_settings_tabs() as $tab_id => $tab_name ) {
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab'              => $tab_id,
				) );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );
				$active  = $active_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
				echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h1>

		<?php
		$number_of_sections = count( $sections );
		$number             = 0;

		if ( $number_of_sections > 1 ) {
			echo '<div><ul class="subsubsub">';
			foreach ( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number ++;
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab'              => $active_tab,
					'section'          => $section_id
				) );
				$class   = '';
				if ( $section == $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';
				if ( $number != $number_of_sections ) {
					echo ' | ';
				}
				echo '</li>';
			}
			echo '</ul></div>';
		}
		?>

		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
					<?php
					settings_fields( 'naked_social_widget_settings' );
					if ( 'main' === $section ) {
						do_action( 'naked-social-widget/settings/tab/top', $active_tab );
					}
					do_action( 'naked-social-widget/settings/tab/top/' . $active_tab . '_' . $section );
					do_settings_sections( 'naked_social_widget_settings_' . $active_tab . '_' . $section );
					do_action( 'naked-social-widget/settings/tab/bottom/' . $active_tab . '_' . $section );
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}