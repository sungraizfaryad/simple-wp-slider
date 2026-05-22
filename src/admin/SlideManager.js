import { useState } from '@wordpress/element';
import {
	Button,
	Dropdown,
	MenuItem,
	NavigableMenu,
	Notice,
	Spinner,
	Modal,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSlider } from './SliderProvider';
import { SlideList } from './SlideList';

function makeNewSlide( type ) {
	return {
		id:
			typeof crypto !== 'undefined' && crypto.randomUUID
				? crypto.randomUUID()
				: 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(
						/[xy]/g,
						( c ) => {
							const r = Math.floor( Math.random() * 16 );
							const v =
								c === 'x' ? r : Math.floor( ( r % 4 ) + 8 );
							return v.toString( 16 );
						}
				  ),
		type,
		attachment_id: 0,
		video_url: '',
		alt: '',
		caption: '',
		link_url: '',
		link_target: '_self',
		link_rel: '',
		cta_text: '',
		cta_url: '',
	};
}

export function SlideManager() {
	const { state, dispatch, save } = useSlider();
	const [ editing, setEditing ] = useState( null );

	if ( ! state.loaded ) {
		return <Spinner />;
	}

	function addSlide( type ) {
		const slide = makeNewSlide( type );
		dispatch( { type: 'set_slides', value: [ ...state.slides, slide ] } );
		setEditing( slide.id );
	}

	function deleteSlide( id ) {
		dispatch( {
			type: 'set_slides',
			value: state.slides.filter( ( s ) => s.id !== id ),
		} );
	}

	const editingSlide = state.slides.find( ( s ) => s.id === editing ) || null;

	return (
		<div>
			{ state.error && <Notice status="error">{ state.error }</Notice> }

			<Dropdown
				renderToggle={ ( { onToggle } ) => (
					<Button variant="primary" onClick={ onToggle }>
						{ __( '+ Add slide', 'simple-wp-slider' ) }
					</Button>
				) }
				renderContent={ ( { onClose } ) => (
					<NavigableMenu>
						<MenuItem
							onClick={ () => {
								onClose();
								addSlide( 'image' );
							} }
						>
							{ __( 'Image', 'simple-wp-slider' ) }
						</MenuItem>
						<MenuItem
							onClick={ () => {
								onClose();
								addSlide( 'video_self' );
							} }
						>
							{ __( 'Video (uploaded)', 'simple-wp-slider' ) }
						</MenuItem>
						<MenuItem
							onClick={ () => {
								onClose();
								addSlide( 'video_youtube' );
							} }
						>
							{ __( 'YouTube', 'simple-wp-slider' ) }
						</MenuItem>
						<MenuItem
							onClick={ () => {
								onClose();
								addSlide( 'video_vimeo' );
							} }
						>
							{ __( 'Vimeo', 'simple-wp-slider' ) }
						</MenuItem>
					</NavigableMenu>
				) }
			/>

			<SlideList onEdit={ setEditing } onDelete={ deleteSlide } />

			{ editingSlide && (
				<Modal
					title={ __( 'Edit slide', 'simple-wp-slider' ) }
					onRequestClose={ () => setEditing( null ) }
					size="medium"
				>
					<p>
						{ __(
							'Slide editor arrives in Task 23 (SlideEditorModal). Settings panel arrives in Task 24.',
							'simple-wp-slider'
						) }
					</p>
					<p>
						{ __( 'Slide id:', 'simple-wp-slider' ) }{ ' ' }
						<code>{ editingSlide.id }</code>
					</p>
				</Modal>
			) }

			<p style={ { marginTop: '1rem' } }>
				<Button
					variant="primary"
					disabled={ state.saving }
					onClick={ save }
				>
					{ state.saving
						? __( 'Saving…', 'simple-wp-slider' )
						: __( 'Save slider', 'simple-wp-slider' ) }
				</Button>
			</p>
		</div>
	);
}
