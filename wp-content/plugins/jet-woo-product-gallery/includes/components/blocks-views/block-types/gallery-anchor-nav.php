<?php
/**
 * JetGallery Archive Nav Block Type.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Type_Anchor_Nav' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Type_Anchor_Nav class.
	 */
	class Jet_Gallery_Blocks_Views_Type_Anchor_Nav extends Jet_Gallery_Blocks_Views_Type_Base {

		/**
		 * Returns block name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'gallery-anchor-nav';
		}

		public function get_css_scheme() {

			$css_scheme = [
				'item'                      => '.jet-woo-product-gallery__image-item',
				'items'                     => '.jet-woo-product-gallery-anchor-nav-items',
				'controller'                => '.jet-woo-product-gallery-anchor-nav-controller',
				'controller-bullet'         => '.jet-woo-product-gallery-anchor-nav-controller .controller-item__bullet',
				'controller-bullet-current' => '.jet-woo-product-gallery-anchor-nav-controller .current-item .controller-item__bullet',
			];

			return array_merge( parent::get_css_scheme(), $css_scheme );

		}

		/**
		 * Add style block options.
		 *
		 * @return boolean
		 */
		public function add_style_manager_options() {

			// Images style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_images_style',
					'initial_open' => true,
					'title'        => __( 'Images', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_space_between',
					'label'        => __( 'Space Between Images', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'attributes'   => [
						'default' => [
							'value' => 5,
						],
					],
					'separator'    => 'after',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['item'] . '+' . $this->css_scheme['item'] ) => 'margin-top: {{VALUE}}px;',
					],
				]
			);

			// Common images controls.
			$this->register_common_images_style_controls();

			$this->controls_manager->end_section();

			// Images style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_navigation_style',
					'initial_open' => false,
					'title'        => __( 'Navigation', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'navigation_width',
					'label'        => __( 'Width', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'attributes'   => [
						'default' => [
							'value' => [
								'value' => 10,
								'unit'  => '%',
							],
						],
					],
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 70,
								'max' => 500,
							],
						],
						[
							'value'     => '%',
							'intervals' => [
								'min' => 0,
								'max' => 50,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller'] ) => 'max-width: {{VALUE}}{{UNIT}};',
						$this->css_selector( $this->css_scheme['items'] )      => 'max-width: calc(100% - {{VALUE}}{{UNIT}});',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'navigation_offset_top',
					'label'        => __( 'Offset Top', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 0,
								'max' => 500,
							],
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller'] ) => 'margin-top: {{VALUE}}{{UNIT}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'navigation_bullets_width',
					'label'        => __( 'Bullets Width', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 0,
								'max' => 100,
							],
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] ) => 'width: {{VALUE}}{{UNIT}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'navigation_bullets_height',
					'label'        => __( 'Bullets Height', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 0,
								'max' => 100,
							],
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] ) => 'height: {{VALUE}}{{UNIT}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'navigation_bullets_margin',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] ) => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'navigation_bullets_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id'        => 'navigation_bullets_style_tabs',
					'separator' => 'before',
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'navigation_bullets_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'navigation_bullets_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'navigation_bullets_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'navigation_bullets_bg_hover',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] . ':hover' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'navigation_bullets_border_color_hover',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet'] . ':hover' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'navigation_bullets_current_style_tab',
					'title' => __( 'Current', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'navigation_bullets_bg_current',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet-current'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'navigation_bullets_border_color_current',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['controller-bullet-current'] ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->end_section();

			// Photoswipe Gallery view style controls.
			$this->register_photoswipe_gallery_style_controls();

			// Photoswipe Gallery trigger button style controls.
			$this->register_photoswipe_gallery_button_trigger_style_controls();

			// Video style controls.
			$this->register_video_style_controls();

			// Video play button style controls.
			$this->register_video_play_button_style_controls();

			// Video popup button style controls.
			$this->register_video_popup_button_style_controls();

		}

	}

}