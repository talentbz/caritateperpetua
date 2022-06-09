<?php
/**
 * JetGallery Slider Block Type.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Type_Slider' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Type_Slider class.
	 */
	class Jet_Gallery_Blocks_Views_Type_Slider extends Jet_Gallery_Blocks_Views_Type_Base {

		/**
		 * Returns block name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'gallery-slider';
		}

		public function get_css_scheme() {

			$css_scheme = [
				'image-wrapper'      => '.jet-woo-product-gallery__image',
				'images-arrows'      => '.jet-woo-product-gallery-slider .jet-swiper-nav',
				'thumbnails-wrapper' => '.jet-woo-swiper-gallery-thumbs',
				'thumbnails'         => '.jet-woo-swiper-control-thumbs__item',
				'thumbnails-arrows'  => '.jet-woo-swiper-gallery-thumbs .jet-swiper-nav',
				'pagination'         => '.swiper-pagination',
				'pagination-items'   => '.swiper-pagination .swiper-pagination-bullet',
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
					'id'           => 'images_alignment',
					'label'        => __( 'Alignment', 'jet-woo-product-gallery' ),
					'separator'    => 'after',
					'attributes'   => [
						'default' => [
							'value' => 'left',
						],
					],
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['image-wrapper'] ) => 'text-align: {{VALUE}};',
					],
				]
			);

			// Common images controls.
			$this->register_common_images_style_controls();

			$this->controls_manager->add_control(
				[
					'id'        => 'images_prev_next_heading',
					'type'      => 'text',
					'content'   => __( 'Prev/Next Arrows', 'jet-woo-product-gallery' ),
					'condition' => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_navigation_arrow_size',
					'label'        => __( 'Size', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 6,
								'max' => 80,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] ) => 'font-size: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_navigation_arrow_padding',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id'        => 'images_navigation_arrow_style_tabs',
					'separator' => 'before',
					'condition' => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'images_navigation_arrow_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_color',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'images_navigation_arrow_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_color_hover',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . ':hover' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_bg_hover',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . ':hover' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_border_color_hover',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . ':hover' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'images_navigation_arrow_disabled_style_tab',
					'title' => __( 'Disabled', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_color_disabled',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.swiper-button-disabled' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_bg_disabled',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.swiper-button-disabled' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_navigation_arrow_border_color_disabled',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.swiper-button-disabled' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->add_control(
				[
					'id'        => 'images_prev_heading',
					'type'      => 'text',
					'content'   => __( 'Prev Arrow', 'jet-woo-product-gallery' ),
					'condition' => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_prev_arrow_vertical_position',
					'label'        => __( 'Vertical Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'top'    => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'bottom' => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'return_value' => [
						'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
						'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
						'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-prev' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_prev_arrow_top_position',
					'label'        => __( 'Top Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-prev' ) => 'top: {{VALUE}}{{UNIT}}; bottom: auto;',
					],
					'condition'    => [
						'images_prev_arrow_vertical_position' => 'top',
						'slider_show_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_prev_arrow_bottom_position',
					'label'        => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-prev' ) => 'bottom: {{VALUE}}{{UNIT}}; top: auto;',
					],
					'condition'    => [
						'images_prev_arrow_vertical_position' => 'bottom',
						'slider_show_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_prev_arrow_horizontal_position',
					'label'        => __( 'Horizontal Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'return_value' => [
						'left'   => 'right: auto;',
						'center' => 'left: 50%; right: auto; transform: translate(-50%, 0);',
						'right'  => 'left: auto;',
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-prev' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_prev_arrow_left_position',
					'label'        => __( 'Left Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-prev' ) => 'left: {{VALUE}}{{UNIT}}; right: auto;',
					],
					'condition'    => [
						'images_prev_arrow_horizontal_position' => 'left',
						'slider_show_nav'                       => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_prev_arrow_right_position',
					'label'        => __( 'Right Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-prev' ) => 'right: {{VALUE}}{{UNIT}}; left: auto;',
					],
					'condition'    => [
						'images_prev_arrow_horizontal_position' => 'right',
						'slider_show_nav'                       => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'        => 'images_next_heading',
					'type'      => 'text',
					'content'   => __( 'Next Arrow', 'jet-woo-product-gallery' ),
					'condition' => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_next_arrow_vertical_position',
					'label'        => __( 'Vertical Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'top'    => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'bottom' => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'return_value' => [
						'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
						'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
						'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-next' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_next_arrow_top_position',
					'label'        => __( 'Top Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-next' ) => 'top: {{VALUE}}{{UNIT}}; bottom: auto;',
					],
					'condition'    => [
						'images_next_arrow_vertical_position' => 'top',
						'slider_show_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_next_arrow_bottom_position',
					'label'        => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-next' ) => 'bottom: {{VALUE}}{{UNIT}}; top: auto;',
					],
					'condition'    => [
						'images_next_arrow_vertical_position' => 'bottom',
						'slider_show_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'images_next_arrow_horizontal_position',
					'label'        => __( 'Horizontal Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'return_value' => [
						'left'   => 'right: auto;',
						'center' => 'left: 50%; right: auto; transform: translate(-50%, 0);',
						'right'  => 'left: auto;',
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-next' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_next_arrow_left_position',
					'label'        => __( 'Left Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-next' ) => 'left: {{VALUE}}{{UNIT}}; right: auto;',
					],
					'condition'    => [
						'images_next_arrow_horizontal_position' => 'left',
						'slider_show_nav'                       => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'images_next_arrow_right_position',
					'label'        => __( 'Right Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['images-arrows'] . '.jet-swiper-button-next' ) => 'right: {{VALUE}}{{UNIT}}; left: auto;',
					],
					'condition'    => [
						'images_next_arrow_horizontal_position' => 'right',
						'slider_show_nav'                       => true,
					],
				]
			);

			$this->controls_manager->end_section();

			// Bullets style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_pagination_style',
					'initial_open' => false,
					'title'        => __( 'Pagination', 'jet-woo-product-gallery' ),
					'condition'    => [
						'slider_show_pagination' => true,
						'slider_pagination_type' => 'bullets',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_items_size',
					'label'        => __( 'Bullet Size', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 1,
								'max' => 40,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] )                                                                => 'width: {{VALUE}}{{UNIT}}; height: {{VALUE}}{{UNIT}};',
						$this->css_selector( '.jet-woo-swiper-vertical ' . $this->css_scheme['pagination'] . '.swiper-pagination-bullets-dynamic' ) => 'width: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id'        => 'pagination_items_style_tabs',
					'condition' => [
						'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
					],
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'pagination_items_normal_styles',
					'title' => __( 'Normal', 'jet-plugin' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_items_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'pagination_items_hover_styles',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_items_bg_hover',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] . ':hover' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_items_border_color_hover',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] . ':hover' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'pagination_items_active_styles',
					'title' => __( 'Active', 'jet-plugin' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_items_bg_active',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] . '.swiper-pagination-bullet-active' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_items_border_color_active',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] . '.swiper-pagination-bullet-active' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_items_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
					'condition'    => [
						'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_items_margin',
					'label'        => __( 'Bullet Space', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] ) => 'margin: auto {{RIGHT}} auto {{LEFT}};',
					],
					'condition'    => [
						'slider_pagination_direction'       => 'horizontal',
						'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_items_margin_vertical',
					'label'        => __( 'Bullet Space', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination-items'] ) => 'margin: {{TOP}} auto {{BOTTOM}} auto;',
					],
					'condition'    => [
						'slider_pagination_direction'       => 'vertical',
						'slider_pagination_controller_type' => [ 'bullets', 'dynamic' ],
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_progressbar_height',
					'label'        => __( 'Progressbar Height', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'attributes'   => [
						'default' => [
							'value' => [
								'value' => 4,
								'unit'  => 'px',
							],
						],
					],
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'step' => 1,
								'min'  => 1,
								'max'  => 40,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-horizontal .swiper-pagination-progressbar' ) => 'height: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_pagination_direction'       => 'horizontal',
						'slider_pagination_controller_type' => 'progressbar',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_progressbar_width',
					'label'        => __( 'Progressbar Width', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'attributes'   => [
						'default' => [
							'value' => [
								'value' => 4,
								'unit'  => 'px',
							],
						],
					],
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'step' => 1,
								'min'  => 1,
								'max'  => 40,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-vertical .swiper-pagination-progressbar' ) => 'width: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_pagination_direction'       => 'vertical',
						'slider_pagination_controller_type' => 'progressbar',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_progressbar_color',
					'label'        => __( 'Progressbar Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.swiper-pagination-progressbar' ) => 'background-color: {{VALUE}}',
					],
					'condition'    => [
						'slider_pagination_controller_type' => 'progressbar',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_progressbar_fill_color',
					'label'        => __( 'Progressbar Fill Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.swiper-pagination-progressbar-fill' ) => 'background-color: {{VALUE}}',
					],
					'condition'    => [
						'slider_pagination_controller_type' => 'progressbar',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_fraction_typography',
					'type'         => 'typography',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination'] ) => 'font-family: {{FAMILY}}; font-weight: {{WEIGHT}}; text-transform: {{TRANSFORM}}; font-style: {{STYLE}}; text-decoration: {{DECORATION}}; line-height: {{LINEHEIGHT}}{{LH_UNIT}}; letter-spacing: {{LETTERSPACING}}{{LS_UNIT}}; font-size: {{SIZE}}{{S_UNIT}};',
					],
					'condition'    => [
						'slider_pagination_controller_type' => 'fraction',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'pagination_fraction_color',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination'] ) => 'color: {{VALUE}}',
					],
					'condition'    => [
						'slider_pagination_controller_type' => 'fraction',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_fraction_padding',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
					'condition'    => [
						'slider_pagination_controller_type' => [ 'bullets', 'fraction' ],
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_top_position',
					'label'        => __( 'Top Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-horizontal ' . $this->css_scheme['pagination'] ) => 'top: {{VALUE}}{{UNIT}}; bottom: auto;',
					],
					'condition'    => [
						'slider_pagination_h_position' => 'top',
						'slider_pagination_direction'  => 'horizontal',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_bottom_position',
					'label'        => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-horizontal ' . $this->css_scheme['pagination'] ) => 'bottom: {{VALUE}}{{UNIT}}; top: auto;',
					],
					'condition'    => [
						'slider_pagination_h_position' => 'bottom',
						'slider_pagination_direction'  => 'horizontal',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_right_position',
					'label'        => __( 'End Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-vertical ' . $this->css_scheme['pagination'] ) => ! is_rtl() ? 'right: {{VALUE}}{{UNIT}}; left: auto;' : 'left: {{VALUE}}{{UNIT}}; right: auto;',
					],
					'condition'    => [
						'slider_pagination_v_position' => 'end',
						'slider_pagination_direction'  => 'vertical',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_left_position',
					'label'        => __( 'Start Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-vertical ' . $this->css_scheme['pagination'] ) => ! is_rtl() ? 'left: {{VALUE}}{{UNIT}}; right: auto;' : 'right: {{VALUE}}{{UNIT}}; left: auto;',
					],
					'condition'    => [
						'slider_pagination_v_position' => 'start',
						'slider_pagination_direction'  => 'vertical',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_horizontal_alignment',
					'label'        => __( 'Alignment', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['pagination'] ) => 'text-align: {{VALUE}};',
					],
					'condition'    => [
						'slider_pagination_direction'       => 'horizontal',
						'slider_pagination_controller_type' => [ 'bullets', 'fraction' ],
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'pagination_fraction_vertical_alignment',
					'label'        => __( 'Alignment', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'top'    => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'bottom' => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'return_value' => [
						'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
						'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
						'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( '.jet-woo-swiper-vertical .jet-gallery-swiper-slider ' . $this->css_scheme['pagination'] . '.swiper-pagination-fraction' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_pagination_direction'       => 'vertical',
						'slider_pagination_controller_type' => 'fraction',
					],
				]
			);

			$this->controls_manager->end_section();

			// Thumbnails style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_thumbnails_style',
					'initial_open' => false,
					'title'        => __( 'Thumbnails', 'jet-woo-product-gallery' ),
					'condition'    => [
						'slider_show_pagination' => true,
						'slider_pagination_type' => 'thumbnails',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_horizontal_alignment',
					'label'        => __( 'Alignment', 'jet-woo-product-gallery' ),
					'attributes'   => [
						'default' => [
							'value' => 'left',
						],
					],
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-wrapper'] . '.swiper-container-horizontal' ) => 'text-align: {{VALUE}};',
					],
					'condition'    => [
						'slider_pagination_direction' => 'horizontal',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_vertical_width',
					'label'        => __( 'Width', 'jet-woo-product-gallery' ),
					'type'         => 'range',
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
						$this->css_selector( '.jet-woo-swiper-vertical .jet-gallery-swiper-thumb' ) => 'max-width: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_pagination_direction' => 'vertical',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_vertical_height',
					'label'        => __( 'Height', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 100,
								'max' => 2000,
							],
						],
					],
					'attributes'   => [
						'default' => [
							'value' => [
								'value' => 400,
								'unit'  => 'px',
							],
						],
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-wrapper'] . '.swiper-container-vertical' ) => 'height: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_pagination_direction' => 'vertical',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_horizontal_gutter',
					'label'        => __( 'Gutter', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-wrapper'] . '.swiper-container-horizontal' ) => 'padding-top: {{TOP}}; padding-right: unset; padding-bottom: {{BOTTOM}}; padding-left: unset;',
					],
					'condition'    => [
						'slider_pagination_direction' => 'horizontal',
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_vertical_gutter',
					'label'        => __( 'Gutter', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-wrapper'] . '.swiper-container-vertical' ) => 'padding-top: unset; padding-right: {{RIGHT}}; padding-bottom: unset; padding-left: {{LEFT}};',
					],
					'condition'    => [
						'slider_pagination_direction' => 'vertical',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'        => 'thumbnails_prev_next_heading',
					'type'      => 'text',
					'content'   => __( 'Prev/Next Arrows', 'jet-woo-product-gallery' ),
					'condition' => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_navigation_arrow_size',
					'label'        => __( 'Size', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'units'        => [
						[
							'value'     => 'px',
							'intervals' => [
								'min' => 6,
								'max' => 80,
							],
						],
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] ) => 'font-size: {{VALUE}}{{UNIT}};',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_navigation_arrow_padding',
					'label'        => __( 'Padding', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'units'        => [ 'px', '%' ],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_border',
					'label'        => __( 'Border', 'jet-woo-product-gallery' ),
					'type'         => 'border',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] ) => 'border-style: {{STYLE}}; border-width: {{WIDTH}}; border-radius: {{RADIUS}}; border-color: {{COLOR}}',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->start_tabs(
				'style_controls',
				[
					'id'        => 'thumbnails_navigation_arrow_style_tabs',
					'separator' => 'before',
					'condition' => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'thumbnails_navigation_arrow_normal_style_tab',
					'title' => __( 'Normal', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_color',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_bg',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'thumbnails_navigation_arrow_hover_style_tab',
					'title' => __( 'Hover', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_color_hover',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . ':hover' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_bg_hover',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . ':hover' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_border_color_hover',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . ':hover' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->start_tab(
				'style_controls',
				[
					'id'    => 'thumbnails_navigation_arrow_disabled_style_tab',
					'title' => __( 'Disabled', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_color_disabled',
					'label'        => __( 'Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.swiper-button-disabled' ) => 'color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_bg_disabled',
					'label'        => __( 'Background Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.swiper-button-disabled' ) => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_navigation_arrow_border_color_disabled',
					'label'        => __( 'Border Color', 'jet-woo-product-gallery' ),
					'type'         => 'color-picker',
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.swiper-button-disabled' ) => 'border-color: {{VALUE}}',
					],
				]
			);

			$this->controls_manager->end_tab();

			$this->controls_manager->end_tabs();

			$this->controls_manager->add_control(
				[
					'id'        => 'thumbnails_prev_heading',
					'type'      => 'text',
					'content'   => __( 'Prev Arrow', 'jet-woo-product-gallery' ),
					'condition' => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_prev_arrow_vertical_position',
					'label'        => __( 'Vertical Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'top'    => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'bottom' => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'return_value' => [
						'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
						'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
						'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_prev_arrow_top_position',
					'label'        => __( 'Top Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' ) => 'top: {{VALUE}}{{UNIT}}; bottom: auto;',
					],
					'condition'    => [
						'thumbnails_prev_arrow_vertical_position' => 'top',
						'slider_show_thumb_nav'                   => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_prev_arrow_bottom_position',
					'label'        => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' ) => 'bottom: {{VALUE}}{{UNIT}}; top: auto;',
					],
					'condition'    => [
						'thumbnails_prev_arrow_vertical_position' => 'bottom',
						'slider_show_thumb_nav'                   => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_prev_arrow_horizontal_position',
					'label'        => __( 'Horizontal Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'return_value' => [
						'left'   => 'right: auto;',
						'center' => 'left: 50%; right: auto; transform: translate(-50%, 0);',
						'right'  => 'left: auto;',
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_prev_arrow_left_position',
					'label'        => __( 'Left Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' ) => 'left: {{VALUE}}{{UNIT}}; right: auto;',
					],
					'condition'    => [
						'thumbnails_prev_arrow_horizontal_position' => 'left',
						'slider_show_thumb_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_prev_arrow_right_position',
					'label'        => __( 'Right Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-prev' ) => 'right: {{VALUE}}{{UNIT}}; left: auto;',
					],
					'condition'    => [
						'thumbnails_prev_arrow_horizontal_position' => 'right',
						'slider_show_thumb_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'        => 'thumbnails_next_heading',
					'type'      => 'text',
					'content'   => __( 'Next Arrow', 'jet-woo-product-gallery' ),
					'condition' => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_next_arrow_vertical_position',
					'label'        => __( 'Vertical Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'top'    => [
							'shortcut' => __( 'Top', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-up-alt',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-minus',
						],
						'bottom' => [
							'shortcut' => __( 'Bottom', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-arrow-down-alt',
						],
					],
					'return_value' => [
						'top'    => 'top: 0; bottom: auto; transform: translate(0,0);',
						'center' => 'top: 50%; bottom: auto; transform: translate(0,-50%);',
						'bottom' => 'top: auto; bottom: 0; transform: translate(0,0);',
					],
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_next_arrow_top_position',
					'label'        => __( 'Top Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' ) => 'top: {{VALUE}}{{UNIT}}; bottom: auto;',
					],
					'condition'    => [
						'thumbnails_next_arrow_vertical_position' => 'top',
						'slider_show_thumb_nav'                   => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_next_arrow_bottom_position',
					'label'        => __( 'Bottom Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' ) => 'bottom: {{VALUE}}{{UNIT}}; top: auto;',
					],
					'condition'    => [
						'thumbnails_next_arrow_vertical_position' => 'bottom',
						'slider_show_thumb_nav'                   => true,
					],
				]
			);

			$this->controls_manager->add_control(
				[
					'id'           => 'thumbnails_next_arrow_horizontal_position',
					'label'        => __( 'Horizontal Position', 'jet-woo-product-gallery' ),
					'type'         => 'choose',
					'options'      => [
						'left'   => [
							'shortcut' => __( 'Left', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignleft',
						],
						'center' => [
							'shortcut' => __( 'Center', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-aligncenter',
						],
						'right'  => [
							'shortcut' => __( 'Right', 'jet-woo-product-gallery' ),
							'icon'     => 'dashicons-editor-alignright',
						],
					],
					'return_value' => [
						'left'   => 'right: auto;',
						'center' => 'left: 50%; right: auto; transform: translate(-50%, 0);',
						'right'  => 'left: auto;',
					],
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' ) => '{{VALUE}}',
					],
					'condition'    => [
						'slider_show_thumb_nav' => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_next_arrow_left_position',
					'label'        => __( 'Left Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' ) => 'left: {{VALUE}}{{UNIT}}; right: auto;',
					],
					'condition'    => [
						'thumbnails_next_arrow_horizontal_position' => 'left',
						'slider_show_thumb_nav'                     => true,
					],
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'thumbnails_next_arrow_right_position',
					'label'        => __( 'Right Indent', 'jet-woo-product-gallery' ),
					'type'         => 'range',
					'allow_reset'  => true,
					'units'        => jet_woo_product_gallery_tools()->get_slider_nav_controls_position_ranges( 'blocks' ),
					'separator'    => 'before',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['thumbnails-arrows'] . '.jet-swiper-button-next' ) => 'right: {{VALUE}}{{UNIT}}; left: auto;',
					],
					'condition'    => [
						'thumbnails_next_arrow_horizontal_position' => 'right',
						'slider_show_thumb_nav'                     => true,
					],
				]
			);

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