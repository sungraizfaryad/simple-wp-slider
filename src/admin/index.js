import { createRoot } from '@wordpress/element';
import { SliderProvider } from './SliderProvider';
import { SlideManager } from './SlideManager';
import './style.scss';

document.addEventListener( 'DOMContentLoaded', () => {
	const mount = document.getElementById( 'swps-admin-root' );
	if ( ! mount ) {
		return;
	}
	const sliderId = parseInt( mount.dataset.sliderId, 10 ) || 0;
	if ( ! sliderId ) {
		return;
	}
	const root = createRoot( mount );
	root.render(
		<SliderProvider sliderId={ sliderId }>
			<SlideManager />
		</SliderProvider>
	);
} );
