/**
 * Public-site behaviour. No framework, no build step.
 *
 * Three things, all of them ports of React state from the Next.js components:
 *   1. the header's scroll treatment over the homepage hero (Header.tsx)
 *   2. the mobile menu toggle (Header.tsx)
 *   3. scroll-triggered reveals (ui/Reveal.tsx)
 */
( function () {
	'use strict';

	/* ---------------------------------------------------------------------
	   1. Header over the hero.

	   Constants copied from Header.tsx so the feel matches exactly. Up to 80%
	   of the hero the bar only blurs; past that it fades to solid white. The
	   markup ships SOLID and this removes it, so a failed script leaves a
	   readable header rather than white text on a white bar.
	   --------------------------------------------------------------------- */
	var MAX_BLUR_PX    = 12;
	var HEADER_PX      = 64;
	var REVEAL_START   = 0.8;
	var HERO_DARK_RGB  = '21, 25, 37';

	var header = document.getElementById( 'jb-header' );
	var wash   = document.getElementById( 'jb-header-wash' );
	var solid  = document.getElementById( 'jb-header-solid' );

	// The hero element must actually EXIST, not merely be expected. Trusting
	// data-hero alone meant that on a homepage with no hero yet, heroHeight
	// fell back to the viewport, progress came out around 0.07, and the header
	// went into transparent-over-video mode on top of a white page -- rendering
	// every nav link white on white. Only the active link stayed visible,
	// because flame is flame either way.
	var hero = document.getElementById( 'hero' );

	if ( header && wash && solid && hero && header.getAttribute( 'data-hero' ) === '1' ) {
		var raf = 0;

		var update = function () {
			var heroHeight  = hero.offsetHeight || window.innerHeight;
			var progress    = Math.min( 1, Math.max( 0, ( window.scrollY + HEADER_PX ) / heroHeight ) );
			var blur, alpha;

			if ( progress <= REVEAL_START ) {
				blur  = ( progress / REVEAL_START ) * MAX_BLUR_PX;
				alpha = 0;
			} else {
				var t = ( progress - REVEAL_START ) / ( 1 - REVEAL_START );
				blur  = ( 1 - t ) * MAX_BLUR_PX;
				alpha = t;
			}

			wash.style.backgroundColor      = 'rgba(' + HERO_DARK_RGB + ', ' + alpha + ')';
			wash.style.backdropFilter       = 'blur(' + blur + 'px)';
			wash.style.webkitBackdropFilter = 'blur(' + blur + 'px)';

			// "clear" means the bar is still over the hero: hide the white
			// plate and let the light logo and white nav text show through.
			header.classList.toggle( 'is-clear', progress < 1 );
			solid.style.opacity = progress < 1 ? '0' : '1';
		};

		var onScroll = function () {
			cancelAnimationFrame( raf );
			raf = requestAnimationFrame( update );
		};

		update();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
	}

	/* ---------------------------------------------------------------------
	   2. Mobile menu.
	   --------------------------------------------------------------------- */
	var toggle = document.getElementById( 'jb-menu-toggle' );
	var drawer = document.getElementById( 'jb-mobile-nav' );

	if ( toggle && drawer ) {
		toggle.addEventListener( 'click', function () {
			var open = drawer.classList.toggle( 'hidden' ) === false;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );

		// Escape closes it, matching the React version's keydown handler.
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && ! drawer.classList.contains( 'hidden' ) ) {
				drawer.classList.add( 'hidden' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	/* ---------------------------------------------------------------------
	   3. Reveals.

	   Reveal.tsx fades content in as it enters the viewport. Elements start
	   visible in CSS and are only hidden once this runs, so with JavaScript
	   off -- or for a crawler -- the content is simply there. Anyone who has
	   asked for reduced motion is skipped entirely.
	   --------------------------------------------------------------------- */
	var targets = document.querySelectorAll( '[data-reveal]' );

	if ( targets.length && 'IntersectionObserver' in window &&
	     ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {

		Array.prototype.forEach.call( targets, function ( el ) {
			el.classList.add( 'jb-reveal' );
		} );

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}
				var el    = entry.target;
				var delay = parseInt( el.getAttribute( 'data-reveal-delay' ) || '0', 10 );
				setTimeout( function () {
					el.classList.add( 'is-revealed' );
				}, delay );
				observer.unobserve( el );
			} );
		}, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 } );

		Array.prototype.forEach.call( targets, function ( el ) {
			observer.observe( el );
		} );
	}

	/* ---------------------------------------------------------------------
	   4. Pillar showcase auto-rotation (ServicesShowcase + PillarVisual).

	   One of the four panels is unhidden at a time on a 6s timer, paused
	   while the pointer is over the visual -- ported from the setState/raf
	   loop in ServicesShowcase.tsx. The reveal-in motion inside a panel is
	   plain CSS keyed off the .is-active class this adds (see site.src.css),
	   so this only owns the timer, the progress bar width, and hide/show.
	   --------------------------------------------------------------------- */
	var showcase = document.getElementById( 'jb-showcase' );

	if ( showcase && ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		var visuals    = showcase.querySelectorAll( '[data-pillar-visual]' );
		var ctas       = showcase.querySelectorAll( '[data-pillar-cta]' );
		var texts      = showcase.querySelectorAll( '[data-pillar-text]' );
		var visualWrap = document.getElementById( 'jb-showcase-visual' );
		var count      = visuals.length;

		if ( count > 1 ) {
			var AUTO_MS    = parseInt( showcase.getAttribute( 'data-autoplay' ), 10 ) || 6000;
			var pv_active  = 0;
			var pv_paused  = false;
			var pv_progress = 0;
			var pv_last    = 0;
			var pv_raf     = 0;

			var pv_activate = function ( index ) {
				pv_active = index;
				for ( var i = 0; i < count; i++ ) {
					var on = i === index;
					visuals[ i ].classList.toggle( 'hidden', ! on );
					visuals[ i ].classList.toggle( 'is-active', on );
					ctas[ i ].classList.toggle( 'hidden', ! on );
					ctas[ i ].classList.toggle( 'is-active', on );
					texts[ i ].classList.toggle( 'hidden', ! on );
				}
			};

			var pv_setProgress = function ( p ) {
				var bar = ctas[ pv_active ].querySelector( '.jb-pv-progress' );
				if ( bar ) {
					bar.style.width = p + '%';
				}
			};

			var pv_tick = function ( now ) {
				if ( ! pv_paused ) {
					var dt = now - pv_last;
					pv_progress += ( dt / AUTO_MS ) * 100;
					if ( pv_progress >= 100 ) {
						pv_progress = 0;
						pv_activate( ( pv_active + 1 ) % count );
					}
					pv_setProgress( pv_progress );
				}
				pv_last = now;
				pv_raf = requestAnimationFrame( pv_tick );
			};

			if ( visualWrap ) {
				visualWrap.addEventListener( 'mouseenter', function () { pv_paused = true; } );
				visualWrap.addEventListener( 'mouseleave', function () { pv_paused = false; } );
			}

			pv_last = performance.now();
			pv_raf  = requestAnimationFrame( pv_tick );
		}
	}
} )();
