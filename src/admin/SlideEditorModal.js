import { useState } from '@wordpress/element';
import {
	Modal,
	TextControl,
	SelectControl,
	Button,
	Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { MediaButton } from './MediaButton';
import { resolveOembed } from './rest';

export function SlideEditorModal( { slide, onClose, onSave } ) {
	const [ draft, setDraft ] = useState( { ...slide } );
	const [ resolving, setResolving ] = useState( false );
	const [ resolveError, setResolveError ] = useState( '' );

	const set = ( k, v ) => setDraft( ( d ) => ( { ...d, [ k ]: v } ) );

	async function resolveVideoUrl() {
		setResolveError( '' );
		setResolving( true );
		try {
			const out = await resolveOembed( draft.video_url );
			setDraft( ( d ) => ( {
				...d,
				_resolved_thumb: out.thumbnail_url,
				_vimeo_thumb: out.thumbnail_url,
			} ) );
		} catch ( err ) {
			setResolveError(
				err.message || __( 'Could not resolve URL', 'simple-wp-slider' )
			);
		}
		setResolving( false );
	}

	return (
		<Modal
			title={ __( 'Edit slide', 'simple-wp-slider' ) }
			onRequestClose={ onClose }
			size="medium"
		>
			{ resolveError && <Notice status="error">{ resolveError }</Notice> }

			{ draft.type === 'image' && (
				<>
					<MediaButton
						mime="image"
						onSelect={ ( att ) =>
							setDraft( ( d ) => ( {
								...d,
								attachment_id: att.id,
								alt: att.alt || d.alt,
								thumbnail: att.sizes?.thumbnail?.url || att.url,
							} ) )
						}
					/>
					{ draft.attachment_id ? (
						<p>
							{ __( 'Selected attachment:', 'simple-wp-slider' ) }{ ' ' }
							#{ draft.attachment_id }
						</p>
					) : null }
					<TextControl
						label={ __( 'Alt text', 'simple-wp-slider' ) }
						value={ draft.alt }
						onChange={ ( v ) => set( 'alt', v ) }
					/>
					<TextControl
						label={ __( 'Caption', 'simple-wp-slider' ) }
						value={ draft.caption }
						onChange={ ( v ) => set( 'caption', v ) }
					/>
					<TextControl
						label={ __( 'Link URL', 'simple-wp-slider' ) }
						value={ draft.link_url }
						onChange={ ( v ) => set( 'link_url', v ) }
					/>
					<SelectControl
						label={ __( 'Link target', 'simple-wp-slider' ) }
						value={ draft.link_target }
						options={ [
							{
								label: __( 'Same window', 'simple-wp-slider' ),
								value: '_self',
							},
							{
								label: __( 'New window', 'simple-wp-slider' ),
								value: '_blank',
							},
						] }
						onChange={ ( v ) => set( 'link_target', v ) }
					/>
					<TextControl
						label={ __( 'CTA text', 'simple-wp-slider' ) }
						value={ draft.cta_text }
						onChange={ ( v ) => set( 'cta_text', v ) }
					/>
					<TextControl
						label={ __( 'CTA URL', 'simple-wp-slider' ) }
						value={ draft.cta_url }
						onChange={ ( v ) => set( 'cta_url', v ) }
					/>
				</>
			) }

			{ draft.type === 'video_self' && (
				<>
					<MediaButton
						mime="video"
						label={ __(
							'Choose video from Media Library',
							'simple-wp-slider'
						) }
						onSelect={ ( att ) =>
							setDraft( ( d ) => ( {
								...d,
								attachment_id: att.id,
							} ) )
						}
					/>
					{ draft.attachment_id ? (
						<p>
							{ __( 'Selected attachment:', 'simple-wp-slider' ) }{ ' ' }
							#{ draft.attachment_id }
						</p>
					) : null }
					<TextControl
						label={ __( 'Caption', 'simple-wp-slider' ) }
						value={ draft.caption }
						onChange={ ( v ) => set( 'caption', v ) }
					/>
				</>
			) }

			{ ( draft.type === 'video_youtube' ||
				draft.type === 'video_vimeo' ) && (
				<>
					<TextControl
						label={
							draft.type === 'video_youtube'
								? __( 'YouTube URL', 'simple-wp-slider' )
								: __( 'Vimeo URL', 'simple-wp-slider' )
						}
						value={ draft.video_url }
						onChange={ ( v ) => set( 'video_url', v ) }
					/>
					<Button
						variant="secondary"
						disabled={ resolving || ! draft.video_url }
						onClick={ resolveVideoUrl }
					>
						{ resolving
							? __( 'Resolving…', 'simple-wp-slider' )
							: __( 'Validate & preview', 'simple-wp-slider' ) }
					</Button>
					{ draft._resolved_thumb && (
						<img
							src={ draft._resolved_thumb }
							alt=""
							style={ {
								maxWidth: '240px',
								display: 'block',
								marginTop: '0.5rem',
							} }
						/>
					) }
					<TextControl
						label={ __( 'Caption', 'simple-wp-slider' ) }
						value={ draft.caption }
						onChange={ ( v ) => set( 'caption', v ) }
					/>
				</>
			) }

			<p style={ { marginTop: '1rem' } }>
				<Button variant="primary" onClick={ () => onSave( draft ) }>
					{ __( 'Save slide', 'simple-wp-slider' ) }
				</Button>{ ' ' }
				<Button variant="link" onClick={ onClose }>
					{ __( 'Cancel', 'simple-wp-slider' ) }
				</Button>
			</p>
		</Modal>
	);
}
