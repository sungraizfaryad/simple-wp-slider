import {
	PanelBody,
	ToggleControl,
	RangeControl,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSlider } from './SliderProvider';

export function SettingsPanel() {
	const { state, dispatch } = useSlider();
	if ( ! state.settings ) {
		return null;
	}
	const s = state.settings;
	const set = ( k, v ) =>
		dispatch( { type: 'set_settings', value: { ...s, [ k ]: v } } );

	return (
		<PanelBody
			title={ __( 'Slider settings', 'simple-wp-slider' ) }
			initialOpen={ false }
		>
			<ToggleControl
				label={ __( 'Autoplay', 'simple-wp-slider' ) }
				checked={ !! s.autoplay }
				onChange={ ( v ) => set( 'autoplay', v ) }
			/>
			<RangeControl
				label={ __( 'Autoplay delay (ms)', 'simple-wp-slider' ) }
				min={ 500 }
				max={ 60000 }
				step={ 500 }
				value={ s.autoplay_delay }
				onChange={ ( v ) => set( 'autoplay_delay', v ) }
			/>
			<ToggleControl
				label={ __( 'Loop', 'simple-wp-slider' ) }
				checked={ !! s.loop }
				onChange={ ( v ) => set( 'loop', v ) }
			/>
			<RangeControl
				label={ __( 'Transition speed (ms)', 'simple-wp-slider' ) }
				min={ 100 }
				max={ 10000 }
				step={ 100 }
				value={ s.speed }
				onChange={ ( v ) => set( 'speed', v ) }
			/>
			<SelectControl
				label={ __( 'Effect', 'simple-wp-slider' ) }
				value={ s.effect }
				options={ [
					{
						label: __( 'Slide', 'simple-wp-slider' ),
						value: 'slide',
					},
					{ label: __( 'Fade', 'simple-wp-slider' ), value: 'fade' },
				] }
				onChange={ ( v ) => set( 'effect', v ) }
			/>
			<ToggleControl
				label={ __( 'Show arrows', 'simple-wp-slider' ) }
				checked={ !! s.arrows }
				onChange={ ( v ) => set( 'arrows', v ) }
			/>
			<ToggleControl
				label={ __( 'Show dots', 'simple-wp-slider' ) }
				checked={ !! s.dots }
				onChange={ ( v ) => set( 'dots', v ) }
			/>
			<ToggleControl
				label={ __( 'Keyboard navigation', 'simple-wp-slider' ) }
				checked={ !! s.keyboard }
				onChange={ ( v ) => set( 'keyboard', v ) }
			/>
			<ToggleControl
				label={ __( 'Pause on hover', 'simple-wp-slider' ) }
				checked={ !! s.pause_on_hover }
				onChange={ ( v ) => set( 'pause_on_hover', v ) }
			/>
			<RangeControl
				label={ __( 'Slides per view', 'simple-wp-slider' ) }
				min={ 1 }
				max={ 10 }
				value={ s.slides_per_view }
				onChange={ ( v ) => set( 'slides_per_view', v ) }
			/>
			<RangeControl
				label={ __( 'Space between slides (px)', 'simple-wp-slider' ) }
				min={ 0 }
				max={ 200 }
				value={ s.space_between }
				onChange={ ( v ) => set( 'space_between', v ) }
			/>
			<SelectControl
				label={ __( 'Aspect ratio', 'simple-wp-slider' ) }
				value={ s.aspect_ratio }
				options={ [
					{ label: __( 'Auto', 'simple-wp-slider' ), value: 'auto' },
					{ label: '16:9', value: '16:9' },
					{ label: '4:3', value: '4:3' },
					{ label: '1:1', value: '1:1' },
				] }
				onChange={ ( v ) => set( 'aspect_ratio', v ) }
			/>
			<ToggleControl
				label={ __(
					'Respect prefers-reduced-motion (disable autoplay)',
					'simple-wp-slider'
				) }
				checked={ !! s.reduced_motion_disable_autoplay }
				onChange={ ( v ) =>
					set( 'reduced_motion_disable_autoplay', v )
				}
			/>
		</PanelBody>
	);
}
