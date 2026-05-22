import apiFetch from '@wordpress/api-fetch';

export const fetchSlider = ( id ) =>
	apiFetch( { path: `/swps/v1/sliders/${ id }` } );

export const saveSlider = ( id, payload ) =>
	apiFetch( {
		path: `/swps/v1/sliders/${ id }`,
		method: 'POST',
		data: payload,
	} );

export const reorderSlides = ( id, order ) =>
	apiFetch( {
		path: `/swps/v1/sliders/${ id }/reorder`,
		method: 'POST',
		data: { order },
	} );

export const resolveOembed = ( url ) => {
	const usp = new URLSearchParams( { url } );
	return apiFetch( { path: `/swps/v1/oembed-resolve?${ usp.toString() }` } );
};

export const dismissNotice = ( notice ) =>
	apiFetch( {
		path: '/swps/v1/notices/dismiss',
		method: 'POST',
		data: { notice },
	} );
