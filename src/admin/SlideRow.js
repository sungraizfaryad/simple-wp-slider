import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

const TYPE_LABELS = {
	image: __( 'Image', 'simple-wp-slider' ),
	video_self: __( 'Video (uploaded)', 'simple-wp-slider' ),
	video_youtube: __( 'YouTube', 'simple-wp-slider' ),
	video_vimeo: __( 'Vimeo', 'simple-wp-slider' ),
};

export function SlideRow( { slide, onEdit, onDelete } ) {
	const { attributes, listeners, setNodeRef, transform, transition } =
		useSortable( { id: slide.id } );
	const style = {
		transform: CSS.Transform.toString( transform ),
		transition,
	};

	const needsRefix =
		slide.type === 'image' && ! slide.attachment_id && slide._legacy_url;

	return (
		<div ref={ setNodeRef } style={ style } className="swps-slide-row">
			<span
				className="swps-drag"
				{ ...attributes }
				{ ...listeners }
				aria-label={ __( 'Reorder', 'simple-wp-slider' ) }
			>
				⠿
			</span>
			{ slide.thumbnail ? (
				<img className="swps-thumb" src={ slide.thumbnail } alt="" />
			) : (
				<span className="swps-thumb" />
			) }
			<strong>
				{ slide.caption ||
					slide.alt ||
					__( '(no label)', 'simple-wp-slider' ) }
			</strong>
			<span>{ TYPE_LABELS[ slide.type ] || slide.type }</span>
			{ needsRefix && (
				<span className="swps-amber">
					⚠{ ' ' }
					{ __( 'Re-attach from Media Library', 'simple-wp-slider' ) }
				</span>
			) }
			<div className="swps-row-actions">
				<Button
					variant="secondary"
					onClick={ () => onEdit( slide.id ) }
				>
					{ __( 'Edit', 'simple-wp-slider' ) }
				</Button>
				<Button
					variant="link"
					isDestructive
					onClick={ () => onDelete( slide.id ) }
				>
					{ __( 'Delete', 'simple-wp-slider' ) }
				</Button>
			</div>
		</div>
	);
}
