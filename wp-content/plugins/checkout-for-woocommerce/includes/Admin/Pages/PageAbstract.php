<?php

namespace Objectiv\Plugins\Checkout\Admin\Pages;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * Class Admin
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
abstract class PageAbstract {
	protected $title;
	protected $capability;
	protected $slug;
	protected $priority           = 100;
	protected static $parent_slug = 'cfw-settings';

	/**
	 * PageAbstract constructor.
	 * @param $title
	 * @param $capability
	 * @param string|null $slug
	 */
	public function __construct( $title, $capability, string $slug = null ) {
		$this->title      = $title;
		$this->capability = $capability;
		$this->slug       = join( '-', array_filter( array( self::$parent_slug, $slug ) ) );
	}

	/**
	 * @param int $priority
	 * @return $this
	 */
	public function set_priority( int $priority ) {
		$this->priority = $priority;

		return $this;
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'setup_menu' ), $this->priority );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu_node' ), 100 + $this->priority );
	}

	public function setup_menu() {
		add_submenu_page( self::$parent_slug, $this->title, $this->title, $this->capability, $this->slug, array( $this, 'output_with_wrap' ), $this->priority );
	}

	public function get_url(): string {
		$url = add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) );

		return esc_url( $url );
	}

	public function is_current_page(): bool {
		return ( $_GET['page'] ?? '' ) === $this->slug;
	}

	abstract public function output();

	/**
	 * The admin page wrap
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function output_with_wrap() {
		?>
		<div class="cfw-admin-page-heading">
			<h1>
				<span><?php echo cfw__( 'CheckoutWC', 'checkout-wc' ); ?> &gt; </span><?php echo $this->title; ?>
			</h1>
		</div>

		<div class="cfw-admin-content-wrap cfw-admin-screen-<?php echo sanitize_title_with_dashes( $this->title ); ?>">
			<div class="wp-header-end"></div>
			<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
			<script type="text/javascript">window.Beacon('init', '355a5a54-eb9d-4b64-ac5f-39c95644ad36')</script>

			<?php $this->output(); ?>
		</div>
		<?php
	}

	public function maybe_show_overridden_setting_notice( $show = false, $replacement_text = '' ) {
		if ( ! $show ) {
			return;
		}
		?>
		<div class='cfw-notification-message'>
			<strong><?php cfw_e( 'Setting Overridden', 'checkout-wc' ); ?></strong> &mdash;

			<?php if ( empty( $replacement_text ) ) : ?>
				<?php cfw_e( 'This setting is currently programmatically overridden. To enable it remove your custom code.', 'checkout-wc' ); ?>
			<?php else : ?>
				<?php echo $replacement_text; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * @param string $required_plans
	 * @return string
	 */
	public function get_upgrade_required_notice( string $required_plans ): string {
		ob_start();
		?>
		<div class='cfw-license-upgrade-blocker'>
			<div class="inner">
				<h3>
					<?php cfw_e( 'Upgrade Your Plan', 'checkout-wc' ); ?>
				</h3>

				<?php echo sprintf( cfw__( 'A %s plan is required to access this feature.', 'checkout-wc' ), $required_plans ); ?>
				<p>
					<?php echo sprintf( cfw__( 'You can upgrade your license in <a target="_blank" href="%1$s">Account</a>. For help upgrading your license, <a target="_blank" href="%2$s">click here.</a>', 'checkout-wc' ), 'https://www.checkoutwc.com/account/', 'https://kb.checkoutwc.com/article/53-upgrading-your-license' ); ?>
				</p>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	public function output_form_open() {
		?>
		<form name="settings" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<?php
			SettingsManager::instance()->the_nonce();
	}

	public function output_form_close() {
		submit_button();
		?>
		</form>
		<?php
	}

	/**
	 * @param string $setting
	 * @param string $label
	 * @param mixed $default_value
	 * @param array $options
	 * @param array $descriptions
	 * @param bool $enabled
	 * @param string|null $notice
	 * @param bool $show_overridden_notice
	 */
	public function output_radio_group_row( string $setting, string $label, $default_value, array $options, array $descriptions = array(), bool $enabled = true, string $notice = null, bool $show_overridden_notice = false ) {
		$settings   = SettingsManager::instance();
		$value      = $settings->get_setting( $setting );
		$field_name = $settings->get_field_name( $setting );
		?>
		<tr class="<?php echo $setting; ?>-row">
			<th scope="row" valign="top">
				<label for="<?php echo $field_name; ?>">
					<?php echo $label; ?>
				</label>
			</th>
			<td>
				<?php echo $notice; ?>
				<p>
					<?php foreach ( $options as $option_value => $option_label ) : ?>
						<label>
							<input <?php echo ! $enabled ? 'disabled' : ''; ?> type="radio" name="<?php echo $field_name; ?>" value="<?php echo $option_value; ?>" <?php echo $option_value === $value || ( empty( $value ) && $option_value === $default_value ) ? 'checked' : ''; ?> /> <?php echo $option_label; ?><br />
						</label>
					<?php endforeach; ?>
				</p>

				<?php foreach ( $descriptions as $description ) : ?>
					<p class="description">
						<?php echo $description; ?>
					</p>
				<?php endforeach; ?>

				<?php $this->maybe_show_overridden_setting_notice( $show_overridden_notice ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param string $setting
	 * @param string $label
	 * @param string $long_label
	 * @param string $description
	 * @param bool $enabled
	 * @param string|null $notice
	 * @param bool $show_overridden_notice
	 * @param string $overridden_notice
	 */
	public function output_checkbox_row( string $setting, string $label, string $long_label, string $description = '', bool $enabled = true, string $notice = null, bool $show_overridden_notice = false, string $overridden_notice = '' ) {
		$settings   = SettingsManager::instance();
		$field_name = $settings->get_field_name( $setting );
		$value      = $settings->get_setting( $setting );
		$checked    = 'yes' === $value && $enabled;
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo esc_attr( $field_name ); ?>">
					<?php echo $label; ?>
				</label>
			</th>
			<td>
				<?php echo $notice; ?>

				<input <?php echo ! $enabled ? 'disabled' : ''; ?> type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="no" />
				<input <?php echo ! $enabled ? 'disabled' : ''; ?> type="checkbox" class="cfw-checkbox cfw-checkbox-<?php echo $setting; ?>" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( 'cfw_checkbox_' . $setting ); ?>" value="yes" <?php echo $checked ? 'checked' : ''; ?> />

				<label class="cfw-checkbox-label cfw-checkbox-label-<?php echo $setting; ?>" for="<?php echo esc_attr( 'cfw_checkbox_' . $setting ); ?>">
					<?php echo $long_label; ?>
				</label>

				<?php if ( ! empty( $description ) ) : ?>
				<p>
					<span class="description">
						<?php echo $description; ?>
					</span>
				</p>
				<?php endif; ?>

				<?php $this->maybe_show_overridden_setting_notice( $show_overridden_notice, $overridden_notice ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param string $setting
	 * @param string $label
	 * @param string $description
	 * @param bool $enabled
	 * @param bool $show_overridden_notice
	 * @param string $overridden_notice
	 */
	public function output_toggle_checkbox( string $setting, string $label, string $description, bool $enabled = true, bool $show_overridden_notice = false, string $overridden_notice = '' ) {
		$settings   = SettingsManager::instance();
		$field_name = $settings->get_field_name( $setting );
		$value      = $settings->get_setting( $setting );
		$field_id   = "cfw_{$setting}";
		?>
		<div class="postbox cfw-toggle-container">
			<div class="inside">
				<input <?php echo ! $enabled ? 'disabled' : ''; ?> type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="no" />
				<input <?php echo ! $enabled ? 'disabled' : ''; ?> type="checkbox" class="cfw-toggle-checkbox cfw-toggle-checkbox-<?php echo $setting; ?>" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="yes" <?php echo 'yes' === $value ? 'checked' : ''; ?> />

				<label class="cfw-toggle-checkbox-label cfw-toggle-checkbox-label-<?php echo $setting; ?>" for="<?php echo esc_attr( $field_id ); ?>">
					<?php echo $label; ?>
				</label>

				<label class="cfw-toggle-checkbox-text-label" for="<?php echo esc_attr( $field_id ); ?>"><?php echo $label; ?></label>

				<p>
					<span class="description">
						<?php echo $description; ?>
					</span>
				</p>

				<?php
				$this->maybe_show_overridden_setting_notice( $show_overridden_notice, $overridden_notice );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * @param string $setting
	 * @param string $label
	 * @param string $description
	 */
	public function output_text_input_row( string $setting, string $label, string $description ) {
		$settings   = SettingsManager::instance();
		$field_name = $settings->get_field_name( $setting );
		$value      = $settings->get_setting( $setting );
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo esc_attr( $field_name ); ?>">
					<?php echo $label; ?>
				</label>
			</th>
			<td class="cfw-text-input-wrap">
				<input type="text" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $setting ); ?>" />
				<p>
					<span class="description">
						<?php echo $description; ?>
					</span>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param string $setting
	 * @param string $label
	 * @param string $description
	 */
	public function output_number_input_row( string $setting, string $label, string $description ) {
		$settings   = SettingsManager::instance();
		$field_name = $settings->get_field_name( $setting );
		$value      = $settings->get_setting( $setting );
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo esc_attr( $field_name ); ?>">
					<?php echo $label; ?>
				</label>
			</th>
			<td class="cfw-text-input-wrap">
				<input style="width: 75px" type="number" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $setting ); ?>" />
				<p>
					<span class="description">
						<?php echo $description; ?>
					</span>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param string $setting
	 * @param string $label
	 * @param string $description
	 * @param bool $enabled
	 * @param int $textarea_rows
	 * @param string|null $notice
	 */
	public function output_textarea_row( string $setting, string $label, string $description, bool $enabled = true, int $textarea_rows = 6, string $notice = null ) {
		$settings   = SettingsManager::instance();
		$field_name = $settings->get_field_name( $setting );
		$value      = $settings->get_setting( $setting );
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="<?php echo $field_name; ?>">
					<?php echo $label; ?>
				</label>
			</th>
			<td>
				<?php
				echo $notice;

				if ( ! $enabled ) {
					echo '<div id="cfw_textarea_placeholder"></div>';
				} else {
					wp_editor(
						$value,
						sanitize_title_with_dashes( $field_name ),
						array(
							'textarea_rows' => $textarea_rows,
							'quicktags'     => false,
							'media_buttons' => false,
							'textarea_name' => $field_name,
							'tinymce'       => false,
						)
					);
				}
				?>
				<p>
					<span class="description">
						<?php echo $description; ?>
					</span>
				</p>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param \WP_Admin_Bar $admin_bar
	 */
	public function add_admin_bar_menu_node( \WP_Admin_Bar $admin_bar ) {
		if ( ! $this->can_show_admin_bar_button() ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'     => $this->slug,
				'title'  => $this->title,
				'href'   => $this->get_url(),
				'parent' => self::$parent_slug,
			)
		);
	}

	/**
	 * @return bool
	 */
	function can_show_admin_bar_button(): bool {
		if ( ! apply_filters( 'cfw_do_admin_bar', current_user_can( 'manage_options' ) && ( SettingsManager::instance()->get_setting( 'hide_admin_bar_button' ) !== 'yes' || is_cfw_page() ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public static function get_parent_slug(): string {
		return self::$parent_slug;
	}

	/**
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}
}
