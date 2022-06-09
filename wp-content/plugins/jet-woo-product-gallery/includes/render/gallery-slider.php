<?php
/**
 * JetGallery Slider widget views manager.
 */

// If this file is called directly, abort.$this->attributes_variation_images
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Gallery_Slider' ) ) {

	/**
	 * Define Jet_Gallery_Slider class.
	 */
	class Jet_Gallery_Slider extends Jet_Gallery_Render_Base {

		public function get_name() {
			return 'jet-woo-product-gallery-slider';
		}

		public function default_settings() {

			$default_settings = [
				'image_size'                                 => 'thumbnail',
				'thumbs_image_size'                          => 'thumbnail',
				'slider_enable_infinite_loop'                => false,
				'slider_equal_slides_height'                 => false,
				'slider_sensitivity'                         => 0.8,
				'slider_enable_center_mode'                  => false,
				'slider_center_mode_slides'                  => 4,
				'slider_center_mode_slides_tablet'           => 3,
				'slider_center_mode_slides_mobile'           => 2,
				'slider_center_mode_space_between'           => 10,
				'slider_center_mode_space_between_tablet'    => 10,
				'slider_center_mode_space_between_mobile'    => 10,
				'slider_transition_effect'                   => 'slide',
				'slider_show_nav'                            => true,
				'slider_nav_arrow_prev'                      => [],
				'slider_nav_arrow_next'                      => [],
				'slider_show_pagination'                     => true,
				'slider_pagination_type'                     => 'bullets',
				'slider_pagination_controller_type'          => 'bullets',
				'pagination_thumbnails_columns'              => 4,
				'pagination_thumbnails_columns_tablet'       => 3,
				'pagination_thumbnails_columns_mobile'       => 2,
				'pagination_thumbnails_space_between'        => 10,
				'pagination_thumbnails_space_between_tablet' => 10,
				'pagination_thumbnails_space_between_mobile' => 10,
				'slider_pagination_direction'                => 'horizontal',
				'slider_pagination_v_position'               => 'start',
				'slider_pagination_h_position'               => 'bottom',
				'slider_show_thumb_nav'                      => false,
				'pagination_thumbnails_slider_arrow_prev'    => [],
				'pagination_thumbnails_slider_arrow_next'    => [],
			];

			return array_merge( parent::default_settings(), $default_settings );

		}

		public function render() {
			jet_woo_product_gallery_assets()->enqueue_scripts();
			$this->get_render_gallery_content();
		}

		/**
		 * Returns swiper slider setting options
		 *
		 * @return string
		 */
		public function get_slider_data_settings() {

			$settings = $this->get_settings();

			$slider_settings = [
				'slider_enable_infinite_loop'                => filter_var( $settings['slider_enable_infinite_loop'], FILTER_VALIDATE_BOOLEAN ),
				'slider_equal_slides_height'                 => isset( $settings['slider_equal_slides_height'] ) ? ! filter_var( $settings['slider_equal_slides_height'], FILTER_VALIDATE_BOOLEAN ) : false,
				'slider_sensitivity'                         => ! empty( $settings['slider_sensitivity'] ) ? $settings['slider_sensitivity'] : 1,
				'slider_enable_center_mode'                  => filter_var( $settings['slider_enable_center_mode'], FILTER_VALIDATE_BOOLEAN ),
				'slider_transition_effect'                   => $settings['slider_transition_effect'],
				'show_navigation'                            => filter_var( $settings['slider_show_nav'], FILTER_VALIDATE_BOOLEAN ),
				'show_pagination'                            => filter_var( $settings['slider_show_pagination'], FILTER_VALIDATE_BOOLEAN ),
				'pagination_type'                            => $settings['slider_pagination_type'],
				'pagination_controller_type'                 => $settings['slider_pagination_controller_type'],
				'pagination_direction'                       => $settings['slider_pagination_direction'],
				'slider_center_mode_slides'                  => isset( $settings['slider_center_mode_slides'] ) ? $settings['slider_center_mode_slides'] : 4,
				'slider_center_mode_slides_tablet'           => isset( $settings['slider_center_mode_slides_tablet'] ) ? $settings['slider_center_mode_slides_tablet'] : 3,
				'slider_center_mode_slides_mobile'           => isset( $settings['slider_center_mode_slides_mobile'] ) ? $settings['slider_center_mode_slides_mobile'] : 2,
				'slider_center_mode_space_between'           => isset( $settings['slider_center_mode_space_between'] ) ? $settings['slider_center_mode_space_between'] : 10,
				'slider_center_mode_space_between_tablet'    => isset( $settings['slider_center_mode_space_between_tablet'] ) ? $settings['slider_center_mode_space_between_tablet'] : 10,
				'slider_center_mode_space_between_mobile'    => isset( $settings['slider_center_mode_space_between_mobile'] ) ? $settings['slider_center_mode_space_between_mobile'] : 10,
				'pagination_thumbnails_columns'              => isset( $settings['pagination_thumbnails_columns'] ) ? $settings['pagination_thumbnails_columns'] : 4,
				'pagination_thumbnails_columns_tablet'       => isset( $settings['pagination_thumbnails_columns_tablet'] ) ? $settings['pagination_thumbnails_columns_tablet'] : 3,
				'pagination_thumbnails_columns_mobile'       => isset( $settings['pagination_thumbnails_columns_mobile'] ) ? $settings['pagination_thumbnails_columns_mobile'] : 2,
				'pagination_thumbnails_space_between'        => isset( $settings['pagination_thumbnails_space_between'] ) ? $settings['pagination_thumbnails_space_between'] : 10,
				'pagination_thumbnails_space_between_tablet' => isset( $settings['pagination_thumbnails_space_between_tablet'] ) ? $settings['pagination_thumbnails_space_between_tablet'] : 10,
				'pagination_thumbnails_space_between_mobile' => isset( $settings['pagination_thumbnails_space_between_mobile'] ) ? $settings['pagination_thumbnails_space_between_mobile'] : 10,
			];

			$slider_settings = apply_filters( 'jet-woo-product-gallery/slider/pre-options', $slider_settings, $settings );

			$options = [
				'effect'                   => ! $slider_settings['slider_enable_center_mode'] ? $slider_settings['slider_transition_effect'] : 'slide',
				'loop'                     => $slider_settings['slider_enable_infinite_loop'],
				'autoHeight'               => 'horizontal' === $settings['slider_pagination_direction'] ? $slider_settings['slider_equal_slides_height'] : true,
				'longSwipesRatio'          => $slider_settings['slider_sensitivity'],
				'centeredSlides'           => 'horizontal' === $slider_settings['pagination_direction'] ? $slider_settings['slider_enable_center_mode'] : false,
				'direction'                => $slider_settings['show_pagination'] ? $slider_settings['pagination_direction'] : 'horizontal',
				'showNavigation'           => $slider_settings['show_navigation'],
				'showPagination'           => $slider_settings['show_pagination'],
				'paginationType'           => $slider_settings['pagination_type'],
				'paginationControllerType' => $slider_settings['pagination_controller_type'],
			];

			if ( $options['centeredSlides'] ) {
				$options['breakpoints'] = [
					0    => [
						'slidesPerView' => $slider_settings['slider_center_mode_slides_mobile'],
						'spaceBetween'  => $slider_settings['slider_center_mode_space_between_mobile'],
					],
					768  => [
						'slidesPerView' => $slider_settings['slider_center_mode_slides_tablet'],
						'spaceBetween'  => $slider_settings['slider_center_mode_space_between_tablet'],
					],
					1025 => [
						'slidesPerView' => $slider_settings['slider_center_mode_slides'],
						'spaceBetween'  => $slider_settings['slider_center_mode_space_between'],
					],
				];
			}

			$thumb_options = [];

			if ( $slider_settings['show_pagination'] && 'thumbnails' === $slider_settings['pagination_type'] ) {
				$thumb_options = [
					'breakpoints' => [
						0    => [
							'slidesPerView' => $slider_settings['pagination_thumbnails_columns_mobile'],
							'spaceBetween'  => $slider_settings['pagination_thumbnails_space_between_mobile'],
						],
						768  => [
							'slidesPerView' => $slider_settings['pagination_thumbnails_columns_tablet'],
							'spaceBetween'  => $slider_settings['pagination_thumbnails_space_between_tablet'],
						],
						1025 => [
							'slidesPerView' => $slider_settings['pagination_thumbnails_columns'],
							'spaceBetween'  => $slider_settings['pagination_thumbnails_space_between'],
						],
					],
				];
			}

			$options = apply_filters( 'jet-woo-product-gallery/slider/options', $options, $settings );
			$options = json_encode( $options );

			$thumb_options = apply_filters( 'jet-woo-product-gallery/slider/thumb-options', $thumb_options, $settings );
			$thumb_options = json_encode( $thumb_options );

			return sprintf( 'data-swiper-settings=\'%1$s\' data-swiper-thumb-settings=\'%2$s\'', $options, $thumb_options );

		}

		/**
		 * Returns slider navigation arrows
		 *
		 * @param $prev_arrow
		 * @param $next_arrow
		 *
		 * @return string|null
		 */
		public function get_slider_navigation( $prev_arrow, $next_arrow ) {

			$nav_prev_icon = $this->render_icon( $prev_arrow, '%s', '', false );
			$nav_next_icon = $this->render_icon( $next_arrow, '%s', '', false );

			$swiper_prev_arrow = $this->get_slider_arrow( 'jet-swiper-nav jet-swiper-button-prev', $nav_prev_icon );
			$swiper_next_arrow = $this->get_slider_arrow( 'jet-swiper-nav jet-swiper-button-next', $nav_next_icon );

			return $swiper_prev_arrow . $swiper_next_arrow;

		}

	}

}
