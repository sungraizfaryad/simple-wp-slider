import Swiper from 'swiper';
import {
	Navigation,
	Pagination,
	Autoplay,
	A11y,
	Keyboard,
	EffectFade,
} from 'swiper/modules';
import { __ } from '@wordpress/i18n';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import './style.scss';

function initSlider( el ) {
	let cfg = {};
	try {
		cfg = JSON.parse( el.dataset.swpsConfig || '{}' );
	} catch ( e ) {
		cfg = {};
	}

	const reduceMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches;
	if ( reduceMotion && cfg.reduced_motion_disable_autoplay ) {
		cfg.autoplay = false;
	}

	const breakpoints =
		cfg.breakpoints && typeof cfg.breakpoints === 'object'
			? Object.fromEntries(
					Object.entries( cfg.breakpoints ).map( ( [ bp, v ] ) => [
						bp,
						{
							slidesPerView: v.slides_per_view || 1,
							spaceBetween: v.space_between || 0,
						},
					] )
			  )
			: undefined;

	const swiper = new Swiper( el.querySelector( '.swiper' ), {
		modules: [
			Navigation,
			Pagination,
			Autoplay,
			A11y,
			Keyboard,
			EffectFade,
		],
		loop: !! cfg.loop,
		speed: cfg.speed || 600,
		effect: cfg.effect || 'slide',
		keyboard: cfg.keyboard ? { enabled: true } : false,
		autoplay: cfg.autoplay
			? {
					delay: cfg.autoplay_delay || 5000,
					pauseOnMouseEnter: !! cfg.pause_on_hover,
					disableOnInteraction: false,
			  }
			: false,
		navigation: cfg.arrows
			? {
					prevEl: el.querySelector( '.swiper-button-prev' ),
					nextEl: el.querySelector( '.swiper-button-next' ),
			  }
			: false,
		pagination: cfg.dots
			? {
					el: el.querySelector( '.swiper-pagination' ),
					clickable: true,
			  }
			: false,
		slidesPerView: cfg.slides_per_view || 1,
		spaceBetween: cfg.space_between || 0,
		breakpoints,
		a11y: {
			prevSlideMessage: __( 'Previous slide', 'simple-wp-slider' ),
			nextSlideMessage: __( 'Next slide', 'simple-wp-slider' ),
		},
		on: {
			slideChangeTransitionStart: ( s ) => {
				s.slides.forEach( ( slide ) => {
					const v = slide.querySelector( 'video.swps-video' );
					if ( v ) {
						v.pause();
					}
				} );
				const active = s.slides[ s.activeIndex ];
				const av =
					active &&
					active.querySelector(
						'video.swps-video[data-swps-autoplay-on-active="1"]'
					);
				if ( av ) {
					av.play().catch( () => {} );
				}
			},
		},
	} );

	el.querySelectorAll( '.swps-yt-facade' ).forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			const id = btn.dataset.ytId;
			if ( ! id ) {
				return;
			}
			const iframe = document.createElement( 'iframe' );
			iframe.src = `https://www.youtube-nocookie.com/embed/${ id }?autoplay=1&rel=0`;
			iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
			iframe.allowFullscreen = true;
			iframe.title = btn.getAttribute( 'aria-label' ) || 'YouTube video';
			btn.replaceWith( iframe );
			if ( swiper.autoplay ) {
				swiper.autoplay.stop();
			}
		} );
	} );

	el.querySelectorAll( '.swps-vimeo-facade' ).forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			const id = btn.dataset.vimeoId;
			if ( ! id ) {
				return;
			}
			const iframe = document.createElement( 'iframe' );
			iframe.src = `https://player.vimeo.com/video/${ id }?autoplay=1`;
			iframe.allow = 'autoplay; fullscreen; picture-in-picture';
			iframe.allowFullscreen = true;
			iframe.title = btn.getAttribute( 'aria-label' ) || 'Vimeo video';
			btn.replaceWith( iframe );
			if ( swiper.autoplay ) {
				swiper.autoplay.stop();
			}
		} );
	} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	document.querySelectorAll( '.swps-slider' ).forEach( initSlider );
} );
