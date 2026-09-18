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

	if ( header && wash && solid && header.getAttribute( 'data-hero' ) === '1' ) {
		var raf = 0;

		var update = function () {
			var hero        = document.getElementById( 'hero' );
			var heroHeight  = ( hero && hero.offsetHeight ) || window.innerHeight;
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
} )();
