const { __ } = wp.i18n;

const {
	PanelBody,
	ToggleControl
} = wp.components;

export default props => {

	const {
		attributes,
		setAttributes
	} = props;

	return (
		<PanelBody title={ __( 'Photoswipe Gallery', 'jet-woo-product-gallery' ) } initialOpen={ false }>
			<ToggleControl
				label={ __( 'Show Caption', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_caption }
				onChange={ () => {
					setAttributes( { gallery_show_caption: ! attributes.gallery_show_caption } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Full Screen', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_fullscreen }
				onChange={ () => {
					setAttributes( { gallery_show_fullscreen: ! attributes.gallery_show_fullscreen } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Zoom', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_zoom }
				onChange={ () => {
					setAttributes( { gallery_show_zoom: ! attributes.gallery_show_zoom } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Share', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_share }
				onChange={ () => {
					setAttributes( { gallery_show_share: ! attributes.gallery_show_share } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Counter', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_counter }
				onChange={ () => {
					setAttributes( { gallery_show_counter: ! attributes.gallery_show_counter } );
				} }
			/>

			<ToggleControl
				label={ __( 'Show Arrows', 'jet-woo-product-gallery' ) }
				checked={ attributes.gallery_show_arrows }
				onChange={ () => {
					setAttributes( { gallery_show_arrows: ! attributes.gallery_show_arrows } );
				} }
			/>
		</PanelBody>
	);

};