<?php
/**
 * JetGallery Grid Block Type.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Blocks_Views_Type_Grid' ) ) {

	/**
	 * Define Jet_Gallery_Blocks_Views_Type_Grid class.
	 */
	class Jet_Gallery_Blocks_Views_Type_Grid extends Jet_Gallery_Blocks_Views_Type_Base {

		/**
		 * Returns block name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'gallery-grid';
		}

		public function get_css_scheme() {

			$css_scheme = [
				'row'     => '.jet-woo-product-gallery-grid',
				'columns' => '.jet-woo-product-gallery__image-item',
			];

			return array_merge( parent::get_css_scheme(), $css_scheme );

		}

		/**
		 * Add style block options.
		 *
		 * @return boolean
		 */
		public function add_style_manager_options() {

			// Columns style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_columns_style',
					'initial_open' => true,
					'title'        => __( 'Columns', 'jet-woo-product-gallery' ),
				]
			);

			$this->controls_manager->add_responsive_control(
				[
					'id'           => 'columns_padding',
					'label'        => __( 'Columns Gutter', 'jet-woo-product-gallery' ),
					'type'         => 'dimensions',
					'css_selector' => [
						$this->css_selector( $this->css_scheme['columns'] ) => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
						$this->css_selector( $this->css_scheme['row'] )     => 'margin-left: -{{LEFT}}; margin-right: -{{RIGHT}};',
					],
				]
			);

			$this->controls_manager->end_section();

			// Images style controls.
			$this->controls_manager->start_section(
				'style_controls',
				[
					'id'           => 'section_images_style',
					'initial_open' => false,
					'title'        => __( 'Images', 'jet-woo-product-gallery' ),
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