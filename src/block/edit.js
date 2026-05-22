import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { sliderId } = attributes;
	const blockProps = useBlockProps();

	const sliders = useSelect(
		( select ) =>
			select( 'core' ).getEntityRecords( 'postType', 'swps_slider', {
				per_page: 100,
				status: 'publish,draft',
				_fields: [ 'id', 'title', 'status' ],
			} ),
		[]
	);

	const options = [
		{ label: __( '— Select a slider —', 'simple-wp-slider' ), value: 0 },
		...( sliders || [] ).map( ( s ) => ( {
			label: `${
				s.title.rendered || __( '(no title)', 'simple-wp-slider' )
			}${ s.status === 'draft' ? ' (draft)' : '' }`,
			value: s.id,
		} ) ),
	];

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Slider', 'simple-wp-slider' ) }>
					<SelectControl
						label={ __( 'Select slider', 'simple-wp-slider' ) }
						value={ sliderId }
						options={ options }
						onChange={ ( v ) =>
							setAttributes( {
								sliderId: parseInt( v, 10 ) || 0,
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>

			{ sliderId ? (
				<ServerSideRender
					block="swps/slider"
					attributes={ attributes }
					EmptyResponsePlaceholder={ () => (
						<Placeholder
							label={ __(
								'Simple WP Slider',
								'simple-wp-slider'
							) }
						>
							{ __(
								'This slider has no slides yet.',
								'simple-wp-slider'
							) }
						</Placeholder>
					) }
				/>
			) : (
				<Placeholder
					icon="images-alt2"
					label={ __( 'Simple WP Slider', 'simple-wp-slider' ) }
					instructions={ __(
						'Pick a slider from the sidebar.',
						'simple-wp-slider'
					) }
				/>
			) }
		</div>
	);
}
