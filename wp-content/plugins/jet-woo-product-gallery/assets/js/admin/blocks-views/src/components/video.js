const { __ } = wp.i18n;

const {
	MediaUpload,
	MediaUploadCheck
} = wp.blockEditor;

const {
	Button,
	PanelBody,
	SelectControl,
	ToggleControl
} = wp.components;

export default props => {

	const {
		attributes,
		setAttributes
	} = props;

	return (
		<PanelBody title={ __( 'Video', 'jet-woo-product-gallery' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Display Video In', 'jet-woo-product-gallery' ) }
				value={ attributes.video_display_in }
				options={ [
					{
						value: 'content',
						label: __( 'Content', 'jet-woo-product-gallery' )
					},
					{
						value: 'popup',
						label: __( 'Popup', 'jet-woo-product-gallery' )
					}
				] }
				onChange={ ( newValue ) => {
					setAttributes( { video_display_in: newValue } );
				} }
			/>

			<SelectControl
				label={ __( 'Display Video In', 'jet-woo-product-gallery' ) }
				help = { __( 'Worked just with youtube and vimeo video types.', 'jet-woo-product-gallery' ) }
				value={ attributes.aspect_ratio }
				options={ [
					{
						value: '16-9',
						label: '16:9'
					},
					{
						value: '21-9',
						label: '21:9'
					},
					{
						value: '9-16',
						label: '9:16'
					},
					{
						value: '4-3',
						label: '4:3'
					},
					{
						value: '2-3',
						label: '2:3'
					},
					{
						value: '3-2',
						label: '3:2'
					},
					{
						value: '1-1',
						label: '1:1'
					}
				] }
				onChange={ ( newValue ) => {
					setAttributes( { aspect_ratio: newValue } );
				} }
			/>

			{ 'content' === attributes.video_display_in &&
				<ToggleControl
					label={ __( 'Display Video at First Place', 'jet-woo-product-gallery' ) }
					checked={ attributes.first_place_video }
					onChange={ () => {
						setAttributes( { first_place_video: ! attributes.first_place_video } );
					} }
				/>
			}

			<ToggleControl
				label={ __( 'Autoplay', 'jet-woo-product-gallery' ) }
				checked={ attributes.autoplay }
				onChange={ () => {
					setAttributes( { autoplay: ! attributes.autoplay } );
				} }
			/>

			<ToggleControl
				label={ __( 'Loop', 'jet-woo-product-gallery' ) }
				checked={ attributes.loop }
				onChange={ () => {
					setAttributes( { loop: ! attributes.loop } );
				} }
			/>

			{ 'content' === attributes.video_display_in &&
				<div className={ "components-base-control" }>
					<ToggleControl
						label={ __( 'Show Play Button', 'jet-woo-product-gallery' ) }
						checked={ attributes.show_play_button }
						onChange={ () => {
							setAttributes( { show_play_button: ! attributes.show_play_button } );
						} }
					/>

					{ attributes.show_play_button &&
						<MediaUploadCheck>
							{ 0 !== Object.keys( attributes.play_button_icon ).length &&
								<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
									<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
										setAttributes( { play_button_icon: {} } );
									} }
									></Button>
									<img src={ attributes.play_button_icon.url } width="100%" height="auto" />
								</div>
							}
							<div className="components-base-control jet-media-control">
								<MediaUpload
									allowedTypes={ [ 'image/svg+xml' ] }
									value={ attributes.play_button_icon.id }
									onSelect={ ( media ) => {
										const iconData = {
											id:  media.id,
											url: media.url
										};

										setAttributes( { play_button_icon: iconData } );
									} }
									render={ ( { open } ) => (
										<Button
											isSecondary
											icon="edit"
											onClick={ open }
										>{ __( 'Select Play Button Icon', 'jet-woo-product-gallery' ) }</Button>
									) }
								/>
							</div>
						</MediaUploadCheck>
					}
				</div>
			}

			{ 'popup' === attributes.video_display_in &&
				<MediaUploadCheck>
					{ 0 !== Object.keys( attributes.popup_button_icon ).length &&
						<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
							<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
								setAttributes( { popup_button_icon: {} } );
							} }
							></Button>
							<img src={ attributes.popup_button_icon.url } width="100%" height="auto" />
						</div>
					}
					<div className="components-base-control jet-media-control">
						<MediaUpload
							allowedTypes={ [ 'image/svg+xml' ] }
							value={ attributes.popup_button_icon.id }
							onSelect={ ( media ) => {
								const iconData = {
									id:  media.id,
									url: media.url
								};

								setAttributes( { popup_button_icon: iconData } );
							} }
							render={ ( { open } ) => (
								<Button
									isSecondary
									icon="edit"
									onClick={ open }
								>{ __( 'Select Popup Button Icon', 'jet-woo-product-gallery' ) }</Button>
							) }
						/>
					</div>
				</MediaUploadCheck>
			}
		</PanelBody>
	);

};