/**
 * Catalog — admin settings enhancements (progressive, dependency-free).
 *
 * Inline help: each "?" button is wired to an accessible popover. Where the
 * native Popover API exists it is used; otherwise a small show/hide fallback
 * keeps it keyboard- and screen-reader-operable via aria-expanded.
 *
 * Loaded with `defer` and degrades gracefully: with JS disabled, all settings
 * still save and the help text remains reachable.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.catalog-admin' );

	if ( ! root ) {
		return;
	}

	var supportsPopover =
		typeof HTMLElement !== 'undefined' &&
		HTMLElement.prototype.hasOwnProperty( 'popover' );

	if ( supportsPopover ) {
		// Native Popover handles its own toggling; nothing more to wire up.
		return;
	}

	function closeAll( except ) {
		root.querySelectorAll( '.catalog-help[aria-expanded="true"]' ).forEach(
			function ( btn ) {
				if ( btn === except ) {
					return;
				}
				btn.setAttribute( 'aria-expanded', 'false' );
				var tip = document.getElementById(
					btn.getAttribute( 'aria-describedby' )
				);
				if ( tip ) {
					tip.hidden = true;
				}
			}
		);
	}

	root.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest( '.catalog-help' );

		if ( ! btn ) {
			return;
		}

		var tip = document.getElementById(
			btn.getAttribute( 'aria-describedby' )
		);

		if ( ! tip ) {
			return;
		}

		var open = btn.getAttribute( 'aria-expanded' ) === 'true';
		closeAll( btn );
		btn.setAttribute( 'aria-expanded', String( ! open ) );
		tip.hidden = open;
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' ) {
			closeAll( null );
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '.catalog-help, .catalog-tip' ) ) {
			closeAll( null );
		}
	} );
} )();
