<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;

use Objectiv\Plugins\Checkout\Model\Template;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 * @package Objectiv\Plugins\Checkout\Admin\Pages
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Appearance extends PageAbstract {
	protected $settings_manager;

	public function __construct( SettingsManager $settings_manager ) {
		$this->settings_manager = $settings_manager;

		parent::__construct( cfw__( 'Appearance', 'checkout-wc' ), 'manage_options', 'appearance' );
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 1000 );
		add_action( $this->settings_manager->prefix . '_settings_saved', array( $this, 'maybe_activate_theme' ) );

		parent::init();
	}

	public function maybe_activate_theme() {
		$prefix = $this->settings_manager->prefix;

		$new_settings = stripslashes_deep( $_REQUEST[ "{$prefix}_setting" ] );

		if ( empty( $new_settings['active_template'] ) ) {
			return;
		}

		$active_template = new Template( $this->settings_manager->get_setting( 'active_template' ) );
		$active_template->init();
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_media();

		wp_enqueue_script( 'cfw-webfont-loader', 'https://cdnjs.cloudflare.com/ajax/libs/webfont/1.6.28/webfontloader.js' );
	}

	public function output() {
		$tabbed_navigation = new \WP_Tabbed_Navigation( 'Appearance', 'subpage' );

		$tabbed_navigation->add_tab( 'Templates', add_query_arg( array( 'subpage' => 'templates' ), $this->get_url() ) );
		$tabbed_navigation->add_tab( 'Design', add_query_arg( array( 'subpage' => 'design' ), $this->get_url() ) );

		if ( $this->get_current_tab() === false ) {
			$_GET['subpage'] = 'templates';
		}

		$tabbed_navigation->display_tabs( false );

		if ( $this->get_current_tab() === 'templates' ) {
			$this->templates_tab();
		}

		if ( $this->get_current_tab() === 'design' ) {
			$this->design_tab();
		}
	}

	public function templates_tab() {
		$settings        = SettingsManager::instance();
		$templates       = Template::get_all_available();
		$active_template = $settings->get_setting( 'active_template' );
		?>
		<div class="theme-browser cfw-theme-browser">
			<div class="themes wp-clearfix">
				<?php
				foreach ( $templates as $template ) :
					$screenshot = $template->get_template_uri() . '/screenshot.png';

					$active = ( $active_template === $template->get_slug() );
					?>
					<?php add_thickbox(); ?>
					<div class="theme <?php echo $active ? 'active' : ''; ?>">
						<div class="theme-screenshot">
							<a href="#TB_inline?width=1200&height=900&inlineId=theme-preview-<?php echo $template->get_slug(); ?>" class="thickbox">
								<img class="theme-screenshot-img" src="<?php echo $screenshot; ?>" />
							</a>
							<div id="theme-preview-<?php echo $template->get_slug(); ?>" style="display:none;">
								<img src="<?php echo $screenshot; ?>" />
							</div>
						</div>
						<div class="theme-id-container">

							<h2 class="theme-name" id="<?php echo $template->get_slug(); ?>-name">
								<strong>
									<?php echo $active ? cfw__( 'Active: ' ) : ''; ?>
								</strong>

								<?php echo $template->get_name(); ?>
							</h2>

							<?php if ( ! $active ) : ?>
								<div class="theme-actions">
									<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
										<input type="hidden" name="<?php echo $settings->get_field_name( 'active_template' ); ?>" value="<?php echo $template->get_slug(); ?>" />
										<?php $settings->the_nonce(); ?>
										<?php submit_button( cfw__( 'Activate', 'checkout-wc' ), 'button-secondary', $name = 'submit', $wrap = false ); ?>
									</form>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function design_tab() {
		$fonts_list           = $this->get_fonts_list();
		$settings             = SettingsManager::instance();
		$current_body_font    = $settings->get_setting( 'body_font' );
		$current_heading_font = $settings->get_setting( 'heading_font' );
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<?php $settings->the_nonce(); ?>

			<h1><?php cfw_e( 'Global Settings', 'checkout-wc' ); ?></h1>
			<p><?php cfw_e( 'These settings apply to all themes.', 'checkout-wc' ); ?></p>
			<hr>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row" valign="top">
						<?php cfw_e( 'Logo', 'checkout-wc' ); ?>
					</th>
					<td>
						<div class='cfw-admin-image-preview-wrapper'>
							<img class="cfw-admin-image-preview" src='<?php echo wp_get_attachment_url( $settings->get_setting( 'logo_attachment_id' ) ); ?>' width='100' style='max-height: 100px; width: 100px;'>
						</div>
						<input class="cfw-admin-image-picker-button button" type="button" value="<?php cfw_e( 'Upload image' ); ?>" />
						<input type='hidden' name='<?php echo $settings->get_field_name( 'logo_attachment_id' ); ?>' id='logo_attachment_id' value="<?php echo $settings->get_setting( 'logo_attachment_id' ); ?>">

						<a class="delete-custom-img button secondary-button"><?php cfw_e( 'Clear Logo', 'checkout-wc' ); ?></a>
					</td>
				</tr>

				<tr>
					<?php
					$this->output_radio_group_row(
						'label_style',
						cfw__( 'Field Label Style', 'checkout-wc' ),
						'floating',
						array(
							'floating' => cfw__( 'Floating (Recommended)', 'checkout-wc' ),
							'normal'   => cfw__( 'Normal', 'checkout-wc' ),
						),
						array(
							cfw__( 'Floating: Automatically show and hide labels based on whether the field has a value. (Recommended)', 'checkout-wc' ),
							cfw__( 'Normal: Labels appear above each field at all times.', 'checkout-wc' ),
						)
					);
					?>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="<?php echo sanitize_title_with_dashes( $settings->get_field_name( 'footer_text' ) ); ?>"><?php cfw_e( 'Footer Text', 'checkout-wc' ); ?></label>
					</th>
					<td>
						<?php
						wp_editor(
							$settings->get_setting( 'footer_text' ),
							sanitize_title_with_dashes( $settings->get_field_name( 'footer_text' ) ),
							array(
								'textarea_rows' => 5,
								'textarea_name' => $settings->get_field_name( 'footer_text' ),
								'tinymce'       => true,
							)
						);
						?>
						<p>
							<span class="description">
								<?php cfw_e( 'If left blank, a standard copyright notice will be displayed. Set to a single space to override this behavior.', 'checkout-wc' ); ?>
							</span>
						</p>
					</td>
				</tr>
				</tbody>
			</table>

			<h1><?php cfw_e( 'Theme Specific Settings', 'checkout-wc' ); ?></h1>
			<p>
				<?php echo sprintf( cfw__( 'These settings apply to your selected theme. (%s)', 'checkout-wc' ), cfw_get_active_template()->get_name() ); ?>
			</p>

			<hr />

			<?php $template_path = cfw_get_active_template()->get_slug(); ?>

			<h2>
				<?php cfw_e( 'Fonts', 'checkout-wc' ); ?>
			</h2>

			<table class="form-table template-settings template-<?php echo $template_path; ?>" style="margin-bottom: 3em">
				<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="<?php echo sanitize_title_with_dashes( $settings->get_field_name( 'body_font' ) ); ?>"><?php cfw_e( 'Body Font', 'checkout-wc' ); ?></label>
					</th>
					<td>
						<select id="cfw-body-font-selector" class="wc-enhanced-select" name="<?php echo $settings->get_field_name( 'body_font' ); ?>">
							<option value="System Font Stack"><?php cfw_e( 'System Font Stack', 'checkout-wc' ); ?></option>
							<?php foreach ( $fonts_list->items as $font ) : ?>
								<option value="<?php echo $font->family; ?>"<?php echo $font->family === $current_body_font ? 'selected="selected"' : ''; ?> >
									<?php echo $font->family; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top">
						<label for="<?php echo sanitize_title_with_dashes( $settings->get_field_name( 'heading_font' ) ); ?>"><?php cfw_e( 'Heading Font', 'checkout-wc' ); ?></label>
					</th>
					<td>
						<select id="cfw-heading-font-selector" class="wc-enhanced-select" name="<?php echo $settings->get_field_name( 'heading_font' ); ?>">
							<option value="System Font Stack"><?php cfw_e( 'System Font Stack', 'checkout-wc' ); ?></option>

							<?php foreach ( $fonts_list->items as $font ) : ?>
								<option value="<?php echo $font->family; ?>" <?php echo $font->family === $current_heading_font ? 'selected="selected"' : ''; ?> >
									<?php echo $font->family; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="padding: 0;">
						<p>
							<span class="description">
								<?php cfw_e( 'By default, CheckoutWC uses a System Font Stack, which yields the fastest performance. You may choose to use a Google Font here.', 'checkout-wc' ); ?>
							</span>
						</p>
					</td>
				</tr>
				</tbody>
			</table>

			<h2>
				<?php cfw_e( 'Color Settings', 'checkout-wc' ); ?>
			</h2>

			<?php foreach ( $this->get_theme_color_settings() as $color_settings_section ) : ?>
				<?php
				if ( empty( $color_settings_section['settings'] ) ) {
					continue;
				}
				?>
				<h3>
					<?php echo esc_html( $color_settings_section['title'] ); ?>
				</h3>

				<table class="form-table template-settings template-<?php echo $template_path; ?>">
					<tbody>
						<?php foreach ( $color_settings_section['settings'] as $key => $label ) : ?>
							<?php
							$saved_value   = $settings->get_setting( $key, array( $template_path ) );
							$default_value = cfw_get_active_template()->get_default_setting( $key );
							?>
							<tr>
								<th scope="row" valign="top">
									<label for="<?php echo $settings->get_field_name( $key, array( $template_path ) ); ?>"><?php echo $label; ?></label>
								</th>
								<td>
									<input class="cfw-admin-color-picker" type="text" name="<?php echo $settings->get_field_name( $key, array( $template_path ) ); ?>" value="<?php echo empty( $saved_value ) ? $default_value : $saved_value; ?>" data-default-color="<?php echo $default_value; ?>" />
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>

			<table class="form-table template-settings template-<?php echo $template_path; ?>">
				<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="<?php echo $settings->get_field_name( 'custom_css', array( $template_path ) ); ?>"><?php cfw_e( 'Custom CSS', 'checkout-wc' ); ?></label></th>
					<td id="cfw_css_editor">
						<?php
						wp_editor(
							$settings->get_setting( 'custom_css', array( $template_path ) ),
							sanitize_title_with_dashes( $settings->get_field_name( 'custom_css', array( $template_path ) ) ),
							array(
								'textarea_rows' => 5,
								'quicktags'     => false,
								'media_buttons' => false,
								'textarea_name' => $settings->get_field_name( 'custom_css', array( $template_path ) ),
								'tinymce'       => false,
							)
						);
						?>
						<p>
							<span class="description">
								<?php cfw_e( 'Add Custom CSS rules to fully control the appearance of the checkout template.', 'checkout-wc' ); ?>
							</span>
						</p>
					</td>
				</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
		<?php
	}

	public function get_current_tab() {
		return empty( $_GET['subpage'] ) ? false : $_GET['subpage'];
	}

	public function get_fonts_list() {
		$cfw_google_fonts_list = get_transient( 'cfw_google_font_list' );

		if ( empty( $cfw_google_fonts_list ) ) {
			$cfw_google_fonts_list = wp_remote_get( 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyAkSLrj88M_Y-rFfjRI2vgIzjIZ0N1fynE&sort=popularity' );
			$cfw_google_fonts_list = json_decode( wp_remote_retrieve_body( $cfw_google_fonts_list ) );

			set_transient( 'cfw_google_font_list', $cfw_google_fonts_list, 30 * DAY_IN_SECONDS );
		}

		return $cfw_google_fonts_list;
	}

	/**
	 * @return array
	 */
	public static function get_theme_color_settings(): array {
		$active_template = cfw_get_active_template();
		$color_settings  = array();

		// Body
		$color_settings['body'] = array(
			'title'    => 'Body',
			'settings' => array(),
		);

		$color_settings['body']['settings']['body_background_color'] = cfw__( 'Body Background Color', 'checkout-wc' );
		$color_settings['body']['settings']['body_text_color']       = cfw__( 'Body Text Color', 'checkout-wc' );
		$color_settings['body']['settings']['link_color']            = cfw__( 'Link Color', 'checkout-wc' );

		// Header
		$color_settings['header'] = array(
			'title'    => 'Header',
			'settings' => array(),
		);

		if ( $active_template->supports( 'header-background' ) ) {
			$color_settings['header']['settings']['header_background_color'] = cfw__( 'Header Background Color', 'checkout-wc' );
		}

		$color_settings['header']['settings']['header_text_color'] = cfw__( 'Header Text Color', 'checkout-wc' );

		// Footer
		$color_settings['footer'] = array(
			'title'    => 'Footer',
			'settings' => array(),
		);

		if ( $active_template->supports( 'footer-background' ) ) {
			$color_settings['footer']['settings']['footer_background_color'] = cfw__( 'Footer Background Color', 'checkout-wc' );
		}

		$color_settings['footer']['settings']['footer_color'] = cfw__( 'Footer Text Color', 'checkout-wc' );

		// Cart Summary
		$color_settings['cart_summary'] = array(
			'title'    => 'Cart Summary',
			'settings' => array(),
		);

		if ( $active_template->supports( 'summary-background' ) ) {
			$color_settings['cart_summary']['settings']['summary_background_color'] = cfw__( 'Summary Background Color', 'checkout-wc' );
			$color_settings['cart_summary']['settings']['summary_text_color']       = cfw__( 'Summary Text Color', 'checkout-wc' );
		}

		$color_settings['cart_summary']['settings']['summary_mobile_background_color'] = cfw__( 'Summary Mobile Background Color', 'checkout-wc' );

		$color_settings['cart_summary']['settings']['cart_item_quantity_color']      = cfw__( 'Cart Item Quantity Background Color', 'checkout-wc' );
		$color_settings['cart_summary']['settings']['cart_item_quantity_text_color'] = cfw__( 'Cart Item Quantity Text Color', 'checkout-wc' );

		// Breadcrumbs
		$color_settings['breadcrumbs'] = array(
			'title'    => 'Breadcrumbs',
			'settings' => array(),
		);

		if ( $active_template->supports( 'breadcrumb-colors' ) ) {
			$color_settings['breadcrumbs']['settings']['breadcrumb_completed_text_color']   = cfw__( 'Completed Breadcrumb Completed Text Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_current_text_color']     = cfw__( 'Current Breadcrumb Text Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_next_text_color']        = cfw__( 'Next Breadcrumb Text Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_completed_accent_color'] = cfw__( 'Completed Breadcrumb Accent Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_current_accent_color']   = cfw__( 'Current Breadcrumb Accent Color', 'checkout-wc' );
			$color_settings['breadcrumbs']['settings']['breadcrumb_next_accent_color']      = cfw__( 'Next Breadcrumb Accent Color', 'checkout-wc' );
		}

		$color_settings['buttons'] = array(
			'title'    => 'Buttons',
			'settings' => array(),
		);

		// Buttons
		$color_settings['buttons']['settings']['button_color']                      = cfw__( 'Primary Button Background Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['button_text_color']                 = cfw__( 'Primary Button Text Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['button_hover_color']                = cfw__( 'Primary Button Background Hover Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['button_text_hover_color']           = cfw__( 'Primary Button Text Hover Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_color']            = cfw__( 'Secondary Button Background Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_text_color']       = cfw__( 'Secondary Button Text Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_hover_color']      = cfw__( 'Secondary Button Background Hover Color', 'checkout-wc' );
		$color_settings['buttons']['settings']['secondary_button_text_hover_color'] = cfw__( 'Secondary Button Text Hover Color', 'checkout-wc' );

		// Theme Specific Colors
		$color_settings['active_theme_colors'] = array(
			'title'    => 'Theme Specific Colors',
			'settings' => apply_filters( 'cfw_active_theme_color_settings', array() ),
		);

		return apply_filters( 'cfw_theme_color_settings', $color_settings );
	}
}
