<?php
/**
 * Class Jet Woo Product Gallery Video Integration
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Woo_Gallery_Video_Integration' ) ) {

	/**
	 * Define Jet_Woo_Gallery_Video_Integration_class
	 */
	class Jet_Woo_Gallery_Video_Integration {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {
			add_action( 'init', array( $this, 'add_product_meta' ), 99 );
		}

		/**
		 * Initialize template metabox
		 *
		 * @return void
		 */
		public function add_product_meta() {
			new Cherry_X_Post_Meta( array(
				'id'            => 'gallery-video-settings',
				'title'         => esc_html__( 'Jet Product Gallery Video', 'jet-woo-product-gallery' ),
				'page'          => array( 'product' ),
				'context'       => 'side',
				'priority'      => 'low',
				'callback_args' => false,
				'builder_cb'    => array( $this, 'get_builder' ),
				'fields'        => array(
					'_jet_woo_product_video_type'        => array(
						'type'    => 'select',
						'element' => 'control',
						'value'   => 'youtube',
						'options' => array(
							'youtube'     => __( 'Youtube', 'jet-woo-product-gallery' ),
							'vimeo'       => __( 'Vimeo', 'jet-woo-product-gallery' ),
							'self_hosted' => __( 'Self Hosted', 'jet-woo-product-gallery' ),
						),
						'label'   => __( 'Video Type:', 'jet-woo-product-gallery' ),
						'class'   => 'jet-woo-product-gallery-cx-select',
					),
					'_jet_woo_product_video_placeholder' => array(
						'label'              => __( 'Placeholder:', 'jet-woo-product-gallery' ),
						'type'               => 'media',
						'element'            => 'control',
						'upload_button_text' => __( 'Choose Placeholder', 'jet-woo-product-gallery' ),
						'multi_upload'       => false,
						'class'              => 'jet-woo-product-gallery-cx-text',
					),
					'_jet_woo_product_vimeo_video_url'   => array(
						'label'      => __( 'Video URL:', 'jet-woo-product-gallery' ),
						'type'       => 'text',
						'element'    => 'control',
						'conditions' => array(
							'_jet_woo_product_video_type' => 'vimeo',
						),
					),
					'_jet_woo_product_youtube_video_url' => array(
						'label'      => __( 'Video URL:', 'jet-woo-product-gallery' ),
						'type'       => 'text',
						'element'    => 'control',
						'conditions' => array(
							'_jet_woo_product_video_type' => 'youtube',
						),
					),
					'_jet_woo_product_self_hosted_video' => array(
						'label'              => __( 'Video:', 'jet-woo-product-gallery' ),
						'type'               => 'media',
						'element'            => 'control',
						'upload_button_text' => __( 'Choose Video', 'jet-woo-product-gallery' ),
						'multi_upload'       => false,
						'library_type'       => 'video',
						'conditions'         => array(
							'_jet_woo_product_video_type' => 'self_hosted',
						),
					),
				),
			) );
		}

		/**
		 * Return UI builder instance
		 *
		 * @return CX_Interface_Builder
		 */
		public function get_builder() {

			$builder_data = jet_woo_product_gallery()->module_loader->get_included_module_data( 'cherry-x-interface-builder.php' );

			return new CX_Interface_Builder(
				array(
					'path' => $builder_data['path'],
					'url'  => $builder_data['url'],
				)
			);

		}

		/**
		 * Returns product gallery video type
		 *
		 * @param $settings
		 *
		 * @return array|int|string
		 */
		public function get_video_type( $settings ) {
			if ( 'products' === $settings['gallery_source'] ) {
				$product_id = $this->get_gallery_product_id( $settings['product_id'] );
				$video_type = get_post_field( '_jet_woo_product_video_type', $product_id );
			} else {
				$video_type = filter_var( $settings['enable_video'], FILTER_VALIDATE_BOOLEAN ) ? $settings['video_type'] : '';
			}

			return $video_type;
		}

		/**
		 * Returns product gallery custom placeholder
		 *
		 * @param $settings
		 *
		 * @return array|int|string
		 */
		public function get_video_custom_placeholder( $settings ) {

			$placeholder = '';

			if ( 'products' === $settings['gallery_source'] ) {
				$product_id  = $this->get_gallery_product_id( $settings['product_id'] );
				$placeholder = get_post_field( '_jet_woo_product_video_placeholder', $product_id );
			} elseif ( filter_var( $settings['enable_video'], FILTER_VALIDATE_BOOLEAN ) && isset( $settings['custom_placeholder']['id'] ) ) {
				$placeholder = $settings['custom_placeholder']['id'];
			}

			return $placeholder;

		}

		/**
		 * Returns product gallery youtube video url
		 *
		 * @param $settings
		 *
		 * @return array|int|string
		 */
		public function get_youtube_video_url( $settings ) {
			$video_url = '';

			if ( 'products' === $settings['gallery_source'] ) {
				$product_id = $this->get_gallery_product_id( $settings['product_id'] );
				$video_url  = get_post_field( '_jet_woo_product_youtube_video_url', $product_id );
			} else {
				if ( filter_var( $settings['enable_video'], FILTER_VALIDATE_BOOLEAN ) && 'youtube' === $settings['video_type'] ) {
					$video_url = $settings['youtube_url'];
				}
			}

			return $video_url;
		}

		/**
		 * Returns product gallery vimeo video url
		 *
		 * @param $settings
		 *
		 * @return array|int|string
		 */
		public function get_vimeo_video_url( $settings ) {
			$video_url = '';

			if ( 'products' === $settings['gallery_source'] ) {
				$product_id = $this->get_gallery_product_id( $settings['product_id'] );
				$video_url  = get_post_field( '_jet_woo_product_vimeo_video_url', $product_id );
			} else {
				if ( filter_var( $settings['enable_video'], FILTER_VALIDATE_BOOLEAN ) && 'vimeo' === $settings['video_type'] ) {
					$video_url = $settings['vimeo_url'];
				}
			}

			return $video_url;
		}

		/**
		 * Returns product gallery self-hosted video id
		 *
		 * @param $settings
		 *
		 * @return array|int|string
		 */
		public function get_self_hosted_video_id( $settings ) {

			$video_id = '';

			if ( 'products' === $settings['gallery_source'] ) {
				$product_id = $this->get_gallery_product_id( $settings['product_id'] );
				$video_id   = get_post_field( '_jet_woo_product_self_hosted_video', $product_id );
			} else {
				if ( filter_var( $settings['enable_video'], FILTER_VALIDATE_BOOLEAN ) && 'self_hosted' === $settings['video_type'] ) {
					if ( is_array( $settings['self_hosted_url'] ) && isset( $settings['self_hosted_url']['id'] ) ) {
						$video_id = $settings['self_hosted_url']['id'];
					} elseif ( is_numeric( $settings['self_hosted_url'] ) ) {
						$video_id = $settings['self_hosted_url'];
					} else {
						$video_id = attachment_url_to_postid( $settings['self_hosted_url'] );
					}
				}
			}

			return $video_id;

		}

		/**
		 * Returns Woocommerce product id depending on manual control
		 *
		 * @param $id
		 *
		 * @return int
		 */
		public function get_gallery_product_id( $id ) {
			if ( ! empty( $id ) ) {
				$post_id = $id;
			} else {
				global $post;
				$post_id = $post->ID;
			}

			return $post_id;
		}

		/**
		 * Returns the instance.
		 *
		 * @return object
		 * @since  1.0.0
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

		}

	}

}

/**
 * Returns instance of Jet_Woo_Gallery_Video_Integration
 *
 * @return object
 */
function jet_woo_gallery_video_integration() {
	return Jet_Woo_Gallery_Video_Integration::get_instance();
}
