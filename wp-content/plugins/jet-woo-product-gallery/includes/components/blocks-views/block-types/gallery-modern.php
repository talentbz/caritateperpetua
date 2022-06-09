<?php
/**
 * JetGallery Modern Block Type.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Type_Modern' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Type_Modern class.
	 */
	class Jet_Gallery_Blocks_Views_Type_Modern extends Jet_Gallery_Blocks_Views_Type_Base {

		/**
		 * Returns block name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'gallery-modern';
		}

		public function get_css_scheme() {

			$css_scheme = [
				'wrapper' => '.jet-woo-product-gallery-modern',
				'image-2' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+2)',
				'image-3' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+3)',
				'image-4' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+4)',
				'image-5' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+5)',
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
					'id'           => 'first_images_proportion',
					'label'        => __( 'First Proportion', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'attributes'   => [
						'default' => [
							'value' => [
								'value' => 30,
								'unit'  => '%',
							],
						],
					],
					'units'        => [
						[
							'value'     => '%',
							'intervals' => [
								'min' => 10,
								'max' => 90,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-2'] ) => 'max-width: {{VALUE}}{{UNIT}};',
						$this->css_selector( $this->css_scheme['image-3'] ) => 'max-width: calc(100% - {{VALUE}}{{UNIT}});',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'second_images_proportion',
					'label'        => __( 'Second Proportion', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'attributes'   => [
						'default' => [
							'value' => [
								'value' => 70,
								'unit'  => '%',
							],
						],
					],
					'units'        => [
						[
							'value'     => '%',
							'intervals' => [
								'min' => 10,
								'max' => 90,
							],
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-4'] ) => 'max-width: {{VALUE}}{{UNIT}};',
						$this->css_selector( $this->css_scheme['image-5'] ) => 'max-width: calc(100% - {{VALUE}}{{UNIT}});',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'      => 'second_image_heading',
					'type'    => 'text',
					'content' => __( 'Image 2', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'second_image_margin',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-2'] ) => 'margin: {{TOP}} auto {{BOTTOM}} auto;',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'second_image_vertical_alignment',
					'label'        => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'flex-start' => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center'     => [
							'shortcut' => __( 'Middle', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'flex-end'   => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-2'] ) => 'align-self: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'      => 'third_image_heading',
					'type'    => 'text',
					'content' => __( 'Image 3', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'third_image_margin',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-3'] ) => 'margin: {{TOP}} auto {{BOTTOM}} auto;',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'third_image_vertical_alignment',
					'label'        => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'flex-start' => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center'     => [
							'shortcut' => __( 'Middle', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'flex-end'   => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-3'] ) => 'align-self: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'      => 'fourth_image_heading',
					'type'    => 'text',
					'content' => __( 'Image 4', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'fourth_image_margin',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-4'] ) => 'margin: {{TOP}} auto {{BOTTOM}} auto;',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'fourth_image_vertical_alignment',
					'label'        => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'flex-start' => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center'     => [
							'shortcut' => __( 'Middle', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'flex-end'   => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-4'] ) => 'align-self: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'      => 'fifth_image_heading',
					'type'    => 'text',
					'content' => __( 'Image 5', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'fifth_image_margin',
					'label'        => __( 'Margin', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-5'] ) => 'margin: {{TOP}} auto {{BOTTOM}} auto;',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'fifth_image_vertical_alignment',
					'label'        => __( 'Vertical Alignment', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'flex-start' => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center'     => [
							'shortcut' => __( 'Middle', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'flex-end'   => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-5'] ) => 'align-self: {{VALUE}};',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_outer_offset',
					'label'        => __( 'Outer Offset', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'both',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['wrapper'] ) => 'margin: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
				]
			);

			// Common images controls.
			$this->register_common_images_style_controls();

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