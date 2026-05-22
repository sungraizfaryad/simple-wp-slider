import {
	createContext,
	useContext,
	useReducer,
	useEffect,
	useCallback,
} from '@wordpress/element';
import { fetchSlider, saveSlider } from './rest';

const SliderCtx = createContext( null );

const initial = {
	id: 0,
	title: '',
	slides: [],
	settings: null,
	dirty: false,
	saving: false,
	error: null,
	loaded: false,
};

function reducer( state, action ) {
	switch ( action.type ) {
		case 'loaded':
			return {
				...state,
				...action.payload,
				dirty: false,
				saving: false,
				loaded: true,
			};
		case 'set_title':
			return { ...state, title: action.value, dirty: true };
		case 'set_slides':
			return { ...state, slides: action.value, dirty: true };
		case 'set_settings':
			return { ...state, settings: action.value, dirty: true };
		case 'saving':
			return { ...state, saving: true, error: null };
		case 'saved':
			return { ...state, ...action.payload, saving: false, dirty: false };
		case 'save_error':
			return { ...state, saving: false, error: action.error };
		default:
			return state;
	}
}

export function SliderProvider( { sliderId, children } ) {
	const [ state, dispatch ] = useReducer( reducer, {
		...initial,
		id: sliderId,
	} );

	useEffect( () => {
		if ( ! sliderId ) {
			return;
		}
		fetchSlider( sliderId )
			.then( ( data ) => dispatch( { type: 'loaded', payload: data } ) )
			.catch( ( err ) =>
				dispatch( {
					type: 'save_error',
					error: err.message || 'load failed',
				} )
			);
	}, [ sliderId ] );

	const save = useCallback( async () => {
		dispatch( { type: 'saving' } );
		try {
			const data = await saveSlider( state.id, {
				title: state.title,
				slides: state.slides,
				settings: state.settings,
			} );
			dispatch( { type: 'saved', payload: data } );
			return true;
		} catch ( err ) {
			dispatch( {
				type: 'save_error',
				error: err.message || 'save failed',
			} );
			return false;
		}
	}, [ state.id, state.title, state.slides, state.settings ] );

	// Auto-persist slides + settings when classic Publish/Update is clicked.
	useEffect( () => {
		if ( ! state.loaded || ! sliderId ) {
			return undefined;
		}
		const form = document.getElementById( 'post' );
		if ( ! form ) {
			return undefined;
		}
		let submitting = false;
		const handler = async ( event ) => {
			if ( submitting ) {
				return;
			}
			event.preventDefault();
			submitting = true;
			try {
				await saveSlider( sliderId, {
					title: state.title,
					slides: state.slides,
					settings: state.settings,
				} );
			} catch ( err ) {
				// Surface save errors in admin notice.
				dispatch( {
					type: 'save_error',
					error: err.message || 'save failed',
				} );
				submitting = false;
				return;
			}
			form.submit();
		};
		form.addEventListener( 'submit', handler );
		return () => form.removeEventListener( 'submit', handler );
	}, [ sliderId, state.loaded, state.slides, state.settings, state.title ] );

	return (
		<SliderCtx.Provider value={ { state, dispatch, save } }>
			{ children }
		</SliderCtx.Provider>
	);
}

export function useSlider() {
	const ctx = useContext( SliderCtx );
	if ( ! ctx ) {
		throw new Error( 'useSlider must be inside SliderProvider' );
	}
	return ctx;
}
