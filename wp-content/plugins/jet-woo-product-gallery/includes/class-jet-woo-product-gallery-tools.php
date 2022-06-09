<?php
/**
 * Jet Product Gallery tools class
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Product_Gallery_Tools' ) ) {

	/**
	 * Define Jet_Woo_Product_Gallery_Tools class
	 */
	class Jet_Woo_Product_Gallery_Tools {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns image size array in slug => name format.
		 *
		 * @param string $context
		 *
		 * @return  array
		 */
		public function get_image_sizes( $context = 'elementor' ) {

			global $_wp_additional_image_sizes;

			$sizes         = get_intermediate_image_sizes();
			$result        = [];
			$blocks_result = [];

			foreach ( $sizes as $size ) {
				if ( in_array( $size, [ 'thumbnail', 'medium', 'medium_large', 'large' ] ) ) {
					$label           = ucwords( trim( str_replace( [ '-', '_' ], [ ' ', ' ' ], $size ) ) );
					$result[ $size ] = $label;
					$blocks_result[] = [
						'value' => $size,
						'label' => $label,
					];
				} else {
					$label = sprintf(
						'%1$s (%2$sx%3$s)',
						ucwords( trim( str_replace( [ '-', '_' ], [ ' ', ' ' ], $size ) ) ),
						$_wp_additional_image_sizes[ $size ]['width'],
						$_wp_additional_image_sizes[ $size ]['height']
					);

					$result[ $size ] = $label;
					$blocks_result[] = [
						'value' => $size,
						'label' => $label,
					];
				}
			}

			$result        = array_merge( [ 'full' => __( 'Full', 'jet-woo-product-gallery' ) ], $result );
			$blocks_result = array_merge(
				[
					[
						'value' => 'full',
						'label' => __( 'Full', 'jet-woo-product-gallery' ),
					],
				],
				$blocks_result
			);

			if ( 'blocks' === $context ) {
				return $blocks_result;
			} else {
				return $result;
			}

		}

		/**
		 * Returns array with numbers in $index => $name format for numeric selects.
		 *
		 * @param integer $to Max numbers
		 *
		 * @return array
		 */
		public function get_select_range( $to = 10 ) {

			$range = range( 1, $to );

			return array_combine( $range, $range );

		}

		/**
		 * Get post types options list
		 *
		 * @return array
		 */
		public function get_post_types() {

			$post_types = get_post_types( [ 'public' => true ], 'objects' );
			$deprecated = apply_filters( 'jet-woo-product-gallery/post-types-list/deprecated', [ 'attachment', 'elementor_library' ] );
			$result     = [];

			if ( empty( $post_types ) ) {
				return $result;
			}

			foreach ( $post_types as $slug => $post_type ) {
				if ( in_array( $slug, $deprecated ) ) {
					continue;
				}

				$result[ $slug ] = $post_type->label;
			}

			return $result;

		}

		/**
		 * Returns vertical flex alignment options
		 *
		 * @return array[]
		 */
		public function get_vertical_flex_alignment() {
			return [
				'flex-start' => [
					'title' => esc_html__( 'Top', 'jet-woo-product-gallery' ),
					'icon'  => 'eicon-v-align-top',
				],
				'center'     => [
					'title' => esc_html__( 'Middle', 'jet-woo-product-gallery' ),
					'icon'  => 'eicon-v-align-middle',
				],
				'flex-end'   => [
					'title' => esc_html__( 'Bottom', 'jet-woo-product-gallery' ),
					'icon'  => 'eicon-v-align-bottom',
				],
			];
		}

		/**
		 * Returns slider arrows position ranges options
		 *
		 * @param string $context
		 *
		 * @return array
		 */
		public function get_slider_nav_controls_position_ranges( $context = 'elementor' ) {
			if ( 'blocks' === $context ) {
				return [
					[
						'value'     => 'px',
						'intervals' => [
							'min' => -400,
							'max' => 400,
						],
					],
					[
						'value'     => '%',
						'intervals' => [
							'min' => -100,
							'max' => 100,
						],
					],
					[
						'value'     => 'em',
						'intervals' => [
							'min' => -50,
							'max' => 50,
						],
					],
				];
			} else {
				return [
					'px' => [
						'min' => -400,
						'max' => 400,
					],
					'%'  => [
						'min' => -100,
						'max' => 100,
					],
					'em' => [
						'min' => -50,
						'max' => 50,
					],
				];
			}
		}

		/**
		 * Get post types list for options.
		 *
		 * @return array
		 */
		public function get_post_types_for_options() {

			$args = [
				'public' => true,
			];

			$post_types = get_post_types( $args, 'objects', 'and' );
			$post_types = wp_list_pluck( $post_types, 'label', 'name' );

			if ( isset( $post_types[ jet_engine()->post_type->slug() ] ) ) {
				unset( $post_types[ jet_engine()->post_type->slug() ] );
			}

			return $post_types;

		}

		/**
		 * Get gallery source list for options.
		 *
		 * @return array
		 */
		public function get_gallery_source_options() {

			$gallery_source = [
				'cpt'    => esc_html__( 'Post Types', 'jet-woo-product-gallery' ),
				'manual' => esc_html__( 'Manual Select', 'jet-woo-product-gallery' ),
			];

			if ( class_exists( 'WooCommerce' ) ) {
				$gallery_source['products'] = esc_html__( 'WooCommerce Products', 'jet-woo-product-gallery' );
			}

			return $gallery_source;

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance( $shortcodes = array() ) {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Product_Gallery_Tools
 *
 * @return object
 */
function jet_woo_product_gallery_tools() {
	return Jet_Woo_Product_Gallery_Tools::get_instance();
}
