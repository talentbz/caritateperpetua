<?php
/**
 * Class: Jet_Woo_Product_Gallery_Modern
 * Name: Gallery Modern
 * Slug: jet-woo-product-gallery-modern
 */

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Jet_Woo_Product_Gallery_Modern extends Jet_Gallery_Widget_Base {

	public function get_name() {
		return 'jet-woo-product-gallery-modern';
	}

	public function get_title() {
		return esc_html__( 'Gallery Modern', 'jet-woo-product-gallery' );
	}

	public function get_script_depends() {
		return array( 'zoom', 'wc-single-product', 'mediaelement', 'photoswipe-ui-default', 'photoswipe' );
	}

	public function get_style_depends() {
		return array( 'mediaelement', 'photoswipe', 'photoswipe-default-skin' );
	}

	public function get_icon() {
		return 'jet-gallery-icon-modern';
	}

	public function get_jet_help_url() {
		return 'https://crocoblock.com/knowledge-base/articles/jetproductgallery-how-to-showcase-the-product-images-with-gallery-modern-widget-proportions-and-style-settings/';
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
			'jet-woo-product-gallery-modern/css-scheme',
			array(
				'wrapper' => '.jet-woo-product-gallery-modern',
				'items'   => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item',
				'images'  => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image',
				'image-2' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+2)',
				'image-3' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+3)',
				'image-4' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+4)',
				'image-5' => '.jet-woo-product-gallery-modern .jet-woo-product-gallery__image-item:nth-child(5n+5)',
			)
		);

		$this->register_controls_images_style( $css_scheme );

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
			'gallery_images_proportion_1',
			array(
				'label'      => esc_html__( 'Images Proportion 1 (%)', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'%',
				),
				'range'      => array(
					'%' => array(
						'min' => 10,
						'max' => 90,
					),
				),
				'default'    => array(
					'size' => 30,
					'unit' => '%',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['image-2'] => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['image-3'] => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->add_responsive_control(
			'gallery_images_proportion_2',
			array(
				'label'      => esc_html__( 'Images Proportion 2 (%)', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'%',
				),
				'range'      => array(
					'%' => array(
						'min' => 10,
						'max' => 90,
					),
				),
				'default'    => array(
					'size' => 70,
					'unit' => '%',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['image-4'] => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['image-5'] => 'max-width: calc(100% - {{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->add_control(
			'gallery_images_2_heading',
			array(
				'label'     => esc_html__( 'Image 2', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_2_alignment',
			array(
				'label'       => esc_html__( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors'   => array(
					'{{WRAPPER}} ' . $css_scheme['image-2'] => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'gallery_images_2_margin',
			array(
				'label'              => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} ' . $css_scheme['image-2'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'gallery_images_3_heading',
			array(
				'label'     => esc_html__( 'Image 3', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_3_alignment',
			array(
				'label'       => esc_html__( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors'   => array(
					'{{WRAPPER}} ' . $css_scheme['image-3'] => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'gallery_images_3_margin',
			array(
				'label'              => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} ' . $css_scheme['image-3'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'gallery_images_4_heading',
			array(
				'label'     => esc_html__( 'Image 4', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_4_alignment',
			array(
				'label'       => esc_html__( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors'   => array(
					'{{WRAPPER}} ' . $css_scheme['image-4'] => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'gallery_images_4_margin',
			array(
				'label'              => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} ' . $css_scheme['image-4'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'gallery_images_5_heading',
			array(
				'label'     => esc_html__( 'Image 5', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'gallery_images_5_alignment',
			array(
				'label'       => esc_html__( 'Vertical Alignment', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => jet_woo_product_gallery_tools()->get_vertical_flex_alignment(),
				'selectors'   => array(
					'{{WRAPPER}} ' . $css_scheme['image-5'] => 'align-self: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'gallery_images_5_margin',
			array(
				'label'              => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', '%' ),
				'allowed_dimensions' => 'vertical',
				'placeholder'        => array(
					'top'    => '',
					'right'  => 'auto',
					'bottom' => '',
					'left'   => 'auto',
				),
				'selectors'          => array(
					'{{WRAPPER}} ' . $css_scheme['image-5'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'separator'   => 'before',
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
				'exclude'  => array(
					'box_shadow_position',
				),
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

		$this->add_responsive_control(
			'gallery_images_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['items'] . ':not(.jet-woo-product-gallery--with-video)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'gallery_images_margin',
			array(
				'label'      => esc_html__( 'Outer Offset', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['wrapper'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		jet_woo_product_gallery()->base->render_gallery( 'gallery-modern', $this->get_settings_for_display(), 'elementor' );
	}

}