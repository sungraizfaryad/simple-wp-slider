import { createRoot } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { SliderProvider, useSlider } from './SliderProvider';
import './style.scss';

function Placeholder() {
	const { state } = useSlider();
	if ( ! state.loaded ) {
		return <Spinner />;
	}
	return (
		<div>
			<p>
				Slider loaded: <strong>{ state.title || '(untitled)' }</strong>
			</p>
			<p>
				{ state.slides.length } slides. SlideManager UI arrives in Task
				22.
			</p>
		</div>
	);
}

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
			<Placeholder />
		</SliderProvider>
	);
} );
