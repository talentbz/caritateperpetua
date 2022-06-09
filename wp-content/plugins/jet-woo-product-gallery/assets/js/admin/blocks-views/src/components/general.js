const { __ } = wp.i18n;

const {
	MediaUpload,
	MediaUploadCheck
} = wp.blockEditor;

const {
	Button,
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl
} = wp.components;

export default props => {

	const {
		attributes,
		setAttributes
	} = props;

	const gallerySources = window.JetGalleryBlocksData.gallerySources;

	const selectedGalleryImages = Object.values( attributes.gallery_images ).map( ( { id, url } ) =>
		<div className={ "preview-jet-gallery-item" } style={ { backgroundImage: 'url(' +  url + ')' } } >
			<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
				const previewImages = Object.values( attributes.gallery_images ).filter( image => image.id !== id );

				setAttributes( { gallery_images: previewImages } );
			} }
			></Button>
		</div>
	);

	return (
		<PanelBody title={ __( 'General', 'jet-woo-product-gallery' ) }>
			<SelectControl
				label={ __( 'Source', 'jet-woo-product-gallery' ) }
				value={ attributes.gallery_source }
				options={ gallerySources }
				onChange={ ( newValue ) => {
					setAttributes( { gallery_source: newValue } );
				} }
			/>

			{ 'manual' === attributes.gallery_source &&
				<MediaUploadCheck>
					<div className="components-base-control jet-media-control">
						{ 0 !== attributes.gallery_images.length &&
							<div className={ "preview-jet-gallery-grid" }>{ selectedGalleryImages }</div>
						}
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							multiple={ true }
							gallery={ true }
							value={ Object.values( attributes.gallery_images ).map( ( { id } ) => id ) }
							onSelect={ ( media ) => {
								let imagesData = {};

								media.forEach( ( el, i ) => {
									imagesData[i] = {
										id: el.id,
										url: el.url
									};
								} );

								setAttributes( { gallery_images: imagesData } );
							} }
							render={ ( { open } ) => (
								<Button
									isSecondary
									icon="edit"
									onClick={ open }
								>{ __( 'Select Gallery Images', 'jet-woo-product-gallery' ) }</Button>
							) }
						/>
					</div>
				</MediaUploadCheck>
			}

			{ 'cpt' === attributes.gallery_source &&
				<div className={ "components-base-control" }>
					<TextControl
						type="text"
						label={ __( 'Gallery Key', 'jet-woo-product-gallery' ) }
						value={ attributes.gallery_key }
						onChange={ ( newValue ) => {
							setAttributes( { gallery_key: newValue } )
						} }
					/>

					<ToggleControl
						label={ __( 'Enable Featured Image', 'jet-woo-product-gallery' ) }
						checked={ attributes.enable_feature_image }
						onChange={ () => {
							setAttributes( { enable_feature_image: ! attributes.enable_feature_image } );
						} }
					/>
				</div>
			}

			{ 'products' === attributes.gallery_source &&
				<div className={ "components-base-control" }>
					<TextControl
						type="number"
						label={ __( 'Product id', 'jet-woo-product-gallery' ) }
						value={ attributes.product_id }
						onChange={ ( newValue ) => {
							setAttributes( { product_id: Number( newValue ) } )
						} }
					/>

					<ToggleControl
						label={ __( 'Disable Featured Image', 'jet-woo-product-gallery' ) }
						checked={ attributes.disable_feature_image }
						onChange={ () => {
							setAttributes( { disable_feature_image: ! attributes.disable_feature_image } );
						} }
					/>
				</div>
			}

			{ 'products' !== attributes.gallery_source &&
				<ToggleControl
					label={ __( 'Enable Video', 'jet-woo-product-gallery' ) }
					checked={ attributes.enable_video }
					onChange={ () => {
						setAttributes( { enable_video: ! attributes.enable_video } );
					} }
				/>
			}

			{ 'products' !== attributes.gallery_source && attributes.enable_video &&
				<SelectControl
					label={ __( 'Video Type', 'jet-woo-product-gallery' ) }
					value={ attributes.video_type }
					options={ [
						{
							value: 'youtube',
							label: __( 'YouTube', 'jet-woo-product-gallery' )
						},
						{
							value: 'vimeo',
							label: __( 'Vimeo', 'jet-woo-product-gallery' )
						},
						{
							value: 'self_hosted',
							label: __( 'Self Hosted', 'jet-woo-product-gallery' )
						}
					] }
					onChange={ ( newValue ) => {
						setAttributes( { video_type: newValue } );
					} }
				/>
			}

			{ 'products' !== attributes.gallery_source && attributes.enable_video && 'youtube' === attributes.video_type &&
				<TextControl
					type="text"
					label={ __( 'YouTube URL', 'jet-woo-product-gallery' ) }
					value={ attributes.youtube_url }
					onChange={ ( newValue ) => {
						setAttributes( { youtube_url: newValue } )
					} }
				/>
			}

			{ 'products' !== attributes.gallery_source && attributes.enable_video && 'vimeo' === attributes.video_type &&
				<TextControl
					type="text"
					label={ __( 'Vimeo URL', 'jet-woo-product-gallery' ) }
					value={ attributes.vimeo_url }
					onChange={ ( newValue ) => {
						setAttributes( { vimeo_url: newValue } )
					} }
				/>
			}

			{ 'products' !== attributes.gallery_source && attributes.enable_video && 'self_hosted' === attributes.video_type &&
				<MediaUploadCheck>
					{ 0 !== Object.keys( attributes.self_hosted_url ).length &&
						<div className={ "preview-jet-gallery-media preview-jet-gallery-media-video" }>
							<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
								setAttributes( { self_hosted_url: {} } );
							} }
							></Button>
							<div className={ "jet-video-wrapper" }>
								<video preload="metadata" src={ attributes.self_hosted_url.url }></video>
								<img className={ "video-icon" } src={ attributes.self_hosted_url.icon } />
							</div>
						</div>
					}
					<div className="components-base-control jet-media-control">
						<MediaUpload
							allowedTypes={ [ 'video' ] }
							value={ attributes.self_hosted_url.id }
							onSelect={ ( media ) => {
								const videoData = {
									id:  media.id,
									url: media.url,
									icon: media.icon
								};

								setAttributes( { self_hosted_url: videoData } );
							} }
							render={ ( { open } ) => (
								<Button
									isSecondary
									icon="edit"
									onClick={ open }
								>{ __( 'Select Video', 'jet-woo-product-gallery' ) }</Button>
							) }
						/>
					</div>
				</MediaUploadCheck>
			}

			{ 'products' !== attributes.gallery_source && attributes.enable_video &&
				<MediaUploadCheck>
					{ 0 !== Object.keys( attributes.custom_placeholder ).length &&
						<div className={ "preview-jet-gallery-media" }>
							<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
								setAttributes( { custom_placeholder: {} } );
							} }
							></Button>
							<img src={ attributes.custom_placeholder.url } width="100%" height="auto" />
						</div>
					}
					<div className="components-base-control jet-media-control">
						<MediaUpload
							allowedTypes={ [ 'image' ] }
							value={ attributes.custom_placeholder.id }
							onSelect={ ( media ) => {
								const imageData = {
									id:  media.id,
									url: media.url
								};

								setAttributes( { custom_placeholder: imageData } );
							} }
							render={ ( { open } ) => (
								<Button
									isSecondary
									icon="edit"
									onClick={ open }
								>{ __( 'Select Video Placeholder', 'jet-woo-product-gallery' ) }</Button>
							) }
						/>
					</div>
				</MediaUploadCheck>
			}

			<ToggleControl
				label={ __( 'Enable Zoom', 'jet-woo-product-gallery' ) }
				checked={ attributes.enable_zoom }
				onChange={ () => {
					setAttributes( { enable_zoom: ! attributes.enable_zoom } );
				} }
			/>

			{ attributes.enable_zoom &&
				<TextControl
					type="number"
					label={ __( 'Zoom Magnify', 'jet-woo-product-gallery' ) }
					value={ attributes.zoom_magnify }
					min={ `1` }
					max={ `2` }
					step={ `0.1` }
					onChange={ ( newValue ) => {
						setAttributes( { zoom_magnify: Number( newValue ) } )
					} }
				/>
			}

			<ToggleControl
				label={ __( 'Enable Photoswipe Gallery', 'jet-woo-product-gallery' ) }
				checked={ attributes.enable_gallery }
				onChange={ () => {
					setAttributes( { enable_gallery: ! attributes.enable_gallery } );
				} }
			/>

			{ attributes.enable_gallery &&
				<SelectControl
					label={ __( 'Photoswipe Gallery Trigger Type', 'jet-woo-product-gallery' ) }
					value={ attributes.gallery_trigger_type }
					options={ [
						{
							value: 'button',
							label: __( 'Button', 'jet-woo-product-gallery' ),
						},
						{
							value: 'image',
							label: __( 'Image', 'jet-woo-product-gallery' ),
						}
					] }
					onChange={ ( newValue ) => {
						setAttributes( { gallery_trigger_type: newValue } );
					} }
				/>
			}

			{ attributes.enable_gallery && 'button' === attributes.gallery_trigger_type &&
				<div className={ "components-base-control" }>
					<SelectControl
						label={ __( 'Trigger Button Position', 'jet-woo-product-gallery' ) }
						value={ attributes.gallery_button_position }
						options={ [
							{
								value: 'top-right',
								label: __( 'Top Right', 'jet-woo-product-gallery' ),
							},
							{
								value: 'bottom-right',
								label: __( 'Bottom Right', 'jet-woo-product-gallery' ),
							},
							{
								value: 'bottom-left',
								label: __( 'Bottom Left', 'jet-woo-product-gallery' ),
							},
							{
								value: 'top-left',
								label: __( 'Top Left', 'jet-woo-product-gallery' ),
							},
							{
								value: 'center',
								label: __( 'Center', 'jet-woo-product-gallery' ),
							}
						] }
						onChange={ ( newValue ) => {
							setAttributes( { gallery_button_position: newValue } );
						} }
					/>

					<MediaUploadCheck>
						{ 0 !== Object.keys( attributes.gallery_button_icon ).length &&
							<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
								<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
									setAttributes( { gallery_button_icon: {} } );
								} }
								></Button>
								<img src={ attributes.gallery_button_icon.url } width="100%" height="auto" />
							</div>
						}
						<div className="components-base-control jet-media-control">
							<MediaUpload
								allowedTypes={ [ 'image/svg+xml' ] }
								value={ attributes.gallery_button_icon.id }
								onSelect={ ( media ) => {
									const iconData = {
										id:  media.id,
										url: media.url
									};

									setAttributes( { gallery_button_icon: iconData } );
								} }
								render={ ( { open } ) => (
									<Button
										isSecondary
										icon="edit"
										onClick={ open }
									>{ __( 'Select Trigger Button Icon', 'jet-woo-product-gallery' ) }</Button>
								) }
							/>
						</div>
					</MediaUploadCheck>

					<ToggleControl
						label={ __( 'Show on Hover', 'jet-woo-product-gallery' ) }
						checked={ attributes.show_on_hover }
						onChange={ () => {
							setAttributes( { show_on_hover: ! attributes.show_on_hover } );
						} }
					/>
				</div>
			}
		</PanelBody>
	);

};