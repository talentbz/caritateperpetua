const { __ } = wp.i18n;

const {
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

export default props => {

	const imageSizes = window.JetGalleryBlocksData.imageSizes;

	const {
		attributes,
		setAttributes
	} = props;

	return (
		<PanelBody title={ __( 'Images', 'jet-woo-product-gallery' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Image Size', 'jet-woo-product-gallery' ) }
				value={ attributes.image_size }
				options={ imageSizes }
				onChange={ newValue => {
					setAttributes( {
						image_size: newValue,
					} );
				}}
			/>

			{ undefined !== attributes.navigation_controller_position &&
				<SelectControl
					label={ __( 'Navigation Position', 'jet-woo-product-gallery' ) }
					value={ attributes.navigation_controller_position }
					options={
						[
							{
								value: 'left',
								label: __( 'Start', 'jet-woo-product-gallery' )
							},
							{
								value: 'right',
								label: __( 'End', 'jet-woo-product-gallery' )
							}
						]
					}
					onChange={ newValue => {
						setAttributes( {
							navigation_controller_position: newValue,
						} );
					}}
				/>
			}

			{ undefined !== attributes.thumbs_image_size &&
				<SelectControl
					label={ __( 'Thumbnails Size', 'jet-woo-product-gallery' ) }
					value={ attributes.thumbs_image_size }
					options={ imageSizes }
					onChange={ newValue => {
						setAttributes( {
							thumbs_image_size: newValue,
						} );
					}}
				/>
			}

			{ undefined !== attributes.columns &&
				<TextControl
					type="number"
					label={ __( 'Columns Number', 'jet-woo-product-gallery' ) }
					value={ attributes.columns }
					min={ `1` }
					max={ `6` }
					onChange={ newValue => {
						setAttributes( { columns: Number( newValue ) } );
					} }
				/>
			}

			{ undefined !== attributes.columns_tablet &&
				<TextControl
					type="number"
					label={ __( 'Columns Number (Tablet)', 'jet-woo-product-gallery' ) }
					value={ attributes.columns_tablet }
					min={ `1` }
					max={ `6` }
					onChange={ newValue => {
						setAttributes( { columns_tablet: Number( newValue ) } );
					} }
				/>
			}

			{ undefined !== attributes.columns_mobile &&
				<TextControl
					type="number"
					label={ __( 'Columns Number (Mobile)', 'jet-woo-product-gallery' ) }
					value={ attributes.columns_mobile }
					min={ `1` }
					max={ `6` }
					onChange={ newValue => {
						setAttributes( { columns_mobile: Number( newValue ) } );
					} }
				/>
			}
		</PanelBody>
	);

};