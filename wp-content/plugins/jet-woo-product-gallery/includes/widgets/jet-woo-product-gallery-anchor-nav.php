<?php
/**
 * Class: Jet_Woo_Product_Gallery_Anchor_Nav
 * Name: Gallery Anchor Nav
 * Slug: jet-woo-product-gallery-anchor-nav
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Product_Gallery_Anchor_Nav extends Jet_Gallery_Widget_Base {

	public function get_name() {
		return 'jet-woo-product-gallery-anchor-nav';
	}

	public function get_title() {
		return esc_html__( 'Gallery Anchor Navigation', 'jet-woo-product-gallery' );
	}

	public function get_script_depends() {
		return array( 'zoom', 'wc-single-product', 'mediaelement', 'photoswipe-ui-default', 'photoswipe' );
	}

	public function get_style_depends() {
		return array( 'mediaelement', 'photoswipe', 'photoswipe-default-skin' );
	}

	public function get_icon() {
		return 'jet-gallery-icon-anchor-nav';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetproductgallery-gallery-anchor-navigation-widget-how-to-create-a-scrollable-product-images-layout-with-an-anchor-navigation-element/';
	}

	public function get_categories() {
		return array( 'jet-woo-product-gallery' );
	}

	public function register_gallery_content_controls() {

		$this->start_controls_section(
			'section_product_images',
			array(
				'label'      => esc_html__( 'Images', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => esc_html__( 'Image Size', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => jet_woo_product_gallery_tools()->get_image_sizes(),
			)
		);

		$this->end_controls_section();

		$css_scheme = apply_filters(
			'jet-woo-product-gallery-anchor-nav/css-scheme',
			[
				'items'                     => '.jet-woo-product-gallery-anchor-nav-items',
				'item'                      => '.jet-woo-product-gallery__image-item',
				'images'                    => '.jet-woo-product-gallery-anchor-nav .jet-woo-product-gallery__image',
				'controller'                => '.jet-woo-product-gallery-anchor-nav-controller',
				'controller-bullet'         => '.jet-woo-product-gallery-anchor-nav-controller .controller-item__bullet',
				'controller-bullet-current' => '.jet-woo-product-gallery-anchor-nav-controller .current-item .controller-item__bullet',
			]
		);

		$this->register_controls_images_style( $css_scheme );

		$this->register_controls_controller_style( $css_scheme );

	}

	public function register_controls_images_style( $css_scheme ) {

		$this->start_controls_section(
			'section_gallery_images_style',
			array(
				'label'      => esc_html__( 'Images', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'gallery_images_space_between',
			array(
				'label'      => esc_html__( 'Space Between Images', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 5,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['item'] . '+' . $css_scheme['item'] => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'gallery_images_border',
				'label'       => esc_html__( 'Border', 'jet-woo-product-gallery' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['images'],
			)
		);

		$this->add_control(
			'gallery_images_border_radius',
			[
				'label'      => __( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ' . $css_scheme['images'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'gallery_images_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['images'],
			)
		);
		$this->add_control(
			'gallery_images_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['images'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

	}

	public function register_controls_controller_style( $css_scheme ) {
		$this->start_controls_section(
			'section_controller_style',
			array(
				'label'      => esc_html__( 'Navigation', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'controller_width',
			array(
				'label'       => esc_html__( 'Width', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::SLIDER,
				'render_type' => 'template',
				'size_units'  => array(
					'px',
					'%',
				),
				'range'       => array(
					'px' => array(
						'min' => 70,
						'max' => 500,
					),
					'%'  => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'     => array(
					'size' => 150,
					'unit' => 'px',
				),
				'selectors'   => array(
					'{{WRAPPER}} ' . $css_scheme['controller'] => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['items']      => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->add_responsive_control(
			'controller_offset_top',
			array(
				'label'      => esc_html__( 'Offset Top (px)', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'default'    => array(
					'size' => 0,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['controller'] => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'controller_position',
			array(
				'label'        => esc_html__( 'Position', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::CHOOSE,
				'default'      => 'left',
				'options'      => array(
					'left'  => array(
						'title' => esc_html__( 'Start', 'jet-woo-product-gallery' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-left' : 'eicon-h-align-right',
					),
					'right' => array(
						'title' => esc_html__( 'End', 'jet-woo-product-gallery' ),
						'icon'  => ! is_rtl() ? 'eicon-h-align-right' : 'eicon-h-align-left',
					),
				),
				'prefix_class' => 'jet-woo-product-gallery-anchor-nav-controller-',
			)
		);

		$this->add_control(
			'bullets_heading',
			array(
				'label'     => esc_html__( 'Bullets', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'controller_bullets_width',
			array(
				'label'      => esc_html__( 'Width', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'controller_bullets_height',
			array(
				'label'      => esc_html__( 'Height', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'controller_bullets_style_tabs' );

		$this->start_controls_tab(
			'controller_bullets_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'controller_bullets_normal_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'controller_bullets_normal_border',
				'label'       => esc_html__( 'Border', 'jet-woo-product-gallery' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['controller-bullet'],
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'controller_bullets_normal_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['controller-bullet'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'controller_bullets_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'controller_bullets_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'controller_bullets_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ':hover' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'controller_bullets_normal_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'controller_bullets_hover_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['controller-bullet'] . ':hover',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'controller_bullets_current_styles',
			array(
				'label' => esc_html__( 'Current', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'controller_bullets_current_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet-current'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'controller_bullets_current_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet-current'] => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'controller_bullets_normal_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'controller_bullets_current_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['controller-bullet-current'],
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'controller_bullets_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'controller_bullets_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['controller-bullet'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		jet_woo_product_gallery()->base->render_gallery( 'gallery-anchor-nav', $this->get_settings_for_display(), 'elementor' );
	}

}