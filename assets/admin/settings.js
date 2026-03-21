/**
 * MVP Docs — Settings page JS (color picker init + copy URL).
 */
( function () {
	'use strict';

	// Initialize WP color pickers.
	if ( typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker ) {
		jQuery( document ).ready( function ( $ ) {
			$( '.mvpd-color-field' ).wpColorPicker();
		} );
	}

	// Copy URL buttons.
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.mvpd-copy-url' );
		if ( ! btn ) {
			return;
		}

		var url  = btn.dataset.url;
		var orig = btn.textContent;

		navigator.clipboard.writeText( url ).then( function () {
			btn.textContent = 'Copied!';
			setTimeout( function () {
				btn.textContent = orig;
			}, 2000 );
		} );
	} );
} )();
