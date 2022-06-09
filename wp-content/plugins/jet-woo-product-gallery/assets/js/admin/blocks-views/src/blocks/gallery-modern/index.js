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
	useBlockProps
} = wp.blockEditor;

const { Disabled } = wp.components;

registerBlockType( metadata, {
	icon: <span dangerouslySetInnerHTML={ { __html: icon } }></span>,
	edit: props => {
		const blockProps = useBlockProps();

		const {
			attributes,
			name
		} = props;

		return [
			props.isSelected && (
				<InspectorControls>
					<General { ...props } />

					<Images { ...props } />

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
