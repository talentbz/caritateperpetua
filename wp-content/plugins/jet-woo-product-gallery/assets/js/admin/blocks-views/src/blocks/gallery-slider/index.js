import metadata from './block.json';

import General from '../../components/general.js';
import Images from '../../components/images.js';
import Photoswipe from '../../components/photoswipe.js';
import Video from '../../components/video.js';

const { __ } = wp.i18n;
const { icon } = metadata;
const { registerBlockType } = wp.blocks;
const { serverSideRender: ServerSideRender } = wp;

const {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps
} = wp.blockEditor;

const {
	Button,
	Disabled,
	PanelBody,
	SelectControl,
	TabPanel,
	TextControl,
	ToggleControl
} = wp.components;

registerBlockType( metadata, {
	icon: <span dangerouslySetInnerHTML={ { __html: icon } }></span>,
	edit: props => {
		const blockProps = useBlockProps();

		const {
			attributes,
			name,
			setAttributes
		} = props;

		return [
			props.isSelected && (
				<InspectorControls>
					<General { ...props } />

					<Images { ...props } />

					<PanelBody title={ __( 'Slider', 'jet-woo-product-gallery' ) } initialOpen={ false }>
						<SelectControl
							label={ __( 'Slider Direction', 'jet-woo-product-gallery' ) }
							value={ attributes.slider_pagination_direction }
							options={ [
								{
									value: 'vertical',
									label: __( 'Vertical', 'jet-woo-product-gallery' )
								},
								{
									value: 'horizontal',
									label: __( 'Horizontal', 'jet-woo-product-gallery' )
								}
							] }
							onChange={ ( newValue ) => {
								setAttributes( { slider_pagination_direction: newValue } );
							} }
						/>

						<ToggleControl
							label={ __( 'Enable Infinite Loop', 'jet-woo-product-gallery' ) }
							checked={ attributes.slider_enable_infinite_loop }
							onChange={ () => {
								setAttributes( { slider_enable_infinite_loop: ! attributes.slider_enable_infinite_loop } );
							} }
						/>

						{ ( 'vertical' !== attributes.slider_pagination_direction || ! attributes.slider_show_pagination ) &&
							<ToggleControl
								label={ __( 'Enable Equal Slides Height', 'jet-woo-product-gallery' ) }
								checked={ attributes.slider_equal_slides_height }
								onChange={ () => {
									setAttributes( { slider_equal_slides_height: ! attributes.slider_equal_slides_height } );
								} }
							/>
						}

						<TextControl
							type="number"
							label={ __( 'Slider Sensitivity', 'jet-woo-product-gallery' ) }
							value={ attributes.slider_sensitivity }
							min={ `0` }
							max={ `1` }
							step={ `0.1` }
							onChange={ ( newValue ) => {
								setAttributes( { slider_sensitivity: Number( newValue ) } )
							} }
						/>

						{ ( 'vertical' !== attributes.slider_pagination_direction || ! attributes.slider_show_pagination ) &&
							<ToggleControl
								label={ __( 'Enable Center Mode', 'jet-woo-product-gallery' ) }
								checked={ attributes.slider_enable_center_mode }
								onChange={ () => {
									setAttributes( { slider_enable_center_mode: ! attributes.slider_enable_center_mode } );
								} }
							/>
						}

						{ attributes.slider_enable_center_mode && 'horizontal' === attributes.slider_pagination_direction &&
							<TabPanel
								className="jet-responsive-panel"
								activeClass="active-tab"
								initialTabName="desktop"
								tabs={ [
									{
										name: 'desktop',
										title: __( 'Desktop', 'jet-woo-product-gallery' ),
										className: 'desktop-panel',
									},
									{
										name: 'tablet',
										title: __( 'Tablet', 'jet-woo-product-gallery' ),
										className: 'tablet-panel',
									},
									{
										name: 'mobile',
										title: __( 'Mobile', 'jet-woo-product-gallery' ),
										className: 'mobile-panel',
									},
								] }
							>
								{ ( tab ) =>
									<PanelBody>
										{ 'desktop' === tab.name &&
											<div className={ "components-base-control" }>
												<TextControl
													type="number"
													label={ __( 'Slides to Show', 'jet-woo-product-gallery' ) }
													value={ attributes.slider_center_mode_slides }
													min={ `2` }
													max={ `10` }
													onChange={ newValue => {
														setAttributes( { slider_center_mode_slides: Number( newValue ) } );
													} }
												/>

												<TextControl
													type="number"
													label={ __( 'Space Between', 'jet-woo-product-gallery' ) }
													value={ attributes.slider_center_mode_space_between }
													min={ `0` }
													onChange={ newValue => {
														setAttributes( { slider_center_mode_space_between: Number( newValue ) } );
													} }
												/>
											</div>
										}

										{ 'tablet' === tab.name &&
											<div className={ "components-base-control" }>
												<TextControl
													type="number"
													label={ __( 'Slides to Show', 'jet-woo-product-gallery' ) }
													value={ attributes.slider_center_mode_slides_tablet }
													min={ `2` }
													max={ `10` }
													onChange={ newValue => {
														setAttributes( { slider_center_mode_slides_tablet: Number( newValue ) } );
													} }
												/>

												<TextControl
													type="number"
													label={ __( 'Space Between', 'jet-woo-product-gallery' ) }
													value={ attributes.slider_center_mode_space_between_tablet }
													min={ `0` }
													onChange={ newValue => {
														setAttributes( { slider_center_mode_space_between_tablet: Number( newValue ) } );
													} }
												/>
											</div>
										}

										{ 'mobile' === tab.name &&
											<div className={ "components-base-control" }>
												<TextControl
													type="number"
													label={ __( 'Slides to Show', 'jet-woo-product-gallery' ) }
													value={ attributes.slider_center_mode_slides_mobile }
													min={ `2` }
													max={ `10` }
													onChange={ newValue => {
														setAttributes( { slider_center_mode_slides_mobile: Number( newValue ) } );
													} }
												/>

												<TextControl
													type="number"
													label={ __( 'Space Between', 'jet-woo-product-gallery' ) }
													value={ attributes.slider_center_mode_space_between_mobile }
													min={ `0` }
													onChange={ newValue => {
														setAttributes( { slider_center_mode_space_between_mobile: Number( newValue ) } );
													} }
												/>
											</div>
										}
									</PanelBody>
								}
							</TabPanel>
						}

						{ ! attributes.slider_enable_center_mode &&
							<SelectControl
								label={ __( 'Transition Effect', 'jet-woo-product-gallery' ) }
								value={ attributes.slider_transition_effect }
								options={ [
									{
										value: 'slide',
										label: __( 'Slide', 'jet-woo-product-gallery' )
									},
									{
										value: 'fade',
										label: __( 'Fade', 'jet-woo-product-gallery' )
									},
									{
										value: 'cube',
										label: __( 'Cube', 'jet-woo-product-gallery' )
									},
									{
										value: 'coverflow',
										label: __( 'Coverflow', 'jet-woo-product-gallery' )
									},
									{
										value: 'flip',
										label: __( 'Flip', 'jet-woo-product-gallery' )
									}
								] }
								onChange={ ( newValue ) => {
									setAttributes( { slider_transition_effect: newValue } );
								} }
							/>
						}

						<ToggleControl
							label={ __( 'Show Navigation', 'jet-woo-product-gallery' ) }
							checked={ attributes.slider_show_nav }
							onChange={ () => {
								setAttributes( { slider_show_nav: ! attributes.slider_show_nav } );
							} }
						/>

						{ attributes.slider_show_nav &&
							<div className={ "components-base-control" }>
								<MediaUploadCheck>
									{ 0 !== Object.keys( attributes.slider_nav_arrow_prev ).length &&
										<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
											<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
												setAttributes( { slider_nav_arrow_prev: {} } );
											} }
											></Button>
											<img src={ attributes.slider_nav_arrow_prev.url } width="100%" height="auto" />
										</div>
									}
									<div className="components-base-control jet-media-control">
										<MediaUpload
											allowedTypes={ [ 'image/svg+xml' ] }
											value={ attributes.slider_nav_arrow_prev.id }
											onSelect={ ( media ) => {
												const iconData = {
													id:  media.id,
													url: media.url
												};

												setAttributes( { slider_nav_arrow_prev: iconData } );
											} }
											render={ ( { open } ) => (
												<Button
													isSecondary
													icon="edit"
													onClick={ open }
												>{ __( 'Select Previous Arrow Icon', 'jet-woo-product-gallery' ) }</Button>
											) }
										/>
									</div>
								</MediaUploadCheck>

								<MediaUploadCheck>
									{ 0 !== Object.keys( attributes.slider_nav_arrow_next ).length &&
										<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
											<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
												setAttributes( { slider_nav_arrow_next: {} } );
											} }
											></Button>
											<img src={ attributes.slider_nav_arrow_next.url } width="100%" height="auto" />
										</div>
									}
									<div className="components-base-control jet-media-control">
										<MediaUpload
											allowedTypes={ [ 'image/svg+xml' ] }
											value={ attributes.slider_nav_arrow_next.id }
											onSelect={ ( media ) => {
												const iconData = {
													id:  media.id,
													url: media.url
												};

												setAttributes( { slider_nav_arrow_next: iconData } );
											} }
											render={ ( { open } ) => (
												<Button
													isSecondary
													icon="edit"
													onClick={ open }
												>{ __( 'Select Next Arrow Icon', 'jet-woo-product-gallery' ) }</Button>
											) }
										/>
									</div>
								</MediaUploadCheck>
							</div>
						}

						<ToggleControl
							label={ __( 'Show Pagination', 'jet-woo-product-gallery' ) }
							checked={ attributes.slider_show_pagination }
							onChange={ () => {
								setAttributes( { slider_show_pagination: ! attributes.slider_show_pagination } );
							} }
						/>

						{ attributes.slider_show_pagination &&
							<div className={ "components-base-control" }>
								<SelectControl
									label={ __( 'Choose Controller', 'jet-woo-product-gallery' ) }
									value={ attributes.slider_pagination_type }
									options={ [
										{
											value: 'bullets',
											label: __( 'Pagination', 'jet-woo-product-gallery' )
										},
										{
											value: 'thumbnails',
											label: __( 'Thumbnails', 'jet-woo-product-gallery' )
										}
									] }
									onChange={ ( newValue ) => {
										setAttributes( { slider_pagination_type: newValue } );
									} }
								/>

								{ 'bullets' === attributes.slider_pagination_type &&
								<SelectControl
									label={ __( 'Pagination Type', 'jet-woo-product-gallery' ) }
									value={ attributes.slider_pagination_controller_type }
									options={ [
										{
											value: 'bullets',
											label: __( 'Bullets', 'jet-woo-product-gallery' )
										},
										{
											value: 'dynamic',
											label: __( 'Dynamic', 'jet-woo-product-gallery' )
										},
										{
											value: 'fraction',
											label: __( 'Fraction', 'jet-woo-product-gallery' )
										},
										{
											value: 'progressbar',
											label: __( 'Progressbar', 'jet-woo-product-gallery' )
										}
									] }
									onChange={ ( newValue ) => {
										setAttributes( { slider_pagination_controller_type: newValue } );
									} }
								/>
								}

								{ 'vertical' === attributes.slider_pagination_direction &&
									<SelectControl
										label={ __( 'Controller Position', 'jet-woo-product-gallery' ) }
										value={ attributes.slider_pagination_v_position }
										options={ [
											{
												value: 'start',
												label: __( 'Start', 'jet-woo-product-gallery' )
											},
											{
												value: 'end',
												label: __( 'End', 'jet-woo-product-gallery' )
											}
										] }
										onChange={ ( newValue ) => {
											setAttributes( { slider_pagination_v_position: newValue } );
										} }
									/>
								}

								{ 'horizontal' === attributes.slider_pagination_direction &&
									<SelectControl
										label={ __( 'Controller Position', 'jet-woo-product-gallery' ) }
										value={ attributes.slider_pagination_h_position }
										options={ [
											{
												value: 'top',
												label: __( 'Top', 'jet-woo-product-gallery' )
											},
											{
												value: 'bottom',
												label: __( 'Bottom', 'jet-woo-product-gallery' )
											}
										] }
										onChange={ ( newValue ) => {
											setAttributes( { slider_pagination_h_position: newValue } );
										} }
									/>
								}

								{ 'thumbnails' === attributes.slider_pagination_type &&
									<div className={ "components-base-control" }>
										<TabPanel
											className="jet-responsive-panel"
											activeClass="active-tab"
											initialTabName="desktop"
											tabs={ [
												{
													name: 'desktop',
													title: __( 'Desktop', 'jet-woo-product-gallery' ),
													className: 'desktop-panel',
												},
												{
													name: 'tablet',
													title: __( 'Tablet', 'jet-woo-product-gallery' ),
													className: 'tablet-panel',
												},
												{
													name: 'mobile',
													title: __( 'Mobile', 'jet-woo-product-gallery' ),
													className: 'mobile-panel',
												},
											] }
										>
											{ ( tab ) =>
												<PanelBody>
													{ 'desktop' === tab.name &&
														<div className={ "components-base-control" }>
															<TextControl
																type="number"
																label={ __( 'Visible Slides', 'jet-woo-product-gallery' ) }
																value={ attributes.pagination_thumbnails_columns }
																min={ `2` }
																max={ `12` }
																onChange={ newValue => {
																	setAttributes( { pagination_thumbnails_columns: Number( newValue ) } );
																} }
															/>

															<TextControl
																type="number"
																label={ __( 'Space Between', 'jet-woo-product-gallery' ) }
																value={ attributes.pagination_thumbnails_space_between }
																min={ `0` }
																onChange={ newValue => {
																	setAttributes( { pagination_thumbnails_space_between: Number( newValue ) } );
																} }
															/>
														</div>
													}

													{ 'tablet' === tab.name &&
														<div className={ "components-base-control" }>
															<TextControl
																type="number"
																label={ __( 'Visible Slides', 'jet-woo-product-gallery' ) }
																value={ attributes.pagination_thumbnails_columns_tablet }
																min={ `2` }
																max={ `12` }
																onChange={ newValue => {
																	setAttributes( { pagination_thumbnails_columns_tablet: Number( newValue ) } );
																} }
															/>

															<TextControl
																type="number"
																label={ __( 'Space Between', 'jet-woo-product-gallery' ) }
																value={ attributes.pagination_thumbnails_space_between_tablet }
																min={ `0` }
																onChange={ newValue => {
																	setAttributes( { pagination_thumbnails_space_between_tablet: Number( newValue ) } );
																} }
															/>
														</div>
													}

													{ 'mobile' === tab.name &&
														<div className={ "components-base-control" }>
															<TextControl
																type="number"
																label={ __( 'Visible Slides', 'jet-woo-product-gallery' ) }
																value={ attributes.pagination_thumbnails_columns_mobile }
																min={ `2` }
																max={ `12` }
																onChange={ newValue => {
																	setAttributes( { pagination_thumbnails_columns_mobile: Number( newValue ) } );
																} }
															/>

															<TextControl
																type="number"
																label={ __( 'Space Between', 'jet-woo-product-gallery' ) }
																value={ attributes.pagination_thumbnails_space_between_mobile }
																min={ `0` }
																onChange={ newValue => {
																	setAttributes( { pagination_thumbnails_space_between_mobile: Number( newValue ) } );
																} }
															/>
														</div>
													}
												</PanelBody>
											}
										</TabPanel>

										<ToggleControl
											label={ __( 'Show Thumbnails Navigation', 'jet-woo-product-gallery' ) }
											checked={ attributes.slider_show_thumb_nav }
											onChange={ () => {
												setAttributes( { slider_show_thumb_nav: ! attributes.slider_show_thumb_nav } );
											} }
										/>
									</div>
								}

								{ attributes.slider_show_thumb_nav && 'thumbnails' === attributes.slider_pagination_type &&
									<div className={ "components-base-control" }>
										<MediaUploadCheck>
											{ 0 !== Object.keys( attributes.pagination_thumbnails_slider_arrow_prev ).length &&
												<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
													<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
														setAttributes( { pagination_thumbnails_slider_arrow_prev: {} } );
													} }
													></Button>
													<img src={ attributes.pagination_thumbnails_slider_arrow_prev.url } width="100%" height="auto" />
												</div>
											}
											<div className="components-base-control jet-media-control">
												<MediaUpload
													allowedTypes={ [ 'image/svg+xml' ] }
													value={ attributes.pagination_thumbnails_slider_arrow_prev.id }
													onSelect={ ( media ) => {
														const iconData = {
															id:  media.id,
															url: media.url
														};

														setAttributes( { pagination_thumbnails_slider_arrow_prev: iconData } );
													} }
													render={ ( { open } ) => (
														<Button
															isSecondary
															icon="edit"
															onClick={ open }
														>{ __( 'Select Previous Arrow Icon', 'jet-woo-product-gallery' ) }</Button>
													) }
												/>
											</div>
										</MediaUploadCheck>

										<MediaUploadCheck>
											{ 0 !== Object.keys( attributes.pagination_thumbnails_slider_arrow_next ).length &&
												<div className={ "preview-jet-gallery-media preview-jet-gallery-media-icon" }>
													<Button className={ "jet-remove-button" } isPrimary icon="no-alt" onClick={ () => {
														setAttributes( { pagination_thumbnails_slider_arrow_next: {} } );
													} }
													></Button>
													<img src={ attributes.pagination_thumbnails_slider_arrow_next.url } width="100%" height="auto" />
												</div>
											}
											<div className="components-base-control jet-media-control">
												<MediaUpload
													allowedTypes={ [ 'image/svg+xml' ] }
													value={ attributes.pagination_thumbnails_slider_arrow_next.id }
													onSelect={ ( media ) => {
														const iconData = {
															id:  media.id,
															url: media.url
														};

														setAttributes( { pagination_thumbnails_slider_arrow_next: iconData } );
													} }
													render={ ( { open } ) => (
														<Button
															isSecondary
															icon="edit"
															onClick={ open }
														>{ __( 'Select Next Arrow Icon', 'jet-woo-product-gallery' ) }</Button>
													) }
												/>
											</div>
										</MediaUploadCheck>
									</div>
								}
							</div>
						}
					</PanelBody>

					{ attributes.enable_gallery &&
					<Photoswipe { ...props } />
					}

					{ ( 'products' === attributes.gallery_source || 'products' !== attributes.gallery_source && attributes.enable_video ) &&
					<Video { ...props } />
					}
				</InspectorControls>
			),

			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ name }
						attributes={ attributes }
						httpMethod={ 'POST' }
					/>
				</Disabled>
			</div>
		];
	},
	save: props => {
		return null;
	}
} );
