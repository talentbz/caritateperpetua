<?php

namespace Objectiv\Plugins\Checkout\Admin;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Model\Bump;

class DataUpgrader {
	public function __construct() {}

	/**
	 * @throws \Exception
	 */
	public function init() {
		global $wpdb;

		$db_version = get_option( 'cfw_db_version', false );

		// Don't run upgrades for first time activators
		if ( ! $db_version ) {
			$this->update_version();
			return;
		}

		// 3.0.0 upgrades
		if ( version_compare( '3.0.0', $db_version, '>' ) ) {
			cfw_get_active_template()->init();

			if ( SettingsManager::instance()->get_setting( 'allow_tracking' ) === 1 ) {
				SettingsManager::instance()->update_setting( 'allow_tracking', md5( trailingslashit( home_url() ) ) );
			}
		}

		// 3.3.0 upgrades
		if ( version_compare( '3.3.0', $db_version, '>' ) ) {
			SettingsManager::instance()->add_setting( 'override_view_order_template', 'yes' );

			// Do this again because we are dumb
			if ( SettingsManager::instance()->get_setting( 'allow_tracking' ) === 1 ) {
				SettingsManager::instance()->update_setting( 'allow_tracking', md5( trailingslashit( home_url() ) ) );
			}
		}

		// 3.6.1 upgrades
		if ( version_compare( '3.6.1', $db_version, '>' ) ) {
			// Set default glass accent color
			SettingsManager::instance()->update_setting( 'accent_color', '#dee6fe', true, array( 'glass' ) );
		}

		// 3.14.0 upgrades
		if ( version_compare( '3.14.0', $db_version, '>' ) ) {
			// Set default glass accent color
			SettingsManager::instance()->add_setting( 'enable_order_review_step', 'no' );
		}

		// 5.3.0 upgrades
		if ( version_compare( '5.3.0', $db_version, '>' ) ) {
			foreach ( cfw_get_available_templates() as $template ) {
				$breadcrumb_completed_text_color   = '#7f7f7f';
				$breadcrumb_current_text_color     = '#333333';
				$breadcrumb_next_text_color        = '#7f7f7f';
				$breadcrumb_completed_accent_color = '#333333';
				$breadcrumb_current_accent_color   = '#333333';
				$breadcrumb_next_accent_color      = '#333333';

				if ( $template->get_slug() === 'glass' ) {
					$breadcrumb_current_text_color   = SettingsManager::instance()->get_setting( 'button_color', array( 'glass' ) );
					$breadcrumb_current_accent_color = SettingsManager::instance()->get_setting( 'button_color', array( 'glass' ) );
					$breadcrumb_next_text_color      = '#dfdcdb';
					$breadcrumb_next_accent_color    = '#dfdcdb';

				} elseif ( $template->get_slug() === 'futurist' ) {
					$futurist_header_bg_color          = SettingsManager::instance()->get_setting( 'header_background_color', array( $template->get_slug() ) );
					$color                             = '#ffffff' === $futurist_header_bg_color ? '#333333' : '#222222';
					$breadcrumb_completed_text_color   = $color;
					$breadcrumb_current_text_color     = $color;
					$breadcrumb_next_text_color        = $color;
					$breadcrumb_completed_accent_color = $color;
					$breadcrumb_current_accent_color   = $color;
					$breadcrumb_next_accent_color      = $color;
				}

				SettingsManager::instance()->update_setting( 'breadcrumb_completed_text_color', $breadcrumb_completed_text_color, true, array( $template->get_slug() ) );
				SettingsManager::instance()->update_setting( 'breadcrumb_current_text_color', $breadcrumb_current_text_color, true, array( $template->get_slug() ) );
				SettingsManager::instance()->update_setting( 'breadcrumb_next_text_color', $breadcrumb_next_text_color, true, array( $template->get_slug() ) );
				SettingsManager::instance()->update_setting( 'breadcrumb_completed_accent_color', $breadcrumb_completed_accent_color, true, array( $template->get_slug() ) );
				SettingsManager::instance()->update_setting( 'breadcrumb_current_accent_color', $breadcrumb_current_accent_color, true, array( $template->get_slug() ) );
				SettingsManager::instance()->update_setting( 'breadcrumb_next_accent_color', $breadcrumb_next_accent_color, true, array( $template->get_slug() ) );
			}

			// Convert order bump data
			$items = $wpdb->get_results( "SELECT order_item_id, meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '_cfw_order_bump_id';" );

			foreach ( $items as $item ) {
				$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $item->order_item_id ) );

				$order = \wc_get_order( (int) $order_id );
				if ( $order ) {
					if ( (int) $order->get_meta( 'cfw_has_bump' ) === 1 ) {
						continue;
					}
					$order->add_meta_data( 'cfw_has_bump', true );
					$order->add_meta_data( 'cfw_bump_' . $item->meta_value, true );
					$order->save();
				}
			}
		}

		// 5.3.1 upgrades
		if ( version_compare( '5.3.1', $db_version, '>' ) ) {
			foreach ( cfw_get_available_templates() as $template ) {
				$template->init();
			}

			$settings_manager = SettingsManager::instance();
			$settings_manager->update_setting( 'summary_background_color', '#f8f8f8', false, array( 'futurist' ) );

			// Force save the settings
			$settings_manager->set_settings_obj( $settings_manager->settings );
		}

		if ( version_compare( '5.3.2', $db_version, '>' ) ) {
			$futurist_header_bg_color = SettingsManager::instance()->get_setting( 'header_background_color', array( 'futurist' ) );
			$color                    = '#ffffff' === $futurist_header_bg_color ? '#333333' : $futurist_header_bg_color;

			SettingsManager::instance()->update_setting( 'breadcrumb_completed_text_color', $color, true, array( 'futurist' ) );
			SettingsManager::instance()->update_setting( 'breadcrumb_current_text_color', $color, true, array( 'futurist' ) );
			SettingsManager::instance()->update_setting( 'breadcrumb_next_text_color', $color, true, array( 'futurist' ) );
			SettingsManager::instance()->update_setting( 'breadcrumb_completed_accent_color', $color, true, array( 'futurist' ) );
			SettingsManager::instance()->update_setting( 'breadcrumb_current_accent_color', $color, true, array( 'futurist' ) );
			SettingsManager::instance()->update_setting( 'breadcrumb_next_accent_color', $color, true, array( 'futurist' ) );
		}

		if ( version_compare( '6.0.0', $db_version, '>' ) ) {
			SettingsManager::instance()->add_setting( 'enable_international_phone_field', 'no' );
			SettingsManager::instance()->add_setting( 'enable_side_cart', 'no' );
			SettingsManager::instance()->add_setting( 'enable_free_shipping_progress_bar', 'no' );
			SettingsManager::instance()->add_setting( 'side_cart_free_shipping_threshold', '' );
			SettingsManager::instance()->add_setting( 'side_cart_amount_remaining_message', '' );
			SettingsManager::instance()->add_setting( 'side_cart_free_shipping_message', '' );
			SettingsManager::instance()->add_setting( 'side_cart_free_shipping_threshold', '' );
			SettingsManager::instance()->add_setting( 'side_cart_free_shipping_progress_indicator_color', cfw_get_active_template()->get_default_setting( 'button_color' ) );
			SettingsManager::instance()->add_setting( 'enable_floating_cart_button', 'yes' );
			SettingsManager::instance()->add_setting( 'floating_cart_button_bottom_position', '20' );
			SettingsManager::instance()->add_setting( 'floating_cart_button_right_position', '20' );
			SettingsManager::instance()->add_setting( 'enable_ajax_add_to_cart', 'no' );
		}

		if ( version_compare( '6.0.1', $db_version, '>' ) ) {
			SettingsManager::instance()->add_setting( 'side_cart_free_shipping_progress_bg_color', '#f5f5f5' );
		}

		if ( version_compare( '6.0.6', $db_version, '>' ) ) {
			SettingsManager::instance()->add_setting( 'enable_order_bumps_on_side_cart', 'no' );
		}

		if ( version_compare( '6.1.0', $db_version, '>' ) ) {
			SettingsManager::instance()->add_setting( 'side_cart_icon_color', '#222222' );
			SettingsManager::instance()->add_setting( 'side_cart_icon_width', '34' );
			SettingsManager::instance()->add_setting( 'side_cart_icon', 'cart-outline.svg' );
		}

		if ( version_compare( '6.1.4', $db_version, '>' ) ) {
			SettingsManager::instance()->add_setting( 'hide_floating_cart_button_empty_cart', 'no' );
			SettingsManager::instance()->add_setting( 'enable_astra_support', 'no' );

			$bumps = Bump::get_all();

			foreach ( $bumps as $bump ) {
				add_post_meta( $bump->get_id(), 'captured_revenue', $bump->get_estimated_revenue() );
			}
		}

		$this->update_version();
	}

	private function update_version() {
		$db_version = get_option( 'cfw_db_version', '0.0.0' );

		// Only update db version if the current version is greater than the db version
		if ( version_compare( CFW_VERSION, $db_version, '>' ) ) {
			update_option( 'cfw_db_version', CFW_VERSION );
		}
	}
}
