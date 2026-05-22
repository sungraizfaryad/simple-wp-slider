import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export function MediaButton( { mime = 'image', onSelect, label } ) {
	const open = () => {
		if ( ! window.wp || ! window.wp.media ) {
			return;
		}
		const frame = window.wp.media( {
			title: __( 'Select media', 'simple-wp-slider' ),
			library: { type: mime },
			multiple: false,
		} );
		frame.on( 'select', () => {
			const att = frame.state().get( 'selection' ).first().toJSON();
			onSelect( att );
		} );
		frame.open();
	};
	return (
		<Button variant="secondary" onClick={ open }>
			{ label || __( 'Choose from Media Library', 'simple-wp-slider' ) }
		</Button>
	);
}
